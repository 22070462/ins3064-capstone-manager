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
            if (!$this->middleware->requireAuth('/login', true)) {
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
            if (!$this->middleware->requireAuth('/login', true)) {
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
     * Student can withdraw their Pending or Approved registration.
     * This allows them to register for a different topic.
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

            // Get current user info
            $currentUser = $this->middleware->getCurrentUser();
            $studentId = $currentUser['student_id'] ?? null;
            $userId = $currentUser['id'] ?? null;

            if (!$studentId) {
                $this->jsonError('Student information not found', 403);
                return;
            }

            // Withdraw registration through repository (with transaction)
            $result = $this->topicRepository->withdrawRegistration($studentId, $userId);

            // Return success response
            $this->jsonSuccess(
                $result['data'],
                $result['message']
            );

        } catch (Exception $e) {
            // Handle business rule violations and errors
            $statusCode = (int) $e->getCode();

            // Map exception codes to HTTP status codes
            if ($statusCode === 404) {
                $this->jsonError($e->getMessage(), 404);
            } elseif ($statusCode === 403) {
                $this->jsonError($e->getMessage(), 403);
            } elseif ($statusCode === 400) {
                $this->jsonError($e->getMessage(), 400);
            } else {
                // Log unexpected errors
                error_log("Withdraw registration error: " . $e->getMessage());
                $this->jsonError(
                    'An error occurred while withdrawing your registration. Please try again.',
                    500,
                    ['error' => $e->getMessage()]
                );
            }
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

    /**
     * Create a new topic (POST /api/topics/create)
     * Lecturer only
     * 
     * @return void
     */
    public function create(): void
    {
        try {
            // AUTHORIZATION: Require Lecturer role
            if (!$this->middleware->requireLecturer(true)) {
                return;
            }

            // Validate request method
            if (!$this->isPost()) {
                $this->jsonError('Invalid request method. POST required.', 405);
                return;
            }

            // Get JSON input
            $input = $this->getJsonInput(true);

            if (!$input) {
                $this->jsonError('Invalid JSON input', 400);
                return;
            }

            // Validate required fields
            $required = ['title', 'description', 'department_id'];
            $missing = $this->validateRequired($input, $required);

            if (!empty($missing)) {
                $this->jsonError(
                    'Missing required fields: ' . implode(', ', $missing),
                    400,
                    ['missing_fields' => $missing]
                );
                return;
            }

            // Get current user info
            $currentUser = $this->middleware->getCurrentUser();
            $lecturerId = $currentUser['lecturer_id'] ?? null;
            $userId = $currentUser['id'] ?? null;

            if (!$lecturerId) {
                $this->jsonError('Lecturer information not found', 403);
                return;
            }

            // Sanitize input
            $data = [
                'title'         => $this->sanitize($input['title']),
                'description'   => $this->sanitize($input['description']),
                'department_id' => filter_var($input['department_id'], FILTER_VALIDATE_INT),
                'status'        => $input['status'] ?? 'Draft',
                'max_students'  => isset($input['max_students']) ? filter_var($input['max_students'], FILTER_VALIDATE_INT) : 5,
                'tags'          => $input['tags'] ?? []
            ];

            // Validate integers
            if ($data['department_id'] === false || $data['max_students'] === false) {
                $this->jsonError('Invalid numeric values', 400);
                return;
            }

            // Validate status
            $validStatuses = ['Draft', 'Published'];
            if (!in_array($data['status'], $validStatuses)) {
                $this->jsonError('Invalid status. Must be Draft or Published', 400);
                return;
            }

            // Create topic through repository
            $result = $this->topicRepository->createTopic($data, $lecturerId, $userId);

            // Return success response
            $this->jsonSuccess(
                $result['data'],
                $result['message']
            );

        } catch (Exception $e) {
            $statusCode = (int) $e->getCode();

            if ($statusCode === 404) {
                $this->jsonError($e->getMessage(), 404);
            } elseif ($statusCode === 400) {
                $this->jsonError($e->getMessage(), 400);
            } else {
                error_log("Topic creation error: " . $e->getMessage());
                $this->jsonError(
                    'An error occurred while creating the topic. Please try again.',
                    500,
                    ['error' => $e->getMessage()]
                );
            }
        }
    }

    /**
     * Update an existing topic (PUT /api/topics/{id})
     * Lecturer only - can only update own topics
     * 
     * @param int $id Topic ID
     * @return void
     */
    public function update(int $id): void
    {
        try {
            // AUTHORIZATION: Require Lecturer role
            if (!$this->middleware->requireLecturer(true)) {
                return;
            }

            // Validate request method
            if (!$this->isPut()) {
                $this->jsonError('Invalid request method. PUT required.', 405);
                return;
            }

            // Validate ID
            if ($id <= 0) {
                $this->jsonError('Invalid topic ID', 400);
                return;
            }

            // Get JSON input
            $input = $this->getJsonInput(true);

            if (!$input) {
                $this->jsonError('Invalid JSON input', 400);
                return;
            }

            // Get current user info
            $currentUser = $this->middleware->getCurrentUser();
            $lecturerId = $currentUser['lecturer_id'] ?? null;
            $userId = $currentUser['id'] ?? null;

            if (!$lecturerId) {
                $this->jsonError('Lecturer information not found', 403);
                return;
            }

            // Sanitize input
            $data = [];
            
            if (isset($input['title'])) {
                $data['title'] = $this->sanitize($input['title']);
            }
            
            if (isset($input['description'])) {
                $data['description'] = $this->sanitize($input['description']);
            }
            
            if (isset($input['status'])) {
                $validStatuses = ['Draft', 'Published'];
                if (!in_array($input['status'], $validStatuses)) {
                    $this->jsonError('Invalid status. Must be Draft or Published', 400);
                    return;
                }
                $data['status'] = $input['status'];
            }
            
            if (isset($input['max_students'])) {
                $maxStudents = filter_var($input['max_students'], FILTER_VALIDATE_INT);
                if ($maxStudents === false) {
                    $this->jsonError('Invalid max_students value', 400);
                    return;
                }
                $data['max_students'] = $maxStudents;
            }
            
            if (isset($input['tags'])) {
                $data['tags'] = $input['tags'];
            }

            // Update topic through repository
            $result = $this->topicRepository->updateTopic($id, $data, $lecturerId, $userId);

            // Return success response
            $this->jsonSuccess(
                $result['data'],
                $result['message']
            );

        } catch (Exception $e) {
            $statusCode = (int) $e->getCode();

            if ($statusCode === 404) {
                $this->jsonError($e->getMessage(), 404);
            } elseif ($statusCode === 403) {
                $this->jsonError($e->getMessage(), 403);
            } elseif ($statusCode === 400) {
                $this->jsonError($e->getMessage(), 400);
            } else {
                error_log("Topic update error: " . $e->getMessage());
                $this->jsonError(
                    'An error occurred while updating the topic. Please try again.',
                    500,
                    ['error' => $e->getMessage()]
                );
            }
        }
    }

    /**
     * Get lecturer's topics (GET /api/topics/my-topics)
     * Lecturer only
     * 
     * @return void
     */
    public function getMyTopics(): void
    {
        try {
            // AUTHORIZATION: Require Lecturer role
            if (!$this->middleware->requireLecturer(true)) {
                return;
            }

            // Get current user info
            $currentUser = $this->middleware->getCurrentUser();
            $lecturerId = $currentUser['lecturer_id'] ?? null;

            if (!$lecturerId) {
                $this->jsonError('Lecturer information not found', 403);
                return;
            }

            // Fetch lecturer's topics
            $topics = $this->topicRepository->getLecturerTopics($lecturerId);

            $this->jsonSuccess(
                $topics,
                'Topics retrieved successfully'
            );

        } catch (Exception $e) {
            error_log("Get lecturer topics error: " . $e->getMessage());
            $this->jsonError('Failed to retrieve topics', 500);
        }
    }

    /**
     * Get registrations for lecturer's topics (GET /api/topics/my-registrations)
     * Lecturer only
     * 
     * @return void
     */
    public function getMyRegistrations(): void
    {
        try {
            // AUTHORIZATION: Require Lecturer role
            if (!$this->middleware->requireLecturer(true)) {
                return;
            }

            // Get current user info
            $currentUser = $this->middleware->getCurrentUser();
            $lecturerId = $currentUser['lecturer_id'] ?? null;

            if (!$lecturerId) {
                $this->jsonError('Lecturer information not found', 403);
                return;
            }

            // Get optional status filter
            $status = $this->getQuery('status');
            $validStatuses = ['Pending', 'Approved', 'Rejected'];
            
            if ($status && !in_array($status, $validStatuses)) {
                $this->jsonError('Invalid status filter', 400);
                return;
            }

            // Fetch registrations
            $registrations = $this->topicRepository->getLecturerRegistrations($lecturerId, $status);

            $this->jsonSuccess(
                $registrations,
                'Registrations retrieved successfully'
            );

        } catch (Exception $e) {
            error_log("Get lecturer registrations error: " . $e->getMessage());
            $this->jsonError('Failed to retrieve registrations', 500);
        }
    }

    /**
     * Approve a student registration (POST /api/topics/registrations/{id}/approve)
     * Lecturer only
     * 
     * @param int $id Registration ID
     * @return void
     */
    public function approveRegistration(int $id): void
    {
        try {
            // AUTHORIZATION: Require Lecturer role
            if (!$this->middleware->requireLecturer(true)) {
                return;
            }

            // Validate request method
            if (!$this->isPost()) {
                $this->jsonError('Invalid request method. POST required.', 405);
                return;
            }

            // Validate ID
            if ($id <= 0) {
                $this->jsonError('Invalid registration ID', 400);
                return;
            }

            // Get current user info
            $currentUser = $this->middleware->getCurrentUser();
            $lecturerId = $currentUser['lecturer_id'] ?? null;
            $userId = $currentUser['id'] ?? null;

            if (!$lecturerId) {
                $this->jsonError('Lecturer information not found', 403);
                return;
            }

            // Approve registration through repository
            $result = $this->topicRepository->approveRegistration($id, $lecturerId, $userId);

            // Return success response
            $this->jsonSuccess(
                $result['data'],
                $result['message']
            );

        } catch (Exception $e) {
            $statusCode = (int) $e->getCode();

            if ($statusCode === 404) {
                $this->jsonError($e->getMessage(), 404);
            } elseif ($statusCode === 403) {
                $this->jsonError($e->getMessage(), 403);
            } elseif ($statusCode === 400) {
                $this->jsonError($e->getMessage(), 400);
            } else {
                error_log("Approve registration error: " . $e->getMessage());
                $this->jsonError(
                    'An error occurred while approving the registration. Please try again.',
                    500,
                    ['error' => $e->getMessage()]
                );
            }
        }
    }

    /**
     * Reject a student registration (POST /api/topics/registrations/{id}/reject)
     * Lecturer only
     * 
     * @param int $id Registration ID
     * @return void
     */
    public function rejectRegistration(int $id): void
    {
        try {
            // AUTHORIZATION: Require Lecturer role
            if (!$this->middleware->requireLecturer(true)) {
                return;
            }

            // Validate request method
            if (!$this->isPost()) {
                $this->jsonError('Invalid request method. POST required.', 405);
                return;
            }

            // Validate ID
            if ($id <= 0) {
                $this->jsonError('Invalid registration ID', 400);
                return;
            }

            // Get JSON input
            $input = $this->getJsonInput(true);

            if (!$input || empty($input['reason'])) {
                $this->jsonError('Rejection reason is required', 400);
                return;
            }

            $reason = $this->sanitize($input['reason']);

            // Get current user info
            $currentUser = $this->middleware->getCurrentUser();
            $lecturerId = $currentUser['lecturer_id'] ?? null;
            $userId = $currentUser['id'] ?? null;

            if (!$lecturerId) {
                $this->jsonError('Lecturer information not found', 403);
                return;
            }

            // Reject registration through repository
            $result = $this->topicRepository->rejectRegistration($id, $lecturerId, $reason, $userId);

            // Return success response
            $this->jsonSuccess(
                $result['data'],
                $result['message']
            );

        } catch (Exception $e) {
            $statusCode = (int) $e->getCode();

            if ($statusCode === 404) {
                $this->jsonError($e->getMessage(), 404);
            } elseif ($statusCode === 403) {
                $this->jsonError($e->getMessage(), 403);
            } elseif ($statusCode === 400) {
                $this->jsonError($e->getMessage(), 400);
            } else {
                error_log("Reject registration error: " . $e->getMessage());
                $this->jsonError(
                    'An error occurred while rejecting the registration. Please try again.',
                    500,
                    ['error' => $e->getMessage()]
                );
            }
        }
    }

    /**
     * Delete a topic (DELETE /api/topics/{id})
     * Lecturer only - can only delete own topics
     * 
     * @param int $id Topic ID
     * @return void
     */
    public function delete(int $id): void
    {
        try {
            // AUTHORIZATION: Require Lecturer role
            if (!$this->middleware->requireLecturer(true)) {
                return;
            }

            // Validate request method
            if (!$this->isDelete()) {
                $this->jsonError('Invalid request method. DELETE required.', 405);
                return;
            }

            // Validate ID
            if ($id <= 0) {
                $this->jsonError('Invalid topic ID', 400);
                return;
            }

            // Get current user info
            $currentUser = $this->middleware->getCurrentUser();
            $lecturerId = $currentUser['lecturer_id'] ?? null;
            $userId = $currentUser['id'] ?? null;

            if (!$lecturerId) {
                $this->jsonError('Lecturer information not found', 403);
                return;
            }

            // Delete topic through repository
            $result = $this->topicRepository->deleteTopic($id, $lecturerId, $userId);

            // Return success response
            $this->jsonSuccess(
                $result['data'],
                $result['message']
            );

        } catch (Exception $e) {
            $statusCode = (int) $e->getCode();

            if ($statusCode === 404) {
                $this->jsonError($e->getMessage(), 404);
            } elseif ($statusCode === 403) {
                $this->jsonError($e->getMessage(), 403);
            } elseif ($statusCode === 400) {
                $this->jsonError($e->getMessage(), 400);
            } else {
                error_log("Topic deletion error: " . $e->getMessage());
                $this->jsonError(
                    'An error occurred while deleting the topic. Please try again.',
                    500,
                    ['error' => $e->getMessage()]
                );
            }
        }
    }
}
