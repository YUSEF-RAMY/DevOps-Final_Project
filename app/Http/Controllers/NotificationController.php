<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private NotificationService $notifications)
    {
    }

    public function index(Request $request)
    {
        if (auth('admin')->check()) {
            $items = UserNotification::where('admin_id', auth('admin')->id())
                ->latest()
                ->limit(20)
                ->get();
        } elseif (auth()->check()) {
            $items = UserNotification::where('user_id', auth()->id())
                ->latest()
                ->limit(20)
                ->get();
        } else {
            return response()->json(['notifications' => [], 'unread_count' => 0]);
        }

        return response()->json([
            'notifications' => $items,
            'unread_count' => $items->whereNull('read_at')->count(),
        ]);
    }

    public function markRead(UserNotification $notification)
    {
        if (auth()->check() && $notification->user_id === auth()->id()) {
            $notification->markAsRead();
        } elseif (auth('admin')->check() && $notification->admin_id === auth('admin')->id()) {
            $notification->markAsRead();
        } else {
            abort(403);
        }

        return response()->json(['success' => true]);
    }

    public function markAllRead()
    {
        if (auth()->check()) {
            UserNotification::where('user_id', auth()->id())->unread()->update(['read_at' => now()]);
        } elseif (auth('admin')->check()) {
            UserNotification::where('admin_id', auth('admin')->id())->unread()->update(['read_at' => now()]);
        }

        return response()->json(['success' => true]);
    }
}
