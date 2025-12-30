<?php

namespace TaskLedger\App\Http\Middleware;

use TaskLedger\App\Services\PermissionService;

class CheckPermission
{
    /**
     * Handle the request
     * 
     * @param  \TaskLedger\Framework\Http\Request\Request $request
     * @param  \Closure $next
     * @param  array $params
     * @return mixed
     */
    public function handle($request, \Closure $next, ...$params)
    {
        $userId = get_current_user_id();
        
        if (!$userId) {
            return $request->abort(401, 'Unauthorized');
        }

        // First parameter is the required permission
        if (!isset($params[0])) {
            return $request->abort(500, 'Permission not specified');
        }

        $permission = $params[0];
        
        // Second parameter (optional) is board_id from request
        $boardId = null;
        if (isset($params[1])) {
            $boardId = $params[1];
        } else {
            // Try to get board_id from request
            $boardId = $request->get('board_id');
        }

        if (PermissionService::hasPermission($userId, $permission, $boardId)) {
            return $next($request);
        }

        // Return 403 Forbidden
        return $request->abort(403, 'You do not have permission to perform this action');
    }
}

