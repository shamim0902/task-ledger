<?php

$router->namespace('TaskLedger\App\Http\Controllers')
	->withPolicy('UserPolicy')
	->group(fn($router) => require_once __DIR__ . "/api.php");
