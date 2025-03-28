<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    /**
     * Create a new notification.
     */
    public function createNotification($userId, $body, $heading)
    {
        return Notification::create([
            'user_id'       => $userId,
            'body'          => $body,
            'heading'       => $heading,
            
        ]);
    }

    /**
     * Get notifications by user ID.
     */
    public function getUserNotifications($userId)
    {
        return Notification::where('user_id', $userId)->orderBy('created_at', 'desc')->get();
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead($notificationId)
    {
        $notification = Notification::find($notificationId);
        if ($notification) {
            $notification->update(['updated_at' => now()]);
            return $notification;
        }
        return null;
    }

    /**
     * Delete a notification.
     */
    public function deleteNotification($notificationId)
    {
        return Notification::where('id', $notificationId)->delete();
    }
}
