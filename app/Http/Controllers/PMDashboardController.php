<?php

namespace TaskLedger\App\Http\Controllers;

use TaskLedger\App\Models\Log;
use TaskLedger\App\Models\LogItem;
use TaskLedger\App\Models\User;
use TaskLedger\Framework\Http\Request\Request;
use FluentBoards\App\Models\Task;
use TaskLedger\App\Models\Meta;

class PMDashboardController extends Controller
{
    /**
     * Get team activity summary
     */
    public function getTeamActivity(Request $request)
    {
        $date = $request->get('date', date('Y-m-d'));
        $teamMembers = $request->get('team_members', 'all');
        
        // Get all users who have submitted logs
        $userIds = Log::where('log_date', $date)
            ->distinct()
            ->pluck('user_id')
            ->toArray();

        if ($teamMembers !== 'all' && is_array($teamMembers)) {
            $userIds = array_intersect($userIds, $teamMembers);
        }

        $teamActivity = [];
        
        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if (!$user) continue;

            $log = Log::where('user_id', $userId)
                ->where('log_date', $date)
                ->with('logItems')
                ->first();

            if (!$log) continue;

            $tasksWorkedOn = $log->logItems->count();
            $completedTasks = $log->logItems->where('activity_type', 'completed')->count();
            $blockedTasks = $log->logItems->where('activity_type', 'blocked')->count();
            $totalHours = $log->logItems->sum('time_spent');
            $totalStoryPoints = $log->logItems->sum('complete_weight');

            // Get task details for this member
            $taskDetails = [];
            foreach ($log->logItems as $item) {
                $task = Task::find($item->task_id);
                if (!$task) continue;

                $taskDetails[] = [
                    'id' => $item->id,
                    'task_id' => $task->id,
                    'title' => $task->title,
                    'status' => $item->activity_type,
                    'hours' => $item->time_spent,
                    'story_points' => $item->complete_weight,
                    'blocker_reason' => $item->note ?? null,
                    'board' => $task->board ? [
                        'id' => $task->board->id,
                        'title' => $task->board->title,
                    ] : null,
                ];
            }

            $teamActivity[] = [
                'user_id' => $user->ID,
                'user_name' => $user->display_name ?? $user->user_nicename,
                'user_email' => $user->user_email,
                'user_initials' => $this->getInitials($user->display_name ?? $user->user_nicename),
                'tasks_worked_on' => $tasksWorkedOn,
                'completed_tasks' => $completedTasks,
                'blocked_tasks' => $blockedTasks,
                'total_hours' => $totalHours,
                'story_points' => $totalStoryPoints,
                'notes' => $log->additional_notes ?? '',
                'has_update' => true,
                'log_id' => $log->id,
                'tasks' => $taskDetails,
            ];
        }

        // Get users without updates (only if filtering by specific team members)
        if ($teamMembers !== 'all' && is_array($teamMembers)) {
            // Only show selected team members, even if they don't have updates
            foreach ($teamMembers as $memberId) {
                if (!in_array($memberId, $userIds)) {
                    $user = User::find($memberId);
                    if ($user) {
                        $teamActivity[] = [
                            'user_id' => $user->ID,
                            'user_name' => $user->display_name ?? $user->user_nicename,
                            'user_email' => $user->user_email,
                            'user_initials' => $this->getInitials($user->display_name ?? $user->user_nicename),
                            'tasks_worked_on' => 0,
                            'completed_tasks' => 0,
                            'blocked_tasks' => 0,
                            'total_hours' => 0,
                            'story_points' => 0,
                            'notes' => '',
                            'has_update' => false,
                            'log_id' => null,
                            'tasks' => [],
                        ];
                    }
                }
            }
        } else {
            // Show all users who have ever submitted a log
            $allUserIdsWithHistory = Log::distinct()->pluck('user_id')->toArray();
            $allUsers = User::whereIn('ID', $allUserIdsWithHistory)->get();

            foreach ($allUsers as $user) {
                $hasUpdate = in_array($user->ID, $userIds);
                if (!$hasUpdate) {
                    $teamActivity[] = [
                        'user_id' => $user->ID,
                        'user_name' => $user->display_name ?? $user->user_nicename,
                        'user_email' => $user->user_email,
                        'user_initials' => $this->getInitials($user->display_name ?? $user->user_nicename),
                        'tasks_worked_on' => 0,
                        'completed_tasks' => 0,
                        'blocked_tasks' => 0,
                        'total_hours' => 0,
                        'story_points' => 0,
                        'notes' => '',
                        'has_update' => false,
                        'log_id' => null,
                        'tasks' => [],
                    ];
                }
            }
        }

