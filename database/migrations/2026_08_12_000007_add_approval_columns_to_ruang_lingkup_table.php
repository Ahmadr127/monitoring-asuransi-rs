<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ruang_lingkup', function (Blueprint $table) {
            // draft | in_review | approved | rejected
            $table->string('approval_status')->default('approved')->after('status')->index();
            $table->foreignId('submitted_by')->nullable()->after('approval_status')
                ->constrained('users')->nullOnDelete();
            $table->foreignId('current_approval_request_id')->nullable()->after('submitted_by')
                ->constrained('approval_requests')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ruang_lingkup', function (Blueprint $table) {
            $table->dropForeign(['submitted_by']);
            $table->dropForeign(['current_approval_request_id']);
            $table->dropColumn(['approval_status', 'submitted_by', 'current_approval_request_id']);
        });
    }
};