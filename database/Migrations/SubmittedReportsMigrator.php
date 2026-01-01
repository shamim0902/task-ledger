<?php

namespace TaskLedger\Database\Migrations;

use TaskLedger\Framework\Database\Schema;

class SubmittedReportsMigrator extends Migrator
{
    public static string $tableName = 'task_ledger_submitted_reports';

    public static function getSqlSchema(): string
    {
        return <<<SQL
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `submitted_by` BIGINT UNSIGNED NOT NULL COMMENT 'User ID of manager/admin who submitted',
            `employee_id` BIGINT UNSIGNED NOT NULL COMMENT 'User ID of employee the report is for',
            `timeframe` VARCHAR(50) NOT NULL COMMENT 'weekly, monthly, yearly, custom',
            `start_date` DATE NOT NULL,
            `end_date` DATE NOT NULL,
            `report_data` LONGTEXT NOT NULL COMMENT 'JSON encoded report data',
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            KEY `submitted_by_index` (`submitted_by`),
            KEY `employee_id_index` (`employee_id`),
            KEY `created_at_index` (`created_at`)
        SQL;
    }
}

