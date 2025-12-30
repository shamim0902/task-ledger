<?php

namespace TaskLedger\App\Models;

class Role extends Model
{
    protected $table = 'task_ledger_roles';
    protected $primaryKey = 'id';
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    /**
     * Get permissions for this role
     */
    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'task_ledger_role_permissions',
            'role_id',
            'permission_id'
        );
    }

    /**
     * Get users with this role
     */
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'task_ledger_user_role_projects',
            'role_id',
            'user_id'
        )->withPivot('board_id');
    }

    /**
     * Check if role is a system role
     */
    public function isSystemRole()
    {
        return $this->is_system === true;
    }

    /**
     * Check if role is Admin
     */
    public function isAdmin()
    {
        return $this->slug === 'admin';
    }
}

