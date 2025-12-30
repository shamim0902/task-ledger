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
        $roles = Role::where('is_system', true)
            ->withCount('users')
            ->get();
        return $roles;
    }

    /**
     * Get a specific role
     */
    public function show($id)
    {
        $role = Role::withCount('users')->findOrFail($id);
        return $role;
    }
}

