<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom pelacakan data yang bersumber dari database Simutu.
     * Additive saja: tidak mengubah kolom/alur user yang sudah ada.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('simutu_id')->nullable()->after('id');
            $table->string('simutu_status')->nullable()->after('simutu_id');
            $table->timestamp('simutu_synced_at')->nullable()->after('simutu_status');
            $table->index('simutu_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['simutu_id']);
            $table->dropColumn(['simutu_id', 'simutu_status', 'simutu_synced_at']);
        });
    }
};