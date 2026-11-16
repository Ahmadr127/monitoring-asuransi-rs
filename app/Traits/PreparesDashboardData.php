<?php

namespace App\Traits;

use App\Models\RoleTableSetting;
use App\Models\RuangLingkup;

trait PreparesDashboardData
{
    private function mapRuangLingkup($item, $withDays = true, $clampDays = false)
    {
        if ($withDays) {
            if ($item->tanggal_berakhir) {
                $days = now()->startOfDay()->diffInDays($item->tanggal_berakhir->startOfDay(), false);
                $item->days_remaining = $clampDays ? max(0, $days) : $days;
            } else {
                $item->days_remaining = null;
            }
        }

        $item->jenis_layanan = $item->jenisLayananRelasi->pluck('nama')->toArray();
        $item->diskon_persen = $item->diskon?->count() > 0 ? $item->diskon->first()->persentase_diskon : 0;
        $item->is_high_profile = (bool) $item->is_high_profile;

        $pivotBenefits = $item->badanUsahaPivot->pluck('benefit')->flatten()->filter()->values()->toArray();
        $item->benefit = $pivotBenefits;

        $pivotCakupan = $item->badanUsahaPivot->pluck('cakupan')->flatten()->filter()->values()->toArray();
        $item->cakupan = $pivotCakupan;

        $item->obat = $item->obatRelasi->pluck('nama')->toArray();

        $item->deskripsi_list = $item->badanUsahaPivot->pluck('deskripsi')->filter()->values()->toArray();

        return $item;
    }

    private function prepareDashboardData()
    {
        // Expired contracts di-update oleh schedule harian `contracts:update-expired`
        // (lihat routes/console.php), tidak perlu ditulis saat GET halaman.
        // Map dilakukan SATU KALI per item; list lain diturunkan dari sini
        // (tidak ada relasi yang dipanggil ulang atau @json duplikat besar).
        $all = RuangLingkup::with([
            'diskon', 'penagihanPembayaran', 'badanUsaha', 'jenisLayananRelasi', 'obatRelasi', 'badanUsahaPivot',
        ])->approved()->get()->map(function ($item) {
            $this->mapRuangLingkup($item, true, false);

            if ($item->status === 'aktif') {
                $item->sort_priority = 1;
            } else {
                $item->days_remaining = RuangLingkup::SORT_INACTIVE;
                $item->sort_priority = 2;
            }

            return $item;
        });

        $allKerjasama = $all->sortBy([
            ['sort_priority', 'asc'],
            ['days_remaining', 'asc'],
        ])->values();

        $activeItems = $all->where('status', 'aktif')->sortBy('days_remaining')->values();
        $inactiveItems = $all->where('status', 'tidak_aktif')->sortByDesc('created_at')->values();

        $kerjasamaAktif = $activeItems;
        $kerjasamaTidakAktif = $inactiveItems;

        $filterExpiring = function (int $days) use ($activeItems) {
            $end = now()->addDays($days)->startOfDay();
            $today = now()->startOfDay();

            return $activeItems->filter(function ($item) use ($today, $end) {
                if (! $item->tanggal_berakhir) {
                    return false;
                }
                $itemEnd = $item->tanggal_berakhir->startOfDay();

                return $itemEnd->greaterThanOrEqualTo($today) && $itemEnd->lessThanOrEqualTo($end);
            })->sortBy('tanggal_berakhir')->values();
        };

        $expiringContracts30 = $filterExpiring(RuangLingkup::EXPIRING_SOON);
        $expiringContracts90 = $filterExpiring(RuangLingkup::EXPIRING_WARNING);

        $tableSetting = RoleTableSetting::where('role_id', auth()->user()->role_id)
            ->where('table_name', 'ruang_lingkup')
            ->first();

        $visibleColumns = $tableSetting
            ? $tableSetting->visible_columns
            : ['nama_asuransi', 'alias_versi_tera', 'nomor_spk', 'penjamin_category', 'periode', 'status', 'jenis_layanan'];

        return compact(
            'all', 'allKerjasama', 'activeItems', 'inactiveItems',
            'kerjasamaAktif', 'kerjasamaTidakAktif',
            'expiringContracts30', 'expiringContracts90',
            'visibleColumns'
        );
    }

    private function getVisibleColumns(bool $includeHistory = false, ?array $defaults = null): array
    {
        $tableSetting = RoleTableSetting::where('role_id', auth()->user()->role_id)
            ->where('table_name', 'ruang_lingkup')
            ->first();

        if ($defaults === null) {
            $defaults = [
                'nama_asuransi', 'alias_versi_tera', 'nomor_spk', 'is_high_profile', 'penjamin_category',
                'jangka_waktu', 'badan_usaha', 'diskon', 'jenis_layanan',
                'benefit', 'obat', 'cakupan', 'periode', 'status',
            ];
        }

        if ($includeHistory) {
            $defaults[] = 'contract_history';
        }

        return $tableSetting
            ? $tableSetting->visible_columns
            : $defaults;
    }
}
