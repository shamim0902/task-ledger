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
                'description' => 'Full system access. Can manage everyone.',
                'is_system' => true,
            ],
            [
                'name' => 'Manager',
                'slug' => 'manager',
                'description' => 'Can manage assigned members only.',
                'is_system' => true,
            ],
            [
                'name' => 'Member',
                'slug' => 'member',
                'description' => 'Cannot manage anyone. Can only access own projects.',
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

