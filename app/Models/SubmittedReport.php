<?php

namespace TaskLedger\App\Models;

use TaskLedger\Framework\Database\Orm\Model;

class SubmittedReport extends Model
{
    protected $table = 'task_ledger_submitted_reports';

    public $timestamps = false;
    public $primaryKey = 'id';

    protected $fillable = [
        'submitted_by',
        'employee_id',
        'timeframe',
        'start_date',
        'end_date',
        'report_data',
    ];

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}

