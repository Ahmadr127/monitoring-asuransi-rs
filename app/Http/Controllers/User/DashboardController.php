<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\RuangLingkup;
use App\Traits\PreparesDashboardData;

class DashboardController extends Controller
{
    use PreparesDashboardData;

    public function index()
    {
        $data = $this->prepareDashboardData();

        return view('user.dashboard', $data);
    }

    public function kerjasamaDetail($type)
    {
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
                return redirect()->route('user.kerjasama.index');
        }

        $visibleColumns = $this->getVisibleColumns();

        $kerjasama = $kerjasama->map(function ($item) {
            return $this->mapRuangLingkup($item, false, false);
        });

        return view('user.kerjasama_list', compact('kerjasama', 'title', 'type', 'icon', 'color', 'visibleColumns'));
    }
}
