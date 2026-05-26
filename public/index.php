<?php

/**
 * Application Entry Point
 * 
 * This is the main entry point for all HTTP requests.
 * All requests are routed through this file via .htaccess
 * 
 * @package Public
 * @author  Capstone Project Team
 * @version 1.0.0
 */

// Start output buffering
ob_start();

// Define application constants
define('APP_ROOT', dirname(__DIR__));
define('APP_START_TIME', microtime(true));

// Load autoloader
require_once APP_ROOT . '/config/autoload.php';

// Import required classes
use App\Core\Router;

// Initialize router
$router = new Router();

// Load application routes
require_once APP_ROOT . '/config/routes.php';

// Dispatch the router
$router->dispatch();

// Flush output buffer
ob_end_flush();
