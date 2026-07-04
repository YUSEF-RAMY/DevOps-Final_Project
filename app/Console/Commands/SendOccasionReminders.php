<?php

namespace App\Console\Commands;

use App\Services\OccasionReminderService;
use Illuminate\Console\Command;

class SendOccasionReminders extends Command
{
    protected $signature = 'pure-rose:send-occasion-reminders';
    protected $description = 'Send personalized occasion reminder notifications to customers';

    public function handle(OccasionReminderService $service): int
    {
        $count = $service->processDueReminders();
        $this->info("Sent {$count} occasion reminder(s).");

        return self::SUCCESS;
    }
}
