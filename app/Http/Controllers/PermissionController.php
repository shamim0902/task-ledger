<?php

namespace TaskLedger\App\Http\Controllers;

use TaskLedger\App\Models\Permission;
use TaskLedger\App\Services\PermissionService;
use TaskLedger\Framework\Http\Request\Request;

class PermissionController extends Controller
{
    /**
     * List all permissions (grouped)
     */
    public function index()
    {
        $permissions = Permission::orderBy('group')->orderBy('name')->get();
        
        // Group by permission group
        $grouped = $permissions->groupBy('group');
        
        return [
            'permissions' => $permissions,
            'grouped' => $grouped,
        ];
    }

    /**
     * Get permissions by group
     */
    public function byGroup($group)
    {
        $permissions = Permission::where('group', $group)
            ->orderBy('name')
            ->get();
        
        return $permissions;
    }

    /**
     * Get current user permissions
     */
    public function getUserPermissions(Request $request)
    {
        $userId = get_current_user_id();
        $boardId = $request->get('board_id');
        
        $permissions = PermissionService::getUserPermissions($userId, $boardId);
        
        return [
            'permissions' => $permissions->pluck('slug')->toArray(),
            'is_admin' => PermissionService::isAdmin($userId),
        ];
    }
}

