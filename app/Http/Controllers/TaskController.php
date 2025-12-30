<?php

namespace TaskLedger\App\Http\Controllers;

use FluentBoards\App\Models\Task;
use TaskLedger\App\Services\PermissionService;
use TaskLedger\Framework\Http\Request\Request;
use TaskLedger\App\Models\Meta;
use TaskLedger\App\Models\LogItem;

class TaskController extends Controller
{
    public function get(Request $request)
    {
        $currentUser = (int) wp_get_current_user()->ID; // ensure int

        // Get all boards the user has access to
        $userBoards = \FluentBoards\App\Models\Board::byAccessUser($currentUser)
            ->pluck('id')
            ->toArray();

        if (empty($userBoards)) {
            return [];
        }

        // Get all tasks from boards the user has access to
        $tasks = Task::whereNull('archived_at')
        ->whereNull('parent_id')
        ->whereIn('board_id', $userBoards)
        ->with('assignees')
        ->with('board')
        ->with('taskCustomFields')
        ->with('subtasks')
        ->get();

        $tasks = $tasks->map(function ($task) use ($currentUser) {
            // Task weight (cache the meta call)
            $taskWeightMeta = Meta::getMetaForTask($task->id, 'weight');
            $task->weight = $taskWeightMeta ? (int)$taskWeightMeta->meta_value : 8;

            // Subtasks: calculate weight for each
            foreach ($task->subtasks as $subtask) {
                $totalCompleteWeight = LogItem::where('task_id', $subtask->id)
                    ->where('activity_type', 'completed')
                    ->sum('complete_weight');

                $subMeta = Meta::getMetaForTask($subtask->id, 'weight');
                if ($subMeta) {
                    $calculatedWeight = (int)$subMeta->meta_value - (int)$totalCompleteWeight;
                    $subtask->weight = max(0, $calculatedWeight);
                } else {
                    $subtask->weight = max(0, 1 - (int)$totalCompleteWeight);
                }
            }

            return $task;
        })->values(); // reindex numeric keys so JSON becomes [0=>...,1=>...]

        return $tasks;
    }

    public function createSubtask(Request $request)
    {
        $currentUser = (int) wp_get_current_user()->ID;
        
        // Check permission to create tasks
        if (!PermissionService::hasPermission($currentUser, 'create_task') && 
            !PermissionService::hasPermission($currentUser, 'manage_tasks')) {
            return $request->abort(403, 'You do not have permission to create tasks');
        }

        $data = $request->all();
        $taskId = $data['task_id'];
        $parentTask = Task::find($taskId);
        
        // Check if user has access to the board
        if ($parentTask && !PermissionService::isAdmin($currentUser)) {
            if (!PermissionService::canAccessBoard($currentUser, $parentTask->board_id)) {
                return $request->abort(403, 'You do not have access to this board');
            }
        }
        // check have any sub task group exist or not if not create one
        $subTaskGroup = Task::where('parent_id', $taskId)->first();
   
        if(!$subTaskGroup) {
            $subTaskGroup = $parentTask->taskMeta()->create([
                'title' =>'Subtasks Group',
                'parent_id' => $taskId,
            ]);
        }

        $groupId = $subTaskGroup->meta['subtask_group_id'];
        $boardId = $parentTask->board_id;

        $weight = $data['weight'];
        $priority = $weight > 5 ? 'high' : 'low';
        $data = [
            'parent_id' => $parentTask->id,
            'title' => $data['title'],
            'board_id' => $parentTask->board_id,
            'status' => 'open',
            'priority' => $priority,
            'due_at' => null,
            'position' => 1
        ];

        $subtask = Task::create($data);

        Meta::setMetaForTask($subtask->id, 'weight', $weight);
        $subtask->weight = $weight;
        return $subtask;

    }

    public function markSubtaskCompleted(Request $request, $id) {
        $subtask = Task::find($id);
        $subtask->status = 'closed';
        $subtask->save();
        return $subtask;
    }
}
