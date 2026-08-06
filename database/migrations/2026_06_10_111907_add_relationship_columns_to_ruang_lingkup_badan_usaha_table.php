<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ruang_lingkup_badan_usaha', function (Blueprint $table) {
            $table->foreignId('ruang_lingkup_id')
                ->nullable()
                ->after('id')
                ->constrained('ruang_lingkup')
                ->onDelete('cascade');

            $table->foreignId('badan_usaha_id')
                ->nullable()
                ->after('ruang_lingkup_id')
                ->constrained('master_badan_usaha')
                ->onDelete('cascade');

            $table->json('benefit')->nullable()->after('badan_usaha_id');
            $table->json('cakupan')->nullable()->after('benefit');
        });
    }

    public function down(): void
    {
        Schema::table('ruang_lingkup_badan_usaha', function (Blueprint $table) {
            $table->dropForeign(['ruang_lingkup_id']);
            $table->dropForeign(['badan_usaha_id']);
            $table->dropColumn(['ruang_lingkup_id', 'badan_usaha_id', 'benefit', 'cakupan']);
        });
    }
};
