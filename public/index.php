<?php
/**
 * PUB CampusMate — Front Controller
 * Pundra University of Science & Technology
 */

declare(strict_types=1);

define('APP_START', microtime(true));
define('ROOT_PATH', dirname(__DIR__));

// ---------------------------------------------------------------------
// 1. Simple, Reliable Environment (.env) Parser
// ---------------------------------------------------------------------
$envFile = ROOT_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            // Strip surrounding quotes
            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }
            $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }
    }
}

// ---------------------------------------------------------------------
// 2. PSR-4 Autoloader with Composer fallback
// ---------------------------------------------------------------------
if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
    require ROOT_PATH . '/vendor/autoload.php';
} else {
    // Built-in PSR-4 autoloader for standalone execution
    spl_autoload_register(function ($class) {
        $prefix = 'App\\';
        $baseDir = ROOT_PATH . '/app/';

        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }

        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($file)) {
            require $file;
        }
    });

    // Load helper functions
    $helpers = [
        ROOT_PATH . '/app/Helpers/auth.php',
        ROOT_PATH . '/app/Helpers/csrf.php',
        ROOT_PATH . '/app/Helpers/validation.php',
        ROOT_PATH . '/app/Helpers/response.php',
    ];
    foreach ($helpers as $helper) {
        if (file_exists($helper)) {
            require_once $helper;
        }
    }
}

// ---------------------------------------------------------------------
// 3. Error & Exception Handling
// ---------------------------------------------------------------------
$isDebug = filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOLEAN);

if ($isDebug) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

set_exception_handler(function (\Throwable $e) use ($isDebug) {
    // Log exception to storage/logs
    $logDir = ROOT_PATH . '/storage/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    $logMsg = '[' . date('Y-m-d H:i:s') . '] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() . "\n" . $e->getTraceAsString() . "\n\n";
    file_put_contents($logDir . '/error.log', $logMsg, FILE_APPEND);

    http_response_code(500);

    if ($isDebug) {
        echo "<!DOCTYPE html><html><head><title>Application Exception</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css'></head><body class='bg-light p-4'><div class='container'><div class='card shadow-sm border-danger'><div class='card-header bg-danger text-white fw-bold'>Application Exception</div><div class='card-body'><h5 class='text-danger'>" . htmlspecialchars($e->getMessage()) . "</h5><p class='text-muted'><strong>Location:</strong> " . htmlspecialchars($e->getFile()) . " (Line " . $e->getLine() . ")</p><pre class='bg-dark text-light p-3 rounded small' style='max-height:400px;overflow:auto;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre></div></div></div></body></html>";
    } else {
        echo "<!DOCTYPE html><html><head><title>500 Internal Error</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css'></head><body class='bg-light d-flex align-items-center' style='min-height:100vh;'><div class='container text-center'><h1 class='display-3 fw-bold text-danger'>500</h1><h3>Internal Server Error</h3><p class='text-muted'>Something went wrong on the server. Please contact university IT support.</p><a href='/' class='btn btn-primary mt-3'>Return to Home</a></div></body></html>";
    }
    exit;
});

// ---------------------------------------------------------------------
// 4. Secure Session Initialization
// ---------------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_samesite', 'Lax');
    session_name($_ENV['SESSION_COOKIE_NAME'] ?? 'pub_campusmate_session');
    session_start();
}

// ---------------------------------------------------------------------
// 5. Initialize View Layer & Load Routes
// ---------------------------------------------------------------------
\App\Core\View::init();
require_once ROOT_PATH . '/routes/web.php';

// ---------------------------------------------------------------------
// 6. Dispatch Request
// ---------------------------------------------------------------------
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

\App\Core\Router::dispatch($requestUri, $requestMethod);
