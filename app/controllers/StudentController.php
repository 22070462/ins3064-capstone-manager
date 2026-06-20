<?php

/**
 * Student Controller
 * 
 * Handles student profile management including:
 * - View profile
 * - Update profile
 * - Change password
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

class StudentController extends Controller
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
     * Get student profile by ID
     * 
     * Route: GET /api/students/{id}
     * 
     * @param int $id Student ID
     * @return void
     */
    public function show(int $id): void
    {
        try {
            // Check authentication
            if (!$this->middleware->isAuthenticated()) {
                $this->jsonError('Unauthorized', 401);
                return;
            }

            // Get current user
            $currentUser = $this->middleware->getCurrentUser();
            
            // Authorization: Students can only view their own profile
            // Admin and Lecturers can view any student profile
            if ($currentUser['role'] === 'Student') {
                if ($currentUser['student_id'] != $id) {
                    $this->jsonError('Forbidden: You can only view your own profile', 403);
                    return;
                }
            }

            // Get student details with department info
            $sql = "SELECT s.*, 
                           d.name as department_name,
                           u.username, u.status, u.created_at as user_created_at
                    FROM students s
                    JOIN departments d ON s.department_id = d.id
                    JOIN users u ON s.user_id = u.id
                    WHERE s.id = :id
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$student) {
                $this->jsonError('Student not found', 404);
                return;
            }

            // Return student data
            $this->jsonSuccess($student, 'Student profile retrieved successfully');

        } catch (Exception $e) {
            error_log("Get student error: " . $e->getMessage());
            $this->jsonError('Failed to retrieve student profile', 500);
        }
    }

    /**
     * Update student profile
     * 
     * Route: PUT /api/students/{id}
     * 
     * @param int $id Student ID
     * @return void
     */
    public function update(int $id): void
    {
        try {
            // Check authentication
            if (!$this->middleware->isAuthenticated()) {
                $this->jsonError('Unauthorized', 401);
                return;
            }

            // Get current user
            $currentUser = $this->middleware->getCurrentUser();
            
            // Authorization: Students can only update their own profile
            // Admin can update any student profile
            if ($currentUser['role'] === 'Student') {
                if ($currentUser['student_id'] != $id) {
                    $this->jsonError('Forbidden: You can only update your own profile', 403);
                    return;
                }
            } elseif ($currentUser['role'] !== 'Admin') {
                $this->jsonError('Forbidden: Only students and admins can update profiles', 403);
                return;
            }

            // Get input data
            $input = $this->getJsonInput(true);

            // Validate input
            if (empty($input)) {
                $this->jsonError('No data provided', 400);
                return;
            }

            // Check if student exists
            $checkSql = "SELECT id FROM students WHERE id = :id LIMIT 1";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute(['id' => $id]);
            
            if (!$checkStmt->fetch()) {
                $this->jsonError('Student not found', 404);
                return;
            }

            // Prepare update fields
            $updateFields = [];
            $params = ['id' => $id];

            // Allow updates to these fields
            $allowedFields = ['full_name', 'email', 'phone'];

            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    $updateFields[] = "$field = :$field";
                    $params[$field] = $this->sanitize($input[$field]);
                }
            }

            // Check if there's anything to update
            if (empty($updateFields)) {
                $this->jsonError('No valid fields to update', 400);
                return;
            }

            // Validate email if provided
            if (isset($params['email']) && !empty($params['email'])) {
                if (!filter_var($params['email'], FILTER_VALIDATE_EMAIL)) {
                    $this->jsonError('Invalid email format', 400);
                    return;
                }

                // Check if email is already used by another student
                $emailCheckSql = "SELECT id FROM students WHERE email = :email AND id != :id LIMIT 1";
                $emailCheckStmt = $this->db->prepare($emailCheckSql);
                $emailCheckStmt->execute([
                    'email' => $params['email'],
                    'id' => $id
                ]);

                if ($emailCheckStmt->fetch()) {
                    $this->jsonError('Email is already in use by another student', 409);
                    return;
                }
            }

            // Update student
            $sql = "UPDATE students SET " . implode(', ', $updateFields) . ", updated_at = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            // Log activity
            $this->logActivity(
                $currentUser['id'],
                'UPDATE_STUDENT',
                'students',
                $id,
                "Updated student profile"
            );

            // Get updated student data
            $getUpdatedSql = "SELECT s.*, 
                                     d.name as department_name,
                                     u.username, u.status
                              FROM students s
                              JOIN departments d ON s.department_id = d.id
                              JOIN users u ON s.user_id = u.id
                              WHERE s.id = :id
                              LIMIT 1";

            $getUpdatedStmt = $this->db->prepare($getUpdatedSql);
            $getUpdatedStmt->execute(['id' => $id]);
            $updatedStudent = $getUpdatedStmt->fetch(PDO::FETCH_ASSOC);

            $this->jsonSuccess($updatedStudent, 'Profile updated successfully');

        } catch (Exception $e) {
            error_log("Update student error: " . $e->getMessage());
            $this->jsonError('Failed to update profile', 500);
        }
    }

    /**
     * Change password
     * 
     * Route: POST /api/auth/change-password
     * 
     * @return void
     */
    public function changePassword(): void
    {
        try {
            // Check authentication
            if (!$this->middleware->isAuthenticated()) {
                $this->jsonError('Unauthorized', 401);
                return;
            }

            // Get current user
            $currentUser = $this->middleware->getCurrentUser();
            $userId = $currentUser['id'];

            // Get input data
            $input = $this->getJsonInput(true);

            // Validate required fields
            $required = ['current_password', 'new_password'];
            $missing = $this->validateRequired($input, $required);

            if (!empty($missing)) {
                $this->jsonError('Missing required fields: ' . implode(', ', $missing), 400);
                return;
            }

            $currentPassword = $input['current_password'];
            $newPassword = $input['new_password'];

            // Validate new password
            if (strlen($newPassword) < 6) {
                $this->jsonError('New password must be at least 6 characters', 400);
                return;
            }

            // Get user from database
            $sql = "SELECT password FROM users WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $this->jsonError('User not found', 404);
                return;
            }

            // Verify current password
            if (!password_verify($currentPassword, $user['password'])) {
                $this->jsonError('Current password is incorrect', 401);
                return;
            }

            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update password
            $updateSql = "UPDATE users SET password = :password, updated_at = NOW() WHERE id = :id";
            $updateStmt = $this->db->prepare($updateSql);
            $updateStmt->execute([
                'password' => $hashedPassword,
                'id' => $userId
            ]);

            // Log activity
            $this->logActivity(
                $userId,
                'CHANGE_PASSWORD',
                'users',
                $userId,
                "User changed password"
            );

            $this->jsonSuccess(null, 'Password changed successfully');

        } catch (Exception $e) {
            error_log("Change password error: " . $e->getMessage());
            $this->jsonError('Failed to change password', 500);
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
