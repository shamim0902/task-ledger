<?php

namespace TaskLedger\Database\Migrations;

use TaskLedger\Framework\Database\Schema;

class LogsMigrator extends Migrator
{
    public static string $tableName = 'task_ledger_logs';

    public static function getSqlSchema(): string
    {
        return <<<SQL
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `user_id` BIGINT UNSIGNED NOT NULL,
            `log_date` DATE NOT NULL,
            `community_post_id` BIGINT UNSIGNED NULL,
            `additional_notes` LONGTEXT NULL,
            `status` ENUM('submitted','draft') DEFAULT 'submitted',
            `reviewed` BOOLEAN DEFAULT FALSE,
            `reviewed_at` TIMESTAMP NULL,
            `reviewed_by` BIGINT UNSIGNED NULL,
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY `log_date_index` (`log_date`)
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
            Schema::query("ALTER TABLE $table ADD COLUMN `reviewed` BOOLEAN DEFAULT FALSE AFTER `status`");
        }
        
        if (!in_array('reviewed_at', $columns)) {
            Schema::query("ALTER TABLE $table ADD COLUMN `reviewed_at` TIMESTAMP NULL AFTER `reviewed`");
        }
        
        if (!in_array('reviewed_by', $columns)) {
            Schema::query("ALTER TABLE $table ADD COLUMN `reviewed_by` BIGINT UNSIGNED NULL AFTER `reviewed_at`");
        }
    }
}
