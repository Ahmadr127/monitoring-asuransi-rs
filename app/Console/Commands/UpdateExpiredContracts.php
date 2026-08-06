<?php

namespace App\Console\Commands;

use App\Models\RuangLingkup;
use Illuminate\Console\Command;

class UpdateExpiredContracts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contracts:update-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update status kerjasama yang sudah expired menjadi tidak aktif';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai update status kerjasama yang expired...');

        // Ambil semua kerjasama yang sudah expired tapi masih aktif
        $expiredContracts = RuangLingkup::expiredButActive()->get();

        $updatedCount = 0;

        foreach ($expiredContracts as $contract) {
            if ($contract->updateExpiredStatus()) {
                $updatedCount++;
                $this->line("✓ {$contract->nama_asuransi} - Status diubah menjadi tidak aktif");
            }
        }

        if ($updatedCount > 0) {
            $this->info("Berhasil mengupdate {$updatedCount} kerjasama yang expired.");
        } else {
            $this->info('Tidak ada kerjasama yang perlu diupdate.');
        }

        return 0;
    }
}
