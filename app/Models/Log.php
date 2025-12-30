<?php

namespace TaskLedger\App\Models;

class Log extends Model
{
    protected $table = 'task_ledger_logs';

    public $timestamps = false;
    public $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'log_date',
        'community_post_id',
        'additional_notes',
        'status',
        'reviewed',
        'reviewed_at',
        'reviewed_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function logItems()
    {
        return $this->hasMany(LogItem::class, 'log_id');
    }

    public static function hasLogForToday($user_id)
    {
        return static::where('user_id', $user_id)
            ->where('log_date', date('Y-m-d'))
            ->exists();
    }
}