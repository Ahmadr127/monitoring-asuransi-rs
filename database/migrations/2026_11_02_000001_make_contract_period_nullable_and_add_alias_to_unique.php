<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ruang_lingkup', function (Blueprint $table) {
            $table->dropUnique('ruang_lingkup_unique');
        });

        Schema::table('ruang_lingkup', function (Blueprint $table) {
            $table->date('tanggal_mulai')->nullable()->change();
            $table->date('tanggal_berakhir')->nullable()->change();
        });

        Schema::table('ruang_lingkup', function (Blueprint $table) {
            // Alias ikut menjadi identitas kontrak agar beberapa kontrak dengan
            // nama + kategori + periode yang sama tetap bisa dibedakan.
            $table->unique([
                'nama_asuransi',
                'penjamin_category',
                'alias_versi_tera',
                'tanggal_mulai',
                'tanggal_berakhir',
            ], 'ruang_lingkup_unique');
        });
    }

    public function down(): void
    {
        Schema::table('ruang_lingkup', function (Blueprint $table) {
            $table->dropUnique('ruang_lingkup_unique');
        });

        // Isi tanggal yang NULL agar kolom bisa dikembalikan menjadi NOT NULL.
        DB::table('ruang_lingkup')
            ->whereNull('tanggal_mulai')
            ->update(['tanggal_mulai' => now()->toDateString()]);

        DB::table('ruang_lingkup')
            ->whereNull('tanggal_berakhir')
            ->update(['tanggal_berakhir' => now()->toDateString()]);

        Schema::table('ruang_lingkup', function (Blueprint $table) {
            $table->date('tanggal_mulai')->nullable(false)->change();
            $table->date('tanggal_berakhir')->nullable(false)->change();
        });

        Schema::table('ruang_lingkup', function (Blueprint $table) {
            $table->unique([
                'nama_asuransi',
                'penjamin_category',
                'tanggal_mulai',
                'tanggal_berakhir',
            ], 'ruang_lingkup_unique');
        });
    }
};
