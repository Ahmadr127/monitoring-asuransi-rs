<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PenagihanPembayaran;
use App\Models\RuangLingkup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PenagihanPembayaranController extends Controller
{
    public function index()
    {
        $penagihans = PenagihanPembayaran::with('ruangLingkup')->orderBy('created_at', 'desc')->get();
        $ruangLingkups = RuangLingkup::where('status', 'aktif')->get();
        $existingRlIds = $penagihans->pluck('ruang_lingkup_id')->toArray();

        return view('admin.master.penagihan_pembayaran', compact('penagihans', 'ruangLingkups', 'existingRlIds'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'ruang_lingkup_id' => [
                    'required', 'exists:ruang_lingkup,id',
                    Rule::unique('penagihan_pembayaran', 'ruang_lingkup_id'),
                ],
                'jangka_waktu_hari' => 'required|integer|min:1',
                'tipe_hari' => 'required|string',
                'keterangan' => 'nullable|string',
            ], [
                'ruang_lingkup_id.unique' => 'Asuransi ini sudah memiliki penagihan & pembayaran.',
            ]);

            PenagihanPembayaran::create($validated);

            return redirect()->route('admin.penagihan-pembayaran.index')
                ->with('success', 'Data penagihan & pembayaran berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->route('admin.penagihan-pembayaran.index')
                ->with('error', 'Gagal menambahkan data: '.$e->getMessage());
        }
    }

    public function update(Request $request, PenagihanPembayaran $penagihanPembayaran)
    {
        try {
            $validated = $request->validate([
                'ruang_lingkup_id' => [
                    'required', 'exists:ruang_lingkup,id',
                    Rule::unique('penagihan_pembayaran', 'ruang_lingkup_id')->ignore($penagihanPembayaran->id),
                ],
                'jangka_waktu_hari' => 'required|integer|min:1',
                'tipe_hari' => 'required|string',
                'keterangan' => 'nullable|string',
            ], [
                'ruang_lingkup_id.unique' => 'Asuransi ini sudah memiliki penagihan & pembayaran.',
            ]);

            $penagihanPembayaran->update($validated);

            return redirect()->route('admin.penagihan-pembayaran.index')
                ->with('success', 'Data penagihan & pembayaran berhasil diupdate');
        } catch (\Exception $e) {
            return redirect()->route('admin.penagihan-pembayaran.index')
                ->with('error', 'Gagal mengupdate data: '.$e->getMessage());
        }
    }

    public function destroy(PenagihanPembayaran $penagihanPembayaran)
    {
        try {
            $penagihanPembayaran->delete();

            return redirect()->route('admin.penagihan-pembayaran.index')
                ->with('success', 'Data penagihan & pembayaran berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('admin.penagihan-pembayaran.index')
                ->with('error', 'Gagal menghapus data: '.$e->getMessage());
        }
    }
}
