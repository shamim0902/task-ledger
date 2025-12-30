<?php

namespace TaskLedger\App\Hooks\Handlers;

use TaskLedger\Framework\Foundation\Application;
use TaskLedger\App\Hooks\Handlers\AdminMenuHandler;

class ShortcodeHandler
{
    public function __construct(Application $app)
    {
        add_shortcode('taskledger_shortcode', [$this, 'shortcode']);
    }

    public function shortcode()
    {
        $adminMenuHandler = new AdminMenuHandler();
        return $adminMenuHandler->render();
    }
}