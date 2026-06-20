<?php

/**
 * Admin Controller
 * 
 * Handles administrative functions including:
 * - User management
 * - System reports
 * - Data export (CSV, Excel)
 * - System settings
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

class AdminController extends Controller
{
    /**
     * Middleware instance for authentication and authorization
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
     * Constructor - Initialize dependencies
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware = new Middleware();
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Export topic assignments to CSV file
     * 
     * Executes a complex SQL JOIN query across multiple tables:
     * - topic_assignments
     * - topic_registrations
     * - students
     * - topics
     * - lecturers
     * - departments
     * 
     * Generates a CSV file with proper headers and forces download.
     * 
     * Access: Admin only
     * Route: GET /api/admin/export/assignments
     * 
     * @return void
     */
    public function exportAssignments(): void
    {
        try {
            // AUTHORIZATION: Require Admin role
            if (!$this->middleware->requireAdmin(true)) {
                return; // Middleware handles the response
            }

            // Complex SQL JOIN query
            $sql = "SELECT 
                        ta.id AS assignment_id,
                        ta.assigned_at,
                        ta.notes AS assignment_notes,
                        
                        tr.status AS registration_status,
                        tr.registered_at,
                        
                        s.student_code,
                        s.full_name AS student_name,
                        s.email AS student_email,
                        s.phone AS student_phone,
                        s.enrollment_year,
                        
                        t.title AS topic_title,
                        t.description AS topic_description,
                        t.status AS topic_status,
                        t.created_at AS topic_created_at,
                        
                        l.lecturer_code,
                        l.full_name AS lecturer_name,
                        l.email AS lecturer_email,
                        l.phone AS lecturer_phone,
                        l.specialization AS lecturer_specialization,
                        l.max_quota AS lecturer_max_quota,
                        
                        d.code AS department_code,
                        d.name AS department_name,
                        
                        (SELECT GROUP_CONCAT(tag_name SEPARATOR ', ') 
                         FROM topic_tags tt 
                         WHERE tt.topic_id = t.id) AS topic_tags,
                        
                        (SELECT COUNT(*) 
                         FROM submissions sub 
                         WHERE sub.assignment_id = ta.id) AS total_submissions
                    
                    FROM topic_assignments ta
                    
                    INNER JOIN topic_registrations tr 
                        ON ta.registration_id = tr.id
                    
                    INNER JOIN students s 
                        ON tr.student_id = s.id
                    
                    INNER JOIN topics t 
                        ON tr.topic_id = t.id
                    
                    INNER JOIN lecturers l 
                        ON ta.lecturer_id = l.id
                    
                    INNER JOIN departments d 
                        ON s.department_id = d.id
                    
                    ORDER BY ta.assigned_at DESC, s.student_code ASC";

            // Execute query
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Check if data exists
            if (empty($assignments)) {
                $this->jsonError('No assignment data available for export', 404);
                return;
            }

            // Generate filename with timestamp
            $filename = 'topic_assignments_' . date('Y-m-d_His') . '.csv';

            // Set HTTP headers for CSV download
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');

            // Open output stream
            $output = fopen('php://output', 'w');

            // Add UTF-8 BOM for Excel compatibility
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            // Define CSV headers
            $headers = [
                'Assignment ID',
                'Student Code',
                'Student Name',
                'Student Email',
                'Student Phone',
                'Enrollment Year',
                'Department Code',
                'Department Name',
                'Topic Title',
                'Topic Description',
                'Topic Tags',
                'Topic Status',
                'Lecturer Code',
                'Lecturer Name',
                'Lecturer Email',
                'Lecturer Phone',
                'Lecturer Specialization',
                'Lecturer Max Quota',
                'Registration Status',
                'Registered At',
                'Assigned At',
                'Assignment Notes',
                'Total Submissions',
                'Topic Created At'
            ];

            // Write headers to CSV
            fputcsv($output, $headers);

            // Write data rows
            foreach ($assignments as $assignment) {
                $row = [
                    $assignment['assignment_id'],
                    $assignment['student_code'],
                    $assignment['student_name'],
                    $assignment['student_email'] ?? 'N/A',
                    $assignment['student_phone'] ?? 'N/A',
                    $assignment['enrollment_year'] ?? 'N/A',
                    $assignment['department_code'],
                    $assignment['department_name'],
                    $assignment['topic_title'],
                    $this->cleanCsvField($assignment['topic_description']),
                    $assignment['topic_tags'] ?? 'N/A',
                    $assignment['topic_status'],
                    $assignment['lecturer_code'],
                    $assignment['lecturer_name'],
                    $assignment['lecturer_email'] ?? 'N/A',
                    $assignment['lecturer_phone'] ?? 'N/A',
                    $assignment['lecturer_specialization'] ?? 'N/A',
                    $assignment['lecturer_max_quota'],
                    $assignment['registration_status'],
                    $assignment['registered_at'],
                    $assignment['assigned_at'],
                    $this->cleanCsvField($assignment['assignment_notes'] ?? 'N/A'),
                    $assignment['total_submissions'],
                    $assignment['topic_created_at']
                ];

                fputcsv($output, $row);
            }

            // Close output stream
            fclose($output);

            // Log activity
            $this->logActivity(
                $this->middleware->getUserId(),
                'EXPORT',
                'topic_assignments',
                0,
                "Exported {count($assignments)} assignments to CSV"
            );

            exit; // Important: Stop script execution after download

        } catch (Exception $e) {
            error_log("Export assignments error: " . $e->getMessage());
            $this->jsonError('Failed to export assignments: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Export all students to CSV
     * 
     * Access: Admin only
     * Route: GET /api/admin/export/students
     * 
     * @return void
     */
    public function exportStudents(): void
    {
        try {
            // AUTHORIZATION: Require Admin role
            if (!$this->middleware->requireAdmin(true)) {
                return;
            }

            // Query students with department info
            $sql = "SELECT 
                        s.id,
                        s.student_code,
                        s.full_name,
                        s.email,
                        s.phone,
                        s.enrollment_year,
                        s.created_at,
                        d.code AS department_code,
                        d.name AS department_name,
                        u.username,
                        u.status AS account_status,
                        (SELECT COUNT(*) 
                         FROM topic_registrations tr 
                         WHERE tr.student_id = s.id) AS total_registrations
                    FROM students s
                    INNER JOIN departments d ON s.department_id = d.id
                    INNER JOIN users u ON s.user_id = u.id
                    ORDER BY s.student_code ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($students)) {
                $this->jsonError('No student data available', 404);
                return;
            }

            // Generate filename
            $filename = 'students_' . date('Y-m-d_His') . '.csv';

            // Set headers
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');

            // Open output
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            // Headers
            $headers = [
                'ID', 'Student Code', 'Full Name', 'Email', 'Phone',
                'Enrollment Year', 'Department Code', 'Department Name',
                'Username', 'Account Status', 'Total Registrations', 'Created At'
            ];
            fputcsv($output, $headers);

            // Data rows
            foreach ($students as $student) {
                fputcsv($output, [
                    $student['id'],
                    $student['student_code'],
                    $student['full_name'],
                    $student['email'] ?? 'N/A',
                    $student['phone'] ?? 'N/A',
                    $student['enrollment_year'] ?? 'N/A',
                    $student['department_code'],
                    $student['department_name'],
                    $student['username'],
                    $student['account_status'],
                    $student['total_registrations'],
                    $student['created_at']
                ]);
            }

            fclose($output);

            // Log activity
            $this->logActivity(
                $this->middleware->getUserId(),
                'EXPORT',
                'students',
                0,
                "Exported " . count($students) . " students to CSV"
            );

            exit;

        } catch (Exception $e) {
            error_log("Export students error: " . $e->getMessage());
            $this->jsonError('Failed to export students', 500);
        }
    }

    /**
     * Export all topics to CSV
     * 
     * Access: Admin only
     * Route: GET /api/admin/export/topics
     * 
     * @return void
     */
    public function exportTopics(): void
    {
        try {
            // AUTHORIZATION: Require Admin role
            if (!$this->middleware->requireAdmin(true)) {
                return;
            }

            // Query topics with related data
            $sql = "SELECT 
                        t.id,
                        t.title,
                        t.description,
                        t.status,
                        t.max_students,
                        t.created_at,
                        d.code AS department_code,
                        d.name AS department_name,
                        l.lecturer_code,
                        l.full_name AS lecturer_name,
                        (SELECT GROUP_CONCAT(tag_name SEPARATOR ', ') 
                         FROM topic_tags tt 
                         WHERE tt.topic_id = t.id) AS tags,
                        (SELECT COUNT(*) 
                         FROM topic_registrations tr 
                         WHERE tr.topic_id = t.id 
                         AND tr.status = 'Approved') AS approved_count,
                        (SELECT COUNT(*) 
                         FROM topic_registrations tr 
                         WHERE tr.topic_id = t.id 
                         AND tr.status = 'Pending') AS pending_count
                    FROM topics t
                    INNER JOIN departments d ON t.department_id = d.id
                    INNER JOIN lecturers l ON t.created_by = l.id
                    ORDER BY t.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($topics)) {
                $this->jsonError('No topic data available', 404);
                return;
            }

            // Generate filename
            $filename = 'topics_' . date('Y-m-d_His') . '.csv';

            // Set headers
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');

            // Open output
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            // Headers
            $headers = [
                'ID', 'Title', 'Description', 'Status', 'Max Students',
                'Department Code', 'Department Name', 'Lecturer Code',
                'Lecturer Name', 'Tags', 'Approved Count', 'Pending Count',
                'Created At'
            ];
            fputcsv($output, $headers);

            // Data rows
            foreach ($topics as $topic) {
                fputcsv($output, [
                    $topic['id'],
                    $topic['title'],
                    $this->cleanCsvField($topic['description']),
                    $topic['status'],
                    $topic['max_students'],
                    $topic['department_code'],
                    $topic['department_name'],
                    $topic['lecturer_code'],
                    $topic['lecturer_name'],
                    $topic['tags'] ?? 'N/A',
                    $topic['approved_count'],
                    $topic['pending_count'],
                    $topic['created_at']
                ]);
            }

            fclose($output);

            // Log activity
            $this->logActivity(
                $this->middleware->getUserId(),
                'EXPORT',
                'topics',
                0,
                "Exported " . count($topics) . " topics to CSV"
            );

            exit;

        } catch (Exception $e) {
            error_log("Export topics error: " . $e->getMessage());
            $this->jsonError('Failed to export topics', 500);
        }
    }

    /**
     * Get dashboard statistics
     * 
     * Access: Admin only
     * Route: GET /api/admin/dashboard/stats
     * 
     * @return void
     */
    public function getDashboardStats(): void
    {
        try {
            // AUTHORIZATION: Require Admin role
            if (!$this->middleware->requireAdmin(true)) {
                return;
            }

            // Get various statistics
            $stats = [
                'total_users' => $this->getCount('users'),
                'total_students' => $this->getCount('students'),
                'total_lecturers' => $this->getCount('lecturers'),
                'total_topics' => $this->getCount('topics'),
                'published_topics' => $this->getCount('topics', "status = 'Published'"),
                'total_registrations' => $this->getCount('topic_registrations'),
                'pending_registrations' => $this->getCount('topic_registrations', "status = 'Pending'"),
                'approved_registrations' => $this->getCount('topic_registrations', "status = 'Approved'"),
                'total_assignments' => $this->getCount('topic_assignments'),
                'total_submissions' => $this->getCount('submissions'),
                'total_departments' => $this->getCount('departments'),
                'active_users' => $this->getCount('users', "status = 'Active'")
            ];

            $this->jsonSuccess($stats, 'Dashboard statistics retrieved successfully');

        } catch (Exception $e) {
            error_log("Dashboard stats error: " . $e->getMessage());
            $this->jsonError('Failed to retrieve statistics', 500);
        }
    }

    /**
     * Get count from table with optional condition
     * 
     * SECURITY: Uses whitelist approach to prevent SQL injection
     * 
     * @param string $table     Table name
     * @param string $condition Optional WHERE condition (use prepared statement format)
     * @param array $params     Parameters for prepared statement
     * @return int Count
     */
    private function getCount(string $table, string $condition = '', array $params = []): int
    {
        // Whitelist of allowed tables to prevent SQL injection
        $allowedTables = [
            'users', 'students', 'lecturers', 'topics', 
            'topic_registrations', 'topic_assignments', 
            'submissions', 'departments'
        ];

        if (!in_array($table, $allowedTables)) {
            throw new Exception("Invalid table name: {$table}");
        }

        // Build query with backticks to prevent injection
        $sql = "SELECT COUNT(*) as count FROM `{$table}`";
        
        if ($condition) {
            $sql .= " WHERE {$condition}";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) $result['count'];
    }

    /**
     * Clean CSV field to prevent injection and formatting issues
     * 
     * @param string $field Field value
     * @return string Cleaned field
     */
    private function cleanCsvField(string $field): string
    {
        // Remove line breaks and extra spaces
        $field = preg_replace('/\s+/', ' ', $field);
        $field = trim($field);

        // Limit length to prevent extremely long fields
        if (strlen($field) > 500) {
            $field = substr($field, 0, 497) . '...';
        }

        return $field;
    }

    /**
     * Get all users with detailed information
     * 
     * Access: Admin only
     * Route: GET /api/admin/users
     * 
     * @return void
     */
    public function getUsers(): void
    {
        try {
            error_log("=== AdminController::getUsers() START ===");
            
            // AUTHORIZATION: Require Admin role
            if (!$this->middleware->requireAdmin(true)) {
                error_log("getUsers: Authorization FAILED");
                return;
            }

            error_log("getUsers: Authorization OK, preparing query");

            // Complex query to get users with their roles
            $sql = "SELECT 
                        u.id,
                        u.username,
                        u.role,
                        u.status,
                        u.created_at,
                        CASE 
                            WHEN u.role = 'Student' THEN s.full_name
                            WHEN u.role = 'Lecturer' THEN l.full_name
                            ELSE u.username
                        END as full_name,
                        CASE 
                            WHEN u.role = 'Student' THEN s.email
                            WHEN u.role = 'Lecturer' THEN l.email
                            ELSE NULL
                        END as email,
                        CASE 
                            WHEN u.role = 'Student' THEN s.student_code
                            WHEN u.role = 'Lecturer' THEN l.lecturer_code
                            ELSE NULL
                        END as code,
                        CASE 
                            WHEN u.role = 'Student' THEN d.name
                            WHEN u.role = 'Lecturer' THEN ld.name
                            ELSE NULL
                        END as department
                    FROM users u
                    LEFT JOIN students s ON u.id = s.user_id
                    LEFT JOIN lecturers l ON u.id = l.user_id
                    LEFT JOIN departments d ON s.department_id = d.id
                    LEFT JOIN departments ld ON l.department_id = ld.id
                    ORDER BY u.created_at DESC";

            error_log("getUsers: Executing SQL query");
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            error_log("getUsers: Found " . count($users) . " users");
            $this->jsonSuccess($users, 'Users retrieved successfully');
            error_log("=== AdminController::getUsers() END ===");

        } catch (Exception $e) {
            error_log("getUsers ERROR: " . $e->getMessage());
            error_log("getUsers TRACE: " . $e->getTraceAsString());
            $this->jsonError('Failed to retrieve users: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get user details by ID
     * 
     * Access: Admin only
     * Route: GET /api/admin/users/{id}
     * 
     * @param int $userId User ID
     * @return void
     */
    public function getUserDetails(int $userId): void
    {
        try {
            // AUTHORIZATION: Require Admin role
            if (!$this->middleware->requireAdmin(true)) {
                return;
            }

            // Get user with role-specific details
            $sql = "SELECT 
                        u.id,
                        u.username,
                        u.password,
                        u.role,
                        u.status,
                        u.created_at,
                        CASE 
                            WHEN u.role = 'Student' THEN s.full_name
                            WHEN u.role = 'Lecturer' THEN l.full_name
                            ELSE u.username
                        END as full_name,
                        CASE 
                            WHEN u.role = 'Student' THEN s.email
                            WHEN u.role = 'Lecturer' THEN l.email
                            ELSE NULL
                        END as email
                    FROM users u
                    LEFT JOIN students s ON u.id = s.user_id
                    LEFT JOIN lecturers l ON u.id = l.user_id
                    WHERE u.id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $this->jsonError('User not found', 404);
                return;
            }

            // Get role-specific details
            $roleDetails = null;
            if ($user['role'] === 'Student') {
                $sql = "SELECT s.*, d.name as department_name 
                        FROM students s
                        INNER JOIN departments d ON s.department_id = d.id
                        WHERE s.user_id = :user_id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute(['user_id' => $userId]);
                $roleDetails = $stmt->fetch(PDO::FETCH_ASSOC);
            } elseif ($user['role'] === 'Lecturer') {
                $sql = "SELECT l.*, d.name as department_name 
                        FROM lecturers l
                        INNER JOIN departments d ON l.department_id = d.id
                        WHERE l.user_id = :user_id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute(['user_id' => $userId]);
                $roleDetails = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            $user['role_details'] = $roleDetails;

            $this->jsonSuccess($user, 'User details retrieved successfully');

        } catch (Exception $e) {
            error_log("Get user details error: " . $e->getMessage());
            $this->jsonError('Failed to retrieve user details', 500);
        }
    }

    /**
     * Update user status (Active/Inactive)
     * 
     * Access: Admin only
     * Route: PUT /api/admin/users/{id}/status
     * 
     * @param int $userId User ID
     * @return void
     */
    public function updateUserStatus(int $userId): void
    {
        try {
            // AUTHORIZATION: Require Admin role
            if (!$this->middleware->requireAdmin(true)) {
                return;
            }

            // Get JSON input
            $input = $this->getJsonInput();

            // Validate input
            if (!isset($input['status']) || !in_array($input['status'], ['Active', 'Inactive'])) {
                $this->jsonError('Invalid status. Must be Active or Inactive', 400);
                return;
            }

            // Check if user exists
            $sql = "SELECT id, role FROM users WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $this->jsonError('User not found', 404);
                return;
            }

            // Prevent admin from deactivating themselves
            if ($userId === $this->middleware->getUserId()) {
                $this->jsonError('Cannot deactivate your own account', 403);
                return;
            }

            // Update status
            $sql = "UPDATE users SET status = :status WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'status' => $input['status'],
                'id' => $userId
            ]);

            // Log activity
            $this->logActivity(
                $this->middleware->getUserId(),
                'UPDATE_STATUS',
                'users',
                $userId,
                "Changed user status to {$input['status']}"
            );

            $this->jsonSuccess([
                'user_id' => $userId,
                'new_status' => $input['status']
            ], 'User status updated successfully');

        } catch (Exception $e) {
            error_log("Update user status error: " . $e->getMessage());
            $this->jsonError('Failed to update user status', 500);
        }
    }

    /**
     * Reset user password (Admin action)
     * 
     * Access: Admin only
     * Route: PUT /api/admin/users/{id}/reset-password
     * 
     * @param int $userId User ID
     * @return void
     */
    public function resetUserPassword(int $userId): void
    {
        try {
            // AUTHORIZATION: Require Admin role
            if (!$this->middleware->requireAdmin(true)) {
                return;
            }

            // Get JSON input
            $input = $this->getJsonInput();

            // Validate input
            if (!isset($input['new_password']) || strlen($input['new_password']) < 6) {
                $this->jsonError('Password must be at least 6 characters', 400);
                return;
            }

            // Check if user exists
            $sql = "SELECT id, username FROM users WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $this->jsonError('User not found', 404);
                return;
            }

            // Hash password
            $hashedPassword = password_hash($input['new_password'], PASSWORD_DEFAULT);

            // Update password
            $sql = "UPDATE users SET password = :password WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'password' => $hashedPassword,
                'id' => $userId
            ]);

            // Log activity
            $this->logActivity(
                $this->middleware->getUserId(),
                'RESET_PASSWORD',
                'users',
                $userId,
                "Admin reset password for user: {$user['username']}"
            );

            $this->jsonSuccess([
                'user_id' => $userId,
                'username' => $user['username']
            ], 'Password reset successfully');

        } catch (Exception $e) {
            error_log("Reset password error: " . $e->getMessage());
            $this->jsonError('Failed to reset password', 500);
        }
    }

    /**
     * Get all registrations
     * 
     * Access: Admin only
     * Route: GET /api/admin/registrations
     * 
     * @return void
     */
    public function getRegistrations(): void
    {
        try {
            // AUTHORIZATION: Require Admin role
            if (!$this->middleware->requireAdmin(true)) {
                return;
            }

            // Get registrations with detailed information
            $sql = "SELECT 
                        tr.id,
                        tr.status,
                        tr.registered_at,
                        tr.reviewed_at,
                        tr.rejection_reason,
                        s.id as student_id,
                        s.student_code,
                        s.full_name as student_name,
                        s.email as student_email,
                        t.id as topic_id,
                        t.title as topic_title,
                        t.status as topic_status,
                        l.id as lecturer_id,
                        l.lecturer_code,
                        l.full_name as lecturer_name,
                        d.name as department_name
                    FROM topic_registrations tr
                    INNER JOIN students s ON tr.student_id = s.id
                    INNER JOIN topics t ON tr.topic_id = t.id
                    INNER JOIN lecturers l ON tr.lecturer_id = l.id
                    INNER JOIN departments d ON s.department_id = d.id
                    ORDER BY tr.registered_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->jsonSuccess($registrations, 'Registrations retrieved successfully');

        } catch (Exception $e) {
            error_log("Get registrations error: " . $e->getMessage());
            $this->jsonError('Failed to retrieve registrations: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get registration details by ID
     * 
     * Access: Admin only
     * Route: GET /api/admin/registrations/{id}
     * 
     * @param int $registrationId Registration ID
     * @return void
     */
    public function getRegistrationDetails(int $registrationId): void
    {
        try {
            // AUTHORIZATION: Require Admin role
            if (!$this->middleware->requireAdmin(true)) {
                return;
            }

            $sql = "SELECT 
                        tr.*,
                        s.student_code,
                        s.full_name as student_name,
                        s.email as student_email,
                        s.phone as student_phone,
                        t.title as topic_title,
                        t.description as topic_description,
                        t.status as topic_status,
                        l.lecturer_code,
                        l.full_name as lecturer_name,
                        l.email as lecturer_email,
                        d.name as department_name
                    FROM topic_registrations tr
                    INNER JOIN students s ON tr.student_id = s.id
                    INNER JOIN topics t ON tr.topic_id = t.id
                    INNER JOIN lecturers l ON tr.lecturer_id = l.id
                    INNER JOIN departments d ON s.department_id = d.id
                    WHERE tr.id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $registrationId]);
            $registration = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$registration) {
                $this->jsonError('Registration not found', 404);
                return;
            }

            $this->jsonSuccess($registration, 'Registration details retrieved successfully');

        } catch (Exception $e) {
            error_log("Get registration details error: " . $e->getMessage());
            $this->jsonError('Failed to retrieve registration details', 500);
        }
    }

    /**
     * Update registration status (Approve/Reject)
     * 
     * Access: Admin only
     * Route: PUT /api/admin/registrations/{id}/status
     * 
     * @param int $registrationId Registration ID
     * @return void
     */
    public function updateRegistrationStatus(int $registrationId): void
    {
        try {
            // AUTHORIZATION: Require Admin role
            if (!$this->middleware->requireAdmin(true)) {
                return;
            }

            // Get JSON input
            $input = $this->getJsonInput();

            // Validate input
            if (!isset($input['status']) || !in_array($input['status'], ['Approved', 'Rejected', 'Pending', 'Withdrawn'])) {
                $this->jsonError('Invalid status. Must be Approved, Rejected, Pending, or Withdrawn', 400);
                return;
            }

            // Get registration
            $sql = "SELECT id, student_id, topic_id, status FROM topic_registrations WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $registrationId]);
            $registration = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$registration) {
                $this->jsonError('Registration not found', 404);
                return;
            }

            // Update status
            $sql = "UPDATE topic_registrations 
                    SET status = :status, 
                        reviewed_at = NOW(),
                        rejection_reason = :rejection_reason
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'status' => $input['status'],
                'rejection_reason' => $input['rejection_reason'] ?? null,
                'id' => $registrationId
            ]);

            // Log activity
            $this->logActivity(
                $this->middleware->getUserId(),
                'UPDATE_STATUS',
                'topic_registrations',
                $registrationId,
                "Changed registration status to {$input['status']}"
            );

            $this->jsonSuccess([
                'registration_id' => $registrationId,
                'new_status' => $input['status']
            ], 'Registration status updated successfully');

        } catch (Exception $e) {
            error_log("Update registration status error: " . $e->getMessage());
            $this->jsonError('Failed to update registration status', 500);
        }
    }

    /**
     * Delete registration
     * 
     * Access: Admin only
     * Route: DELETE /api/admin/registrations/{id}
     * 
     * @param int $registrationId Registration ID
     * @return void
     */
    public function deleteRegistration(int $registrationId): void
    {
        try {
            // AUTHORIZATION: Require Admin role
            if (!$this->middleware->requireAdmin(true)) {
                return;
            }

            // Check if registration exists
            $sql = "SELECT id FROM topic_registrations WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $registrationId]);
            $registration = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$registration) {
                $this->jsonError('Registration not found', 404);
                return;
            }

            // Delete registration
            $sql = "DELETE FROM topic_registrations WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $registrationId]);

            // Log activity
            $this->logActivity(
                $this->middleware->getUserId(),
                'DELETE',
                'topic_registrations',
                $registrationId,
                'Deleted registration'
            );

            $this->jsonSuccess([
                'registration_id' => $registrationId
            ], 'Registration deleted successfully');

        } catch (Exception $e) {
            error_log("Delete registration error: " . $e->getMessage());
            $this->jsonError('Failed to delete registration', 500);
        }
    }

    /**
     * Get comprehensive reports data
     * 
     * Access: Admin only
     * Route: GET /api/admin/reports
     * 
     * @return void
     */
    public function getReports(): void
    {
        try {
            // AUTHORIZATION: Require Admin role
            if (!$this->middleware->requireAdmin(true)) {
                return;
            }

            $reports = [
                'overview' => $this->getOverviewReport(),
                'registrations' => $this->getRegistrationReport(),
                'topics' => $this->getTopicReport(),
                'departments' => $this->getDepartmentReport(),
                'timeline' => $this->getTimelineReport(),
                'performance' => $this->getPerformanceReport()
            ];

            $this->jsonSuccess($reports, 'Reports data retrieved successfully');

        } catch (Exception $e) {
            error_log("Get reports error: " . $e->getMessage());
            $this->jsonError('Failed to retrieve reports: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get overview report statistics
     */
    private function getOverviewReport(): array
    {
        return [
            'total_users' => $this->getCount('users'),
            'active_users' => $this->getCount('users', "status = 'Active'"),
            'inactive_users' => $this->getCount('users', "status = 'Inactive'"),
            'total_students' => $this->getCount('students'),
            'total_lecturers' => $this->getCount('lecturers'),
            'total_topics' => $this->getCount('topics'),
            'published_topics' => $this->getCount('topics', "status = 'Published'"),
            'draft_topics' => $this->getCount('topics', "status = 'Draft'"),
            'archived_topics' => $this->getCount('topics', "status = 'Archived'"),
            'total_registrations' => $this->getCount('topic_registrations'),
            'pending_registrations' => $this->getCount('topic_registrations', "status = 'Pending'"),
            'approved_registrations' => $this->getCount('topic_registrations', "status = 'Approved'"),
            'rejected_registrations' => $this->getCount('topic_registrations', "status = 'Rejected'"),
            'withdrawn_registrations' => $this->getCount('topic_registrations', "status = 'Withdrawn'"),
            'total_assignments' => $this->getCount('topic_assignments'),
            'total_submissions' => $this->getCount('submissions'),
            'total_departments' => $this->getCount('departments')
        ];
    }

    /**
     * Get registration analysis report
     */
    private function getRegistrationReport(): array
    {
        // Registration status distribution
        $sql = "SELECT 
                    status,
                    COUNT(*) as count,
                    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM topic_registrations), 2) as percentage
                FROM topic_registrations
                GROUP BY status
                ORDER BY count DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $statusDistribution = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Top topics by registrations
        $sql = "SELECT 
                    t.id,
                    t.title,
                    COUNT(tr.id) as registration_count,
                    l.full_name as lecturer_name
                FROM topics t
                LEFT JOIN topic_registrations tr ON t.id = tr.topic_id
                LEFT JOIN lecturers l ON t.created_by = l.id
                GROUP BY t.id
                ORDER BY registration_count DESC
                LIMIT 10";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $topTopics = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Registration trends by month
        $sql = "SELECT 
                    DATE_FORMAT(registered_at, '%Y-%m') as month,
                    COUNT(*) as count
                FROM topic_registrations
                WHERE registered_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY month
                ORDER BY month ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $monthlyTrends = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'status_distribution' => $statusDistribution,
            'top_topics' => $topTopics,
            'monthly_trends' => $monthlyTrends
        ];
    }

    /**
     * Get topic analysis report
     */
    private function getTopicReport(): array
    {
        // Topics by status
        $sql = "SELECT 
                    status,
                    COUNT(*) as count
                FROM topics
                GROUP BY status";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $byStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Topics by department
        $sql = "SELECT 
                    d.name as department_name,
                    COUNT(t.id) as topic_count
                FROM departments d
                LEFT JOIN topics t ON d.id = t.department_id
                GROUP BY d.id
                ORDER BY topic_count DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $byDepartment = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Lecturer productivity
        $sql = "SELECT 
                    l.full_name as lecturer_name,
                    l.lecturer_code,
                    COUNT(t.id) as topics_created,
                    COUNT(DISTINCT tr.student_id) as students_supervised
                FROM lecturers l
                LEFT JOIN topics t ON l.id = t.created_by
                LEFT JOIN topic_registrations tr ON l.id = tr.lecturer_id AND tr.status = 'Approved'
                GROUP BY l.id
                ORDER BY topics_created DESC
                LIMIT 10";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $lecturerProductivity = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'by_status' => $byStatus,
            'by_department' => $byDepartment,
            'lecturer_productivity' => $lecturerProductivity
        ];
    }

    /**
     * Get department analysis report
     */
    private function getDepartmentReport(): array
    {
        $sql = "SELECT 
                    d.name as department_name,
                    d.code as department_code,
                    COUNT(DISTINCT s.id) as student_count,
                    COUNT(DISTINCT l.id) as lecturer_count,
                    COUNT(DISTINCT t.id) as topic_count,
                    COUNT(DISTINCT tr.id) as registration_count
                FROM departments d
                LEFT JOIN students s ON d.id = s.department_id
                LEFT JOIN lecturers l ON d.id = l.department_id
                LEFT JOIN topics t ON d.id = t.department_id
                LEFT JOIN topic_registrations tr ON t.id = tr.topic_id
                GROUP BY d.id
                ORDER BY student_count DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get timeline report (activity over time)
     */
    private function getTimelineReport(): array
    {
        // User registrations over time
        $sql = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    role,
                    COUNT(*) as count
                FROM users
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY month, role
                ORDER BY month ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $userGrowth = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Topic creation timeline
        $sql = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as count
                FROM topics
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY month
                ORDER BY month ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $topicCreation = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'user_growth' => $userGrowth,
            'topic_creation' => $topicCreation
        ];
    }

    /**
     * Get performance metrics report
     */
    private function getPerformanceReport(): array
    {
        // Average response time for registration reviews
        $sql = "SELECT 
                    AVG(TIMESTAMPDIFF(HOUR, registered_at, reviewed_at)) as avg_hours
                FROM topic_registrations
                WHERE reviewed_at IS NOT NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $avgReviewTime = $stmt->fetch(PDO::FETCH_ASSOC);

        // Approval rate
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved,
                    ROUND(SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as approval_rate
                FROM topic_registrations
                WHERE status IN ('Approved', 'Rejected')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $approvalMetrics = $stmt->fetch(PDO::FETCH_ASSOC);

        // Topic capacity utilization
        $sql = "SELECT 
                    AVG((SELECT COUNT(*) 
                         FROM topic_registrations tr 
                         WHERE tr.topic_id = t.id 
                         AND tr.status = 'Approved') * 100.0 / t.max_students) as avg_utilization
                FROM topics t
                WHERE t.max_students > 0 
                AND t.status = 'Published'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $capacityMetrics = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'avg_review_time_hours' => round($avgReviewTime['avg_hours'] ?? 0, 2),
            'approval_metrics' => $approvalMetrics,
            'avg_capacity_utilization' => round($capacityMetrics['avg_utilization'] ?? 0, 2)
        ];
    }

    /**
     * Get all system settings
     * 
     * Access: Admin only
     * Route: GET /api/admin/settings
     * 
     * @return void
     */
    public function getSettings(): void
    {
        try {
            // AUTHORIZATION: Require Admin role
            if (!$this->middleware->requireAdmin(true)) {
                return;
            }

            $sql = "SELECT * FROM system_settings ORDER BY setting_key ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Parse JSON values
            foreach ($settings as &$setting) {
                if ($setting['data_type'] === 'json') {
                    $setting['parsed_value'] = json_decode($setting['setting_value'], true);
                } elseif ($setting['data_type'] === 'boolean') {
                    $setting['parsed_value'] = $setting['setting_value'] === 'true';
                }
            }

            $this->jsonSuccess($settings, 'Settings retrieved successfully');

        } catch (Exception $e) {
            error_log("Get settings error: " . $e->getMessage());
            $this->jsonError('Failed to retrieve settings: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update a system setting
     * 
     * Access: Admin only
     * Route: PUT /api/admin/settings/{id}
     * 
     * @param int $settingId Setting ID
     * @return void
     */
    public function updateSetting(int $settingId): void
    {
        try {
            // AUTHORIZATION: Require Admin role
            if (!$this->middleware->requireAdmin(true)) {
                return;
            }

            // Get JSON input
            $input = $this->getJsonInput();

            // Validate input
            if (!isset($input['setting_value'])) {
                $this->jsonError('Setting value is required', 400);
                return;
            }

            // Get setting
            $sql = "SELECT * FROM system_settings WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $settingId]);
            $setting = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$setting) {
                $this->jsonError('Setting not found', 404);
                return;
            }

            // Validate and format value based on data type
            $settingValue = $this->validateSettingValue(
                $input['setting_value'], 
                $setting['data_type'],
                $setting['setting_key']
            );

            if ($settingValue === false) {
                $this->jsonError('Invalid setting value for data type: ' . $setting['data_type'], 400);
                return;
            }

            // Update setting
            $sql = "UPDATE system_settings 
                    SET setting_value = :value,
                        description = :description
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'value' => $settingValue,
                'description' => $input['description'] ?? $setting['description'],
                'id' => $settingId
            ]);

            // Log activity
            $this->logActivity(
                $this->middleware->getUserId(),
                'UPDATE',
                'system_settings',
                $settingId,
                "Updated setting: {$setting['setting_key']} = {$settingValue}"
            );

            $this->jsonSuccess([
                'setting_id' => $settingId,
                'setting_key' => $setting['setting_key'],
                'new_value' => $settingValue
            ], 'Setting updated successfully');

        } catch (Exception $e) {
            error_log("Update setting error: " . $e->getMessage());
            $this->jsonError('Failed to update setting: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create a new system setting
     * 
     * Access: Admin only
     * Route: POST /api/admin/settings
     * 
     * @return void
     */
    public function createSetting(): void
    {
        try {
            // AUTHORIZATION: Require Admin role
            if (!$this->middleware->requireAdmin(true)) {
                return;
            }

            // Get JSON input
            $input = $this->getJsonInput();

            // Validate input
            if (!isset($input['setting_key']) || !isset($input['setting_value']) || !isset($input['data_type'])) {
                $this->jsonError('Setting key, value, and data_type are required', 400);
                return;
            }

            // Check if key already exists
            $sql = "SELECT id FROM system_settings WHERE setting_key = :key";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['key' => $input['setting_key']]);
            
            if ($stmt->fetch()) {
                $this->jsonError('Setting key already exists', 400);
                return;
            }

            // Validate data type
            $validTypes = ['string', 'integer', 'boolean', 'date', 'json'];
            if (!in_array($input['data_type'], $validTypes)) {
                $this->jsonError('Invalid data type. Must be: ' . implode(', ', $validTypes), 400);
                return;
            }

            // Validate and format value
            $settingValue = $this->validateSettingValue(
                $input['setting_value'], 
                $input['data_type'],
                $input['setting_key']
            );

            if ($settingValue === false) {
                $this->jsonError('Invalid setting value for data type: ' . $input['data_type'], 400);
                return;
            }

            // Create setting
            $sql = "INSERT INTO system_settings (setting_key, setting_value, description, data_type) 
                    VALUES (:key, :value, :description, :data_type)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'key' => $input['setting_key'],
                'value' => $settingValue,
                'description' => $input['description'] ?? '',
                'data_type' => $input['data_type']
            ]);

            $settingId = $this->db->lastInsertId();

            // Log activity
            $this->logActivity(
                $this->middleware->getUserId(),
                'CREATE',
                'system_settings',
                $settingId,
                "Created setting: {$input['setting_key']}"
            );

            $this->jsonSuccess([
                'setting_id' => $settingId,
                'setting_key' => $input['setting_key']
            ], 'Setting created successfully');

        } catch (Exception $e) {
            error_log("Create setting error: " . $e->getMessage());
            $this->jsonError('Failed to create setting: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete a system setting
     * 
     * Access: Admin only
     * Route: DELETE /api/admin/settings/{id}
     * 
     * @param int $settingId Setting ID
     * @return void
     */
    public function deleteSetting(int $settingId): void
    {
        try {
            // AUTHORIZATION: Require Admin role
            if (!$this->middleware->requireAdmin(true)) {
                return;
            }

            // Get setting
            $sql = "SELECT setting_key FROM system_settings WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $settingId]);
            $setting = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$setting) {
                $this->jsonError('Setting not found', 404);
                return;
            }

            // Prevent deletion of critical settings
            $criticalSettings = [
                'academic_year', 'semester', 'contact_email',
                'max_file_size_mb', 'session_timeout_minutes'
            ];

            if (in_array($setting['setting_key'], $criticalSettings)) {
                $this->jsonError('Cannot delete critical system setting', 403);
                return;
            }

            // Delete setting
            $sql = "DELETE FROM system_settings WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $settingId]);

            // Log activity
            $this->logActivity(
                $this->middleware->getUserId(),
                'DELETE',
                'system_settings',
                $settingId,
                "Deleted setting: {$setting['setting_key']}"
            );

            $this->jsonSuccess([
                'setting_id' => $settingId
            ], 'Setting deleted successfully');

        } catch (Exception $e) {
            error_log("Delete setting error: " . $e->getMessage());
            $this->jsonError('Failed to delete setting', 500);
        }
    }

    /**
     * Validate setting value based on data type
     * 
     * @param mixed $value Value to validate
     * @param string $dataType Data type
     * @param string $key Setting key for special validation
     * @return string|false Formatted value or false if invalid
     */
    private function validateSettingValue($value, string $dataType, string $key)
    {
        switch ($dataType) {
            case 'integer':
                if (!is_numeric($value) || intval($value) != $value) {
                    return false;
                }
                return (string)intval($value);

            case 'boolean':
                if (is_bool($value)) {
                    return $value ? 'true' : 'false';
                }
                if (in_array(strtolower($value), ['true', '1', 'yes', 'on'])) {
                    return 'true';
                }
                if (in_array(strtolower($value), ['false', '0', 'no', 'off'])) {
                    return 'false';
                }
                return false;

            case 'date':
                $date = date_create($value);
                if (!$date) {
                    return false;
                }
                return date_format($date, 'Y-m-d');

            case 'json':
                if (is_array($value)) {
                    $value = json_encode($value);
                }
                $decoded = json_decode($value);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return false;
                }
                return $value;

            case 'string':
            default:
                return (string)$value;
        }
    }

    /**
     * Delete user account
     * 
     * Access: Admin only
     * Route: DELETE /api/admin/users/{id}
     * 
     * @param int $userId User ID
     * @return void
     */
    public function deleteUser(int $userId): void
    {
        try {
            // AUTHORIZATION: Require Admin role
            if (!$this->middleware->requireAdmin(true)) {
                return;
            }

            // Check if user exists
            $sql = "SELECT id, username, role FROM users WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $this->jsonError('User not found', 404);
                return;
            }

            // Prevent admin from deleting themselves
            if ($userId === $this->middleware->getUserId()) {
                $this->jsonError('Cannot delete your own account', 403);
                return;
            }

            // Begin transaction
            $this->db->beginTransaction();

            try {
                // Delete related records based on role
                if ($user['role'] === 'Student') {
                    // Get student ID
                    $sql = "SELECT id FROM students WHERE user_id = :user_id";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute(['user_id' => $userId]);
                    $student = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($student) {
                        // Delete student's registrations, assignments, submissions
                        $this->db->prepare("DELETE FROM submissions WHERE assignment_id IN (SELECT id FROM topic_assignments WHERE registration_id IN (SELECT id FROM topic_registrations WHERE student_id = :student_id))")->execute(['student_id' => $student['id']]);
                        $this->db->prepare("DELETE FROM topic_assignments WHERE registration_id IN (SELECT id FROM topic_registrations WHERE student_id = :student_id)")->execute(['student_id' => $student['id']]);
                        $this->db->prepare("DELETE FROM topic_registrations WHERE student_id = :student_id")->execute(['student_id' => $student['id']]);
                        $this->db->prepare("DELETE FROM students WHERE id = :id")->execute(['id' => $student['id']]);
                    }
                } elseif ($user['role'] === 'Lecturer') {
                    // Get lecturer ID
                    $sql = "SELECT id FROM lecturers WHERE user_id = :user_id";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute(['user_id' => $userId]);
                    $lecturer = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($lecturer) {
                        // Update topics created by lecturer to unassigned
                        $this->db->prepare("UPDATE topics SET created_by = NULL WHERE created_by = :lecturer_id")->execute(['lecturer_id' => $lecturer['id']]);
                        $this->db->prepare("DELETE FROM lecturers WHERE id = :id")->execute(['id' => $lecturer['id']]);
                    }
                }

                // Delete user
                $sql = "DELETE FROM users WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute(['id' => $userId]);

                // Commit transaction
                $this->db->commit();

                // Log activity
                $this->logActivity(
                    $this->middleware->getUserId(),
                    'DELETE',
                    'users',
                    $userId,
                    "Deleted user: {$user['username']} (Role: {$user['role']})"
                );

                $this->jsonSuccess([
                    'deleted_user_id' => $userId,
                    'username' => $user['username']
                ], 'User deleted successfully');

            } catch (Exception $e) {
                // Rollback on error
                $this->db->rollBack();
                throw $e;
            }

        } catch (Exception $e) {
            error_log("Delete user error: " . $e->getMessage());
            $this->jsonError('Failed to delete user: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get all students with detailed information
     * 
     * Access: Admin only
     * Route: GET /api/admin/students
     * 
     * @return void
     */
    public function getStudents(): void
    {
        try {
            // AUTHORIZATION: Require Admin role
            if (!$this->middleware->requireAdmin(true)) {
                return;
            }

            // Query students with department, user, and registration statistics
            $sql = "SELECT 
                        s.id,
                        s.student_code,
                        s.full_name,
                        s.email,
                        s.phone,
                        s.enrollment_year,
                        s.created_at,
                        d.code AS department_code,
                        d.name AS department_name,
                        u.id AS user_id,
                        u.username,
                        u.status AS account_status,
                        (SELECT COUNT(*) 
                         FROM topic_registrations tr 
                         WHERE tr.student_id = s.id) AS total_registrations,
                        (SELECT COUNT(*) 
                         FROM topic_registrations tr 
                         WHERE tr.student_id = s.id 
                         AND tr.status = 'Approved') AS approved_registrations,
                        (SELECT COUNT(*) 
                         FROM topic_registrations tr 
                         WHERE tr.student_id = s.id 
                         AND tr.status = 'Pending') AS pending_registrations,
                        (SELECT t.title
                         FROM topic_registrations tr
                         INNER JOIN topics t ON tr.topic_id = t.id
                         WHERE tr.student_id = s.id
                         AND tr.status = 'Approved'
                         ORDER BY tr.registered_at DESC
                         LIMIT 1) AS current_topic,
                        (SELECT COUNT(*)
                         FROM topic_assignments ta
                         INNER JOIN topic_registrations tr ON ta.registration_id = tr.id
                         WHERE tr.student_id = s.id) AS total_assignments,
                        (SELECT COUNT(*)
                         FROM submissions sub
                         INNER JOIN topic_assignments ta ON sub.assignment_id = ta.id
                         INNER JOIN topic_registrations tr ON ta.registration_id = tr.id
                         WHERE tr.student_id = s.id) AS total_submissions
                    FROM students s
                    INNER JOIN departments d ON s.department_id = d.id
                    INNER JOIN users u ON s.user_id = u.id
                    ORDER BY s.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->jsonSuccess($students, 'Students retrieved successfully');

        } catch (Exception $e) {
            error_log("getStudents ERROR: " . $e->getMessage());
            error_log("getStudents TRACE: " . $e->getTraceAsString());
            $this->jsonError('Failed to retrieve students: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get student details by ID
     * 
     * Access: Admin only
     * Route: GET /api/admin/students/{id}
     * 
     * @param int $studentId Student ID
     * @return void
     */
    public function getStudentDetails(int $studentId): void
    {
        try {
            // AUTHORIZATION: Require Admin role
            if (!$this->middleware->requireAdmin(true)) {
                return;
            }

            // Get student basic info
            $sql = "SELECT 
                        s.*,
                        d.code AS department_code,
                        d.name AS department_name,
                        u.id AS user_id,
                        u.username,
                        u.status AS account_status,
                        u.created_at AS account_created_at
                    FROM students s
                    INNER JOIN departments d ON s.department_id = d.id
                    INNER JOIN users u ON s.user_id = u.id
                    WHERE s.id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $studentId]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$student) {
                $this->jsonError('Student not found', 404);
                return;
            }

            // Get registrations
            $sql = "SELECT 
                        tr.*,
                        t.title AS topic_title,
                        t.description AS topic_description,
                        l.full_name AS lecturer_name,
                        l.lecturer_code
                    FROM topic_registrations tr
                    INNER JOIN topics t ON tr.topic_id = t.id
                    INNER JOIN lecturers l ON t.created_by = l.id
                    WHERE tr.student_id = :student_id
                    ORDER BY tr.registered_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['student_id' => $studentId]);
            $student['registrations'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get assignments
            $sql = "SELECT 
                        ta.*,
                        t.title AS topic_title,
                        l.full_name AS lecturer_name,
                        (SELECT COUNT(*) FROM submissions WHERE assignment_id = ta.id) AS submission_count
                    FROM topic_assignments ta
                    INNER JOIN topic_registrations tr ON ta.registration_id = tr.id
                    INNER JOIN topics t ON tr.topic_id = t.id
                    INNER JOIN lecturers l ON ta.lecturer_id = l.id
                    WHERE tr.student_id = :student_id
                    ORDER BY ta.assigned_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['student_id' => $studentId]);
            $student['assignments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->jsonSuccess($student, 'Student details retrieved successfully');

        } catch (Exception $e) {
            error_log("getStudentDetails ERROR: " . $e->getMessage());
            $this->jsonError('Failed to retrieve student details', 500);
        }
    }

    /**
     * Get all lecturers with detailed information
     * 
     * Access: Admin only
     * Route: GET /api/admin/lecturers
     * 
     * @return void
     */
    public function getLecturers(): void
    {
        try {
            // AUTHORIZATION: Require Admin role
            if (!$this->middleware->requireAdmin(true)) {
                return;
            }

            // Query lecturers with department, user, and topic statistics
            $sql = "SELECT 
                        l.id,
                        l.lecturer_code,
                        l.full_name,
                        l.email,
                        l.phone,
                        l.specialization,
                        l.max_quota,
                        l.created_at,
                        d.code AS department_code,
                        d.name AS department_name,
                        u.id AS user_id,
                        u.username,
                        u.status AS account_status,
                        (SELECT COUNT(*) 
                         FROM topics t 
                         WHERE t.created_by = l.id) AS total_topics,
                        (SELECT COUNT(*) 
                         FROM topics t 
                         WHERE t.created_by = l.id 
                         AND t.status = 'Published') AS published_topics,
                        (SELECT COUNT(*) 
                         FROM topics t 
                         WHERE t.created_by = l.id 
                         AND t.status = 'Draft') AS draft_topics,
                        (SELECT COUNT(DISTINCT tr.student_id)
                         FROM topic_registrations tr
                         INNER JOIN topics t ON tr.topic_id = t.id
                         WHERE t.created_by = l.id
                         AND tr.status = 'Approved') AS total_students,
                        (SELECT COUNT(*)
                         FROM topic_registrations tr
                         INNER JOIN topics t ON tr.topic_id = t.id
                         WHERE t.created_by = l.id
                         AND tr.status = 'Pending') AS pending_approvals,
                        (SELECT COUNT(*)
                         FROM topic_assignments ta
                         WHERE ta.lecturer_id = l.id) AS total_assignments
                    FROM lecturers l
                    INNER JOIN departments d ON l.department_id = d.id
                    INNER JOIN users u ON l.user_id = u.id
                    ORDER BY l.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $lecturers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->jsonSuccess($lecturers, 'Lecturers retrieved successfully');

        } catch (Exception $e) {
            error_log("getLecturers ERROR: " . $e->getMessage());
            error_log("getLecturers TRACE: " . $e->getTraceAsString());
            $this->jsonError('Failed to retrieve lecturers: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get lecturer details by ID
     * 
     * Access: Admin only
     * Route: GET /api/admin/lecturers/{id}
     * 
     * @param int $lecturerId Lecturer ID
     * @return void
     */
    public function getLecturerDetails(int $lecturerId): void
    {
        try {
            // AUTHORIZATION: Require Admin role
            if (!$this->middleware->requireAdmin(true)) {
                return;
            }

            // Get lecturer basic info
            $sql = "SELECT 
                        l.*,
                        d.code AS department_code,
                        d.name AS department_name,
                        u.id AS user_id,
                        u.username,
                        u.status AS account_status,
                        u.created_at AS account_created_at
                    FROM lecturers l
                    INNER JOIN departments d ON l.department_id = d.id
                    INNER JOIN users u ON l.user_id = u.id
                    WHERE l.id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $lecturerId]);
            $lecturer = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$lecturer) {
                $this->jsonError('Lecturer not found', 404);
                return;
            }

            // Get topics
            $sql = "SELECT 
                        t.*,
                        d.name AS department_name,
                        (SELECT COUNT(*) FROM topic_registrations tr WHERE tr.topic_id = t.id) AS registration_count,
                        (SELECT COUNT(*) FROM topic_registrations tr WHERE tr.topic_id = t.id AND tr.status = 'Approved') AS approved_count,
                        (SELECT COUNT(*) FROM topic_registrations tr WHERE tr.topic_id = t.id AND tr.status = 'Pending') AS pending_count
                    FROM topics t
                    INNER JOIN departments d ON t.department_id = d.id
                    WHERE t.created_by = :lecturer_id
                    ORDER BY t.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['lecturer_id' => $lecturerId]);
            $lecturer['topics'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get students (approved registrations)
            $sql = "SELECT 
                        s.id,
                        s.student_code,
                        s.full_name AS student_name,
                        s.email AS student_email,
                        t.title AS topic_title,
                        tr.status AS registration_status,
                        tr.registered_at,
                        d.name AS department_name
                    FROM topic_registrations tr
                    INNER JOIN topics t ON tr.topic_id = t.id
                    INNER JOIN students s ON tr.student_id = s.id
                    INNER JOIN departments d ON s.department_id = d.id
                    WHERE t.created_by = :lecturer_id
                    AND tr.status = 'Approved'
                    ORDER BY tr.registered_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['lecturer_id' => $lecturerId]);
            $lecturer['students'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->jsonSuccess($lecturer, 'Lecturer details retrieved successfully');

        } catch (Exception $e) {
            error_log("getLecturerDetails ERROR: " . $e->getMessage());
            $this->jsonError('Failed to retrieve lecturer details', 500);
        }
    }

    /**
     * Get all topics with detailed information
     * 
     * Access: Admin only
     * Route: GET /api/admin/topics
     * 
     * @return void
     */
    public function getTopics(): void
    {
        try {
            // AUTHORIZATION: Require Admin role
            if (!$this->middleware->requireAdmin(true)) {
                return;
            }

            // Query topics with lecturer, department, and registration statistics
            $sql = "SELECT 
                        t.id,
                        t.title,
                        t.description,
                        t.status,
                        t.max_students,
                        t.created_at,
                        t.updated_at,
                        d.code AS department_code,
                        d.name AS department_name,
                        l.id AS lecturer_id,
                        l.lecturer_code,
                        l.full_name AS lecturer_name,
                        l.email AS lecturer_email,
                        (SELECT COUNT(*) 
                         FROM topic_registrations tr 
                         WHERE tr.topic_id = t.id) AS total_registrations,
                        (SELECT COUNT(*) 
                         FROM topic_registrations tr 
                         WHERE tr.topic_id = t.id 
                         AND tr.status = 'Approved') AS approved_registrations,
                        (SELECT COUNT(*) 
                         FROM topic_registrations tr 
                         WHERE tr.topic_id = t.id 
                         AND tr.status = 'Pending') AS pending_registrations,
                        (SELECT COUNT(*) 
                         FROM topic_registrations tr 
                         WHERE tr.topic_id = t.id 
                         AND tr.status = 'Rejected') AS rejected_registrations,
                        (SELECT GROUP_CONCAT(tag_name SEPARATOR ', ') 
                         FROM topic_tags tt 
                         WHERE tt.topic_id = t.id) AS tags
                    FROM topics t
                    INNER JOIN departments d ON t.department_id = d.id
                    INNER JOIN lecturers l ON t.created_by = l.id
                    ORDER BY t.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->jsonSuccess($topics, 'Topics retrieved successfully');

        } catch (Exception $e) {
            error_log("getTopics ERROR: " . $e->getMessage());
            error_log("getTopics TRACE: " . $e->getTraceAsString());
            $this->jsonError('Failed to retrieve topics: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get topic details by ID
     * 
     * Access: Admin only
     * Route: GET /api/admin/topics/{id}
     * 
     * @param int $topicId Topic ID
     * @return void
     */
    public function getTopicDetails(int $topicId): void
    {
        try {
            // AUTHORIZATION: Require Admin role
            if (!$this->middleware->requireAdmin(true)) {
                return;
            }

            // Get topic basic info
            $sql = "SELECT 
                        t.*,
                        d.code AS department_code,
                        d.name AS department_name,
                        l.id AS lecturer_id,
                        l.lecturer_code,
                        l.full_name AS lecturer_name,
                        l.email AS lecturer_email,
                        l.phone AS lecturer_phone,
                        (SELECT GROUP_CONCAT(tag_name SEPARATOR ', ') 
                         FROM topic_tags tt 
                         WHERE tt.topic_id = t.id) AS tags
                    FROM topics t
                    INNER JOIN departments d ON t.department_id = d.id
                    INNER JOIN lecturers l ON t.created_by = l.id
                    WHERE t.id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $topicId]);
            $topic = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$topic) {
                $this->jsonError('Topic not found', 404);
                return;
            }

            // Get registrations
            $sql = "SELECT 
                        tr.*,
                        s.student_code,
                        s.full_name AS student_name,
                        s.email AS student_email,
                        s.phone AS student_phone,
                        s.enrollment_year,
                        d.name AS student_department
                    FROM topic_registrations tr
                    INNER JOIN students s ON tr.student_id = s.id
                    INNER JOIN departments d ON s.department_id = d.id
                    WHERE tr.topic_id = :topic_id
                    ORDER BY tr.registered_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['topic_id' => $topicId]);
            $topic['registrations'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get assignments
            $sql = "SELECT 
                        ta.*,
                        s.student_code,
                        s.full_name AS student_name,
                        (SELECT COUNT(*) FROM submissions WHERE assignment_id = ta.id) AS submission_count
                    FROM topic_assignments ta
                    INNER JOIN topic_registrations tr ON ta.registration_id = tr.id
                    INNER JOIN students s ON tr.student_id = s.id
                    WHERE tr.topic_id = :topic_id
                    ORDER BY ta.assigned_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['topic_id' => $topicId]);
            $topic['assignments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->jsonSuccess($topic, 'Topic details retrieved successfully');

        } catch (Exception $e) {
            error_log("getTopicDetails ERROR: " . $e->getMessage());
            $this->jsonError('Failed to retrieve topic details', 500);
        }
    }

    /**
     * Update topic status (Admin action)
     * 
     * Access: Admin only
     * Route: PUT /api/admin/topics/{id}/status
     * 
     * @param int $topicId Topic ID
     * @return void
     */
    public function updateTopicStatus(int $topicId): void
    {
        try {
            // AUTHORIZATION: Require Admin role
            if (!$this->middleware->requireAdmin(true)) {
                return;
            }

            // Get JSON input
            $input = $this->getJsonInput();

            // Validate input
            if (!isset($input['status']) || !in_array($input['status'], ['Published', 'Draft', 'Archived'])) {
                $this->jsonError('Invalid status. Must be Published, Draft, or Archived', 400);
                return;
            }

            // Check if topic exists
            $sql = "SELECT id, title, status FROM topics WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $topicId]);
            $topic = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$topic) {
                $this->jsonError('Topic not found', 404);
                return;
            }

            // Update status
            $sql = "UPDATE topics SET status = :status, updated_at = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'status' => $input['status'],
                'id' => $topicId
            ]);

            // Log activity
            $this->logActivity(
                $this->middleware->getUserId(),
                'UPDATE_STATUS',
                'topics',
                $topicId,
                "Changed topic status from {$topic['status']} to {$input['status']}"
            );

            $this->jsonSuccess([
                'topic_id' => $topicId,
                'old_status' => $topic['status'],
                'new_status' => $input['status']
            ], 'Topic status updated successfully');

        } catch (Exception $e) {
            error_log("Update topic status error: " . $e->getMessage());
            $this->jsonError('Failed to update topic status', 500);
        }
    }

    /**
     * Delete topic (Admin action)
     * 
     * Access: Admin only
     * Route: DELETE /api/admin/topics/{id}
     * 
     * @param int $topicId Topic ID
     * @return void
     */
    public function deleteTopic(int $topicId): void
    {
        try {
            // AUTHORIZATION: Require Admin role
            if (!$this->middleware->requireAdmin(true)) {
                return;
            }

            // Check if topic exists
            $sql = "SELECT id, title FROM topics WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $topicId]);
            $topic = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$topic) {
                $this->jsonError('Topic not found', 404);
                return;
            }

            // Check if topic has approved registrations
            $sql = "SELECT COUNT(*) as count FROM topic_registrations WHERE topic_id = :id AND status = 'Approved'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $topicId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                $this->jsonError('Cannot delete topic with approved registrations', 400);
                return;
            }

            // Begin transaction
            $this->db->beginTransaction();

            try {
                // Delete related data
                $this->db->prepare("DELETE FROM submissions WHERE assignment_id IN (SELECT id FROM topic_assignments WHERE registration_id IN (SELECT id FROM topic_registrations WHERE topic_id = :id))")->execute(['id' => $topicId]);
                $this->db->prepare("DELETE FROM topic_assignments WHERE registration_id IN (SELECT id FROM topic_registrations WHERE topic_id = :id)")->execute(['id' => $topicId]);
                $this->db->prepare("DELETE FROM topic_registrations WHERE topic_id = :id")->execute(['id' => $topicId]);
                $this->db->prepare("DELETE FROM topic_tags WHERE topic_id = :id")->execute(['id' => $topicId]);
                $this->db->prepare("DELETE FROM topics WHERE id = :id")->execute(['id' => $topicId]);

                // Commit transaction
                $this->db->commit();

                // Log activity
                $this->logActivity(
                    $this->middleware->getUserId(),
                    'DELETE',
                    'topics',
                    $topicId,
                    "Deleted topic: {$topic['title']}"
                );

                $this->jsonSuccess([
                    'deleted_topic_id' => $topicId,
                    'title' => $topic['title']
                ], 'Topic deleted successfully');

            } catch (Exception $e) {
                // Rollback on error
                $this->db->rollBack();
                throw $e;
            }

        } catch (Exception $e) {
            error_log("Delete topic error: " . $e->getMessage());
            $this->jsonError('Failed to delete topic: ' . $e->getMessage(), 500);
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
