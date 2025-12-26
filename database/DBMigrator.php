<?php

namespace TaskLedger\Database;

use TaskLedger\Framework\Database\Schema;

use TaskLedger\Database\Migrations\SettingsMigrator;
use TaskLedger\Database\Migrations\LogsMigrator;
use TaskLedger\Database\Migrations\LogItemsMigrator;
use TaskLedger\Database\Migrations\TasksMigrator;
use TaskLedger\Database\Migrations\TaskActivityMigrator;
use TaskLedger\Database\Migrations\TaskMetaMigrator;

class DBMigrator
{
    private static $migrations = [
        SettingsMigrator::class,
        LogsMigrator::class,
        LogItemsMigrator::class,
        TasksMigrator::class,
        TaskActivityMigrator::class,
        TaskMetaMigrator::class,
    ];

    public static function migrateUp($network_wide = false)
    {
        foreach (self::$migrations as $migrator) {
            $migrator::migrate();
        }
    }

    public static function migrateDown()
    {
        foreach (self::$migrations as $migrator) {
            $migrator::dropTable();
        }
    }
}
