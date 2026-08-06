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
        Schema::create('diskon', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ruang_lingkup_id')->constrained('ruang_lingkup')->onDelete('cascade');
            $table->integer('persentase_diskon');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique('ruang_lingkup_id', 'diskon_ruang_lingkup_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diskon');
    }
};
