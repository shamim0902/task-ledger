<?php

namespace TaskLedger\App\Http\Controllers;

use TaskLedger\App\Models\Log;
use TaskLedger\App\Models\User;
use TaskLedger\App\Services\PermissionService;
use TaskLedger\Framework\Http\Request\Request;
use TaskLedger\Framework\Http\Response\Response;
use TaskLedger\Framework\Http\Controller;
use TaskLedger\App\Models\LogItem;
use TaskLedger\App\Models\Meta;

class LogController extends Controller
{
    public function create(Request $request)
    {
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        
        // All users can create daily logs - no permission check needed

        $data = $request->all();
        $today = date('Y-m-d');

        // 1️⃣ Find existing log for today
        $log = Log::where('user_id', $user_id)
            ->where('log_date', $today)
            ->first();
 
        // 2️⃣ Create or update log
        // Get status from request, default to 'draft' for new logs, preserve existing status for updates
        $status = $data['status'] ?? ($log ? $log->status : 'draft');
        
        if ($log) {
            // update notes and status
            $log->update([
                'additional_notes' => $data['notes'],
                'status' => $status
            ]);
        } else {
            // Create new log with status from request or 'draft' by default
            $log = Log::create([
                'user_id' => $user_id,
                'log_date' => $today,
                'additional_notes' => $data['notes'],
                'status' => $status
            ]);
        }

        if (!$log) {
            return (new Response())->sendError([
                'message' => 'Log save failed',
            ], 500);
        }

        // 3️⃣ Upsert log items
        foreach ($data['tasks'] as $task) {
            $status = $task['status'] ?? 'in-progress';
            $blockerReason = null;
            $note = $task['note'] ?? null;

            // If status is blocked, use blocker_reason field
            if ($status === 'blocked') {
                $blockerReason = $task['blocker_reason'] ?? $task['note'] ?? null;
                // If no blocker reason provided, use note as fallback
                if (!$blockerReason && $note) {
                    $blockerReason = $note;
                }
            }

            // Determine the task_id to use
            // For subtasks, use the subtask's own ID (not parent's ID)
            // This ensures each subtask gets its own unique log item
            $taskId = $task['task_id'] ?? $task['id'];
            
            // If this is a subtask (has subtask_id), use the subtask's ID as task_id
            if (isset($task['subtask_id']) && $task['subtask_id']) {
                $taskId = $task['subtask_id'];
            }

            $isUpdate = LogItem::updateOrCreate(
                [
                    'log_id'   => $log->id,
                    'task_id'  => $taskId,
                    'task_type'=> 'board',
                ],
                [
                    'activity_type'   => $status,
                    'complete_weight' => $task['complete_weight'] ?? 0,
                    'note'            => $note,
                    'block_reason'    => $blockerReason,
                    'time_spent'      => $task['hours'] ?? 0,
                    'task_id'         => $taskId,
                ]
            );
        }

        // Trigger email notification if log status is 'submitted'
        if ($status === 'submitted') {
            do_action('task_ledger_log_submitted', $log, $user_id);
        }

        return [
            'message' => 'Log saved successfully',
            'data' => $log
        ];
    }


    public function get(Request $request)
    {
        $userId = get_current_user_id();
        
        // Admin can view all logs, others can only view their own
        if (PermissionService::isAdmin($userId)) {
            return Log::all();
        }
        
        // All other users can view their own logs
        return Log::where('user_id', $userId)->get();
    }

