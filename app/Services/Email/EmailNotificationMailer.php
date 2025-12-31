<?php

namespace TaskLedger\App\Services\Email;

use TaskLedger\App\Helpers\EmailShortCodeHelper;
use TaskLedger\App\Services\Email\EmailNotifications;
use TaskLedger\Framework\Support\Arr;

class EmailNotificationMailer
{
    /**
     * Send email notification
     *
     * @param string $notificationName
     * @param array $data Data for shortcode replacement
     * @param string|array $toEmail Recipient email(s)
     * @return bool
     */
    public static function send($notificationName, $data = [], $toEmail = null)
    {
        $notification = EmailNotifications::getNotification($notificationName);
        
        if (!$notification) {
            return false;
        }

        $settings = Arr::get($notification, 'settings', []);
        
        // Check if notification is active
        if (Arr::get($settings, 'active') !== 'yes') {
            return false;
        }

        // Get recipient email
        if (!$toEmail) {
            $toEmail = self::getRecipientEmail($notification, $data);
        }

        if (!$toEmail) {
            return false;
        }

        // Process subject
        $subject = Arr::get($settings, 'subject', '');
        $subject = EmailShortCodeHelper::processShortcodes($subject, $data);

        // Process body
        $isDefaultBody = Arr::get($settings, 'is_default_body', 'yes') === 'yes' || empty(Arr::get($settings, 'email_body'));
        
        if ($isDefaultBody) {
            $body = self::renderTemplate(Arr::get($notification, 'template_path'), $data);
        } else {
            $body = Arr::get($settings, 'email_body', '');
            $body = EmailShortCodeHelper::processShortcodes($body, $data);
        }

        // Send email
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
        ];

        return wp_mail($toEmail, $subject, $body, $headers);
    }

    /**
     * Get recipient email from notification and data
     *
     * @param array $notification
     * @param array $data
     * @return string|null
     */
    private static function getRecipientEmail($notification, $data)
    {
        $recipient = Arr::get($notification, 'recipient', '');
        
        if ($recipient === 'manager') {
            return $data['manager']['email'] ?? null;
        }
        
        return null;
    }

    /**
     * Render email template
     *
     * @param string $templatePath
     * @param array $data
     * @return string
     */
    private static function renderTemplate($templatePath, $data = [])
    {
        $templateFile = self::getTemplatePath($templatePath);
        
        if (!file_exists($templateFile)) {
            return '';
        }

        // Extract variables for template
        $developer = $data['developer'] ?? [];
        $log = $data['log'] ?? [];
        $manager = $data['manager'] ?? [];
        $unreviewed = $data['unreviewed'] ?? [];

        ob_start();
        include $templateFile;
        $content = ob_get_clean();

        return $content;
    }

    /**
     * Get template file path
     *
     * @param string $templatePath
     * @return string
     */
    private static function getTemplatePath($templatePath)
    {
        // Get plugin directory path using WordPress function
        $pluginDir = plugin_dir_path(dirname(dirname(dirname(__DIR__))));
        $templateFile = $pluginDir . 'app/Views/emails/' . $templatePath . '.php';
        
        return $templateFile;
    }

    /**
     * Format tasks list for email
     *
     * @param array $tasks
     * @return string
     */
    public static function formatTasksList($tasks)
    {
        if (empty($tasks)) {
            return '';
        }

        $html = '<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; margin: 15px 0;">';
        
        foreach ($tasks as $task) {
            $html .= '<tr style="border-bottom: 1px solid #e5e7eb;">';
            $html .= '<td style="padding: 15px 0;">';
            $html .= '<strong style="color: #111827; display: block; margin-bottom: 5px;">' . esc_html($task['title'] ?? 'Untitled Task') . '</strong>';
            
            $meta = [];
            if (!empty($task['hours'])) {
                $meta[] = esc_html($task['hours']) . 'h';
            }
            if (!empty($task['complete_weight'])) {
                $meta[] = esc_html($task['complete_weight']) . ' pts';
            }
            if (!empty($task['status'])) {
                $statusColors = [
                    'completed' => '#10b981',
                    'blocked' => '#ef4444',
                    'in-progress' => '#3b82f6',
                ];
                $color = $statusColors[$task['status']] ?? '#6b7280';
                $meta[] = '<span style="color: ' . $color . ';">' . esc_html(ucfirst($task['status'])) . '</span>';
            }
            
            if (!empty($meta)) {
                $html .= '<div style="color: #6b7280; font-size: 14px; margin-top: 5px;">' . implode(' • ', $meta) . '</div>';
            }
            
            if (!empty($task['note'])) {
                $html .= '<div style="color: #6b7280; font-size: 14px; margin-top: 5px; font-style: italic;">' . esc_html($task['note']) . '</div>';
            }
            
            if (!empty($task['blocker_reason'])) {
                $html .= '<div style="color: #ef4444; font-size: 14px; margin-top: 5px;"><strong>Blocker:</strong> ' . esc_html($task['blocker_reason']) . '</div>';
            }
            
            $html .= '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</table>';
        
        return $html;
    }

    /**
     * Format unreviewed tasks list for email
     *
     * @param array $unreviewedTasks Array of unreviewed logs with tasks
     * @return string
     */
    public static function formatUnreviewedTasksList($unreviewedTasks)
    {
        if (empty($unreviewedTasks)) {
            return '<p style="color: #10b981;">' . esc_html__('All tasks have been reviewed.', 'taskledger') . '</p>';
        }

        $html = '';
        
        foreach ($unreviewedTasks as $log) {
            $developerName = $log['developer']['name'] ?? 'Unknown';
            $logDate = $log['log']['date'] ?? '';
            $tasks = $log['log']['tasks'] ?? [];
            
            $html .= '<div style="margin-bottom: 25px; padding: 15px; background-color: #ffffff; border-left: 4px solid #f59e0b; border-radius: 4px;">';
            $html .= '<h3 style="margin: 0 0 10px 0; color: #111827; font-size: 16px;">';
            $html .= esc_html($developerName) . ' - ' . esc_html($logDate);
            $html .= '</h3>';
            
            if (!empty($tasks)) {
                $html .= self::formatTasksList($tasks);
            }
            
            $html .= '</div>';
        }
        
        return $html;
    }
}

