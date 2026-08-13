<?php

namespace Database\Seeders;

use App\Models\ContractHistory;
use App\Models\RuangLingkup;
use Illuminate\Database\Seeder;

class ContractHistorySeeder extends Seeder
{
    public function run(): void
    {
        $ruangLingkups = RuangLingkup::all();

        if ($ruangLingkups->isEmpty()) {
            $this->command->warn('⚠️  Tidak ada ruang lingkup. Contract history tidak dibuat.');

            return;
        }

        $totalCreated = 0;

        foreach ($ruangLingkups as $ruangLingkup) {
            $totalCreated += ContractHistory::factory()
                ->count(fake()->numberBetween(1, 3))
                ->create(['ruang_lingkup_id' => $ruangLingkup->id])
                ->count();
        }

        $this->command->info('✅ Contract history seeded: '.$totalCreated.' records');
    }
}
