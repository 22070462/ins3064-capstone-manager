<?php

/**
 * Application Routes Configuration
 * 
 * Define all application routes here.
 * Routes are processed in order of registration.
 * 
 * @package Config
 * @author  Capstone Project Team
 * @version 1.0.0
 */

// Note: Router instance ($router) is passed from index.php
// Do not initialize a new router here

/**
 * ============================================
 * API ROUTES - Topic Management
 * ============================================
 */

// Register for a topic (Student only)
$router->post('/api/topics/register', 'App\Controllers\TopicController', 'register');

// Get all available topics
$router->get('/api/topics', 'App\Controllers\TopicController', 'index');

// Get student's registration history
$router->get('/api/topics/registrations/{studentId}', 'App\Controllers\TopicController', 'getStudentRegistrations');

// Check student's registration eligibility
$router->get('/api/topics/eligibility/{studentId}', 'App\Controllers\TopicController', 'checkEligibility');

// Withdraw topic registration
$router->post('/api/topics/withdraw', 'App\Controllers\TopicController', 'withdraw');

// Get student's approved project (My Project feature)
$router->get('/api/topics/my-project/{studentId}', 'App\Controllers\TopicController', 'getMyProject');

// Update project progress
$router->post('/api/topics/my-project/progress', 'App\Controllers\TopicController', 'updateProjectProgress');

// Get registration statistics (Admin/Lecturer only)
$router->get('/api/topics/stats', 'App\Controllers\TopicController', 'getStatistics');

/**
 * ============================================
 * API ROUTES - Lecturer Topic Management
 * ============================================
 */

// Create new topic (Lecturer only)
$router->post('/api/topics/create', 'App\Controllers\TopicController', 'create');

// Get lecturer's topics (Lecturer only) - MUST BE BEFORE /api/topics/{id}
$router->get('/api/topics/my-topics', 'App\Controllers\TopicController', 'getMyTopics');

// Get registrations for lecturer's topics (Lecturer only)
$router->get('/api/topics/my-registrations', 'App\Controllers\TopicController', 'getMyRegistrations');

// Approve student registration (Lecturer only)
$router->post('/api/topics/registrations/{id}/approve', 'App\Controllers\TopicController', 'approveRegistration');

// Reject student registration (Lecturer only)
$router->post('/api/topics/registrations/{id}/reject', 'App\Controllers\TopicController', 'rejectRegistration');

// Get topic details by ID - MUST BE AFTER specific routes
$router->get('/api/topics/{id}', 'App\Controllers\TopicController', 'show');

// Update topic (Lecturer only)
$router->put('/api/topics/{id}', 'App\Controllers\TopicController', 'update');

// Delete topic (Lecturer only)
$router->delete('/api/topics/{id}', 'App\Controllers\TopicController', 'delete');

/**
 * ============================================
 * API ROUTES - Submission Management
 * ============================================
 */

// Get milestones with submission status (Student only)
$router->get('/api/submissions/milestones', 'App\Controllers\TopicController', 'getMilestones');

// Get student's submissions (Student only)
$router->get('/api/submissions/my-submissions', 'App\Controllers\TopicController', 'getMySubmissions');

// Submit work for a milestone (Student only)
$router->post('/api/submissions/submit', 'App\Controllers\TopicController', 'submitWork');

// Get submission details (Student only)
$router->get('/api/submissions/{id}', 'App\Controllers\TopicController', 'getSubmissionDetails');

// Delete submission (Student only - before grading)
$router->delete('/api/submissions/{id}', 'App\Controllers\TopicController', 'deleteSubmission');

/**
 * ============================================
 * API ROUTES - Admin Functions
 * ============================================
 */

// User Management (Admin only)
$router->get('/api/admin/users', 'App\Controllers\AdminController', 'getUsers');
$router->get('/api/admin/users/{id}', 'App\Controllers\AdminController', 'getUserDetails');
$router->put('/api/admin/users/{id}/status', 'App\Controllers\AdminController', 'updateUserStatus');
$router->put('/api/admin/users/{id}/reset-password', 'App\Controllers\AdminController', 'resetUserPassword');
$router->delete('/api/admin/users/{id}', 'App\Controllers\AdminController', 'deleteUser');

// Student Management (Admin only)
$router->get('/api/admin/students', 'App\Controllers\AdminController', 'getStudents');
$router->get('/api/admin/students/{id}', 'App\Controllers\AdminController', 'getStudentDetails');

// Lecturer Management (Admin only)
$router->get('/api/admin/lecturers', 'App\Controllers\AdminController', 'getLecturers');
$router->get('/api/admin/lecturers/{id}', 'App\Controllers\AdminController', 'getLecturerDetails');

// Topic Management (Admin only)
$router->get('/api/admin/topics', 'App\Controllers\AdminController', 'getTopics');
$router->get('/api/admin/topics/{id}', 'App\Controllers\AdminController', 'getTopicDetails');
$router->put('/api/admin/topics/{id}/status', 'App\Controllers\AdminController', 'updateTopicStatus');
$router->delete('/api/admin/topics/{id}', 'App\Controllers\AdminController', 'deleteTopic');

// Registration Management (Admin only)
$router->get('/api/admin/registrations', 'App\Controllers\AdminController', 'getRegistrations');
$router->get('/api/admin/registrations/{id}', 'App\Controllers\AdminController', 'getRegistrationDetails');
$router->put('/api/admin/registrations/{id}/status', 'App\Controllers\AdminController', 'updateRegistrationStatus');
$router->delete('/api/admin/registrations/{id}', 'App\Controllers\AdminController', 'deleteRegistration');

// Reports & Analytics (Admin only)
$router->get('/api/admin/reports', 'App\Controllers\AdminController', 'getReports');

