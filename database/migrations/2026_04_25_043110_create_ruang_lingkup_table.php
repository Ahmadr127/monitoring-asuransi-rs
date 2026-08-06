<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel utama kerjasama asuransi (ruang lingkup).
     * Relasi:
     *   - payor_id  → master_payor
     *   - tpa_id    → master_tpa
     *   - badan_usaha_id → master_badan_usaha
     *   - jenis layanan  → pivot: kerjasama_layanan
     *   - obat           → pivot: kerjasama_obat
     */
    public function up(): void
    {
        Schema::create('ruang_lingkup', function (Blueprint $table) {
            $table->id();
            $table->string('nama_asuransi');

            // Tandai apakah kerjasama ini termasuk high profile
            $table->boolean('is_high_profile')->default(false);

            // Kategori Penjamin (TPA, PAYOR, TPA/PAYOR)
            $table->string('penjamin_category')->nullable();

            // Alias / versi tera untuk identifikasi internal
            $table->string('alias_versi_tera')->nullable();

            // Badan usaha yang terlibat
            $table->foreignId('badan_usaha_id')
                ->nullable()
                ->constrained('master_badan_usaha')
                ->onDelete('set null');

            // JSON columns (legacy / backup — data utama ada di pivot)
            $table->json('jenis_layanan')->nullable();
            $table->json('obat')->nullable();

            // Data kerjasama
            $table->json('benefit')->nullable();
            $table->json('cakupan')->nullable();
            $table->text('deskripsi')->nullable();

            // Periode kontrak
            $table->date('tanggal_mulai');
            $table->date('tanggal_berakhir');

            $table->enum('status', ['aktif', 'tidak_aktif'])->default('aktif');
            $table->timestamps();

            $table->index(['status', 'tanggal_berakhir']);
            $table->index(['status', 'created_at']);
            $table->index('tanggal_berakhir');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ruang_lingkup');
    }
};
