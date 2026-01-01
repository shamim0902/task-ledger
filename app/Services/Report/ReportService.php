<?php

namespace TaskLedger\App\Services\Report;

use TaskLedger\App\Models\Log;
use TaskLedger\App\Models\LogItem;
use TaskLedger\App\Models\User;

class ReportService
{
    /**
     * Generate comprehensive report for a user within a date range
     *
     * @param int $userId
     * @param string $timeframe weekly|monthly|yearly
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public static function generate($userId, $timeframe, $startDate, $endDate)
    {
        $user = User::find($userId);
        if (!$user) {
            return null;
        }

        // Get all logs within date range
        $logs = Log::where('user_id', $userId)
            ->whereBetween('log_date', [$startDate, $endDate])
            ->where('status', 'submitted')
            ->orderBy('log_date', 'asc')
            ->get();

        // Get all log items for these logs
        $logItemIds = $logs->pluck('id')->toArray();
        $logItems = LogItem::whereIn('log_id', $logItemIds)->get();

        // Calculate summary statistics
        $totalSubmissions = $logs->count();
        $totalTasks = $logItems->count();
        $completedTasks = $logItems->where('activity_type', 'completed')->count();
        $blockedTasks = $logItems->where('activity_type', 'blocked')->count();
        $inProgressTasks = $logItems->where('activity_type', 'in-progress')->count();
        $totalHours = $logItems->sum('time_spent') ?? 0;
        $totalPoints = $logItems->sum('complete_weight') ?? 0;
        
        $daysWithSubmissions = $logs->pluck('log_date')->unique()->count();
        $averageHoursPerDay = $daysWithSubmissions > 0 ? round($totalHours / $daysWithSubmissions, 2) : 0;
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0;

        // Get detailed task list
        $tasks = [];
        foreach ($logItems as $item) {
            $taskTitle = 'Task #' . $item->task_id;
            
            if (class_exists('\FluentBoards\App\Models\Task')) {
                $task = \FluentBoards\App\Models\Task::find($item->task_id);
                if ($task) {
                    $taskTitle = $task->title;
                } else {
                    $taskTitle = $item->note ?: 'Task #' . $item->task_id;
                }
            } else {
                $taskTitle = $item->note ?: 'Custom Task #' . $item->task_id;
            }

            $log = $logs->firstWhere('id', $item->log_id);
            
            $tasks[] = [
                'id' => $item->id,
                'task_id' => $item->task_id,
                'title' => $taskTitle,
                'status' => $item->activity_type ?? 'in-progress',
                'hours' => (float)($item->time_spent ?? 0),
                'points' => (float)($item->complete_weight ?? 0),
                'note' => $item->note ?? '',
                'blocker_reason' => $item->block_reason ?? '',
                'date' => $log ? $log->log_date : '',
            ];
        }

        // Calculate daily trends
        $trends = [];
        foreach ($logs as $log) {
            $dayItems = $logItems->where('log_id', $log->id);
            $trends[] = [
                'date' => $log->log_date,
                'tasks_count' => $dayItems->count(),
                'hours' => $dayItems->sum('time_spent') ?? 0,
                'points' => $dayItems->sum('complete_weight') ?? 0,
                'completed' => $dayItems->where('activity_type', 'completed')->count(),
                'blocked' => $dayItems->where('activity_type', 'blocked')->count(),
            ];
        }

        // Get blocker analysis
        $blockers = [];
        $blockedItems = $logItems->where('activity_type', 'blocked');
        foreach ($blockedItems as $item) {
            $taskTitle = 'Task #' . $item->task_id;
            
            if (class_exists('\FluentBoards\App\Models\Task')) {
                $task = \FluentBoards\App\Models\Task::find($item->task_id);
                if ($task) {
                    $taskTitle = $task->title;
                }
            }
            
            $log = $logs->firstWhere('id', $item->log_id);
            
            $blockers[] = [
                'task_id' => $item->task_id,
                'title' => $taskTitle,
                'reason' => $item->block_reason ?? $item->note ?? 'No reason provided',
                'date' => $log ? $log->log_date : '',
            ];
        }

        return [
            'employee' => [
                'id' => $user->ID,
                'name' => $user->display_name ?? $user->user_nicename,
                'email' => $user->user_email,
            ],
            'timeframe' => $timeframe,
            'date_range' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'summary' => [
                'total_submissions' => $totalSubmissions,
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'blocked_tasks' => $blockedTasks,
                'in_progress_tasks' => $inProgressTasks,
                'total_hours' => round($totalHours, 2),
                'total_points' => round($totalPoints, 2),
                'completion_rate' => $completionRate,
                'average_hours_per_day' => $averageHoursPerDay,
                'days_with_submissions' => $daysWithSubmissions,
            ],
            'tasks' => $tasks,
            'trends' => $trends,
            'blockers' => $blockers,
        ];
    }

    /**
     * Format report data as HTML for email
     *
     * @param array $reportData
     * @return string
     */
    public static function formatForEmail($reportData)
    {
        if (!$reportData) {
            return '';
        }

        $employee = $reportData['employee'] ?? [];
        $summary = $reportData['summary'] ?? [];
        $tasks = $reportData['tasks'] ?? [];
        $dateRange = $reportData['date_range'] ?? [];
        $timeframe = ucfirst($reportData['timeframe'] ?? '');
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Report</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 600;">Employee ' . $timeframe . ' Report</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 30px;">
                            <h2 style="color: #111827; margin: 0 0 20px 0; font-size: 20px;">' . esc_html($employee['name'] ?? '') . '</h2>
                            <p style="color: #6b7280; margin: 0 0 20px 0; font-size: 14px;">
                                <strong>Period:</strong> ' . esc_html($dateRange['start'] ?? '') . ' to ' . esc_html($dateRange['end'] ?? '') . '
                            </p>
                            
                            <div style="background-color: #f9fafb; border-radius: 6px; padding: 20px; margin: 20px 0;">
                                <h3 style="color: #111827; margin: 0 0 15px 0; font-size: 18px;">Summary Statistics</h3>
                                <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse: collapse;">
                                    <tr>
                                        <td style="color: #6b7280; font-size: 14px; padding: 8px 0; border-bottom: 1px solid #e5e7eb;"><strong>Total Submissions:</strong></td>
                                        <td style="color: #111827; font-size: 14px; padding: 8px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">' . ($summary['total_submissions'] ?? 0) . '</td>
                                    </tr>
                                    <tr>
                                        <td style="color: #6b7280; font-size: 14px; padding: 8px 0; border-bottom: 1px solid #e5e7eb;"><strong>Tasks Worked On:</strong></td>
                                        <td style="color: #111827; font-size: 14px; padding: 8px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">' . ($summary['total_tasks'] ?? 0) . '</td>
                                    </tr>
                                    <tr>
                                        <td style="color: #6b7280; font-size: 14px; padding: 8px 0; border-bottom: 1px solid #e5e7eb;"><strong>Completed Tasks:</strong></td>
                                        <td style="color: #10b981; font-size: 14px; padding: 8px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">' . ($summary['completed_tasks'] ?? 0) . '</td>
                                    </tr>
                                    <tr>
                                        <td style="color: #6b7280; font-size: 14px; padding: 8px 0; border-bottom: 1px solid #e5e7eb;"><strong>Blocked Tasks:</strong></td>
                                        <td style="color: #ef4444; font-size: 14px; padding: 8px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">' . ($summary['blocked_tasks'] ?? 0) . '</td>
                                    </tr>
                                    <tr>
                                        <td style="color: #6b7280; font-size: 14px; padding: 8px 0; border-bottom: 1px solid #e5e7eb;"><strong>Total Hours:</strong></td>
                                        <td style="color: #111827; font-size: 14px; padding: 8px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">' . ($summary['total_hours'] ?? 0) . 'h</td>
                                    </tr>
                                    <tr>
                                        <td style="color: #6b7280; font-size: 14px; padding: 8px 0; border-bottom: 1px solid #e5e7eb;"><strong>Total Story Points:</strong></td>
                                        <td style="color: #111827; font-size: 14px; padding: 8px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">' . ($summary['total_points'] ?? 0) . '</td>
                                    </tr>
                                    <tr>
                                        <td style="color: #6b7280; font-size: 14px; padding: 8px 0; border-bottom: 1px solid #e5e7eb;"><strong>Completion Rate:</strong></td>
                                        <td style="color: #111827; font-size: 14px; padding: 8px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">' . ($summary['completion_rate'] ?? 0) . '%</td>
                                    </tr>
                                    <tr>
                                        <td style="color: #6b7280; font-size: 14px; padding: 8px 0;"><strong>Average Hours/Day:</strong></td>
                                        <td style="color: #111827; font-size: 14px; padding: 8px 0; text-align: right;">' . ($summary['average_hours_per_day'] ?? 0) . 'h</td>
                                    </tr>
                                </table>
                            </div>';

        if (!empty($tasks)) {
            $html .= '<div style="margin: 30px 0;">
                <h3 style="color: #111827; margin: 0 0 15px 0; font-size: 18px;">Task Details</h3>
                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; margin: 15px 0;">';
            
            foreach ($tasks as $task) {
                $statusColors = [
                    'completed' => '#10b981',
                    'blocked' => '#ef4444',
                    'in-progress' => '#3b82f6',
                ];
                $statusColor = $statusColors[$task['status']] ?? '#6b7280';
                
                $html .= '<tr style="border-bottom: 1px solid #e5e7eb;">
                    <td style="padding: 15px 0;">
                        <strong style="color: #111827; display: block; margin-bottom: 5px;">' . esc_html($task['title']) . '</strong>
                        <div style="color: #6b7280; font-size: 14px; margin-top: 5px;">
                            <span style="color: ' . $statusColor . ';">' . esc_html(ucfirst($task['status'])) . '</span>';
                
                if ($task['hours'] > 0) {
                    $html .= ' • ' . esc_html($task['hours']) . 'h';
                }
                if ($task['points'] > 0) {
                    $html .= ' • ' . esc_html($task['points']) . ' pts';
                }
                if (!empty($task['date'])) {
                    $html .= ' • ' . esc_html($task['date']);
                }
                
                $html .= '</div>';
                
                if (!empty($task['note'])) {
                    $html .= '<div style="color: #6b7280; font-size: 14px; margin-top: 5px; font-style: italic;">' . esc_html($task['note']) . '</div>';
                }
                
                if (!empty($task['blocker_reason'])) {
                    $html .= '<div style="color: #ef4444; font-size: 14px; margin-top: 5px;"><strong>Blocker:</strong> ' . esc_html($task['blocker_reason']) . '</div>';
                }
                
                $html .= '</td>
                </tr>';
            }
            
            $html .= '</table>
            </div>';
        }

        $html .= '</td>
                </tr>
                <tr>
                    <td style="background-color: #f9fafb; padding: 20px; text-align: center; border-top: 1px solid #e5e7eb;">
                        <p style="color: #6b7280; margin: 0; font-size: 12px;">Generated by Task Ledger</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>';

        return $html;
    }

