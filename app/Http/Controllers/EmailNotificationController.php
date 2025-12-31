<?php

namespace TaskLedger\App\Http\Controllers;

use TaskLedger\App\Services\Email\EmailNotifications;
use TaskLedger\App\Helpers\EmailShortCodeHelper;
use TaskLedger\App\Services\PermissionService;
use TaskLedger\Framework\Http\Request\Request;
use TaskLedger\Framework\Support\Arr;

class EmailNotificationController extends Controller
{
    /**
     * List all email notifications
     */
    public function index()
    {
        $userId = get_current_user_id();
        
        // Only admin can access email notifications
        if (!PermissionService::isAdmin($userId)) {
            return $this->sendError('You do not have permission to access email notifications', 403);
        }

        $notifications = EmailNotifications::getNotifications();

        return [
            'data' => array_values($notifications)
        ];
    }

    /**
     * Get a specific notification
     */
    public function find($notification)
    {
        $userId = get_current_user_id();
        
        // Only admin can access email notifications
        if (!PermissionService::isAdmin($userId)) {
            return $this->sendError('You do not have permission to access email notifications', 403);
        }

        $name = sanitize_text_field($notification);
        $notification = EmailNotifications::getNotification($name);

        if ($notification) {
            return [
                'data' => $notification,
                'shortcodes' => EmailShortCodeHelper::getEmailNotificationShortcodes(),
            ];
        }

        return $this->sendError('Notification not found', 404);
    }

    /**
     * Update notification settings
     */
    public function update(Request $request, $notification)
    {
        $userId = get_current_user_id();
        
        // Only admin can update email notifications
        if (!PermissionService::isAdmin($userId)) {
            return $this->sendError('You do not have permission to update email notifications', 403);
        }

        $name = sanitize_text_field($notification);
        $data = $request->all();

        $settings = Arr::get($data, 'settings', []);
        
        $updated = EmailNotifications::updateNotification($name, $settings);
        
        if ($updated) {
            return [
                'message' => __('Notification updated successfully', 'taskledger'),
                'data' => $updated
            ];
        } else {
            return $this->sendError('Failed to update notification', 500);
        }
    }

    /**
     * Enable/disable notification
     */
    public function enableNotification(Request $request, $name)
    {
        $userId = get_current_user_id();
        
        // Only admin can enable/disable email notifications
        if (!PermissionService::isAdmin($userId)) {
            return $this->sendError('You do not have permission to update email notifications', 403);
        }

        $enabledValue = sanitize_text_field(Arr::get($request->all(), 'active'));

        $notification = EmailNotifications::updateNotification(
            $name,
            ['active' => $enabledValue]
        );

        if ($notification) {
            return [
                'message' => __('Notification updated successfully', 'taskledger'),
                'data' => $notification
            ];
        }
        
        return $this->sendError('Failed to update notification', 500);
    }

    /**
     * Get shortcodes
     */
    public function getShortCodes()
    {
        $userId = get_current_user_id();
        
        // Only admin can access shortcodes
        if (!PermissionService::isAdmin($userId)) {
            return $this->sendError('You do not have permission to access shortcodes', 403);
        }

        return [
            'data' => [
                'shortcodes' => EmailShortCodeHelper::getEmailNotificationShortcodes(),
            ],
        ];
    }
}

