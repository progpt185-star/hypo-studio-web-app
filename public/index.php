<?php

define('LARAVEL_START', microtime(true));

// Load Composer's autoloader
require __DIR__.'/../vendor/autoload.php';

// Boot the application
$app = require_once __DIR__.'/../bootstrap/app.php';

// Run the HTTP kernel
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

$response->send();

$kernel->terminate($request, $response);
