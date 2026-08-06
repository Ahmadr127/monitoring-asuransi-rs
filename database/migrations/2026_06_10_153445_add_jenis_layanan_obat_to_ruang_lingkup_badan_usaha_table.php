<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ruang_lingkup_badan_usaha', function (Blueprint $table) {
            $table->json('jenis_layanan_ids')->nullable()->after('cakupan');
            $table->json('obat_ids')->nullable()->after('jenis_layanan_ids');
        });
    }

    public function down(): void
    {
        Schema::table('ruang_lingkup_badan_usaha', function (Blueprint $table) {
            $table->dropColumn(['jenis_layanan_ids', 'obat_ids']);
        });
    }
};
