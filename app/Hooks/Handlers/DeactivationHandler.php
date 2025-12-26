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
        // ...
    }
}
