<?php

namespace TaskLedger\Database\Migrations;

class RolePermissionsMigrator extends Migrator
{
    public static string $tableName = 'task_ledger_role_permissions';

    public static function getSqlSchema(): string
    {
        return <<<SQL
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `role_id` BIGINT UNSIGNED NOT NULL,
            `permission_id` BIGINT UNSIGNED NOT NULL,
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `role_permission_unique` (`role_id`, `permission_id`),
            KEY `role_id_index` (`role_id`),
            KEY `permission_id_index` (`permission_id`)
        SQL;
    }
}

