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
                    t.created_by,
                    d.name as department_name,
                    d.code as department_code,
                    l.id as lecturer_id,
                    l.full_name as lecturer_name,
                    l.lecturer_code,
                    l.max_quota,
                    (SELECT COUNT(*) 
                     FROM topic_registrations tr 
                     WHERE tr.topic_id = t.id 
                     AND tr.status = 'Approved') as approved_count,
                    (SELECT COUNT(*) 
                     FROM topic_registrations tr 
                     WHERE tr.topic_id = t.id 
                     AND tr.status = 'Pending') as pending_count,
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

    /**
     * Create a new topic (Lecturer only)
     * 
     * @param array $data Topic data
     * @param int $lecturerId Lecturer ID
     * @param int $userId User ID for audit logging
     * @return array Result with topic ID
     * @throws Exception If validation fails
     */
    public function createTopic(array $data, int $lecturerId, int $userId): array
    {
        try {
            // Begin transaction
            $this->db->beginTransaction();

            // Validate required fields
            $required = ['title', 'description', 'department_id'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Missing required field: {$field}", 400);
                }
            }

            // Validate lecturer exists
            $this->validateLecturerExists($lecturerId);

            // Validate department exists
            $sql = "SELECT id FROM departments WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $data['department_id']]);
            if (!$stmt->fetch()) {
                throw new Exception('Department not found', 404);
            }

            // Insert topic
            $sql = "INSERT INTO topics 
                    (title, description, department_id, created_by, status, max_students, created_at, updated_at) 
                    VALUES 
                    (:title, :description, :department_id, :created_by, :status, :max_students, NOW(), NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'title'         => $data['title'],
                'description'   => $data['description'],
                'department_id' => $data['department_id'],
                'created_by'    => $lecturerId,
                'status'        => $data['status'] ?? 'Draft',
                'max_students'  => $data['max_students'] ?? 5
            ]);

            $topicId = (int) $this->db->lastInsertId();

            // Insert tags if provided
            if (!empty($data['tags']) && is_array($data['tags'])) {
                $this->insertTopicTags($topicId, $data['tags']);
            }

            // Log activity
            $this->logActivity(
                $userId,
                'CREATE',
                'topics',
                $topicId,
                "Created new topic: {$data['title']}"
            );

            // Commit transaction
            $this->db->commit();

            // Fetch created topic
            $topic = $this->getTopicById($topicId);

            return [
                'success' => true,
                'message' => 'Topic created successfully',
                'data'    => $topic
            ];

        } catch (Exception $e) {
            // Rollback on error
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Update an existing topic
     * 
     * @param int $topicId Topic ID
     * @param array $data Updated data
     * @param int $lecturerId Lecturer ID (for ownership verification)
     * @param int $userId User ID for audit logging
     * @return array Result
     * @throws Exception If validation fails
     */
    public function updateTopic(int $topicId, array $data, int $lecturerId, int $userId): array
    {
        try {
            // Begin transaction
            $this->db->beginTransaction();

            // Verify topic exists and belongs to lecturer
            $sql = "SELECT id, title, created_by FROM topics WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $topicId]);
            $topic = $stmt->fetch();

            if (!$topic) {
                throw new Exception('Topic not found', 404);
            }

            if ($topic['created_by'] != $lecturerId) {
                throw new Exception('You can only update your own topics', 403);
            }

            // Build update query dynamically
            $updateFields = [];
            $params = ['id' => $topicId];

            if (isset($data['title'])) {
                $updateFields[] = 'title = :title';
                $params['title'] = $data['title'];
            }

            if (isset($data['description'])) {
                $updateFields[] = 'description = :description';
                $params['description'] = $data['description'];
            }

            if (isset($data['status'])) {
                $updateFields[] = 'status = :status';
                $params['status'] = $data['status'];
            }

            if (isset($data['max_students'])) {
                $updateFields[] = 'max_students = :max_students';
                $params['max_students'] = $data['max_students'];
            }

            if (empty($updateFields)) {
                throw new Exception('No fields to update', 400);
            }

            $updateFields[] = 'updated_at = NOW()';

            $sql = "UPDATE topics SET " . implode(', ', $updateFields) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            // Update tags if provided
            if (isset($data['tags']) && is_array($data['tags'])) {
                // Delete old tags
                $sql = "DELETE FROM topic_tags WHERE topic_id = :topic_id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute(['topic_id' => $topicId]);

                // Insert new tags
                $this->insertTopicTags($topicId, $data['tags']);
            }

            // Log activity
            $this->logActivity(
                $userId,
                'UPDATE',
                'topics',
                $topicId,
                "Updated topic: {$topic['title']}"
            );

            // Commit transaction
            $this->db->commit();

            // Fetch updated topic
            $updatedTopic = $this->getTopicById($topicId);

            return [
                'success' => true,
                'message' => 'Topic updated successfully',
                'data'    => $updatedTopic
            ];

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Get topics created by a specific lecturer
     * 
     * @param int $lecturerId Lecturer ID
     * @return array List of topics
     */
    public function getLecturerTopics(int $lecturerId): array
    {
        $sql = "SELECT 
                    t.id,
                    t.title,
                    t.description,
                    t.status,
                    t.max_students,
                    t.created_at,
                    t.updated_at,
                    d.name as department_name,
                    d.code as department_code,
                    (SELECT COUNT(*) 
                     FROM topic_registrations tr 
                     WHERE tr.topic_id = t.id 
                     AND tr.status = 'Approved') as approved_count,
                    (SELECT COUNT(*) 
                     FROM topic_registrations tr 
                     WHERE tr.topic_id = t.id 
                     AND tr.status = 'Pending') as pending_count,
                    (SELECT GROUP_CONCAT(tag_name SEPARATOR ', ') 
                     FROM topic_tags tt 
                     WHERE tt.topic_id = t.id) as tags
                FROM topics t
                INNER JOIN departments d ON t.department_id = d.id
                WHERE t.created_by = :lecturer_id
                ORDER BY t.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['lecturer_id' => $lecturerId]);

        return $stmt->fetchAll();
    }

    /**
     * Get registrations for lecturer's topics
     * 
     * @param int $lecturerId Lecturer ID
     * @param string|null $status Filter by status (optional)
     * @return array List of registrations
     */
    public function getLecturerRegistrations(int $lecturerId, ?string $status = null): array
    {
        $sql = "SELECT 
                    tr.id,
                    tr.topic_id,
                    tr.student_id,
                    tr.status,
                    tr.registered_at,
                    tr.reviewed_at,
                    tr.rejection_reason,
                    s.student_code,
                    s.full_name as student_name,
                    s.email as student_email,
                    s.phone as student_phone,
                    s.enrollment_year,
                    t.title as topic_title,
                    d.name as department_name
                FROM topic_registrations tr
                INNER JOIN students s ON tr.student_id = s.id
                INNER JOIN topics t ON tr.topic_id = t.id
                INNER JOIN departments d ON s.department_id = d.id
                WHERE tr.lecturer_id = :lecturer_id";

        if ($status !== null) {
            $sql .= " AND tr.status = :status";
        }

        $sql .= " ORDER BY tr.registered_at DESC";

        $stmt = $this->db->prepare($sql);
        
        $params = ['lecturer_id' => $lecturerId];
        if ($status !== null) {
            $params['status'] = $status;
        }
        
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Insert topic tags
     * 
     * @param int $topicId Topic ID
     * @param array $tags Array of tag names
     * @return void
     */
    private function insertTopicTags(int $topicId, array $tags): void
    {
        if (empty($tags)) {
            return;
        }

        $sql = "INSERT INTO topic_tags (topic_id, tag_name, created_at) VALUES (:topic_id, :tag_name, NOW())";
        $stmt = $this->db->prepare($sql);

        foreach ($tags as $tag) {
            $tag = trim($tag);
            if (!empty($tag)) {
                $stmt->execute([
                    'topic_id' => $topicId,
                    'tag_name' => $tag
                ]);
            }
        }
    }

    /**
     * Delete a topic
     * 
     * @param int $topicId Topic ID
     * @param int $lecturerId Lecturer ID (for ownership verification)
     * @param int $userId User ID for audit logging
     * @return array Result
     * @throws Exception If validation fails
     */
    public function deleteTopic(int $topicId, int $lecturerId, int $userId): array
    {
        try {
            // Begin transaction
            $this->db->beginTransaction();

            // Verify topic exists and belongs to lecturer
            $sql = "SELECT id, title, created_by FROM topics WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $topicId]);
            $topic = $stmt->fetch();

            if (!$topic) {
                throw new Exception('Topic not found', 404);
            }

            if ($topic['created_by'] != $lecturerId) {
                throw new Exception('You can only delete your own topics', 403);
            }

            // Check if topic has approved registrations
            $sql = "SELECT COUNT(*) as count 
                    FROM topic_registrations 
                    WHERE topic_id = :topic_id 
                    AND status = 'Approved'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['topic_id' => $topicId]);
            $result = $stmt->fetch();

            if ($result['count'] > 0) {
                throw new Exception('Cannot delete topic with approved registrations', 400);
            }

            // Delete topic tags
            $sql = "DELETE FROM topic_tags WHERE topic_id = :topic_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['topic_id' => $topicId]);

            // Delete pending registrations
            $sql = "DELETE FROM topic_registrations 
                    WHERE topic_id = :topic_id 
                    AND status = 'Pending'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['topic_id' => $topicId]);

            // Delete topic
            $sql = "DELETE FROM topics WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $topicId]);

            // Log activity
            $this->logActivity(
                $userId,
                'DELETE',
                'topics',
                $topicId,
                "Deleted topic: {$topic['title']}"
            );

            // Commit transaction
            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Topic deleted successfully',
                'data'    => ['topic_id' => $topicId]
            ];

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Approve a student registration
     * 
     * @param int $registrationId Registration ID
     * @param int $lecturerId Lecturer ID (for ownership verification)
     * @param int $userId User ID for audit logging
     * @return array Result
     * @throws Exception If validation fails
     */
    public function approveRegistration(int $registrationId, int $lecturerId, int $userId): array
    {
        try {
            // Begin transaction
            $this->db->beginTransaction();

            // Verify registration exists and belongs to lecturer
            $sql = "SELECT tr.id, tr.status, tr.lecturer_id, tr.student_id, tr.topic_id,
                           s.full_name as student_name, t.title as topic_title
                    FROM topic_registrations tr
                    INNER JOIN students s ON tr.student_id = s.id
                    INNER JOIN topics t ON tr.topic_id = t.id
                    WHERE tr.id = :id 
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $registrationId]);
            $registration = $stmt->fetch();

            if (!$registration) {
                throw new Exception('Registration not found', 404);
            }

            if ($registration['lecturer_id'] != $lecturerId) {
                throw new Exception('You can only approve registrations for your own topics', 403);
            }

            if ($registration['status'] !== 'Pending') {
                throw new Exception("Registration is already {$registration['status']}", 400);
            }

            // Check lecturer quota before approving
            $this->validateLecturerQuota($lecturerId);

            // Update registration status
            $sql = "UPDATE topic_registrations 
                    SET status = 'Approved', reviewed_at = NOW() 
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $registrationId]);

            // Log activity
            $this->logActivity(
                $userId,
                'UPDATE',
                'topic_registrations',
                $registrationId,
                "Approved registration for student: {$registration['student_name']}, topic: {$registration['topic_title']}"
            );

            // Commit transaction
            $this->db->commit();

            // Fetch updated registration
            $updatedRegistration = $this->getRegistrationById($registrationId);

            return [
                'success' => true,
                'message' => 'Registration approved successfully',
                'data'    => $updatedRegistration
            ];

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Reject a student registration
     * 
     * @param int $registrationId Registration ID
     * @param int $lecturerId Lecturer ID (for ownership verification)
     * @param string $reason Rejection reason
     * @param int $userId User ID for audit logging
     * @return array Result
     * @throws Exception If validation fails
     */
    public function rejectRegistration(int $registrationId, int $lecturerId, string $reason, int $userId): array
    {
        try {
            // Begin transaction
            $this->db->beginTransaction();

            // Verify registration exists and belongs to lecturer
            $sql = "SELECT tr.id, tr.status, tr.lecturer_id, tr.student_id, tr.topic_id,
                           s.full_name as student_name, t.title as topic_title
                    FROM topic_registrations tr
                    INNER JOIN students s ON tr.student_id = s.id
                    INNER JOIN topics t ON tr.topic_id = t.id
                    WHERE tr.id = :id 
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $registrationId]);
            $registration = $stmt->fetch();

            if (!$registration) {
                throw new Exception('Registration not found', 404);
            }

            if ($registration['lecturer_id'] != $lecturerId) {
                throw new Exception('You can only reject registrations for your own topics', 403);
            }

            if ($registration['status'] !== 'Pending') {
                throw new Exception("Registration is already {$registration['status']}", 400);
            }

            // Update registration status
            $sql = "UPDATE topic_registrations 
                    SET status = 'Rejected', reviewed_at = NOW(), rejection_reason = :reason 
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'id' => $registrationId,
                'reason' => $reason
            ]);

            // Log activity
            $this->logActivity(
                $userId,
                'UPDATE',
                'topic_registrations',
                $registrationId,
                "Rejected registration for student: {$registration['student_name']}, topic: {$registration['topic_title']}, reason: {$reason}"
            );

            // Commit transaction
            $this->db->commit();

            // Fetch updated registration
            $updatedRegistration = $this->getRegistrationById($registrationId);

            return [
                'success' => true,
                'message' => 'Registration rejected successfully',
                'data'    => $updatedRegistration
            ];

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Withdraw/Cancel a student's topic registration
     * 
     * Business Rules:
     * - Only Pending or Approved registrations can be withdrawn
     * - Student can only withdraw their own registration
     * - Uses database transaction for data integrity
     * - Logs activity to audit trail
     * 
     * @param int $studentId Student ID (for ownership verification)
     * @param int $userId User ID for audit logging
     * @return array Result with success status and message
     * @throws Exception If validation fails or no active registration found
     */
    public function withdrawRegistration(int $studentId, int $userId): array
    {
        try {
            // Begin transaction for data integrity
            $this->db->beginTransaction();

            // Find student's active registration (Pending or Approved)
            $sql = "SELECT 
                        tr.id,
                        tr.student_id,
                        tr.topic_id,
                        tr.lecturer_id,
                        tr.status,
                        tr.registered_at,
                        t.title as topic_title,
                        l.full_name as lecturer_name
                    FROM topic_registrations tr
                    INNER JOIN topics t ON tr.topic_id = t.id
                    INNER JOIN lecturers l ON tr.lecturer_id = l.id
                    WHERE tr.student_id = :student_id 
                    AND tr.status IN ('Pending', 'Approved')
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['student_id' => $studentId]);
            $registration = $stmt->fetch();

            // Validate registration exists
            if (!$registration) {
                throw new Exception(
                    'No active registration found. You can only withdraw Pending or Approved registrations.',
                    404
                );
            }

            // Store registration details for logging
            $registrationId = $registration['id'];
            $topicTitle = $registration['topic_title'];
            $lecturerName = $registration['lecturer_name'];
            $previousStatus = $registration['status'];

            // Delete the registration
            $sql = "DELETE FROM topic_registrations WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $registrationId]);

            // Log activity to audit trail
            $this->logActivity(
                $userId,
                'DELETE',
                'topic_registrations',
                $registrationId,
                "Student withdrew {$previousStatus} registration for topic: '{$topicTitle}' (Lecturer: {$lecturerName})"
            );

            // Commit transaction
            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Registration withdrawn successfully. You can now register for other topics.',
                'data'    => [
                    'registration_id' => $registrationId,
                    'topic_title'     => $topicTitle,
                    'previous_status' => $previousStatus,
                    'withdrawn_at'    => date('Y-m-d H:i:s')
                ]
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
     * Get student's approved project with detailed information
     * 
     * @param int $studentId Student ID
     * @return array|null Project data or null if no approved project
     */
    public function getStudentApprovedProject(int $studentId): ?array
    {
        $sql = "SELECT 
                    tr.id as registration_id,
                    tr.student_id,
                    tr.topic_id,
                    tr.lecturer_id,
                    tr.status,
                    tr.registered_at,
                    tr.reviewed_at,
                    tr.progress_percentage,
                    tr.progress_notes,
                    tr.progress_updated_at,
                    s.student_code,
                    s.full_name as student_name,
                    s.email as student_email,
                    s.phone as student_phone,
                    t.title as topic_title,
                    t.description as topic_description,
                    t.max_students,
                    l.full_name as lecturer_name,
                    l.lecturer_code,
                    l.email as lecturer_email,
                    l.phone as lecturer_phone,
                    d.name as department_name,
                    d.code as department_code,
                    (SELECT GROUP_CONCAT(tag_name SEPARATOR ', ') 
                     FROM topic_tags tt 
                     WHERE tt.topic_id = t.id) as tags
                FROM topic_registrations tr
                INNER JOIN students s ON tr.student_id = s.id
                INNER JOIN topics t ON tr.topic_id = t.id
                INNER JOIN lecturers l ON tr.lecturer_id = l.id
                INNER JOIN departments d ON t.department_id = d.id
                WHERE tr.student_id = :student_id 
                AND tr.status = 'Approved'
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['student_id' => $studentId]);

        return $stmt->fetch() ?: null;
    }

    /**
     * Update student project progress
     * 
     * @param int $studentId Student ID
     * @param int $progress Progress percentage (0-100)
     * @param string|null $notes Progress notes
     * @param int $userId User ID for audit logging
     * @return array Result
     * @throws Exception If no approved project found
     */
    public function updateProjectProgress(int $studentId, int $progress, ?string $notes, int $userId): array
    {
        try {
            // Begin transaction
            $this->db->beginTransaction();

            // Find student's approved registration
            $sql = "SELECT id, topic_id FROM topic_registrations 
                    WHERE student_id = :student_id AND status = 'Approved' LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['student_id' => $studentId]);
            $registration = $stmt->fetch();

            if (!$registration) {
                throw new Exception('No approved project found. You must have an approved project to update progress.', 404);
            }

            // Update progress
            $sql = "UPDATE topic_registrations 
                    SET progress_percentage = :progress,
                        progress_notes = :notes,
                        progress_updated_at = NOW()
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'progress' => $progress,
                'notes'    => $notes,
                'id'       => $registration['id']
            ]);

            // Log activity
            $this->logActivity(
                $userId,
                'UPDATE',
                'topic_registrations',
                $registration['id'],
                "Updated project progress to {$progress}%"
            );

            // Commit transaction
            $this->db->commit();

            // Fetch updated project
            $project = $this->getStudentApprovedProject($studentId);

            return [
                'success' => true,
                'message' => 'Project progress updated successfully',
                'data'    => $project
            ];

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * ==================================================
     * SUBMISSION MANAGEMENT METHODS
     * ==================================================
     */

    /**
     * Get student's approved assignment (for submission access)
     * 
     * @param int $studentId Student ID
     * @return array|null Assignment data or null if not found
     */
    public function getStudentAssignment(int $studentId): ?array
    {
        $sql = "SELECT 
                    ta.id as assignment_id,
                    ta.registration_id,
                    ta.lecturer_id,
                    ta.assigned_at,
                    tr.student_id,
                    tr.topic_id,
                    tr.status as registration_status,
                    t.title as topic_title,
                    t.description as topic_description,
                    s.student_code,
                    s.full_name as student_name,
                    l.full_name as lecturer_name,
                    l.email as lecturer_email
                FROM topic_assignments ta
                INNER JOIN topic_registrations tr ON ta.registration_id = tr.id
                INNER JOIN topics t ON tr.topic_id = t.id
                INNER JOIN students s ON tr.student_id = s.id
                INNER JOIN lecturers l ON ta.lecturer_id = l.id
                WHERE tr.student_id = :student_id
                AND tr.status = 'Approved'
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['student_id' => $studentId]);

        return $stmt->fetch() ?: null;
    }

    /**
     * Get all active milestones
     * 
     * @return array List of milestones
     */
    public function getActiveMilestones(): array
    {
        $sql = "SELECT 
                    id,
                    title,
                    description,
                    deadline,
                    weight_percentage,
                    sequence_order,
                    is_active
                FROM milestones
                WHERE is_active = TRUE
                ORDER BY sequence_order ASC, deadline ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get student's submissions with milestone details
     * 
     * @param int $studentId Student ID
     * @return array List of submissions
     */
    public function getStudentSubmissions(int $studentId): array
    {
        // First, get the student's assignment
        $assignment = $this->getStudentAssignment($studentId);
        
        if (!$assignment) {
            return [];
        }

        $assignmentId = $assignment['assignment_id'];

        $sql = "SELECT 
                    s.id,
                    s.assignment_id,
                    s.milestone_id,
                    s.file_url,
                    s.file_name,
                    s.submitted_at,
                    s.is_late,
                    s.comments,
                    m.title as milestone_title,
                    m.description as milestone_description,
                    m.deadline as milestone_deadline,
                    m.weight_percentage,
                    m.sequence_order,
                    -- Get evaluation score if exists
                    (SELECT SUM(es.score) 
                     FROM evaluation_scores es 
                     WHERE es.submission_id = s.id) as total_score,
                    (SELECT COUNT(*) 
                     FROM evaluation_scores es 
                     WHERE es.submission_id = s.id) as evaluation_count
                FROM submissions s
                INNER JOIN milestones m ON s.milestone_id = m.id
                WHERE s.assignment_id = :assignment_id
                ORDER BY m.sequence_order ASC, s.submitted_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['assignment_id' => $assignmentId]);

        return $stmt->fetchAll();
    }

    /**
     * Get submission details by ID
     * 
     * @param int $submissionId Submission ID
     * @return array|null Submission data or null if not found
     */
    public function getSubmissionById(int $submissionId): ?array
    {
        $sql = "SELECT 
                    s.*,
                    m.title as milestone_title,
                    m.description as milestone_description,
                    m.deadline as milestone_deadline,
                    m.weight_percentage,
                    ta.registration_id,
                    tr.student_id,
                    tr.topic_id,
                    st.full_name as student_name,
                    st.student_code
                FROM submissions s
                INNER JOIN milestones m ON s.milestone_id = m.id
                INNER JOIN topic_assignments ta ON s.assignment_id = ta.id
                INNER JOIN topic_registrations tr ON ta.registration_id = tr.id
                INNER JOIN students st ON tr.student_id = st.id
                WHERE s.id = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $submissionId]);

        return $stmt->fetch() ?: null;
    }

    /**
     * Check if student has already submitted for a milestone
     * 
     * @param int $assignmentId Assignment ID
     * @param int $milestoneId Milestone ID
     * @return bool True if already submitted, false otherwise
     */
    public function hasSubmittedForMilestone(int $assignmentId, int $milestoneId): bool
    {
        $sql = "SELECT COUNT(*) as count 
                FROM submissions 
                WHERE assignment_id = :assignment_id 
                AND milestone_id = :milestone_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'assignment_id' => $assignmentId,
            'milestone_id'  => $milestoneId
        ]);

        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    /**
     * Create a new submission
     * 
     * @param array $data Submission data
     * @param int $studentId Student ID (for validation)
     * @param int $userId User ID for audit logging
     * @return array Result with submission ID
     * @throws Exception If validation fails
     */
    public function createSubmission(array $data, int $studentId, int $userId): array
    {
        try {
            // Begin transaction
            $this->db->beginTransaction();

            // Validate required fields
            $required = ['milestone_id', 'file_url', 'file_name'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Missing required field: {$field}", 400);
                }
            }

            // Get student's assignment
            $assignment = $this->getStudentAssignment($studentId);
            
            if (!$assignment) {
                throw new Exception('You do not have an approved project assignment yet.', 403);
            }

            $assignmentId = $assignment['assignment_id'];
            $milestoneId = (int) $data['milestone_id'];

            // Check if already submitted for this milestone
            if ($this->hasSubmittedForMilestone($assignmentId, $milestoneId)) {
                throw new Exception('You have already submitted for this milestone. Please contact your lecturer if you need to resubmit.', 400);
            }

            // Validate milestone exists and is active
            $sql = "SELECT id, title, deadline, is_active 
                    FROM milestones 
                    WHERE id = :id 
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $milestoneId]);
            $milestone = $stmt->fetch();

            if (!$milestone) {
                throw new Exception('Milestone not found', 404);
            }

            if (!$milestone['is_active']) {
                throw new Exception('This milestone is no longer active for submissions', 400);
            }

            // Check if submission is late
            $isLate = (strtotime($milestone['deadline']) < time());

            // Insert submission
            $sql = "INSERT INTO submissions 
                    (assignment_id, milestone_id, file_url, file_name, submitted_at, is_late, comments) 
                    VALUES 
                    (:assignment_id, :milestone_id, :file_url, :file_name, NOW(), :is_late, :comments)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'assignment_id' => $assignmentId,
                'milestone_id'  => $milestoneId,
                'file_url'      => $data['file_url'],
                'file_name'     => $data['file_name'],
                'is_late'       => $isLate ? 1 : 0,
                'comments'      => $data['comments'] ?? null
            ]);

            $submissionId = (int) $this->db->lastInsertId();

            // Log activity
            $this->logActivity(
                $userId,
                'CREATE',
                'submissions',
                $submissionId,
                "Submitted work for milestone: {$milestone['title']}"
            );

            // Commit transaction
            $this->db->commit();

            // Fetch created submission
            $submission = $this->getSubmissionById($submissionId);

            return [
                'success' => true,
                'message' => $isLate ? 
                    'Submission uploaded successfully (marked as late).' : 
                    'Submission uploaded successfully.',
                'data'    => $submission,
                'is_late' => $isLate
            ];

        } catch (Exception $e) {
            // Rollback on error
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Get milestones with submission status for a student
     * 
     * @param int $studentId Student ID
     * @return array List of milestones with submission status
     */
    public function getMilestonesWithSubmissionStatus(int $studentId): array
    {
        // Get student's assignment
        $assignment = $this->getStudentAssignment($studentId);
        
        if (!$assignment) {
            // Return all milestones without submission status
            return $this->getActiveMilestones();
        }

        $assignmentId = $assignment['assignment_id'];

        $sql = "SELECT 
                    m.id,
                    m.title,
                    m.description,
                    m.deadline,
                    m.weight_percentage,
                    m.sequence_order,
                    m.is_active,
                    s.id as submission_id,
                    s.file_url,
                    s.file_name,
                    s.submitted_at,
                    s.is_late,
                    s.comments as submission_comments,
                    (SELECT SUM(es.score) 
                     FROM evaluation_scores es 
                     WHERE es.submission_id = s.id) as total_score,
                    (SELECT COUNT(*) 
                     FROM evaluation_scores es 
                     WHERE es.submission_id = s.id) as evaluation_count,
                    CASE 
                        WHEN s.id IS NULL THEN 'Not Submitted'
                        WHEN EXISTS (SELECT 1 FROM evaluation_scores es WHERE es.submission_id = s.id) THEN 'Graded'
                        ELSE 'Submitted'
                    END as status
                FROM milestones m
                LEFT JOIN submissions s ON m.id = s.milestone_id AND s.assignment_id = :assignment_id
                WHERE m.is_active = TRUE
                ORDER BY m.sequence_order ASC, m.deadline ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['assignment_id' => $assignmentId]);

        return $stmt->fetchAll();
    }

    /**
     * Delete a submission (before grading)
     * 
     * @param int $submissionId Submission ID
     * @param int $studentId Student ID (for ownership verification)
     * @param int $userId User ID for audit logging
     * @return array Result
     * @throws Exception If validation fails
     */
    public function deleteSubmission(int $submissionId, int $studentId, int $userId): array
    {
        try {
            // Begin transaction
            $this->db->beginTransaction();

            // Verify submission exists and belongs to student
            $submission = $this->getSubmissionById($submissionId);

            if (!$submission) {
                throw new Exception('Submission not found', 404);
            }

            if ($submission['student_id'] != $studentId) {
                throw new Exception('You can only delete your own submissions', 403);
            }

            // Check if submission has been graded
            $sql = "SELECT COUNT(*) as count 
                    FROM evaluation_scores 
                    WHERE submission_id = :submission_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['submission_id' => $submissionId]);
            $result = $stmt->fetch();

            if ($result['count'] > 0) {
                throw new Exception('Cannot delete a graded submission. Please contact your lecturer.', 400);
            }

            // Delete the submission
            $sql = "DELETE FROM submissions WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $submissionId]);

            // Log activity
            $this->logActivity(
                $userId,
                'DELETE',
                'submissions',
                $submissionId,
                "Deleted submission for milestone: {$submission['milestone_title']}"
            );

            // Commit transaction
            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Submission deleted successfully',
                'data'    => ['submission_id' => $submissionId]
            ];

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
}
