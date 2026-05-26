<?php

/**
 * PSR-4 Autoloader
 * 
 * Automatically loads classes based on PSR-4 naming conventions.
 * Maps namespaces to directory paths.
 * 
 * @package Config
 * @author  Capstone Project Team
 * @version 1.0.0
 */

spl_autoload_register(function ($class) {
    // Base directory for the namespace prefix
    $baseDir = __DIR__ . '/../';

    // Namespace prefix to directory mapping
    $prefixes = [
        'App\\Core\\'        => $baseDir . 'app/core/',
        'App\\Controllers\\' => $baseDir . 'app/controllers/',
        'App\\Models\\'      => $baseDir . 'app/models/',
        'Config\\'           => $baseDir . 'config/',
    ];

    // Check each prefix
    foreach ($prefixes as $prefix => $dir) {
        // Does the class use the namespace prefix?
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            // No, move to the next registered prefix
            continue;
        }

        // Get the relative class name
        $relativeClass = substr($class, $len);

        // Replace namespace separators with directory separators
        // and append with .php
        $file = $dir . str_replace('\\', '/', $relativeClass) . '.php';

        // If the file exists, require it
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

/**
 * Error and Exception Handlers
 */

// Set error reporting based on environment
$environment = $_ENV['APP_ENV'] ?? 'production';

if ($environment === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/error.log');
}

/**
 * Custom error handler
 */
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    $errorTypes = [
        E_ERROR             => 'Error',
        E_WARNING           => 'Warning',
        E_PARSE             => 'Parse Error',
        E_NOTICE            => 'Notice',
        E_CORE_ERROR        => 'Core Error',
        E_CORE_WARNING      => 'Core Warning',
        E_COMPILE_ERROR     => 'Compile Error',
        E_COMPILE_WARNING   => 'Compile Warning',
        E_USER_ERROR        => 'User Error',
        E_USER_WARNING      => 'User Warning',
        E_USER_NOTICE       => 'User Notice',
        E_STRICT            => 'Strict Notice',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_DEPRECATED        => 'Deprecated',
        E_USER_DEPRECATED   => 'User Deprecated',
    ];

    $errorType = $errorTypes[$errno] ?? 'Unknown Error';
    $message = "[{$errorType}] {$errstr} in {$errfile} on line {$errline}";

    error_log($message);

    // Don't execute PHP internal error handler
    return true;
});

/**
 * Custom exception handler
 */
set_exception_handler(function ($exception) {
    $message = sprintf(
        "[Exception] %s in %s on line %d\nStack trace:\n%s",
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString()
    );

    error_log($message);

    // Send appropriate response
    http_response_code(500);
    
    if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'An unexpected error occurred',
            'error'   => $exception->getMessage()
        ]);
    } else {
        echo '<h1>500 - Internal Server Error</h1>';
        echo '<p>An unexpected error occurred. Please try again later.</p>';
    }

    exit;
});

/**
 * Timezone configuration
 */
date_default_timezone_set('Asia/Manila'); // Adjust to your timezone

/**
 * Session configuration
 */
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
ini_set('session.cookie_samesite', 'Lax');

/**
 * CORS Headers (for API endpoints)
 * Adjust origins as needed for production
 */
if (isset($_SERVER['HTTP_ORIGIN'])) {
    // Allow from any origin in development
    // In production, specify exact origins
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400'); // Cache for 1 day
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    }

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }

    exit(0);
}
