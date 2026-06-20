<?php

/**
 * Lecturer Controller
 * 
 * Handles lecturer-specific features including:
 * - View my students
 * - View student progress
 * - Manage evaluations
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

class LecturerController extends Controller
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
     * Get all students with approved registrations for the lecturer
     * 
     * Route: GET /api/lecturers/my-students
     * 
     * @return void
     */
    public function getMyStudents(): void
    {
        try {
            // Check authentication
            if (!$this->middleware->isAuthenticated()) {
                error_log("My Students: Not authenticated");
                $this->jsonError('Unauthorized', 401);
                return;
            }

            // Get current user
            $currentUser = $this->middleware->getCurrentUser();
            error_log("My Students: Current user - " . print_r($currentUser, true));
            
            // Check if user is lecturer
            if ($currentUser['role'] !== 'Lecturer') {
                error_log("My Students: Not a lecturer - Role: " . $currentUser['role']);
                $this->jsonError('Forbidden: Only lecturers can access this resource', 403);
                return;
            }

            $lecturerId = $currentUser['lecturer_id'];
            error_log("My Students: Lecturer ID - " . $lecturerId);

            // Get all students with approved registrations for this lecturer
            $sql = "SELECT 
                        tr.id as registration_id,
                        tr.registered_at,
                        tr.status as registration_status,
                        s.id as student_id,
                        s.student_code,
                        s.full_name as student_name,
                        s.email as student_email,
                        s.phone as student_phone,
                        t.id as topic_id,
                        t.title as topic_title,
                        d.name as department_name,
                        0 as progress,
                        'Active' as project_status
                    FROM topic_registrations tr
                    INNER JOIN students s ON tr.student_id = s.id
                    INNER JOIN topics t ON tr.topic_id = t.id
                    INNER JOIN departments d ON s.department_id = d.id
                    WHERE tr.lecturer_id = :lecturer_id
                      AND tr.status = 'Approved'
                    ORDER BY tr.registered_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['lecturer_id' => $lecturerId]);
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

            error_log("My Students: Found " . count($students) . " students");

            $this->jsonSuccess($students, 'Students retrieved successfully');

        } catch (Exception $e) {
            error_log("Get my students error: " . $e->getMessage());
            $this->jsonError('Failed to retrieve students', 500);
        }
    }

    /**
     * Get student project progress details
     * 
     * Route: GET /api/lecturers/student-progress/{registrationId}
     * 
     * @param int $registrationId Registration ID
     * @return void
     */
    public function getStudentProgress(int $registrationId): void
    {
        try {
            // Check authentication
            if (!$this->middleware->isAuthenticated()) {
                $this->jsonError('Unauthorized', 401);
                return;
            }

            // Get current user
            $currentUser = $this->middleware->getCurrentUser();
            
            // Check if user is lecturer
            if ($currentUser['role'] !== 'Lecturer') {
                $this->jsonError('Forbidden: Only lecturers can access this resource', 403);
                return;
            }

            $lecturerId = $currentUser['lecturer_id'];

            // Verify this registration belongs to the lecturer
            $checkSql = "SELECT tr.id 
                        FROM topic_registrations tr
                        WHERE tr.id = :registration_id 
                          AND tr.lecturer_id = :lecturer_id
                        LIMIT 1";
            
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([
                'registration_id' => $registrationId,
                'lecturer_id' => $lecturerId
            ]);

            if (!$checkStmt->fetch()) {
                $this->jsonError('Registration not found or unauthorized', 404);
                return;
            }

            // Get registration and student details
            $sql = "SELECT 
                        tr.id as registration_id,
                        s.id as student_id,
                        s.full_name as student_name,
                        s.student_code,
                        t.title as topic_title,
                        t.description as topic_description,
                        0 as overall_progress,
                        DATE(tr.registered_at) as start_date,
                        NULL as completion_date,
                        '' as notes
                    FROM topic_registrations tr
                    INNER JOIN students s ON tr.student_id = s.id
                    INNER JOIN topics t ON tr.topic_id = t.id
                    WHERE tr.id = :registration_id
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['registration_id' => $registrationId]);
            $progressData = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$progressData) {
                $this->jsonError('Progress data not found', 404);
                return;
            }

            // Get milestones (if milestones table exists)
            $milestones = [];
            try {
                $milestonesSql = "SELECT 
                                    pm.id,
                                    pm.title,
                                    pm.description,
                                    pm.deadline,
                                    pm.status,
                                    pm.progress
                                FROM project_milestones pm
                                WHERE pm.registration_id = :registration_id
                                ORDER BY pm.deadline ASC";
                
                $milestonesStmt = $this->db->prepare($milestonesSql);
                $milestonesStmt->execute(['registration_id' => $registrationId]);
                $milestones = $milestonesStmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                // Milestones table might not exist yet
                error_log("Milestones query error: " . $e->getMessage());
            }

            // Get recent submissions (if submissions table exists)
            $submissions = [];
            try {
                $submissionsSql = "SELECT 
                                    ps.id,
                                    ps.milestone_id,
                                    pm.title as milestone_title,
                                    ps.submitted_at,
                                    ps.status,
                                    ps.grade,
                                    ps.feedback
                                FROM project_submissions ps
                                LEFT JOIN project_milestones pm ON ps.milestone_id = pm.id
                                WHERE ps.registration_id = :registration_id
                                ORDER BY ps.submitted_at DESC
                                LIMIT 10";
                
                $submissionsStmt = $this->db->prepare($submissionsSql);
                $submissionsStmt->execute(['registration_id' => $registrationId]);
                $submissions = $submissionsStmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                // Submissions table might not exist yet
                error_log("Submissions query error: " . $e->getMessage());
            }

            // Combine all data
            $progressData['milestones'] = $milestones;
            $progressData['submissions'] = $submissions;

            $this->jsonSuccess($progressData, 'Student progress retrieved successfully');

        } catch (Exception $e) {
            error_log("Get student progress error: " . $e->getMessage());
            $this->jsonError('Failed to retrieve student progress', 500);
        }
    }

    /**
     * Get lecturer statistics
     * 
     * Route: GET /api/lecturers/statistics
     * 
     * @return void
     */
    public function getStatistics(): void
    {
        try {
            // Check authentication
            if (!$this->middleware->isAuthenticated()) {
                $this->jsonError('Unauthorized', 401);
                return;
            }

            // Get current user
            $currentUser = $this->middleware->getCurrentUser();
            
            // Check if user is lecturer
            if ($currentUser['role'] !== 'Lecturer') {
                $this->jsonError('Forbidden: Only lecturers can access this resource', 403);
                return;
            }

            $lecturerId = $currentUser['lecturer_id'];

            // Get statistics
            $stats = [];

            // Total topics
            $topicsSql = "SELECT COUNT(*) as count FROM topics WHERE lecturer_id = :lecturer_id";
            $topicsStmt = $this->db->prepare($topicsSql);
            $topicsStmt->execute(['lecturer_id' => $lecturerId]);
            $stats['total_topics'] = $topicsStmt->fetch(PDO::FETCH_ASSOC)['count'];

            // Published topics
            $publishedSql = "SELECT COUNT(*) as count FROM topics WHERE lecturer_id = :lecturer_id AND status = 'Published'";
            $publishedStmt = $this->db->prepare($publishedSql);
            $publishedStmt->execute(['lecturer_id' => $lecturerId]);
            $stats['published_topics'] = $publishedStmt->fetch(PDO::FETCH_ASSOC)['count'];

            // Total students
            $studentsSql = "SELECT COUNT(*) as count FROM topic_registrations WHERE lecturer_id = :lecturer_id AND status = 'Approved'";
            $studentsStmt = $this->db->prepare($studentsSql);
            $studentsStmt->execute(['lecturer_id' => $lecturerId]);
            $stats['total_students'] = $studentsStmt->fetch(PDO::FETCH_ASSOC)['count'];

            // Pending registrations
            $pendingSql = "SELECT COUNT(*) as count FROM topic_registrations WHERE lecturer_id = :lecturer_id AND status = 'Pending'";
            $pendingStmt = $this->db->prepare($pendingSql);
            $pendingStmt->execute(['lecturer_id' => $lecturerId]);
            $stats['pending_registrations'] = $pendingStmt->fetch(PDO::FETCH_ASSOC)['count'];

            $this->jsonSuccess($stats, 'Statistics retrieved successfully');

        } catch (Exception $e) {
            error_log("Get statistics error: " . $e->getMessage());
            $this->jsonError('Failed to retrieve statistics', 500);
        }
    }

    /**
     * Get all submissions that need evaluation
     * 
     * Route: GET /api/lecturers/submissions-to-evaluate
     * 
     * @return void
     */
    public function getSubmissionsToEvaluate(): void
    {
        try {
            // Check authentication
            if (!$this->middleware->isAuthenticated()) {
                $this->jsonError('Unauthorized', 401);
                return;
            }

            // Get current user
            $currentUser = $this->middleware->getCurrentUser();
            
            // Check if user is lecturer
            if ($currentUser['role'] !== 'Lecturer') {
                $this->jsonError('Forbidden: Only lecturers can access this resource', 403);
                return;
            }

            $lecturerId = $currentUser['lecturer_id'];

            // Get all submissions from students under this lecturer
            $sql = "SELECT 
                        s.id as submission_id,
                        s.file_url,
                        s.file_name,
                        s.submitted_at,
                        s.is_late,
                        s.comments as submission_comments,
                        m.id as milestone_id,
                        m.title as milestone_title,
                        m.deadline,
                        m.weight_percentage,
                        st.id as student_id,
                        st.student_code,
                        st.full_name as student_name,
                        t.id as topic_id,
                        t.title as topic_title,
                        ta.id as assignment_id,
                        tr.id as registration_id,
                        COALESCE(AVG(es.score), 0) as average_score,
                        COUNT(DISTINCT es.id) as evaluation_count,
                        (SELECT COUNT(*) FROM evaluation_rubrics WHERE milestone_id = m.id) as total_rubrics
                    FROM submissions s
                    INNER JOIN topic_assignments ta ON s.assignment_id = ta.id
                    INNER JOIN topic_registrations tr ON ta.registration_id = tr.id
                    INNER JOIN students st ON tr.student_id = st.id
                    INNER JOIN topics t ON tr.topic_id = t.id
                    INNER JOIN milestones m ON s.milestone_id = m.id
                    LEFT JOIN evaluation_scores es ON s.id = es.submission_id
                    WHERE ta.lecturer_id = :lecturer_id
                    GROUP BY s.id, m.id, st.id, t.id, ta.id, tr.id
                    ORDER BY s.submitted_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['lecturer_id' => $lecturerId]);
            $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Add evaluation status to each submission
            foreach ($submissions as &$submission) {
                $evaluationCount = (int)$submission['evaluation_count'];
                $totalRubrics = (int)$submission['total_rubrics'];
                
                if ($totalRubrics > 0 && $evaluationCount >= $totalRubrics) {
                    $submission['evaluation_status'] = 'Completed';
                } elseif ($evaluationCount > 0) {
                    $submission['evaluation_status'] = 'Partial';
                } else {
                    $submission['evaluation_status'] = 'Pending';
                }
            }

            $this->jsonSuccess($submissions, 'Submissions retrieved successfully');

        } catch (Exception $e) {
            error_log("Get submissions to evaluate error: " . $e->getMessage());
            $this->jsonError('Failed to retrieve submissions', 500);
        }
    }

    /**
     * Get evaluation rubrics for a milestone
     * 
     * Route: GET /api/lecturers/evaluation-rubrics/{milestoneId}
     * 
     * @param int $milestoneId Milestone ID
     * @return void
     */
    public function getEvaluationRubrics(int $milestoneId): void
    {
        try {
            // Check authentication
            if (!$this->middleware->isAuthenticated()) {
                $this->jsonError('Unauthorized', 401);
                return;
            }

            // Get current user
            $currentUser = $this->middleware->getCurrentUser();
            
            // Check if user is lecturer
            if ($currentUser['role'] !== 'Lecturer') {
                $this->jsonError('Forbidden: Only lecturers can access this resource', 403);
                return;
            }

            // Get rubrics for the milestone
            $sql = "SELECT 
                        id,
                        milestone_id,
                        criteria_name,
                        max_score,
                        description,
                        sequence_order
                    FROM evaluation_rubrics
                    WHERE milestone_id = :milestone_id
                    ORDER BY sequence_order ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['milestone_id' => $milestoneId]);
            $rubrics = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->jsonSuccess($rubrics, 'Rubrics retrieved successfully');

        } catch (Exception $e) {
            error_log("Get evaluation rubrics error: " . $e->getMessage());
            $this->jsonError('Failed to retrieve rubrics', 500);
        }
    }

    /**
     * Get existing evaluation scores for a submission
     * 
     * Route: GET /api/lecturers/evaluation-scores/{submissionId}
     * 
     * @param int $submissionId Submission ID
     * @return void
     */
    public function getEvaluationScores(int $submissionId): void
    {
        try {
            // Check authentication
            if (!$this->middleware->isAuthenticated()) {
                $this->jsonError('Unauthorized', 401);
                return;
            }

            // Get current user
            $currentUser = $this->middleware->getCurrentUser();
            
            // Check if user is lecturer
            if ($currentUser['role'] !== 'Lecturer') {
                $this->jsonError('Forbidden: Only lecturers can access this resource', 403);
                return;
            }

            $lecturerId = $currentUser['lecturer_id'];

            // Verify this submission belongs to lecturer's student
            $checkSql = "SELECT s.id 
                        FROM submissions s
                        INNER JOIN topic_assignments ta ON s.assignment_id = ta.id
                        WHERE s.id = :submission_id 
                          AND ta.lecturer_id = :lecturer_id
                        LIMIT 1";
            
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([
                'submission_id' => $submissionId,
                'lecturer_id' => $lecturerId
            ]);

            if (!$checkStmt->fetch()) {
                $this->jsonError('Submission not found or unauthorized', 404);
                return;
            }

            // Get evaluation scores
            $sql = "SELECT 
                        es.id,
                        es.submission_id,
                        es.rubric_id,
                        es.score,
                        es.comments,
                        es.graded_at,
                        er.criteria_name,
                        er.max_score
                    FROM evaluation_scores es
                    INNER JOIN evaluation_rubrics er ON es.rubric_id = er.id
                    WHERE es.submission_id = :submission_id
                      AND es.grader_id = :grader_id
                    ORDER BY er.sequence_order ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'submission_id' => $submissionId,
                'grader_id' => $lecturerId
            ]);
            $scores = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->jsonSuccess($scores, 'Evaluation scores retrieved successfully');

        } catch (Exception $e) {
            error_log("Get evaluation scores error: " . $e->getMessage());
            $this->jsonError('Failed to retrieve evaluation scores', 500);
        }
    }

    /**
     * Submit evaluation scores for a submission
     * 
     * Route: POST /api/lecturers/evaluate-submission
     * 
     * Expected JSON body:
     * {
     *   "submission_id": 1,
     *   "scores": [
     *     {
     *       "rubric_id": 1,
     *       "score": 8.5,
     *       "comments": "Good work"
     *     }
     *   ]
     * }
     * 
     * @return void
     */
    public function evaluateSubmission(): void
    {
        try {
            // Check authentication
            if (!$this->middleware->isAuthenticated()) {
                $this->jsonError('Unauthorized', 401);
                return;
            }

            // Get current user
            $currentUser = $this->middleware->getCurrentUser();
            
            // Check if user is lecturer
            if ($currentUser['role'] !== 'Lecturer') {
                $this->jsonError('Forbidden: Only lecturers can access this resource', 403);
                return;
            }

            $lecturerId = $currentUser['lecturer_id'];

            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['submission_id']) || !isset($input['scores']) || !is_array($input['scores'])) {
                $this->jsonError('Invalid input: submission_id and scores array required', 400);
                return;
            }

            $submissionId = (int)$input['submission_id'];
            $scores = $input['scores'];

            // Verify this submission belongs to lecturer's student
            $checkSql = "SELECT s.id 
                        FROM submissions s
                        INNER JOIN topic_assignments ta ON s.assignment_id = ta.id
                        WHERE s.id = :submission_id 
                          AND ta.lecturer_id = :lecturer_id
                        LIMIT 1";
            
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([
                'submission_id' => $submissionId,
                'lecturer_id' => $lecturerId
            ]);

            if (!$checkStmt->fetch()) {
                $this->jsonError('Submission not found or unauthorized', 404);
                return;
            }

            // Start transaction
            $this->db->beginTransaction();

            try {
                // Insert or update evaluation scores
                foreach ($scores as $scoreData) {
                    if (!isset($scoreData['rubric_id']) || !isset($scoreData['score'])) {
                        throw new Exception('Each score must have rubric_id and score');
                    }

                    $rubricId = (int)$scoreData['rubric_id'];
                    $score = (float)$scoreData['score'];
                    $comments = $scoreData['comments'] ?? null;

                    // Check if score already exists
                    $checkScoreSql = "SELECT id FROM evaluation_scores 
                                     WHERE submission_id = :submission_id 
                                       AND rubric_id = :rubric_id 
                                       AND grader_id = :grader_id";
                    
                    $checkScoreStmt = $this->db->prepare($checkScoreSql);
                    $checkScoreStmt->execute([
                        'submission_id' => $submissionId,
                        'rubric_id' => $rubricId,
                        'grader_id' => $lecturerId
                    ]);
                    
                    $existingScore = $checkScoreStmt->fetch(PDO::FETCH_ASSOC);

                    if ($existingScore) {
                        // Update existing score
                        $updateSql = "UPDATE evaluation_scores 
                                     SET score = :score, 
                                         comments = :comments, 
                                         updated_at = NOW() 
                                     WHERE id = :id";
                        
                        $updateStmt = $this->db->prepare($updateSql);
                        $updateStmt->execute([
                            'score' => $score,
                            'comments' => $comments,
                            'id' => $existingScore['id']
                        ]);
                    } else {
                        // Insert new score
                        $insertSql = "INSERT INTO evaluation_scores 
                                     (submission_id, rubric_id, grader_id, score, comments, graded_at) 
                                     VALUES 
                                     (:submission_id, :rubric_id, :grader_id, :score, :comments, NOW())";
                        
                        $insertStmt = $this->db->prepare($insertSql);
                        $insertStmt->execute([
                            'submission_id' => $submissionId,
                            'rubric_id' => $rubricId,
                            'grader_id' => $lecturerId,
                            'score' => $score,
                            'comments' => $comments
                        ]);
                    }
                }

                // Commit transaction
                $this->db->commit();

                // Log activity
                $this->logActivity(
                    $currentUser['user_id'],
                    'evaluate_submission',
                    'evaluation_scores',
                    $submissionId,
                    "Evaluated submission with " . count($scores) . " criteria"
                );

                $this->jsonSuccess(
                    ['submission_id' => $submissionId],
                    'Evaluation submitted successfully'
                );

            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }

        } catch (Exception $e) {
            error_log("Evaluate submission error: " . $e->getMessage());
            $this->jsonError('Failed to submit evaluation: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get evaluation summary for a student
     * 
     * Route: GET /api/lecturers/student-evaluation-summary/{registrationId}
     * 
     * @param int $registrationId Registration ID
     * @return void
     */
    public function getStudentEvaluationSummary(int $registrationId): void
    {
        try {
            // Check authentication
            if (!$this->middleware->isAuthenticated()) {
                $this->jsonError('Unauthorized', 401);
                return;
            }

            // Get current user
            $currentUser = $this->middleware->getCurrentUser();
            
            // Check if user is lecturer
            if ($currentUser['role'] !== 'Lecturer') {
                $this->jsonError('Forbidden: Only lecturers can access this resource', 403);
                return;
            }

            $lecturerId = $currentUser['lecturer_id'];

            // Verify this registration belongs to the lecturer
            $checkSql = "SELECT tr.id 
                        FROM topic_registrations tr
                        WHERE tr.id = :registration_id 
                          AND tr.lecturer_id = :lecturer_id
                        LIMIT 1";
            
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([
                'registration_id' => $registrationId,
                'lecturer_id' => $lecturerId
            ]);

            if (!$checkStmt->fetch()) {
                $this->jsonError('Registration not found or unauthorized', 404);
                return;
            }

            // Get student and topic info
            $infoSql = "SELECT 
                            s.id as student_id,
                            s.student_code,
                            s.full_name as student_name,
                            t.title as topic_title
                        FROM topic_registrations tr
                        INNER JOIN students s ON tr.student_id = s.id
                        INNER JOIN topics t ON tr.topic_id = t.id
                        WHERE tr.id = :registration_id
                        LIMIT 1";
            
            $infoStmt = $this->db->prepare($infoSql);
            $infoStmt->execute(['registration_id' => $registrationId]);
            $info = $infoStmt->fetch(PDO::FETCH_ASSOC);

            // Get evaluation summary by milestone
            $summarySql = "SELECT 
                                m.id as milestone_id,
                                m.title as milestone_title,
                                m.weight_percentage,
                                s.id as submission_id,
                                s.submitted_at,
                                COALESCE(AVG(es.score), 0) as average_score,
                                COALESCE(SUM(er.max_score), 0) as total_max_score,
                                COUNT(DISTINCT es.id) as evaluated_rubrics,
                                COUNT(DISTINCT er.id) as total_rubrics
                            FROM milestones m
                            LEFT JOIN submissions s ON m.id = s.milestone_id
                            LEFT JOIN topic_assignments ta ON s.assignment_id = ta.id 
                                AND ta.registration_id = :registration_id
                            LEFT JOIN evaluation_rubrics er ON m.id = er.milestone_id
                            LEFT JOIN evaluation_scores es ON s.id = es.submission_id 
                                AND es.rubric_id = er.id
                            WHERE m.is_active = TRUE
                            GROUP BY m.id, s.id
                            ORDER BY m.sequence_order ASC";
            
            $summaryStmt = $this->db->prepare($summarySql);
            $summaryStmt->execute(['registration_id' => $registrationId]);
            $milestones = $summaryStmt->fetchAll(PDO::FETCH_ASSOC);

            // Calculate overall score
            $totalWeightedScore = 0;
            $totalWeight = 0;

            foreach ($milestones as &$milestone) {
                $maxScore = (float)$milestone['total_max_score'];
                $avgScore = (float)$milestone['average_score'];
                $weight = (float)$milestone['weight_percentage'];
                
                if ($maxScore > 0) {
                    $percentage = ($avgScore / $maxScore) * 100;
                    $milestone['score_percentage'] = round($percentage, 2);
                    
                    $totalWeightedScore += ($percentage * $weight / 100);
                    $totalWeight += $weight;
                } else {
                    $milestone['score_percentage'] = 0;
                }

                $milestone['evaluation_status'] = 
                    $milestone['evaluated_rubrics'] >= $milestone['total_rubrics'] ? 'Completed' :
                    ($milestone['evaluated_rubrics'] > 0 ? 'Partial' : 'Pending');
            }

            $overallScore = $totalWeight > 0 ? round($totalWeightedScore, 2) : 0;

            $result = [
                'student_info' => $info,
                'milestones' => $milestones,
                'overall_score' => $overallScore
            ];

            $this->jsonSuccess($result, 'Evaluation summary retrieved successfully');

        } catch (Exception $e) {
            error_log("Get student evaluation summary error: " . $e->getMessage());
            $this->jsonError('Failed to retrieve evaluation summary', 500);
        }
    }

    /**
     * Get lecturer profile
     * 
     * Route: GET /api/lecturers/profile
     * 
     * @return void
     */
    public function getProfile(): void
    {
        try {
            // Check authentication
            if (!$this->middleware->isAuthenticated()) {
                error_log("Get profile: Not authenticated");
                $this->jsonError('Unauthorized', 401);
                return;
            }

            // Get current user
            $currentUser = $this->middleware->getCurrentUser();
            
            error_log("Get profile: Current user - " . print_r($currentUser, true));
            
            // Check if user is lecturer
            if (!isset($currentUser['role']) || $currentUser['role'] !== 'Lecturer') {
                error_log("Get profile: Not a lecturer - Role: " . ($currentUser['role'] ?? 'none'));
                $this->jsonError('Forbidden: Only lecturers can access this resource', 403);
                return;
            }

            // Check if lecturer_id exists
            if (!isset($currentUser['lecturer_id'])) {
                error_log("Get profile: lecturer_id not found in session");
                $this->jsonError('Lecturer information not found in session', 400);
                return;
            }

            $lecturerId = $currentUser['lecturer_id'];
            error_log("Get profile: Lecturer ID - " . $lecturerId);

            // Get lecturer profile with department info
            $sql = "SELECT 
                        l.id,
                        l.user_id,
                        l.lecturer_code,
                        l.full_name,
                        l.email,
                        l.phone,
                        l.specialization,
                        l.max_quota,
                        l.department_id,
                        d.name as department_name,
                        d.code as department_code,
                        u.username,
                        l.created_at,
                        l.updated_at
                    FROM lecturers l
                    INNER JOIN departments d ON l.department_id = d.id
                    INNER JOIN users u ON l.user_id = u.id
                    WHERE l.id = :lecturer_id
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['lecturer_id' => $lecturerId]);
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$profile) {
                error_log("Get profile: No profile found for lecturer_id: " . $lecturerId);
                $this->jsonError('Profile not found', 404);
                return;
            }

            error_log("Get profile: Profile found - " . $profile['full_name']);

            // Get additional statistics
            $statsSql = "SELECT 
                            (SELECT COUNT(*) FROM topics WHERE created_by = ?) as total_topics,
                            (SELECT COUNT(*) FROM topics WHERE created_by = ? AND status = 'Published') as published_topics,
                            (SELECT COUNT(*) FROM topic_registrations WHERE lecturer_id = ? AND status = 'Approved') as total_students,
                            (SELECT COUNT(*) FROM topic_registrations WHERE lecturer_id = ? AND status = 'Pending') as pending_registrations";

            $statsStmt = $this->db->prepare($statsSql);
            $statsStmt->execute([$lecturerId, $lecturerId, $lecturerId, $lecturerId]);
            $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

            $profile['statistics'] = $stats;

            error_log("Get profile: Success");
            $this->jsonSuccess($profile, 'Profile retrieved successfully');

        } catch (Exception $e) {
            error_log("Get profile error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $this->jsonError('Failed to retrieve profile: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update lecturer profile
     * 
     * Route: PUT /api/lecturers/profile
     * 
     * Expected JSON body:
     * {
     *   "full_name": "John Doe",
     *   "email": "john@example.com",
     *   "phone": "0123456789",
     *   "specialization": "Machine Learning, AI"
     * }
     * 
     * @return void
     */
    public function updateProfile(): void
    {
        try {
            // Check authentication
            if (!$this->middleware->isAuthenticated()) {
                $this->jsonError('Unauthorized', 401);
                return;
            }

            // Get current user
            $currentUser = $this->middleware->getCurrentUser();
            
            // Check if user is lecturer
            if ($currentUser['role'] !== 'Lecturer') {
                $this->jsonError('Forbidden: Only lecturers can access this resource', 403);
                return;
            }

            $lecturerId = $currentUser['lecturer_id'];

            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                $this->jsonError('Invalid input', 400);
                return;
            }

            // Prepare update fields
            $updateFields = [];
            $params = ['lecturer_id' => $lecturerId];

            if (isset($input['full_name']) && trim($input['full_name']) !== '') {
                $updateFields[] = 'full_name = :full_name';
                $params['full_name'] = trim($input['full_name']);
            }

            if (isset($input['email'])) {
                // Validate email format
                if ($input['email'] !== '' && !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                    $this->jsonError('Invalid email format', 400);
                    return;
                }
                $updateFields[] = 'email = :email';
                $params['email'] = trim($input['email']);
            }

            if (isset($input['phone'])) {
                $updateFields[] = 'phone = :phone';
                $params['phone'] = trim($input['phone']);
            }

            if (isset($input['specialization'])) {
                $updateFields[] = 'specialization = :specialization';
                $params['specialization'] = trim($input['specialization']);
            }

            if (empty($updateFields)) {
                $this->jsonError('No fields to update', 400);
                return;
            }

            // Update profile
            $sql = "UPDATE lecturers 
                    SET " . implode(', ', $updateFields) . ", updated_at = NOW()
                    WHERE id = :lecturer_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            // Log activity
            $this->logActivity(
                $currentUser['user_id'],
                'update_profile',
                'lecturers',
                $lecturerId,
                'Updated profile information'
            );

            // Return updated profile
            $this->getProfile();

        } catch (Exception $e) {
            error_log("Update profile error: " . $e->getMessage());
            $this->jsonError('Failed to update profile', 500);
        }
    }

    /**
     * Change password
     * 
     * Route: PUT /api/lecturers/change-password
     * 
     * Expected JSON body:
     * {
     *   "current_password": "oldpass123",
     *   "new_password": "newpass123",
     *   "confirm_password": "newpass123"
     * }
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

            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['current_password']) || !isset($input['new_password']) || !isset($input['confirm_password'])) {
                $this->jsonError('All password fields are required', 400);
                return;
            }

            $currentPassword = $input['current_password'];
            $newPassword = $input['new_password'];
            $confirmPassword = $input['confirm_password'];

            // Validate new password
            if (strlen($newPassword) < 6) {
                $this->jsonError('New password must be at least 6 characters', 400);
                return;
            }

            if ($newPassword !== $confirmPassword) {
                $this->jsonError('New passwords do not match', 400);
                return;
            }

            // Get current password hash from database
            $sql = "SELECT password_hash FROM users WHERE id = :user_id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $currentUser['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $this->jsonError('User not found', 404);
                return;
            }

            // Verify current password
            if (!password_verify($currentPassword, $user['password_hash'])) {
                $this->jsonError('Current password is incorrect', 400);
                return;
            }

            // Hash new password
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update password
            $updateSql = "UPDATE users SET password_hash = :password_hash, updated_at = NOW() WHERE id = :user_id";
            $updateStmt = $this->db->prepare($updateSql);
            $updateStmt->execute([
                'password_hash' => $newPasswordHash,
                'user_id' => $currentUser['user_id']
            ]);

            // Log activity
            $this->logActivity(
                $currentUser['user_id'],
                'change_password',
                'users',
                $currentUser['user_id'],
                'Changed password'
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
