<?php

namespace TaskLedger\App\Services;

use TaskLedger\App\Models\Role;
use TaskLedger\App\Models\UserRoleProject;
use TaskLedger\App\Models\Permission;

class PermissionService
{
    /**
     * Check if user has a specific permission
     *
     * @param int $userId WordPress user ID
     * @param string $permissionSlug Permission slug to check
     * @param int|null $boardId Board ID for project-specific check (null for global)
     * @return bool
     */
    public static function hasPermission($userId, $permissionSlug, $boardId = null)
    {
        // Admin always has all permissions
        if (self::isAdmin($userId)) {
            return true;
        }

        // Get user roles (global and board-specific)
        $userRoles = self::getUserRoles($userId, $boardId);

        if (empty($userRoles)) {
            return false;
        }

        // Get permission
        $permission = Permission::where('slug', $permissionSlug)->first();
        if (!$permission) {
            return false;
        }

        // Check if any of the user's roles have this permission
        foreach ($userRoles as $role) {
            if ($role->permissions()->where('permission_id', $permission->id)->exists()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all roles for a user (global and board-specific)
     *
     * @param int $userId WordPress user ID
     * @param int|null $boardId Board ID (null for global roles only)
     * @return \Illuminate\Support\Collection
     */
    public static function getUserRoles($userId, $boardId = null)
    {
        $query = UserRoleProject::where('user_id', $userId)
            ->with('role.permissions');

        if ($boardId === null) {
            // Get only global roles
            $query->whereNull('board_id');
        } else {
            // Get both global and board-specific roles
            $query->where(function($q) use ($boardId) {
                $q->whereNull('board_id')
                  ->orWhere('board_id', $boardId);
            });
        }

        $userRoleProjects = $query->get();
        
        return $userRoleProjects->map(function($urp) {
            return $urp->role;
        })->unique('id');
    }

    /**
     * Check if user can access a specific board
     *
     * @param int $userId WordPress user ID
     * @param int $boardId Board ID
     * @return bool
     */
    public static function canAccessBoard($userId, $boardId)
    {
        // Admin can access all boards
        if (self::isAdmin($userId)) {
            return true;
        }

        // Check if user has any role for this board (global or board-specific)
        $hasRole = UserRoleProject::where('user_id', $userId)
            ->where(function($query) use ($boardId) {
                $query->whereNull('board_id')
                      ->orWhere('board_id', $boardId);
            })
            ->exists();

        return $hasRole;
    }

    /**
     * Get all boards user has access to
     *
     * @param int $userId WordPress user ID
     * @return array Array of board IDs
     */
    public static function getUserBoards($userId)
    {
        // Admin has access to all boards
        if (self::isAdmin($userId)) {
            // Return all board IDs from Fluent Boards
            if (class_exists('\FluentBoards\App\Models\Board')) {
                return \FluentBoards\App\Models\Board::pluck('id')->toArray();
            }
            return [];
        }

        // Get boards from user role assignments
        $boardIds = UserRoleProject::where('user_id', $userId)
            ->whereNotNull('board_id')
            ->distinct()
            ->pluck('board_id')
            ->toArray();

        // If user has global roles, they have access to all boards
        $hasGlobalRole = UserRoleProject::where('user_id', $userId)
            ->whereNull('board_id')
            ->exists();

        if ($hasGlobalRole) {
            // Return all board IDs
            if (class_exists('\FluentBoards\App\Models\Board')) {
                return \FluentBoards\App\Models\Board::pluck('id')->toArray();
            }
        }

        return $boardIds;
    }

    /**
     * Check if user is Admin
     *
     * @param int $userId WordPress user ID
     * @return bool
     */
    public static function isAdmin($userId)
    {
        $adminRole = Role::where('slug', 'admin')->first();
        if (!$adminRole) {
            return false;
        }

        return UserRoleProject::where('user_id', $userId)
            ->where('role_id', $adminRole->id)
            ->exists();
    }

    /**
     * Check if user is Manager
     *
     * @param int $userId WordPress user ID
     * @param int|null $boardId Board ID (null for global check)
     * @return bool
     */
    public static function isManager($userId, $boardId = null)
    {
        $managerRole = Role::where('slug', 'manager')->first();
        if (!$managerRole) {
            return false;
        }

        $query = UserRoleProject::where('user_id', $userId)
            ->where('role_id', $managerRole->id);

        if ($boardId !== null) {
            $query->where(function($q) use ($boardId) {
                $q->whereNull('board_id')
                  ->orWhere('board_id', $boardId);
            });
        }

        return $query->exists();
    }

    /**
     * Check if user is Member
     *
     * @param int $userId WordPress user ID
     * @param int|null $boardId Board ID (null for global check)
     * @return bool
     */
    public static function isMember($userId, $boardId = null)
    {
        $memberRole = Role::where('slug', 'member')->first();
        if (!$memberRole) {
            return false;
        }

        $query = UserRoleProject::where('user_id', $userId)
            ->where('role_id', $memberRole->id);

        if ($boardId !== null) {
            $query->where(function($q) use ($boardId) {
                $q->whereNull('board_id')
                  ->orWhere('board_id', $boardId);
            });
        }

        return $query->exists();
    }

    /**
     * Assign role to user for a board
     *
     * @param int $userId WordPress user ID
     * @param int $roleId Role ID
     * @param int|null $boardId Board ID (null for global role)
     * @return UserRoleProject
     */
    public static function assignRole($userId, $roleId, $boardId = null)
    {
        return UserRoleProject::firstOrCreate([
            'user_id' => $userId,
            'role_id' => $roleId,
            'board_id' => $boardId,
        ]);
    }

    /**
     * Remove role from user for a board
     *
     * @param int $userId WordPress user ID
     * @param int $roleId Role ID
     * @param int|null $boardId Board ID (null for global role)
     * @return bool
     */
    public static function removeRole($userId, $roleId, $boardId = null)
    {
        $query = UserRoleProject::where('user_id', $userId)
            ->where('role_id', $roleId);

        if ($boardId === null) {
            $query->whereNull('board_id');
        } else {
            $query->where('board_id', $boardId);
        }

        return $query->delete();
    }

    /**
     * Get all permissions for a user (across all their roles)
     *
     * @param int $userId WordPress user ID
     * @param int|null $boardId Board ID (null for global)
     * @return \Illuminate\Support\Collection
     */
    public static function getUserPermissions($userId, $boardId = null)
    {
        // Admin has all permissions
        if (self::isAdmin($userId)) {
            return Permission::all();
        }

        $roles = self::getUserRoles($userId, $boardId);
        $permissionIds = [];

        foreach ($roles as $role) {
            $rolePermissions = $role->permissions()->pluck('permission_id')->toArray();
            $permissionIds = array_merge($permissionIds, $rolePermissions);
        }

        return Permission::whereIn('id', array_unique($permissionIds))->get();
    }
}

