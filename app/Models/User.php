<?php

namespace TaskLedger\App\Models;

use TaskLedger\App\Models\Model;
use TaskLedger\Framework\Database\Orm\UserProxyTrait;

class User extends Model
{
    use UserProxyTrait;

    public $timestamps = false;

    protected $table = 'users';
    
    protected $primaryKey = 'ID';

    public function posts()
    {
        return $this->hasMany(Post::class, 'post_author', 'ID');
    }

    /**
     * Get roles for this user
     */
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'task_ledger_user_role_projects',
            'user_id',
            'role_id'
        );
    }

    /**
     * Get members managed by this user (if manager)
     */
    public function managedMembers()
    {
        return $this->hasMany(ManagerMember::class, 'manager_id', 'ID');
    }

    /**
     * Get the manager for this user (if member)
     */
    public function manager()
    {
        return $this->hasOne(ManagerMember::class, 'member_id', 'ID');
    }
}
