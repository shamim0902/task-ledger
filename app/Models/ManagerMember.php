<?php

namespace TaskLedger\App\Models;

class ManagerMember extends Model
{
    protected $table = 'task_ledger_manager_members';
    protected $primaryKey = 'id';
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'manager_id',
        'member_id',
    ];

    /**
     * Get the manager (user)
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id', 'ID');
    }

    /**
     * Get the member (user)
     */
    public function member()
    {
        return $this->belongsTo(User::class, 'member_id', 'ID');
    }

    /**
     * Scope to filter by manager
     */
    public function scopeForManager($query, $managerId)
    {
        return $query->where('manager_id', $managerId);
    }

    /**
     * Scope to filter by member
     */
    public function scopeForMember($query, $memberId)
    {
        return $query->where('member_id', $memberId);
    }
}

