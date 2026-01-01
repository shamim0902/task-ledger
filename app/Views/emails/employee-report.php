<?php
/**
 * Employee Report Email Template
 * 
 * Available variables:
 * - $employee: array with id, name, email
 * - $timeframe: weekly|monthly|yearly
 * - $date_range: array with start, end
 * - $summary: array with statistics
 * - $tasks: array of task details
 * - $trends: array of daily trends
 * - $blockers: array of blocked tasks
 */

$employee = $employee ?? [];
$timeframe = $timeframe ?? 'weekly';
$dateRange = $date_range ?? [];
$summary = $summary ?? [];
$tasks = $tasks ?? [];
$trends = $trends ?? [];
$blockers = $blockers ?? [];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html__('Employee Report', 'taskledger'); ?></title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 600;">
                                <?php echo esc_html__('Employee', 'taskledger'); ?> <?php echo esc_html(ucfirst($timeframe)); ?> <?php echo esc_html__('Report', 'taskledger'); ?>
                            </h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 30px;">
                            <h2 style="color: #111827; margin: 0 0 20px 0; font-size: 20px;">
                                <?php echo esc_html($employee['name'] ?? ''); ?>
                            </h2>
                            <p style="color: #6b7280; margin: 0 0 20px 0; font-size: 14px;">
                                <strong><?php echo esc_html__('Period:', 'taskledger'); ?></strong> 
                                <?php echo esc_html($dateRange['start'] ?? ''); ?> 
                                <?php echo esc_html__('to', 'taskledger'); ?> 
                                <?php echo esc_html($dateRange['end'] ?? ''); ?>
                            </p>
                            
                            <!-- Summary Statistics -->
                            <div style="background-color: #f9fafb; border-radius: 6px; padding: 20px; margin: 20px 0;">
                                <h3 style="color: #111827; margin: 0 0 15px 0; font-size: 18px;">
                                    <?php echo esc_html__('Summary Statistics', 'taskledger'); ?>
                                </h3>
                                <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse: collapse;">
                                    <tr>
                                        <td style="color: #6b7280; font-size: 14px; padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                                            <strong><?php echo esc_html__('Total Submissions:', 'taskledger'); ?></strong>
                                        </td>
                                        <td style="color: #111827; font-size: 14px; padding: 8px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">
                                            <?php echo esc_html($summary['total_submissions'] ?? 0); ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="color: #6b7280; font-size: 14px; padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                                            <strong><?php echo esc_html__('Tasks Worked On:', 'taskledger'); ?></strong>
                                        </td>
                                        <td style="color: #111827; font-size: 14px; padding: 8px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">
                                            <?php echo esc_html($summary['total_tasks'] ?? 0); ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="color: #6b7280; font-size: 14px; padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                                            <strong><?php echo esc_html__('Completed Tasks:', 'taskledger'); ?></strong>
                                        </td>
                                        <td style="color: #10b981; font-size: 14px; padding: 8px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">
                                            <?php echo esc_html($summary['completed_tasks'] ?? 0); ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="color: #6b7280; font-size: 14px; padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                                            <strong><?php echo esc_html__('Blocked Tasks:', 'taskledger'); ?></strong>
                                        </td>
                                        <td style="color: #ef4444; font-size: 14px; padding: 8px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">
                                            <?php echo esc_html($summary['blocked_tasks'] ?? 0); ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="color: #6b7280; font-size: 14px; padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                                            <strong><?php echo esc_html__('Total Hours:', 'taskledger'); ?></strong>
                                        </td>
                                        <td style="color: #111827; font-size: 14px; padding: 8px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">
                                            <?php echo esc_html($summary['total_hours'] ?? 0); ?>h
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="color: #6b7280; font-size: 14px; padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                                            <strong><?php echo esc_html__('Total Story Points:', 'taskledger'); ?></strong>
                                        </td>
                                        <td style="color: #111827; font-size: 14px; padding: 8px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">
                                            <?php echo esc_html($summary['total_points'] ?? 0); ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="color: #6b7280; font-size: 14px; padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                                            <strong><?php echo esc_html__('Completion Rate:', 'taskledger'); ?></strong>
                                        </td>
                                        <td style="color: #111827; font-size: 14px; padding: 8px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">
                                            <?php echo esc_html($summary['completion_rate'] ?? 0); ?>%
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="color: #6b7280; font-size: 14px; padding: 8px 0;">
                                            <strong><?php echo esc_html__('Average Hours/Day:', 'taskledger'); ?></strong>
                                        </td>
                                        <td style="color: #111827; font-size: 14px; padding: 8px 0; text-align: right;">
                                            <?php echo esc_html($summary['average_hours_per_day'] ?? 0); ?>h
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            
                            <?php if (!empty($tasks)): ?>
                            <!-- Task Details -->
                            <div style="margin: 30px 0;">
                                <h3 style="color: #111827; margin: 0 0 15px 0; font-size: 18px;">
                                    <?php echo esc_html__('Task Details', 'taskledger'); ?>
                                </h3>
                                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; margin: 15px 0;">
                                    <?php foreach ($tasks as $task): ?>
                                    <?php
                                    $statusColors = [
                                        'completed' => '#10b981',
                                        'blocked' => '#ef4444',
                                        'in-progress' => '#3b82f6',
                                    ];
                                    $statusColor = $statusColors[$task['status']] ?? '#6b7280';
                                    ?>
                                    <tr style="border-bottom: 1px solid #e5e7eb;">
                                        <td style="padding: 15px 0;">
                                            <strong style="color: #111827; display: block; margin-bottom: 5px;">
                                                <?php echo esc_html($task['title'] ?? ''); ?>
                                            </strong>
                                            <div style="color: #6b7280; font-size: 14px; margin-top: 5px;">
                                                <span style="color: <?php echo esc_attr($statusColor); ?>;">
                                                    <?php echo esc_html(ucfirst($task['status'] ?? '')); ?>
                                                </span>
                                                <?php if (!empty($task['hours'])): ?>
                                                    • <?php echo esc_html($task['hours']); ?>h
                                                <?php endif; ?>
                                                <?php if (!empty($task['points'])): ?>
                                                    • <?php echo esc_html($task['points']); ?> pts
                                                <?php endif; ?>
                                                <?php if (!empty($task['date'])): ?>
                                                    • <?php echo esc_html($task['date']); ?>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (!empty($task['note'])): ?>
                                            <div style="color: #6b7280; font-size: 14px; margin-top: 5px; font-style: italic;">
                                                <?php echo esc_html($task['note']); ?>
                                            </div>
                                            <?php endif; ?>
                                            <?php if (!empty($task['blocker_reason'])): ?>
                                            <div style="color: #ef4444; font-size: 14px; margin-top: 5px;">
                                                <strong><?php echo esc_html__('Blocker:', 'taskledger'); ?></strong> 
                                                <?php echo esc_html($task['blocker_reason']); ?>
                                            </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </table>
                            </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 20px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="color: #6b7280; margin: 0; font-size: 12px;">
                                <?php echo esc_html__('Generated by Task Ledger', 'taskledger'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

