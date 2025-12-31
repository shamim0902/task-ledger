<?php

namespace TaskLedger\App\Http\Policies;

use TaskLedger\Framework\Http\Request\Request;

class UserPolicy extends Policy
{
    /**
     * Check user permission for any method.
     * Allows all logged-in users to access routes.
     * 
     * @param  \TaskLedger\Framework\Http\Request\Request $request
     * @return bool
     */
    public function verifyRequest(Request $request, ...$args)
    {
        $user = $request->user();
        return $user && $user->ID > 0;
    }

    /**
     * Check user permission for the current method.
     * 
     * @param  \TaskLedger\Framework\Http\Request\Request $request
     * @return bool
     */
    public function create(Request $request, ...$args)
    {
        return $this->verifyRequest($request, ...$args);
    }

    /**
     * Check user permission for the current method.
     * 
     * @param  \TaskLedger\Framework\Http\Request\Request $request
     * @return bool
     */
    public function update(Request $request, ...$args)
    {
        return $this->verifyRequest($request, ...$args);
    }
}
