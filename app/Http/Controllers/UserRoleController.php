<?php

namespace TaskLedger\App\Http\Controllers;

use TaskLedger\App\Models\User;
use TaskLedger\App\Models\Role;
use TaskLedger\App\Models\UserRoleProject;
use TaskLedger\App\Services\PermissionService;
use TaskLedger\Framework\Http\Request\Request;
use TaskLedger\App\Models\ManagerMember;

class UserRoleController extends Controller
{
    /**
     * Get roles for a user
     */
    public function getUserRoles($userId)
    {
        $user = User::findOrFail($userId);
        
        $userRoleProjects = UserRoleProject::where('user_id', $userId)
            ->with(['role', 'user'])
            ->get();

        $roles = [];
        foreach ($userRoleProjects as $urp) {
            $roles[] = [
                'id' => $urp->id,
                'role' => $urp->role,
            ];
        }

        return [
            'user' => $user,
            'roles' => $roles,
        ];
    }

    /**
     * Assign role to user (global only)
     */
    public function assignRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,ID',
            'role_id' => 'required|exists:task_ledger_roles,id',
        ]);

        $userId = $request->get('user_id');
        $roleId = $request->get('role_id');

        // Check if assignment already exists
        $existing = UserRoleProject::where('user_id', $userId)
            ->where('role_id', $roleId)
            ->first();

        if ($existing) {
            return [
                'error' => 'Role assignment already exists',
                'assignment' => $existing->load(['role', 'user']),
            ];
        }

        $assignment = PermissionService::assignRole($userId, $roleId);

        return [
            'assignment' => $assignment->load(['role', 'user']),
            'message' => 'Role assigned successfully',
        ];
    }

    /**
     * Remove role from user
     */
    public function removeRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,ID',
            'role_id' => 'required|exists:task_ledger_roles,id',
        ]);

        $userId = $request->get('user_id');
        $roleId = $request->get('role_id');

        // Check if user is trying to remove their own admin role (prevent)
        $role = Role::find($roleId);
        if ($role && $role->slug === 'admin' && $userId == get_current_user_id()) {
            return [
                'error' => 'You cannot remove your own admin role',
            ];
        }

        $deleted = PermissionService::removeRole($userId, $roleId);

        if ($deleted) {
            return [
                'message' => 'Role removed successfully',
            ];
        }

        return [
            'error' => 'Role assignment not found',
        ];
    }

    /**
     * Get all users with a specific role
     */
    public function getUsersByRole($roleId)
    {
        $assignments = UserRoleProject::where('role_id', $roleId)
            ->with(['user', 'role'])
            ->get();

        $users = $assignments->map(function($assignment) {
            return [
                'user' => $assignment->user,
            ];
        });

        return [
            'users' => $users,
            'role' => Role::find($roleId),
        ];
    }

    /**
     * Assign a member to a manager
     */
    public function assignMember(Request $request)
    {
        $request->validate([
            'manager_id' => 'required|exists:users,ID',
            'member_id' => 'required|exists:users,ID',
        ]);

        $managerId = $request->get('manager_id');
        $memberId = $request->get('member_id');

        // Verify manager has manager role
        if (!PermissionService::isManager($managerId)) {
            return [
                'error' => 'User must have manager role to assign members',
            ];
        }

        // All users are members by default, no need to verify member role

        // Check if assignment already exists
        $existing = \TaskLedger\App\Models\ManagerMember::where('manager_id', $managerId)
            ->where('member_id', $memberId)
            ->first();

        if ($existing) {
            return [
                'error' => 'Member is already assigned to this manager',
                'assignment' => $existing->load(['manager', 'member']),
            ];
        }

        $assignment = PermissionService::assignMember($managerId, $memberId);

        return [
            'assignment' => $assignment->load(['manager', 'member']),
            'message' => 'Member assigned to manager successfully',
        ];
    }

    /**
     * Remove a member from a manager
     */
    public function removeMember(Request $request)
    {
        $request->validate([
            'manager_id' => 'required|exists:users,ID',
            'member_id' => 'required|exists:users,ID',
        ]);

        $managerId = $request->get('manager_id');
        $memberId = $request->get('member_id');

        $deleted = PermissionService::removeMember($managerId, $memberId);

        if ($deleted) {
            return [
                'message' => 'Member removed from manager successfully',
            ];
        }

        return [
            'error' => 'Manager-member assignment not found',
        ];
    }

    /**
     * Get all members assigned to a manager
     */
    public function getManagedMembers($managerId)
    {
        $members = PermissionService::getManagedMembers($managerId);

        return [
            'manager_id' => $managerId,
            'members' => $members,
        ];
    }

    /**
     * Get the manager for a member
     */
    public function getManager($memberId)
    {
        $manager = PermissionService::getManager($memberId);

        return [
            'member_id' => $memberId,
            'manager' => $manager,
        ];
    }

    /**
     * Get all users with their roles (for management UI)
     */
    public function getAllUsersWithRoles()
    {
        $users = User::all();
        $result = [];

        foreach ($users as $user) {
            $userRoles = UserRoleProject::where('user_id', $user->ID)
                ->with('role')
                ->get();

            $result[] = [
                'user' => $user,
                'roles' => $userRoles->map(function($urp) {
                    return [
                        'role' => $urp->role,
                    ];
                }),
            ];
        }

        return $result;
    }
}

