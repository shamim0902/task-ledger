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
        // Only WordPress admins can view user roles
        if (!current_user_can('manage_options')) {
            return $this->sendError(['message' => 'You do not have permission to view user roles'], 403);
        }

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
        // Only WordPress admins can assign roles
        if (!current_user_can('manage_options')) {
            return $this->sendError(['message' => 'You do not have permission to assign roles'], 403);
        }

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
        // Only WordPress admins can remove roles
        if (!current_user_can('manage_options')) {
            return $this->sendError(['message' => 'You do not have permission to remove roles'], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,ID',
            'role_id' => 'required|exists:task_ledger_roles,id',
        ]);

        $userId = $request->get('user_id');
        $roleId = $request->get('role_id');

        // Get user and role info for better error messages
        $user = User::find($userId);
        $role = Role::find($roleId);
        
        if (!$user) {
            return $this->sendError(['message' => 'User not found'], 404);
        }
        
        if (!$role) {
            return $this->sendError(['message' => 'Role not found'], 404);
        }

        // Check if user is trying to remove their own admin role (prevent)
        if ($role->slug === 'admin' && $userId == get_current_user_id()) {
            return $this->sendError(['message' => 'You cannot remove your own admin role. Please ask another admin to remove it.'], 400);
        }

        // Check if assignment exists before trying to delete
        $assignment = UserRoleProject::where('user_id', $userId)
            ->where('role_id', $roleId)
            ->first();

        if (!$assignment) {
            $userName = $user->display_name ?? $user->user_nicename ?? 'User';
            return $this->sendError(['message' => "{$userName} does not have the {$role->name} role assigned"], 404);
        }

        $deleted = PermissionService::removeRole($userId, $roleId);

        // delete() returns number of rows deleted, so check if > 0
        if ($deleted > 0) {
            $userName = $user->display_name ?? $user->user_nicename ?? 'User';
            return [
                'message' => "{$role->name} role removed successfully from {$userName}",
                'success' => true,
            ];
        }

        return $this->sendError(['message' => 'Failed to remove role assignment. Please try again.'], 500);
    }

    /**
     * Get all users with a specific role
     */
    public function getUsersByRole($roleId)
    {
        // Only WordPress admins can view users by role
        if (!current_user_can('manage_options')) {
            return $this->sendError(['message' => 'You do not have permission to view users by role'], 403);
        }

        $assignments = UserRoleProject::where('role_id', $roleId)
            ->with(['user', 'role'])
            ->get();

        $users = $assignments->map(function($assignment) {
            return [
                'id' => $assignment->id,
                'user_id' => $assignment->user_id,
                'role_id' => $assignment->role_id,
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
        // Only WordPress admins can view all users with roles
        if (!current_user_can('manage_options')) {
            return $this->sendError(['message' => 'You do not have permission to view users'], 403);
        }

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

    /**
     * Get all manager-member assignments (for hierarchy display)
     */
    public function getAllAssignments()
    {
        $assignments = ManagerMember::with(['manager', 'member'])->get();
        
        return $assignments->map(function($assignment) {
            return [
                'id' => $assignment->id,
                'manager_id' => $assignment->manager_id,
                'member_id' => $assignment->member_id,
                'manager' => [
                    'ID' => $assignment->manager->ID,
                    'display_name' => $assignment->manager->display_name,
                    'user_nicename' => $assignment->manager->user_nicename,
                    'user_email' => $assignment->manager->user_email,
                ],
                'member' => [
                    'ID' => $assignment->member->ID,
                    'display_name' => $assignment->member->display_name,
                    'user_nicename' => $assignment->member->user_nicename,
                    'user_email' => $assignment->member->user_email,
                ],
            ];
        });
    }
}

