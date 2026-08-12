<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::withCount('roles')->orderBy('name')->get();

        return view('admin.master.permissions', compact('permissions'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:permissions,name|alpha_dash',
                'display_name' => 'nullable|string|max:255',
            ], [
                'name.required' => 'Nama permission harus diisi',
                'name.unique' => 'Permission sudah ada',
                'name.alpha_dash' => 'Permission hanya huruf, angka, dash dan underscore',
            ]);

            Permission::create([
                'name' => Str::lower($request->name),
                'display_name' => $request->display_name,
            ]);

            return redirect()->route('admin.permissions.index')
                ->with('success', 'Permission berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error('Gagal menambahkan permission: '.$e->getMessage());

            return redirect()->route('admin.permissions.index')
                ->with('error', 'Gagal menambahkan permission: '.$e->getMessage());
        }
    }

    public function destroy(Permission $permission)
    {
        try {
            if ($permission->roles()->count() > 0) {
                return redirect()->route('admin.permissions.index')
                    ->with('error', 'Permission masih dipakai role, tidak dapat dihapus');
            }

            $permission->delete();

            return redirect()->route('admin.permissions.index')
                ->with('success', 'Permission berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus permission: '.$e->getMessage());

            return redirect()->route('admin.permissions.index')
                ->with('error', 'Gagal menghapus permission: '.$e->getMessage());
        }
    }
}