<?php

namespace App\Services;

use App\Models\OrganizationType;
use App\Models\OrganizationUnit;
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
     * Ambil seluruh departemen/unit dari simutu.
     */
    protected function fetchSimutuUnits(): \Illuminate\Support\Collection
    {
        return DB::connection($this->connection())
            ->table('tbl_unit')
            ->select(
                'tbl_unit.id as simutu_unit_id',
                'tbl_unit.kode_unit',
                'tbl_unit.nama_unit',
                'tbl_unit.deskripsi_unit',
                'tbl_unit.status_unit'
            )
            ->get();
    }

    /**
     * Ambil calon kepala departemen (user simutu dengan role kepala) + unit-nya.
     */
    protected function fetchSimutuHeads(): \Illuminate\Support\Collection
    {
        $headRoleNames = config('simutu_sync.head_role_names', ['Kepala unit', 'Kepala']);

        return DB::connection($this->connection())
            ->table('users')
            ->join('tbl_role', 'users.role_id', '=', 'tbl_role.id')
            ->select(
                'users.id as simutu_user_id',
                'users.unit_id as simutu_unit_id'
            )
            ->whereIn('tbl_role.nama_role', $headRoleNames)
            ->whereNotNull('users.unit_id')
            ->orderBy('users.updated_at', 'desc')
            ->get();
    }

    /**
     * Cache id organization_units lokal per kode unit.
     */
    protected ?array $localUnitIdsByCode = null;

    protected function localUnitIdByCode(?string $code): ?int
    {
        if ($this->localUnitIdsByCode === null) {
            $this->localUnitIdsByCode = OrganizationUnit::pluck('id', 'code')->all();
        }

        return $code ? ($this->localUnitIdsByCode[$code] ?? null) : null;
    }

    /**
     * Sinkronisasi departemen (tbl_unit) ke tabel organization_units lokal.
     *
     * @return array{units_checked:int, units_created:int, units_updated:int}
     */
    public function syncDepartments(bool $dryRun = false): array
    {
        $stats = ['units_checked' => 0, 'units_created' => 0, 'units_updated' => 0];

        if (! $this->enabled()) {
            return $stats;
        }

        $type = OrganizationType::firstOrCreate(
            ['name' => 'departemen'],
            [
                'display_name' => 'Departemen',
                'level' => 3,
                'description' => 'Unit/departemen hasil sinkronisasi dari Simutu.',
            ]
        );

        foreach ($this->fetchSimutuUnits() as $unit) {
            $u = (array) $unit;
            $stats['units_checked']++;

            $data = [
                'name' => $u['nama_unit'],
                'type_id' => $type->id,
                'description' => $u['deskripsi_unit'] ?? null,
                'is_active' => ($u['status_unit'] ?? '') === 'aktif',
            ];

            $existing = OrganizationUnit::where('code', $u['kode_unit'])->first();

            if ($existing) {
                if (! $dryRun) {
                    $existing->update($data);
                }
                $stats['units_updated']++;
            } else {
                if (! $dryRun) {
                    OrganizationUnit::create(array_merge(['code' => $u['kode_unit']], $data));
                }
                $stats['units_created']++;
            }
        }

        return $stats;
    }

    /**
     * Pasang organization_units.head_id dari user simutu ber-role kepala.
     * Jalankan SETELAH sinkronisasi users agar kepala sudah ada di tabel users lokal.
     *
     * @return array{heads_mapped:int}
     */
    public function syncDepartmentHeads(bool $dryRun = false): array
    {
        $stats = ['heads_mapped' => 0];

        if (! $this->enabled()) {
            return $stats;
        }

        $codeBySimutuUnitId = [];
        foreach ($this->fetchSimutuUnits() as $unit) {
            $u = (array) $unit;
            $codeBySimutuUnitId[$u['simutu_unit_id']] = $u['kode_unit'];
        }

        $unitIdsByCode = OrganizationUnit::pluck('id', 'code')->all();

        foreach ($this->fetchSimutuHeads() as $head) {
            $h = (array) $head;

            $code = $codeBySimutuUnitId[$h['simutu_unit_id']] ?? null;
            $unitId = $code ? ($unitIdsByCode[$code] ?? null) : null;

            if (! $unitId) {
                continue;
            }

            $headUser = User::where('simutu_id', $h['simutu_user_id'])->first();
            if (! $headUser) {
                continue;
            }

            $unit = OrganizationUnit::find($unitId);
            if (! $unit || (int) $unit->head_id === (int) $headUser->id) {
                continue;
            }

            $stats['heads_mapped']++;
            if (! $dryRun) {
                $unit->update(['head_id' => $headUser->id]);
            }
        }

        return $stats;
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
                'users.unit_id as simutu_unit_id',
                'tbl_unit.nama_unit',
                'tbl_unit.kode_unit',
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
                    'users.unit_id as simutu_unit_id',
                    'tbl_unit.nama_unit',
                    'tbl_unit.kode_unit',
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
            'nip' => $simutu['nip'] ?? ($local?->nip),
            'email' => ($simutu['email'] ?? null) ?: ($local?->email),
            'simutu_id' => $simutu['simutu_id'] ?? null,
            'simutu_status' => $simutu['status_user'] ?? null,
            'simutu_synced_at' => now(),
        ];

        $unitId = $this->localUnitIdByCode($simutu['kode_unit'] ?? null);
        if ($unitId) {
            $data['organization_unit_id'] = $unitId;
        }

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
     * Jalankan sinkronisasi penuh (departemen + users + kepala departemen).
     *
     * @return array{checked:int, created:int, updated:int,
     *               units_checked:int, units_created:int, units_updated:int,
     *               heads_mapped:int}
     */
    public function sync(bool $dryRun = false): array
    {
        $stats = ['checked' => 0, 'created' => 0, 'updated' => 0];

        if (! $this->enabled()) {
            return $this->emptyStats();
        }

        // 1) Departemen dulu agar FK organization_unit_id valid saat user disinkron.
        $stats = array_merge($stats, $this->syncDepartments($dryRun));
        $this->localUnitIdsByCode = null;

        // 2) Users (sekaligus relasi organization_unit_id).
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

        // 3) Kepala departemen (butuh users lokal sudah tersinkron).
        $stats = array_merge($stats, $this->syncDepartmentHeads($dryRun));

        return $stats;
    }

    protected function emptyStats(): array
    {
        return [
            'checked' => 0,
            'created' => 0,
            'updated' => 0,
            'units_checked' => 0,
            'units_created' => 0,
            'units_updated' => 0,
            'heads_mapped' => 0,
        ];
    }
}