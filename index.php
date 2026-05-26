<?php

/**
 * Root Index File - Development Testing
 * 
 * This file provides a simple test route to verify database connectivity.
 * For production, all requests should go through public/index.php
 * 
 * @package Root
 * @author  Capstone Project Team
 * @version 1.0.0
 */

// Start output buffering
ob_start();

// Define application constants
define('APP_ROOT', __DIR__);
define('APP_START_TIME', microtime(true));

// Load autoloader
require_once APP_ROOT . '/config/autoload.php';

// Import required classes
use App\Core\Router;
use Config\Database;

// Initialize router
$router = new Router();

// Load application routes from config
require_once APP_ROOT . '/config/routes.php';

// Additional development/test routes below

/**
 * Test Route: Database Connection Test
 * 
 * URL: http://localhost/capstone_project/test-db
 * Method: GET
 * 
 * Tests the Singleton Database connection and displays result
 */
$router->get('/test-db', function() {
    try {
        // Get Database Singleton instance
        $database = Database::getInstance();
        
        // Get PDO connection
        $connection = $database->getConnection();
        
        // Test the connection
        if ($database->testConnection()) {
            // Success - Display green message
            echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            text-align: center;
            max-width: 600px;
        }
        .success {
            color: #4caf50;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        .details {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
            text-align: left;
        }
        .details h3 {
            margin-top: 0;
            color: #333;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #666;
        }
        .value {
            color: #333;
        }
        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">✅</div>
        <div class="success">Database Connected Successfully!</div>
        <p>Your Singleton Database connection is working perfectly.</p>
        
        <div class="details">
            <h3>Connection Details</h3>
            <div class="detail-row">
                <span class="label">Status:</span>
                <span class="value" style="color: #4caf50;">Active</span>
            </div>
            <div class="detail-row">
                <span class="label">Driver:</span>
                <span class="value">MySQL (PDO)</span>
            </div>
            <div class="detail-row">
                <span class="label">Host:</span>
                <span class="value">localhost</span>
            </div>
            <div class="detail-row">
                <span class="label">Database:</span>
                <span class="value">capstone_db</span>
            </div>
            <div class="detail-row">
                <span class="label">Pattern:</span>
                <span class="value">Singleton</span>
            </div>
            <div class="detail-row">
                <span class="label">Connection Type:</span>
                <span class="value">Persistent: No</span>
            </div>
        </div>
        
        <a href="/capstone_project/test_connection.php" class="btn">Run Full System Test</a>
        <a href="/capstone_project/app/views/student/register.php" class="btn">Go to Registration</a>
    </div>
</body>
</html>';
        } else {
            throw new Exception('Connection test failed');
        }
        
    } catch (PDOException $e) {
        // PDO Error - Display red message
        echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Error</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #f44336 0%, #e91e63 100%);
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            text-align: center;
            max-width: 600px;
        }
        .error {
            color: #f44336;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        .error-details {
            background: #ffebee;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
            text-align: left;
            border-left: 4px solid #f44336;
        }
        .error-details h3 {
            margin-top: 0;
            color: #c62828;
        }
        .error-message {
            color: #c62828;
            font-family: monospace;
            background: white;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
            word-break: break-word;
        }
        .solutions {
            background: #e3f2fd;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
            text-align: left;
            border-left: 4px solid #2196f3;
        }
        .solutions h3 {
            margin-top: 0;
            color: #1565c0;
        }
        .solutions ol {
            margin: 10px 0;
            padding-left: 20px;
        }
        .solutions li {
            margin: 10px 0;
            color: #333;
        }
        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: #2196f3;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">❌</div>
        <div class="error">Database Connection Failed!</div>
        <p>Unable to connect to the database. Please check the error details below.</p>
        
        <div class="error-details">
            <h3>Error Details</h3>
            <div class="error-message">' . htmlspecialchars($e->getMessage()) . '</div>
        </div>
        
        <div class="solutions">
            <h3>Troubleshooting Steps</h3>
            <ol>
                <li><strong>Check MySQL is running:</strong> Open XAMPP Control Panel and ensure MySQL is started (green indicator)</li>
                <li><strong>Verify database exists:</strong> Open phpMyAdmin and check if "capstone_db" database exists</li>
                <li><strong>Check credentials:</strong> Open config/Database.php and verify username/password (default: root with empty password)</li>
                <li><strong>Import database:</strong> If database is empty, import database.sql file via phpMyAdmin</li>
                <li><strong>Check PHP PDO extension:</strong> Ensure PDO and PDO_MySQL extensions are enabled in php.ini</li>
            </ol>
        </div>
        
        <a href="/capstone_project/test_connection.php" class="btn">Run Full System Test</a>
    </div>
</body>
</html>';
        
    } catch (Exception $e) {
        // General Error
        echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #ff9800 0%, #ff5722 100%);
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            text-align: center;
            max-width: 600px;
        }
        .error {
            color: #ff5722;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        .error-message {
            background: #fff3e0;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
            color: #e65100;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">⚠️</div>
        <div class="error">An Error Occurred</div>
        <div class="error-message">' . htmlspecialchars($e->getMessage()) . '</div>
    </div>
</body>
</html>';
    }
    
    exit;
}, 'TestDatabaseConnection');

