<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Capstone Project Management</title>
    
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-container {
            max-width: 450px;
            width: 100%;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .login-header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .login-body {
            padding: 40px 30px;
        }
        .form-label {
            font-weight: 600;
            color: #333;
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
            width: 100%;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .credentials-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
        }
        .credentials-box h6 {
            color: #667eea;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .credentials-box small {
            display: block;
            margin: 5px 0;
            font-family: monospace;
        }
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        .loading-overlay.active {
            display: flex;
        }
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="bi bi-shield-lock" style="font-size: 48px;"></i>
                <h1>Welcome Back</h1>
                <p class="mb-0">Capstone Project Management System</p>
            </div>
            
            <div class="login-body">
                <!-- Alert Container -->
                <div id="alertContainer"></div>

                <!-- Login Form -->
                <form id="loginForm" novalidate>
                    <div class="mb-3">
                        <label for="username" class="form-label">
                            <i class="bi bi-person-fill text-primary"></i> Username
                        </label>
                        <input type="text" class="form-control form-control-lg" id="username" name="username" required autofocus>
                        <div class="invalid-feedback">
                            Please enter your username.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock-fill text-primary"></i> Password
                        </label>
                        <input type="password" class="form-control form-control-lg" id="password" name="password" required>
                        <div class="invalid-feedback">
                            Please enter your password.
                        </div>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">
                            Remember me
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-login" id="loginBtn">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </button>
                </form>

                <!-- Test Credentials -->
                <div class="credentials-box">
                    <h6><i class="bi bi-info-circle"></i> Test Credentials</h6>
                    <small><strong>Admin:</strong> admin / admin123</small>
                    <small><strong>Lecturer:</strong> lecturer1 / lecturer123</small>
                    <small><strong>Student:</strong> student1 / student123</small>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-3">
            <small class="text-white">
                <a href="/capstone_project/" class="text-white text-decoration-none">
                    <i class="bi bi-house-fill"></i> Back to Home
                </a>
            </small>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Login Script -->
    <script>
        // Configuration - FIXED: Removed /public/ from path
        const API_BASE_URL = '/capstone_project/api';

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('loginForm').addEventListener('submit', handleLogin);
        });

        /**
         * Handle login form submission
         */
        async function handleLogin(event) {
            event.preventDefault();

            // Validate form
            const form = event.target;
            if (!form.checkValidity()) {
                event.stopPropagation();
                form.classList.add('was-validated');
                return;
            }

            // Get form data
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;

            // Show loading
            showLoading(true);
            disableLoginButton(true);

            try {
                // Make API call
                const response = await fetch(`${API_BASE_URL}/auth/login`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        username: username,
                        password: password
                    })
                });

                // Parse response
                const result = await response.json();

                if (response.ok && result.success) {
                    // Success - show message and redirect
                    showAlert('Login successful! Redirecting...', 'success');
                    
                    // Redirect based on role
                    setTimeout(() => {
                        if (result.data && result.data.redirect) {
                            window.location.href = result.data.redirect;
                        } else {
                            // Default redirect to registration page for students
                            window.location.href = '/capstone_project/app/views/student/register.php';
                        }
                    }, 1000);
                } else {
                    // Error
                    showAlert(result.message || 'Login failed', 'danger');
                    disableLoginButton(false);
                }

            } catch (error) {
                console.error('Login error:', error);
                showAlert('Network error. Please try again.', 'danger');
                disableLoginButton(false);
            } finally {
                showLoading(false);
            }
        }

        /**
         * Show alert message
         */
        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}-fill"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            alertContainer.innerHTML = alertHtml;
        }

        /**
         * Show/hide loading overlay
         */
        function showLoading(show) {
            const overlay = document.getElementById('loadingOverlay');
            if (show) {
                overlay.classList.add('active');
            } else {
                overlay.classList.remove('active');
            }
        }

        /**
         * Enable/disable login button
         */
        function disableLoginButton(disable) {
            const btn = document.getElementById('loginBtn');
            if (disable) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Logging in...';
            } else {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-box-arrow-in-right"></i> Login';
            }
        }
    </script>
</body>
</html>
