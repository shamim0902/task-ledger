<?php

namespace TaskLedger\App\Http\Controllers;

use TaskLedger\App\Models\User;
use TaskLedger\App\Models\Role;
use TaskLedger\App\Models\UserRoleProject;
use TaskLedger\App\Services\PermissionService;
use TaskLedger\Framework\Http\Request\Request;

class UserRoleController extends Controller
{
    /**
     * Get roles for a user (with board assignments)
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
                'board_id' => $urp->board_id,
                'is_global' => $urp->isGlobal(),
            ];
        }

        return [
            'user' => $user,
            'roles' => $roles,
        ];
    }

    /**
     * Assign role to user for a board
     */
    public function assignRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,ID',
            'role_id' => 'required|exists:task_ledger_roles,id',
            'board_id' => 'nullable|integer',
        ]);

        $userId = $request->get('user_id');
        $roleId = $request->get('role_id');
        $boardId = $request->get('board_id');

        // Check if assignment already exists
        $existing = UserRoleProject::where('user_id', $userId)
            ->where('role_id', $roleId)
            ->where(function($query) use ($boardId) {
                if ($boardId === null) {
                    $query->whereNull('board_id');
                } else {
                    $query->where('board_id', $boardId);
                }
            })
            ->first();

        if ($existing) {
            return [
                'error' => 'Role assignment already exists',
                'assignment' => $existing->load(['role', 'user']),
            ];
        }

        $assignment = PermissionService::assignRole($userId, $roleId, $boardId);

        return [
            'assignment' => $assignment->load(['role', 'user']),
            'message' => 'Role assigned successfully',
        ];
    }

    /**
     * Remove role from user for a board
     */
    public function removeRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,ID',
            'role_id' => 'required|exists:task_ledger_roles,id',
            'board_id' => 'nullable|integer',
        ]);

        $userId = $request->get('user_id');
        $roleId = $request->get('role_id');
        $boardId = $request->get('board_id');

        // Check if user is trying to remove their own admin role (prevent)
        $role = Role::find($roleId);
        if ($role && $role->slug === 'admin' && $userId == get_current_user_id()) {
            return [
                'error' => 'You cannot remove your own admin role',
            ];
        }

        $deleted = PermissionService::removeRole($userId, $roleId, $boardId);

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
    public function getUsersByRole($roleId, Request $request)
    {
        $boardId = $request->get('board_id');

        $query = UserRoleProject::where('role_id', $roleId)
            ->with(['user', 'role']);

        if ($boardId !== null) {
            $query->where(function($q) use ($boardId) {
                $q->whereNull('board_id')
                  ->orWhere('board_id', $boardId);
            });
        }

        $assignments = $query->get();

        $users = $assignments->map(function($assignment) {
            return [
                'user' => $assignment->user,
                'board_id' => $assignment->board_id,
                'is_global' => $assignment->isGlobal(),
            ];
        });

        return [
            'users' => $users,
            'role' => Role::find($roleId),
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
                        'board_id' => $urp->board_id,
                        'is_global' => $urp->isGlobal(),
                    ];
                }),
            ];
        }

        return $result;
    }
}

