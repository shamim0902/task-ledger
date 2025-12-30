<?php

namespace TaskLedger\App\Hooks\Handlers;

use TaskLedger\Database\DBMigrator;
use TaskLedger\Database\Seeders\RolesAndPermissionsSeeder;
use TaskLedger\Framework\Foundation\Application;

class ActivationHandler
{
    protected $app = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }
    
    public function handle($networkWide = false)
    {
        if (!$this->app->isMultisite()) {
            return $this->handleSinglesite();
        } else {
            return $this->handleMultisite($networkWide);
        }
    }

    protected function handleSinglesite()
    {
        $this->activatePlugin();
    }

    protected function handleMultisite($networkWide)
    {
        if ($networkWide) {
            if (is_super_admin()) {
                foreach (wp_get_sites() as $blog) {
                    switch_to_blog($blog['blog_id']);
                    $this->activatePlugin();
                    restore_current_blog();
                }
            }
        } else {
            if (current_user_can('activate_plugins')) {
                $this->activatePlugin();
            }
        }
    }

    protected function activatePlugin()
    {
        // Plugin Activation Code...
        try {
            // Run migrations
            DBMigrator::migrateUp();
            
            // Seed default roles and permissions
            RolesAndPermissionsSeeder::seed();
        } catch (\Exception $e) {
            // Log error but don't output during activation
            error_log('Task Ledger activation error: ' . $e->getMessage());
            // Don't throw - allow plugin to activate even if migrations fail
            // They can be run manually later
        }
    }
}
