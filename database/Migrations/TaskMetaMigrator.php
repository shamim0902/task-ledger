<?php

namespace TaskLedger\Database\Migrations;

class TaskMetaMigrator extends Migrator
{
    public static string $tableName = 'task_ledger_task_meta';

    public static function getSqlSchema(): string
    {
        return <<<SQL
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `task_type` ENUM('board','manual') NOT NULL,
            `task_id` BIGINT UNSIGNED NOT NULL,
            `weight` TINYINT UNSIGNED DEFAULT 1,
            `progress` TINYINT UNSIGNED DEFAULT 0,
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `task_unique` (`task_type`, `task_id`)
        SQL;
    }
}
