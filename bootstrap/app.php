<?php

// Ensure Composer autoload is available for bootstrapping.
require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Foundation\Application;

$app = new Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

// Load environment variables (if vlucas/phpdotenv is available)
if (class_exists(\Dotenv\Dotenv::class)) {
    try {
        $dotEnv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__));
        $dotEnv->safeLoad();
    } catch (Throwable $e) {
        // Ignore dotenv load errors here; app may still bootstrap with defaults.
    }
}

// Bind a minimal config repository if real config files are missing.
if (! $app->bound('config')) {
    $app->singleton('config', function () {
        $configFiles = [];
        $appConfig = [];
        if (file_exists(dirname(__DIR__) . '/config/app.php')) {
            $appConfig = include dirname(__DIR__) . '/config/app.php';
        }
        $configArray = array_replace_recursive($appConfig ?: [], []);
        return new Illuminate\Config\Repository($configArray);
    });
}

// Provide a minimal 'files' binding used by some service providers during boot.
if (! $app->bound('files')) {
    $app->singleton('files', function () {
        return new Illuminate\Filesystem\Filesystem();
    });
}

return $app;
