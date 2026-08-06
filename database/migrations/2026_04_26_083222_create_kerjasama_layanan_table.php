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
        Schema::create('kerjasama_layanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ruang_lingkup_id')->constrained('ruang_lingkup')->onDelete('cascade');
            $table->foreignId('jenis_layanan_id')->constrained('master_jenis_layanan')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kerjasama_layanan');
    }
};
