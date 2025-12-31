<?php

namespace TaskLedger\App\Helpers;

class EmailShortCodeHelper
{
    /**
     * Get available shortcodes for email notifications
     *
     * @return array
     */
    public static function getEmailNotificationShortcodes(): array
    {
        return [
            'developer' => [
                'label' => __('Developer', 'taskledger'),
                'shortcodes' => [
                    '{{developer.name}}' => __('Developer\'s display name', 'taskledger'),
                    '{{developer.email}}' => __('Developer\'s email address', 'taskledger'),
                    '{{developer.id}}' => __('Developer\'s user ID', 'taskledger'),
                ]
            ],
            'log' => [
                'label' => __('Daily Log', 'taskledger'),
                'shortcodes' => [
                    '{{log.date}}' => __('Log date', 'taskledger'),
                    '{{log.tasks_count}}' => __('Number of tasks in the log', 'taskledger'),
                    '{{log.total_hours}}' => __('Total hours logged', 'taskledger'),
                    '{{log.total_points}}' => __('Total story points', 'taskledger'),
                    '{{log.tasks}}' => __('List of tasks with details', 'taskledger'),
                    '{{log.notes}}' => __('Additional notes from the log', 'taskledger'),
                ]
            ],
            'manager' => [
                'label' => __('Manager', 'taskledger'),
                'shortcodes' => [
                    '{{manager.name}}' => __('Manager\'s display name', 'taskledger'),
                    '{{manager.email}}' => __('Manager\'s email address', 'taskledger'),
                ]
            ],
            'unreviewed' => [
                'label' => __('Unreviewed Tasks', 'taskledger'),
                'shortcodes' => [
                    '{{unreviewed_count}}' => __('Count of unreviewed tasks', 'taskledger'),
                    '{{unreviewed_tasks}}' => __('List of unreviewed tasks', 'taskledger'),
                ]
            ],
        ];
    }

    /**
     * Process shortcodes in a string
     *
     * @param string $content
     * @param array $data Data array containing values for shortcodes
     * @return string
     */
    public static function processShortcodes($content, $data = [])
    {
        if (empty($content)) {
            return '';
        }

        $shortcodes = [
            'developer.name' => $data['developer']['name'] ?? '',
            'developer.email' => $data['developer']['email'] ?? '',
            'developer.id' => $data['developer']['id'] ?? '',
            'log.date' => $data['log']['date'] ?? '',
            'log.tasks_count' => $data['log']['tasks_count'] ?? 0,
            'log.total_hours' => $data['log']['total_hours'] ?? 0,
            'log.total_points' => $data['log']['total_points'] ?? 0,
            'log.tasks' => $data['log']['tasks'] ?? '',
            'log.notes' => $data['log']['notes'] ?? '',
            'manager.name' => $data['manager']['name'] ?? '',
            'manager.email' => $data['manager']['email'] ?? '',
            'unreviewed_count' => $data['unreviewed']['count'] ?? 0,
            'unreviewed_tasks' => $data['unreviewed']['tasks'] ?? '',
        ];

        foreach ($shortcodes as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }

        return $content;
    }
}

