<?php

namespace TaskLedger\Database\Migrations;

class ManagerMembersMigrator extends Migrator
{
    public static string $tableName = 'task_ledger_manager_members';

    public static function getSqlSchema(): string
    {
        return <<<SQL
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `manager_id` BIGINT UNSIGNED NOT NULL COMMENT 'User ID of the manager',
            `member_id` BIGINT UNSIGNED NOT NULL COMMENT 'User ID of the member',
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `manager_member_unique` (`manager_id`, `member_id`),
            KEY `manager_id_index` (`manager_id`),
            KEY `member_id_index` (`member_id`)
        SQL;
    }
}

