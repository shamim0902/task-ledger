<?php

namespace TaskLedger\App\Http\Controllers;

use TaskLedger\App\Models\Log;
use TaskLedger\App\Models\LogItem;
use TaskLedger\App\Models\User;
use TaskLedger\Framework\Http\Request\Request;
use FluentBoards\App\Models\Task;
use FluentBoards\App\Models\Board;
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
        $boardIds = $request->get('boards', 'all');
        
        // Get all users who have submitted logs
        $userIds = Log::where('log_date', $date)
            ->distinct()
            ->pluck('user_id')
            ->toArray();

        if ($teamMembers !== 'all' && is_array($teamMembers)) {
            $userIds = array_intersect($userIds, $teamMembers);
        }
        
        // Filter by board if specified
        if ($boardIds !== 'all' && is_array($boardIds)) {
            $logs = Log::where('log_date', $date)->with('logItems')->get();
            $filteredUserIds = [];
            
            foreach ($logs as $log) {
                $hasMatchingTask = $log->logItems->filter(function($item) use ($boardIds) {
                    $task = Task::find($item->task_id);
                    return $task && in_array($task->board_id, $boardIds);
                })->count() > 0;
                
                if ($hasMatchingTask) {
                    $filteredUserIds[] = $log->user_id;
                }
            }
            
            $userIds = array_intersect($userIds, array_unique($filteredUserIds));
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

            // Filter log items by board if specified
            $logItems = $log->logItems;
            if ($boardIds !== 'all' && is_array($boardIds)) {
                $logItems = $logItems->filter(function($item) use ($boardIds) {
                    $task = Task::find($item->task_id);
                    return $task && in_array($task->board_id, $boardIds);
                });
            }

            $tasksWorkedOn = $logItems->count();
            $completedTasks = $logItems->where('activity_type', 'completed')->count();
            $blockedTasks = $logItems->where('activity_type', 'blocked')->count();
            $totalHours = $logItems->sum('time_spent');
            $totalStoryPoints = $logItems->sum('complete_weight');

            // Get task details for this member
            $taskDetails = [];
            foreach ($logItems as $item) {
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

        // Filter by board if specified
        if ($boardIds !== 'all' && is_array($boardIds)) {
            $logs = $logs->filter(function($log) use ($boardIds) {
                return $log->logItems->filter(function($item) use ($boardIds) {
                    $task = Task::find($item->task_id);
                    return $task && in_array($task->board_id, $boardIds);
                })->count() > 0;
            });
        }

        $totalUpdates = $logs->count();
        $totalTasksCompleted = 0;
        $totalBlockedTasks = 0;
        $missingUpdates = 0;

        $allUserIds = [];
        foreach ($logs as $log) {
            $logItems = $log->logItems;
            
            // Filter by board if needed
            if ($boardIds !== 'all' && is_array($boardIds)) {
                $logItems = $logItems->filter(function($item) use ($boardIds) {
                    $task = Task::find($item->task_id);
                    return $task && in_array($task->board_id, $boardIds);
                });
            }
            
            $allUserIds[] = $log->user_id;
            $totalTasksCompleted += $logItems->where('activity_type', 'completed')->count();
            $totalBlockedTasks += $logItems->where('activity_type', 'blocked')->count();
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
        $teamMembers = $request->get('team_members', 'all');

        $logs = Log::where('log_date', $date)
            ->with('logItems')
            ->get();

        // Filter by board if specified
        if ($boardIds !== 'all' && is_array($boardIds)) {
            $logItemIds = LogItem::whereIn('log_id', $logs->pluck('id'))
                ->get()
                ->filter(function($item) use ($boardIds) {
                    $task = Task::find($item->task_id);
                    return $task && in_array($task->board_id, $boardIds);
                })
                ->pluck('id')
                ->toArray();
            
            // Filter logs to only include those with matching log items
            $logs = $logs->filter(function($log) use ($logItemIds) {
                return $log->logItems->whereIn('id', $logItemIds)->count() > 0;
            });
        }

        // Get all users or filtered users
        $allUserIds = Log::distinct()->pluck('user_id')->toArray();
        
        if ($teamMembers !== 'all' && is_array($teamMembers)) {
            $allUserIds = array_intersect($allUserIds, $teamMembers);
        }
        
        $users = User::whereIn('ID', $allUserIds)->get();

        $tasksTouchedByUser = [];
        $tasksCompletedByUser = [];
        $workDistribution = [
            'completed' => 0,
            'in_progress' => 0,
            'blocked' => 0,
        ];

        // Calculate totals for general stats
        $totalTouched = 0;
        $totalCompleted = 0;

        foreach ($users as $user) {
            $userLog = $logs->where('user_id', $user->ID)->first();
            if ($userLog) {
                // Filter log items by board if needed
                $userLogItems = $userLog->logItems;
                if ($boardIds !== 'all' && is_array($boardIds)) {
                    $userLogItems = $userLogItems->filter(function($item) use ($boardIds) {
                        $task = Task::find($item->task_id);
                        return $task && in_array($task->board_id, $boardIds);
                    });
                }
                
                $touched = $userLogItems->count();
                $completed = $userLogItems->where('activity_type', 'completed')->count();
                
                $tasksTouchedByUser[$user->ID] = $touched;
                $tasksCompletedByUser[$user->ID] = $completed;
                
                $totalTouched += $touched;
                $totalCompleted += $completed;
            } else {
                $tasksTouchedByUser[$user->ID] = 0;
                $tasksCompletedByUser[$user->ID] = 0;
            }
        }

        // Work distribution
        foreach ($logs as $log) {
            $logItems = $log->logItems;
            
            // Filter by board if needed
            if ($boardIds !== 'all' && is_array($boardIds)) {
                $logItems = $logItems->filter(function($item) use ($boardIds) {
                    $task = Task::find($item->task_id);
                    return $task && in_array($task->board_id, $boardIds);
                });
            }
            
            foreach ($logItems as $item) {
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

        // Determine if we should show general stats (more than 100 users or single member selected)
        $showGeneralStats = false;
        $generalStats = null;
        
        if ($users->count() > 100 || ($teamMembers !== 'all' && is_array($teamMembers) && count($teamMembers) === 1)) {
            $showGeneralStats = true;
            $generalStats = [
                'all_touched' => $totalTouched,
                'all_completed' => $totalCompleted,
            ];
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
            'show_general_stats' => $showGeneralStats,
            'general_stats' => $generalStats,
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

                // Filter by board if specified
                if ($boardIds !== 'all' && is_array($boardIds) && !in_array($task->board_id, $boardIds)) {
                    continue;
                }

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
        $boardIds = $request->get('boards', 'all');
        
        $logs = Log::where('log_date', $date)
            ->with(['logItems', 'user'])
            ->get();

        $blockedTasks = [];
        
        foreach ($logs as $log) {
            foreach ($log->logItems->where('activity_type', 'blocked') as $item) {
                $task = Task::find($item->task_id);
                if (!$task) continue;

                // Filter by board if specified
                if ($boardIds !== 'all' && is_array($boardIds) && !in_array($task->board_id, $boardIds)) {
                    continue;
                }

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
        $userId = get_current_user_id();
        
        // Get boards accessible to the current user
        $boards = Board::byAccessUser($userId)
            ->whereNull('archived_at')
            ->orderBy('title', 'asc')
            ->get();

        return $boards->map(function($board) {
            return [
                'id' => $board->id,
                'title' => $board->title,
                'name' => $board->title, // For compatibility
            ];
        })->values();
    }

    /**
     * Send reminders to team members
     */
    public function sendReminders(Request $request)
    {
        $date = $request->get('date', date('Y-m-d'));
        $teamMembers = $request->get('team_members', []);
        
        if (empty($teamMembers) || !is_array($teamMembers)) {
            return [
                'success' => false,
                'message' => 'No team members selected'
            ];
        }

        $logs = Log::where('log_date', $date)
            ->whereIn('user_id', $teamMembers)
            ->with('logItems')
            ->get();

        $remindersSent = 0;
        $errors = [];

        foreach ($teamMembers as $memberId) {
            $user = User::find($memberId);
            if (!$user) {
                $errors[] = "User ID {$memberId} not found";
                continue;
            }

            $userLog = $logs->where('user_id', $memberId)->first();
            $hasUpdate = $userLog !== null;
            $blockedCount = $userLog ? $userLog->logItems->where('activity_type', 'blocked')->count() : 0;

            // Only send reminder if user needs one
            if (!$hasUpdate || $blockedCount > 0) {
                $subject = 'Task Update Reminder';
                $message = "Hello {$user->display_name},\n\n";
                
                if (!$hasUpdate) {
                    $message .= "This is a reminder that you haven't submitted your daily update for {$date}.\n\n";
                }
                
                if ($blockedCount > 0) {
                    $message .= "You have {$blockedCount} blocked task(s) that need attention.\n\n";
                }
                
                $message .= "Please submit your update at your earliest convenience.\n\n";
                $message .= "Thank you!";

                $emailSent = wp_mail(
                    $user->user_email,
                    $subject,
                    $message,
                    [
                        'Content-Type: text/plain; charset=UTF-8',
                        'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
                    ]
                );

                if ($emailSent) {
                    $remindersSent++;
                } else {
                    $errors[] = "Failed to send reminder to {$user->display_name}";
                }
            }
        }

        return [
            'success' => true,
            'reminders_sent' => $remindersSent,
            'total_members' => count($teamMembers),
            'errors' => $errors
        ];
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

