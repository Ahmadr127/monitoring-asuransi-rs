<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReminderRecipient;
use App\Models\Role;
use App\Models\User;
use App\Services\SimutuUserSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('role')->get();
        $roles = Role::all();

        return view('admin.master.users', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:255', 'unique:users'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', Rules\Password::defaults()],
                'role_id' => ['required', 'exists:roles,id'],
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role_id' => $validated['role_id'],
            ]);

            $role = Role::find($validated['role_id']);
            if ($role && $role->key === 'legal') {
                ReminderRecipient::firstOrCreate(
                    ['email' => $user->email],
                    [
                        'name' => $user->name,
                        'category' => 'internal',
                        'purpose' => 'legal',
                        'is_active' => true,
                    ]
                );
            }

            return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan!');
        } catch (\Exception $e) {
            Log::error('Gagal menambahkan user: '.$e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()->route('admin.users.index')
                ->with('error', 'Gagal menambahkan user: '.$e->getMessage());
        }
    }

    public function update(Request $request, User $user)
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:255', 'unique:users,username,'.$user->id],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
                'password' => ['nullable', Rules\Password::defaults()],
                'role_id' => ['required', 'exists:roles,id'],
            ]);

            $user->update([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'role_id' => $validated['role_id'],
            ]);

            if ($request->filled('password')) {
                $user->update(['password' => Hash::make($validated['password'])]);
            }

            return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui!');
        } catch (\Exception $e) {
            Log::error('Gagal mengupdate user: '.$e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()->route('admin.users.index')
                ->with('error', 'Gagal mengupdate user: '.$e->getMessage());
        }
    }

    public function destroy(User $user)
    {
        try {
            if ($user->id === auth()->id()) {
                return redirect()->route('admin.users.index')->with('error', 'Tidak dapat menghapus akun sendiri!');
            }

            $user->delete();

            return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus user: '.$e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()->route('admin.users.index')
                ->with('error', 'Gagal menghapus user: '.$e->getMessage());
        }
    }

    public function syncFromSimutu()
    {
        try {
            $stats = app(SimutuUserSyncService::class)->sync();

            return redirect()->route('admin.users.index')
                ->with('success', "Simutu sync selesai: {$stats['checked']} dicek, {$stats['created']} dibuat, {$stats['updated']} diperbarui.");
        } catch (\Exception $e) {
            Log::error('Gagal sync dari Simutu: '.$e->getMessage());

            return redirect()->route('admin.users.index')
                ->with('error', 'Gagal sync dari Simutu: '.$e->getMessage());
        }
    }
}
