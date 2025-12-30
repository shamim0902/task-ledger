<?php

namespace TaskLedger\App\Models;

class LogItem extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    public $timestamps = false;

    protected $table = 'task_ledger_log_items';

    protected $fillable = [
        'log_id',
        'task_type',
        'task_id',
        'activity_type',
        'complete_weight',
        'time_spent',
        'note',
        'block_reason',
        'reviewed',
        'reviewed_at',
        'reviewed_by',
    ];

    public function log()
    {
        return $this->belongsTo(Log::class, 'log_id');
    }

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }
}