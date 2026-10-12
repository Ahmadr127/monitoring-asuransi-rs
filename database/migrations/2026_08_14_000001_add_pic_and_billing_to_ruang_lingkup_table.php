<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ruang_lingkup', function (Blueprint $table) {
            $table->string('pic_nama')->nullable()->after('alias_versi_tera');
            $table->string('pic_kontak')->nullable()->after('pic_nama');
            $table->string('pic_portal')->nullable()->after('pic_kontak');
            $table->text('alamat_penagihan')->nullable()->after('pic_portal');
        });
    }

    public function down(): void
    {
        Schema::table('ruang_lingkup', function (Blueprint $table) {
            $table->dropColumn(['pic_nama', 'pic_kontak', 'pic_portal', 'alamat_penagihan']);
        });
    }
};
