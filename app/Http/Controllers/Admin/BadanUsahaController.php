<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BadanUsaha;
use Illuminate\Http\Request;

class BadanUsahaController extends Controller
{
    public function index()
    {
        $badanUsaha = BadanUsaha::orderBy('nama_bu', 'asc')->get();

        return view('admin.master.badan_usaha', compact('badanUsaha'));
    }

    public function store(Request $request)
    {
        try {
            $request->merge([
                'nama_bu' => strtoupper($request->nama_bu),
            ]);

            $validated = $request->validate([
                'nama_bu' => [
                    'required', 'string', 'max:255',
                    function ($attribute, $value, $fail) {
                        if (BadanUsaha::whereRaw('LOWER(nama_bu) = ?', [strtolower($value)])->exists()) {
                            $fail('Nama badan usaha sudah ada');
                        }
                    },
                ],
            ], [
                'nama_bu.required' => 'Nama badan usaha wajib diisi',
                'nama_bu.max' => 'Nama badan usaha maksimal 255 karakter',
            ]);

            BadanUsaha::create($validated);

            return redirect()->route('admin.master-badan-usaha.index')
                ->with('success', 'Data badan usaha berhasil ditambahkan');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Gagal menambahkan data: '.implode(', ', $e->validator->errors()->all()));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menambahkan data: '.$e->getMessage())
                ->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $badanUsaha = BadanUsaha::findOrFail($id);

        try {
            $request->merge([
                'nama_bu' => strtoupper($request->nama_bu),
            ]);

            $validated = $request->validate([
                'nama_bu' => [
                    'required', 'string', 'max:255',
                    function ($attribute, $value, $fail) use ($badanUsaha) {
                        if (BadanUsaha::whereRaw('LOWER(nama_bu) = ?', [strtolower($value)])
                            ->where('id', '!=', $badanUsaha->id)->exists()) {
                            $fail('Nama badan usaha sudah ada');
                        }
                    },
                ],
            ], [
                'nama_bu.required' => 'Nama badan usaha wajib diisi',
                'nama_bu.max' => 'Nama badan usaha maksimal 255 karakter',
            ]);

            $badanUsaha->update($validated);

            return redirect()->route('admin.master-badan-usaha.index')
                ->with('success', 'Data badan usaha berhasil diupdate');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Gagal mengupdate data: '.implode(', ', $e->validator->errors()->all()));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengupdate data: '.$e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $badanUsaha = BadanUsaha::findOrFail($id);

        try {
            $badanUsaha->delete();

            return redirect()->route('admin.master-badan-usaha.index')
                ->with('success', 'Data badan usaha berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('admin.master-badan-usaha.index')
                ->with('error', 'Gagal menghapus data: '.$e->getMessage());
        }
    }
}
