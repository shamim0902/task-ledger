<?php

namespace TaskLedger\Database\Migrations;

class MetaMigrator extends Migrator
{
    public static string $tableName = 'task_ledger_meta';

    public static function getSqlSchema(): string
    {
        return <<<SQL
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `meta_key` VARCHAR(150) NOT NULL,
            `meta_value` LONGTEXT NULL,
            `object_type` ENUM('board','task') NOT NULL,
            `object_id` BIGINT UNSIGNED NOT NULL,
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY `object_lookup_index` (`object_type`, `object_id`)
        SQL;
    }
}
