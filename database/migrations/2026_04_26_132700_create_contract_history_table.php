<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Riwayat perubahan kontrak (rollover, perubahan status, dll).
     * Relasi: ruang_lingkup_id → ruang_lingkup
     */
    public function up(): void
    {
        Schema::create('contract_history', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ruang_lingkup_id')
                ->constrained('ruang_lingkup')
                ->onDelete('cascade');

            // Jenis aksi: rollover, status_change, dll
            $table->string('action_type')->default('rollover')->index();

            // Tanggal berakhir sebelum dan sesudah perubahan
            $table->date('old_end_date')->nullable();
            $table->date('new_end_date')->nullable();

            // Status sebelum dan sesudah perubahan
            $table->string('old_status')->nullable();
            $table->string('new_status')->nullable();

            // Catatan tambahan
            $table->text('notes')->nullable();

            // Data tambahan (misal: old_start_date, new_start_date, dll)
            $table->json('metadata')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_history');
    }
};
