# Task Ledger

A comprehensive task management and daily logging system for WordPress that helps teams track work, submit daily logs, and manage task reviews efficiently.

## Description

Task Ledger is a powerful WordPress plugin designed for teams to manage daily task submissions, track work progress, and facilitate task reviews. It integrates seamlessly with Fluent Boards and provides role-based access control for administrators, managers, and team members.

### Key Features

- **Daily Task Logging**: Team members can log their daily tasks with time tracking, story points, and status updates
- **Task Submission System**: Submit daily logs with completed, in-progress, and blocked tasks
- **Review System**: Managers and admins can review and approve task submissions
- **Role-Based Access Control**: Three-tier role system (Admin, Manager, Member) with granular permissions
- **PM Dashboard**: Comprehensive dashboard for project managers to view team activity and reports
- **Email Notifications**: Automated email notifications for daily submissions and monthly reminders
- **Manager-Member Assignment**: Assign team members to managers for organized task review
- **Fluent Boards Integration**: Seamlessly integrates with Fluent Boards for task management
- **Shortcode Support**: Display task ledger interface on any page using shortcodes

## Installation

### Requirements

- WordPress 5.0 or higher
- PHP 8.3 or higher
- Fluent Boards plugin (for full task management features)

### Manual Installation

1. Download the plugin zip file
2. Go to WordPress Admin → Plugins → Add New
3. Click "Upload Plugin" and select the zip file
4. Click "Install Now" and then "Activate Plugin"

### Via Composer

```bash
composer require your-vendor/task-ledger
```

## Configuration

### Initial Setup

1. **Activate the Plugin**: Upon activation, the plugin will automatically create all necessary database tables
2. **Assign Roles**: Go to Settings → Roles to assign roles to users
3. **Set Up Managers**: Assign team members to managers in the Roles section
4. **Configure Email Notifications**: Go to Settings → Email Notifications to customize email templates

### User Roles

- **Admin**: Full access to all features including PM Dashboard, Submissions, Settings, and Roles management
- **Manager**: Can review assigned team members' submissions and access the Submissions dashboard
- **Member**: Can create and submit daily task logs

## Usage

### For Team Members

1. Navigate to **Developers** (Dashboard) from the WordPress admin menu
2. Select tasks from your Fluent Boards projects
3. Log time spent, story points, and task status
4. Add notes or blocker reasons for blocked tasks
5. Submit your daily log

### For Managers

1. Navigate to **Submissions** to review team member logs
2. Filter submissions by date range, member, or review status
3. Review individual tasks and mark them as reviewed
4. View team activity in the PM Dashboard

### For Administrators

1. Access **PM Dashboard** for comprehensive team reports
2. Manage **Roles** in Settings → Roles
3. Configure **Email Notifications** in Settings → Email Notifications
4. Review all team submissions in the **Submissions** section

## Email Notifications

The plugin includes two built-in email notifications:

1. **Daily Task Submission**: Sent to managers when team members submit their daily logs
2. **Monthly Unreviewed Reminder**: Monthly reminder to managers about unreviewed tasks

Both notifications can be customized in Settings → Email Notifications.

## Shortcodes

Use the following shortcode to display the Task Ledger interface on any page:

```
[taskledger_shortcode]
```

The shortcode will automatically display the appropriate interface based on the logged-in user's role.

## Database Tables

The plugin creates the following database tables:

- `wp_task_ledger_settings` - Plugin settings
- `wp_task_ledger_tasks` - Task records
- `wp_task_ledger_task_activity` - Task activity logs
- `wp_task_ledger_task_meta` - Task metadata
- `wp_task_ledger_logs` - Daily log submissions
- `wp_task_ledger_log_items` - Individual task entries in logs
- `wp_task_ledger_meta` - General metadata storage
- `wp_task_ledger_roles` - Role definitions
- `wp_task_ledger_permissions` - Permission definitions
- `wp_task_ledger_role_permissions` - Role-permission mappings
- `wp_task_ledger_user_role_projects` - User role assignments
- `wp_task_ledger_manager_members` - Manager-member relationships

## Development

### Requirements

- Node.js 16+ and npm
- Composer
- PHP 7.3+

### Setup

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Build assets
npm run build

# Development mode
npm run dev
```

### Database Migrations

The plugin uses a migration system for database schema management. All migrations run automatically on plugin activation.

To manually run migrations:

```bash
php wpf migrate:up
```

## Support

For support, feature requests, or bug reports, please contact the plugin author.

## Changelog

### 1.0.0
- Initial release
- Daily task logging system
- Role-based access control
- Email notifications
- PM Dashboard
- Review system
- Fluent Boards integration

## License

GPLv2 or later

## Credits

Built with [WPFluent Framework](https://github.com/wpfluent/framework2x)
