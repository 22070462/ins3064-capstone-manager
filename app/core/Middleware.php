<?php

/**
 * Middleware Class
 * 
 * Handles authentication, authorization, and request filtering.
 * Implements Role-Based Access Control (RBAC) for Admin, Lecturer, and Student roles.
 * Provides session management and security checks.
 * 
 * @package App\Core
 * @author  Capstone Project Team
 * @version 1.0.0
 */

namespace App\Core;

class Middleware
{
    /**
     * Available user roles
     */
    const ROLE_ADMIN = 'Admin';
    const ROLE_LECTURER = 'Lecturer';
    const ROLE_STUDENT = 'Student';

    /**
     * Session timeout in seconds (30 minutes)
     * 
     * @var int
     */
    private int $sessionTimeout = 1800;

    /**
     * Constructor - Initialize session
     */
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Check if user is authenticated
     * Redirects to login page if not authenticated
     * 
     * @param string $redirectUrl URL to redirect if not authenticated
     * @return bool True if authenticated, false otherwise
     */
    public function requireAuth(string $redirectUrl = '/login'): bool
    {
        if (!$this->isAuthenticated()) {
            $this->redirectTo($redirectUrl);
            return false;
        }

        // Check session timeout
        if ($this->isSessionExpired()) {
            $this->logout();
            $this->redirectTo($redirectUrl . '?timeout=1');
            return false;
        }

        // Update last activity time
        $this->updateLastActivity();

        return true;
    }

    /**
     * Check if user is authenticated (without redirect)
     * 
     * @return bool True if authenticated, false otherwise
     */
    public function isAuthenticated(): bool
    {
        return isset($_SESSION['user']) && 
               isset($_SESSION['user']['id']) && 
               isset($_SESSION['user']['role']);
    }

    /**
     * Require specific role(s) to access resource
     * 
     * @param string|array $roles        Required role(s)
     * @param string       $redirectUrl  URL to redirect if unauthorized
     * @param bool         $returnJson   Return JSON response instead of redirect
     * @return bool True if authorized, false otherwise
     */
    public function requireRole($roles, string $redirectUrl = '/unauthorized', bool $returnJson = false): bool
    {
        // Ensure user is authenticated first
        if (!$this->isAuthenticated()) {
            if ($returnJson) {
                $this->jsonUnauthorized('Authentication required');
            } else {
                $this->redirectTo('/login');
            }
            return false;
        }

        // Convert single role to array
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        // Check if user has required role
        $userRole = $_SESSION['user']['role'] ?? null;

        if (!in_array($userRole, $roles)) {
            if ($returnJson) {
                $this->jsonForbidden('Insufficient permissions');
            } else {
                $this->redirectTo($redirectUrl);
            }
            return false;
        }

        return true;
    }

    /**
     * Require Admin role
     * 
     * @param bool $returnJson Return JSON response instead of redirect
     * @return bool True if user is admin, false otherwise
     */
    public function requireAdmin(bool $returnJson = false): bool
    {
        return $this->requireRole(self::ROLE_ADMIN, '/unauthorized', $returnJson);
    }

    /**
     * Require Lecturer role
     * 
     * @param bool $returnJson Return JSON response instead of redirect
     * @return bool True if user is lecturer, false otherwise
     */
    public function requireLecturer(bool $returnJson = false): bool
    {
        return $this->requireRole(self::ROLE_LECTURER, '/unauthorized', $returnJson);
    }

    /**
     * Require Student role
     * 
     * @param bool $returnJson Return JSON response instead of redirect
     * @return bool True if user is student, false otherwise
     */
    public function requireStudent(bool $returnJson = false): bool
    {
        return $this->requireRole(self::ROLE_STUDENT, '/unauthorized', $returnJson);
    }

    /**
     * Require Lecturer or Admin role
     * 
     * @param bool $returnJson Return JSON response instead of redirect
     * @return bool True if user is lecturer or admin, false otherwise
     */
    public function requireLecturerOrAdmin(bool $returnJson = false): bool
    {
        return $this->requireRole([self::ROLE_LECTURER, self::ROLE_ADMIN], '/unauthorized', $returnJson);
    }

    /**
     * Check if current user has specific role
     * 
     * @param string $role Role to check
     * @return bool True if user has role, false otherwise
     */
    public function hasRole(string $role): bool
    {
        if (!$this->isAuthenticated()) {
            return false;
        }

        return ($_SESSION['user']['role'] ?? null) === $role;
    }

