<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Capstone Project Management</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --sidebar-width: 250px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: var(--primary-gradient);
            color: white;
            padding: 0;
            z-index: 1000;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h4 {
            margin: 10px 0 5px 0;
            font-size: 18px;
        }
        
        .sidebar-header small {
            opacity: 0.8;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }
        
        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            background: rgba(255,255,255,0.1);
            border-left: 4px solid white;
        }
        
        .sidebar-menu li a i {
            margin-right: 10px;
            font-size: 18px;
            width: 25px;
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 0;
        }
        
        /* Top Bar */
        .top-bar {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .top-bar h2 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        /* Content Area */
        .content-area {
            padding: 30px;
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .stat-card-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }
        
        .stat-card-value {
            font-size: 32px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .stat-card-label {
            color: #666;
            font-size: 14px;
        }
        
        /* Color Variants */
        .bg-primary-gradient { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .bg-success-gradient { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .bg-warning-gradient { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .bg-info-gradient { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        
        /* Topics Section */
        .section-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .topic-item {
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 8px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        
        .topic-item:hover {
            border-color: #667eea;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.2);
        }
        
        .topic-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
            font-size: 16px;
        }
        
        .topic-meta {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .topic-description {
            font-size: 14px;
            color: #555;
            margin-bottom: 10px;
        }
        
        .badge {
            font-size: 12px;
            padding: 5px 10px;
        }
        
        /* Timeline */
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #ddd;
        }
        
        .timeline-item {
            position: relative;
            padding-bottom: 20px;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -24px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #667eea;
            border: 2px solid white;
            box-shadow: 0 0 0 2px #667eea;
        }
        
        .timeline-date {
            font-size: 12px;
            color: #999;
        }
        
        .timeline-content {
            font-size: 14px;
            color: #555;
        }
        
        /* Loading Overlay */
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
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner-border text-light" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <i class="bi bi-person-badge" style="font-size: 48px;"></i>
            <h4>Student Panel</h4>
            <small id="studentName">Student</small>
        </div>
        
        <ul class="sidebar-menu">
            <li><a href="#" class="active" data-page="dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="#" data-page="browse-topics"><i class="bi bi-search"></i> Browse Topics</a></li>
            <li><a href="#" data-page="my-registrations"><i class="bi bi-clipboard-check"></i> My Registrations</a></li>
            <li><a href="#" data-page="my-project"><i class="bi bi-folder"></i> My Project</a></li>
            <li><a href="#" data-page="submissions"><i class="bi bi-upload"></i> Submissions</a></li>
            <li><a href="#" data-page="profile"><i class="bi bi-person-circle"></i> Profile</a></li>
        </ul>
        
        <div style="position: absolute; bottom: 20px; width: 100%; padding: 0 20px;">
            <button class="btn btn-light w-100" id="logoutBtn">
                <i class="bi bi-box-arrow-right"></i> Logout
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <h2><i class="bi bi-speedometer2"></i> Student Dashboard</h2>
            <div class="user-menu">
                <span id="currentTime"></span>
                <div class="user-avatar" id="userAvatar">S</div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <!-- Alert Container -->
            <div id="alertContainer"></div>

            <!-- Welcome Message -->
            <div class="alert alert-primary alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle-fill"></i>
                <strong>Welcome back!</strong> You're logged in as Student.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-card-value" id="availableTopicsCount">0</div>
                            <div class="stat-card-label">Available Topics</div>
                        </div>
                        <div class="stat-card-icon bg-primary-gradient">
                            <i class="bi bi-journal-text"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-card-value" id="myRegistrationsCount">0</div>
                            <div class="stat-card-label">My Registrations</div>
                        </div>
                        <div class="stat-card-icon bg-info-gradient">
                            <i class="bi bi-clipboard-check"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-card-value" id="pendingCount">0</div>
                            <div class="stat-card-label">Pending Approval</div>
                        </div>
                        <div class="stat-card-icon bg-warning-gradient">
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-card-value" id="submissionsCount">0</div>
                            <div class="stat-card-label">Submissions</div>
                        </div>
                        <div class="stat-card-icon bg-success-gradient">
                            <i class="bi bi-upload"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-search text-primary" style="font-size: 48px;"></i>
                            <h5 class="mt-3">Browse Topics</h5>
                            <p class="text-muted">Explore available capstone project topics</p>
                            <button class="btn btn-primary" onclick="browseTopic()">Browse Now</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-clipboard-check text-success" style="font-size: 48px;"></i>
                            <h5 class="mt-3">My Registrations</h5>
                            <p class="text-muted">View your topic registration status</p>
                            <button class="btn btn-success" onclick="viewRegistrations()">View Status</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- My Registrations Section -->
            <div class="section-card">
                <h5 class="mb-3"><i class="bi bi-clipboard-check"></i> My Registrations</h5>
                <div id="registrationsList">
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox" style="font-size: 48px;"></i>
                        <p class="mt-2">No registrations yet. Browse topics to get started!</p>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="section-card">
                <h5 class="mb-3"><i class="bi bi-clock-history"></i> Recent Activity</h5>
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-date">Today, 10:30 AM</div>
                        <div class="timeline-content"><strong>Logged in</strong> to the system</div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-date">Yesterday, 3:45 PM</div>
                        <div class="timeline-content"><strong>Viewed</strong> AI-Powered Chatbot topic</div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-date">2 days ago</div>
                        <div class="timeline-content"><strong>Registered</strong> for E-Commerce Platform topic</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Configuration - FIXED: Removed /public/ from path
        const API_BASE_URL = '/capstone_project/api';

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardData();
            updateClock();
            setInterval(updateClock, 1000);
            
            // Logout button
            document.getElementById('logoutBtn').addEventListener('click', handleLogout);
        });

        /**
         * Load dashboard data
         */
        async function loadDashboardData() {
            showLoading(true);
            
            try {
                // Get current user info first
                const userResponse = await fetch(`${API_BASE_URL}/auth/me`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                });

                if (!userResponse.ok) {
                    throw new Error('Failed to get user info');
                }

                const userResult = await userResponse.json();
                const studentId = userResult.data?.student_id;

                if (!studentId) {
                    showAlert('Student information not found', 'danger');
                    return;
                }

                // Load available topics
                const topicsResponse = await fetch(`${API_BASE_URL}/topics`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                });

                if (topicsResponse.ok) {
                    const topicsResult = await topicsResponse.json();
                    const topics = topicsResult.data || [];
                    document.getElementById('availableTopicsCount').textContent = topics.length;
                }

                // Load student's registrations
                const registrationsResponse = await fetch(`${API_BASE_URL}/topics/registrations/${studentId}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                });

                if (registrationsResponse.ok) {
                    const registrationsResult = await registrationsResponse.json();
                    const registrations = registrationsResult.data || [];
                    
                    // Update counts
                    document.getElementById('myRegistrationsCount').textContent = registrations.length;
                    
                    const pendingCount = registrations.filter(r => r.status === 'Pending').length;
                    document.getElementById('pendingCount').textContent = pendingCount;
                    
                    // Render registrations list
                    renderRegistrations(registrations);
                } else {
                    // Show empty state if no registrations
                    document.getElementById('myRegistrationsCount').textContent = '0';
                    document.getElementById('pendingCount').textContent = '0';
                }

                // Submissions count (placeholder for now)
                document.getElementById('submissionsCount').textContent = '0';

            } catch (error) {
                console.error('Dashboard error:', error);
                showAlert('Failed to load dashboard data', 'danger');
                
                // Show default values on error
                document.getElementById('availableTopicsCount').textContent = '0';
                document.getElementById('myRegistrationsCount').textContent = '0';
                document.getElementById('pendingCount').textContent = '0';
                document.getElementById('submissionsCount').textContent = '0';
            } finally {
                showLoading(false);
            }
        }

        /**
         * Render registrations list
         */
        function renderRegistrations(registrations) {
            const registrationsList = document.getElementById('registrationsList');
            
            if (!registrations || registrations.length === 0) {
                registrationsList.innerHTML = `
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox" style="font-size: 48px;"></i>
                        <p class="mt-2">No registrations yet. Browse topics to get started!</p>
                    </div>
                `;
                return;
            }

            registrationsList.innerHTML = registrations.map(reg => {
                const statusBadge = reg.status === 'Approved' ? 'bg-success' : 
                                   reg.status === 'Pending' ? 'bg-warning' : 'bg-danger';
                
                const actionButton = reg.status === 'Pending' ? 
                    `<button class="btn btn-sm btn-outline-danger" onclick="withdrawRegistration(${reg.id})">Withdraw</button>` :
                    `<button class="btn btn-sm btn-primary" onclick="viewTopicDetails(${reg.topic_id})">View Details</button>`;

                return `
                    <div class="topic-item">
                        <div class="topic-title">${escapeHtml(reg.topic_title || 'Untitled Topic')}</div>
                        <div class="topic-meta">
                            <span class="badge ${statusBadge}">${reg.status}</span>
                            <span class="ms-2"><i class="bi bi-person"></i> ${escapeHtml(reg.lecturer_name || 'Unknown')}</span>
                            <span class="ms-2"><i class="bi bi-calendar"></i> Registered: ${formatDate(reg.registered_at)}</span>
                        </div>
                        <div class="topic-description">
                            ${escapeHtml(reg.topic_description || 'No description available')}
                        </div>
                        ${actionButton}
                    </div>
                `;
            }).join('');
        }

        /**
         * Withdraw registration
         */
        async function withdrawRegistration(registrationId) {
            if (!confirm('Are you sure you want to withdraw this registration?')) {
                return;
            }

            showLoading(true);

            try {
                const response = await fetch(`${API_BASE_URL}/topics/withdraw`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ registration_id: registrationId })
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    showAlert('Registration withdrawn successfully', 'success');
                    loadDashboardData(); // Reload data
                } else {
                    showAlert(result.message || 'Failed to withdraw registration', 'danger');
                }

            } catch (error) {
                console.error('Withdraw error:', error);
                showAlert('Network error. Please try again.', 'danger');
            } finally {
                showLoading(false);
            }
        }

        /**
         * View topic details
         */
        function viewTopicDetails(topicId) {
            showAlert('Topic details feature coming soon!', 'info');
        }

        /**
         * Format date
         */
        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        }

        /**
         * Escape HTML to prevent XSS
         */
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        /**
         * Browse topics
         */
        function browseTopic() {
            window.location.href = '/capstone_project/app/views/student/register.php';
        }

        /**
         * View registrations
         */
        function viewRegistrations() {
            showAlert('Registration details feature coming soon!', 'info');
        }

        /**
         * Handle logout
         */
        async function handleLogout() {
            if (!confirm('Are you sure you want to logout?')) {
                return;
            }

            showLoading(true);

            try {
                const response = await fetch(`${API_BASE_URL}/auth/logout`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    credentials: 'same-origin'
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    showAlert('Logout successful! Redirecting...', 'success');
                    
                    setTimeout(() => {
                        sessionStorage.clear();
                        localStorage.clear();
                        window.location.href = result.data.redirect;
                    }, 500);
                } else {
                    showAlert(result.message || 'Logout failed', 'danger');
                    showLoading(false);
                }

            } catch (error) {
                console.error('Logout error:', error);
                showAlert('Network error. Please try again.', 'danger');
                showLoading(false);
            }
        }

        /**
         * Update clock
         */
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('currentTime').textContent = timeString;
        }

        /**
         * Show alert
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
            
            setTimeout(() => {
                const alert = alertContainer.querySelector('.alert');
                if (alert) {
                    alert.classList.remove('show');
                    setTimeout(() => alert.remove(), 150);
                }
            }, 5000);
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
    </script>
</body>
</html>
