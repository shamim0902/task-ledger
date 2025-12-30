<?php

namespace TaskLedger\Database\Migrations;

class UserRoleProjectsMigrator extends Migrator
{
    public static string $tableName = 'task_ledger_user_role_projects';

    public static function getSqlSchema(): string
    {
        return <<<SQL
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `user_id` BIGINT UNSIGNED NOT NULL,
            `role_id` BIGINT UNSIGNED NOT NULL,
            `board_id` BIGINT UNSIGNED NULL COMMENT 'NULL for global role, board ID for project-specific role',
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `user_role_board_unique` (`user_id`, `role_id`, `board_id`),
            KEY `user_id_index` (`user_id`),
            KEY `role_id_index` (`role_id`),
            KEY `board_id_index` (`board_id`)
        SQL;
    }
}

