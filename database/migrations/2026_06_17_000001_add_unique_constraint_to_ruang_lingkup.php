<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ruang_lingkup', function (Blueprint $table) {
            $table->unique(['nama_asuransi', 'penjamin_category', 'tanggal_mulai', 'tanggal_berakhir'], 'ruang_lingkup_unique');
        });
    }

    public function down(): void
    {
        Schema::table('ruang_lingkup', function (Blueprint $table) {
            $table->dropUnique('ruang_lingkup_unique');
        });
    }
};
