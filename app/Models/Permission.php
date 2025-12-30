<?php

namespace TaskLedger\App\Models;

class Permission extends Model
{
    protected $table = 'task_ledger_permissions';
    protected $primaryKey = 'id';
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'name',
        'slug',
        'group',
        'description',
    ];

    /**
     * Get roles that have this permission
     */
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'task_ledger_role_permissions',
            'permission_id',
            'role_id'
        );
    }

    /**
     * Scope to filter by group
     */
    public function scopeInGroup($query, $group)
    {
        return $query->where('group', $group);
    }
}

