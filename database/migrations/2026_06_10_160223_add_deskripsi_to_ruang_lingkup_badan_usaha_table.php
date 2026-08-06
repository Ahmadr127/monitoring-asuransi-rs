<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ruang_lingkup_badan_usaha', function (Blueprint $table) {
            $table->text('deskripsi')->nullable()->after('obat_ids');
        });
    }

    public function down(): void
    {
        Schema::table('ruang_lingkup_badan_usaha', function (Blueprint $table) {
            $table->dropColumn('deskripsi');
        });
    }
};
