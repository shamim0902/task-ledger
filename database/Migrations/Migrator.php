<?php

namespace TaskLedger\Database\Migrations;
use TaskLedger\Framework\Database\Schema;

abstract class Migrator
{
    public static string $tableName = '';

     /**
     * Migrate the table.
     *
     * @return void
     */

     public static function migrate()
     {
        // Only output in CLI mode, not during web activation
        if (defined('WP_CLI') && WP_CLI) {
            echo "Migrating Table: " . static::getTableName() . "\n";
        }
         Schema::createTableIfNotExist(
             static::getTableName(),
             static::getSqlSchema()
         );
 
     }

     public static function getTableName(bool $withPrefix = true): string
     {
         return ($withPrefix ? static::getDbPrefix() : '') . static::$tableName;
     }

     public static function getDbPrefix(): string
     {
         global $wpdb;
         return $wpdb->prefix;
     }
 
     public static function getCharsetCollate(): string
     {
         global $wpdb;
         return $wpdb->get_charset_collate();
     }

    public static function dropTable()
    {
        // Only output in CLI mode, not during web activation
        if (defined('WP_CLI') && WP_CLI) {
            echo "Dropping Table: " . static::getTableName() . "\n";
        }
         Schema::dropTableIfExists(static::getTableName(false));
     }
 
     abstract public static function getSqlSchema(): string;
}