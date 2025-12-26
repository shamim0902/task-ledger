<?php

namespace TaskLedger\App\Http\Controllers;

use TaskLedger\Framework\Http\Request\Request;

class WelcomeController extends Controller
{
    public function index(Request $request)
    {
        return [
            'message' => 'Welcome to WPFluent.'
        ];
    }
}
