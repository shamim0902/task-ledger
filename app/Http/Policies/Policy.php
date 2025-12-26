<?php

namespace TaskLedger\App\Http\Policies;

use TaskLedger\App\Utils\Auth\Auth;
use TaskLedger\Framework\Http\Request\Request;
use TaskLedger\Framework\Foundation\Policy as BasePolicy;

class Policy extends BasePolicy
{
	/**
     * Check user permission for any method.
     * 
     * @param  \TaskLedger\Framework\Http\Request\Request $request
     * @return bool
     */
    public function verifyRequest(Request $request, ...$args)
    {
        return Auth::check($request, 'manage_options', ...$args);
    }	
}
