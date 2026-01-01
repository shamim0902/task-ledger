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
     * Get the role (from default roles)
     * Override to always return default role, even when eager loaded
     */
    public function role()
    {
        $relation = $this->belongsTo(Role::class, 'role_id');
        
        // Override the getResults method to return default role
        $originalGetResults = $relation->getResults();
        if ($originalGetResults) {
            // If relationship was loaded, replace with default role
            $defaultRole = Role::getDefaultRoleById($originalGetResults->id ?? $this->role_id);
            if ($defaultRole) {
                return $defaultRole;
            }
        }
        
        return $relation;
    }
    
    /**
     * Get role attribute (accessor - returns default role instead of DB role)
     */
    public function getRoleAttribute()
    {
        // Always return default role, regardless of what's loaded
        $roleId = $this->getAttribute('role_id') ?? $this->attributes['role_id'] ?? null;
        if ($roleId) {
            return Role::getDefaultRoleById($roleId);
        }
        
        return null;
    }
    
    /**
     * Override getRelationValue to use default roles
     */
    public function getRelationValue($key)
    {
        if ($key === 'role') {
            $roleId = $this->getAttribute('role_id') ?? $this->attributes['role_id'] ?? null;
            if ($roleId) {
                return Role::getDefaultRoleById($roleId);
            }
            return null;
        }
        
        return parent::getRelationValue($key);
    }

    /**
     * Scope to filter by user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}

