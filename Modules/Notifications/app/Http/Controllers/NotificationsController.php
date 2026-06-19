<?php

namespace Modules\Notifications\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Notifications\Models\Notification;

class NotificationsController extends Controller
{
    /**
     * List all notifications.
     */
    public function index()
    {
        $notifications = Notification::orderBy('created_at', 'desc')->get();
        return response()->json($notifications);
    }

    /**
     * Mark a notification as read.
     */
    public function read($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->update(['read_at' => now()]);

        return response()->json($notification);
    }
}
