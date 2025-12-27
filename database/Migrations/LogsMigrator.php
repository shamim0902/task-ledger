<?php

namespace TaskLedger\Database\Migrations;

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
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY `log_date_index` (`log_date`)
        SQL;
    }
}
