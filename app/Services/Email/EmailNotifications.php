<?php

namespace TaskLedger\App\Services\Email;

use TaskLedger\App\Models\Meta;
use TaskLedger\Framework\Support\Arr;

class EmailNotifications
{
    const META_KEY = 'email_notifications_config';
    const OBJECT_TYPE = 'email_notification';

    public static function getNotifications(): array
    {
        $settings = static::getDefaultNotifications();
        $settings = apply_filters('task_ledger/email_notifications', $settings);
        $config = Arr::get(static::cachedSettings(), 'notification_config', []);
        
        foreach ($settings as $key => &$setting) {
            $setting['name'] = $key;
            $keyConfig = Arr::get($config, $key, []);
            if (!$keyConfig) {
                continue;
            }

            $setting['settings'] = wp_parse_args($keyConfig, $setting['settings']);
        }

        return $settings;
    }

    public static function getDefaultNotifications(): array
    {
        return [
            'daily_task_submission_manager' => [
                'event'            => 'task_ledger_log_submitted',
                'title'            => __('Send daily task notifications', 'taskledger'),
                'description'      => __('This email will be sent to the manager when a developer submits their daily task log.', 'taskledger'),
                'recipient'        => 'manager',
                'smartcode_groups' => [],
                'template_path'    => 'daily-task-submission',
                'is_async'         => false,
                'settings'         => [
                    'active'          => 'yes',
                    'subject'         => __('Daily Task Submission from {{developer.name}}', 'taskledger'),
                    'is_default_body' => 'yes',
                    'email_body'      => '',
                ]
            ],
            'monthly_unreviewed_reminder_manager' => [
                'event'            => 'task_ledger_monthly_reminder',
                'title'            => __('Send monthly reminder of unreviewed tasks', 'taskledger'),
                'description'      => __('This email will be sent monthly to managers with a list of unreviewed tasks from their team members.', 'taskledger'),
                'recipient'        => 'manager',
                'smartcode_groups' => [],
                'template_path'    => 'monthly-unreviewed-reminder',
                'is_async'         => false,
                'settings'         => [
                    'active'          => 'yes',
                    'subject'         => __('Monthly Reminder: Unreviewed Tasks', 'taskledger'),
                    'is_default_body' => 'yes',
                    'email_body'      => '',
                ]
            ],
        ];
    }

    public static function getNotification($name)
    {
        $notifications = static::getNotifications();
        return Arr::get($notifications, $name);
    }

    public static function getNotificationConfig($notificationName = null)
    {
        $configs = self::getNotifications();
        if ($notificationName) {
            return Arr::get($configs, $notificationName . '.settings', []);
        }
        return Arr::pluck($configs, 'settings', 'name');
    }

    public static function updateNotification($name, $data)
    {
        $updateableKeys = [
            'active',
            'subject',
            'email_body',
            'is_default_body'
        ];
        
        $allConfig = static::getSettings();
        $config = static::getNotificationConfig($name);

        foreach ($updateableKeys as $key) {
            $defaultValue = Arr::get($config, $key, '');
            $config[$key] = Arr::get($data, $key, $defaultValue);
        }

        Arr::set($allConfig, 'notification_config.' . $name, $config);

        // Use Meta::setMeta to save configuration
        Meta::setMeta(static::OBJECT_TYPE, 0, static::META_KEY, $allConfig);

        static::updateCache();

        return static::getNotification($name);
    }

    public static function getSettings($key = null)
    {
        $defaultSettings = [
            'notification_config' => []
        ];
        
        $cachedSettings = static::cachedSettings();
        $settings = wp_parse_args($cachedSettings, $defaultSettings);

        if (!empty($key)) {
            return Arr::get($settings, $key);
        }

        return $settings;
    }

    public static function cachedSettings()
    {
        $meta = Meta::getMeta(static::OBJECT_TYPE, 0, static::META_KEY);
            
        return $meta ? $meta->meta_value : [];
    }

    private static function updateCache(): void
    {
        // Clear any potential cache if needed
        // For now, we'll rely on direct database queries
    }
}

