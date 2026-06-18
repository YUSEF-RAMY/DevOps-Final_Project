<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Occasion;
use App\Models\User;
use App\Models\UserNotification;

class NotificationService
{
    public function notifyUser(User $user, string $type, string $title, string $message, array $data = []): UserNotification
    {
        return UserNotification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);
    }

    public function notifyAllAdmins(string $type, string $title, string $message, array $data = []): void
    {
        Admin::query()->each(function (Admin $admin) use ($type, $title, $message, $data) {
            UserNotification::create([
                'admin_id' => $admin->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
            ]);
        });
    }

    public function notifyOccasionReminder(User $user, Occasion $occasion): UserNotification
    {
        $date = $occasion->nextOccurrence()->format('F j');
        $message = sprintf(
            "Hey %s, %s's %s is on %s! Would you like us to prepare their favorite bouquet?",
            $user->name,
            $occasion->recipient_name,
            $occasion->occasion_name,
            $date
        );

        return $this->notifyUser($user, 'occasion_reminder', 'Occasion Reminder', $message, [
            'occasion_id' => $occasion->id,
            'recipient_name' => $occasion->recipient_name,
            'occasion_name' => $occasion->occasion_name,
            'occasion_date' => $occasion->nextOccurrence()->toDateString(),
            'preferred_product_id' => $occasion->preferred_product_id,
        ]);
    }

    public function notifyNewOrder(int $orderId, string $orderNumber, float $total): void
    {
        $this->notifyAllAdmins(
            'new_order',
            'New Order Received',
            "Order #{$orderNumber} has been placed for " . number_format($total, 2) . " AED.",
            ['order_id' => $orderId, 'order_number' => $orderNumber, 'total' => $total]
        );
    }

    public function unreadCountForUser(?User $user): int
    {
        if (!$user) {
            return 0;
        }

        return UserNotification::where('user_id', $user->id)->unread()->count();
    }

    public function unreadCountForAdmin(?Admin $admin): int
    {
        if (!$admin) {
            return 0;
        }

        return UserNotification::where('admin_id', $admin->id)->unread()->count();
    }
}
