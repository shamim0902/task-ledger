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
