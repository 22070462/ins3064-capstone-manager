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
 * API ROUTES - Admin Functions
 * ============================================
 */

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
    session_start();
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Student') {
        header('Location: /capstone_project/login');
        exit;
    }
    require __DIR__ . '/../app/views/student/dashboard.php';
});

// Lecturer dashboard
$router->get('/lecturer/dashboard', function() {
    // Check authentication
    session_start();
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Lecturer') {
        header('Location: /capstone_project/login');
        exit;
    }
    require __DIR__ . '/../app/views/lecturer/dashboard.php';
});

// Admin dashboard (placeholder)
$router->get('/admin/dashboard', function() {
    // Check authentication
    session_start();
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
