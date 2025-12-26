<?php

namespace TaskLedger\Database\Migrations;

class LogItemsMigrator extends Migrator
{
    public static string $tableName = 'task_ledger_log_items';

    public static function getSqlSchema(): string
    {
        return <<<SQL
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `log_id` BIGINT UNSIGNED NOT NULL,
            `task_type` ENUM('board','manual') NOT NULL,
            `task_id` BIGINT UNSIGNED NOT NULL,
            `activity_type` ENUM('worked','completed','blocked') NOT NULL,
            `progress_delta` TINYINT UNSIGNED NULL COMMENT 'Daily progress change (0-100)',
            `note` TEXT NULL,
            `block_reason` TEXT NULL,
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            KEY `log_id_index` (`log_id`),
            KEY `task_lookup_index` (`task_type`, `task_id`)
        SQL;
    }
}
