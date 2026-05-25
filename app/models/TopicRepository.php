<?php

/**
 * Topic Repository Class
 * 
 * Implements the Repository Pattern for Topic-related database operations.
 * Handles complex business logic for topic registration including:
 * - Deadline validation
 * - Duplicate prevention
 * - Lecturer quota checking
 * - Transaction management
 * - Audit trail logging
 * 
 * @package App\Models
 * @author  Capstone Project Team
 * @version 1.0.0
 */

namespace App\Models;

use Config\Database;
use PDO;
use PDOException;
use Exception;

class TopicRepository
{
    /**
     * PDO database connection
     * 
     * @var PDO
     */
    private PDO $db;

    /**
     * Constructor - Initialize database connection
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Register a student for a topic with comprehensive business rule validation
     * 
     * Business Rules Enforced:
     * 1. Registration deadline check
     * 2. Duplicate registration prevention
     * 3. Lecturer quota validation
     * 4. Audit trail logging
     * 
     * @param int $studentId  Student ID
     * @param int $topicId    Topic ID
     * @param int $lecturerId Lecturer ID
     * @param int $userId     User ID for audit logging
     * @return array Result array with success status and data
     * @throws Exception If business rules are violated
     */
    public function registerStudentForTopic(int $studentId, int $topicId, int $lecturerId, int $userId): array
    {
        try {
            // Begin transaction for data integrity
            $this->db->beginTransaction();

            // BUSINESS RULE 1: Check registration deadline
            $this->validateRegistrationDeadline();

            // BUSINESS RULE 2: Check for duplicate registrations
            $this->validateNoDuplicateRegistration($studentId);

            // BUSINESS RULE 3: Validate lecturer quota
            $this->validateLecturerQuota($lecturerId);

            // Validate that topic exists and is published
            $this->validateTopicAvailability($topicId);

            // Validate that student exists
            $this->validateStudentExists($studentId);

            // Validate that lecturer exists
            $this->validateLecturerExists($lecturerId);

            // Insert topic registration
            $registrationId = $this->insertTopicRegistration($studentId, $topicId, $lecturerId);

            // BUSINESS RULE 4: Create audit trail
            $this->logActivity(
                $userId,
                'CREATE',
                'topic_registrations',
                $registrationId,
                "Student registered for topic ID: {$topicId} with lecturer ID: {$lecturerId}"
            );

            // Commit transaction
            $this->db->commit();

            // Fetch the created registration details
            $registration = $this->getRegistrationById($registrationId);

            return [
                'success' => true,
                'message' => 'Topic registration submitted successfully. Awaiting lecturer approval.',
                'data'    => $registration
            ];

        } catch (Exception $e) {
            // Rollback transaction on any error
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            // Re-throw exception to be handled by controller
            throw $e;
        }
    }

    /**
     * Validate registration deadline from system settings
     * 
     * @return void
     * @throws Exception If deadline has passed
     */
    private function validateRegistrationDeadline(): void
    {
        $sql = "SELECT setting_value 
                FROM system_settings 
                WHERE setting_key = :key 
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['key' => 'registration_deadline']);
        $result = $stmt->fetch();

        if (!$result) {
            throw new Exception('Registration deadline setting not found in system', 500);
        }

        $deadline = $result['setting_value'];
        $currentDate = date('Y-m-d');

