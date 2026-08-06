<?php

namespace App\Console\Commands;

use App\Services\ContractReminderService;
use Illuminate\Console\Command;

class SendContractReminders extends Command
{
    protected $signature = 'app:send-contract-reminders';

    protected $description = 'Send dynamic email reminders for insurance contracts (H-90, H-30, H-7)';

    public function handle(ContractReminderService $reminderService)
    {
        $this->info('Starting dynamic contract reminder scan...');

        $sentCount = $reminderService->sendRemindersForAllContracts();

        $this->info("Scan completed. {$sentCount} contracts triggered reminders.");
    }
}
