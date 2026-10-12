<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrganizationUnit;
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
        $users = User::with(['role', 'organizationUnit'])->orderBy('name')->get();
        $roles = Role::all();
        $departments = OrganizationUnit::orderBy('name')->get();

        return view('admin.master.users', compact('users', 'roles', 'departments'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:255', 'unique:users'],
                'nip' => ['nullable', 'string', 'max:50'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'no_telpon' => ['nullable', 'string', 'max:30'],
                'password' => ['required', Rules\Password::defaults()],
                'role_id' => ['required', 'exists:roles,id'],
                'organization_unit_id' => ['nullable', 'exists:organization_units,id'],
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'nip' => $validated['nip'] ?? null,
                'email' => $validated['email'],
                'no_telpon' => $validated['no_telpon'] ?? null,
                'password' => Hash::make($validated['password']),
                'role_id' => $validated['role_id'],
                'organization_unit_id' => $validated['organization_unit_id'] ?? null,
            ]);

            $role = Role::find($validated['role_id']);
            if ($role && $role->hasPermission('approval')) {
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
                'nip' => ['nullable', 'string', 'max:50'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
                'no_telpon' => ['nullable', 'string', 'max:30'],
                'password' => ['nullable', Rules\Password::defaults()],
                'role_id' => ['required', 'exists:roles,id'],
                'organization_unit_id' => ['nullable', 'exists:organization_units,id'],
            ]);

            $user->update([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'nip' => $validated['nip'] ?? null,
                'email' => $validated['email'],
                'no_telpon' => $validated['no_telpon'] ?? null,
                'role_id' => $validated['role_id'],
                'organization_unit_id' => $validated['organization_unit_id'] ?? null,
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
                ->with('success', sprintf(
                    'Simutu sync selesai: %d user dicek, %d dibuat, %d diperbarui; %d departemen dicek, %d dibuat, %d diperbarui, %d kepala dihubungkan.',
                    $stats['checked'],
                    $stats['created'],
                    $stats['updated'],
                    $stats['units_checked'],
                    $stats['units_created'],
                    $stats['units_updated'],
                    $stats['heads_mapped']
                ));
        } catch (\Exception $e) {
            Log::error('Gagal sync dari Simutu: '.$e->getMessage());

            return redirect()->route('admin.users.index')
                ->with('error', 'Gagal sync dari Simutu: '.$e->getMessage());
        }
    }
}
