<?php

/**
 * Router Class
 * 
 * Handles HTTP request routing to appropriate controllers and methods.
 * Supports GET, POST, PUT, DELETE methods and dynamic route parameters.
 * Implements RESTful routing conventions.
 * 
 * @package App\Core
 * @author  Capstone Project Team
 * @version 1.0.0
 */

namespace App\Core;

class Router
{
    /**
     * Array of registered routes
     * 
     * @var array
     */
    private array $routes = [];

    /**
     * Current request method
     * 
     * @var string
     */
    private string $requestMethod;

    /**
     * Current request URI
     * 
     * @var string
     */
    private string $requestUri;

    /**
     * Route parameters extracted from URI
     * 
     * @var array
     */
    private array $params = [];

    /**
     * Constructor - Initialize router with current request
     */
    public function __construct()
    {
        $this->requestMethod = $_SERVER['REQUEST_METHOD'];
        $this->requestUri = $this->parseUri();
    }

    /**
     * Parse and clean the request URI
     * 
     * @return string Cleaned URI path
     */
    private function parseUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Remove query string
        $uri = parse_url($uri, PHP_URL_PATH);
        
        // Remove trailing slash except for root
        $uri = $uri !== '/' ? rtrim($uri, '/') : $uri;
        
        // Remove base path if application is in subdirectory
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath !== '/' && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }
        
        return $uri ?: '/';
    }

    /**
     * Register a GET route
     * 
     * @param string          $path       Route path (e.g., '/users/{id}')
     * @param string|callable $controller Controller class name or closure
     * @param string|null     $method     Controller method name (null for closures)
     * @return void
     */
    public function get(string $path, $controller, $method = null): void
    {
        $this->addRoute('GET', $path, $controller, $method);
    }

    /**
     * Register a POST route
     * 
     * @param string          $path       Route path
     * @param string|callable $controller Controller class name or closure
     * @param string|null     $method     Controller method name (null for closures)
     * @return void
     */
    public function post(string $path, $controller, $method = null): void
    {
        $this->addRoute('POST', $path, $controller, $method);
    }

    /**
     * Register a PUT route
     * 
     * @param string          $path       Route path
     * @param string|callable $controller Controller class name or closure
     * @param string|null     $method     Controller method name (null for closures)
     * @return void
     */
    public function put(string $path, $controller, $method = null): void
    {
        $this->addRoute('PUT', $path, $controller, $method);
    }

    /**
     * Register a DELETE route
     * 
     * @param string          $path       Route path
     * @param string|callable $controller Controller class name or closure
     * @param string|null     $method     Controller method name (null for closures)
     * @return void
     */
    public function delete(string $path, $controller, $method = null): void
    {
        $this->addRoute('DELETE', $path, $controller, $method);
    }

    /**
     * Add a route to the routes array
     * 
     * @param string          $httpMethod HTTP method (GET, POST, PUT, DELETE)
     * @param string          $path       Route path
     * @param string|callable $controller Controller class name or closure
     * @param string|null     $method     Controller method name (null for closures)
     * @return void
     */
    private function addRoute(string $httpMethod, string $path, $controller, $method = null): void
    {
        // Convert route path to regex pattern
        $pattern = $this->convertToRegex($path);
        
        $this->routes[] = [
            'method'     => $httpMethod,
            'path'       => $path,
            'pattern'    => $pattern,
            'controller' => $controller,
            'action'     => $method
        ];
    }

    /**
     * Convert route path to regex pattern
     * Supports dynamic parameters like {id}, {slug}, etc.
     * 
     * @param string $path Route path
     * @return string Regex pattern
     */
    private function convertToRegex(string $path): string
    {
        // Escape forward slashes
        $pattern = str_replace('/', '\/', $path);
        
        // Convert {param} to named capture groups
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^\/]+)', $pattern);
        
        return '/^' . $pattern . '$/';
    }

    /**
     * Match current request to registered routes and dispatch
     * 
     * @return void
     */
    public function dispatch(): void
    {
        $matched = false;

        foreach ($this->routes as $route) {
            // Check if HTTP method matches
            if ($route['method'] !== $this->requestMethod) {
                continue;
            }

            // Check if URI matches route pattern
            if (preg_match($route['pattern'], $this->requestUri, $matches)) {
                $matched = true;
                
                // Extract named parameters
                $this->params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                
                // Dispatch to controller
                $this->callController($route['controller'], $route['action']);
                break;
            }
        }

        // Handle 404 if no route matched
        if (!$matched) {
            $this->handleNotFound();
        }
    }

    /**
     * Instantiate controller and call the specified method, or execute closure
     * 
     * @param string|callable $controller Controller class name or closure
     * @param string|null     $method     Method name to call (null for closures)
     * @return void
     */
    private function callController($controller, $method = null): void
    {
        try {
            // Check if controller is a closure
            if (is_callable($controller)) {
                // Execute closure with parameters
                call_user_func_array($controller, $this->params);
                return;
            }

            // Controller is a class name
            $controllerName = $controller;
            $methodName = $method;

            // Check if controller class exists
            if (!class_exists($controllerName)) {
                throw new \Exception("Controller {$controllerName} not found");
            }

            // Instantiate controller
            $controller = new $controllerName();

            // Check if method exists
            if (!method_exists($controller, $methodName)) {
                throw new \Exception("Method {$methodName} not found in {$controllerName}");
            }

            // Call controller method with parameters
            call_user_func_array([$controller, $methodName], $this->params);
            
        } catch (\Exception $e) {
            error_log("Router Error: " . $e->getMessage());
            $this->handleError($e->getMessage());
        }
    }

    /**
     * Handle 404 Not Found errors
     * 
     * @return void
     */
    private function handleNotFound(): void
    {
        http_response_code(404);
        
        // Check if request expects JSON (API endpoint)
        if ($this->isApiRequest()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Endpoint not found',
                'path'    => $this->requestUri
            ]);
        } else {
            // Render 404 view
            echo "<h1>404 - Page Not Found</h1>";
            echo "<p>The requested page '{$this->requestUri}' could not be found.</p>";
        }
        exit;
    }

    /**
     * Handle general routing errors
     * 
     * @param string $message Error message
     * @return void
     */
    private function handleError(string $message): void
    {
        http_response_code(500);
        
        if ($this->isApiRequest()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Internal server error',
                'error'   => $message
            ]);
        } else {
            echo "<h1>500 - Internal Server Error</h1>";
            echo "<p>{$message}</p>";
        }
        exit;
    }

    /**
     * Check if current request is an API request
     * 
     * @return bool True if API request, false otherwise
     */
    private function isApiRequest(): bool
    {
        return strpos($this->requestUri, '/api/') === 0 ||
               (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    }

    /**
     * Get route parameters
     * 
     * @return array Route parameters
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Get all registered routes (for debugging)
     * 
     * @return array All registered routes
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
