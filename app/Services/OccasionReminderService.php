<?php

namespace App\Services;

use App\Models\Occasion;
use App\Models\User;

class OccasionReminderService
{
    public function __construct(private NotificationService $notifications)
    {
    }

    public function processDueReminders(): int
    {
        $count = 0;

        Occasion::query()
            ->where('reminder_status', true)
            ->with('user')
            ->chunkById(100, function ($occasions) use (&$count) {
                foreach ($occasions as $occasion) {
                    if (!$occasion->user || !$occasion->isDueForReminder()) {
                        continue;
                    }

                    $this->notifications->notifyOccasionReminder($occasion->user, $occasion);
                    $occasion->update(['last_reminded_at' => now()]);
                    $count++;
                }
            });

        return $count;
    }

    public function upcomingForUser(User $user, int $limit = 6)
    {
        return $user->occasions()
            ->with('preferredProduct')
            ->get()
            ->sortBy(fn (Occasion $o) => $o->nextOccurrence())
            ->take($limit)
            ->values();
    }
}
