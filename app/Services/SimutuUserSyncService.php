<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Menarik data pegawai dari database Simutu (master data) ke tabel users lokal.
 *
 * Prinsip non-destruktif:
 *  - Tidak pernah menghapus user lokal (aman untuk FK transport_requests.user_id dll).
 *  - role_id lokal TIDAK ditimpa untuk user lama; hanya diisi saat user baru.
 *  - Password disalin dari simutu agar satu akun satu password (hash bcrypt kompatibel).
 */
class SimutuUserSyncService
{
    public function enabled(): bool
    {
        return (bool) config('simutu_sync.enabled', true);
    }

    protected function connection(): string
    {
        return (string) config('simutu_sync.db_connection', 'simutu');
    }

    /**
     * Ambil seluruh pegawai aktif/pending dari simutu (dengan nama unit & role).
     */
    protected function fetchSimutuUsers(): \Illuminate\Support\Collection
    {
        return DB::connection($this->connection())
            ->table('users')
            ->leftJoin('tbl_unit', 'users.unit_id', '=', 'tbl_unit.id')
            ->leftJoin('tbl_role', 'users.role_id', '=', 'tbl_role.id')
            ->select(
                'users.id as simutu_id',
                'users.nama_lengkap',
                'users.nip',
                'users.username',
                'users.email',
                'users.password',
                'users.profesi',
                'users.status_user',
                'users.updated_at',
                'tbl_unit.nama_unit',
                'tbl_role.nama_role'
            )
            ->get();
    }

    /**
     * Sync untuk satu username. Jika akun belum ada lokal, buat otomatis.
     */
    public function syncForUsername(?string $username): ?User
    {
        if (! $this->enabled() || ! $username) {
            return null;
        }

        try {
            $simutu = DB::connection($this->connection())
                ->table('users')
                ->leftJoin('tbl_unit', 'users.unit_id', '=', 'tbl_unit.id')
                ->leftJoin('tbl_role', 'users.role_id', '=', 'tbl_role.id')
                ->select(
                    'users.id as simutu_id',
                    'users.nama_lengkap',
                    'users.nip',
                    'users.username',
                    'users.email',
                    'users.password',
                    'users.profesi',
                    'users.status_user',
                    'tbl_unit.nama_unit',
                    'tbl_role.nama_role'
                )
                ->where('users.username', $username)
                ->first();
        } catch (\Throwable $e) {
            Log::warning('Simutu syncForUsername gagal: '.$e->getMessage());
            return null;
        }

        if (! $simutu) {
            return null;
        }

        $local = $this->findLocalUser((array) $simutu);
        return $this->applyToLocal((array) $simutu, $local, createIfMissing: true);
    }

    protected function findLocalUser(array $simutu): ?User
    {
        if (! empty($simutu['simutu_id'])) {
            $bySimutuId = User::where('simutu_id', $simutu['simutu_id'])->first();
            if ($bySimutuId) {
                return $bySimutuId;
            }
        }

        if (! empty($simutu['username'])) {
            $byUsername = User::where('username', $simutu['username'])->first();
            if ($byUsername) {
                return $byUsername;
            }
        }

        if (! empty($simutu['email'])) {
            $byEmail = User::where('email', $simutu['email'])->first();
            if ($byEmail) {
                return $byEmail;
            }
        }

        return null;
    }

    protected function resolveRoleId(array $simutu, ?User $local): ?int
    {
        if ($local && $local->role_id) {
            return $local->role_id; // jangan timpa role lokal yang sudah diatur admin
        }

        // 1) Cocok via nama role simutu
        foreach (config('simutu_sync.role_key_map', []) as $needle => $key) {
            if (str_contains(strtoupper($simutu['nama_role'] ?? ''), $needle)) {
                $role = Role::where('key', $key)->first();
                if ($role) {
                    return $role->id;
                }
            }
        }

        // 2) Cocok via unit simutu
        $unit = strtoupper($simutu['nama_unit'] ?? '');
        foreach (config('simutu_sync.unit_roles', []) as $needle => $key) {
            if (str_contains($unit, $needle)) {
                $role = Role::where('key', $key)->first();
                if ($role) {
                    return $role->id;
                }
            }
        }

        // 3) Default role untuk user yang belum punya role (baru atau role_id null).
        if (! $local || ! $local->role_id) {
            $defaultKey = config('simutu_sync.default_role');
            if ($defaultKey) {
                $role = Role::where('key', $defaultKey)->first();
                if ($role) {
                    return $role->id;
                }
            }
        }

        return $local?->role_id ?? null;
    }

    protected function applyToLocal(array $simutu, ?User $local, bool $createIfMissing): ?User
    {
        $data = [
            'name' => $simutu['nama_lengkap'] ?? ($local?->name),
            'username' => $simutu['username'] ?? ($local?->username),
            'email' => ($simutu['email'] ?? null) ?: ($local?->email),
            'simutu_id' => $simutu['simutu_id'] ?? null,
            'simutu_status' => $simutu['status_user'] ?? null,
            'simutu_synced_at' => now(),
        ];

        if (config('simutu_sync.sync_password', true) && ! empty($simutu['password'])) {
            $data['password'] = $simutu['password'];
        }

        $roleId = $this->resolveRoleId($simutu, $local);
        if ($roleId) {
            $data['role_id'] = $roleId;
        }

        if ($local) {
            $local->update($data);
            return $local;
        }

        if (! $createIfMissing) {
            return null;
        }

        if (User::where('username', $data['username'])->exists()) {
            return null;
        }

        return User::create($data);
    }

    /**
     * Jalankan sinkronisasi penuh.
     *
     * @return array{created:int, updated:int, checked:int}
     */
    public function sync(bool $dryRun = false): array
    {
        $stats = ['checked' => 0, 'created' => 0, 'updated' => 0];

        if (! $this->enabled()) {
            return $stats;
        }

        foreach ($this->fetchSimutuUsers() as $simutu) {
            $stats['checked']++;
            $simutuArr = (array) $simutu;
            $local = $this->findLocalUser($simutuArr);

            if (! $local) {
                if ($dryRun) {
                    $stats['created']++;
                } else {
                    $user = $this->applyToLocal($simutuArr, null, createIfMissing: true);
                    if ($user) {
                        $stats['created']++;
                    }
                }
                continue;
            }

            $fresh = $this->applyToLocal($simutuArr, $local, createIfMissing: false);
            if ($fresh && $fresh->simutu_synced_at) {
                $stats['updated']++;
            }
        }

        return $stats;
    }
}