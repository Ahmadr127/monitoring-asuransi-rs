<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisLayanan;
use Illuminate\Http\Request;

class JenisLayananController extends Controller
{
    public function index()
    {
        $jenisLayanan = JenisLayanan::orderBy('nama', 'asc')->get();

        return view('admin.master.jenis_layanan', compact('jenisLayanan'));
    }

    public function store(Request $request)
    {
        try {
            $request->merge([
                'nama' => strtoupper($request->nama),
                'kode' => strtoupper($request->kode),
            ]);

            $request->validate([
                'nama' => [
                    'required', 'string', 'max:255',
                    function ($attribute, $value, $fail) {
                        if (JenisLayanan::whereRaw('LOWER(nama) = ?', [strtolower($value)])->exists()) {
                            $fail('Nama jenis layanan sudah ada.');
                        }
                    },
                ],
                'kode' => [
                    'required', 'string', 'max:50',
                    function ($attribute, $value, $fail) {
                        if (JenisLayanan::whereRaw('LOWER(kode) = ?', [strtolower($value)])->exists()) {
                            $fail('Kode layanan sudah digunakan.');
                        }
                    },
                ],
                'deskripsi' => 'nullable|string',
            ]);

            $layanan = JenisLayanan::create([
                'nama' => $request->nama,
                'kode' => $request->kode,
                'deskripsi' => $request->deskripsi,
            ]);

            if ($request->wantsJson()) {
                return response()->json(['id' => $layanan->id, 'nama' => $layanan->nama, 'kode' => $layanan->kode]);
            }

            return redirect()->route('admin.master-jenis-layanan.index')
                ->with('success', 'Jenis Layanan berhasil ditambahkan');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Validasi gagal', 'errors' => $e->errors()], 422);
            }

            return redirect()->route('admin.master-jenis-layanan.index')
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Gagal: '.implode(', ', $e->validator->errors()->all()));
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Gagal menambahkan data: '.$e->getMessage()], 500);
            }

            return redirect()->route('admin.master-jenis-layanan.index')
                ->with('error', 'Gagal menambahkan data: '.$e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $jenisLayanan = JenisLayanan::findOrFail($id);

            $request->merge([
                'nama' => strtoupper($request->nama),
                'kode' => strtoupper($request->kode),
            ]);

            $request->validate([
                'nama' => [
                    'required', 'string', 'max:255',
                    function ($attribute, $value, $fail) use ($jenisLayanan) {
                        if (JenisLayanan::whereRaw('LOWER(nama) = ?', [strtolower($value)])
                            ->where('id', '!=', $jenisLayanan->id)->exists()) {
                            $fail('Nama jenis layanan sudah ada.');
                        }
                    },
                ],
                'kode' => [
                    'required', 'string', 'max:50',
                    function ($attribute, $value, $fail) use ($jenisLayanan) {
                        if (JenisLayanan::whereRaw('LOWER(kode) = ?', [strtolower($value)])
                            ->where('id', '!=', $jenisLayanan->id)->exists()) {
                            $fail('Kode layanan sudah digunakan.');
                        }
                    },
                ],
                'deskripsi' => 'nullable|string',
            ]);

            $jenisLayanan->update([
                'nama' => $request->nama,
                'kode' => $request->kode,
                'deskripsi' => $request->deskripsi,
            ]);

            return redirect()->route('admin.master-jenis-layanan.index')
                ->with('success', 'Jenis Layanan berhasil diperbarui');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('admin.master-jenis-layanan.index')
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Gagal: '.implode(', ', $e->validator->errors()->all()));
        } catch (\Exception $e) {
            return redirect()->route('admin.master-jenis-layanan.index')
                ->with('error', 'Gagal mengupdate data: '.$e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $jenisLayanan = JenisLayanan::findOrFail($id);
            $jenisLayanan->delete();

            return redirect()->route('admin.master-jenis-layanan.index')
                ->with('success', 'Jenis Layanan berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('admin.master-jenis-layanan.index')
                ->with('error', 'Gagal menghapus data: '.$e->getMessage());
        }
    }
}
