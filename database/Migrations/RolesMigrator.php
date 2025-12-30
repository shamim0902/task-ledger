<?php

namespace TaskLedger\Database\Migrations;

class RolesMigrator extends Migrator
{
    public static string $tableName = 'task_ledger_roles';

    public static function getSqlSchema(): string
    {
        return <<<SQL
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `slug` VARCHAR(100) NOT NULL UNIQUE,
            `description` TEXT NULL,
            `is_system` TINYINT(1) DEFAULT 0 COMMENT 'System roles cannot be deleted',
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY `slug_index` (`slug`),
            KEY `is_system_index` (`is_system`)
        SQL;
    }
}

