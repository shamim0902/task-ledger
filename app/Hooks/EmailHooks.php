<?php

namespace TaskLedger\App\Hooks;

use TaskLedger\App\Models\Log;
use TaskLedger\App\Models\LogItem;
use TaskLedger\App\Models\User;
use TaskLedger\App\Models\ManagerMember;
use TaskLedger\App\Services\Email\EmailNotificationMailer;
use TaskLedger\App\Services\PermissionService;

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

        // Get all managers for this developer (send email to all if multiple)
        $allManagerMembers = ManagerMember::where('member_id', $userId)->with('manager')->get();
        
        if ($allManagerMembers->isEmpty()) {
            // No manager assigned, skip email
            return;
        }
        
        $managers = $allManagerMembers->map(function($mm) {
            return $mm->manager;
        })->filter(); // Remove any null managers
        
        if ($managers->isEmpty()) {
            return;
        }
        
        // Use the first manager for data preparation (or could use primary manager if we add that field)
        $primaryManager = $managers->first();

        // Get log items with task details
        $logItems = LogItem::where('log_id', $log->id)->get();
        
        $tasks = [];
        $totalHours = 0;
        $totalPoints = 0;

        foreach ($logItems as $item) {
            $taskTitle = 'Task #' . $item->task_id;
            
            // Try to get task details from Fluent Boards if available
            if (class_exists('\FluentBoards\App\Models\Task')) {
                $task = \FluentBoards\App\Models\Task::find($item->task_id);
                if ($task) {
                    $taskTitle = $task->title;
                } else {
                    // Task not found in Fluent Boards, use fallback
                    $taskTitle = $item->note ?: 'Task #' . $item->task_id;
                }
            } else {
                // Fluent Boards not installed, use log item data directly
                $taskTitle = $item->note ?: 'Custom Task #' . $item->task_id;
            }
            
            $tasks[] = [
                'title' => $taskTitle,
                'hours' => $item->time_spent ?? 0,
                'complete_weight' => $item->complete_weight ?? 0,
                'status' => $item->activity_type ?? 'in-progress',
                'note' => $item->note ?? '',
                'blocker_reason' => $item->block_reason ?? '',
            ];
            $totalHours += (float)($item->time_spent ?? 0);
            $totalPoints += (float)($item->complete_weight ?? 0);
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
                'name' => $primaryManager->display_name ?? $primaryManager->user_nicename ?? '',
                'email' => $primaryManager->user_email ?? '',
            ],
        ];

        // Send email notification to all managers
        $emailSent = false;
        $emailFailed = false;
        
        foreach ($managers as $manager) {
            if (!$manager || !$manager->user_email) {
                continue;
            }
            
            // Update manager data for this specific manager
            $data['manager'] = [
                'name' => $manager->display_name ?? $manager->user_nicename ?? '',
                'email' => $manager->user_email ?? '',
            ];
            
            $result = EmailNotificationMailer::send('daily_task_submission_manager', $data, $manager->user_email);
            
            if ($result) {
                $emailSent = true;
            } else {
                $emailFailed = true;
            }
        }
    }
}