    /**
     * Check if current user is admin
     * 
     * @return bool True if admin, false otherwise
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    /**
     * Check if current user is lecturer
     * 
     * @return bool True if lecturer, false otherwise
     */
    public function isLecturer(): bool
    {
        return $this->hasRole(self::ROLE_LECTURER);
    }

    /**
     * Check if current user is student
     * 
     * @return bool True if student, false otherwise
     */
    public function isStudent(): bool
    {
        return $this->hasRole(self::ROLE_STUDENT);
    }

    /**
     * Get current user data
     * 
     * @return array|null User data or null if not authenticated
     */
    public function getCurrentUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    /**
     * Get current user ID
     * 
     * @return int|null User ID or null if not authenticated
     */
    public function getUserId(): ?int
    {
        return $_SESSION['user']['id'] ?? null;
    }

    /**
     * Get current user role
     * 
     * @return string|null User role or null if not authenticated
     */
    public function getUserRole(): ?string
    {
        return $_SESSION['user']['role'] ?? null;
    }

    /**
     * Check if session has expired
     * 
     * @return bool True if expired, false otherwise
     */
    private function isSessionExpired(): bool
    {
        if (!isset($_SESSION['last_activity'])) {
            return false;
        }

        $elapsed = time() - $_SESSION['last_activity'];
        return $elapsed > $this->sessionTimeout;
    }

    /**
     * Update last activity timestamp
     * 
     * @return void
     */
    private function updateLastActivity(): void
    {
        $_SESSION['last_activity'] = time();
    }

    /**
     * Login user and create session
     * 
     * @param array $userData User data to store in session
     * @return void
     */
    public function login(array $userData): void
    {
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        // Store user data in session
        $_SESSION['user'] = [
            'id'       => $userData['id'],
            'username' => $userData['username'],
            'role'     => $userData['role'],
            'status'   => $userData['status'] ?? 'Active'
        ];

        // Set initial activity time
        $_SESSION['last_activity'] = time();
        $_SESSION['login_time'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    /**
     * Logout user and destroy session
     * 
     * @return void
     */
    public function logout(): void
    {
        // Clear session data
        $_SESSION = [];

        // Destroy session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        // Destroy session
        session_destroy();
    }

    /**
     * Validate CSRF token
     * 
     * @param string|null $token Token to validate
     * @return bool True if valid, false otherwise
     */
    public function validateCsrfToken(?string $token): bool
    {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token ?? '');
    }

    /**
     * Generate CSRF token
     * 
     * @return string Generated CSRF token
     */
    public function generateCsrfToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Require valid CSRF token for request
     * 
     * @param bool $returnJson Return JSON response instead of redirect
     * @return bool True if valid, false otherwise
     */
    public function requireCsrfToken(bool $returnJson = false): bool
    {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        if (!$this->validateCsrfToken($token)) {
            if ($returnJson) {
                $this->jsonForbidden('Invalid CSRF token');
            } else {
                http_response_code(403);
                die('Invalid CSRF token');
            }
            return false;
        }

        return true;
    }

    /**
     * Check if request is from same origin
     * 
     * @return bool True if same origin, false otherwise
     */
    public function isSameOrigin(): bool
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $host = $_SERVER['HTTP_HOST'] ?? '';

        return strpos($origin, $host) !== false;
    }

    /**
     * Redirect to URL
     * 
     * @param string $url URL to redirect to
     * @return void
     */
    private function redirectTo(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * Send JSON unauthorized response (401)
     * 
     * @param string $message Error message
     * @return void
     */
    private function jsonUnauthorized(string $message = 'Unauthorized'): void
    {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
        exit;
    }

    /**
     * Send JSON forbidden response (403)
     * 
     * @param string $message Error message
     * @return void
     */
    private function jsonForbidden(string $message = 'Forbidden'): void
    {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
        exit;
    }

    /**
     * Set custom session timeout
     * 
     * @param int $seconds Timeout in seconds
     * @return void
     */
    public function setSessionTimeout(int $seconds): void
    {
        $this->sessionTimeout = $seconds;
    }

    /**
     * Check if user account is active
     * 
     * @return bool True if active, false otherwise
     */
    public function isAccountActive(): bool
    {
        if (!$this->isAuthenticated()) {
            return false;
        }

        $status = $_SESSION['user']['status'] ?? 'Inactive';
        return $status === 'Active';
    }

    /**
     * Require active account status
     * 
     * @param string $redirectUrl URL to redirect if account inactive
     * @return bool True if active, false otherwise
     */
    public function requireActiveAccount(string $redirectUrl = '/account-suspended'): bool
    {
        if (!$this->isAccountActive()) {
            $this->logout();
            $this->redirectTo($redirectUrl);
            return false;
        }

        return true;
    }
}
