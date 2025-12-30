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

// Projects routes (keep basic project listing, remove role management)
$router->get('/projects', 'ProjectsController@index');
$router->post('/projects', 'ProjectsController@create');
$router->post('/projects/import', 'ProjectsController@import');
$router->get('/projects/{id}', 'ProjectsController@show');
