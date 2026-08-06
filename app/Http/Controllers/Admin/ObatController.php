<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Obat;
use Illuminate\Http\Request;

class ObatController extends Controller
{
    public function index()
    {
        $obat = Obat::orderBy('nama', 'asc')->get();

        return view('admin.master.obat', compact('obat'));
    }

    public function store(Request $request)
    {
        try {
            $request->merge([
                'nama' => strtoupper($request->nama),
            ]);

            $request->validate([
                'nama' => [
                    'required', 'string', 'max:255',
                    function ($attribute, $value, $fail) {
                        if (Obat::whereRaw('LOWER(nama) = ?', [strtolower($value)])->exists()) {
                            $fail('Nama obat ini sudah terdaftar dalam sistem.');
                        }
                    },
                ],
            ]);

            Obat::create([
                'nama' => $request->nama,
            ]);

            return redirect()->route('admin.master-obat.index')
                ->with('success', 'Obat berhasil ditambahkan');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('admin.master-obat.index')
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Gagal: '.implode(', ', $e->validator->errors()->all()));
        } catch (\Exception $e) {
            return redirect()->route('admin.master-obat.index')
                ->with('error', 'Gagal menambahkan data: '.$e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $obat = Obat::findOrFail($id);

            $request->merge([
                'nama' => strtoupper($request->nama),
            ]);

            $request->validate([
                'nama' => [
                    'required', 'string', 'max:255',
                    function ($attribute, $value, $fail) use ($obat) {
                        if (Obat::whereRaw('LOWER(nama) = ?', [strtolower($value)])
                            ->where('id', '!=', $obat->id)->exists()) {
                            $fail('Nama obat ini sudah terdaftar dalam sistem.');
                        }
                    },
                ],
            ]);

            $obat->update([
                'nama' => $request->nama,
            ]);

            return redirect()->route('admin.master-obat.index')
                ->with('success', 'Obat berhasil diperbarui');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('admin.master-obat.index')
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Gagal: '.implode(', ', $e->validator->errors()->all()));
        } catch (\Exception $e) {
            return redirect()->route('admin.master-obat.index')
                ->with('error', 'Gagal mengupdate data: '.$e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $obat = Obat::findOrFail($id);
            $obat->delete();

            return redirect()->route('admin.master-obat.index')
                ->with('success', 'Obat berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('admin.master-obat.index')
                ->with('error', 'Gagal menghapus data: '.$e->getMessage());
        }
    }
}
