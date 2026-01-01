<?php

namespace TaskLedger\App\Http\Controllers;

use TaskLedger\App\Models\User;
use TaskLedger\App\Services\PermissionService;
use TaskLedger\App\Services\Report\ReportService;
use TaskLedger\Framework\Http\Request\Request;
use TaskLedger\Framework\Http\Response\Response;

class ReportController extends Controller
{
    /**
     * Calculate date range based on timeframe and selected date
     *
     * @param string $timeframe
     * @param string $selectedDate
     * @return array ['start' => string, 'end' => string]
     */
    private function calculateDateRange($timeframe, $selectedDate)
    {
        $date = strtotime($selectedDate);
        
        switch ($timeframe) {
            case 'weekly':
                // Monday to Sunday of the week containing selected date
                $start = date('Y-m-d', strtotime('monday this week', $date));
                $end = date('Y-m-d', strtotime('sunday this week', $date));
                break;
                
            case 'monthly':
                // First to last day of the month
                $start = date('Y-m-01', $date);
                $end = date('Y-m-t', $date);
                break;
                
            case 'yearly':
                // January 1 to December 31
                $start = date('Y-01-01', $date);
                $end = date('Y-12-31', $date);
                break;
                
            default:
                // Default to weekly
                $start = date('Y-m-d', strtotime('monday this week', $date));
                $end = date('Y-m-d', strtotime('sunday this week', $date));
        }
        
        return ['start' => $start, 'end' => $end];
    }

    /**
     * Generate report for a person/timeframe
     *
     * @param Request $request
     * @return array|\WP_REST_Response
     */
    public function generateReport(Request $request)
    {
        $currentUserId = get_current_user_id();
        
        if (!$currentUserId) {
            return $this->sendError(['message' => 'User not authenticated'], 401);
        }

        // Only Manager and Admin can generate reports
        $isAdmin = PermissionService::isAdmin($currentUserId);
        $isManager = PermissionService::isManager($currentUserId);
        
        if (!$isAdmin && !$isManager) {
            return $this->sendError(['message' => 'You do not have permission to generate reports'], 403);
        }

        $userId = $request->get('user_id');
        $timeframe = $request->get('timeframe', 'weekly'); // weekly, monthly, yearly
        $selectedDate = $request->get('date', date('Y-m-d'));
        
        // Validate timeframe
        if (!in_array($timeframe, ['weekly', 'monthly', 'yearly'])) {
            return $this->sendError(['message' => 'Invalid timeframe. Must be weekly, monthly, or yearly'], 400);
        }

        if (!$userId) {
            return $this->sendError(['message' => 'User ID is required'], 400);
        }

        // Check permissions: Manager can only generate reports for assigned members
        if ($isManager && !$isAdmin) {
            if (!PermissionService::hasAssignedMember($currentUserId, $userId)) {
                return $this->sendError(['message' => 'You can only generate reports for your assigned members'], 403);
            }
        }

        // Calculate date range
        $dateRange = $this->calculateDateRange($timeframe, $selectedDate);

        // Generate report
        $reportData = ReportService::generate(
            $userId,
            $timeframe,
            $dateRange['start'],
            $dateRange['end']
        );

        if (!$reportData) {
            return $this->sendError(['message' => 'Failed to generate report. User not found or no data available.'], 404);
        }

        return $reportData;
    }

    /**
     * Get available timeframes for regeneration
     *
     * @param Request $request
     * @param int $userId
     * @return array|\WP_REST_Response
     */
    public function getReportHistory(Request $request, $userId)
    {
        $currentUserId = get_current_user_id();
        
        if (!$currentUserId) {
            return $this->sendError(['message' => 'User not authenticated'], 401);
        }

        // Only Manager and Admin can view report history
        $isAdmin = PermissionService::isAdmin($currentUserId);
        $isManager = PermissionService::isManager($currentUserId);
        
        if (!$isAdmin && !$isManager) {
            return $this->sendError(['message' => 'You do not have permission to view report history'], 403);
        }

        // Check permissions: Manager can only view history for assigned members
        if ($isManager && !$isAdmin) {
            if (!PermissionService::hasAssignedMember($currentUserId, $userId)) {
                return $this->sendError(['message' => 'You can only view report history for your assigned members'], 403);
            }
        }

        $timeframes = ReportService::getAvailableTimeframes($userId);

        return $timeframes;
    }

