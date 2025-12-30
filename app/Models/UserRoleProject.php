<?php

namespace TaskLedger\App\Models;

class UserRoleProject extends Model
{
    protected $table = 'task_ledger_user_role_projects';
    protected $primaryKey = 'id';
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'user_id',
        'role_id',
    ];

    /**
     * Get the user
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'ID');
    }

    /**
     * Get the role
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Scope to filter by user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}

