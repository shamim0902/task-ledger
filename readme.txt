=== Task Ledger ===
Contributors: taskledger
Tags: task management, daily logs, team collaboration, project management, time tracking
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 8.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A comprehensive task management and daily logging system for WordPress teams.

== Description ==

Task Ledger is a powerful WordPress plugin designed for teams to manage daily task submissions, track work progress, and facilitate task reviews. It integrates seamlessly with Fluent Boards and provides role-based access control for administrators, managers, and team members.

= Key Features =

* **Daily Task Logging**: Team members can log their daily tasks with time tracking, story points, and status updates
* **Task Submission System**: Submit daily logs with completed, in-progress, and blocked tasks
* **Review System**: Managers and admins can review and approve task submissions
* **Role-Based Access Control**: Three-tier role system (Admin, Manager, Member) with granular permissions
* **PM Dashboard**: Comprehensive dashboard for project managers to view team activity and reports
* **Email Notifications**: Automated email notifications for daily submissions and monthly reminders
* **Manager-Member Assignment**: Assign team members to managers for organized task review
* **Fluent Boards Integration**: Seamlessly integrates with Fluent Boards for task management
* **Shortcode Support**: Display task ledger interface on any page using shortcodes

= Installation =

1. Upload the plugin files to the `/wp-content/plugins/task-ledger` directory, or install the plugin through the WordPress plugins screen directly
2. Activate the plugin through the 'Plugins' screen in WordPress
3. The plugin will automatically create all necessary database tables upon activation
4. Go to Settings → Roles to assign roles to users
5. Configure email notifications in Settings → Email Notifications

= Requirements =

* WordPress 5.0 or higher
* PHP 7.3 or higher
* Fluent Boards plugin (recommended for full task management features)

= Usage =

= For Team Members =

1. Navigate to **Developers** (Dashboard) from the WordPress admin menu
2. Select tasks from your Fluent Boards projects
3. Log time spent, story points, and task status
4. Add notes or blocker reasons for blocked tasks
5. Submit your daily log

= For Managers =

1. Navigate to **Submissions** to review team member logs
2. Filter submissions by date range, member, or review status
3. Review individual tasks and mark them as reviewed
4. View team activity in the PM Dashboard

= For Administrators =

1. Access **PM Dashboard** for comprehensive team reports
2. Manage **Roles** in Settings → Roles
3. Configure **Email Notifications** in Settings → Email Notifications
4. Review all team submissions in the **Submissions** section

= Email Notifications =

The plugin includes two built-in email notifications:

1. **Daily Task Submission**: Sent to managers when team members submit their daily logs
2. **Monthly Unreviewed Reminder**: Monthly reminder to managers about unreviewed tasks

Both notifications can be customized in Settings → Email Notifications.

= Shortcodes =

Use the following shortcode to display the Task Ledger interface on any page:

`[taskledger_shortcode]`

The shortcode will automatically display the appropriate interface based on the logged-in user's role.

= User Roles =

* **Admin**: Full access to all features including PM Dashboard, Submissions, Settings, and Roles management
* **Manager**: Can review assigned team members' submissions and access the Submissions dashboard
* **Member**: Can create and submit daily task logs

= Frequently Asked Questions =

= Does this plugin require Fluent Boards? =

While the plugin works independently, full task management features require Fluent Boards plugin for task creation and management.

= Can I customize email notifications? =

Yes, you can customize email subject lines and body content in Settings → Email Notifications. The plugin supports shortcodes for dynamic content.

= How do I assign roles to users? =

Go to Settings → Roles in the WordPress admin menu. Click on a role and use the "Assign Users" button to assign users to that role.

= Can managers review all team members? =

Managers can only review team members that have been assigned to them. Administrators can review all team members.

= Screenshots =

1. Daily Task Logging Dashboard
2. Task Selection Modal
3. Submissions Review Interface
4. PM Dashboard with Team Activity
5. Role Management Interface
6. Email Notification Settings

= Changelog =

= 1.0.0 =
* Initial release
* Daily task logging system
* Role-based access control (Admin, Manager, Member)
* Email notifications (daily submission and monthly reminder)
* PM Dashboard with team activity reports
* Review system for task submissions
* Fluent Boards integration
* Manager-member assignment system
* Shortcode support for frontend display
* Auto-save functionality for daily logs
* Task status tracking (in-progress, completed, blocked)
* Time tracking and story points
* Submission filtering and search
* Bulk review actions

= Upgrade Notice =

= 1.0.0 =
Initial release of Task Ledger. Activate and configure roles to get started.

== Installation ==

1. Upload `task-ledger` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. The plugin will automatically create database tables on activation
4. Configure roles and email notifications in Settings

== Frequently Asked Questions ==

= Does this plugin require Fluent Boards? =

While the plugin works independently, full task management features require Fluent Boards plugin for task creation and management.

= Can I customize email notifications? =

Yes, you can customize email subject lines and body content in Settings → Email Notifications. The plugin supports shortcodes for dynamic content.

= How do I assign roles to users? =

Go to Settings → Roles in the WordPress admin menu. Click on a role and use the "Assign Users" button to assign users to that role.

= Can managers review all team members? =

Managers can only review team members that have been assigned to them. Administrators can review all team members.

== Screenshots ==

1. Daily Task Logging Dashboard
2. Task Selection Modal
3. Submissions Review Interface
4. PM Dashboard with Team Activity
5. Role Management Interface
6. Email Notification Settings

== Changelog ==

= 1.0.0 =
* Initial release
* Daily task logging system
* Role-based access control (Admin, Manager, Member)
* Email notifications (daily submission and monthly reminder)
* PM Dashboard with team activity reports
* Review system for task submissions
* Fluent Boards integration
* Manager-member assignment system
* Shortcode support for frontend display
* Auto-save functionality for daily logs
* Task status tracking (in-progress, completed, blocked)
* Time tracking and story points
* Submission filtering and search
* Bulk review actions

== Upgrade Notice ==

= 1.0.0 =
Initial release of Task Ledger. Activate and configure roles to get started.

