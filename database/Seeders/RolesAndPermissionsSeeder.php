<?php

namespace TaskLedger\Database\Seeders;

use TaskLedger\App\Models\Role;
use TaskLedger\App\Models\Permission;

class RolesAndPermissionsSeeder
{
    public static function seed()
    {
        // Create default permissions
        $permissions = self::createPermissions();
        
        // Create default roles
        $roles = self::createRoles();
        
        // Assign permissions to roles
        self::assignPermissionsToRoles($roles, $permissions);
    }

    private static function createPermissions()
    {
        $permissionGroups = [
            'dashboard' => [
                ['name' => 'View Dashboard', 'slug' => 'view_dashboard', 'description' => 'Access the main dashboard'],
                ['name' => 'View PM Dashboard', 'slug' => 'view_pm_dashboard', 'description' => 'Access the Project Manager dashboard'],
                ['name' => 'View Own Dashboard', 'slug' => 'view_own_dashboard', 'description' => 'Access own personal dashboard'],
            ],
            'tasks' => [
                ['name' => 'View All Tasks', 'slug' => 'view_all_tasks', 'description' => 'View all tasks in assigned boards'],
                ['name' => 'View Own Tasks', 'slug' => 'view_own_tasks', 'description' => 'View only own tasks'],
                ['name' => 'Create Task', 'slug' => 'create_task', 'description' => 'Create new tasks'],
                ['name' => 'Edit Task', 'slug' => 'edit_task', 'description' => 'Edit existing tasks'],
                ['name' => 'Delete Task', 'slug' => 'delete_task', 'description' => 'Delete tasks'],
                ['name' => 'Manage Tasks', 'slug' => 'manage_tasks', 'description' => 'Full task management (create, edit, delete)'],
            ],
            'members' => [
                ['name' => 'Manage Members', 'slug' => 'manage_members', 'description' => 'Add/remove members from projects'],
                ['name' => 'View Members', 'slug' => 'view_members', 'description' => 'View team members'],
                ['name' => 'Add Members', 'slug' => 'add_members', 'description' => 'Add members to projects'],
                ['name' => 'Remove Members', 'slug' => 'remove_members', 'description' => 'Remove members from projects'],
            ],
            'boards' => [
                ['name' => 'View All Boards', 'slug' => 'view_all_boards', 'description' => 'View all boards'],
                ['name' => 'View Assigned Boards', 'slug' => 'view_assigned_boards', 'description' => 'View only assigned boards'],
                ['name' => 'Create Board', 'slug' => 'create_board', 'description' => 'Create new boards'],
                ['name' => 'Edit Board', 'slug' => 'edit_board', 'description' => 'Edit boards'],
                ['name' => 'Delete Board', 'slug' => 'delete_board', 'description' => 'Delete boards'],
            ],
            'logs' => [
                ['name' => 'Create Daily Log', 'slug' => 'create_daily_log', 'description' => 'Create daily work logs'],
                ['name' => 'View All Logs', 'slug' => 'view_all_logs', 'description' => 'View all team logs'],
                ['name' => 'View Own Logs', 'slug' => 'view_own_logs', 'description' => 'View only own logs'],
                ['name' => 'Edit Logs', 'slug' => 'edit_logs', 'description' => 'Edit existing logs'],
            ],
        ];

        $permissions = [];
        foreach ($permissionGroups as $group => $groupPermissions) {
            foreach ($groupPermissions as $perm) {
                $permission = Permission::firstOrCreate(
                    ['slug' => $perm['slug']],
                    [
                        'name' => $perm['name'],
                        'group' => $group,
                        'description' => $perm['description'],
                    ]
                );
                $permissions[$perm['slug']] = $permission;
            }
        }

        return $permissions;
    }

    private static function createRoles()
    {
        $roles = [
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Full system access with all permissions',
                'is_system' => true,
            ],
            [
                'name' => 'Manager',
                'slug' => 'manager',
                'description' => 'Can manage members and view PM dashboard for assigned projects',
                'is_system' => true,
            ],
            [
                'name' => 'Member',
                'slug' => 'member',
                'description' => 'Can access only own projects and create daily logs',
                'is_system' => true,
            ],
        ];

        $createdRoles = [];
        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );
            $createdRoles[$roleData['slug']] = $role;
        }

        return $createdRoles;
    }

    private static function assignPermissionsToRoles($roles, $permissions)
    {
        // Admin gets all permissions (handled in PermissionService)
        // No need to assign all permissions here

        // Manager permissions
        if (isset($roles['manager'])) {
            $managerPermissions = [
                'manage_members',
                'view_pm_dashboard',
                'view_all_tasks',
                'manage_tasks',
                'view_assigned_boards',
                'view_members',
                'add_members',
                'remove_members',
            ];

            foreach ($managerPermissions as $permSlug) {
                if (isset($permissions[$permSlug])) {
                    $roles['manager']->permissions()->syncWithoutDetaching([$permissions[$permSlug]->id]);
                }
            }
        }

        // Member permissions
        if (isset($roles['member'])) {
            $memberPermissions = [
                'view_own_tasks',
                'create_daily_log',
                'view_own_dashboard',
                'view_own_logs',
                'view_assigned_boards',
            ];

            foreach ($memberPermissions as $permSlug) {
                if (isset($permissions[$permSlug])) {
                    $roles['member']->permissions()->syncWithoutDetaching([$permissions[$permSlug]->id]);
                }
            }
        }
    }
}

