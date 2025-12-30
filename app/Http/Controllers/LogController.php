<?php

namespace TaskLedger\App\Http\Controllers;

use TaskLedger\App\Models\Log;
use TaskLedger\App\Models\User;
use TaskLedger\App\Services\PermissionService;
use TaskLedger\Framework\Http\Request\Request;
use TaskLedger\Framework\Http\Response\Response;
use TaskLedger\Framework\Http\Controller;
use TaskLedger\App\Models\LogItem;
use FluentBoards\App\Models\Task;
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
        if ($log) {
            // update notes
            $log->update([
                'additional_notes' => $data['notes']
            ]);
        } else {
            $log = Log::create([
                'user_id' => $user_id,
                'log_date' => $today,
                'additional_notes' => $data['notes']
            ]);
        }

        if (!$log) {
            return Response::json([
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

            $isUpdate = LogItem::updateOrCreate(
                [
                    'log_id'   => $log->id,
                    'task_id'  => $task['task_id'] ?? $task['id'],
                    'task_type'=> 'board',
                ],
                [
                    'activity_type'   => $status,
                    'complete_weight' => $task['complete_weight'] ?? 0,
                    'note'            => $note,
                    'block_reason'    => $blockerReason,
                    'time_spent'      => $task['hours'] ?? 0,
                    'task_id'         => $task['task_id'] ?? $task['id'],
                ]
            );
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
            $task = Task::find($item->task_id);
            if ($task) {
                $item->log_id = $log->id;
                $item->status = $item->activity_type;
                $item->title = $task->title;
                $item->hours = $item->time_spent;
                $item->note = $item->note;
                $item->weight = Meta::getMetaForTask($task->id, 'weight')->meta_value ?? 1;
                $item->complete_weight = $item->complete_weight ?? 0;
                // Include blocker reason if status is blocked
                if ($item->activity_type === 'blocked') {
                    $item->blocker_reason = $item->block_reason ?? $item->note ?? '';
                } else {
                    $item->blocker_reason = '';
                }
            }
        });

        return [
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
                $task = Task::find($item->task_id);
                if ($task) {
                    return [
                        'id' => $item->id,
                        'task_id' => $task->id,
                        'title' => $task->title,
                        'status' => $item->activity_type,
                        'hours' => $item->time_spent,
                        'complete_weight' => $item->complete_weight,
                        'note' => $item->note,
                    ];
                }
                return null;
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
            return Response::json([
                'message' => 'Log item not found',
            ], 404);
        }

        // Verify the log belongs to the current user
        $log = Log::find($logItem->log_id);
        if (!$log || $log->user_id != $user_id) {
            return Response::json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $logItem->delete();

        return [
            'message' => 'Task removed from log successfully',
        ];
    }
}