<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->dropColumn(['can_reject', 'skip_if']);
        });
    }

    public function down(): void
    {
        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->boolean('can_reject')->default(true)->after('approver_value');
            $table->json('skip_if')->nullable()->after('is_active');
        });
    }
};