    /**
     * Format report data as CSV
     *
     * @param array $reportData
     * @return string
     */
    public static function formatForCSV($reportData)
    {
        if (!$reportData) {
            return '';
        }

        $employee = $reportData['employee'] ?? [];
        $summary = $reportData['summary'] ?? [];
        $tasks = $reportData['tasks'] ?? [];
        $dateRange = $reportData['date_range'] ?? [];
        $timeframe = ucfirst($reportData['timeframe'] ?? '');
        
        $rows = [];
        $rows[] = 'Employee ' . $timeframe . ' Report';
        $rows[] = 'Employee: ' . ($employee['name'] ?? '');
        $rows[] = 'Period: ' . ($dateRange['start'] ?? '') . ' to ' . ($dateRange['end'] ?? '');
        $rows[] = '';
        
        $rows[] = 'Summary Statistics';
        $rows[] = 'Total Submissions,' . ($summary['total_submissions'] ?? 0);
        $rows[] = 'Tasks Worked On,' . ($summary['total_tasks'] ?? 0);
        $rows[] = 'Completed Tasks,' . ($summary['completed_tasks'] ?? 0);
        $rows[] = 'Blocked Tasks,' . ($summary['blocked_tasks'] ?? 0);
        $rows[] = 'Total Hours,' . ($summary['total_hours'] ?? 0);
        $rows[] = 'Total Story Points,' . ($summary['total_points'] ?? 0);
        $rows[] = 'Completion Rate,' . ($summary['completion_rate'] ?? 0) . '%';
        $rows[] = 'Average Hours/Day,' . ($summary['average_hours_per_day'] ?? 0);
        $rows[] = '';
        
        if (!empty($tasks)) {
            $rows[] = 'Task Details';
            $rows[] = 'Task Title,Status,Date,Hours,Story Points,Note,Blocker Reason';
            
            foreach ($tasks as $task) {
                $rows[] = [
                    '"' . str_replace('"', '""', $task['title']) . '"',
                    $task['status'],
                    $task['date'] ?? '',
                    $task['hours'] ?? 0,
                    $task['points'] ?? 0,
                    '"' . str_replace('"', '""', $task['note'] ?? '') . '"',
                    '"' . str_replace('"', '""', $task['blocker_reason'] ?? '') . '"',
                ];
            }
        }
        
        return implode("\n", array_map(function($row) {
            return is_array($row) ? implode(',', $row) : $row;
        }, $rows));
    }

