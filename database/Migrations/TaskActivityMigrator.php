<?php

namespace TaskLedger\Database\Migrations;

class TaskActivityMigrator extends Migrator
{
    public static string $tableName = 'task_ledger_task_activity';

    public static function getSqlSchema(): string
    {
        return <<<SQL
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `task_type` ENUM('board','manual') NOT NULL,
            `task_id` BIGINT UNSIGNED NOT NULL,
            `user_id` BIGINT UNSIGNED NOT NULL,
            `log_date` DATE NOT NULL,
            `activity_type` ENUM('worked','completed','blocked') NOT NULL,
            `note` TEXT NULL,
            `block_reason` TEXT NULL,
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            KEY `task_timeline_index` (`task_type`, `task_id`),
            KEY `user_date_index` (`user_id`, `log_date`)
        SQL;
    }
}