    public function getTodayLogs()
    {
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        
        $log = Log::where('user_id', $user_id)
            ->where('log_date', date('Y-m-d'))
            ->with('logItems')
            ->first();

        if (!$log) {
            return [
                'additional_notes' => '',
                'log_items' => []
            ];
        }

        $log->logItems->each(function ($item) use ($log) {
            $item->log_id = $log->id;
            $item->status = $item->activity_type;
            $item->hours = $item->time_spent;
            $item->note = $item->note;
            $item->complete_weight = $item->complete_weight ?? 0;
            
            // Try to get task details from Fluent Boards if available
            if (class_exists('\FluentBoards\App\Models\Task')) {
                $task = \FluentBoards\App\Models\Task::find($item->task_id);
                if ($task) {
                    $item->title = $task->title;
                    $item->weight = Meta::getMetaForTask($task->id, 'weight')->meta_value ?? 1;
                } else {
                    // Task not found in Fluent Boards, use fallback
                    $item->title = $item->note ?: 'Task #' . $item->task_id;
                    $item->weight = 1;
                }
            } else {
                // Fluent Boards not installed, use fallback data
                // For custom tasks, title might be stored in note or we need to get it from elsewhere
                $item->title = $item->note ?: 'Custom Task #' . $item->task_id;
                $item->weight = 1;
            }
            
            // Include blocker reason if status is blocked
            if ($item->activity_type === 'blocked') {
                $item->blocker_reason = $item->block_reason ?? $item->note ?? '';
            } else {
                $item->blocker_reason = '';
            }
        });

        return [
            'id' => $log->id,
            'status' => $log->status ?? 'draft',
            'additional_notes' => $log->additional_notes ?? '',
            'log_items' => $log->logItems->toArray()
        ];
    }

    /**
     * Get log history with pagination and filters
     */
    public function getHistory(Request $request)
    {
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;

        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 10);

        $query = Log::where('user_id', $user_id)
            ->with('logItems')
            ->orderBy('log_date', 'desc');

        if ($startDate) {
            $query->where('log_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('log_date', '<=', $endDate);
        }

        $total = $query->count();
        $logs = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $logs->each(function ($log) {
            $tasksCount = $log->logItems->count();
            $totalHours = $log->logItems->sum('time_spent');
            $totalPoints = $log->logItems->sum('complete_weight');

            $log->tasks_count = $tasksCount;
            $log->total_hours = round($totalHours, 2);
            $log->total_points = round($totalPoints, 2);

            // Get task details
            $log->tasks = $log->logItems->map(function ($item) {
                $taskData = [
                    'id' => $item->id,
                    'task_id' => $item->task_id,
                    'status' => $item->activity_type,
                    'hours' => $item->time_spent,
                    'complete_weight' => $item->complete_weight,
                    'note' => $item->note,
                ];
                
                // Try to get task details from Fluent Boards if available
                if (class_exists('\FluentBoards\App\Models\Task')) {
                    $task = \FluentBoards\App\Models\Task::find($item->task_id);
                    if ($task) {
                        $taskData['title'] = $task->title;
                    } else {
                        // Task not found in Fluent Boards, use fallback
                        $taskData['title'] = $item->note ?: 'Task #' . $item->task_id;
                    }
                } else {
                    // Fluent Boards not installed, use fallback
                    $taskData['title'] = $item->note ?: 'Custom Task #' . $item->task_id;
                }
                
                return $taskData;
            })->filter()->values();
        });

        return [
            'logs' => $logs,
            'current_page' => (int)$page,
            'per_page' => (int)$perPage,
            'total' => $total,
            'total_pages' => ceil($total / $perPage),
        ];
    }

    /**
     * Delete a task from log
     */
    public function deleteLogItem(Request $request, $id)
    {
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;

        $logItem = LogItem::find($id);
        if (!$logItem) {
            return (new Response())->sendError([
                'message' => 'Log item not found',
            ], 404);
        }

        // Verify the log belongs to the current user
        $log = Log::find($logItem->log_id);
        if (!$log || $log->user_id != $user_id) {
            return (new Response())->sendError([
                'message' => 'Unauthorized',
            ], 403);
        }

        $logItem->delete();

        return [
            'message' => 'Task removed from log successfully',
        ];
    }

    /**
     * Delete today's log
     */
    public function deleteTodayLog()
    {
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        $today = date('Y-m-d');

        // Find today's log
        $log = Log::where('user_id', $user_id)
            ->where('log_date', $today)
            ->first();

        if (!$log) {
            return (new Response())->sendError([
                'message' => 'No log found for today',
            ], 404);
        }

        // Delete all log items first (cascade delete should handle this, but being explicit)
        LogItem::where('log_id', $log->id)->delete();

        // Delete the log
        $log->delete();

        return [
            'message' => 'Today\'s log deleted successfully',
        ];
    }
}