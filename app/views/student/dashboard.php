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
                            <button class="btn btn-primary" onclick="showBrowseTopicsSection()">Browse Now</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-clipboard-check text-success" style="font-size: 48px;"></i>
                            <h5 class="mt-3">My Registrations</h5>
                            <p class="text-muted">View your topic registration status</p>
                            <button class="btn btn-success" onclick="showMyRegistrationsSection()">View Status</button>
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
        // Configuration
        const API_BASE_URL = '/capstone_project/api';
        
        // Global state
        let currentStudentId = null;
        let currentUserId = null;
        let studentRegistrations = [];
        let availableTopics = [];

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            initializeApp();
        });

        /**
         * Initialize application
         */
        async function initializeApp() {
            // Load current user info first
            await loadCurrentUser();
            
            // Load dashboard data
            await loadDashboardData();
            
            // Update clock
            updateClock();
            setInterval(updateClock, 1000);
            
            // Setup navigation
            setupNavigation();
            
            // Logout button
            document.getElementById('logoutBtn').addEventListener('click', handleLogout);
        }

        /**
         * Load current user information
         */
        async function loadCurrentUser() {
            try {
                const response = await fetch(`${API_BASE_URL}/auth/me`, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });

                if (response.ok) {
                    const result = await response.json();
                    currentStudentId = result.data?.student_id;
                    currentUserId = result.data?.id;
                    
                    // Update student name in sidebar
                    const studentName = result.data?.name || 'Student';
                    document.getElementById('studentName').textContent = studentName;
                    
                    // Update avatar
                    const avatar = studentName.charAt(0).toUpperCase();
                    document.getElementById('userAvatar').textContent = avatar;
                    
                    console.log('Student ID loaded:', currentStudentId);
                    
                    if (!currentStudentId) {
                        showAlert('Student information not found. Please contact administrator.', 'danger');
                    }
                } else {
                    console.error('Failed to load user info:', response.status);
                    showAlert('Failed to load user information', 'danger');
                }
            } catch (error) {
                console.error('Load user error:', error);
                showAlert('Network error while loading user information', 'danger');
            }
        }

        /**
         * Setup navigation
         */
        function setupNavigation() {
            const menuLinks = document.querySelectorAll('.sidebar-menu a');
            menuLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const page = this.getAttribute('data-page');
                    
                    // Update active menu
                    menuLinks.forEach(l => l.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Handle navigation
                    if (page === 'browse-topics') {
                        showBrowseTopicsSection();
                    } else if (page === 'my-registrations') {
                        showMyRegistrationsSection();
                    } else if (page === 'dashboard') {
                        location.reload(); // Reload dashboard
                    } else {
                        showAlert(`${page} feature coming soon!`, 'info');
                    }
                });
            });
        }

        /**
         * Load dashboard data
         */
        async function loadDashboardData() {
            console.log('=== Student loadDashboardData START ===');
            console.log('Current student ID:', currentStudentId);
            
            showLoading(true);
            
            try {
                // Ensure student ID is loaded
                if (!currentStudentId) {
                    console.warn('Student ID not available, waiting for user info...');
                    await loadCurrentUser();
                }

                // Check again after loading
                if (!currentStudentId) {
                    console.error('Student ID still not available after loading user info');
                    showAlert('Unable to load student information. Please refresh the page.', 'danger');
                    showLoading(false);
                    return;
                }

                console.log('Loading data for student ID:', currentStudentId);

                // Load available topics
                console.log('Fetching available topics...');
                await loadAvailableTopics();
                console.log('Available topics loaded:', availableTopics.length);
                
                // Load student's registrations
                console.log('Fetching student registrations...');
                await loadStudentRegistrations();
                console.log('Student registrations loaded:', studentRegistrations.length);
                
                // Update stats
                console.log('Updating statistics...');
                updateStatistics();
                
                // Render registrations list
                console.log('Rendering registrations list...');
                renderRegistrationsList();

                console.log('=== Student loadDashboardData SUCCESS ===');

            } catch (error) {
                console.error('=== Student loadDashboardData ERROR ===');
                console.error('Dashboard error:', error);
                showAlert('Failed to load dashboard data: ' + error.message, 'danger');
            } finally {
                showLoading(false);
                console.log('=== Student loadDashboardData END ===');
            }
        }

        /**
         * Load available topics from API
         */
        async function loadAvailableTopics() {
            try {
                console.log('Calling API:', `${API_BASE_URL}/topics`);
                const response = await fetch(`${API_BASE_URL}/topics`, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });

                console.log('Topics API response status:', response.status);

                if (response.ok) {
                    const result = await response.json();
                    availableTopics = result.data || [];
                    console.log('✓ Available topics loaded:', availableTopics.length);
                } else {
                    const errorText = await response.text();
                    console.error('✗ Failed to load topics:', response.status, errorText);
                    availableTopics = [];
                }
            } catch (error) {
                console.error('✗ Load topics error:', error);
                availableTopics = [];
            }
        }

        /**
         * Load student's registrations from API
         */
        async function loadStudentRegistrations() {
            try {
                const url = `${API_BASE_URL}/topics/registrations/${currentStudentId}`;
                console.log('Calling API:', url);
                
                const response = await fetch(url, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });

                console.log('Registrations API response status:', response.status);

                if (response.ok) {
                    const result = await response.json();
                    studentRegistrations = result.data || [];
                    console.log('✓ Student registrations loaded:', studentRegistrations.length);
                } else {
                    const errorText = await response.text();
                    console.error('✗ Failed to load registrations:', response.status, errorText);
                    studentRegistrations = [];
                }
            } catch (error) {
                console.error('✗ Load registrations error:', error);
                studentRegistrations = [];
            }
        }

        /**
         * Update statistics cards
         */
        function updateStatistics() {
            console.log('Updating statistics...');
            
            // Available topics count
            const availableTopicsCountEl = document.getElementById('availableTopicsCount');
            if (availableTopicsCountEl) {
                availableTopicsCountEl.textContent = availableTopics.length;
                console.log('✓ Set availableTopicsCount to:', availableTopics.length);
            } else {
                console.error('✗ Element availableTopicsCount not found!');
            }
            
            // My registrations count
            const myRegistrationsCountEl = document.getElementById('myRegistrationsCount');
            if (myRegistrationsCountEl) {
                myRegistrationsCountEl.textContent = studentRegistrations.length;
                console.log('✓ Set myRegistrationsCount to:', studentRegistrations.length);
            } else {
                console.error('✗ Element myRegistrationsCount not found!');
            }
            
            // Pending count
            const pendingCount = studentRegistrations.filter(r => r.status === 'Pending').length;
            const pendingCountEl = document.getElementById('pendingCount');
            if (pendingCountEl) {
                pendingCountEl.textContent = pendingCount;
                console.log('✓ Set pendingCount to:', pendingCount);
            } else {
                console.error('✗ Element pendingCount not found!');
            }
            
            // Submissions count (placeholder)
            const submissionsCountEl = document.getElementById('submissionsCount');
            if (submissionsCountEl) {
                submissionsCountEl.textContent = '0';
                console.log('✓ Set submissionsCount to: 0');
            } else {
                console.error('✗ Element submissionsCount not found!');
            }
            
            console.log('Statistics updated successfully');
        }

        /**
         * Render registrations list on dashboard
         */
        function renderRegistrationsList() {
            const registrationsList = document.getElementById('registrationsList');
            
            if (!studentRegistrations || studentRegistrations.length === 0) {
                registrationsList.innerHTML = `
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox" style="font-size: 48px;"></i>
                        <p class="mt-2">No registrations yet. Browse topics to get started!</p>
                        <button class="btn btn-primary mt-2" onclick="showBrowseTopicsSection()">
                            <i class="bi bi-search"></i> Browse Topics
                        </button>
                    </div>
                `;
                return;
            }

            registrationsList.innerHTML = studentRegistrations.map(reg => {
                const statusBadge = getStatusBadge(reg.status);
                const statusIcon = getStatusIcon(reg.status);
                
                // Show withdraw button only for Pending or Approved registrations
                const canWithdraw = reg.status === 'Pending' || reg.status === 'Approved';
                
                return `
                    <div class="topic-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="topic-title">
                                    <i class="bi bi-journal-text text-primary"></i>
                                    ${escapeHtml(reg.topic_title || 'Untitled Topic')}
                                </div>
                                <div class="topic-meta">
                                    <span class="badge ${statusBadge}">
                                        <i class="bi ${statusIcon}"></i> ${reg.status}
                                    </span>
                                    <span class="ms-2">
                                        <i class="bi bi-person"></i> ${escapeHtml(reg.lecturer_name || 'Unknown')}
                                    </span>
                                    <span class="ms-2">
                                        <i class="bi bi-calendar"></i> ${formatDate(reg.registered_at)}
                                    </span>
                                </div>
                                <div class="topic-description mt-2">
                                    ${escapeHtml(truncateText(reg.topic_description || 'No description available', 150))}
                                </div>
                                ${canWithdraw ? `
                                <div class="mt-2">
                                    <button class="btn btn-danger btn-sm" onclick="withdrawRegistration()">
                                        <i class="bi bi-x-circle"></i> Withdraw
                                    </button>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        /**
         * Show Browse Topics section
         */
        async function showBrowseTopicsSection() {
            showLoading(true);
            
            try {
                // Reload topics to get latest data
                await loadAvailableTopics();
                await loadStudentRegistrations();
                
                // Check if student has pending or approved registration
                const hasActiveRegistration = studentRegistrations.some(
                    r => r.status === 'Pending' || r.status === 'Approved'
                );
                
                // Scroll to top
                window.scrollTo({ top: 0, behavior: 'smooth' });
                
                // Replace content area with browse topics view
                const contentArea = document.querySelector('.content-area');
                contentArea.innerHTML = `
                    <!-- Alert Container -->
                    <div id="alertContainer"></div>
                    
                    <!-- Back Button -->
                    <div class="mb-3">
                        <button class="btn btn-outline-secondary" onclick="location.reload()">
                            <i class="bi bi-arrow-left"></i> Back to Dashboard
                        </button>
                    </div>
                    
                    ${hasActiveRegistration ? `
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <strong>Note:</strong> You already have a ${studentRegistrations.find(r => r.status === 'Pending' || r.status === 'Approved').status.toLowerCase()} registration. 
                        You cannot register for additional topics until your current registration is resolved.
                    </div>
                    ` : ''}
                    
                    <!-- Available Topics Section -->
                    <div class="section-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">
                                <i class="bi bi-journal-text"></i> Available Topics
                                <span class="badge bg-primary">${availableTopics.length}</span>
                            </h5>
                            <div>
                                <input type="text" class="form-control form-control-sm" id="searchTopics" 
                                       placeholder="Search topics..." style="width: 250px;">
                            </div>
                        </div>
                        
                        <div id="topicsContainer">
                            ${renderTopicsGrid(hasActiveRegistration)}
                        </div>
                    </div>
                `;
                
                // Setup search functionality
                document.getElementById('searchTopics')?.addEventListener('input', function(e) {
                    const searchTerm = e.target.value.toLowerCase();
                    const filteredTopics = availableTopics.filter(topic => 
                        topic.title.toLowerCase().includes(searchTerm) ||
                        topic.description.toLowerCase().includes(searchTerm) ||
                        (topic.lecturer_name && topic.lecturer_name.toLowerCase().includes(searchTerm))
                    );
                    
                    const container = document.getElementById('topicsContainer');
                    container.innerHTML = renderTopicsGridFiltered(filteredTopics, hasActiveRegistration);
                });
                
            } catch (error) {
                console.error('Browse topics error:', error);
                showAlert('Failed to load topics', 'danger');
            } finally {
                showLoading(false);
            }
        }

        /**
         * Render topics grid
         */
        function renderTopicsGrid(disableRegistration = false) {
            if (!availableTopics || availableTopics.length === 0) {
                return `
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox" style="font-size: 64px;"></i>
                        <p class="mt-3">No topics available at the moment</p>
                        <p class="text-muted">Please check back later</p>
                    </div>
                `;
            }

            return renderTopicsGridFiltered(availableTopics, disableRegistration);
        }

        /**
         * Render filtered topics grid
         */
        function renderTopicsGridFiltered(topics, disableRegistration = false) {
            if (!topics || topics.length === 0) {
                return `
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-search" style="font-size: 64px;"></i>
                        <p class="mt-3">No topics found matching your search</p>
                    </div>
                `;
            }

            return `
                <div class="row">
                    ${topics.map(topic => {
                        // Check if student already registered for this topic
                        const alreadyRegistered = studentRegistrations.some(r => r.topic_id === topic.id);
                        const isDisabled = disableRegistration || alreadyRegistered;
                        
                        let buttonHtml = '';
                        if (alreadyRegistered) {
                            const registration = studentRegistrations.find(r => r.topic_id === topic.id);
                            const statusBadge = getStatusBadge(registration.status);
                            buttonHtml = `
                                <span class="badge ${statusBadge}">
                                    <i class="bi ${getStatusIcon(registration.status)}"></i> 
                                    ${registration.status}
                                </span>
                            `;
                        } else if (disableRegistration) {
                            buttonHtml = `
                                <button class="btn btn-secondary btn-sm" disabled>
                                    <i class="bi bi-lock"></i> Registration Locked
                                </button>
                            `;
                        } else {
                            buttonHtml = `
                                <button class="btn btn-primary btn-sm" onclick="registerForTopic(${topic.id}, ${topic.lecturer_id})">
                                    <i class="bi bi-check-circle"></i> Register
                                </button>
                            `;
                        }
                        
                        return `
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title text-primary">
                                            <i class="bi bi-journal-text"></i>
                                            ${escapeHtml(topic.title)}
                                        </h6>
                                        <p class="card-text small text-muted mb-2">
                                            <i class="bi bi-person"></i> ${escapeHtml(topic.lecturer_name || 'Unknown')}
                                        </p>
                                        <p class="card-text small">
                                            ${escapeHtml(truncateText(topic.description, 100))}
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <small class="text-muted">
                                                <i class="bi bi-people"></i> 
                                                ${topic.current_students || 0}/${topic.max_students || 5} students
                                            </small>
                                            ${buttonHtml}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('')}
                </div>
            `;
        }

        /**
         * Register for a topic
         */
        async function registerForTopic(topicId, lecturerId) {
            if (!currentStudentId) {
                showAlert('Student information not available', 'danger');
                return;
            }

            // Confirm registration
            if (!confirm('Are you sure you want to register for this topic?')) {
                return;
            }

            showLoading(true);

            try {
                const response = await fetch(`${API_BASE_URL}/topics/register`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        student_id: currentStudentId,
                        topic_id: topicId,
                        lecturer_id: lecturerId
                    })
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    // Show success toast
                    showToast('Registration Successful!', 'Your registration has been submitted and is pending approval.', 'success');
                    
                    // Reload data and refresh view
                    await loadStudentRegistrations();
                    await showBrowseTopicsSection();
                } else {
                    showAlert(result.message || 'Failed to register for topic', 'danger');
                }

            } catch (error) {
                console.error('Registration error:', error);
                showAlert('Network error. Please try again.', 'danger');
            } finally {
                showLoading(false);
            }
        }

        /**
         * Withdraw/Cancel registration
         */
        async function withdrawRegistration() {
            if (!currentStudentId) {
                showAlert('Student information not available', 'danger');
                return;
            }

            // Confirm withdrawal with detailed message
            const confirmMessage = 'Are you sure you want to withdraw your registration?\n\n' +
                                 'This action will:\n' +
                                 '• Cancel your current registration\n' +
                                 '• Allow you to register for other topics\n' +
                                 '• Cannot be undone\n\n' +
                                 'Do you want to proceed?';
            
            if (!confirm(confirmMessage)) {
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
                    credentials: 'same-origin'
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    // Show success toast
                    showToast(
                        'Registration Withdrawn!', 
                        result.message || 'You can now register for other topics.', 
                        'success'
                    );
                    
                    // Reload data
                    await loadStudentRegistrations();
                    await loadAvailableTopics();
                    
                    // Update statistics
                    updateStatistics();
                    
                    // Check current view and refresh accordingly
                    const currentView = document.querySelector('.content-area').innerHTML;
                    
                    if (currentView.includes('My Registrations')) {
                        // Refresh My Registrations view
                        await showMyRegistrationsSection();
                    } else if (currentView.includes('Available Topics')) {
                        // Refresh Browse Topics view
                        await showBrowseTopicsSection();
                    } else {
                        // Reload dashboard
                        location.reload();
                    }
                } else {
                    showAlert(result.message || 'Failed to withdraw registration', 'danger');
                }

            } catch (error) {
                console.error('Withdrawal error:', error);
                showAlert('Network error. Please try again.', 'danger');
            } finally {
                showLoading(false);
            }
        }

        /**
         * Show My Registrations section
         */
        async function showMyRegistrationsSection() {
            showLoading(true);
            
            try {
                // Reload registrations
                await loadStudentRegistrations();
                
                // Scroll to top
                window.scrollTo({ top: 0, behavior: 'smooth' });
                
                // Replace content area with registrations view
                const contentArea = document.querySelector('.content-area');
                contentArea.innerHTML = `
                    <!-- Alert Container -->
                    <div id="alertContainer"></div>
                    
                    <!-- Back Button -->
                    <div class="mb-3">
                        <button class="btn btn-outline-secondary" onclick="location.reload()">
                            <i class="bi bi-arrow-left"></i> Back to Dashboard
                        </button>
                    </div>
                    
                    <!-- My Registrations Section -->
                    <div class="section-card">
                        <h5 class="mb-3">
                            <i class="bi bi-clipboard-check"></i> My Registrations
                            <span class="badge bg-primary">${studentRegistrations.length}</span>
                        </h5>
                        
                        <!-- Filter Tabs -->
                        <ul class="nav nav-tabs mb-3" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#all-registrations" type="button">
                                    All (${studentRegistrations.length})
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pending-registrations" type="button">
                                    Pending (${studentRegistrations.filter(r => r.status === 'Pending').length})
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#approved-registrations" type="button">
                                    Approved (${studentRegistrations.filter(r => r.status === 'Approved').length})
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#rejected-registrations" type="button">
                                    Rejected (${studentRegistrations.filter(r => r.status === 'Rejected').length})
                                </button>
                            </li>
                        </ul>
                        
                        <!-- Tab Content -->
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="all-registrations">
                                ${renderRegistrationsDetailed(studentRegistrations)}
                            </div>
                            <div class="tab-pane fade" id="pending-registrations">
                                ${renderRegistrationsDetailed(studentRegistrations.filter(r => r.status === 'Pending'))}
                            </div>
                            <div class="tab-pane fade" id="approved-registrations">
                                ${renderRegistrationsDetailed(studentRegistrations.filter(r => r.status === 'Approved'))}
                            </div>
                            <div class="tab-pane fade" id="rejected-registrations">
                                ${renderRegistrationsDetailed(studentRegistrations.filter(r => r.status === 'Rejected'))}
                            </div>
                        </div>
                    </div>
                `;
                
            } catch (error) {
                console.error('My registrations error:', error);
                showAlert('Failed to load registrations', 'danger');
            } finally {
                showLoading(false);
            }
        }

        /**
         * Render detailed registrations list
         */
        function renderRegistrationsDetailed(registrations) {
            if (!registrations || registrations.length === 0) {
                return `
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox" style="font-size: 64px;"></i>
                        <p class="mt-3">No registrations found</p>
                    </div>
                `;
            }

            return registrations.map(reg => {
                const statusBadge = getStatusBadge(reg.status);
                const statusIcon = getStatusIcon(reg.status);
                
                // Show withdraw button only for Pending or Approved registrations
                const canWithdraw = reg.status === 'Pending' || reg.status === 'Approved';
                
                return `
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="card-title text-primary">
                                        <i class="bi bi-journal-text"></i>
                                        ${escapeHtml(reg.topic_title || 'Untitled Topic')}
                                    </h6>
                                    <p class="card-text mb-2">
                                        ${escapeHtml(reg.topic_description || 'No description available')}
                                    </p>
                                    <div class="small text-muted">
                                        <div class="mb-1">
                                            <i class="bi bi-person"></i> 
                                            <strong>Lecturer:</strong> ${escapeHtml(reg.lecturer_name || 'Unknown')}
                                        </div>
                                        <div class="mb-1">
                                            <i class="bi bi-calendar"></i> 
                                            <strong>Registered:</strong> ${formatDate(reg.registered_at)}
                                        </div>
                                        ${reg.reviewed_at ? `
                                        <div class="mb-1">
                                            <i class="bi bi-clock-history"></i> 
                                            <strong>Reviewed:</strong> ${formatDate(reg.reviewed_at)}
                                        </div>
                                        ` : ''}
                                        ${reg.rejection_reason ? `
                                        <div class="alert alert-danger mt-2 mb-0">
                                            <strong>Rejection Reason:</strong> ${escapeHtml(reg.rejection_reason)}
                                        </div>
                                        ` : ''}
                                    </div>
                                    ${canWithdraw ? `
                                    <div class="mt-3">
                                        <button class="btn btn-danger btn-sm" onclick="withdrawRegistration()">
                                            <i class="bi bi-x-circle"></i> Withdraw Registration
                                        </button>
                                        <small class="text-muted ms-2">
                                            <i class="bi bi-info-circle"></i> You can register for another topic after withdrawing
                                        </small>
                                    </div>
                                    ` : ''}
                                </div>
                                <div class="ms-3">
                                    <span class="badge ${statusBadge} fs-6">
                                        <i class="bi ${statusIcon}"></i> ${reg.status}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        /**
         * Get status badge class
         */
        function getStatusBadge(status) {
            switch(status) {
                case 'Approved': return 'bg-success';
                case 'Pending': return 'bg-warning text-dark';
                case 'Rejected': return 'bg-danger';
                default: return 'bg-secondary';
            }
        }

        /**
         * Get status icon
         */
        function getStatusIcon(status) {
            switch(status) {
                case 'Approved': return 'bi-check-circle-fill';
                case 'Pending': return 'bi-clock-history';
                case 'Rejected': return 'bi-x-circle-fill';
                default: return 'bi-question-circle';
            }
        }

        /**
         * Truncate text
         */
        function truncateText(text, maxLength) {
            if (!text) return '';
            if (text.length <= maxLength) return text;
            return text.substring(0, maxLength) + '...';
        }

        /**
         * Format date
         */
        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        /**
         * Escape HTML to prevent XSS
         */
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        /**
         * Show toast notification
         */
        function showToast(title, message, type = 'success') {
            const toastHtml = `
                <div class="position-fixed top-0 end-0 p-3" style="z-index: 11000">
                    <div class="toast show" role="alert">
                        <div class="toast-header bg-${type} text-white">
                            <i class="bi bi-${type === 'success' ? 'check-circle' : 'info-circle'}-fill me-2"></i>
                            <strong class="me-auto">${title}</strong>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                        </div>
                        <div class="toast-body">
                            ${message}
                        </div>
                    </div>
                </div>
            `;
            
            const toastContainer = document.createElement('div');
            toastContainer.innerHTML = toastHtml;
            document.body.appendChild(toastContainer);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                toastContainer.remove();
            }, 5000);
        }
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