/**
 * Debug Route: Show routing information
 * 
 * URL: http://localhost/capstone_project/debug
 * Method: GET
 */
$router->get('/debug', function() {
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Router Debug Info</title>
    <style>
        body {
            font-family: monospace;
            padding: 20px;
            background: #f5f5f5;
        }
        .debug-box {
            background: white;
            padding: 20px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 4px solid #2196f3;
        }
        h2 {
            color: #2196f3;
            margin-top: 0;
        }
        pre {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 3px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>🔍 Router Debug Information</h1>
    
    <div class="debug-box">
        <h2>Request Information</h2>
        <pre>' . print_r([
            'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'N/A',
            'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? 'N/A',
            'PHP_SELF' => $_SERVER['PHP_SELF'] ?? 'N/A',
            'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
            'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A',
        ], true) . '</pre>
    </div>
    
    <div class="debug-box">
        <h2>Parsed URI</h2>
        <pre>' . print_r([
            'Raw URI' => $_SERVER['REQUEST_URI'] ?? '/',
            'Parsed Path' => parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH),
            'Base Path' => dirname($_SERVER['SCRIPT_NAME']),
        ], true) . '</pre>
    </div>
    
    <div class="debug-box">
        <h2>Router State</h2>
        <pre>Router is working correctly if you see this page!</pre>
    </div>
    
    <div class="debug-box">
        <h2>Test Links</h2>
        <ul>
            <li><a href="/capstone_project/">Home Page</a></li>
            <li><a href="/capstone_project/test-db">Database Test</a></li>
            <li><a href="/capstone_project/debug">This Debug Page</a></li>
        </ul>
    </div>
</body>
</html>';
    exit;
}, 'DebugRoute');

/**
 * Root Route: Welcome Page
 * 
 * URL: http://localhost/capstone_project/
 * Method: GET
 */
