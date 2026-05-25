<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Dashboard - Capstone Project Management</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
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
        .bg-success-gradient { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .bg-info-gradient { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .bg-warning-gradient { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .bg-primary-gradient { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        
        /* Topics List */
        .topics-list {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .topic-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            transition: background 0.3s;
        }
        
        .topic-item:last-child {
            border-bottom: none;
        }
        
        .topic-item:hover {
            background: #f8f9fa;
        }
        
        .topic-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .topic-meta {
            font-size: 14px;
            color: #666;
        }
        
        .badge {
            font-size: 12px;
            padding: 5px 10px;
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
            <i class="bi bi-person-workspace" style="font-size: 48px;"></i>
            <h4>Lecturer Panel</h4>
            <small id="lecturerName">Lecturer</small>
        </div>
        
        <ul class="sidebar-menu">
            <li><a href="#" class="active" data-page="dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="#" data-page="my-topics"><i class="bi bi-journal-text"></i> My Topics</a></li>
            <li><a href="#" data-page="create-topic"><i class="bi bi-plus-circle"></i> Create Topic</a></li>
            <li><a href="#" data-page="registrations"><i class="bi bi-clipboard-check"></i> Registrations</a></li>
            <li><a href="#" data-page="students"><i class="bi bi-people"></i> My Students</a></li>
            <li><a href="#" data-page="evaluations"><i class="bi bi-star"></i> Evaluations</a></li>
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
            <h2><i class="bi bi-speedometer2"></i> Lecturer Dashboard</h2>
            <div class="user-menu">
                <span id="currentTime"></span>
                <div class="user-avatar" id="userAvatar">L</div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <!-- Alert Container -->
            <div id="alertContainer"></div>

            <!-- Welcome Message -->
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill"></i>
                <strong>Welcome back!</strong> You're logged in as Lecturer.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-card-value" id="myTopicsCount">0</div>
                            <div class="stat-card-label">My Topics</div>
                        </div>
                        <div class="stat-card-icon bg-success-gradient">
                            <i class="bi bi-journal-text"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-card-value" id="publishedTopicsCount">0</div>
                            <div class="stat-card-label">Published Topics</div>
                        </div>
                        <div class="stat-card-icon bg-info-gradient">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-card-value" id="studentsCount">0</div>
                            <div class="stat-card-label">My Students</div>
                        </div>
                        <div class="stat-card-icon bg-warning-gradient">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-card-value" id="pendingCount">0</div>
                            <div class="stat-card-label">Pending Registrations</div>
                        </div>
                        <div class="stat-card-icon bg-primary-gradient">
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- My Topics Section -->
            <div class="topics-list">
                <h5 class="mb-3"><i class="bi bi-journal-text"></i> My Topics</h5>
                <div id="topicsList">
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox" style="font-size: 48px;"></i>
                        <p class="mt-2">Loading topics...</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="bi bi-plus-circle text-success" style="font-size: 48px;"></i>
                            <h5 class="mt-3">Create New Topic</h5>
                            <p class="text-muted">Add a new capstone project topic</p>
                            <button class="btn btn-success">Create Topic</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="bi bi-clipboard-check text-info" style="font-size: 48px;"></i>
                            <h5 class="mt-3">Review Registrations</h5>
                            <p class="text-muted">Approve or reject student registrations</p>
                            <button class="btn btn-info">View Registrations</button>
                        </div>
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
                const lecturerId = userResult.data?.lecturer_id;

                if (!lecturerId) {
                    showAlert('Lecturer information not found', 'danger');
                    return;
                }

                // Load all topics to filter by lecturer
                const topicsResponse = await fetch(`${API_BASE_URL}/topics`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                });

                if (topicsResponse.ok) {
                    const topicsResult = await topicsResponse.json();
                    const allTopics = topicsResult.data || [];
                    
                    // Filter topics by current lecturer
                    const myTopics = allTopics.filter(t => t.created_by == lecturerId);
                    const publishedTopics = myTopics.filter(t => t.status === 'Published');
                    
                    // Update counts
                    document.getElementById('myTopicsCount').textContent = myTopics.length;
                    document.getElementById('publishedTopicsCount').textContent = publishedTopics.length;
                    
                    // Calculate total students and pending registrations
                    let totalStudents = 0;
                    let pendingCount = 0;
                    
                    myTopics.forEach(topic => {
                        totalStudents += parseInt(topic.approved_count || 0);
                        pendingCount += parseInt(topic.pending_count || 0);
                    });
                    
                    document.getElementById('studentsCount').textContent = totalStudents;
                    document.getElementById('pendingCount').textContent = pendingCount;
                    
                    // Render topics list
                    renderTopics(myTopics);
                } else {
                    // Show default values on error
                    document.getElementById('myTopicsCount').textContent = '0';
                    document.getElementById('publishedTopicsCount').textContent = '0';
                    document.getElementById('studentsCount').textContent = '0';
                    document.getElementById('pendingCount').textContent = '0';
                }

            } catch (error) {
                console.error('Dashboard error:', error);
                showAlert('Failed to load dashboard data', 'danger');
                
                // Show default values on error
                document.getElementById('myTopicsCount').textContent = '0';
                document.getElementById('publishedTopicsCount').textContent = '0';
                document.getElementById('studentsCount').textContent = '0';
                document.getElementById('pendingCount').textContent = '0';
            } finally {
                showLoading(false);
            }
        }

        /**
         * Render topics list
         */
        function renderTopics(topics) {
            const topicsList = document.getElementById('topicsList');
            
            if (!topics || topics.length === 0) {
                topicsList.innerHTML = `
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox" style="font-size: 48px;"></i>
                        <p class="mt-2">No topics yet. Create your first topic to get started!</p>
                    </div>
                `;
                return;
            }

            topicsList.innerHTML = topics.map(topic => {
                const statusBadge = topic.status === 'Published' ? 'bg-success' : 
                                   topic.status === 'Draft' ? 'bg-warning' : 'bg-secondary';
                
                const approvedCount = parseInt(topic.approved_count || 0);
                const pendingCount = parseInt(topic.pending_count || 0);
                const totalRegistrations = approvedCount + pendingCount;

                return `
                    <div class="topic-item">
                        <div class="topic-title">${escapeHtml(topic.title || 'Untitled Topic')}</div>
                        <div class="topic-meta">
                            <span class="badge ${statusBadge}">${topic.status}</span>
                            <span class="ms-2"><i class="bi bi-people"></i> ${approvedCount} students</span>
                            ${pendingCount > 0 ? `<span class="ms-2 text-warning"><i class="bi bi-clock"></i> ${pendingCount} pending</span>` : ''}
                            <span class="ms-2"><i class="bi bi-calendar"></i> Created: ${formatDate(topic.created_at)}</span>
                        </div>
                    </div>
                `;
            }).join('');
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
                    credentials: 'same-origin' // Important: Include cookies
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    showAlert('Logout successful! Redirecting...', 'success');
                    
                    // Force redirect after short delay
                    setTimeout(() => {
                        // Clear any local storage
                        sessionStorage.clear();
                        localStorage.clear();
                        
                        // Redirect to login
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
            
            // Auto dismiss after 5 seconds
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
