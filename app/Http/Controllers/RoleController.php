<?php

namespace TaskLedger\App\Http\Controllers;

use TaskLedger\App\Models\Role;
use TaskLedger\App\Services\PermissionService;
use TaskLedger\Framework\Http\Request\Request;

class RoleController extends Controller
{
    /**
     * List all roles
     */
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return $roles;
    }

    /**
     * Get a specific role
     */
    public function show($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        return $role;
    }

    /**
     * Create a new role
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:task_ledger_roles,slug',
            'description' => 'nullable|string',
        ]);

        $role = Role::create([
            'name' => $request->get('name'),
            'slug' => $request->get('slug'),
            'description' => $request->get('description'),
            'is_system' => false,
        ]);

        return [
            'role' => $role->load('permissions'),
            'message' => 'Role created successfully',
        ];
    }

    /**
     * Update a role
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        // Prevent editing system roles
        if ($role->is_system) {
            return [
                'error' => 'System roles cannot be modified',
            ];
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:task_ledger_roles,slug,' . $id,
            'description' => 'nullable|string',
        ]);

        $role->update([
            'name' => $request->get('name'),
            'slug' => $request->get('slug'),
            'description' => $request->get('description'),
        ]);

        return [
            'role' => $role->load('permissions'),
            'message' => 'Role updated successfully',
        ];
    }

    /**
     * Delete a role
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        // Prevent deleting system roles
        if ($role->is_system) {
            return [
                'error' => 'System roles cannot be deleted',
            ];
        }

        $role->delete();

        return [
            'message' => 'Role deleted successfully',
        ];
    }

    /**
     * Get permissions for a role
     */
    public function getPermissions($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        return [
            'role' => $role,
            'permissions' => $role->permissions,
        ];
    }

    /**
     * Update permissions for a role
     */
    public function updatePermissions(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        // Prevent modifying system role permissions (optional - you may want to allow this)
        // if ($role->is_system) {
        //     return [
        //         'error' => 'System role permissions cannot be modified',
        //     ];
        // }

        $request->validate([
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'exists:task_ledger_permissions,id',
        ]);

        $permissionIds = $request->get('permission_ids', []);
        $role->permissions()->sync($permissionIds);

        return [
            'role' => $role->load('permissions'),
            'message' => 'Permissions updated successfully',
        ];
    }
}

