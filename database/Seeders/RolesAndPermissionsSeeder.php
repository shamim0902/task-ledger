<?php

namespace TaskLedger\Database\Seeders;

use TaskLedger\App\Models\Role;
use TaskLedger\App\Models\User;
use TaskLedger\App\Services\PermissionService;

class RolesAndPermissionsSeeder
{
    public static function seed()
    {
        // Create default roles (system roles only)
        $roles = self::createRoles();
        
        // Assign Member role to all existing users by default
        self::assignMemberRoleToAllUsers($roles['member']);
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
            // Use firstOrCreate to avoid duplicates, but update description if it exists
            $role = Role::firstOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );
            
            // Update description if role already exists but description is different
            if ($role->description !== $roleData['description']) {
                $role->update(['description' => $roleData['description']]);
            }
            
            // Ensure is_system is set correctly
            if (!$role->is_system) {
                $role->update(['is_system' => true]);
            }
            
            $createdRoles[$roleData['slug']] = $role;
        }
        
        return $createdRoles;
    }

    private static function assignMemberRoleToAllUsers($memberRole)
    {
        if (!$memberRole) {
            return;
        }

        // Get all WordPress users
        $users = get_users(['fields' => ['ID']]);
        
        foreach ($users as $user) {
            $userId = $user->ID;
            
            // Check if user already has a role assigned
            $hasRole = \TaskLedger\App\Models\UserRoleProject::where('user_id', $userId)->exists();
            
            // If user has no role, assign Member role by default
            if (!$hasRole) {
                PermissionService::assignRole($userId, $memberRole->id);
            }
        }
    }
}

