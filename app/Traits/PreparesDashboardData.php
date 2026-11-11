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
        RuangLingkup::expiredButActive()->get()->each(fn ($c) => $c->updateExpiredStatus());

        $all = RuangLingkup::with([
            'diskon', 'penagihanPembayaran', 'badanUsaha', 'jenisLayananRelasi', 'obatRelasi', 'badanUsahaPivot',
        ])->approved()->get()->map(function ($item) {
            $item = $this->mapRuangLingkup($item, true, false);

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

        $activeItems = $all->where('status', 'aktif');
        $inactiveItems = $all->where('status', 'tidak_aktif');

        $kerjasamaAktif = $activeItems->map(function ($item) {
            return $this->mapRuangLingkup($item, true, true);
        })->sortBy('days_remaining')->values();

        $kerjasamaTidakAktif = $inactiveItems
            ->sortByDesc('created_at')
            ->map(fn ($item) => $this->mapRuangLingkup($item, false, false))
            ->values();

        $expiringContracts30 = $activeItems->filter(function ($item) {
            if (! $item->tanggal_berakhir) {
                return false;
            }

            $end = $item->tanggal_berakhir->startOfDay();

            return $end->greaterThanOrEqualTo(now()->startOfDay()) && $end->lessThanOrEqualTo(now()->addDays(RuangLingkup::EXPIRING_SOON));
        })->sortBy('tanggal_berakhir')->map(function ($item) {
            return $this->mapRuangLingkup($item, true, true);
        })->values();

        $expiringContracts90 = $activeItems->filter(function ($item) {
            if (! $item->tanggal_berakhir) {
                return false;
            }

            $end = $item->tanggal_berakhir->startOfDay();

            return $end->greaterThanOrEqualTo(now()->startOfDay()) && $end->lessThanOrEqualTo(now()->addDays(RuangLingkup::EXPIRING_WARNING));
        })->sortBy('tanggal_berakhir')->map(function ($item) {
            return $this->mapRuangLingkup($item, true, true);
        })->values();

        $tableSetting = RoleTableSetting::where('role_id', auth()->user()->role_id)
            ->where('table_name', 'ruang_lingkup')
            ->first();

        $visibleColumns = $tableSetting
            ? $tableSetting->visible_columns
            : ['nama_asuransi', 'alias_versi_tera', 'penjamin_category', 'periode', 'status', 'jenis_layanan'];

        return compact(
            'all', 'allKerjasama', 'activeItems', 'inactiveItems',
            'kerjasamaAktif', 'kerjasamaTidakAktif',
            'expiringContracts30', 'expiringContracts90',
            'visibleColumns'
        );
    }

    private function getVisibleColumns(bool $includeHistory = false, array $defaults = null): array
    {
        $tableSetting = RoleTableSetting::where('role_id', auth()->user()->role_id)
            ->where('table_name', 'ruang_lingkup')
            ->first();

        if ($defaults === null) {
            $defaults = [
                'nama_asuransi', 'alias_versi_tera', 'is_high_profile', 'penjamin_category',
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
