<?php

namespace TaskLedger\Database\Migrations;

class TasksMigrator extends Migrator
{
    public static string $tableName = 'task_ledger_tasks';

    public static function getSqlSchema(): string
    {
        return <<<SQL
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(255) NOT NULL,
            `description` TEXT NULL,
            `created_by` BIGINT UNSIGNED NOT NULL,
            `assigned_to` BIGINT UNSIGNED NULL,
            `due_date` DATE NULL,
            `weight` TINYINT UNSIGNED DEFAULT 1 COMMENT 'Task size / effort',
            `progress` TINYINT UNSIGNED DEFAULT 0 COMMENT 'Completion percentage',
            `sync_to_board` TINYINT(1) DEFAULT 0,
            `board_task_id` BIGINT UNSIGNED NULL,
            `status` ENUM('open','completed','blocked') DEFAULT 'open',
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY `assigned_to_index` (`assigned_to`),
            KEY `board_task_index` (`board_task_id`)
        SQL;
    }
}