// System Settings (Admin only)
$router->get('/api/admin/settings', 'App\Controllers\AdminController', 'getSettings');
$router->post('/api/admin/settings', 'App\Controllers\AdminController', 'createSetting');
$router->put('/api/admin/settings/{id}', 'App\Controllers\AdminController', 'updateSetting');
$router->delete('/api/admin/settings/{id}', 'App\Controllers\AdminController', 'deleteSetting');

// Export assignments to CSV (Admin only)
$router->get('/api/admin/export/assignments', 'App\Controllers\AdminController', 'exportAssignments');

// Export students to CSV (Admin only)
$router->get('/api/admin/export/students', 'App\Controllers\AdminController', 'exportStudents');

// Export topics to CSV (Admin only)
$router->get('/api/admin/export/topics', 'App\Controllers\AdminController', 'exportTopics');

// Get dashboard statistics (Admin only)
$router->get('/api/admin/dashboard/stats', 'App\Controllers\AdminController', 'getDashboardStats');

/**
 * ============================================
 * WEB ROUTES - Dashboards
 * ============================================
 */

// Student dashboard
$router->get('/student/dashboard', function() {
    // Check authentication
    // FIXED: Check if session is already started to avoid duplicate session_start()
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Student') {
        header('Location: /capstone_project/login');
        exit;
    }
    require __DIR__ . '/../app/views/student/dashboard.php';
});

// Lecturer dashboard
$router->get('/lecturer/dashboard', function() {
    // Check authentication
    // FIXED: Check if session is already started to avoid duplicate session_start()
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Lecturer') {
        header('Location: /capstone_project/login');
        exit;
    }
    require __DIR__ . '/../app/views/lecturer/dashboard.php';
});

// Admin dashboard (placeholder)
$router->get('/admin/dashboard', function() {
    // Check authentication
    // FIXED: Check if session is already started to avoid duplicate session_start()
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
        header('Location: /capstone_project/login');
        exit;
    }
    require __DIR__ . '/../app/views/admin/dashboard.php';
});

/**
 * ============================================
 * WEB ROUTES - Authentication
 * ============================================
 */

// Show login form (FIXED - Now accessible)
$router->get('/login', 'App\Controllers\AuthController', 'showLoginForm');

/**
 * ============================================
 * API ROUTES - Authentication
 * ============================================
 */

// Login
$router->post('/api/auth/login', 'App\Controllers\AuthController', 'login');

// Logout
$router->post('/api/auth/logout', 'App\Controllers\AuthController', 'logout');

// Get current user
$router->get('/api/auth/me', 'App\Controllers\AuthController', 'me');

// Change password
$router->post('/api/auth/change-password', 'App\Controllers\StudentController', 'changePassword');

/**
 * ============================================
 * API ROUTES - Student Management
 * ============================================
 */

// Get student profile by ID
$router->get('/api/students/{id}', 'App\Controllers\StudentController', 'show');

// Update student profile
$router->put('/api/students/{id}', 'App\Controllers\StudentController', 'update');

/**
 * ============================================
 * API ROUTES - Lecturer Management
 * ============================================
 */

// Get lecturer's students (approved registrations)
$router->get('/api/lecturers/my-students', 'App\Controllers\LecturerController', 'getMyStudents');

// Get student progress details
$router->get('/api/lecturers/student-progress/{registrationId}', 'App\Controllers\LecturerController', 'getStudentProgress');

// Get lecturer statistics
$router->get('/api/lecturers/statistics', 'App\Controllers\LecturerController', 'getStatistics');

// Get submissions to evaluate
$router->get('/api/lecturers/submissions-to-evaluate', 'App\Controllers\LecturerController', 'getSubmissionsToEvaluate');

// Get evaluation rubrics for a milestone
$router->get('/api/lecturers/evaluation-rubrics/{milestoneId}', 'App\Controllers\LecturerController', 'getEvaluationRubrics');

// Get evaluation scores for a submission
$router->get('/api/lecturers/evaluation-scores/{submissionId}', 'App\Controllers\LecturerController', 'getEvaluationScores');

// Submit evaluation scores
$router->post('/api/lecturers/evaluate-submission', 'App\Controllers\LecturerController', 'evaluateSubmission');

// Get student evaluation summary
$router->get('/api/lecturers/student-evaluation-summary/{registrationId}', 'App\Controllers\LecturerController', 'getStudentEvaluationSummary');

// Get lecturer profile
$router->get('/api/lecturers/profile', 'App\Controllers\LecturerController', 'getProfile');

// Update lecturer profile
$router->put('/api/lecturers/profile', 'App\Controllers\LecturerController', 'updateProfile');

// Change password
$router->put('/api/lecturers/change-password', 'App\Controllers\LecturerController', 'changePassword');

/**
 * ============================================
 * API ROUTES - User Management (To be implemented)
 * ============================================
 */

// $router->get('/api/users', 'App\Controllers\UserController', 'index');
// $router->get('/api/users/{id}', 'App\Controllers\UserController', 'show');
// $router->post('/api/users', 'App\Controllers\UserController', 'create');
// $router->put('/api/users/{id}', 'App\Controllers\UserController', 'update');
// $router->delete('/api/users/{id}', 'App\Controllers\UserController', 'delete');

/**
 * ============================================
 * WEB ROUTES - Views (To be implemented)
 * ============================================
 */

// $router->get('/', 'App\Controllers\HomeController', 'index');
// $router->get('/login', 'App\Controllers\AuthController', 'showLoginForm');
// $router->get('/dashboard', 'App\Controllers\DashboardController', 'index');

// Note: Router dispatch is handled in index.php after all routes are loaded
