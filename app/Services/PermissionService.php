<?php

namespace TaskLedger\App\Services;

use TaskLedger\App\Models\Role;
use TaskLedger\App\Models\UserRoleProject;
use TaskLedger\App\Models\ManagerMember;

class PermissionService
{
    /**
     * Check if a manager can manage a target user
     * Hierarchy: Admin can manage everyone, Manager can only manage assigned members, Member can't manage anyone
     *
     * @param int $managerId WordPress user ID of the manager
     * @param int $targetUserId WordPress user ID of the target user
     * @return bool
     */
    public static function canManageUser($managerId, $targetUserId)
    {
        // Admin can manage everyone
        if (self::isAdmin($managerId)) {
            return true;
        }

        // Manager can only manage assigned members
        if (self::isManager($managerId)) {
            return self::hasAssignedMember($managerId, $targetUserId);
        }

        // Member can't manage anyone
        return false;
    }

    /**
     * Check if a manager has a specific member assigned
     *
     * @param int $managerId WordPress user ID of the manager
     * @param int $memberId WordPress user ID of the member
     * @return bool
     */
    public static function hasAssignedMember($managerId, $memberId)
    {
        return ManagerMember::where('manager_id', $managerId)
            ->where('member_id', $memberId)
            ->exists();
    }

    /**
     * Get all roles for a user
     *
     * @param int $userId WordPress user ID
     * @return \Illuminate\Support\Collection
     */
    public static function getUserRoles($userId)
    {
        $userRoleProjects = UserRoleProject::where('user_id', $userId)
            ->with('role')
            ->get();
        
        return $userRoleProjects->map(function($urp) {
            return $urp->role;
        })->unique('id');
    }

    /**
     * Check if user can access a specific board
     * For now, all users with roles can access all boards
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

        // If user has any role, they can access boards
        return UserRoleProject::where('user_id', $userId)->exists();
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
            if (class_exists('\FluentBoards\App\Models\Board')) {
                return \FluentBoards\App\Models\Board::pluck('id')->toArray();
            }
            return [];
        }

        // All users with roles have access to all boards
        if (UserRoleProject::where('user_id', $userId)->exists()) {
            if (class_exists('\FluentBoards\App\Models\Board')) {
                return \FluentBoards\App\Models\Board::pluck('id')->toArray();
            }
        }

        return [];
    }

    /**
     * Check if user is Admin
     *
     * @param int $userId WordPress user ID
     * @return bool
     */
    public static function isAdmin($userId)
    {
        if(current_user_can('manage_options')) {
            return true;
        }
        
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
     * @return bool
     */
    public static function isManager($userId)
    {
        $managerRole = Role::where('slug', 'manager')->first();
        if (!$managerRole) {
            return false;
        }

        return UserRoleProject::where('user_id', $userId)
            ->where('role_id', $managerRole->id)
            ->exists();
    }

    /**
     * Check if user is Member
     *
     * @param int $userId WordPress user ID
     * @return bool
     */
    public static function isMember($userId)
    {
        $memberRole = Role::where('slug', 'member')->first();
        if (!$memberRole) {
            return false;
        }

        return UserRoleProject::where('user_id', $userId)
            ->where('role_id', $memberRole->id)
            ->exists();
    }

    /**
     * Get all members assigned to a manager
     *
     * @param int $managerId WordPress user ID of the manager
     * @return \Illuminate\Support\Collection
     */
    public static function getManagedMembers($managerId)
    {
        return ManagerMember::where('manager_id', $managerId)
            ->with('member')
            ->get()
            ->map(function($mm) {
                return $mm->member;
            });
    }

    /**
     * Get the manager for a member
     *
     * @param int $memberId WordPress user ID of the member
     * @return \TaskLedger\App\Models\User|null
     */
    public static function getManager($memberId)
    {
        $managerMember = ManagerMember::where('member_id', $memberId)
            ->with('manager')
            ->first();

        return $managerMember ? $managerMember->manager : null;
    }

    /**
     * Assign role to user (global only, no board)
     *
     * @param int $userId WordPress user ID
     * @param int $roleId Role ID
     * @return UserRoleProject
     */
    public static function assignRole($userId, $roleId)
    {
        return UserRoleProject::firstOrCreate([
            'user_id' => $userId,
            'role_id' => $roleId,
        ]);
    }

    /**
     * Remove role from user
     *
     * @param int $userId WordPress user ID
     * @param int $roleId Role ID
     * @return bool
     */
    public static function removeRole($userId, $roleId)
    {
        return UserRoleProject::where('user_id', $userId)
            ->where('role_id', $roleId)
            ->delete();
    }

    /**
     * Assign a member to a manager
     *
     * @param int $managerId WordPress user ID of the manager
     * @param int $memberId WordPress user ID of the member
     * @return ManagerMember
     */
    public static function assignMember($managerId, $memberId)
    {
        return ManagerMember::firstOrCreate([
            'manager_id' => $managerId,
            'member_id' => $memberId,
        ]);
    }

    /**
     * Remove a member from a manager
     *
     * @param int $managerId WordPress user ID of the manager
     * @param int $memberId WordPress user ID of the member
     * @return bool
     */
    public static function removeMember($managerId, $memberId)
    {
        return ManagerMember::where('manager_id', $managerId)
            ->where('member_id', $memberId)
            ->delete();
    }
}