        return $teamActivity;
    }

    /**
     * Get dashboard summary statistics
     */
    public function getSummaryStats(Request $request)
    {
        $date = $request->get('date', date('Y-m-d'));
        $boardIds = $request->get('boards', 'all');

        $logs = Log::where('log_date', $date)
            ->with('logItems')
            ->get();

        $totalUpdates = $logs->count();
        $totalTasksCompleted = 0;
        $totalBlockedTasks = 0;
        $missingUpdates = 0;

        $allUserIds = [];
        foreach ($logs as $log) {
            $allUserIds[] = $log->user_id;
            $totalTasksCompleted += $log->logItems->where('activity_type', 'completed')->count();
            $totalBlockedTasks += $log->logItems->where('activity_type', 'blocked')->count();
        }

        // Get users who have submitted logs before but not today
        $usersWithHistory = Log::distinct()->pluck('user_id')->toArray();
        $missingUpdates = count($usersWithHistory) - count(array_unique($allUserIds));

        return [
            'updates_submitted' => $totalUpdates,
            'tasks_completed' => $totalTasksCompleted,
            'blocked_tasks' => $totalBlockedTasks,
            'missing_updates' => max(0, $missingUpdates),
            'date' => $date,
        ];
    }

    /**
     * Get task analytics
     */
    public function getTaskAnalytics(Request $request)
    {
        $date = $request->get('date', date('Y-m-d'));
        $boardIds = $request->get('boards', 'all');

        $logs = Log::where('log_date', $date)
            ->with('logItems')
            ->get();

        // Get all users
        $allUserIds = Log::distinct()->pluck('user_id')->toArray();
        $users = User::whereIn('ID', $allUserIds)->get();

        $tasksTouchedByUser = [];
        $tasksCompletedByUser = [];
        $workDistribution = [
            'completed' => 0,
            'in_progress' => 0,
            'blocked' => 0,
        ];

        foreach ($users as $user) {
            $userLog = $logs->where('user_id', $user->ID)->first();
            if ($userLog) {
                $tasksTouchedByUser[$user->ID] = $userLog->logItems->count();
                $tasksCompletedByUser[$user->ID] = $userLog->logItems->where('activity_type', 'completed')->count();
            } else {
                $tasksTouchedByUser[$user->ID] = 0;
                $tasksCompletedByUser[$user->ID] = 0;
            }
        }

        // Work distribution
        foreach ($logs as $log) {
            foreach ($log->logItems as $item) {
                if ($item->activity_type === 'completed') {
                    $workDistribution['completed']++;
                } elseif ($item->activity_type === 'blocked') {
                    $workDistribution['blocked']++;
                } else {
                    $workDistribution['in_progress']++;
                }
            }
        }

        $total = array_sum($workDistribution);
        $workDistributionPercent = [];
        foreach ($workDistribution as $key => $value) {
            $workDistributionPercent[$key] = $total > 0 ? round(($value / $total) * 100) : 0;
        }

        // Blocked tasks trend (last 7 days)
        $blockedTasksTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $checkDate = date('Y-m-d', strtotime("-{$i} days"));
            $dayLogs = Log::where('log_date', $checkDate)
                ->with('logItems')
                ->get();
            
            $blockedCount = 0;
            foreach ($dayLogs as $dayLog) {
                $blockedCount += $dayLog->logItems->where('activity_type', 'blocked')->count();
            }
            
            $blockedTasksTrend[] = [
                'date' => $checkDate,
                'count' => $blockedCount,
                'label' => date('M d', strtotime($checkDate)),
            ];
        }

        // Attention required
        $attentionRequired = [];
        $todayLogs = Log::where('log_date', $date)->pluck('user_id')->toArray();
        $usersWithHistory = Log::distinct()->pluck('user_id')->toArray();

        foreach ($usersWithHistory as $userId) {
            $user = User::find($userId);
            if (!$user) continue;

            $hasUpdate = in_array($userId, $todayLogs);
            $userLog = $logs->where('user_id', $userId)->first();
            $blockedCount = $userLog ? $userLog->logItems->where('activity_type', 'blocked')->count() : 0;

            $issues = [];
            if (!$hasUpdate) {
                $issues[] = 'Missing update';
            }
            if ($blockedCount > 0) {
                $issues[] = "{$blockedCount} blocked task(s)";
            }

            if (!empty($issues)) {
                $attentionRequired[] = [
                    'user_id' => $user->ID,
                    'user_name' => $user->display_name ?? $user->user_nicename,
                    'user_initials' => $this->getInitials($user->display_name ?? $user->user_nicename),
                    'issues' => $issues,
                ];
            }
        }

        return [
            'tasks_touched_by_user' => $tasksTouchedByUser,
            'tasks_completed_by_user' => $tasksCompletedByUser,
            'work_distribution' => $workDistributionPercent,
            'blocked_tasks_trend' => $blockedTasksTrend,
            'attention_required' => $attentionRequired,
            'users' => $users->map(function($user) {
                return [
                    'id' => $user->ID,
                    'name' => $user->display_name ?? $user->user_nicename,
                ];
            })->values(),
        ];
    }

    /**
     * Get task overview with filters
     */
    public function getTaskOverview(Request $request)
    {
        $date = $request->get('date', date('Y-m-d'));
        $status = $request->get('status', 'all');
        $boardIds = $request->get('boards', 'all');

        $logs = Log::where('log_date', $date)
            ->with(['logItems', 'user'])
            ->get();

        $tasks = [];
        
        foreach ($logs as $log) {
            foreach ($log->logItems as $item) {
                if ($status !== 'all' && $item->activity_type !== $status) {
                    continue;
                }

                $task = Task::find($item->task_id);
                if (!$task) continue;

                $taskData = [
                    'id' => $item->id,
                    'task_id' => $task->id,
                    'title' => $task->title,
                    'status' => $item->activity_type,
                    'assignee' => [
                        'id' => $log->user->ID,
                        'name' => $log->user->display_name ?? $log->user->user_nicename,
                        'initials' => $this->getInitials($log->user->display_name ?? $log->user->user_nicename),
                    ],
                    'board' => $task->board ? [
                        'id' => $task->board->id,
                        'title' => $task->board->title,
                    ] : null,
                    'hours' => $item->time_spent,
                    'story_points' => $item->complete_weight,
                    'blocker_reason' => $item->note ?? null,
                ];

                $tasks[] = $taskData;
            }
        }

        return $tasks;
    }

    /**
     * Get blocked tasks
     */
    public function getBlockedTasks(Request $request)
    {
        $date = $request->get('date', date('Y-m-d'));
        
        $logs = Log::where('log_date', $date)
            ->with(['logItems', 'user'])
            ->get();

        $blockedTasks = [];
        
        foreach ($logs as $log) {
            foreach ($log->logItems->where('activity_type', 'blocked') as $item) {
                $task = Task::find($item->task_id);
                if (!$task) continue;

                $blockedTasks[] = [
                    'id' => $item->id,
                    'task_id' => $task->id,
                    'title' => $task->title,
                    'assignee' => [
                        'id' => $log->user->ID,
                        'name' => $log->user->display_name ?? $log->user->user_nicename,
                        'initials' => $this->getInitials($log->user->display_name ?? $log->user->user_nicename),
                    ],
                    'board' => $task->board ? [
                        'id' => $task->board->id,
                        'title' => $task->board->title,
                    ] : null,
                    'blocker_reason' => $item->note ?? 'No reason provided',
                ];
            }
        }

        return $blockedTasks;
    }

    /**
     * Get all team members
     */
    public function getTeamMembers()
    {
        $userIds = Log::distinct()->pluck('user_id')->toArray();
        $users = User::whereIn('ID', $userIds)->get();

        return $users->map(function($user) {
            return [
                'id' => $user->ID,
                'name' => $user->display_name ?? $user->user_nicename,
                'email' => $user->user_email,
                'initials' => $this->getInitials($user->display_name ?? $user->user_nicename),
            ];
        })->values();
    }

    /**
     * Get all boards
     */
    public function getBoards()
    {
        // This would need to be implemented based on your board structure
        // For now, returning empty array
        return [];
    }

    /**
     * Helper to get user initials
     */
    private function getInitials($name)
    {
        $words = explode(' ', trim($name));
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[count($words) - 1], 0, 1));
        }
        return strtoupper(substr($name, 0, 2));
    }
}

