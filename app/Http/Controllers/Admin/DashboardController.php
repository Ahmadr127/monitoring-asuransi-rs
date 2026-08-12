<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RuangLingkup;
use App\Traits\PreparesDashboardData;

class DashboardController extends Controller
{
    use PreparesDashboardData;

    public function index()
    {
        $user = auth()->user();

        if (! $user->isAdmin()) {
            return redirect()->route('user.kerjasama.index');
        }

        $data = $this->prepareDashboardData();
        $data['kerjasama'] = $data['allKerjasama'];
        $data['expiringContracts'] = $data['expiringContracts90'];
        $data['notifications'] = collect();

        foreach ($data['expiringContracts90'] as $contract) {
            $data['notifications']->push([
                'type' => 'warning',
                'title' => 'Kontrak Akan Berakhir',
                'message' => $contract->nama_asuransi.' akan berakhir dalam '.floor($contract->days_remaining).' hari',
                'time' => $contract->tanggal_berakhir->diffForHumans(),
                'icon' => 'clock',
            ]);
        }

        foreach ($data['all']->where('created_at', '>=', now()->subDays(7))->sortByDesc('created_at') as $contract) {
            $data['notifications']->push([
                'type' => 'success',
                'title' => 'Kontrak Baru Ditambahkan',
                'message' => $contract->nama_asuransi.' telah ditambahkan',
                'time' => $contract->created_at->diffForHumans(),
                'icon' => 'plus',
            ]);
        }

        foreach ($data['all']->filter(fn ($c) => $c->updated_at >= now()->subDays(7) && ! $c->updated_at->eq($c->created_at))->sortByDesc('updated_at') as $contract) {
            $data['notifications']->push([
                'type' => 'info',
                'title' => 'Kontrak Diperbarui',
                'message' => $contract->nama_asuransi.' telah diperbarui',
                'time' => $contract->updated_at->diffForHumans(),
                'icon' => 'refresh',
            ]);
        }

        return view('admin.dashboard', $data);
    }

    public function kerjasamaDetail($type)
    {
        $user = auth()->user();

        if (! $user->isAdmin()) {
            return redirect()->route('user.kerjasama.index');
        }

        $with = ['diskon', 'penagihanPembayaran', 'badanUsaha', 'jenisLayananRelasi', 'obatRelasi', 'badanUsahaPivot'];

        $icon = 'document';
        $color = 'success';

        switch ($type) {
            case 'all':
                $title = 'Semua Kerjasama';
                $kerjasama = RuangLingkup::with($with)->orderBy('created_at', 'desc')->get();
                break;

            case 'aktif':
                $title = 'Kerjasama Aktif';
                $kerjasama = RuangLingkup::with($with)->approved()->where('status', 'aktif')->orderBy('created_at', 'desc')->get();
                $icon = 'check-circle';
                break;

            case 'expiring':
                $title = 'Kerjasama Akan Berakhir';
                $kerjasama = RuangLingkup::with($with)->approved()
                    ->where('status', 'aktif')
                    ->whereDate('tanggal_berakhir', '>', now())
                    ->whereDate('tanggal_berakhir', '<=', now()->addDays(30))
                    ->orderBy('tanggal_berakhir', 'asc')
                    ->get()
                    ->map(function ($item) {
                        $item->days_remaining = now()->diffInDays($item->tanggal_berakhir);

                        return $item;
                    });
                $icon = 'clock';
                $color = 'warning';
                break;

            case 'tidak_aktif':
                $title = 'Kerjasama Tidak Aktif';
                $kerjasama = RuangLingkup::with($with)->where('status', 'tidak_aktif')->orderBy('created_at', 'desc')->get();
                $icon = 'x';
                $color = 'danger';
                break;

            default:
                return redirect()->route('admin.dashboard');
        }

        $visibleColumns = $this->getVisibleColumns();

        $kerjasama = $kerjasama->map(function ($item) {
            return $this->mapRuangLingkup($item, false, false);
        });

        return view('admin.kerjasama_list', compact('kerjasama', 'title', 'type', 'icon', 'color', 'visibleColumns'));
    }
}
