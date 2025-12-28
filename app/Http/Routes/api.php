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
