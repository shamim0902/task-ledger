<?php

namespace TaskLedger\Database\Migrations;

use TaskLedger\Framework\Database\Schema;

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
            `activity_type` ENUM('in-progress','completed','blocked') NOT NULL,
            `complete_weight` DECIMAL(10,2) NULL DEFAULT 1,
            `time_spent` DECIMAL(10,2) NULL DEFAULT 0,
            `note` TEXT NULL,
            `block_reason` TEXT NULL,
            `reviewed` BOOLEAN DEFAULT FALSE,
            `reviewed_at` TIMESTAMP NULL,
            `reviewed_by` BIGINT UNSIGNED NULL,
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            KEY `log_id_index` (`log_id`),
            KEY `task_lookup_index` (`task_type`, `task_id`)
        SQL;
    }

    public static function migrate()
    {
        parent::migrate();
        self::addReviewColumnsIfNotExist();
    }

    private static function addReviewColumnsIfNotExist()
    {
        global $wpdb;
        $table = static::getTableName();
        
        if (!Schema::hasTable(static::$tableName)) {
            return;
        }

        $columns = Schema::getColumns(static::$tableName) ?: [];
        
        if (!in_array('reviewed', $columns)) {
            Schema::query("ALTER TABLE $table ADD COLUMN `reviewed` BOOLEAN DEFAULT FALSE AFTER `block_reason`");
        }
        
        if (!in_array('reviewed_at', $columns)) {
            Schema::query("ALTER TABLE $table ADD COLUMN `reviewed_at` TIMESTAMP NULL AFTER `reviewed`");
        }
        
        if (!in_array('reviewed_by', $columns)) {
            Schema::query("ALTER TABLE $table ADD COLUMN `reviewed_by` BIGINT UNSIGNED NULL AFTER `reviewed_at`");
        }
    }
}