    /**
     * Send report to admin via email
     *
     * @param Request $request
     * @return array|\WP_REST_Response
     */
    public function sendReportToAdmin(Request $request)
    {
        $currentUserId = get_current_user_id();
        
        if (!$currentUserId) {
            return $this->sendError(['message' => 'User not authenticated'], 401);
        }

        // Only Manager and Admin can send reports
        $isAdmin = PermissionService::isAdmin($currentUserId);
        $isManager = PermissionService::isManager($currentUserId);
        
        if (!$isAdmin && !$isManager) {
            return $this->sendError(['message' => 'You do not have permission to send reports'], 403);
        }

        $userId = $request->get('user_id');
        $timeframe = $request->get('timeframe', 'weekly');
        $selectedDate = $request->get('date', date('Y-m-d'));
        
        if (!$userId) {
            return $this->sendError(['message' => 'User ID is required'], 400);
        }

        // Check permissions: Manager can only send reports for assigned members
        if ($isManager && !$isAdmin) {
            if (!PermissionService::hasAssignedMember($currentUserId, $userId)) {
                return $this->sendError(['message' => 'You can only send reports for your assigned members'], 403);
            }
        }

        // Calculate date range
        $dateRange = $this->calculateDateRange($timeframe, $selectedDate);

        // Generate report
        $reportData = ReportService::generate(
            $userId,
            $timeframe,
            $dateRange['start'],
            $dateRange['end']
        );

        if (!$reportData) {
            return $this->sendError(['message' => 'Failed to generate report'], 404);
        }

        // Format as HTML email
        $emailBody = ReportService::formatForEmail($reportData);
        
        if (empty($emailBody)) {
            return $this->sendError(['message' => 'Failed to format report for email'], 500);
        }

        // Get all admin users (WordPress administrators and Task Ledger admins)
        $adminEmails = [];
        
        // Get WordPress administrators
        $wpAdmins = get_users(['role' => 'administrator']);
        foreach ($wpAdmins as $admin) {
            $adminEmails[] = $admin->user_email;
        }
        
        // Get Task Ledger admins (users with admin role)
        $allUsers = User::all();
        foreach ($allUsers as $user) {
            if (PermissionService::isAdmin($user->ID)) {
                if (!in_array($user->user_email, $adminEmails)) {
                    $adminEmails[] = $user->user_email;
                }
            }
        }

        if (empty($adminEmails)) {
            return $this->sendError(['message' => 'No admin users found to send report to'], 404);
        }

        // Send email to all admins
        $employeeName = $reportData['employee']['name'] ?? 'Employee';
        $subject = 'Employee Report: ' . $employeeName . ' - ' . ucfirst($timeframe) . ' Report';
        
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
        ];

        $emailSent = false;
        $errors = [];
        
        foreach ($adminEmails as $adminEmail) {
            $result = wp_mail($adminEmail, $subject, $emailBody, $headers);
            if ($result) {
                $emailSent = true;
            } else {
                $errors[] = $adminEmail;
            }
        }

        if ($emailSent) {
            return [
                'success' => true,
                'message' => 'Report sent to admin(s) successfully',
                'sent_to' => count($adminEmails) - count($errors),
                'errors' => $errors,
            ];
        } else {
            return $this->sendError(['message' => 'Failed to send report email'], 500);
        }
    }

    /**
     * Download report as CSV
     *
     * @param Request $request
     * @return \WP_REST_Response
     */
    public function downloadReport(Request $request)
    {
        $currentUserId = get_current_user_id();
        
        if (!$currentUserId) {
            return $this->sendError(['message' => 'User not authenticated'], 401);
        }

        // Only Manager and Admin can download reports
        $isAdmin = PermissionService::isAdmin($currentUserId);
        $isManager = PermissionService::isManager($currentUserId);
        
        if (!$isAdmin && !$isManager) {
            return $this->sendError(['message' => 'You do not have permission to download reports'], 403);
        }

        $userId = $request->get('user_id');
        $timeframe = $request->get('timeframe', 'weekly');
        $selectedDate = $request->get('date', date('Y-m-d'));
        
        if (!$userId) {
            return $this->sendError(['message' => 'User ID is required'], 400);
        }

        // Check permissions: Manager can only download reports for assigned members
        if ($isManager && !$isAdmin) {
            if (!PermissionService::hasAssignedMember($currentUserId, $userId)) {
                return $this->sendError(['message' => 'You can only download reports for your assigned members'], 403);
            }
        }

        // Calculate date range
        $dateRange = $this->calculateDateRange($timeframe, $selectedDate);

        // Generate report
        $reportData = ReportService::generate(
            $userId,
            $timeframe,
            $dateRange['start'],
            $dateRange['end']
        );

        if (!$reportData) {
            return $this->sendError(['message' => 'Failed to generate report'], 404);
        }

        // Format as CSV
        $csvContent = ReportService::formatForCSV($reportData);
        
        if (empty($csvContent)) {
            return $this->sendError(['message' => 'Failed to format report as CSV'], 500);
        }

        // Get employee name for filename
        $employeeName = $reportData['employee']['name'] ?? 'employee';
        $employeeName = sanitize_file_name($employeeName);
        $filename = 'report-' . $employeeName . '-' . $timeframe . '-' . $selectedDate . '.csv';

        // Return CSV file download
        $response = new Response();
        return $response->send($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}

