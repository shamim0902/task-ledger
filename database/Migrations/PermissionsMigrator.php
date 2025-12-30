<?php

namespace TaskLedger\Database\Migrations;

class PermissionsMigrator extends Migrator
{
    public static string $tableName = 'task_ledger_permissions';

    public static function getSqlSchema(): string
    {
        return <<<SQL
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `slug` VARCHAR(100) NOT NULL UNIQUE,
            `group` VARCHAR(50) NOT NULL COMMENT 'Permission group: dashboard, tasks, members, boards, logs',
            `description` TEXT NULL,
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY `slug_index` (`slug`),
            KEY `group_index` (`group`)
        SQL;
    }
}

