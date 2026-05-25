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
     * @param string $table     Table name
     * @param string $condition Optional WHERE condition
     * @return int Count
     */
    private function getCount(string $table, string $condition = ''): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$table}";
        if ($condition) {
            $sql .= " WHERE {$condition}";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
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
