<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')->with('permissions')->orderBy('created_at', 'desc')->get();
        $permissions = \App\Models\Permission::orderBy('name')->get();

        return view('admin.master.roles', compact('roles', 'permissions'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:roles,name',
                'key' => 'required|string|max:255|unique:roles,key|alpha_dash',
            ], [
                'name.required' => 'Nama role harus diisi',
                'name.unique' => 'Nama role sudah digunakan',
                'key.required' => 'Key role harus diisi',
                'key.unique' => 'Key role sudah digunakan',
                'key.alpha_dash' => 'Key role hanya boleh berisi huruf, angka, dash dan underscore',
            ]);

            Role::create([
                'name' => $request->name,
                'key' => Str::lower($request->key),
            ]);

            return redirect()->route('admin.roles.index')
                ->with('success', 'Role berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error('Gagal menambahkan role: '.$e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()->route('admin.roles.index')
                ->with('error', 'Gagal menambahkan role: '.$e->getMessage());
        }
    }

    public function update(Request $request, Role $role)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:roles,name,'.$role->id,
                'key' => 'required|string|max:255|unique:roles,key,'.$role->id.'|alpha_dash',
                'permissions' => 'nullable|array',
                'permissions.*' => 'exists:permissions,id',
            ], [
                'name.required' => 'Nama role harus diisi',
                'name.unique' => 'Nama role sudah digunakan',
                'key.required' => 'Key role harus diisi',
                'key.unique' => 'Key role sudah digunakan',
                'key.alpha_dash' => 'Key role hanya boleh berisi huruf, angka, dash dan underscore',
            ]);

            $role->update([
                'name' => $request->name,
                'key' => Str::lower($request->key),
            ]);

            if ($request->has('permissions')) {
                $role->permissions()->sync($request->input('permissions', []));
            }

            return redirect()->route('admin.roles.index')
                ->with('success', 'Role berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Gagal mengupdate role: '.$e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()->route('admin.roles.index')
                ->with('error', 'Gagal mengupdate role: '.$e->getMessage());
        }
    }

    public function destroy(Role $role)
    {
        try {
            if ($role->users()->count() > 0) {
                return redirect()->route('admin.roles.index')
                    ->with('error', 'Role tidak dapat dihapus karena masih digunakan oleh '.$role->users()->count().' user');
            }

            if ($role->is_system) {
                return redirect()->route('admin.roles.index')
                    ->with('error', 'Role sistem tidak dapat dihapus');
            }

            $role->delete();

            return redirect()->route('admin.roles.index')
                ->with('success', 'Role berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus role: '.$e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()->route('admin.roles.index')
                ->with('error', 'Gagal menghapus role: '.$e->getMessage());
        }
    }
}
