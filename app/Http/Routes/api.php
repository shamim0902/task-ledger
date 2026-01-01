<?php

/**
 * @var $router FluentFramework\Http\Router\Router
 */

$router->get('/welcome', 'WelcomeController@index');

$router->get('/users', 'UserController@get');
$router->get('/users/{id}', 'UserController@find');
$router->post('/users', 'UserController@create');
$router->patch('/users/{id}', 'UserController@update');
$router->delete('/users/{id}', 'UserController@delete');


$router->get('/posts', 'PostController@get');
$router->get('/posts/{id}', 'PostController@find');
$router->post('/upload', 'PostController@upload');

// my routes
$router->get('/tasks', 'TaskController@get');
$router->post('/subtasks', 'TaskController@createSubtask');
$router->patch('/subtasks/completed/{id}', 'TaskController@markSubtaskCompleted');

$router->post('/logs', 'LogController@create');

$router->get('/logs', 'LogController@get');
$router->get('/logs/history', 'LogController@getHistory');
$router->delete('/logs/items/{id}', 'LogController@deleteLogItem');
$router->delete('/logs/today', 'LogController@deleteTodayLog');
$router->get('/today-logs', 'LogController@getTodayLogs');

// PM Dashboard routes
$router->get('/pm/team-activity', 'PMDashboardController@getTeamActivity');
$router->get('/pm/summary-stats', 'PMDashboardController@getSummaryStats');
$router->get('/pm/task-analytics', 'PMDashboardController@getTaskAnalytics');
$router->get('/pm/task-overview', 'PMDashboardController@getTaskOverview');
$router->get('/pm/blocked-tasks', 'PMDashboardController@getBlockedTasks');
$router->get('/pm/team-members', 'PMDashboardController@getTeamMembers');
$router->get('/pm/boards', 'PMDashboardController@getBoards');
$router->post('/pm/send-reminders', 'PMDashboardController@sendReminders');

// Role Management routes (read-only, system roles only)
$router->get('/roles', 'RoleController@index');
$router->get('/roles/{id}', 'RoleController@show');

// User Role routes
$router->get('/user-roles/user/{userId}', 'UserRoleController@getUserRoles');
$router->post('/user-roles/assign', 'UserRoleController@assignRole');
$router->post('/user-roles/remove', 'UserRoleController@removeRole');
$router->get('/user-roles/role/{roleId}', 'UserRoleController@getUsersByRole');
$router->get('/user-roles/all', 'UserRoleController@getAllUsersWithRoles');

// Manager-Member Assignment routes
$router->post('/user-roles/assign-member', 'UserRoleController@assignMember');
$router->post('/user-roles/remove-member', 'UserRoleController@removeMember');
$router->get('/user-roles/manager/{managerId}/members', 'UserRoleController@getManagedMembers');
$router->get('/user-roles/member/{memberId}/manager', 'UserRoleController@getManager');
$router->get('/user-roles/all-assignments', 'UserRoleController@getAllAssignments');

// Projects routes (keep basic project listing, remove role management)
$router->get('/projects', 'ProjectsController@index');
$router->post('/projects', 'ProjectsController@create');
$router->post('/projects/import', 'ProjectsController@import');
$router->get('/projects/{id}', 'ProjectsController@show');

// Review routes
$router->get('/review/members', 'ReviewController@getReviewableMembers');
$router->get('/review/submissions', 'ReviewController@getSubmissions');
$router->get('/review/submissions/{id}', 'ReviewController@getSubmissionDetails');
$router->post('/review/logs/{id}/review', 'ReviewController@markLogReviewed');
$router->post('/review/log-items/{id}/review', 'ReviewController@markLogItemReviewed');
$router->post('/review/bulk-review', 'ReviewController@bulkMarkReviewed');

// Email Notification routes
$router->get('/email-notifications', 'EmailNotificationController@index');
$router->get('/email-notifications/{name}', 'EmailNotificationController@find');
$router->put('/email-notifications/{name}', 'EmailNotificationController@update');
$router->post('/email-notifications/{name}/enable', 'EmailNotificationController@enableNotification');
$router->get('/email-notifications/shortcodes', 'EmailNotificationController@getShortCodes');

// Report routes
$router->get('/reports/history/{userId}', 'ReportController@getReportHistory');
$router->get('/reports/submitted', 'ReportController@getSubmittedReports');
$router->post('/reports/generate', 'ReportController@generateReport');
$router->post('/reports/send', 'ReportController@sendReportToAdmin');
$router->get('/reports/download', 'ReportController@downloadReport');
