<?php

namespace TaskLedger\App\Http\Controllers;

use TaskLedger\App\App;

use TaskLedger\Framework\Http\Controller as BaseController;

abstract class Controller extends BaseController
{
    public function app($module = null)
    {
        return App::make($module);
    }
}
