<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('Starting database seeding...');
        $this->command->newLine();

        // 1. Seed Master Data (Independent tables)
        $this->command->info('Seeding Master Data...');
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            BadanUsahaSeeder::class,
            JenisLayananSeeder::class,
            ObatSeeder::class,
        ]);
        $this->command->newLine();

        // 2. Seed Ruang Lingkup (Depends on master data)
        $this->command->info('Seeding Ruang Lingkup...');
        $this->call([
            RuangLingkupSeeder::class,
        ]);
        $this->command->newLine();

        // 3. Seed Related Data (Depends on Ruang Lingkup)
        $this->command->info('Seeding Related Data...');
        $this->call([
            DiskonSeeder::class,
            PenagihanPembayaranSeeder::class,
        ]);
        $this->command->newLine();

        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->newLine();

        // Summary Table-like output
        $this->command->info('┌──────────────────────────────────────────────────────────┐');
        $this->command->info('│                  SEEDING SUMMARY REPORT                  │');
        $this->command->info('├──────────────────────────────────────────────────────────┤');
        $this->command->info('│  - Roles & Users Management      : [ SUCCESS ]           │');
        $this->command->info('│  - Master Data (BU, Services)    : [ SUCCESS ]           │');
        $this->command->info('│  - Insurance Scope (Master RL)   : [ SUCCESS ]           │');
        $this->command->info('│  - Financial (Discounts & Terms) : [ SUCCESS ]           │');
        $this->command->info('└──────────────────────────────────────────────────────────┘');
        $this->command->newLine();
    }
}
