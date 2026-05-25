<?php

/**
 * Topic Controller
 * 
 * Handles HTTP requests related to topic management and registration.
 * Implements RESTful API endpoints for:
 * - Topic registration (POST)
 * - Topic listing (GET)
 * - Registration status checking (GET)
 * 
 * @package App\Controllers
 * @author  Capstone Project Team
 * @version 1.0.0
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\TopicRepository;
use Exception;

class TopicController extends Controller
{
    /**
     * Topic repository instance
     * 
     * @var TopicRepository
     */
    private TopicRepository $topicRepository;

    /**
     * Middleware instance for authentication and authorization
     * 
     * @var Middleware
     */
    private Middleware $middleware;

    /**
     * Constructor - Initialize dependencies
     */
    public function __construct()
    {
        parent::__construct();
        $this->topicRepository = new TopicRepository();
        $this->middleware = new Middleware();
    }

    /**
     * Register student for a topic (POST /api/topics/register)
     * 
     * Required POST parameters:
     * - student_id: int
     * - topic_id: int
     * - lecturer_id: int
     * 
     * Business Rules Enforced:
     * 1. User must be authenticated as Student
     * 2. Registration deadline validation
     * 3. Duplicate registration prevention
     * 4. Lecturer quota validation
     * 5. Automatic audit trail logging
     * 
     * @return void
     */
    public function register(): void
    {
        try {
            // AUTHORIZATION: Require Student role
            if (!$this->middleware->requireStudent(true)) {
                return; // Middleware handles the response
            }

            // Validate request method
            if (!$this->isPost()) {
                $this->jsonError('Invalid request method. POST required.', 405);
                return;
            }

            // Get JSON input or POST data
            $input = $this->getJsonInput(true) ?? $this->getPost();

            // Validate required fields
            $required = ['student_id', 'topic_id', 'lecturer_id'];
            $missing = $this->validateRequired($input, $required);

            if (!empty($missing)) {
                $this->jsonError(
                    'Missing required fields: ' . implode(', ', $missing),
                    400,
                    ['missing_fields' => $missing]
                );
                return;
            }

            // Sanitize and validate input
            $studentId = filter_var($input['student_id'], FILTER_VALIDATE_INT);
            $topicId = filter_var($input['topic_id'], FILTER_VALIDATE_INT);
            $lecturerId = filter_var($input['lecturer_id'], FILTER_VALIDATE_INT);

            if ($studentId === false || $topicId === false || $lecturerId === false) {
                $this->jsonError('Invalid input. All IDs must be valid integers.', 400);
                return;
            }

            // AUTHORIZATION: Verify student is registering for themselves
            $currentUser = $this->middleware->getCurrentUser();
            $currentUserId = $currentUser['id'] ?? null;

            // Get student's user_id to verify ownership
            if (!$this->verifyStudentOwnership($studentId, $currentUserId)) {
                $this->jsonError('You can only register for your own account.', 403);
                return;
            }

            // Process registration through repository (with transaction)
            $result = $this->topicRepository->registerStudentForTopic(
                $studentId,
                $topicId,
                $lecturerId,
                $currentUserId
            );

            // Return success response
            $this->jsonSuccess(
                $result['data'],
                $result['message']
            );

        } catch (Exception $e) {
            // Handle business rule violations and errors
            $statusCode = (int) $e->getCode();

            // Map exception codes to HTTP status codes
            if ($statusCode === 403) {
                $this->jsonError($e->getMessage(), 403);
            } elseif ($statusCode === 404) {
                $this->jsonError($e->getMessage(), 404);
            } elseif ($statusCode === 400) {
                $this->jsonError($e->getMessage(), 400);
            } else {
                // Log unexpected errors
                error_log("Topic Registration Error: " . $e->getMessage());
                $this->jsonError(
                    'An error occurred while processing your registration. Please try again.',
                    500,
                    ['error' => $e->getMessage()]
                );
            }
        }
    }

    /**
     * Verify that the student belongs to the current user
     * 
     * @param int $studentId Student ID
     * @param int $userId    User ID
     * @return bool True if student belongs to user, false otherwise
     */
    private function verifyStudentOwnership(int $studentId, int $userId): bool
    {
        try {
            // Query to check if student belongs to user
            $db = \Config\Database::getInstance()->getConnection();
            $sql = "SELECT id FROM students WHERE id = :student_id AND user_id = :user_id LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'student_id' => $studentId,
                'user_id'    => $userId
            ]);

            return $stmt->fetch() !== false;

        } catch (Exception $e) {
            error_log("Student ownership verification error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all available topics (GET /api/topics)
     * 
     * Optional query parameters:
     * - department_id: int (filter by department)
     * 
     * @return void
     */
    public function index(): void
    {
        try {
            // AUTHORIZATION: Require authentication
            if (!$this->middleware->requireAuth('/login')) {
                return;
            }

            // Get optional department filter
            $departmentId = $this->getQuery('department_id');
            $departmentId = $departmentId ? filter_var($departmentId, FILTER_VALIDATE_INT) : null;

            // Fetch available topics
            $topics = $this->topicRepository->getAvailableTopics($departmentId);

            $this->jsonSuccess(
                $topics,
                'Topics retrieved successfully'
            );

        } catch (Exception $e) {
            error_log("Topic listing error: " . $e->getMessage());
            $this->jsonError('Failed to retrieve topics', 500);
        }
    }

    /**
     * Get topic details by ID (GET /api/topics/{id})
     * 
     * @param int $id Topic ID
     * @return void
     */
    public function show(int $id): void
    {
        try {
            // AUTHORIZATION: Require authentication
            if (!$this->middleware->requireAuth('/login')) {
                return;
            }

            // Validate ID
            if ($id <= 0) {
                $this->jsonError('Invalid topic ID', 400);
                return;
            }

            // Fetch topic details
            $topic = $this->topicRepository->getTopicById($id);

            if (!$topic) {
                $this->jsonError('Topic not found', 404);
                return;
            }

            $this->jsonSuccess(
                $topic,
                'Topic details retrieved successfully'
            );

        } catch (Exception $e) {
            error_log("Topic details error: " . $e->getMessage());
            $this->jsonError('Failed to retrieve topic details', 500);
        }
    }

    /**
     * Get student's registration history (GET /api/topics/registrations/{studentId})
     * 
     * @param int $studentId Student ID
     * @return void
     */
    public function getStudentRegistrations(int $studentId): void
    {
        try {
            // AUTHORIZATION: Require Student role
            if (!$this->middleware->requireStudent(true)) {
                return;
            }

            // Verify student is accessing their own data
            $currentUser = $this->middleware->getCurrentUser();
            $currentUserId = $currentUser['id'] ?? null;

            if (!$this->verifyStudentOwnership($studentId, $currentUserId)) {
                $this->jsonError('You can only view your own registrations.', 403);
                return;
            }

            // Fetch registrations
            $registrations = $this->topicRepository->getStudentRegistrations($studentId);

            $this->jsonSuccess(
                $registrations,
                'Registration history retrieved successfully'
            );

        } catch (Exception $e) {
            error_log("Student registrations error: " . $e->getMessage());
            $this->jsonError('Failed to retrieve registration history', 500);
        }
    }

    /**
     * Check student's registration eligibility (GET /api/topics/eligibility/{studentId})
     * 
     * @param int $studentId Student ID
     * @return void
     */
    public function checkEligibility(int $studentId): void
    {
        try {
            // AUTHORIZATION: Require Student role
            if (!$this->middleware->requireStudent(true)) {
                return;
            }

            // Verify student is checking their own eligibility
            $currentUser = $this->middleware->getCurrentUser();
            $currentUserId = $currentUser['id'] ?? null;

            if (!$this->verifyStudentOwnership($studentId, $currentUserId)) {
                $this->jsonError('You can only check your own eligibility.', 403);
                return;
            }

            // Check eligibility
            $eligibility = $this->topicRepository->checkStudentRegistrationEligibility($studentId);

            $this->jsonSuccess(
                $eligibility,
                $eligibility['eligible'] ? 'Student is eligible to register' : 'Student is not eligible to register'
            );

        } catch (Exception $e) {
            error_log("Eligibility check error: " . $e->getMessage());
            $this->jsonError('Failed to check eligibility', 500);
        }
    }

    /**
     * Withdraw topic registration (POST /api/topics/withdraw)
     * 
     * Required POST parameters:
     * - registration_id: int
     * 
     * @return void
     */
    public function withdraw(): void
    {
        try {
            // AUTHORIZATION: Require Student role
            if (!$this->middleware->requireStudent(true)) {
                return;
            }

            // Validate request method
            if (!$this->isPost()) {
                $this->jsonError('Invalid request method. POST required.', 405);
                return;
            }

            // Get input
            $input = $this->getJsonInput(true) ?? $this->getPost();

            // Validate required fields
            if (!isset($input['registration_id'])) {
                $this->jsonError('Missing required field: registration_id', 400);
                return;
            }

            $registrationId = filter_var($input['registration_id'], FILTER_VALIDATE_INT);

            if ($registrationId === false) {
                $this->jsonError('Invalid registration ID', 400);
                return;
            }

            // TODO: Implement withdrawal logic in repository
            // For now, return a placeholder response
            $this->jsonSuccess(
                ['registration_id' => $registrationId],
                'Withdrawal functionality will be implemented in the next phase'
            );

        } catch (Exception $e) {
            error_log("Withdrawal error: " . $e->getMessage());
            $this->jsonError('Failed to process withdrawal', 500);
        }
    }

    /**
     * Get registration statistics (GET /api/topics/stats)
     * Admin and Lecturer only
     * 
     * @return void
     */
    public function getStatistics(): void
    {
        try {
            // AUTHORIZATION: Require Lecturer or Admin role
            if (!$this->middleware->requireLecturerOrAdmin(true)) {
                return;
            }

            // TODO: Implement statistics logic
            $this->jsonSuccess(
                [],
                'Statistics functionality will be implemented in the next phase'
            );

        } catch (Exception $e) {
            error_log("Statistics error: " . $e->getMessage());
            $this->jsonError('Failed to retrieve statistics', 500);
        }
    }
}
