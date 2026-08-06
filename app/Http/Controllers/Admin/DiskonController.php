<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Diskon;
use App\Models\RuangLingkup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DiskonController extends Controller
{
    public function index()
    {
        $diskons = Diskon::with('ruangLingkup')
            ->orderBy('created_at', 'desc')
            ->get();

        $ruangLingkups = RuangLingkup::where('status', 'aktif')->get();
        $existingRlIds = $diskons->pluck('ruang_lingkup_id')->toArray();

        return view('admin.master.diskon', compact('diskons', 'ruangLingkups', 'existingRlIds'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'ruang_lingkup_id' => [
                    'required', 'exists:ruang_lingkup,id',
                    Rule::unique('diskon', 'ruang_lingkup_id'),
                ],
                'persentase_diskon' => 'required|integer|min:0|max:100',
                'keterangan' => 'nullable|string',
            ], [
                'ruang_lingkup_id.unique' => 'Asuransi ini sudah memiliki diskon.',
            ]);

            Diskon::create([
                'ruang_lingkup_id' => $validated['ruang_lingkup_id'],
                'persentase_diskon' => $validated['persentase_diskon'],
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            return redirect()->route('admin.diskon.index')
                ->with('success', 'Data diskon berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->route('admin.diskon.index')
                ->with('error', 'Gagal menambahkan data: '.$e->getMessage());
        }
    }

    public function update(Request $request, Diskon $diskon)
    {
        try {
            $validated = $request->validate([
                'ruang_lingkup_id' => [
                    'required', 'exists:ruang_lingkup,id',
                    Rule::unique('diskon', 'ruang_lingkup_id')->ignore($diskon->id),
                ],
                'persentase_diskon' => 'required|integer|min:0|max:100',
                'keterangan' => 'nullable|string',
            ], [
                'ruang_lingkup_id.unique' => 'Asuransi ini sudah memiliki diskon.',
            ]);

            $diskon->update($validated);

            return redirect()->route('admin.diskon.index')
                ->with('success', 'Data diskon berhasil diupdate');
        } catch (\Exception $e) {
            return redirect()->route('admin.diskon.index')
                ->with('error', 'Gagal mengupdate data: '.$e->getMessage());
        }
    }

    public function destroy(Diskon $diskon)
    {
        try {
            $diskon->delete();

            return redirect()->route('admin.diskon.index')
                ->with('success', 'Data diskon berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('admin.diskon.index')
                ->with('error', 'Gagal menghapus data: '.$e->getMessage());
        }
    }
}
