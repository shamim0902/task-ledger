<?php

namespace TaskLedger\App\Hooks\Handlers;

use TaskLedger\Framework\Foundation\Application;

class DeactivationHandler
{
    protected $app = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }
    
    public function handle()
    {
        // Unschedule monthly unreviewed reminder cron
        $timestamp = wp_next_scheduled('task_ledger_monthly_unreviewed_reminder');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'task_ledger_monthly_unreviewed_reminder');
        }
        
        // Also clear any scheduled instances (in case of multiple)
        wp_clear_scheduled_hook('task_ledger_monthly_unreviewed_reminder');
    }
}
