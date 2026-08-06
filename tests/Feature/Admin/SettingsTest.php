<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_table_settings_index()
    {
        $this->loginAsAdmin();

        $response = $this->get(route('admin.table-settings.index'));

        $response->assertStatus(200);
    }

    public function test_table_settings_get()
    {
        $this->loginAsAdmin();

        $response = $this->get(route('admin.table-settings.get'));

        $response->assertStatus(200);
    }

    public function test_table_settings_update()
    {
        $user = $this->loginAsAdmin();

        $response = $this->post(route('admin.table-settings.update'), [
            'role_id' => $user->role_id,
            'table_name' => 'ruang_lingkup',
            'visible_columns' => ['periode', 'status'],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('role_table_settings', [
            'role_id' => $user->role_id,
            'table_name' => 'ruang_lingkup',
        ]);
    }
}
