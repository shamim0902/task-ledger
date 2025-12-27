<?php

namespace TaskLedger\App\Http\Controllers;

use FluentBoards\App\Models\Task;
use TaskLedger\Framework\Http\Request\Request;
use TaskLedger\App\Models\Meta;

class TaskController extends Controller
{
    public function get(Request $request)
    {
        $tasks = Task::whereNull('archived_at')
        ->whereNull('parent_id')
        ->with('assignees')
        ->with('board')
        ->with('taskCustomFields')
        ->with('subtasks')
        ->get();

        $currentUser = $request->user()->ID;

        $tasks = $tasks->filter(function ($task) use ($currentUser) {
            if(Meta::getMetaForTask($task->id, 'weight')) {
                $task->weight = Meta::getMetaForTask($task->id, 'weight')->meta_value;
            } else {
                $task->weight = 8;
            }
            foreach($task->subtasks as $subtask) {
                if(Meta::getMetaForTask($subtask->id, 'weight')) {
                    $subtask->weight = Meta::getMetaForTask($subtask->id, 'weight')->meta_value;
                } else {
                    $subtask->weight = 1;
                }
            }
            return $task->assignees->contains('ID', $currentUser);
        });

        return $tasks;
    }

    public function createSubtask(Request $request)
    {
        $data = $request->all();
        $taskId = $data['task_id'];
        $parentTask = Task::find($taskId);
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
}
