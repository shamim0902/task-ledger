<?php

namespace TaskLedger\Database\Migrations;

class SettingsMigrator extends Migrator
{
    public static string $tableName = 'task_ledger_settings';

    public static function getSqlSchema(): string
    {
        return <<<SQL
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `setting_key` VARCHAR(150) NOT NULL,
            `setting_value` LONGTEXT NULL,
            `autoload` TINYINT(1) DEFAULT 0,
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `setting_key_unique` (`setting_key`)
        SQL;
    }
}
