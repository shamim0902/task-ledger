<?php

namespace TaskLedger\App\Http\Controllers;

use TaskLedger\App\Models\Log;
use TaskLedger\App\Models\User;

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
        $data = $request->all();

        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
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

            $isUpdate = LogItem::updateOrCreate(
                [
                    'log_id'   => $log->id,
                    'task_id'  => $task['task_id'] ?? $task['id'],
                    'task_type'=> 'board',
                ],
                [
                    'activity_type'   => $task['status'] ?? 'in-progress',
                    'complete_weight' => $task['complete_weight'],
                    'note'            => $task['note'],
                    'time_spent'      => $task['hours'],
                    'task_id'         => $task['task_id'] ?? $task['id'],
                ]
            );
        }

        return [
            'message' => 'Log saved successfully',
            'data' => $log
        ];
    }


    public function get()
    {
        return Log::all();
    }

    public function getTodayLogs()
    {
        $log = Log::where('log_date', date('Y-m-d'))
        ->with('logItems')
        ->first();

        $log->logItems->each(function ($item) use ($tasks, $log) {
            $task = Task::find($item->task_id);
            $item->log_id = $log->id;
            $item->status = $item->activity_type;
            $item->title = $task->title;
            $item->hours = $item->time_spent;
            $item->note = $item->note;
            $item->weight = Meta::getMetaForTask($task->id, 'weight')->meta_value ?? 1;
        });

        return $log;
    }
}