        if ($currentDate > $deadline) {
            throw new Exception(
                "Registration deadline has passed. Deadline was: {$deadline}",
                403
            );
        }
    }

    /**
     * Validate that student doesn't have existing pending or approved registrations
     * 
     * @param int $studentId Student ID
     * @return void
     * @throws Exception If duplicate registration found
     */
    private function validateNoDuplicateRegistration(int $studentId): void
    {
        $sql = "SELECT COUNT(*) as count 
                FROM topic_registrations 
                WHERE student_id = :student_id 
                AND status IN ('Pending', 'Approved')";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['student_id' => $studentId]);
        $result = $stmt->fetch();

        if ($result['count'] > 0) {
            throw new Exception(
                'You already have a pending or approved topic registration. Students can only register for one topic at a time.',
                400
            );
        }
    }

    /**
     * Validate lecturer quota availability
     * 
     * @param int $lecturerId Lecturer ID
     * @return void
     * @throws Exception If lecturer quota is exceeded
     */
    private function validateLecturerQuota(int $lecturerId): void
    {
        // Get lecturer's maximum quota
        $sql = "SELECT max_quota 
                FROM lecturers 
                WHERE id = :lecturer_id 
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['lecturer_id' => $lecturerId]);
        $lecturer = $stmt->fetch();

        if (!$lecturer) {
            throw new Exception('Lecturer not found', 404);
        }

        $maxQuota = (int) $lecturer['max_quota'];

        // Count current approved and pending registrations for this lecturer
        $sql = "SELECT COUNT(*) as current_count 
                FROM topic_registrations 
                WHERE lecturer_id = :lecturer_id 
                AND status IN ('Approved', 'Pending')";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['lecturer_id' => $lecturerId]);
        $result = $stmt->fetch();

        $currentCount = (int) $result['current_count'];

        if ($currentCount >= $maxQuota) {
            throw new Exception(
                "Lecturer has reached maximum quota ({$maxQuota} students). Current count: {$currentCount}. Please choose another lecturer.",
                400
            );
        }
    }

    /**
     * Validate that topic exists and is available for registration
     * 
     * @param int $topicId Topic ID
     * @return void
     * @throws Exception If topic is not available
     */
    private function validateTopicAvailability(int $topicId): void
    {
        $sql = "SELECT id, title, status 
                FROM topics 
                WHERE id = :topic_id 
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['topic_id' => $topicId]);
        $topic = $stmt->fetch();

        if (!$topic) {
            throw new Exception('Topic not found', 404);
        }

        if ($topic['status'] !== 'Published') {
            throw new Exception(
                "Topic '{$topic['title']}' is not available for registration. Status: {$topic['status']}",
                400
            );
        }
    }

    /**
     * Validate that student exists
     * 
     * @param int $studentId Student ID
     * @return void
     * @throws Exception If student not found
     */
    private function validateStudentExists(int $studentId): void
    {
        $sql = "SELECT id FROM students WHERE id = :student_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['student_id' => $studentId]);

        if (!$stmt->fetch()) {
            throw new Exception('Student not found', 404);
        }
    }

    /**
     * Validate that lecturer exists
     * 
     * @param int $lecturerId Lecturer ID
     * @return void
     * @throws Exception If lecturer not found
     */
    private function validateLecturerExists(int $lecturerId): void
    {
        $sql = "SELECT id FROM lecturers WHERE id = :lecturer_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['lecturer_id' => $lecturerId]);

        if (!$stmt->fetch()) {
            throw new Exception('Lecturer not found', 404);
        }
    }

    /**
     * Insert topic registration record
     * 
     * @param int $studentId  Student ID
     * @param int $topicId    Topic ID
     * @param int $lecturerId Lecturer ID
     * @return int Inserted registration ID
     * @throws PDOException If insert fails
     */
    private function insertTopicRegistration(int $studentId, int $topicId, int $lecturerId): int
    {
        $sql = "INSERT INTO topic_registrations 
                (student_id, topic_id, lecturer_id, status, registered_at) 
                VALUES 
                (:student_id, :topic_id, :lecturer_id, 'Pending', NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'student_id'  => $studentId,
            'topic_id'    => $topicId,
            'lecturer_id' => $lecturerId
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Log activity to audit trail
     * 
     * @param int         $userId      User ID performing the action
     * @param string      $action      Action type (CREATE, UPDATE, DELETE, etc.)
     * @param string      $targetTable Target table name
     * @param int         $targetId    Target record ID
     * @param string|null $details     Additional details
     * @return void
     */
    private function logActivity(int $userId, string $action, string $targetTable, int $targetId, ?string $details = null): void
    {
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
    }

    /**
     * Get registration details by ID
     * 
     * @param int $registrationId Registration ID
     * @return array|null Registration data or null if not found
     */
    private function getRegistrationById(int $registrationId): ?array
    {
        $sql = "SELECT 
                    tr.id,
                    tr.student_id,
                    tr.topic_id,
                    tr.lecturer_id,
                    tr.status,
                    tr.registered_at,
                    s.full_name as student_name,
                    s.student_code,
                    t.title as topic_title,
                    l.full_name as lecturer_name,
                    l.lecturer_code
                FROM topic_registrations tr
                INNER JOIN students s ON tr.student_id = s.id
                INNER JOIN topics t ON tr.topic_id = t.id
                INNER JOIN lecturers l ON tr.lecturer_id = l.id
                WHERE tr.id = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $registrationId]);

        return $stmt->fetch() ?: null;
    }

    /**
     * Get all available topics for registration
     * 
     * @param int|null $departmentId Filter by department (optional)
     * @return array List of available topics
     */
    public function getAvailableTopics(?int $departmentId = null): array
    {
        $sql = "SELECT 
                    t.id,
                    t.title,
                    t.description,
                    t.status,
                    t.max_students,
                    t.created_at,
                    d.name as department_name,
                    d.code as department_code,
                    l.full_name as lecturer_name,
                    l.lecturer_code,
                    l.max_quota,
                    (SELECT COUNT(*) 
                     FROM topic_registrations tr 
                     WHERE tr.topic_id = t.id 
                     AND tr.status IN ('Approved', 'Pending')) as current_registrations,
                    (SELECT GROUP_CONCAT(tag_name SEPARATOR ', ') 
                     FROM topic_tags tt 
                     WHERE tt.topic_id = t.id) as tags
                FROM topics t
                INNER JOIN departments d ON t.department_id = d.id
                INNER JOIN lecturers l ON t.created_by = l.id
                WHERE t.status = 'Published'";

        if ($departmentId !== null) {
            $sql .= " AND t.department_id = :department_id";
        }

        $sql .= " ORDER BY t.created_at DESC";

        $stmt = $this->db->prepare($sql);

        if ($departmentId !== null) {
            $stmt->execute(['department_id' => $departmentId]);
        } else {
            $stmt->execute();
        }

        return $stmt->fetchAll();
    }

    /**
     * Get student's registration history
     * 
     * @param int $studentId Student ID
     * @return array List of registrations
     */
    public function getStudentRegistrations(int $studentId): array
    {
        $sql = "SELECT 
                    tr.id,
                    tr.topic_id,
                    tr.lecturer_id,
                    tr.status,
                    tr.registered_at,
                    tr.reviewed_at,
                    tr.rejection_reason,
                    t.title as topic_title,
                    t.description as topic_description,
                    l.full_name as lecturer_name,
                    l.lecturer_code
                FROM topic_registrations tr
                INNER JOIN topics t ON tr.topic_id = t.id
                INNER JOIN lecturers l ON tr.lecturer_id = l.id
                WHERE tr.student_id = :student_id
                ORDER BY tr.registered_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['student_id' => $studentId]);

        return $stmt->fetchAll();
    }

    /**
     * Get topic details by ID
     * 
     * @param int $topicId Topic ID
     * @return array|null Topic data or null if not found
     */
    public function getTopicById(int $topicId): ?array
    {
        $sql = "SELECT 
                    t.*,
                    d.name as department_name,
                    d.code as department_code,
                    l.full_name as lecturer_name,
                    l.lecturer_code,
                    l.email as lecturer_email,
                    (SELECT COUNT(*) 
                     FROM topic_registrations tr 
                     WHERE tr.topic_id = t.id 
                     AND tr.status = 'Approved') as approved_count,
                    (SELECT GROUP_CONCAT(tag_name SEPARATOR ', ') 
                     FROM topic_tags tt 
                     WHERE tt.topic_id = t.id) as tags
                FROM topics t
                INNER JOIN departments d ON t.department_id = d.id
                INNER JOIN lecturers l ON t.created_by = l.id
                WHERE t.id = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $topicId]);

        return $stmt->fetch() ?: null;
    }

    /**
     * Check if student can register for topics
     * 
     * @param int $studentId Student ID
     * @return array Status information
     */
    public function checkStudentRegistrationEligibility(int $studentId): array
    {
        // Check for existing registrations
        $sql = "SELECT status, registered_at 
                FROM topic_registrations 
                WHERE student_id = :student_id 
                AND status IN ('Pending', 'Approved')
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['student_id' => $studentId]);
        $existing = $stmt->fetch();

        if ($existing) {
            return [
                'eligible' => false,
                'reason'   => "You have an existing {$existing['status']} registration",
                'status'   => $existing['status']
            ];
        }

        // Check deadline
        $sql = "SELECT setting_value 
                FROM system_settings 
                WHERE setting_key = 'registration_deadline' 
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $deadline = $stmt->fetch();

        if ($deadline && date('Y-m-d') > $deadline['setting_value']) {
            return [
                'eligible' => false,
                'reason'   => 'Registration deadline has passed',
                'deadline' => $deadline['setting_value']
            ];
        }

        return [
            'eligible' => true,
            'reason'   => 'Student is eligible to register',
            'deadline' => $deadline['setting_value'] ?? null
        ];
    }
}
