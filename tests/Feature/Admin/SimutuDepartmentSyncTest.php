<?php

namespace Tests\Feature\Admin;

use App\Models\OrganizationUnit;
use App\Models\User;
use App\Services\SimutuUserSyncService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SimutuDepartmentSyncTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['simutu_sync.enabled' => true]);

        // Koneksi simutu palsu: DB test yang sama, tapi dengan prefix 'simutu_'
        // agar tabel users/tbl_unit/tbl_role simutu tidak bentrok dengan tabel lokal.
        $base = config('database.connections.'.config('database.default'));
        $base['prefix'] = 'simutu_';
        config(['database.connections.simutu_test' => $base]);
        config(['simutu_sync.db_connection' => 'simutu_test']);

        $this->createSimutuTables();
    }

    protected function createSimutuTables(): void
    {
        $schema = DB::connection('simutu_test')->getSchemaBuilder();

        $schema->dropIfExists('users');
        $schema->dropIfExists('tbl_unit');
        $schema->dropIfExists('tbl_role');

        $schema->create('tbl_role', function ($t) {
            $t->increments('id');
            $t->string('nama_role');
        });

        $schema->create('tbl_unit', function ($t) {
            $t->increments('id');
            $t->string('kode_unit');
            $t->string('nama_unit');
            $t->text('deskripsi_unit')->nullable();
            $t->string('status_unit');
            $t->timestamps();
        });

        $schema->create('users', function ($t) {
            $t->increments('id');
            $t->string('nama_lengkap');
            $t->string('nip')->nullable();
            $t->string('username');
            $t->string('email');
            $t->string('password');
            $t->integer('role_id')->nullable();
            $t->integer('unit_id')->nullable();
            $t->string('profesi')->nullable();
            $t->string('atasan_langsung')->nullable();
            $t->string('status_user');
            $t->timestamps();
        });
    }

    protected function seedSimutu(): void
    {
        DB::connection('simutu_test')->table('tbl_role')->insert([
            ['id' => 1, 'nama_role' => 'Administrator'],
            ['id' => 8, 'nama_role' => 'Kepala unit'],
            ['id' => 4, 'nama_role' => 'Staff'],
        ]);

        DB::connection('simutu_test')->table('tbl_unit')->insert([
            [
                'id' => 1,
                'kode_unit' => 'UNIT001',
                'nama_unit' => 'Administrator',
                'deskripsi_unit' => 'Unit pusat',
                'status_unit' => 'aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'kode_unit' => 'UNIT036',
                'nama_unit' => 'SDM',
                'deskripsi_unit' => 'Sumber daya manusia',
                'status_unit' => 'aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::connection('simutu_test')->table('users')->insert([
            [
                'id' => 100,
                'nama_lengkap' => 'INDRA THALIB, BSN., MM',
                'nip' => '123',
                'username' => 'indra',
                'email' => 'indra@test.test',
                'password' => 'secret',
                'role_id' => 8,
                'unit_id' => 2,
                'profesi' => null,
                'atasan_langsung' => 'MANAGER',
                'status_user' => 'aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 101,
                'nama_lengkap' => 'Budi Anggota',
                'nip' => '124',
                'username' => 'budi',
                'email' => 'budi@test.test',
                'password' => 'secret',
                'role_id' => 4,
                'unit_id' => 2,
                'profesi' => null,
                'atasan_langsung' => 'MANAGER',
                'status_user' => 'aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function test_sync_creates_departments_users_and_maps_heads(): void
    {
        $this->seedSimutu();

        $stats = app(SimutuUserSyncService::class)->sync();

        $this->assertSame(2, $stats['units_created']);
        $this->assertSame(2, $stats['checked']);
        $this->assertSame(2, $stats['created']);
        $this->assertSame(1, $stats['heads_mapped']);

        $sdm = OrganizationUnit::where('code', 'UNIT036')->first();
        $this->assertNotNull($sdm);
        $this->assertSame('SDM', $sdm->name);
        $this->assertTrue($sdm->is_active);
        $this->assertNotNull($sdm->head_id);

        $indra = User::where('username', 'indra')->first();
        $this->assertNotNull($indra);
        $this->assertSame($sdm->id, $indra->organization_unit_id);
        $this->assertSame($indra->id, $sdm->head_id);

        $budi = User::where('username', 'budi')->first();
        $this->assertNotNull($budi);
        $this->assertSame($sdm->id, $budi->organization_unit_id);
    }

    public function test_resync_updates_instead_of_duplicating(): void
    {
        $this->seedSimutu();

        $service = app(SimutuUserSyncService::class);
        $service->sync();
        $stats = $service->sync();

        $this->assertSame(2, $stats['units_updated']);
        $this->assertSame(0, $stats['units_created']);
        $this->assertSame(2, $stats['updated']);
        $this->assertSame(0, $stats['heads_mapped']);
        $this->assertSame(2, OrganizationUnit::count());
        $this->assertSame(2, User::whereNotNull('simutu_id')->count());
    }

    public function test_dry_run_does_not_write(): void
    {
        $this->seedSimutu();

        $stats = app(SimutuUserSyncService::class)->sync(dryRun: true);

        $this->assertSame(2, $stats['units_created']);
        $this->assertSame(0, OrganizationUnit::count());
        $this->assertSame(0, User::whereNotNull('simutu_id')->count());
    }
}
