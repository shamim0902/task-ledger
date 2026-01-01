<?php

namespace TaskLedger\App\Http\Controllers;

use TaskLedger\App\Models\User;
use TaskLedger\App\Models\SubmittedReport;
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
        $reportData = $request->get('report_data');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
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
        if ($timeframe === 'custom' && $startDate && $endDate) {
            $dateRange = ['start' => $startDate, 'end' => $endDate];
        } else {
            $dateRange = $this->calculateDateRange($timeframe, $selectedDate);
        }

        // Use provided report data or generate new one
        if (!$reportData) {
            $reportData = ReportService::generate(
                $userId,
                $timeframe,
                $dateRange['start'],
                $dateRange['end']
            );

            if (!$reportData) {
                return $this->sendError(['message' => 'Failed to generate report'], 404);
            }
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
            // Save submitted report to database
            try {
                SubmittedReport::create([
                    'submitted_by' => $currentUserId,
                    'employee_id' => $userId,
                    'timeframe' => $timeframe,
                    'start_date' => $dateRange['start'],
                    'end_date' => $dateRange['end'],
                    'report_data' => json_encode($reportData),
                ]);
            } catch (\Exception $e) {
                // Log error but don't fail the request
                error_log('Task Ledger: Failed to save submitted report: ' . $e->getMessage());
            }

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
     * Get submitted reports for the current user
     *
     * @param Request $request
     * @return array|\WP_REST_Response
     */
    public function getSubmittedReports(Request $request)
    {
        $currentUserId = get_current_user_id();
        
        if (!$currentUserId) {
            return $this->sendError(['message' => 'User not authenticated'], 401);
        }

        // Only Manager and Admin can view submitted reports
        $isAdmin = PermissionService::isAdmin($currentUserId);
        $isManager = PermissionService::isManager($currentUserId);
        
        if (!$isAdmin && !$isManager) {
            return $this->sendError(['message' => 'You do not have permission to view submitted reports'], 403);
        }

        try {
            $submittedReports = SubmittedReport::where('submitted_by', $currentUserId)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($report) {
                    $employee = User::find($report->employee_id);
                    
                    // Handle created_at - it might be a string or DateTime
                    $createdAt = $report->created_at;
                    if ($createdAt instanceof \DateTime || $createdAt instanceof \DateTimeInterface) {
                        $createdAt = $createdAt->format('Y-m-d H:i:s');
                    } elseif (is_string($createdAt)) {
                        // Already a string, use as is
                    } else {
                        $createdAt = null;
                    }
                    
                    return [
                        'id' => $report->id,
                        'employee_id' => $report->employee_id,
                        'employee_name' => $employee ? $employee->display_name : 'Unknown',
                        'timeframe' => $report->timeframe,
                        'start_date' => $report->start_date,
                        'end_date' => $report->end_date,
                        'created_at' => $createdAt,
                        'summary' => json_decode($report->report_data, true)['summary'] ?? null,
                    ];
                });

            return $submittedReports->values()->toArray();
        } catch (\Exception $e) {
            error_log('Task Ledger: Error fetching submitted reports: ' . $e->getMessage());
            return [];
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

