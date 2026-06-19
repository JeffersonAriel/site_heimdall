<?php

namespace Modules\Notifications\Services;

use Modules\Notifications\Models\Notification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Dispatch notification to database and log it.
     */
    public function send($title, $message, $type = 'info', $userId = null)
    {
        $notification = Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
        ]);

        // Simulating Email/SMS Dispatch in Log
        Log::info("Notification: [{$type}] {$title} - {$message} (Target User ID: " . ($userId ?? 'ALL') . ")");

        return $notification;
    }
}
