<?php

namespace TaskLedger\App\Hooks;

use TaskLedger\App\Models\Log;
use TaskLedger\App\Models\LogItem;
use TaskLedger\App\Models\User;
use TaskLedger\App\Models\ManagerMember;
use TaskLedger\App\Services\Email\EmailNotificationMailer;
use TaskLedger\App\Services\PermissionService;
use FluentBoards\App\Models\Task;

class CronHooks
{
    /**
     * Register cron hooks
     */
    public static function register()
    {
        // Register monthly cron schedule
        add_filter('cron_schedules', [__CLASS__, 'addMonthlySchedule']);
        
        // Schedule the monthly reminder if not already scheduled
        if (!wp_next_scheduled('task_ledger_monthly_unreviewed_reminder')) {
            wp_schedule_event(time(), 'monthly', 'task_ledger_monthly_unreviewed_reminder');
        }
        
        // Hook the reminder handler
        add_action('task_ledger_monthly_unreviewed_reminder', [__CLASS__, 'handleMonthlyReminder']);
    }

    /**
     * Add monthly schedule to WordPress cron
     *
     * @param array $schedules
     * @return array
     */
    public static function addMonthlySchedule($schedules)
    {
        if (!isset($schedules['monthly'])) {
            $schedules['monthly'] = [
                'interval' => 30 * DAY_IN_SECONDS, // Approximately monthly
                'display' => __('Once Monthly', 'taskledger')
            ];
        }
        return $schedules;
    }

    /**
     * Handle monthly unreviewed tasks reminder
     */
    public static function handleMonthlyReminder()
    {
        // Get date range for past month
        $endDate = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime('-30 days'));

        // Get all managers
        $managers = self::getAllManagers();

        foreach ($managers as $manager) {
            // Get unreviewed logs from manager's team members in the past month
            $unreviewedLogs = self::getUnreviewedLogsForManager($manager->ID, $startDate, $endDate);

            if (empty($unreviewedLogs)) {
                continue; // Skip if no unreviewed tasks
            }

            // Format unreviewed tasks list
            $unreviewedTasksHtml = EmailNotificationMailer::formatUnreviewedTasksList($unreviewedLogs);

            // Prepare data for email
            $data = [
                'manager' => [
                    'name' => $manager->display_name ?? $manager->user_nicename ?? '',
                    'email' => $manager->user_email ?? '',
                ],
                'unreviewed' => [
                    'count' => count($unreviewedLogs),
                    'tasks' => $unreviewedTasksHtml,
                ],
            ];

            // Send email notification
            EmailNotificationMailer::send('monthly_unreviewed_reminder_manager', $data, $manager->user_email);
        }
    }

    /**
     * Get all managers
     *
     * @return array
     */
    private static function getAllManagers()
    {
        $managerRole = \TaskLedger\App\Models\Role::where('slug', 'manager')->first();
        if (!$managerRole) {
            return [];
        }

        $managerUserIds = \TaskLedger\App\Models\UserRoleProject::where('role_id', $managerRole->id)
            ->pluck('user_id')
            ->toArray();

        if (empty($managerUserIds)) {
            return [];
        }

        return User::whereIn('ID', $managerUserIds)->get()->all();
    }

    /**
     * Get unreviewed logs for a manager's team members
     *
     * @param int $managerId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    private static function getUnreviewedLogsForManager($managerId, $startDate, $endDate)
    {
        // Get member IDs for this manager
        $memberIds = ManagerMember::where('manager_id', $managerId)
            ->pluck('member_id')
            ->toArray();

        if (empty($memberIds)) {
            return [];
        }

        // Get unreviewed logs from members
        $logs = Log::whereIn('user_id', $memberIds)
            ->whereBetween('log_date', [$startDate, $endDate])
            ->where('status', 'submitted')
            ->where(function($query) {
                $query->where('reviewed', false)
                      ->orWhereNull('reviewed');
            })
            ->with('logItems')
            ->get();

        $unreviewedLogs = [];

        foreach ($logs as $log) {
            $developer = User::find($log->user_id);
            if (!$developer) {
                continue;
            }

            $tasks = [];
            foreach ($log->logItems as $item) {
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
                }
            }

            $unreviewedLogs[] = [
                'developer' => [
                    'name' => $developer->display_name ?? $developer->user_nicename ?? '',
                    'email' => $developer->user_email ?? '',
                    'id' => $developer->ID,
                ],
                'log' => [
                    'date' => $log->log_date ?? '',
                    'tasks' => $tasks,
                    'notes' => $log->additional_notes ?? '',
                ],
            ];
        }

        return $unreviewedLogs;
    }

    /**
     * Manually trigger monthly reminder (for admin use)
     *
     * @return array
     */
    public static function triggerMonthlyReminder()
    {
        self::handleMonthlyReminder();
        return [
            'message' => __('Monthly reminder sent successfully', 'taskledger'),
        ];
    }
}

