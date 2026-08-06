<?php

namespace App\Console\Commands;

use App\Models\RuangLingkup;
use App\Models\UserNotification;
use Illuminate\Console\Command;

class GenerateContractNotifications extends Command
{
    protected $signature = 'contracts:generate-notifications';

    protected $description = 'Generate notifications for expiring contracts and new contracts';

    public function handle(): int
    {
        $users = \App\Models\User::whereHas('role', fn ($q) => $q->whereIn('key', ['admin', 'legal']))->get();

        foreach ($users as $user) {
            $routeName = $user->role->key === 'admin' ? 'admin.ruang-lingkup.show' : 'user.kerjasama.show';

            $expiring = RuangLingkup::where('status', 'aktif')
                ->whereDate('tanggal_berakhir', '>=', now()->startOfDay())
                ->whereDate('tanggal_berakhir', '<=', now()->addDays(RuangLingkup::EXPIRING_SOON))
                ->get();

            foreach ($expiring as $contract) {
                $days = now()->startOfDay()->diffInDays($contract->tanggal_berakhir->startOfDay(), false);
                $url = route($routeName, $contract, false);

                $exists = UserNotification::where('user_id', $user->id)
                    ->where('title', 'Kontrak Akan Berakhir')
                    ->where('url', $url)
                    ->exists();

                if (! $exists) {
                    UserNotification::create([
                        'user_id' => $user->id,
                        'type' => 'warning',
                        'title' => 'Kontrak Akan Berakhir',
                        'message' => "{$contract->nama_asuransi} akan berakhir dalam {$days} hari",
                        'icon' => 'clock',
                        'url' => $user->role->key === 'legal' ? '' : $url,
                    ]);
                }
            }

            $lastNewNotif = UserNotification::where('user_id', $user->id)
                ->where('title', 'Kontrak Baru Ditambahkan')
                ->latest()
                ->first();

            $recentDateNew = $lastNewNotif?->created_at ?? now()->subDay();

            $recentContracts = RuangLingkup::where('created_at', '>=', $recentDateNew)->get();

            foreach ($recentContracts as $contract) {
                $url = route($routeName, $contract, false);
                $exists = UserNotification::where('user_id', $user->id)
                    ->where('title', 'Kontrak Baru Ditambahkan')
                    ->where('url', $url)
                    ->exists();

                if (! $exists) {
                    UserNotification::create([
                        'user_id' => $user->id,
                        'type' => 'success',
                        'title' => 'Kontrak Baru Ditambahkan',
                        'message' => "{$contract->nama_asuransi} telah ditambahkan",
                        'icon' => 'plus',
                        'url' => $user->role->key === 'legal' ? '' : $url,
                    ]);
                }
            }
        }

        $this->info('Notifications generated successfully.');

        return Command::SUCCESS;
    }
}
