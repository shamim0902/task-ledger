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
     * Default system roles - these are always available and cannot be deleted
     */
    public static function getDefaultRoles()
    {
        return [
            [
                'id' => 1,
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Full system access with all permissions',
                'is_system' => true,
                'created_at' => '2025-12-30 14:39:04',
                'updated_at' => '2025-12-30 14:39:04',
            ],
            [
                'id' => 2,
                'name' => 'Manager',
                'slug' => 'manager',
                'description' => 'Can manage members and view PM dashboard for assigned projects',
                'is_system' => true,
                'created_at' => '2025-12-30 14:39:04',
                'updated_at' => '2025-12-30 14:39:04',
            ],
            [
                'id' => 3,
                'name' => 'Member',
                'slug' => 'member',
                'description' => 'Can access only own projects and create daily logs',
                'is_system' => true,
                'created_at' => '2025-12-30 14:39:04',
                'updated_at' => '2025-12-30 14:39:04',
            ],
        ];
    }

    /**
     * Get all default roles as collection-like array
     */
    public static function getAllDefaultRoles()
    {
        $roles = self::getDefaultRoles();
        
        // Convert to objects that mimic Eloquent models for compatibility
        return array_map(function($roleData) {
            return (object) array_merge($roleData, [
                'users_count' => self::getUsersCountForRole($roleData['slug']),
            ]);
        }, $roles);
    }

    /**
     * Get a default role by slug
     */
    public static function getDefaultRoleBySlug($slug)
    {
        $roles = self::getDefaultRoles();
        foreach ($roles as $role) {
            if ($role['slug'] === $slug) {
                $roleObj = (object) $role;
                $roleObj->users_count = self::getUsersCountForRole($slug);
                return $roleObj;
            }
        }
        return null;
    }

    /**
     * Get a default role by ID
     */
    public static function getDefaultRoleById($id)
    {
        $roles = self::getDefaultRoles();
        foreach ($roles as $role) {
            if ($role['id'] == $id) {
                $roleObj = (object) $role;
                $roleObj->users_count = self::getUsersCountForRole($role['slug']);
                return $roleObj;
            }
        }
        return null;
    }

    /**
     * Get users count for a role (for compatibility with withCount)
     */
    private static function getUsersCountForRole($slug)
    {
        $roles = self::getDefaultRoles();
        $roleId = null;
        foreach ($roles as $role) {
            if ($role['slug'] === $slug) {
                $roleId = $role['id'];
                break;
            }
        }
        
        if (!$roleId) {
            return 0;
        }

        return \TaskLedger\App\Models\UserRoleProject::where('role_id', $roleId)->count();
    }

    /**
     * Check if a role slug is a default/system role
     */
    public static function isDefaultRole($slug)
    {
        $defaultRoles = ['admin', 'manager', 'member'];
        return in_array($slug, $defaultRoles);
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
        );
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

    /**
     * Override delete to prevent deletion of system roles
     */
    public function delete()
    {
        // Prevent deletion of default/system roles
        if (self::isDefaultRole($this->slug)) {
            throw new \Exception('System roles cannot be deleted');
        }
        
        return parent::delete();
    }
}

