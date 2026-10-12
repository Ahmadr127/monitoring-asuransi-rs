<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\JenisLayanan;
use App\Models\Obat;
use App\Models\RuangLingkup;
use App\Traits\PreparesDashboardData;

class KerjasamaController extends Controller
{
    use PreparesDashboardData;

    public function index()
    {
        RuangLingkup::expiredButActive()->get()->each(fn ($c) => $c->updateExpiredStatus());

        $ruangLingkup = RuangLingkup::with([
            'diskon',
            'penagihanPembayaran',
            'contractHistory',
            'jenisLayananRelasi',
            'obatRelasi',
            'badanUsahaPivot.badanUsaha',
        ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->flatMap(fn ($rl) => $rl->badanUsahaPivot->map(fn ($p) => [
                'rl' => $rl,
                'pivot' => $p,
                'bu_nama' => $p->badanUsaha->nama_bu ?? '',
                'benefit' => $p->benefit ?? [],
                'cakupan' => $p->cakupan ?? [],
            ]));

        $visibleColumns = $this->getVisibleColumns(false);

        return view('user.ruang_lingkup', compact('ruangLingkup', 'visibleColumns'));
    }

    public function show(RuangLingkup $ruangLingkup)
    {
        $ruangLingkup->load([
            'badanUsahaPivot.badanUsaha',
            'diskon',
            'penagihanPembayaran',
            'jenisLayananRelasi',
            'obatRelasi',
            'contractHistory',
        ]);

        $visibleColumns = $this->getVisibleColumns(true);
        $jenisLayanan = JenisLayanan::orderBy('nama', 'asc')->get();
        $masterObat = Obat::orderBy('nama', 'asc')->get();

        return view('user.kerjasama_detail', compact('ruangLingkup', 'visibleColumns', 'jenisLayanan', 'masterObat'));
    }

    public function exportCsv()
    {
        $ruangLingkup = RuangLingkup::with(['badanUsahaPivot.badanUsaha'])
            ->orderBy('created_at', 'desc')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="kerjasama-asuransi.csv"',
        ];

        $callback = function () use ($ruangLingkup) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'Nama Asuransi', 'Badan Usaha', 'Periode Mulai', 'Periode Berakhir',
                'Status', 'Jenis Layanan', 'Diskon', 'PIC Nama', 'Kontak PIC', 'Email PIC', 'Alamat Penagihan',
            ]);

            foreach ($ruangLingkup as $rl) {
                $buNames = $rl->badanUsahaPivot->map(fn ($p) => $p->badanUsaha->nama_bu ?? '')->filter()->implode('; ');
                $layanan = $rl->jenisLayananRelasi->pluck('nama')->implode('; ');
                $diskon = $rl->diskon?->first()?->persentase_diskon ?? '';

                fputcsv($file, [
                    $rl->nama_asuransi,
                    $buNames,
                    $rl->tanggal_mulai?->format('d/m/Y'),
                    $rl->tanggal_berakhir?->format('d/m/Y'),
                    $rl->status,
                    $layanan,
                    $diskon ? $diskon.'%' : '',
                    $rl->pic_nama ?? '',
                    $rl->pic_kontak ?? '',
                    $rl->pic_email ?? '',
                    $rl->alamat_penagihan ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

}
