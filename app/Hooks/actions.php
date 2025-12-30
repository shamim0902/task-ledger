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

$app->addCustomAction('exception', 'ExceptionHandler');

if (defined('WP_CLI') && WP_CLI) {
    \WP_CLI::add_command('taskledger', '\TaskLedger\App\Hooks\CLI\Commands');
}

// Auto-assign Member role to new users
$app->addAction('user_register', function($userId) {
    // Get Member role
    $memberRole = \TaskLedger\App\Models\Role::where('slug', 'member')->first();
    
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

/**
 * Enable this line if you want to use custom post types
 */

// $app->addAction('init', 'CPTHandler@registerPostTypes');
