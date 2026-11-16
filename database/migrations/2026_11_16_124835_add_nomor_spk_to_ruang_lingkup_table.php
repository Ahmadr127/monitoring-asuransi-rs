<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ruang_lingkup', function (Blueprint $table) {
            $table->string('nomor_spk', 255)->nullable()->after('alias_versi_tera');
        });
    }

    public function down(): void
    {
        Schema::table('ruang_lingkup', function (Blueprint $table) {
            $table->dropColumn('nomor_spk');
        });
    }
};