    /**
     * Get available timeframes for a user (weeks/months/years with submissions)
     *
     * @param int $userId
     * @return array
     */
    public static function getAvailableTimeframes($userId)
    {
        $logs = Log::where('user_id', $userId)
            ->where('status', 'submitted')
            ->orderBy('log_date', 'desc')
            ->get();

        $weeks = [];
        $months = [];
        $years = [];

        foreach ($logs as $log) {
            $date = strtotime($log->log_date);
            
            // Week (Monday to Sunday)
            $weekStart = date('Y-m-d', strtotime('monday this week', $date));
            $weekEnd = date('Y-m-d', strtotime('sunday this week', $date));
            $weekKey = $weekStart . '_' . $weekEnd;
            
            if (!isset($weeks[$weekKey])) {
                $weeks[$weekKey] = [
                    'type' => 'weekly',
                    'start' => $weekStart,
                    'end' => $weekEnd,
                    'label' => date('M d', strtotime($weekStart)) . ' - ' . date('M d, Y', strtotime($weekEnd)),
                    'submissions_count' => 0,
                ];
            }
            $weeks[$weekKey]['submissions_count']++;
            
            // Month
            $monthStart = date('Y-m-01', $date);
            $monthEnd = date('Y-m-t', $date);
            $monthKey = $monthStart . '_' . $monthEnd;
            
            if (!isset($months[$monthKey])) {
                $months[$monthKey] = [
                    'type' => 'monthly',
                    'start' => $monthStart,
                    'end' => $monthEnd,
                    'label' => date('F Y', $date),
                    'submissions_count' => 0,
                ];
            }
            $months[$monthKey]['submissions_count']++;
            
            // Year
            $yearStart = date('Y-01-01', $date);
            $yearEnd = date('Y-12-31', $date);
            $yearKey = $yearStart . '_' . $yearEnd;
            
            if (!isset($years[$yearKey])) {
                $years[$yearKey] = [
                    'type' => 'yearly',
                    'start' => $yearStart,
                    'end' => $yearEnd,
                    'label' => date('Y', $date),
                    'submissions_count' => 0,
                ];
            }
            $years[$yearKey]['submissions_count']++;
        }

        return [
            'weekly' => array_values($weeks),
            'monthly' => array_values($months),
            'yearly' => array_values($years),
        ];
    }
}

