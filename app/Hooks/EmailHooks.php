<?php

namespace TaskLedger\App\Hooks;

use TaskLedger\App\Models\Log;
use TaskLedger\App\Models\LogItem;
use TaskLedger\App\Models\User;
use TaskLedger\App\Services\Email\EmailNotificationMailer;
use TaskLedger\App\Services\PermissionService;
use FluentBoards\App\Models\Task;

class EmailHooks
{
    /**
     * Register email hooks
     */
    public static function register()
    {
        // Listen for log submission
        add_action('task_ledger_log_submitted', [__CLASS__, 'handleLogSubmitted'], 10, 2);
    }

    /**
     * Handle log submission - send email to manager
     *
     * @param Log $log
     * @param int $userId
     */
    public static function handleLogSubmitted($log, $userId)
    {
        // Get developer info
        $developer = User::find($userId);
        if (!$developer) {
            return;
        }

        // Get manager for this developer
        $manager = PermissionService::getManager($userId);
        if (!$manager) {
            // No manager assigned, skip email
            return;
        }

        // Get log items with task details
        $logItems = LogItem::where('log_id', $log->id)->get();
        
        $tasks = [];
        $totalHours = 0;
        $totalPoints = 0;

        foreach ($logItems as $item) {
            $task = Task::find($item->task_id);
            if ($task) {
                $tasks[] = [
                    'title' => $task->title,
                    'hours' => $item->time_spent ?? 0,
                    'complete_weight' => $item->complete_weight ?? 0,
                    'status' => $item->activity_type ?? 'in-progress',
                    'note' => $item->note ?? '',
                    'blocker_reason' => $item->block_reason ?? '',
                ];
                $totalHours += (float)($item->time_spent ?? 0);
                $totalPoints += (float)($item->complete_weight ?? 0);
            }
        }

        // Format tasks list for email
        $tasksHtml = EmailNotificationMailer::formatTasksList($tasks);

        // Prepare data for email
        $data = [
            'developer' => [
                'name' => $developer->display_name ?? $developer->user_nicename ?? '',
                'email' => $developer->user_email ?? '',
                'id' => $developer->ID,
            ],
            'log' => [
                'date' => $log->log_date ?? '',
                'tasks_count' => count($tasks),
                'total_hours' => number_format($totalHours, 1),
                'total_points' => $totalPoints,
                'tasks' => $tasksHtml,
                'notes' => $log->additional_notes ?? '',
            ],
            'manager' => [
                'name' => $manager->display_name ?? $manager->user_nicename ?? '',
                'email' => $manager->user_email ?? '',
            ],
        ];

        // Send email notification
        EmailNotificationMailer::send('daily_task_submission_manager', $data, $manager->user_email);
    }
}