$router->get('/', function() {
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Capstone Project Management System</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: var(--primary-gradient);
            min-height: 100vh;
        }
        
        /* Hero Section */
        .hero {
            padding: 80px 20px;
            text-align: center;
            color: white;
        }
        
        .hero h1 {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 20px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        .hero p {
            font-size: 20px;
            margin-bottom: 40px;
            opacity: 0.95;
        }
        
        .hero .btn-hero {
            padding: 15px 40px;
            font-size: 18px;
            font-weight: 600;
            border-radius: 50px;
            margin: 10px;
            transition: all 0.3s;
        }
        
        .hero .btn-hero:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        /* Features Section */
        .features {
            background: white;
            padding: 80px 20px;
        }
        
        .features h2 {
            text-align: center;
            font-size: 36px;
            margin-bottom: 50px;
            color: #333;
        }
        
        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: all 0.3s;
            height: 100%;
            border: 2px solid transparent;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
            border-color: #667eea;
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            color: white;
        }
        
        .feature-card h3 {
            font-size: 24px;
            margin-bottom: 15px;
            color: #333;
        }
        
        .feature-card p {
            color: #666;
            line-height: 1.6;
        }
        
        /* Roles Section */
        .roles {
            background: #f8f9fa;
            padding: 80px 20px;
        }
        
        .roles h2 {
            text-align: center;
            font-size: 36px;
            margin-bottom: 50px;
            color: #333;
        }
        
        .role-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: all 0.3s;
            height: 100%;
        }
        
        .role-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
        }
        
        .role-icon {
            width: 100px;
            height: 100px;
            margin: 0 auto 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: white;
        }
        
        .role-card h3 {
            font-size: 28px;
            margin-bottom: 15px;
            color: #333;
        }
        
        .role-card ul {
            text-align: left;
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }
        
        .role-card ul li {
            padding: 8px 0;
            color: #666;
        }
        
        .role-card ul li i {
            color: #667eea;
            margin-right: 10px;
        }
        
        /* Stats Section */
        .stats {
            background: var(--primary-gradient);
            padding: 60px 20px;
            color: white;
        }
        
        .stat-item {
            text-align: center;
            padding: 20px;
        }
        
        .stat-number {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .stat-label {
            font-size: 18px;
            opacity: 0.9;
        }
        
        /* Footer */
        .footer {
            background: #2c3e50;
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        
        .footer a {
            color: #667eea;
            text-decoration: none;
        }
        
        .footer a:hover {
            text-decoration: underline;
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fade-in {
            animation: fadeInUp 0.6s ease-out;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="animate-fade-in">
                <h1><i class="bi bi-mortarboard-fill"></i> Capstone Project Management System</h1>
                <p>Streamline your capstone project workflow with our comprehensive management platform</p>
                <a href="/capstone_project/login" class="btn btn-light btn-hero">
                    <i class="bi bi-box-arrow-in-right"></i> Login to Dashboard
                </a>
                <a href="#features" class="btn btn-outline-light btn-hero">
                    <i class="bi bi-info-circle"></i> Learn More
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <h2><i class="bi bi-stars"></i> Key Features</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon" style="background: var(--primary-gradient);">
                            <i class="bi bi-journal-text"></i>
                        </div>
                        <h3>Topic Management</h3>
                        <p>Browse, register, and manage capstone project topics with ease. Real-time availability tracking and instant notifications.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon" style="background: var(--success-gradient);">
                            <i class="bi bi-people"></i>
                        </div>
                        <h3>Role-Based Access</h3>
                        <p>Separate dashboards for Admins, Lecturers, and Students with tailored features and permissions for each role.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon" style="background: var(--info-gradient);">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <h3>Analytics & Reports</h3>
                        <p>Comprehensive statistics, interactive charts, and CSV export capabilities for data-driven decision making.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h3>Secure Authentication</h3>
                        <p>Industry-standard security with password hashing, session management, and role-based authorization.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <h3>Activity Tracking</h3>
                        <p>Complete audit trail of all system activities with timestamps, user actions, and detailed logs.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
                            <i class="bi bi-phone"></i>
                        </div>
                        <h3>Responsive Design</h3>
                        <p>Fully responsive interface that works seamlessly on desktop, tablet, and mobile devices.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Roles Section -->
    <section class="roles">
        <div class="container">
            <h2><i class="bi bi-person-badge"></i> User Roles</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="role-card">
                        <div class="role-icon" style="background: var(--primary-gradient);">
                            <i class="bi bi-shield-lock"></i>
                        </div>
                        <h3>Admin</h3>
                        <p>System administrators with full control</p>
                        <ul>
                            <li><i class="bi bi-check-circle-fill"></i> Manage all users</li>
                            <li><i class="bi bi-check-circle-fill"></i> View system statistics</li>
                            <li><i class="bi bi-check-circle-fill"></i> Export data to CSV</li>
                            <li><i class="bi bi-check-circle-fill"></i> Monitor activities</li>
                            <li><i class="bi bi-check-circle-fill"></i> System configuration</li>
                        </ul>
                        <a href="/capstone_project/login" class="btn btn-primary">Admin Login</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="role-card">
                        <div class="role-icon" style="background: var(--success-gradient);">
                            <i class="bi bi-person-workspace"></i>
                        </div>
                        <h3>Lecturer</h3>
                        <p>Faculty members supervising projects</p>
                        <ul>
                            <li><i class="bi bi-check-circle-fill"></i> Create topics</li>
                            <li><i class="bi bi-check-circle-fill"></i> Manage registrations</li>
                            <li><i class="bi bi-check-circle-fill"></i> Review submissions</li>
                            <li><i class="bi bi-check-circle-fill"></i> Evaluate students</li>
                            <li><i class="bi bi-check-circle-fill"></i> Track progress</li>
                        </ul>
                        <a href="/capstone_project/login" class="btn btn-success">Lecturer Login</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="role-card">
                        <div class="role-icon" style="background: var(--info-gradient);">
                            <i class="bi bi-person-badge"></i>
                        </div>
                        <h3>Student</h3>
                        <p>Students working on capstone projects</p>
                        <ul>
                            <li><i class="bi bi-check-circle-fill"></i> Browse topics</li>
                            <li><i class="bi bi-check-circle-fill"></i> Register for projects</li>
                            <li><i class="bi bi-check-circle-fill"></i> Submit work</li>
                            <li><i class="bi bi-check-circle-fill"></i> Track status</li>
                            <li><i class="bi bi-check-circle-fill"></i> View feedback</li>
                        </ul>
                        <a href="/capstone_project/login" class="btn btn-info">Student Login</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-6">
                    <div class="stat-item">
                        <div class="stat-number">3</div>
                        <div class="stat-label">Dashboards</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-item">
                        <div class="stat-number">20+</div>
                        <div class="stat-label">Features</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-item">
                        <div class="stat-number">15</div>
                        <div class="stat-label">Database Tables</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-item">
                        <div class="stat-number">100%</div>
                        <div class="stat-label">Secure</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p><strong>Capstone Project Management System</strong></p>
            <p>Version 1.0.0 | Production Ready</p>
            <p>
                <a href="/capstone_project/login"><i class="bi bi-box-arrow-in-right"></i> Login</a> | 
                <a href="/capstone_project/test-db"><i class="bi bi-database"></i> Test Database</a> | 
                <a href="/capstone_project/QUICK_REFERENCE.md"><i class="bi bi-book"></i> Documentation</a>
            </p>
            <p class="mt-3" style="opacity: 0.7;">
                <small>&copy; 2026 Capstone Project Team. All rights reserved.</small>
            </p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>';
    exit;
}, 'HomePage');

// Dispatch the router to handle the current request
$router->dispatch();

// Flush output buffer
ob_end_flush();
