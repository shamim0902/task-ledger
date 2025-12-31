<?php

namespace TaskLedger\Database;

use TaskLedger\Framework\Database\Schema;

use TaskLedger\Database\Migrations\SettingsMigrator;
use TaskLedger\Database\Migrations\LogsMigrator;
use TaskLedger\Database\Migrations\LogItemsMigrator;
use TaskLedger\Database\Migrations\TasksMigrator;
use TaskLedger\Database\Migrations\TaskActivityMigrator;
use TaskLedger\Database\Migrations\TaskMetaMigrator;
use TaskLedger\Database\Migrations\MetaMigrator;
use TaskLedger\Database\Migrations\RolesMigrator;
use TaskLedger\Database\Migrations\PermissionsMigrator;
use TaskLedger\Database\Migrations\RolePermissionsMigrator;
use TaskLedger\Database\Migrations\UserRoleProjectsMigrator;
use TaskLedger\Database\Migrations\ManagerMembersMigrator;

class DBMigrator
{
    private static $migrations = [
        // Core settings
        'TaskLedger\\Database\\Migrations\\SettingsMigrator',
        
        // Task management
        'TaskLedger\\Database\\Migrations\\TasksMigrator',
        'TaskLedger\\Database\\Migrations\\TaskActivityMigrator',
        'TaskLedger\\Database\\Migrations\\TaskMetaMigrator',
        
        // Logging
        'TaskLedger\\Database\\Migrations\\LogsMigrator',
        'TaskLedger\\Database\\Migrations\\LogItemsMigrator',
        
        // Meta storage
        'TaskLedger\\Database\\Migrations\\MetaMigrator',
        
        // Roles and permissions
        'TaskLedger\\Database\\Migrations\\RolesMigrator',
        'TaskLedger\\Database\\Migrations\\PermissionsMigrator',
        'TaskLedger\\Database\\Migrations\\RolePermissionsMigrator',
        'TaskLedger\\Database\\Migrations\\UserRoleProjectsMigrator',
        
        // Manager members
        'TaskLedger\\Database\\Migrations\\ManagerMembersMigrator',
    ];

    public static function migrateUp($network_wide = false)
    {
        foreach (self::$migrations as $migratorClass) {
            if (!class_exists($migratorClass)) {
                throw new \Exception("Migration class {$migratorClass} not found. Please run 'composer dump-autoload'.");
            }
            $migratorClass::migrate();
        }
    }

    public static function migrateDown()
    {
        foreach (self::$migrations as $migratorClass) {
            if (!class_exists($migratorClass)) {
                throw new \Exception("Migration class {$migratorClass} not found. Please run 'composer dump-autoload'.");
            }
            $migratorClass::dropTable();
        }
    }
}
