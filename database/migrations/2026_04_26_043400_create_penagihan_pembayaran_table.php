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
        Schema::create('penagihan_pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ruang_lingkup_id')->constrained('ruang_lingkup')->onDelete('cascade');
            $table->integer('jangka_waktu_hari');
            $table->string('tipe_hari')->default('Kalender');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique('ruang_lingkup_id', 'penagihan_ruang_lingkup_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penagihan_pembayaran');
    }
};
