<?php

namespace App\Console\Commands;

use App\Services\SimutuUserSyncService;
use Illuminate\Console\Command;

class SyncSimutuUsers extends Command
{
    protected $signature = 'simutu:sync-users {--dry-run : Tampilkan perkiraan tanpa menulis ke database}';

    protected $description = 'Menarik data pegawai dari database Simutu ke tabel users lokal (tanpa menghapus data lokal).';

    public function handle(SimutuUserSyncService $service): int
    {
        if (! $service->enabled()) {
            $this->warn('SIMUTU_SYNC_ENABLED=false. Sync dilewati.');
            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');

        try {
            $stats = $service->sync($dryRun);
        } catch (\Throwable $e) {
            $this->error('Sync gagal: '.$e->getMessage());
            return self::FAILURE;
        }

        $this->info(
            sprintf(
                'Simutu sync selesai (%s): %d dicek, %d dibuat, %d diperbarui.',
                $dryRun ? 'dry-run, tanpa menulis' : 'live',
                $stats['checked'],
                $stats['created'],
                $stats['updated']
            )
        );

        return self::SUCCESS;
    }
}