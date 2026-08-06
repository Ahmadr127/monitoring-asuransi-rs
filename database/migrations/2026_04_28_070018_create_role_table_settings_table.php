<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Pengaturan kolom yang ditampilkan per role per tabel.
     * Relasi: role_id → roles
     */
    public function up(): void
    {
        Schema::create('role_table_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->string('table_name');
            $table->json('visible_columns');
            $table->timestamps();

            $table->unique(['role_id', 'table_name'], 'role_table_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_table_settings');
    }
};
