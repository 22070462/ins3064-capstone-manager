<?php

/**
 * Authentication Controller
 * 
 * Handles user authentication including:
 * - Login
 * - Logout
 * - Session management
 * - Password verification
 * 
 * @package App\Controllers
 * @author  Capstone Project Team
 * @version 1.0.0
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Middleware;
use Config\Database;
use PDO;
use Exception;

class AuthController extends Controller
{
    /**
     * Middleware instance
     * 
     * @var Middleware
     */
    private Middleware $middleware;

    /**
     * Database connection
     * 
     * @var PDO
     */
    private PDO $db;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->middleware = new Middleware();
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Show login form
     * 
     * Route: GET /login
     * 
     * @return void
     */
    public function showLoginForm(): void
    {
        // If already logged in, redirect to appropriate dashboard
        if ($this->middleware->isAuthenticated()) {
            $role = $this->middleware->getUserRole();
            $this->redirect($this->getDashboardUrl($role));
            return;
        }

        // Render login view
        $this->setLayout(null); // No layout for login page
        $this->renderView('auth/login', [
            'error' => $this->getFlash()
        ]);
    }

    /**
     * Process login
     * 
     * Route: POST /api/auth/login
     * 
     * @return void
     */
    public function login(): void
    {
        try {
            // Validate request method
            if (!$this->isPost()) {
                $this->jsonError('Invalid request method', 405);
                return;
            }

            // Get input data
            $input = $this->getJsonInput(true) ?? $this->getPost();

            // Validate required fields
            $required = ['username', 'password'];
            $missing = $this->validateRequired($input, $required);

            if (!empty($missing)) {
                $this->jsonError('Missing required fields: ' . implode(', ', $missing), 400);
                return;
            }

            // Sanitize input
            $username = $this->sanitize($input['username']);
            $password = $input['password']; // Don't sanitize password

            // Find user by username
            $sql = "SELECT u.*, 
                           s.id as student_id, s.full_name as student_name,
                           l.id as lecturer_id, l.full_name as lecturer_name
                    FROM users u
                    LEFT JOIN students s ON u.id = s.user_id
                    LEFT JOIN lecturers l ON u.id = l.user_id
                    WHERE u.username = :username
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Check if user exists
            if (!$user) {
                $this->logActivity(0, 'LOGIN_FAILED', 'users', 0, "Failed login attempt for username: {$username}");
                $this->jsonError('Invalid username or password', 401);
                return;
            }

            // Verify password
            if (!password_verify($password, $user['password'])) {
                $this->logActivity($user['id'], 'LOGIN_FAILED', 'users', $user['id'], "Failed login attempt - wrong password");
                $this->jsonError('Invalid username or password', 401);
                return;
            }

            // Check account status
            if ($user['status'] !== 'Active') {
                $this->jsonError('Account is ' . $user['status'] . '. Please contact administrator.', 403);
                return;
            }

            // Prepare user data for session
            $userData = [
                'id'       => $user['id'],
                'username' => $user['username'],
                'role'     => $user['role'],
                'status'   => $user['status']
            ];

            // Add role-specific data
            if ($user['role'] === 'Student' && $user['student_id']) {
                $userData['student_id'] = $user['student_id'];
                $userData['full_name'] = $user['student_name'];
            } elseif ($user['role'] === 'Lecturer' && $user['lecturer_id']) {
                $userData['lecturer_id'] = $user['lecturer_id'];
                $userData['full_name'] = $user['lecturer_name'];
            }

            // Login user (create session)
            $this->middleware->login($userData);
            
            // Debug: Log session data
            error_log("Session created for user: " . $username . " with role: " . $user['role']);
            error_log("Session data: " . print_r($_SESSION, true));

            // Log successful login
            $this->logActivity($user['id'], 'LOGIN', 'users', $user['id'], "Successful login");

            // Return success response
            $this->jsonSuccess([
                'user' => $userData,
                'redirect' => $this->getDashboardUrl($user['role'])
            ], 'Login successful');

        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $this->jsonError('An error occurred during login', 500);
        }
    }

    /**
     * Logout user
     * 
     * Route: POST /api/auth/logout
     * 
     * @return void
     */
    public function logout(): void
    {
        try {
            // Get user ID before logout (if exists)
            $userId = $this->middleware->getUserId();

            // Log logout activity (if user was logged in)
            if ($userId) {
                $this->logActivity($userId, 'LOGOUT', 'users', $userId, "User logged out");
            }

            // Logout (destroy session) - always do this even if not logged in
            $this->middleware->logout();

            // Get base path
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            if ($basePath === '/' || $basePath === '\\') {
                $basePath = '';
            }
            
            // Determine login URL - go back to root login, not public
            $loginUrl = str_replace('/public', '', $basePath) . '/login';

            // Always return success for logout (even if not logged in)
            if ($this->isAjax() || $this->isJsonRequest()) {
                $this->jsonSuccess(['redirect' => $loginUrl], 'Logout successful');
            } else {
                $this->redirect($loginUrl);
            }

        } catch (Exception $e) {
            error_log("Logout error: " . $e->getMessage());
            
            // Even on error, try to redirect to login
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            if ($basePath === '/' || $basePath === '\\') {
                $basePath = '';
            }
            $loginUrl = str_replace('/public', '', $basePath) . '/login';
            
            if ($this->isAjax() || $this->isJsonRequest()) {
                $this->jsonSuccess(['redirect' => $loginUrl], 'Logout completed');
            } else {
                $this->redirect($loginUrl);
            }
        }
    }

    /**
     * Check if request expects JSON response
     * 
     * @return bool
     */
    private function isJsonRequest(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return strpos($accept, 'application/json') !== false;
    }

    /**
     * Get current authenticated user (API endpoint)
     * 
     * Route: GET /api/auth/me
     * 
     * @return void
     */
    public function me(): void
    {
        try {
            // Check if authenticated
            if (!$this->middleware->isAuthenticated()) {
                $this->jsonError('Not authenticated', 401);
                return;
            }

            // Get user data from session
            $user = $this->middleware->getCurrentUser();

            $this->jsonSuccess($user, 'User data retrieved');

        } catch (Exception $e) {
            error_log("Get current user error: " . $e->getMessage());
            $this->jsonError('An error occurred', 500);
        }
    }

    /**
     * Get dashboard URL based on role
     * 
     * @param string $role User role
     * @return string Dashboard URL
     */
    private function getDashboardUrl(string $role): string
    {
        // Get base path - remove /public if present
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') {
            $basePath = '';
        }
        
        // Remove /public from path if present
        $basePath = str_replace('/public', '', $basePath);
        
        switch ($role) {
            case 'Admin':
                return $basePath . '/admin/dashboard';
            case 'Lecturer':
                return $basePath . '/lecturer/dashboard';
            case 'Student':
                return $basePath . '/student/dashboard';
            default:
                return $basePath . '/';
        }
    }

    /**
     * Log activity to audit trail
     * 
     * @param int         $userId      User ID
     * @param string      $action      Action type
     * @param string      $targetTable Target table
     * @param int         $targetId    Target ID
     * @param string|null $details     Details
     * @return void
     */
    private function logActivity(int $userId, string $action, string $targetTable, int $targetId, ?string $details = null): void
    {
        try {
            $sql = "INSERT INTO activity_logs 
                    (user_id, action, target_table, target_id, details, ip_address, user_agent, created_at) 
                    VALUES 
                    (:user_id, :action, :target_table, :target_id, :details, :ip_address, :user_agent, NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'user_id'      => $userId,
                'action'       => $action,
                'target_table' => $targetTable,
                'target_id'    => $targetId,
                'details'      => $details,
                'ip_address'   => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent'   => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);
        } catch (Exception $e) {
            error_log("Activity log error: " . $e->getMessage());
        }
    }
}
