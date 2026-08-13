<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->boolean('requires_attachment')->default(false)->after('approver_value');
        });

        Schema::table('approval_steps', function (Blueprint $table) {
            $table->boolean('requires_attachment')->default(false)->after('approver_value');
            $table->string('attachment_path')->nullable()->after('note');
            $table->string('attachment_original_name')->nullable()->after('attachment_path');
        });
    }

    public function down(): void
    {
        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->dropColumn('requires_attachment');
        });

        Schema::table('approval_steps', function (Blueprint $table) {
            $table->dropColumn(['requires_attachment', 'attachment_path', 'attachment_original_name']);
        });
    }
};