<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE reminder_recipients DROP CONSTRAINT IF EXISTS reminder_recipients_category_check');
            DB::statement("ALTER TABLE reminder_recipients ADD CONSTRAINT reminder_recipients_category_check CHECK (category IN ('internal', 'manual'))");
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE reminder_recipients MODIFY COLUMN category ENUM('internal', 'manual') DEFAULT 'internal'");
        } elseif ($driver === 'sqlite') {
            DB::statement('CREATE TABLE reminder_recipients_new (id INTEGER PRIMARY KEY AUTOINCREMENT, email VARCHAR(255) NOT NULL, name VARCHAR(255), category VARCHAR(255) DEFAULT \'internal\' CHECK (category IN (\'internal\', \'manual\')), insurance_name VARCHAR(255), is_active TINYINT(1) DEFAULT 1, last_manual_sent_at DATETIME, created_at DATETIME, updated_at DATETIME)');
            DB::statement('INSERT INTO reminder_recipients_new SELECT id, email, name, CASE WHEN category = \'asuransi\' THEN \'manual\' ELSE category END, insurance_name, is_active, last_manual_sent_at, created_at, updated_at FROM reminder_recipients');
            DB::statement('DROP TABLE reminder_recipients');
            DB::statement('ALTER TABLE reminder_recipients_new RENAME TO reminder_recipients');
            DB::statement('CREATE INDEX reminder_recipients_insurance_name_index ON reminder_recipients (insurance_name)');
            DB::statement('CREATE INDEX reminder_recipients_category_is_active_index ON reminder_recipients (category, is_active)');
        } else {
            DB::table('reminder_recipients')->where('category', 'asuransi')->update(['category' => 'manual']);

            return;
        }

        DB::table('reminder_recipients')->where('category', 'asuransi')->update(['category' => 'manual']);
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('CREATE TABLE reminder_recipients_old (id INTEGER PRIMARY KEY AUTOINCREMENT, email VARCHAR(255) NOT NULL, name VARCHAR(255), category VARCHAR(255) DEFAULT \'internal\' CHECK (category IN (\'internal\', \'asuransi\')), insurance_name VARCHAR(255), is_active TINYINT(1) DEFAULT 1, last_manual_sent_at DATETIME, created_at DATETIME, updated_at DATETIME)');
            DB::statement('INSERT INTO reminder_recipients_old SELECT id, email, name, CASE WHEN category = \'manual\' THEN \'asuransi\' ELSE category END, insurance_name, is_active, last_manual_sent_at, created_at, updated_at FROM reminder_recipients');
            DB::statement('DROP TABLE reminder_recipients');
            DB::statement('ALTER TABLE reminder_recipients_old RENAME TO reminder_recipients');
            DB::statement('CREATE UNIQUE INDEX reminder_recipients_email_unique ON reminder_recipients (email)');
            DB::statement('CREATE INDEX reminder_recipients_insurance_name_index ON reminder_recipients (insurance_name)');
            DB::statement('CREATE INDEX reminder_recipients_category_is_active_index ON reminder_recipients (category, is_active)');

            return;
        }

        DB::table('reminder_recipients')->where('category', 'manual')->update(['category' => 'asuransi']);

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE reminder_recipients DROP CONSTRAINT IF EXISTS reminder_recipients_category_check');
            DB::statement("ALTER TABLE reminder_recipients ADD CONSTRAINT reminder_recipients_category_check CHECK (category IN ('internal', 'asuransi'))");
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE reminder_recipients MODIFY COLUMN category ENUM('internal', 'asuransi') DEFAULT 'internal'");
        }
    }
};
