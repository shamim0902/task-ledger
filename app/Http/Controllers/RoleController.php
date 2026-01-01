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

        // Get default roles (excluding member for this endpoint)
        $allRoles = Role::getAllDefaultRoles();
        $roles = array_filter($allRoles, function($role) {
            return $role->slug !== 'member';
        });
        
        // Sort by name
        usort($roles, function($a, $b) {
            return strcmp($a->name, $b->name);
        });
        
        return array_values($roles);
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

        $role = Role::getDefaultRoleById($id);
        if (!$role) {
            return $this->sendError(['message' => 'Role not found'], 404);
        }
        
        return $role;
    }
}

