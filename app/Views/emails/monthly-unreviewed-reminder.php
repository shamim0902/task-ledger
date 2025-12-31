<?php
/**
 * Monthly Unreviewed Tasks Reminder Email Template
 * 
 * Available variables:
 * - $manager: array with name, email
 * - $unreviewed: array with count, tasks
 */

$manager = $manager ?? [];
$unreviewed = $unreviewed ?? [];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html__('Monthly Unreviewed Tasks Reminder', 'taskledger'); ?></title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #f59e0b; padding: 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px;">
                                <?php echo esc_html__('Monthly Unreviewed Tasks Reminder', 'taskledger'); ?>
                            </h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 30px;">
                            <p style="margin: 0 0 20px 0; color: #374151; font-size: 16px; line-height: 1.6;">
                                <?php echo esc_html__('Hello', 'taskledger'); ?> <?php echo esc_html($manager['name'] ?? ''); ?>,
                            </p>
                            
                            <p style="margin: 0 0 20px 0; color: #374151; font-size: 16px; line-height: 1.6;">
                                <?php echo esc_html__('This is a monthly reminder about unreviewed task submissions from your team members.', 'taskledger'); ?>
                            </p>
                            
                            <!-- Summary -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 6px; padding: 20px; margin: 20px 0;">
                                <tr>
                                    <td style="padding: 10px 0;">
                                        <strong style="color: #92400e; font-size: 18px;">
                                            <?php echo esc_html($unreviewed['count'] ?? 0); ?> <?php echo esc_html__('Unreviewed Task(s)', 'taskledger'); ?>
                                        </strong>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Unreviewed Tasks List -->
                            <?php if (!empty($unreviewed['tasks'])): ?>
                            <h2 style="color: #111827; font-size: 18px; margin: 30px 0 15px 0;">
                                <?php echo esc_html__('Unreviewed Tasks', 'taskledger'); ?>
                            </h2>
                            <div style="background-color: #f9fafb; border-radius: 6px; padding: 20px; margin: 20px 0;">
                                <?php echo wp_kses_post($unreviewed['tasks']); ?>
                            </div>
                            <?php else: ?>
                            <p style="margin: 20px 0; color: #10b981; font-size: 16px;">
                                <?php echo esc_html__('Great! All tasks have been reviewed.', 'taskledger'); ?>
                            </p>
                            <?php endif; ?>
                            
                            <p style="margin: 30px 0 0 0; color: #6b7280; font-size: 14px; line-height: 1.6;">
                                <?php echo esc_html__('Please review these submissions in the Task Ledger dashboard.', 'taskledger'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 20px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="margin: 0; color: #6b7280; font-size: 12px;">
                                <?php echo esc_html__('This is an automated monthly reminder from Task Ledger.', 'taskledger'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

