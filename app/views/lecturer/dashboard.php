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
    
    <!-- My Students Styles -->
    <link rel="stylesheet" href="/capstone_project/app/views/lecturer/my_students.css">
    
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
            <li><a href="#" class="menu-link active" data-page="dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="#" class="menu-link" data-page="my-topics"><i class="bi bi-journal-text"></i> My Topics</a></li>
            <li><a href="#" class="menu-link" data-page="create-topic"><i class="bi bi-plus-circle"></i> Create Topic</a></li>
            <li><a href="#" class="menu-link" data-page="registrations"><i class="bi bi-clipboard-check"></i> Registrations</a></li>
            <li><a href="#" class="menu-link" data-page="students"><i class="bi bi-people"></i> My Students</a></li>
            <li><a href="#" class="menu-link" data-page="evaluations"><i class="bi bi-star"></i> Evaluations</a></li>
            <li><a href="#" class="menu-link" data-page="profile"><i class="bi bi-person-circle"></i> Profile</a></li>
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

            <!-- Page Content Container -->
            <div id="pageContent"></div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Configuration - FIXED: Removed /public/ from path
        const API_BASE_URL = '/capstone_project/api';
        
        // Current page state
        let currentPage = 'dashboard';
        let currentLecturerId = null;

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            initializeApp();
        });

        /**
         * Initialize application
         */
        async function initializeApp() {
            // Get current user info
            await loadCurrentUser();
            
            // Setup navigation
            setupNavigation();
            
            // Update clock
            updateClock();
            setInterval(updateClock, 1000);
            
            // Logout button
            document.getElementById('logoutBtn').addEventListener('click', handleLogout);
            
            // Show dashboard page (will load data automatically)
            await showPage('dashboard');
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
                    currentLecturerId = result.data?.lecturer_id;
                    
                    console.log('Lecturer ID loaded:', currentLecturerId); // Debug log
                    
                    if (!currentLecturerId) {
                        showAlert('Lecturer information not found. Please contact administrator.', 'danger');
                    }
                    
                    // Update lecturer name in sidebar
                    const fullName = result.data?.full_name || 'Lecturer';
                    document.getElementById('lecturerName').textContent = fullName;
                    
                    // Update user avatar initial
                    const initial = fullName.charAt(0).toUpperCase();
                    document.getElementById('userAvatar').textContent = initial;
                } else {
                    console.error('Failed to load user info:', response.status);
                }
            } catch (error) {
                console.error('Load user error:', error);
            }
        }

        /**
         * Setup navigation
         */
        function setupNavigation() {
            const menuLinks = document.querySelectorAll('.menu-link');
            menuLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const page = this.getAttribute('data-page');
                    navigateTo(page);
                });
            });
        }

        /**
         * Navigate to a page
         */
        async function navigateTo(page) {
            // Update active menu
            document.querySelectorAll('.menu-link').forEach(link => {
                link.classList.remove('active');
            });
            document.querySelector(`[data-page="${page}"]`)?.classList.add('active');
            
            // Show page
            await showPage(page);
            currentPage = page;
        }

        /**
         * Show specific page
         */
        async function showPage(page) {
            const pageContent = document.getElementById('pageContent');
            
            switch(page) {
                case 'dashboard':
                    await showDashboardPage();
                    break;
                case 'create-topic':
                    showCreateTopicPage();
                    break;
                case 'my-topics':
                    await showMyTopicsPage();
                    break;
                case 'registrations':
                    await showRegistrationsPage();
                    break;
                case 'students':
                    await showMyStudentsPage();
                    break;
                case 'evaluations':
                    await showEvaluationsPage();
                    break;
                case 'profile':
                    await showProfilePage();
                    break;
                default:
                    pageContent.innerHTML = `
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            Page "${page}" is under development.
                        </div>
                    `;
            }
        }

        /**
         * Show dashboard page
         */
        async function showDashboardPage() {
            const pageContent = document.getElementById('pageContent');
            pageContent.innerHTML = `
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
                                <button class="btn btn-success" onclick="navigateTo('create-topic')">Create Topic</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="bi bi-clipboard-check text-info" style="font-size: 48px;"></i>
                                <h5 class="mt-3">Review Registrations</h5>
                                <p class="text-muted">Approve or reject student registrations</p>
                                <button class="btn btn-info" onclick="navigateTo('registrations')">View Registrations</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Wait for DOM to be ready
            await new Promise(resolve => setTimeout(resolve, 100));
            
            // Load dashboard data after rendering
            await loadDashboardData();
        }

        /**
         * Show create topic page
         */
        function showCreateTopicPage() {
            const pageContent = document.getElementById('pageContent');
            pageContent.innerHTML = `
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Create New Topic</h5>
                    </div>
                    <div class="card-body">
                        <form id="createTopicForm">
                            <div class="mb-3">
                                <label for="topicTitle" class="form-label">Topic Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="topicTitle" required maxlength="200">
                            </div>

                            <div class="mb-3">
                                <label for="topicDescription" class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="topicDescription" rows="5" required></textarea>
                                <small class="text-muted">Provide detailed information about the project</small>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="departmentId" class="form-label">Department <span class="text-danger">*</span></label>
                                    <select class="form-select" id="departmentId" required>
                                        <option value="">Select Department</option>
                                        <option value="1">Computer Science</option>
                                        <option value="2">Information Technology</option>
                                        <option value="3">Information Systems</option>
                                        <option value="4">Software Engineering</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="maxStudents" class="form-label">Max Students</label>
                                    <input type="number" class="form-control" id="maxStudents" value="5" min="1" max="10">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="topicStatus" class="form-label">Status</label>
                                <select class="form-select" id="topicStatus">
                                    <option value="Draft">Draft (Not visible to students)</option>
                                    <option value="Published">Published (Visible to students)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="topicTags" class="form-label">Tags (comma-separated)</label>
                                <input type="text" class="form-control" id="topicTags" placeholder="e.g., AI, Machine Learning, Python">
                                <small class="text-muted">Separate tags with commas</small>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle"></i> Create Topic
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="navigateTo('dashboard')">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
            
            // Setup form submission
            document.getElementById('createTopicForm').addEventListener('submit', handleCreateTopic);
        }

        /**
         * Show my topics page
         */
        async function showMyTopicsPage() {
            const pageContent = document.getElementById('pageContent');
            pageContent.innerHTML = `
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-journal-text"></i> My Topics</h5>
                        <button class="btn btn-light btn-sm" onclick="navigateTo('create-topic')">
                            <i class="bi bi-plus-circle"></i> Create New
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="myTopicsContainer">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading topics...</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Load topics
            await loadMyTopics();
        }

        /**
         * Show registrations page
         */
        async function showRegistrationsPage() {
            const pageContent = document.getElementById('pageContent');
            pageContent.innerHTML = `
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> Student Registrations</h5>
                    </div>
                    <div class="card-body">
                        <!-- Filter Tabs -->
                        <ul class="nav nav-tabs mb-3" id="registrationTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                                    <i class="bi bi-clock"></i> Pending
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button" role="tab">
                                    <i class="bi bi-check-circle"></i> Approved
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected" type="button" role="tab">
                                    <i class="bi bi-x-circle"></i> Rejected
                                </button>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content" id="registrationTabContent">
                            <div class="tab-pane fade show active" id="pending" role="tabpanel">
                                <div id="pendingRegistrations">
                                    <div class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status"></div>
                                        <p class="mt-2">Loading...</p>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="approved" role="tabpanel">
                                <div id="approvedRegistrations">
                                    <div class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status"></div>
                                        <p class="mt-2">Loading...</p>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="rejected" role="tabpanel">
                                <div id="rejectedRegistrations">
                                    <div class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status"></div>
                                        <p class="mt-2">Loading...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Load registrations
            await loadRegistrations('Pending');
            
            // Setup tab change listeners
            document.getElementById('pending-tab').addEventListener('shown.bs.tab', () => loadRegistrations('Pending'));
            document.getElementById('approved-tab').addEventListener('shown.bs.tab', () => loadRegistrations('Approved'));
            document.getElementById('rejected-tab').addEventListener('shown.bs.tab', () => loadRegistrations('Rejected'));
        }

        /**
         * Handle create topic form submission
         */
        async function handleCreateTopic(e) {
            e.preventDefault();
            
            if (!currentLecturerId) {
                showAlert('Lecturer information not found', 'danger');
                return;
            }
            
            showLoading(true);
            
            try {
                // Get form data
                const title = document.getElementById('topicTitle').value.trim();
                const description = document.getElementById('topicDescription').value.trim();
                const departmentId = parseInt(document.getElementById('departmentId').value);
                const maxStudents = parseInt(document.getElementById('maxStudents').value);
                const status = document.getElementById('topicStatus').value;
                const tagsInput = document.getElementById('topicTags').value.trim();
                
                // Parse tags
                const tags = tagsInput ? tagsInput.split(',').map(t => t.trim()).filter(t => t) : [];
                
                // Create topic
                const response = await fetch(`${API_BASE_URL}/topics/create`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        title,
                        description,
                        department_id: departmentId,
                        max_students: maxStudents,
                        status,
                        tags
                    })
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    showAlert('Topic created successfully!', 'success');
                    // Navigate to my topics page
                    setTimeout(() => navigateTo('my-topics'), 1500);
                } else {
                    showAlert(result.message || 'Failed to create topic', 'danger');
                }
                
            } catch (error) {
                console.error('Create topic error:', error);
                showAlert('Network error. Please try again.', 'danger');
            } finally {
                showLoading(false);
            }
        }

        /**
         * Load my topics
         */
        async function loadMyTopics() {
            try {
                const response = await fetch(`${API_BASE_URL}/topics/my-topics`, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                
                const result = await response.json();
                const container = document.getElementById('myTopicsContainer');
                
                if (response.ok && result.success) {
                    const topics = result.data || [];
                    
                    if (topics.length === 0) {
                        container.innerHTML = `
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-inbox" style="font-size: 64px;"></i>
                                <p class="mt-3">No topics yet</p>
                                <button class="btn btn-success" onclick="navigateTo('create-topic')">
                                    <i class="bi bi-plus-circle"></i> Create Your First Topic
                                </button>
                            </div>
                        `;
                        return;
                    }
                    
                    container.innerHTML = topics.map(topic => {
                        const statusBadge = topic.status === 'Published' ? 'bg-success' : 'bg-warning';
                        const statusIcon = topic.status === 'Published' ? 'bi-check-circle' : 'bi-clock';
                        return `
                            <div class="card mb-3 shadow-sm">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-9">
                                            <h5 class="card-title">
                                                <i class="bi bi-journal-text text-primary"></i>
                                                ${escapeHtml(topic.title)}
                                            </h5>
                                            <p class="card-text text-muted mb-2">
                                                ${escapeHtml(topic.description).substring(0, 150)}${topic.description.length > 150 ? '...' : ''}
                                            </p>
                                            <div class="mb-2">
                                                <span class="badge ${statusBadge}">
                                                    <i class="bi ${statusIcon}"></i> ${topic.status}
                                                </span>
                                                <span class="badge bg-secondary">
                                                    <i class="bi bi-building"></i> ${topic.department_name}
                                                </span>
                                                <span class="badge bg-light text-dark">
                                                    <i class="bi bi-people-fill"></i> Max: ${topic.max_students}
                                                </span>
                                                ${topic.tags ? topic.tags.split(', ').map(tag => 
                                                    `<span class="badge bg-info"><i class="bi bi-tag"></i> ${escapeHtml(tag)}</span>`
                                                ).join(' ') : ''}
                                            </div>
                                            <div class="d-flex gap-3 text-muted small">
                                                <span>
                                                    <i class="bi bi-people text-success"></i> 
                                                    <strong>${topic.approved_count || 0}</strong> approved
                                                </span>
                                                ${parseInt(topic.pending_count || 0) > 0 ? `
                                                    <span class="text-warning">
                                                        <i class="bi bi-clock"></i> 
                                                        <strong>${topic.pending_count}</strong> pending
                                                    </span>
                                                ` : ''}
                                                <span>
                                                    <i class="bi bi-calendar"></i> 
                                                    ${formatDate(topic.created_at)}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-end">
                                            <div class="btn-group-vertical w-100" role="group">
                                                <button class="btn btn-sm btn-outline-info" onclick="viewTopicDetails(${topic.id})" title="View Details">
                                                    <i class="bi bi-eye"></i> View
                                                </button>
                                                <button class="btn btn-sm btn-outline-primary" onclick="editTopic(${topic.id})" title="Edit Topic">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>
                                                ${topic.status === 'Published' ? `
                                                    <button class="btn btn-sm btn-outline-warning" onclick="toggleTopicStatus(${topic.id}, 'Draft')" title="Unpublish">
                                                        <i class="bi bi-eye-slash"></i> Unpublish
                                                    </button>
                                                ` : `
                                                    <button class="btn btn-sm btn-outline-success" onclick="toggleTopicStatus(${topic.id}, 'Published')" title="Publish">
                                                        <i class="bi bi-check-circle"></i> Publish
                                                    </button>
                                                `}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('');
                } else {
                    container.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> ${result.message || 'Failed to load topics'}
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Load topics error:', error);
                document.getElementById('myTopicsContainer').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> Network error
                    </div>
                `;
            }
        }

        /**
         * Load registrations by status
         */
        async function loadRegistrations(status) {
            const containerId = status.toLowerCase() + 'Registrations';
            const container = document.getElementById(containerId);
            
            try {
                const response = await fetch(`${API_BASE_URL}/topics/my-registrations?status=${status}`, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    const registrations = result.data || [];
                    
                    if (registrations.length === 0) {
                        container.innerHTML = `
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-inbox" style="font-size: 48px;"></i>
                                <p class="mt-2">No ${status.toLowerCase()} registrations</p>
                            </div>
                        `;
                        return;
                    }
                    
                    container.innerHTML = registrations.map(reg => `
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h6 class="card-title">${escapeHtml(reg.student_name)}</h6>
                                        <p class="mb-1"><strong>Student Code:</strong> ${reg.student_code}</p>
                                        <p class="mb-1"><strong>Topic:</strong> ${escapeHtml(reg.topic_title)}</p>
                                        <p class="mb-1"><strong>Department:</strong> ${reg.department_name}</p>
                                        <p class="mb-1"><strong>Registered:</strong> ${formatDate(reg.registered_at)}</p>
                                        ${reg.rejection_reason ? `<p class="mb-1 text-danger"><strong>Reason:</strong> ${escapeHtml(reg.rejection_reason)}</p>` : ''}
                                    </div>
                                    <div class="col-md-4 text-end">
                                        ${status === 'Pending' ? `
                                            <button class="btn btn-success btn-sm mb-2 w-100" onclick="approveRegistration(${reg.id})">
                                                <i class="bi bi-check-circle"></i> Approve
                                            </button>
                                            <button class="btn btn-danger btn-sm w-100" onclick="rejectRegistration(${reg.id})">
                                                <i class="bi bi-x-circle"></i> Reject
                                            </button>
                                        ` : `
                                            <span class="badge bg-${status === 'Approved' ? 'success' : 'danger'} fs-6">${status}</span>
                                        `}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).join('');
                } else {
                    container.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> ${result.message || 'Failed to load registrations'}
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Load registrations error:', error);
                container.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> Network error
                    </div>
                `;
            }
        }

        /**
         * Approve registration
         */
        async function approveRegistration(registrationId) {
            if (!confirm('Are you sure you want to approve this registration?')) {
                return;
            }
            
            showLoading(true);
            
            try {
                const response = await fetch(`${API_BASE_URL}/topics/registrations/${registrationId}/approve`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    showAlert('Registration approved successfully!', 'success');
                    // Reload registrations
                    await loadRegistrations('Pending');
                } else {
                    showAlert(result.message || 'Failed to approve registration', 'danger');
                }
            } catch (error) {
                console.error('Approve error:', error);
                showAlert('Network error. Please try again.', 'danger');
            } finally {
                showLoading(false);
            }
        }

        /**
         * Reject registration
         */
        async function rejectRegistration(registrationId) {
            const reason = prompt('Please enter rejection reason:');
            
            if (!reason || reason.trim() === '') {
                showAlert('Rejection reason is required', 'warning');
                return;
            }
            
            showLoading(true);
            
            try {
                const response = await fetch(`${API_BASE_URL}/topics/registrations/${registrationId}/reject`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ reason: reason.trim() })
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    showAlert('Registration rejected', 'success');
                    // Reload registrations
                    await loadRegistrations('Pending');
                } else {
                    showAlert(result.message || 'Failed to reject registration', 'danger');
                }
            } catch (error) {
                console.error('Reject error:', error);
                showAlert('Network error. Please try again.', 'danger');
            } finally {
                showLoading(false);
            }
        }

        /**
         * View topic details
         */
        async function viewTopicDetails(topicId) {
            showLoading(true);
            
            try {
                const response = await fetch(`${API_BASE_URL}/topics/${topicId}`, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    const topic = result.data;
                    
                    // Show modal with topic details
                    const modalHtml = `
                        <div class="modal fade" id="topicDetailsModal" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">${escapeHtml(topic.title)}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <strong>Status:</strong> 
                                            <span class="badge ${topic.status === 'Published' ? 'bg-success' : 'bg-warning'}">${topic.status}</span>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Department:</strong> ${escapeHtml(topic.department_name)}
                                        </div>
                                        <div class="mb-3">
                                            <strong>Description:</strong>
                                            <p class="mt-2">${escapeHtml(topic.description)}</p>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Max Students:</strong> ${topic.max_students}
                                        </div>
                                        <div class="mb-3">
                                            <strong>Current Students:</strong> ${topic.approved_count || 0}
                                        </div>
                                        ${topic.tags ? `
                                            <div class="mb-3">
                                                <strong>Tags:</strong><br>
                                                ${topic.tags.split(', ').map(tag => `<span class="badge bg-info me-1">${escapeHtml(tag)}</span>`).join('')}
                                            </div>
                                        ` : ''}
                                        <div class="mb-3">
                                            <strong>Created:</strong> ${formatDate(topic.created_at)}
                                        </div>
                                        <div class="mb-3">
                                            <strong>Last Updated:</strong> ${formatDate(topic.updated_at)}
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="button" class="btn btn-primary" onclick="editTopic(${topicId})" data-bs-dismiss="modal">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Remove old modal if exists
                    const oldModal = document.getElementById('topicDetailsModal');
                    if (oldModal) oldModal.remove();
                    
                    // Add modal to body
                    document.body.insertAdjacentHTML('beforeend', modalHtml);
                    
                    // Show modal
                    const modal = new bootstrap.Modal(document.getElementById('topicDetailsModal'));
                    modal.show();
                    
                } else {
                    showAlert(result.message || 'Failed to load topic details', 'danger');
                }
            } catch (error) {
                console.error('View topic error:', error);
                showAlert('Network error. Please try again.', 'danger');
            } finally {
                showLoading(false);
            }
        }

        /**
         * Edit topic
         */
        async function editTopic(topicId) {
            showLoading(true);
            
            try {
                // Fetch topic details
                const response = await fetch(`${API_BASE_URL}/topics/${topicId}`, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    const topic = result.data;
                    
                    // Show edit form
                    const pageContent = document.getElementById('pageContent');
                    pageContent.innerHTML = `
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="bi bi-pencil"></i> Edit Topic</h5>
                            </div>
                            <div class="card-body">
                                <form id="editTopicForm">
                                    <input type="hidden" id="editTopicId" value="${topic.id}">
                                    
                                    <div class="mb-3">
                                        <label for="editTopicTitle" class="form-label">Topic Title <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="editTopicTitle" required maxlength="200" value="${escapeHtml(topic.title)}">
                                    </div>

                                    <div class="mb-3">
                                        <label for="editTopicDescription" class="form-label">Description <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="editTopicDescription" rows="5" required>${escapeHtml(topic.description)}</textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="editMaxStudents" class="form-label">Max Students</label>
                                            <input type="number" class="form-control" id="editMaxStudents" value="${topic.max_students}" min="1" max="10">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="editTopicStatus" class="form-label">Status</label>
                                            <select class="form-select" id="editTopicStatus">
                                                <option value="Draft" ${topic.status === 'Draft' ? 'selected' : ''}>Draft (Not visible to students)</option>
                                                <option value="Published" ${topic.status === 'Published' ? 'selected' : ''}>Published (Visible to students)</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="editTopicTags" class="form-label">Tags (comma-separated)</label>
                                        <input type="text" class="form-control" id="editTopicTags" placeholder="e.g., AI, Machine Learning, Python" value="${topic.tags || ''}">
                                        <small class="text-muted">Separate tags with commas</small>
                                    </div>

                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle"></i> <strong>Note:</strong> Department cannot be changed after creation.
                                        <br><strong>Current Department:</strong> ${escapeHtml(topic.department_name)}
                                    </div>

                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-circle"></i> Update Topic
                                        </button>
                                        <button type="button" class="btn btn-secondary" onclick="navigateTo('my-topics')">
                                            <i class="bi bi-x-circle"></i> Cancel
                                        </button>
                                        <button type="button" class="btn btn-danger ms-auto" onclick="deleteTopic(${topic.id})">
                                            <i class="bi bi-trash"></i> Delete Topic
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    `;
                    
                    // Setup form submission
                    document.getElementById('editTopicForm').addEventListener('submit', handleUpdateTopic);
                    
                } else {
                    showAlert(result.message || 'Failed to load topic', 'danger');
                }
            } catch (error) {
                console.error('Edit topic error:', error);
                showAlert('Network error. Please try again.', 'danger');
            } finally {
                showLoading(false);
            }
        }

        /**
         * Handle update topic form submission
         */
        async function handleUpdateTopic(e) {
            e.preventDefault();
            
            showLoading(true);
            
            try {
                const topicId = parseInt(document.getElementById('editTopicId').value);
                const title = document.getElementById('editTopicTitle').value.trim();
                const description = document.getElementById('editTopicDescription').value.trim();
                const maxStudents = parseInt(document.getElementById('editMaxStudents').value);
                const status = document.getElementById('editTopicStatus').value;
                const tagsInput = document.getElementById('editTopicTags').value.trim();
                
                // Parse tags
                const tags = tagsInput ? tagsInput.split(',').map(t => t.trim()).filter(t => t) : [];
                
                // Update topic
                const response = await fetch(`${API_BASE_URL}/topics/${topicId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        title,
                        description,
                        max_students: maxStudents,
                        status,
                        tags
                    })
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    showAlert('Topic updated successfully!', 'success');
                    // Navigate back to my topics
                    setTimeout(() => navigateTo('my-topics'), 1500);
                } else {
                    showAlert(result.message || 'Failed to update topic', 'danger');
                }
                
            } catch (error) {
                console.error('Update topic error:', error);
                showAlert('Network error. Please try again.', 'danger');
            } finally {
                showLoading(false);
            }
        }

        /**
         * Delete topic
         */
        async function deleteTopic(topicId) {
            if (!confirm('Are you sure you want to delete this topic? This action cannot be undone.')) {
                return;
            }
            
            showLoading(true);
            
            try {
                const response = await fetch(`${API_BASE_URL}/topics/${topicId}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    showAlert('Topic deleted successfully!', 'success');
                    // Navigate back to my topics
                    setTimeout(() => navigateTo('my-topics'), 1500);
                } else {
                    showAlert(result.message || 'Failed to delete topic', 'danger');
                }
                
            } catch (error) {
                console.error('Delete topic error:', error);
                showAlert('Network error. Please try again.', 'danger');
            } finally {
                showLoading(false);
            }
        }

        /**
         * Delete topic
         */
        async function deleteTopic(topicId) {
            if (!confirm('Are you sure you want to delete this topic? This action cannot be undone.')) {
                return;
            }
            
            showLoading(true);
            
            try {
                const response = await fetch(`${API_BASE_URL}/topics/${topicId}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    showAlert('Topic deleted successfully!', 'success');
                    // Navigate back to my topics
                    setTimeout(() => navigateTo('my-topics'), 1500);
                } else {
                    showAlert(result.message || 'Failed to delete topic', 'danger');
                }
                
            } catch (error) {
                console.error('Delete topic error:', error);
                showAlert('Network error. Please try again.', 'danger');
            } finally {
                showLoading(false);
            }
        }

        /**
         * Toggle topic status (Publish/Unpublish)
         */
        async function toggleTopicStatus(topicId, newStatus) {
            const action = newStatus === 'Published' ? 'publish' : 'unpublish';
            
            if (!confirm(`Are you sure you want to ${action} this topic?`)) {
                return;
            }
            
            showLoading(true);
            
            try {
                const response = await fetch(`${API_BASE_URL}/topics/${topicId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ status: newStatus })
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    showAlert(`Topic ${action}ed successfully!`, 'success');
                    // Reload topics
                    await loadMyTopics();
                } else {
                    showAlert(result.message || `Failed to ${action} topic`, 'danger');
                }
                
            } catch (error) {
                console.error('Toggle status error:', error);
                showAlert('Network error. Please try again.', 'danger');
            } finally {
                showLoading(false);
            }
        }

        /**
         * Load dashboard data
         */
        async function loadDashboardData() {
            console.log('=== loadDashboardData START ===');
            console.log('Current lecturer ID:', currentLecturerId);
            
            showLoading(true);
            
            try {
                // Ensure lecturer ID is loaded
                if (!currentLecturerId) {
                    console.warn('No lecturer ID, attempting to load user info...');
                    await loadCurrentUser();
                }

                if (!currentLecturerId) {
                    console.error('Still no lecturer ID after loading user');
                    showAlert('Lecturer information not found. Please contact administrator.', 'danger');
                    setDefaultCounts();
                    return;
                }

                console.log('Fetching topics for lecturer:', currentLecturerId);

                // Load lecturer's topics directly (faster than filtering all topics)
                const topicsResponse = await fetch(`${API_BASE_URL}/topics/my-topics`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                });

                console.log('Topics response status:', topicsResponse.status);
                console.log('Topics response ok:', topicsResponse.ok);

                if (!topicsResponse.ok) {
                    const errorText = await topicsResponse.text();
                    console.error('Topics API error:', errorText);
                    showAlert('Failed to load topics. Please refresh the page.', 'danger');
                    setDefaultCounts();
                    return;
                }

                const topicsResult = await topicsResponse.json();
                console.log('Topics result:', topicsResult);
                
                const myTopics = topicsResult.data || [];
                console.log('My topics count:', myTopics.length);
                console.log('My topics:', myTopics);
                
                const publishedTopics = myTopics.filter(t => t.status === 'Published');
                console.log('Published topics count:', publishedTopics.length);
                
                // Update counts - check if elements exist
                const myTopicsCountEl = document.getElementById('myTopicsCount');
                const publishedTopicsCountEl = document.getElementById('publishedTopicsCount');
                const studentsCountEl = document.getElementById('studentsCount');
                const pendingCountEl = document.getElementById('pendingCount');
                
                console.log('Elements found:', {
                    myTopicsCount: !!myTopicsCountEl,
                    publishedTopicsCount: !!publishedTopicsCountEl,
                    studentsCount: !!studentsCountEl,
                    pendingCount: !!pendingCountEl
                });
                
                if (myTopicsCountEl) {
                    myTopicsCountEl.textContent = myTopics.length;
                    console.log('✓ Set myTopicsCount to:', myTopics.length);
                } else {
                    console.error('✗ Element myTopicsCount not found!');
                }
                
                if (publishedTopicsCountEl) {
                    publishedTopicsCountEl.textContent = publishedTopics.length;
                    console.log('✓ Set publishedTopicsCount to:', publishedTopics.length);
                } else {
                    console.error('✗ Element publishedTopicsCount not found!');
                }
                
                // Calculate total students and pending registrations
                let totalStudents = 0;
                let pendingCount = 0;
                
                myTopics.forEach(topic => {
                    const approved = parseInt(topic.approved_count || 0);
                    const pending = parseInt(topic.pending_count || 0);
                    totalStudents += approved;
                    pendingCount += pending;
                    console.log(`Topic "${topic.title}": approved=${approved}, pending=${pending}`);
                });
                
                console.log('Calculated stats:', { totalStudents, pendingCount });
                
                if (studentsCountEl) {
                    studentsCountEl.textContent = totalStudents;
                    console.log('✓ Set studentsCount to:', totalStudents);
                } else {
                    console.error('✗ Element studentsCount not found!');
                }
                
                if (pendingCountEl) {
                    pendingCountEl.textContent = pendingCount;
                    console.log('✓ Set pendingCount to:', pendingCount);
                } else {
                    console.error('✗ Element pendingCount not found!');
                }
                
                // Render topics list if element exists
                const topicsListEl = document.getElementById('topicsList');
                if (topicsListEl) {
                    console.log('Rendering topics list...');
                    renderTopics(myTopics);
                } else {
                    console.error('✗ Element topicsList not found!');
                }

                console.log('=== loadDashboardData SUCCESS ===');

            } catch (error) {
                console.error('=== loadDashboardData ERROR ===');
                console.error('Dashboard error:', error);
                showAlert('Failed to load dashboard data: ' + error.message, 'danger');
                setDefaultCounts();
            } finally {
                showLoading(false);
                console.log('=== loadDashboardData END ===');
            }
        }

        /**
         * Set default counts to 0
         */
        function setDefaultCounts() {
            const myTopicsCountEl = document.getElementById('myTopicsCount');
            const publishedTopicsCountEl = document.getElementById('publishedTopicsCount');
            const studentsCountEl = document.getElementById('studentsCount');
            const pendingCountEl = document.getElementById('pendingCount');
            
            if (myTopicsCountEl) myTopicsCountEl.textContent = '0';
            if (publishedTopicsCountEl) publishedTopicsCountEl.textContent = '0';
            if (studentsCountEl) studentsCountEl.textContent = '0';
            if (pendingCountEl) pendingCountEl.textContent = '0';
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

        /**
         * Show evaluations page
         */
        async function showEvaluationsPage() {
            const pageContent = document.getElementById('pageContent');
            pageContent.innerHTML = `
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-star"></i> Student Evaluations</h5>
                    </div>
                    <div class="card-body">
                        <!-- Filter Tabs -->
                        <ul class="nav nav-tabs mb-3" id="evaluationTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="pending-eval-tab" data-bs-toggle="tab" data-bs-target="#pending-eval" type="button" role="tab">
                                    <i class="bi bi-clock"></i> Pending Evaluation
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="partial-eval-tab" data-bs-toggle="tab" data-bs-target="#partial-eval" type="button" role="tab">
                                    <i class="bi bi-hourglass-split"></i> Partial
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="completed-eval-tab" data-bs-toggle="tab" data-bs-target="#completed-eval" type="button" role="tab">
                                    <i class="bi bi-check-circle"></i> Completed
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="summary-tab" data-bs-toggle="tab" data-bs-target="#summary" type="button" role="tab">
                                    <i class="bi bi-graph-up"></i> Summary
                                </button>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content" id="evaluationTabContent">
                            <div class="tab-pane fade show active" id="pending-eval" role="tabpanel">
                                <div id="pendingEvaluations">
                                    <div class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status"></div>
                                        <p class="mt-2">Loading...</p>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="partial-eval" role="tabpanel">
                                <div id="partialEvaluations">
                                    <div class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status"></div>
                                        <p class="mt-2">Loading...</p>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="completed-eval" role="tabpanel">
                                <div id="completedEvaluations">
                                    <div class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status"></div>
                                        <p class="mt-2">Loading...</p>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="summary" role="tabpanel">
                                <div id="evaluationSummary">
                                    <div class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status"></div>
                                        <p class="mt-2">Loading...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Load submissions
            await loadSubmissionsToEvaluate();
        }

        /**
         * Load submissions to evaluate
         */
        async function loadSubmissionsToEvaluate() {
            try {
                const response = await fetch(`${API_BASE_URL}/lecturers/submissions-to-evaluate`, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    const submissions = result.data || [];
                    
                    // Group by evaluation status
                    const pending = submissions.filter(s => s.evaluation_status === 'Pending');
                    const partial = submissions.filter(s => s.evaluation_status === 'Partial');
                    const completed = submissions.filter(s => s.evaluation_status === 'Completed');
                    
                    // Render each group
                    renderSubmissionsList('pendingEvaluations', pending);
                    renderSubmissionsList('partialEvaluations', partial);
                    renderSubmissionsList('completedEvaluations', completed);
                    
                } else {
                    showAlert(result.message || 'Failed to load submissions', 'danger');
                }
            } catch (error) {
                console.error('Load submissions error:', error);
                showAlert('Network error. Please try again.', 'danger');
            }
        }

        /**
         * Render submissions list
         */
        function renderSubmissionsList(containerId, submissions) {
            const container = document.getElementById(containerId);
            
            if (!container) return;
            
            if (submissions.length === 0) {
                container.innerHTML = `
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox" style="font-size: 48px;"></i>
                        <p class="mt-2">No submissions</p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = submissions.map(sub => {
                const statusClass = sub.evaluation_status === 'Completed' ? 'success' : 
                                   sub.evaluation_status === 'Partial' ? 'warning' : 'secondary';
                const lateClass = sub.is_late == '1' ? 'text-danger' : '';
                
                return `
                    <div class="card mb-3 shadow-sm">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h6 class="card-title">
                                        <i class="bi bi-person-circle text-primary"></i>
                                        ${escapeHtml(sub.student_name)} (${sub.student_code})
                                    </h6>
                                    <p class="mb-1">
                                        <strong>Topic:</strong> ${escapeHtml(sub.topic_title)}
                                    </p>
                                    <p class="mb-1">
                                        <strong>Milestone:</strong> ${escapeHtml(sub.milestone_title)}
                                        <span class="badge bg-info ms-2">${sub.weight_percentage}% weight</span>
                                    </p>
                                    <p class="mb-1">
                                        <strong>Submitted:</strong> 
                                        <span class="${lateClass}">${formatDateTime(sub.submitted_at)}</span>
                                        ${sub.is_late == '1' ? '<span class="badge bg-danger ms-2">LATE</span>' : ''}
                                    </p>
                                    <p class="mb-1">
                                        <strong>Deadline:</strong> ${formatDate(sub.deadline)}
                                    </p>
                                    ${sub.submission_comments ? `<p class="mb-1 text-muted"><em>${escapeHtml(sub.submission_comments)}</em></p>` : ''}
                                    <div class="mt-2">
                                        <span class="badge bg-${statusClass}">
                                            ${sub.evaluation_status}
                                        </span>
                                        ${sub.evaluation_count > 0 ? `
                                            <span class="badge bg-info">
                                                ${sub.evaluation_count}/${sub.total_rubrics} criteria evaluated
                                            </span>
                                        ` : ''}
                                        ${sub.average_score > 0 ? `
                                            <span class="badge bg-success">
                                                Average: ${parseFloat(sub.average_score).toFixed(2)}
                                            </span>
                                        ` : ''}
                                    </div>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="d-grid gap-2">
                                        ${sub.file_url ? `
                                            <a href="${escapeHtml(sub.file_url)}" target="_blank" class="btn btn-sm btn-outline-primary" title="Download submission">
                                                <i class="bi bi-download"></i> Download
                                            </a>
                                        ` : ''}
                                        <button class="btn btn-sm btn-warning" onclick="openEvaluationModal(${sub.submission_id}, '${escapeHtml(sub.student_name)}', '${escapeHtml(sub.milestone_title)}')">
                                            <i class="bi bi-star"></i> ${sub.evaluation_status === 'Completed' ? 'View/Edit' : 'Evaluate'}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        /**
         * Open evaluation modal
         */
        async function openEvaluationModal(submissionId, studentName, milestoneTitle) {
            showLoading(true);
            
            try {
                // Get submission details first to get milestone_id
                const submissionsResponse = await fetch(`${API_BASE_URL}/lecturers/submissions-to-evaluate`, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                
                const submissionsResult = await submissionsResponse.json();
                const submission = submissionsResult.data?.find(s => s.submission_id == submissionId);
                
                if (!submission) {
                    showAlert('Submission not found', 'danger');
                    return;
                }
                
                const milestoneId = submission.milestone_id;
                
                // Load rubrics and existing scores in parallel
                const [rubricsResponse, scoresResponse] = await Promise.all([
                    fetch(`${API_BASE_URL}/lecturers/evaluation-rubrics/${milestoneId}`, {
                        method: 'GET',
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    }),
                    fetch(`${API_BASE_URL}/lecturers/evaluation-scores/${submissionId}`, {
                        method: 'GET',
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    })
                ]);
                
                const rubricsResult = await rubricsResponse.json();
                const scoresResult = await scoresResponse.json();
                
                if (!rubricsResponse.ok || !rubricsResult.success) {
                    showAlert(rubricsResult.message || 'Failed to load rubrics', 'danger');
                    return;
                }
                
                const rubrics = rubricsResult.data || [];
                const existingScores = scoresResult.success ? (scoresResult.data || []) : [];
                
                if (rubrics.length === 0) {
                    showAlert('No evaluation rubrics defined for this milestone', 'warning');
                    return;
                }
                
                // Create scores map for easy lookup
                const scoresMap = {};
                existingScores.forEach(score => {
                    scoresMap[score.rubric_id] = score;
                });
                
                // Build modal HTML
                const modalHtml = `
                    <div class="modal fade" id="evaluationModal" tabindex="-1">
                        <div class="modal-dialog modal-xl">
                            <div class="modal-content">
                                <div class="modal-header bg-warning">
                                    <h5 class="modal-title">
                                        <i class="bi bi-star"></i> Evaluate Submission
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-info">
                                        <strong>Student:</strong> ${escapeHtml(studentName)}<br>
                                        <strong>Milestone:</strong> ${escapeHtml(milestoneTitle)}
                                    </div>
                                    
                                    <form id="evaluationForm">
                                        <input type="hidden" id="evalSubmissionId" value="${submissionId}">
                                        
                                        ${rubrics.map((rubric, index) => {
                                            const existingScore = scoresMap[rubric.id];
                                            return `
                                                <div class="card mb-3">
                                                    <div class="card-header">
                                                        <strong>${index + 1}. ${escapeHtml(rubric.criteria_name)}</strong>
                                                        <span class="badge bg-info float-end">Max: ${rubric.max_score}</span>
                                                    </div>
                                                    <div class="card-body">
                                                        ${rubric.description ? `<p class="text-muted mb-3">${escapeHtml(rubric.description)}</p>` : ''}
                                                        
                                                        <div class="row">
                                                            <div class="col-md-3">
                                                                <label class="form-label">Score <span class="text-danger">*</span></label>
                                                                <input type="number" 
                                                                       class="form-control rubric-score" 
                                                                       data-rubric-id="${rubric.id}"
                                                                       data-max="${rubric.max_score}"
                                                                       min="0" 
                                                                       max="${rubric.max_score}" 
                                                                       step="0.5"
                                                                       value="${existingScore ? existingScore.score : ''}"
                                                                       required>
                                                            </div>
                                                            <div class="col-md-9">
                                                                <label class="form-label">Comments</label>
                                                                <textarea class="form-control rubric-comment" 
                                                                          data-rubric-id="${rubric.id}"
                                                                          rows="2"
                                                                          placeholder="Feedback for this criterion...">${existingScore ? escapeHtml(existingScore.comments || '') : ''}</textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            `;
                                        }).join('')}
                                        
                                        <div class="alert alert-secondary">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <strong>Total Score:</strong> <span id="totalScore">0</span> / <span id="totalMax">${rubrics.reduce((sum, r) => sum + parseFloat(r.max_score), 0)}</span>
                                                </div>
                                                <div class="col-md-6 text-end">
                                                    <strong>Percentage:</strong> <span id="totalPercentage">0</span>%
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                        <i class="bi bi-x-circle"></i> Cancel
                                    </button>
                                    <button type="button" class="btn btn-warning" onclick="submitEvaluation()">
                                        <i class="bi bi-check-circle"></i> Submit Evaluation
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Remove old modal if exists
                const oldModal = document.getElementById('evaluationModal');
                if (oldModal) oldModal.remove();
                
                // Add modal to body
                document.body.insertAdjacentHTML('beforeend', modalHtml);
                
                // Setup score calculation
                document.querySelectorAll('.rubric-score').forEach(input => {
                    input.addEventListener('input', updateTotalScore);
                });
                
                // Calculate initial total
                updateTotalScore();
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('evaluationModal'));
                modal.show();
                
            } catch (error) {
                console.error('Open evaluation modal error:', error);
                showAlert('Network error. Please try again.', 'danger');
            } finally {
                showLoading(false);
            }
        }

        /**
         * Update total score display
         */
        function updateTotalScore() {
            let totalScore = 0;
            let totalMax = 0;
            
            document.querySelectorAll('.rubric-score').forEach(input => {
                const score = parseFloat(input.value) || 0;
                const max = parseFloat(input.getAttribute('data-max')) || 0;
                totalScore += score;
                totalMax += max;
            });
            
            const percentage = totalMax > 0 ? (totalScore / totalMax * 100) : 0;
            
            document.getElementById('totalScore').textContent = totalScore.toFixed(2);
            document.getElementById('totalMax').textContent = totalMax.toFixed(2);
            document.getElementById('totalPercentage').textContent = percentage.toFixed(2);
        }

        /**
         * Submit evaluation
         */
        async function submitEvaluation() {
            const submissionId = document.getElementById('evalSubmissionId').value;
            
            // Collect scores
            const scores = [];
            const scoreInputs = document.querySelectorAll('.rubric-score');
            
            for (const input of scoreInputs) {
                const rubricId = input.getAttribute('data-rubric-id');
                const score = parseFloat(input.value);
                const max = parseFloat(input.getAttribute('data-max'));
                
                if (isNaN(score) || score < 0) {
                    showAlert('Please enter valid scores for all criteria', 'warning');
                    return;
                }
                
                if (score > max) {
                    showAlert(`Score cannot exceed maximum (${max}) for criterion ${rubricId}`, 'warning');
                    return;
                }
                
                const commentInput = document.querySelector(`.rubric-comment[data-rubric-id="${rubricId}"]`);
                const comments = commentInput ? commentInput.value.trim() : null;
                
                scores.push({
                    rubric_id: parseInt(rubricId),
                    score: score,
                    comments: comments
                });
            }
            
            if (scores.length === 0) {
                showAlert('No scores to submit', 'warning');
                return;
            }
            
            showLoading(true);
            
            try {
                const response = await fetch(`${API_BASE_URL}/lecturers/evaluate-submission`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        submission_id: parseInt(submissionId),
                        scores: scores
                    })
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    showAlert('Evaluation submitted successfully!', 'success');
                    
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('evaluationModal'));
                    if (modal) modal.hide();
                    
                    // Reload submissions
                    await loadSubmissionsToEvaluate();
                } else {
                    showAlert(result.message || 'Failed to submit evaluation', 'danger');
                }
            } catch (error) {
                console.error('Submit evaluation error:', error);
                showAlert('Network error. Please try again.', 'danger');
            } finally {
                showLoading(false);
            }
        }

        /**
         * Format date and time
         */
        function formatDateTime(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        /**
         * Show profile page
         */
        async function showProfilePage() {
            const pageContent = document.getElementById('pageContent');
            pageContent.innerHTML = `
                <div class="row">
                    <!-- Profile Information Card -->
                    <div class="col-md-8 mb-4">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="bi bi-person-circle"></i> Profile Information</h5>
                            </div>
                            <div class="card-body">
                                <div id="profileContent">
                                    <div class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status"></div>
                                        <p class="mt-2">Loading profile...</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Change Password Card -->
                        <div class="card mt-4">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0"><i class="bi bi-shield-lock"></i> Change Password</h5>
                            </div>
                            <div class="card-body">
                                <form id="changePasswordForm">
                                    <div class="mb-3">
                                        <label for="currentPassword" class="form-label">Current Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="currentPassword" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="newPassword" class="form-label">New Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="newPassword" required minlength="6">
                                        <small class="text-muted">Minimum 6 characters</small>
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirmPassword" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="confirmPassword" required minlength="6">
                                    </div>
                                    <button type="submit" class="btn btn-warning">
                                        <i class="bi bi-key"></i> Change Password
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Card -->
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Statistics</h5>
                            </div>
                            <div class="card-body">
                                <div id="profileStats">
                                    <div class="text-center py-4">
                                        <div class="spinner-border text-info" role="status"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Account Info Card -->
                        <div class="card mt-4">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Account Info</h5>
                            </div>
                            <div class="card-body">
                                <div id="accountInfo">
                                    <div class="text-center py-4">
                                        <div class="spinner-border text-secondary" role="status"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Load profile data
            await loadProfileData();
            
            // Setup form handlers
            document.getElementById('changePasswordForm').addEventListener('submit', handleChangePassword);
        }

        /**
         * Load profile data
         */
        async function loadProfileData() {
            try {
                const response = await fetch(`${API_BASE_URL}/lecturers/profile`, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    const profile = result.data;
                    renderProfileInfo(profile);
                    renderProfileStats(profile.statistics);
                    renderAccountInfo(profile);
                } else {
                    showAlert(result.message || 'Failed to load profile', 'danger');
                }
            } catch (error) {
                console.error('Load profile error:', error);
                showAlert('Network error. Please try again.', 'danger');
            }
        }

        /**
         * Render profile information
         */
        function renderProfileInfo(profile) {
            const container = document.getElementById('profileContent');
            
            container.innerHTML = `
                <form id="profileForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label"><strong>Lecturer Code</strong></label>
                            <input type="text" class="form-control" value="${escapeHtml(profile.lecturer_code)}" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><strong>Username</strong></label>
                            <input type="text" class="form-control" value="${escapeHtml(profile.username)}" disabled>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="profileFullName" class="form-label"><strong>Full Name</strong> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="profileFullName" value="${escapeHtml(profile.full_name)}" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="profileEmail" class="form-label"><strong>Email</strong></label>
                            <input type="email" class="form-control" id="profileEmail" value="${escapeHtml(profile.email || '')}">
                        </div>
                        <div class="col-md-6">
                            <label for="profilePhone" class="form-label"><strong>Phone</strong></label>
                            <input type="tel" class="form-control" id="profilePhone" value="${escapeHtml(profile.phone || '')}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Department</strong></label>
                        <input type="text" class="form-control" value="${escapeHtml(profile.department_name)}" disabled>
                    </div>

                    <div class="mb-3">
                        <label for="profileSpecialization" class="form-label"><strong>Specialization</strong></label>
                        <textarea class="form-control" id="profileSpecialization" rows="3" placeholder="e.g., Machine Learning, AI, Data Science">${escapeHtml(profile.specialization || '')}</textarea>
                        <small class="text-muted">Your areas of expertise</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Max Quota</strong></label>
                        <input type="number" class="form-control" value="${profile.max_quota}" disabled>
                        <small class="text-muted">Maximum number of students you can supervise</small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Update Profile
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="loadProfileData()">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </button>
                    </div>
                </form>
            `;
            
            // Setup form submission
            document.getElementById('profileForm').addEventListener('submit', handleUpdateProfile);
        }

        /**
         * Render profile statistics
         */
        function renderProfileStats(stats) {
            const container = document.getElementById('profileStats');
            
            container.innerHTML = `
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><i class="bi bi-journal-text text-primary"></i> Total Topics</span>
                        <strong class="fs-4">${stats.total_topics}</strong>
                    </div>
                    <div class="progress" style="height: 5px;">
                        <div class="progress-bar bg-primary" style="width: 100%"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><i class="bi bi-check-circle text-success"></i> Published Topics</span>
                        <strong class="fs-4">${stats.published_topics}</strong>
                    </div>
                    <div class="progress" style="height: 5px;">
                        <div class="progress-bar bg-success" style="width: ${stats.total_topics > 0 ? (stats.published_topics / stats.total_topics * 100) : 0}%"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><i class="bi bi-people text-info"></i> Total Students</span>
                        <strong class="fs-4">${stats.total_students}</strong>
                    </div>
                    <div class="progress" style="height: 5px;">
                        <div class="progress-bar bg-info" style="width: 100%"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><i class="bi bi-clock text-warning"></i> Pending Registrations</span>
                        <strong class="fs-4">${stats.pending_registrations}</strong>
                    </div>
                    <div class="progress" style="height: 5px;">
                        <div class="progress-bar bg-warning" style="width: 100%"></div>
                    </div>
                </div>

                <hr>

                <div class="text-center">
                    <button class="btn btn-info btn-sm w-100" onclick="navigateTo('dashboard')">
                        <i class="bi bi-speedometer2"></i> View Dashboard
                    </button>
                </div>
            `;
        }

        /**
         * Render account information
         */
        function renderAccountInfo(profile) {
            const container = document.getElementById('accountInfo');
            
            const createdDate = new Date(profile.created_at);
            const updatedDate = new Date(profile.updated_at);
            
            container.innerHTML = `
                <div class="mb-3">
                    <small class="text-muted d-block">Account Created</small>
                    <strong>${createdDate.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</strong>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">Last Updated</small>
                    <strong>${updatedDate.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</strong>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">User ID</small>
                    <strong>${profile.user_id}</strong>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">Lecturer ID</small>
                    <strong>${profile.id}</strong>
                </div>
            `;
        }

        /**
         * Handle update profile form submission
         */
        async function handleUpdateProfile(e) {
            e.preventDefault();
            
            const fullName = document.getElementById('profileFullName').value.trim();
            const email = document.getElementById('profileEmail').value.trim();
            const phone = document.getElementById('profilePhone').value.trim();
            const specialization = document.getElementById('profileSpecialization').value.trim();
            
            if (!fullName) {
                showAlert('Full name is required', 'warning');
                return;
            }
            
            showLoading(true);
            
            try {
                const response = await fetch(`${API_BASE_URL}/lecturers/profile`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        full_name: fullName,
                        email: email,
                        phone: phone,
                        specialization: specialization
                    })
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    showAlert('Profile updated successfully!', 'success');
                    
                    // Update sidebar name if changed
                    if (result.data && result.data.full_name) {
                        document.getElementById('lecturerName').textContent = result.data.full_name;
                    }
                    
                    // Reload profile data
                    await loadProfileData();
                } else {
                    showAlert(result.message || 'Failed to update profile', 'danger');
                }
            } catch (error) {
                console.error('Update profile error:', error);
                showAlert('Network error. Please try again.', 'danger');
            } finally {
                showLoading(false);
            }
        }

        /**
         * Handle change password form submission
         */
        async function handleChangePassword(e) {
            e.preventDefault();
            
            const currentPassword = document.getElementById('currentPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            // Validate passwords
            if (newPassword.length < 6) {
                showAlert('New password must be at least 6 characters', 'warning');
                return;
            }
            
            if (newPassword !== confirmPassword) {
                showAlert('New passwords do not match', 'warning');
                return;
            }
            
            if (currentPassword === newPassword) {
                showAlert('New password must be different from current password', 'warning');
                return;
            }
            
            showLoading(true);
            
            try {
                const response = await fetch(`${API_BASE_URL}/lecturers/change-password`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        current_password: currentPassword,
                        new_password: newPassword,
                        confirm_password: confirmPassword
                    })
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    showAlert('Password changed successfully!', 'success');
                    
                    // Clear form
                    document.getElementById('changePasswordForm').reset();
                } else {
                    showAlert(result.message || 'Failed to change password', 'danger');
                }
            } catch (error) {
                console.error('Change password error:', error);
                showAlert('Network error. Please try again.', 'danger');
            } finally {
                showLoading(false);
            }
        }
    </script>

    <!-- My Students Module -->
    <script src="/capstone_project/app/views/lecturer/my_students.js"></script>
</body>
</html>
