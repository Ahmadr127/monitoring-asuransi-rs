<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\RoleTableSetting;
use Illuminate\Http\Request;

class TableSettingController extends Controller
{
    // Definisi kolom yang tersedia untuk setiap tabel
    private $availableColumns = [
        'ruang_lingkup' => [
            'alias_versi_tera' => 'Alias / Versi Tera',
            'penjamin_category' => 'TPA / PAYOR',
            'is_high_profile' => 'High Profile',
            'badan_usaha' => 'Badan Usaha',
            'periode' => 'Periode',
            'jangka_waktu' => 'Jangka Waktu',
            'diskon' => 'Diskon',
            'jenis_layanan' => 'Jenis Layanan',
            'benefit' => 'Benefit',
            'cakupan' => 'Cakupan',
            'obat' => 'Obat',
            'status' => 'Status',
            'contract_history' => 'Riwayat Kontrak',
        ],
    ];

    public function index()
    {
        $roles = Role::all();
        $settings = RoleTableSetting::with('role')->get()->groupBy('role_id');

        return view('admin.settings.table-settings', [
            'roles' => $roles,
            'settings' => $settings,
            'availableColumns' => $this->availableColumns,
        ]);
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'role_id' => 'required|exists:roles,id',
                'table_name' => 'required|in:'.implode(',', array_keys($this->availableColumns)),
                'visible_columns' => 'required|array|min:1',
                'visible_columns.*' => 'required|in:'.implode(',', array_keys($this->availableColumns[$request->table_name] ?? [])),
            ], [
                'visible_columns.required' => 'Kolom yang tampil wajib diisi.',
                'visible_columns.min' => 'Minimal 1 kolom harus dipilih.',
                'visible_columns.*.required' => 'Kolom yang dipilih tidak valid.',
                'visible_columns.*.in' => 'Kolom :input tidak dikenali.',
            ]);

            // Check if table exists
            if (! \Schema::hasTable('role_table_settings')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tabel pengaturan tidak ditemukan. Silakan hubungi administrator.',
                ], 500);
            }

            $setting = RoleTableSetting::updateOrCreate(
                [
                    'role_id' => $request->role_id,
                    'table_name' => $request->table_name,
                ],
                [
                    'visible_columns' => $request->visible_columns,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Pengaturan kolom berhasil disimpan.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation Error:', $e->errors());

            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid: '.implode(', ', \Illuminate\Support\Arr::flatten($e->errors())),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Table Settings Update Error: '.$e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan pengaturan: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getSettings(Request $request)
    {
        $roleId = $request->get('role_id');
        $tableName = $request->get('table_name');

        $setting = RoleTableSetting::where('role_id', $roleId)
            ->where('table_name', $tableName)
            ->first();

        return response()->json([
            'visible_columns' => $setting ? $setting->visible_columns : [],
        ]);
    }
}
