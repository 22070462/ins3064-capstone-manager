<?php

/**
 * Base Controller Class
 * 
 * Provides common functionality for all controllers including
 * view rendering, JSON responses, input validation, and helper methods.
 * All application controllers should extend this base class.
 * 
 * @package App\Core
 * @author  Capstone Project Team
 * @version 1.0.0
 */

namespace App\Core;

abstract class Controller
{
    /**
     * Views directory path
     * 
     * @var string
     */
    protected string $viewPath = __DIR__ . '/../../app/views/';

    /**
     * Layout file to use for rendering views
     * 
     * @var string|null
     */
    protected ?string $layout = 'layouts/main';

    /**
     * Data to pass to views
     * 
     * @var array
     */
    protected array $viewData = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        // Initialize session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Render a view file with optional layout
     * 
     * @param string $view     View file path (relative to views directory)
     * @param array  $data     Data to pass to the view
     * @param bool   $useLayout Whether to use layout wrapper
     * @return void
     */
    protected function renderView(string $view, array $data = [], bool $useLayout = true): void
    {
        // Merge with existing view data
        $data = array_merge($this->viewData, $data);
        
        // Extract data to variables
        extract($data);

        // Build view file path
        $viewFile = $this->viewPath . $view . '.php';

        // Check if view file exists
        if (!file_exists($viewFile)) {
            $this->handleViewError("View file not found: {$view}");
            return;
        }

        // Render with or without layout
        if ($useLayout && $this->layout !== null) {
            $layoutFile = $this->viewPath . $this->layout . '.php';
            
            if (!file_exists($layoutFile)) {
                $this->handleViewError("Layout file not found: {$this->layout}");
                return;
            }

            // Capture view content
            ob_start();
            require $viewFile;
            $content = ob_get_clean();

            // Render layout with content
            require $layoutFile;
        } else {
            // Render view directly
            require $viewFile;
        }
    }

    /**
     * Send JSON response (for API endpoints)
     * 
     * @param bool   $success    Success status
     * @param mixed  $data       Response data
     * @param string $message    Response message
     * @param int    $statusCode HTTP status code
     * @return void
     */
    protected function jsonResponse(bool $success, $data = null, string $message = '', int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');

        $response = [
            'success' => $success,
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Send success JSON response
     * 
     * @param mixed  $data    Response data
     * @param string $message Success message
     * @return void
     */
    protected function jsonSuccess($data = null, string $message = 'Operation successful'): void
    {
        $this->jsonResponse(true, $data, $message, 200);
    }

    /**
     * Send error JSON response
     * 
     * @param string $message    Error message
     * @param int    $statusCode HTTP status code
     * @param mixed  $errors     Validation errors or additional error data
     * @return void
     */
    protected function jsonError(string $message, int $statusCode = 400, $errors = null): void
    {
        $this->jsonResponse(false, $errors, $message, $statusCode);
    }

    /**
     * Redirect to another URL
     * 
     * @param string $url URL to redirect to
     * @return void
     */
    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * Get POST data
     * 
     * @param string|null $key     Specific key to retrieve
     * @param mixed       $default Default value if key not found
     * @return mixed
     */
    protected function getPost(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $_POST;
        }

        return $_POST[$key] ?? $default;
    }

    /**
     * Get GET data
     * 
     * @param string|null $key     Specific key to retrieve
     * @param mixed       $default Default value if key not found
     * @return mixed
     */
    protected function getQuery(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $_GET;
        }

        return $_GET[$key] ?? $default;
    }

    /**
     * Get JSON input from request body
     * 
     * @param bool $assoc Return as associative array
     * @return mixed Decoded JSON data
     */
    protected function getJsonInput(bool $assoc = true)
    {
        $input = file_get_contents('php://input');
        return json_decode($input, $assoc);
    }

    /**
     * Validate required fields in data array
     * 
     * @param array $data     Data to validate
     * @param array $required Required field names
     * @return array Array of missing fields (empty if all present)
     */
    protected function validateRequired(array $data, array $required): array
    {
        $missing = [];

        foreach ($required as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing[] = $field;
            }
        }

        return $missing;
    }

    /**
     * Sanitize input string
     * 
     * @param string $input Input string to sanitize
     * @return string Sanitized string
     */
    protected function sanitize(string $input): string
    {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize array of inputs
     * 
     * @param array $inputs Array of inputs to sanitize
     * @return array Sanitized array
     */
    protected function sanitizeArray(array $inputs): array
    {
        $sanitized = [];

        foreach ($inputs as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArray($value);
            } else {
                $sanitized[$key] = $this->sanitize($value);
            }
        }

        return $sanitized;
    }

    /**
     * Set flash message in session
     * 
     * @param string $type    Message type (success, error, warning, info)
     * @param string $message Message content
     * @return void
     */
    protected function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = [
            'type'    => $type,
            'message' => $message
        ];
    }

    /**
     * Get and clear flash message from session
     * 
     * @return array|null Flash message array or null
     */
    protected function getFlash(): ?array
    {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }

        return null;
    }

    /**
     * Check if request is AJAX
     * 
     * @return bool True if AJAX request, false otherwise
     */
    protected function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Check if request method is POST
     * 
     * @return bool True if POST request, false otherwise
     */
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Check if request method is GET
     * 
     * @return bool True if GET request, false otherwise
     */
    protected function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
     * Get current logged-in user from session
     * 
     * @return array|null User data or null if not logged in
     */
    protected function getCurrentUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    /**
     * Check if user is logged in
     * 
     * @return bool True if logged in, false otherwise
     */
    protected function isLoggedIn(): bool
    {
        return isset($_SESSION['user']) && isset($_SESSION['user']['id']);
    }

    /**
     * Get current user's role
     * 
     * @return string|null User role or null if not logged in
     */
    protected function getUserRole(): ?string
    {
        return $_SESSION['user']['role'] ?? null;
    }

    /**
     * Handle view rendering errors
     * 
     * @param string $message Error message
     * @return void
     */
    private function handleViewError(string $message): void
    {
        http_response_code(500);
        error_log("View Error: {$message}");
        echo "<h1>View Error</h1><p>{$message}</p>";
        exit;
    }

    /**
     * Set layout for view rendering
     * 
     * @param string|null $layout Layout file path or null for no layout
     * @return void
     */
    protected function setLayout(?string $layout): void
    {
        $this->layout = $layout;
    }

    /**
     * Add data to be passed to views
     * 
     * @param string $key   Data key
     * @param mixed  $value Data value
     * @return void
     */
    protected function setViewData(string $key, $value): void
    {
        $this->viewData[$key] = $value;
    }

    /**
     * Upload file with validation
     * 
     * @param array  $file          File from $_FILES
     * @param string $uploadDir     Upload directory path
     * @param array  $allowedTypes  Allowed MIME types
     * @param int    $maxSize       Maximum file size in bytes
     * @return array Result array with 'success', 'message', and 'path' keys
     */
    protected function uploadFile(array $file, string $uploadDir, array $allowedTypes = [], int $maxSize = 10485760): array
    {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'File upload error'];
        }

        // Validate file size
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'message' => 'File size exceeds maximum allowed'];
        }

        // Validate file type
        if (!empty($allowedTypes) && !in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'message' => 'File type not allowed'];
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $destination = $uploadDir . '/' . $filename;

        // Create directory if not exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return [
                'success' => true,
                'message' => 'File uploaded successfully',
                'path'    => $destination,
                'filename' => $filename
            ];
        }

        return ['success' => false, 'message' => 'Failed to move uploaded file'];
    }
}
