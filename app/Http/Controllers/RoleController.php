<?php

namespace TaskLedger\App\Http\Controllers;

use TaskLedger\App\Models\Role;
use TaskLedger\App\Services\PermissionService;
use TaskLedger\Framework\Http\Request\Request;

class RoleController extends Controller
{
    /**
     * List all roles (only system roles: Admin, Manager, Member)
     */
    public function index()
    {
        // Only WordPress admins can view roles
        if (!current_user_can('manage_options')) {
            return $this->sendError(['message' => 'You do not have permission to view roles'], 403);
        }

        $roles = Role::where('is_system', true)
            ->where('slug', '!=', 'member')
            ->withCount('users')
            ->orderBy('name', 'asc')
            ->get();
        return $roles;
    }

    /**
     * Get a specific role
     */
    public function show($id)
    {
        // Only WordPress admins can view roles
        if (!current_user_can('manage_options')) {
            return $this->sendError(['message' => 'You do not have permission to view roles'], 403);
        }

        $role = Role::withCount('users')->findOrFail($id);
        return $role;
    }
}

