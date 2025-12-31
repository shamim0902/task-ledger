<?php

namespace TaskLedger\App\Hooks\Handlers;

use TaskLedger\Framework\Foundation\Application;
use TaskLedger\App\Hooks\Handlers\AdminMenuHandler;
use TaskLedger\App\Services\PermissionService;

class ShortcodeHandler
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
        add_shortcode('taskledger_shortcode', [$this, 'shortcode']);
    }

    public function shortcode()
    {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            return $this->renderLoginMessage();
        }

        $userId = get_current_user_id();
        
        // Determine user role
        $isAdmin = PermissionService::isAdmin($userId);
        $isManager = PermissionService::isManager($userId);
        $isMember = PermissionService::isMember($userId) || (!$isAdmin && !$isManager);
   
        // Create a custom menu handler for shortcode with role-based filtering
        $adminMenuHandler = new AdminMenuHandler();
        
        // Add filter to restrict routes based on role
        add_filter('taskledger_shortcode_routes', function($routes) use ($isAdmin, $isManager, $isMember) {
            return $this->filterRoutesByRole($routes, $isAdmin, $isManager, $isMember);
        }, 10, 1);

        // Add user role info to localized script
        add_filter('fluentFrameworkAdmin', function($data) use ($isAdmin, $isManager, $isMember) {
            $data['userRole'] = $isAdmin ? 'admin' : ($isManager ? 'manager' : 'member');
            $data['isAdmin'] = $isAdmin;
            $data['isManager'] = $isManager;
            $data['isMember'] = $isMember;
            return $data;
        }, 10, 1);

        ob_start();
        $adminMenuHandler->render();
        return ob_get_clean();
    }

    /**
     * Filter routes based on user role
     * 
     * @param array $routes
     * @param bool $isAdmin
     * @param bool $isManager
     * @param bool $isMember
     * @return array
     */
    protected function filterRoutesByRole($routes, $isAdmin, $isManager, $isMember)
    {
        if ($isAdmin) {
            // Admin sees all routes
            return $routes;
        }

        if ($isManager) {
            // Manager sees: Dashboard (Developers) and Submissions
            return array_filter($routes, function($route) {
                $path = $route['path'] ?? '';
                $name = $route['name'] ?? '';
                // Allow dashboard (/) and review/submissions
                return $path === '/' || $name === 'review' || strpos($path, '/review') === 0;
            });
        }

        if ($isMember) {
            // Member sees only: Dashboard (Developers)
            return array_filter($routes, function($route) {
                $path = $route['path'] ?? '';
                // Only allow dashboard
                return $path === '/';
            });
        }

        return [];
    }

    /**
     * Render login message for non-logged-in users
     * 
     * @return string
     */
    protected function renderLoginMessage()
    {
        $loginUrl = wp_login_url(get_permalink());
        $registerUrl = wp_registration_url();
        
        return sprintf(
            '<div style="padding: 2rem; text-align: center; background: #f9fafb; border-radius: 8px; margin: 2rem 0;">
                <h3 style="margin: 0 0 1rem 0; color: #374151;">Please Log In</h3>
                <p style="margin: 0 0 1.5rem 0; color: #6b7280;">You must be logged in to access Task Ledger.</p>
                <div style="display: flex; gap: 1rem; justify-content: center;">
                    <a href="%s" style="padding: 0.75rem 1.5rem; background: #6366f1; color: white; text-decoration: none; border-radius: 0.5rem; font-weight: 500;">Log In</a>
                    <a href="%s" style="padding: 0.75rem 1.5rem; background: white; color: #6366f1; text-decoration: none; border-radius: 0.5rem; font-weight: 500; border: 1px solid #6366f1;">Register</a>
                </div>
            </div>',
            esc_url($loginUrl),
            esc_url($registerUrl)
        );
    }
}