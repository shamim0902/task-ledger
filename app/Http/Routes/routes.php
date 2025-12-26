<?php

$router->namespace('TaskLedger\App\Http\Controllers')
	->withDefaultPolicy()
	->group(fn($router) => require_once __DIR__ . "/api.php");
