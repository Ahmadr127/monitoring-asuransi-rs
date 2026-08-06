<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()
            ->latest()
            ->paginate(20);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function markAsRead(UserNotification $notification)
    {
        abort_if($notification->user_id !== auth()->id(), 403);

        $notification->markAsRead();

        if ($notification->url) {
            return redirect($notification->url);
        }

        return back();
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);

        return back();
    }

    public function unreadCount()
    {
        return response()->json([
            'count' => auth()->user()->unreadNotifications()->count(),
        ]);
    }

    public function dropdown()
    {
        $user = auth()->user();
        $unreadCount = $user->unreadNotifications()->count();
        $recent = $user->notifications()->latest()->take(5)->get()->map(fn ($n) => [
            'id' => $n->id,
            'type' => $n->type,
            'icon' => $n->icon,
            'title' => $n->title,
            'message' => $n->message,
            'url' => $n->url,
            'is_read' => $n->read_at !== null,
            'time' => $n->created_at->diffForHumans(),
            'read_url' => route('admin.notifications.read', $n),
        ]);

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $recent,
        ]);
    }
}
