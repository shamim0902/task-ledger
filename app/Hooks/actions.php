<?php

include_once __DIR__ . '/logoicon.php';
use TaskLedger\App\Hooks\Handlers\ShortcodeHandler;
/**
 * All registered action's handlers should be in app\Hooks\Handlers,
 * addAction is similar to add_action and addCustomAction is just a
 * wrapper over add_action which will add a prefix to the hook name
 * using the plugin slug to make it unique in all wordpress plugins,
 * ex: $app->addCustomAction('foo', ['FooHandler', 'handleFoo']) is
 * equivalent to add_action('slug-foo', ['FooHandler', 'handleFoo']).
 */

/**
 * @var $app TaskLedger\Framework\Foundation\Application
 */

$app->addAction('admin_menu', 'AdminMenuHandler');

// Ensure manager_members table exists (auto-migrate if missing)
$app->addAction('admin_init', function() use ($app) {
    global $wpdb;
    $tableName = $wpdb->prefix . 'task_ledger_manager_members';
    if ($wpdb->get_var("SHOW TABLES LIKE '$tableName'") != $tableName) {
        try {
            // Use DBMigrator to run all migrations (it will skip existing tables)
            \TaskLedger\Database\DBMigrator::migrateUp();
        } catch (\Exception $e) {
            error_log('Task Ledger: Failed to create manager_members table: ' . $e->getMessage());
        }
    }
}, 1);

// Ensure review columns exist in logs and log_items tables
$app->addAction('admin_init', function() use ($app) {
    global $wpdb;
    
    // Check and add columns to task_ledger_logs
    $logsTable = $wpdb->prefix . 'task_ledger_logs';
    if ($wpdb->get_var("SHOW TABLES LIKE '$logsTable'") == $logsTable) {
        $columns = $wpdb->get_col("SHOW COLUMNS FROM $logsTable");
        
        if (!in_array('reviewed', $columns)) {
            $wpdb->query("ALTER TABLE $logsTable ADD COLUMN `reviewed` BOOLEAN DEFAULT FALSE AFTER `status`");
        }
        if (!in_array('reviewed_at', $columns)) {
            $wpdb->query("ALTER TABLE $logsTable ADD COLUMN `reviewed_at` TIMESTAMP NULL AFTER `reviewed`");
        }
        if (!in_array('reviewed_by', $columns)) {
            $wpdb->query("ALTER TABLE $logsTable ADD COLUMN `reviewed_by` BIGINT UNSIGNED NULL AFTER `reviewed_at`");
        }
    }
    
    // Check and add columns to task_ledger_log_items
    $logItemsTable = $wpdb->prefix . 'task_ledger_log_items';
    if ($wpdb->get_var("SHOW TABLES LIKE '$logItemsTable'") == $logItemsTable) {
        $columns = $wpdb->get_col("SHOW COLUMNS FROM $logItemsTable");
        
        if (!in_array('reviewed', $columns)) {
            $wpdb->query("ALTER TABLE $logItemsTable ADD COLUMN `reviewed` BOOLEAN DEFAULT FALSE AFTER `block_reason`");
        }
        if (!in_array('reviewed_at', $columns)) {
            $wpdb->query("ALTER TABLE $logItemsTable ADD COLUMN `reviewed_at` TIMESTAMP NULL AFTER `reviewed`");
        }
        if (!in_array('reviewed_by', $columns)) {
            $wpdb->query("ALTER TABLE $logItemsTable ADD COLUMN `reviewed_by` BIGINT UNSIGNED NULL AFTER `reviewed_at`");
        }
    }
}, 2);

$app->addCustomAction('exception', 'ExceptionHandler');

if (defined('WP_CLI') && WP_CLI) {
    \WP_CLI::add_command('taskledger', '\TaskLedger\App\Hooks\CLI\Commands');
}

// Auto-assign Member role to new users
$app->addAction('user_register', function($userId) {
    // Get Member role from default roles
    $memberRole = \TaskLedger\App\Models\Role::getDefaultRoleBySlug('member');
    
    if ($memberRole) {
        // Check if user already has a role assigned
        $hasRole = \TaskLedger\App\Models\UserRoleProject::where('user_id', $userId)->exists();
        
        // If user has no role, assign Member role by default
        if (!$hasRole) {
            \TaskLedger\App\Services\PermissionService::assignRole($userId, $memberRole->id);
        }
    }
});

//register shortcodes
$app->addAction('init', function() use ($app) {
    new ShortcodeHandler($app);
});

// Register email notification hooks
$app->addAction('init', function() {
    \TaskLedger\App\Hooks\EmailHooks::register();
    \TaskLedger\App\Hooks\CronHooks::register();
});

/**
 * Enable this line if you want to use custom post types
 */

// $app->addAction('init', 'CPTHandler@registerPostTypes');
