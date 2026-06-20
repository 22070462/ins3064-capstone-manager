<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Capstone Project Management</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
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
        .bg-danger-gradient { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
        .bg-purple-gradient { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); }
        
        /* Charts Section */
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .chart-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .chart-card h5 {
            margin-bottom: 20px;
            color: #333;
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }
        
        .btn-export {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-export:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
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
            
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* Card Styles */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .card-header {
            border-bottom: 1px solid #f0f0f0;
            padding: 15px 20px;
        }
        
        /* Table Styles */
        .table th {
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #dee2e6;
        }
        
        .table tbody tr {
            transition: background-color 0.2s;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        /* Badge Styles */
        .badge {
            font-weight: 500;
            padding: 5px 10px;
        }
        
        /* Button Group Styles */
        .btn-group-sm .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
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
            <i class="bi bi-shield-lock" style="font-size: 48px;"></i>
            <h4>Admin Panel</h4>
            <small id="adminName">Administrator</small>
        </div>
        
        <ul class="sidebar-menu">
            <li><a href="#" class="active" data-page="dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="#" data-page="users"><i class="bi bi-people"></i> Users</a></li>
            <li><a href="#" data-page="students"><i class="bi bi-person-badge"></i> Students</a></li>
            <li><a href="#" data-page="lecturers"><i class="bi bi-person-workspace"></i> Lecturers</a></li>
            <li><a href="#" data-page="topics"><i class="bi bi-journal-text"></i> Topics</a></li>
            <li><a href="#" data-page="registrations"><i class="bi bi-clipboard-check"></i> Registrations</a></li>
            <li><a href="#" data-page="reports"><i class="bi bi-graph-up"></i> Reports</a></li>
            <li><a href="#" data-page="settings"><i class="bi bi-gear"></i> Settings</a></li>
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
            <h2><i class="bi bi-speedometer2"></i> Dashboard Overview</h2>
            <div class="user-menu">
                <span id="currentTime"></span>
                <div class="user-avatar" id="userAvatar">A</div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <!-- Alert Container -->
            <div id="alertContainer"></div>

            <!-- Stats Grid -->
            <div class="stats-grid" id="statsGrid">
                <!-- Stats will be loaded here -->
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button class="btn btn-primary btn-export" onclick="exportData('assignments')">
                    <i class="bi bi-download"></i> Export Assignments
                </button>
                <button class="btn btn-success btn-export" onclick="exportData('students')">
                    <i class="bi bi-download"></i> Export Students
                </button>
                <button class="btn btn-info btn-export" onclick="exportData('topics')">
                    <i class="bi bi-download"></i> Export Topics
                </button>
                <button class="btn btn-warning btn-export" onclick="refreshStats()">
                    <i class="bi bi-arrow-clockwise"></i> Refresh Stats
                </button>
            </div>

            <!-- Charts Grid -->
            <div class="charts-grid">
                <div class="chart-card">
                    <h5><i class="bi bi-bar-chart"></i> Registration Status</h5>
                    <canvas id="registrationChart"></canvas>
                </div>
                
                <div class="chart-card">
                    <h5><i class="bi bi-pie-chart"></i> User Distribution</h5>
                    <canvas id="userChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Configuration - FIXED: Removed /public/ from path
        const API_BASE_URL = '/capstone_project/api';
        let charts = {};
        let currentPage = 'dashboard';
        let usersData = [];

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            initializeApp();
        });

        /**
         * Initialize application
         */
        function initializeApp() {
            loadDashboardData();
            updateClock();
            setInterval(updateClock, 1000);
            
            // Logout button
            document.getElementById('logoutBtn').addEventListener('click', handleLogout);
            
            // Page navigation
            document.querySelectorAll('.sidebar-menu a').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const page = this.getAttribute('data-page');
                    navigateToPage(page);
                });
            });
        }

        /**
         * Navigate to different pages
         */
        function navigateToPage(page) {
            currentPage = page;
            
            // Update active menu
            document.querySelectorAll('.sidebar-menu a').forEach(link => {
                link.classList.remove('active');
            });
            document.querySelector(`[data-page="${page}"]`).classList.add('active');
            
            // Update page title
            const titles = {
                'dashboard': 'Dashboard Overview',
                'users': 'User Management',
                'students': 'Student Management',
                'lecturers': 'Lecturer Management',
                'topics': 'Topic Management',
                'registrations': 'Registration Management',
                'reports': 'Reports & Analytics',
                'settings': 'System Settings'
            };
            
            document.querySelector('.top-bar h2').innerHTML = `<i class="bi bi-${getPageIcon(page)}"></i> ${titles[page] || 'Dashboard'}`;
            
            // Load page content
            switch(page) {
                case 'dashboard':
                    loadDashboardData();
                    break;
                case 'users':
                    loadUsersPage();
                    break;
                case 'students':
                    loadStudentsPage();
                    break;
                case 'lecturers':
                    loadLecturersPage();
                    break;
                case 'topics':
                    loadTopicsPage();
                    break;
                case 'registrations':
                    loadRegistrationsPage();
                    break;
                case 'reports':
                    loadReportsPage();
                    break;
                case 'settings':
                    loadSettingsPage();
                    break;
                default:
                    showComingSoon(page);
            }
        }

        /**
         * Get page icon
         */
        function getPageIcon(page) {
            const icons = {
                'dashboard': 'speedometer2',
                'users': 'people',
                'students': 'person-badge',
                'lecturers': 'person-workspace',
                'topics': 'journal-text',
                'registrations': 'clipboard-check',
                'reports': 'graph-up',
                'settings': 'gear'
            };
            return icons[page] || 'speedometer2';
        }

        /**
         * Show coming soon message
         */
        function showComingSoon(page) {
            const contentArea = document.querySelector('.content-area');
            contentArea.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-tools" style="font-size: 64px; color: #667eea;"></i>
                    <h3 class="mt-3">Coming Soon</h3>
                    <p class="text-muted">The ${page} page is under development.</p>
                    <button class="btn btn-primary mt-3" onclick="navigateToPage('dashboard')">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                    </button>
                </div>
            `;
        }

        /**
         * Load dashboard data from backend API
         * Fetches real statistics from AdminController@getDashboardStats
         */
        async function loadDashboardData() {
            showLoading(true);
            
            try {
                const response = await fetch(`${API_BASE_URL}/admin/dashboard/stats`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin' // Include session cookies
                });

                // Handle authentication errors
                if (response.status === 401 || response.status === 403) {
                    showAlert('Session expired or unauthorized. Redirecting to login...', 'warning');
                    setTimeout(() => {
                        window.location.href = '/capstone_project/login';
                    }, 2000);
                    return;
                }

                const result = await response.json();

                if (response.ok && result.success) {
                    // Check if data exists
                    if (!result.data) {
                        showAlert('No data available', 'warning');
                        renderEmptyState();
                        return;
                    }
                    
                    renderDashboardPage(result.data);
                    showAlert('Dashboard loaded successfully', 'success');
                } else {
                    showAlert(result.message || 'Failed to load dashboard data', 'danger');
                    renderEmptyState();
                }

            } catch (error) {
                console.error('Dashboard error:', error);
                showAlert('Network error. Unable to connect to server. Please check your connection and try again.', 'danger');
                renderEmptyState();
            } finally {
                showLoading(false);
            }
        }

        /**
         * Render dashboard page
         */
        function renderDashboardPage(stats) {
            const contentArea = document.querySelector('.content-area');
            contentArea.innerHTML = `
                <!-- Alert Container -->
                <div id="alertContainer"></div>

                <!-- Stats Grid -->
                <div class="stats-grid" id="statsGrid"></div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button class="btn btn-primary btn-export" onclick="exportData('assignments')">
                        <i class="bi bi-download"></i> Export Assignments
                    </button>
                    <button class="btn btn-success btn-export" onclick="exportData('students')">
                        <i class="bi bi-download"></i> Export Students
                    </button>
                    <button class="btn btn-info btn-export" onclick="exportData('topics')">
                        <i class="bi bi-download"></i> Export Topics
                    </button>
                    <button class="btn btn-warning btn-export" onclick="refreshStats()">
                        <i class="bi bi-arrow-clockwise"></i> Refresh Stats
                    </button>
                </div>

                <!-- Charts Grid -->
                <div class="charts-grid">
                    <div class="chart-card">
                        <h5><i class="bi bi-bar-chart"></i> Registration Status</h5>
                        <canvas id="registrationChart"></canvas>
                    </div>
                    
                    <div class="chart-card">
                        <h5><i class="bi bi-pie-chart"></i> User Distribution</h5>
                        <canvas id="userChart"></canvas>
                    </div>
                </div>
            `;
            
            renderStats(stats);
            renderCharts(stats);
        }

        /**
         * Render statistics cards with real data from database
         * Dynamically updates all metric cards with backend data
         */
        function renderStats(stats) {
            const statsGrid = document.getElementById('statsGrid');
            
            // Validate stats object
            if (!stats || typeof stats !== 'object') {
                console.error('Invalid stats data:', stats);
                renderEmptyState();
                return;
            }
            
            const statCards = [
                { label: 'Total Users', value: stats.total_users || 0, icon: 'people', color: 'primary' },
                { label: 'Students', value: stats.total_students || 0, icon: 'person-badge', color: 'success' },
                { label: 'Lecturers', value: stats.total_lecturers || 0, icon: 'person-workspace', color: 'info' },
                { label: 'Topics', value: stats.total_topics || 0, icon: 'journal-text', color: 'warning' },
                { label: 'Published Topics', value: stats.published_topics || 0, icon: 'check-circle', color: 'success' },
                { label: 'Total Registrations', value: stats.total_registrations || 0, icon: 'clipboard-check', color: 'purple' },
                { label: 'Pending Registrations', value: stats.pending_registrations || 0, icon: 'clock-history', color: 'warning' },
                { label: 'Approved Registrations', value: stats.approved_registrations || 0, icon: 'check2-circle', color: 'success' },
                { label: 'Assignments', value: stats.total_assignments || 0, icon: 'file-earmark-text', color: 'info' },
                { label: 'Submissions', value: stats.total_submissions || 0, icon: 'upload', color: 'primary' },
                { label: 'Departments', value: stats.total_departments || 0, icon: 'building', color: 'danger' },
                { label: 'Active Users', value: stats.active_users || 0, icon: 'person-check', color: 'success' }
            ];

            statsGrid.innerHTML = statCards.map(stat => `
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-card-value">${stat.value}</div>
                            <div class="stat-card-label">${stat.label}</div>
                        </div>
                        <div class="stat-card-icon bg-${stat.color}-gradient">
                            <i class="bi bi-${stat.icon}"></i>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        /**
         * Render empty state when no data is available
         */
        function renderEmptyState() {
            const statsGrid = document.getElementById('statsGrid');
            statsGrid.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 64px; color: #ccc;"></i>
                    <h4 class="mt-3 text-muted">No Data Available</h4>
                    <p class="text-muted">Unable to load dashboard statistics. Please try refreshing the page.</p>
                    <button class="btn btn-primary mt-3" onclick="loadDashboardData()">
                        <i class="bi bi-arrow-clockwise"></i> Retry
                    </button>
                </div>
            `;
        }

        /**
         * Render Chart.js graphs with real backend data
         * Updates both Registration Status and User Distribution charts
         */
        function renderCharts(stats) {
            // Validate stats
            if (!stats || typeof stats !== 'object') {
                console.error('Invalid stats for charts:', stats);
                return;
            }

            // Registration Status Chart (Bar Chart)
            const regCtx = document.getElementById('registrationChart');
            if (!regCtx) {
                console.error('Registration chart canvas not found');
                return;
            }
            
            const regContext = regCtx.getContext('2d');
            if (charts.registration) {
                charts.registration.destroy();
            }
            
            const totalReg = stats.total_registrations || 0;
            const pendingReg = stats.pending_registrations || 0;
            const approvedReg = stats.approved_registrations || 0;
            
            charts.registration = new Chart(regContext, {
                type: 'bar',
                data: {
                    labels: ['Total', 'Pending', 'Approved'],
                    datasets: [{
                        label: 'Registrations',
                        data: [totalReg, pendingReg, approvedReg],
                        backgroundColor: [
                            'rgba(102, 126, 234, 0.8)',
                            'rgba(255, 193, 7, 0.8)',
                            'rgba(40, 167, 69, 0.8)'
                        ],
                        borderColor: [
                            'rgba(102, 126, 234, 1)',
                            'rgba(255, 193, 7, 1)',
                            'rgba(40, 167, 69, 1)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': ' + context.parsed.y + ' registrations';
                                }
                            }
                        }
                    },
                    scales: {
                        y: { 
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                precision: 0
                            }
                        }
                    }
                }
            });

            // User Distribution Chart (Doughnut Chart)
            const userCtx = document.getElementById('userChart');
            if (!userCtx) {
                console.error('User chart canvas not found');
                return;
            }
            
            const userContext = userCtx.getContext('2d');
            if (charts.users) {
                charts.users.destroy();
            }
            
            const totalStudents = stats.total_students || 0;
            const totalLecturers = stats.total_lecturers || 0;
            const totalUsers = stats.total_users || 0;
            const totalAdmins = Math.max(0, totalUsers - totalStudents - totalLecturers);
            
            charts.users = new Chart(userContext, {
                type: 'doughnut',
                data: {
                    labels: ['Students', 'Lecturers', 'Admins'],
                    datasets: [{
                        data: [totalStudents, totalLecturers, totalAdmins],
                        backgroundColor: [
                            'rgba(40, 167, 69, 0.8)',
                            'rgba(23, 162, 184, 0.8)',
                            'rgba(102, 126, 234, 0.8)'
                        ],
                        borderColor: [
                            'rgba(40, 167, 69, 1)',
                            'rgba(23, 162, 184, 1)',
                            'rgba(102, 126, 234, 1)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return label + ': ' + value + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }

        /**
         * Export data to CSV file
         * Triggers native browser download for CSV exports
         * Connects to AdminController export endpoints
         * 
         * @param {string} type - Export type: 'assignments', 'students', or 'topics'
         */
        async function exportData(type) {
            // Validate export type
            const validTypes = ['assignments', 'students', 'topics'];
            if (!validTypes.includes(type)) {
                showAlert('Invalid export type', 'danger');
                return;
            }

            showLoading(true);
            
            try {
                // Construct export URL
                const url = `${API_BASE_URL}/admin/export/${type}`;
                
                // Fetch the CSV file
                const response = await fetch(url, {
                    method: 'GET',
                    credentials: 'same-origin', // Include session cookies
                    headers: {
                        'Accept': 'text/csv, application/json'
                    }
                });

                // Handle authentication errors
                if (response.status === 401 || response.status === 403) {
                    showAlert('Unauthorized. Please login again.', 'danger');
                    setTimeout(() => {
                        window.location.href = '/capstone_project/login';
                    }, 2000);
                    return;
                }

                // Check if response is successful
                if (!response.ok) {
                    // Try to parse error message
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        const errorData = await response.json();
                        showAlert(errorData.message || 'Export failed', 'danger');
                    } else {
                        showAlert(`Export failed with status ${response.status}`, 'danger');
                    }
                    return;
                }

                // Check if we got CSV data
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('text/csv')) {
                    showAlert('Invalid response format. Expected CSV file.', 'danger');
                    return;
                }

                // Get the blob data
                const blob = await response.blob();
                
                // Check if blob has data
                if (blob.size === 0) {
                    showAlert(`No ${type} data available to export`, 'warning');
                    return;
                }

                // Create download link
                const downloadUrl = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = downloadUrl;
                
                // Generate filename with timestamp
                const timestamp = new Date().toISOString().split('T')[0];
                link.download = `${type}_${timestamp}.csv`;
                
                // Trigger download
                document.body.appendChild(link);
                link.click();
                
                // Cleanup
                document.body.removeChild(link);
                window.URL.revokeObjectURL(downloadUrl);
                
                // Show success message
                const typeName = type.charAt(0).toUpperCase() + type.slice(1);
                showAlert(`${typeName} exported successfully!`, 'success');

            } catch (error) {
                console.error('Export error:', error);
                showAlert('Network error during export. Please try again.', 'danger');
            } finally {
                showLoading(false);
            }
        }

        /**
         * Refresh statistics
         * Reloads dashboard data from backend
         */
        function refreshStats() {
            showAlert('Refreshing statistics...', 'info');
            loadDashboardData();
        }

        /**
         * Handle logout
         * Calls AuthController logout endpoint to destroy session
         * Redirects to login page on success
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
                    credentials: 'same-origin' // Important: Include session cookies
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    showAlert('Logout successful! Redirecting...', 'success');
                    
                    // Clear any local/session storage
                    try {
                        sessionStorage.clear();
                        localStorage.clear();
                    } catch (e) {
                        console.warn('Could not clear storage:', e);
                    }
                    
                    // Force redirect after short delay
                    setTimeout(() => {
                        window.location.href = result.data.redirect || '/capstone_project/login';
                    }, 500);
                } else {
                    showAlert(result.message || 'Logout failed', 'danger');
                    showLoading(false);
                }

            } catch (error) {
                console.error('Logout error:', error);
                showAlert('Network error during logout. Please try again.', 'danger');
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

        // ==================== USERS MANAGEMENT PAGE ====================

        /**
         * Load Users Management page
         */
        async function loadUsersPage() {
            showLoading(true);
            
            try {
                const response = await fetch(`${API_BASE_URL}/admin/users`, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });

                if (response.status === 401 || response.status === 403) {
                    showAlert('Unauthorized. Please login again.', 'danger');
                    setTimeout(() => window.location.href = '/capstone_project/login', 2000);
                    return;
                }

                const result = await response.json();

                if (response.ok && result.success) {
                    usersData = result.data || [];
                    renderUsersPage();
                    showAlert('Users loaded successfully', 'success');
                } else {
                    showAlert(result.message || 'Failed to load users', 'danger');
                }

            } catch (error) {
                console.error('Load users error:', error);
                showAlert('Network error. Unable to load users.', 'danger');
            } finally {
                showLoading(false);
            }
        }

        /**
         * Render Users page UI
         */
        function renderUsersPage() {
            const contentArea = document.querySelector('.content-area');
            contentArea.innerHTML = `
                <div id="alertContainer"></div>
                
                <!-- Stats Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <div>
                                    <div class="stat-card-value">${usersData.length}</div>
                                    <div class="stat-card-label">Total Users</div>
                                </div>
                                <div class="stat-card-icon bg-primary-gradient">
                                    <i class="bi bi-people"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <div>
                                    <div class="stat-card-value">${usersData.filter(u => u.role === 'Student').length}</div>
                                    <div class="stat-card-label">Students</div>
                                </div>
                                <div class="stat-card-icon bg-success-gradient">
                                    <i class="bi bi-person-badge"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <div>
                                    <div class="stat-card-value">${usersData.filter(u => u.role === 'Lecturer').length}</div>
                                    <div class="stat-card-label">Lecturers</div>
                                </div>
                                <div class="stat-card-icon bg-info-gradient">
                                    <i class="bi bi-person-workspace"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <div>
                                    <div class="stat-card-value">${usersData.filter(u => u.status === 'Active').length}</div>
                                    <div class="stat-card-label">Active Users</div>
                                </div>
                                <div class="stat-card-icon bg-success-gradient">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-people"></i> User List</h5>
                        <div class="d-flex gap-2">
                            <input type="text" id="userSearchInput" class="form-control form-control-sm" 
                                   placeholder="Search users..." style="width: 250px;">
                            <select id="roleFilter" class="form-select form-select-sm" style="width: 150px;">
                                <option value="">All Roles</option>
                                <option value="Student">Student</option>
                                <option value="Lecturer">Lecturer</option>
                                <option value="Admin">Admin</option>
                            </select>
                            <select id="statusFilter" class="form-select form-select-sm" style="width: 150px;">
                                <option value="">All Status</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="usersTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Full Name</th>
                                        <th>Code</th>
                                        <th>Role</th>
                                        <th>Department</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="usersTableBody">
                                    <!-- Users will be rendered here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;

            // Initialize filters
            document.getElementById('userSearchInput').addEventListener('input', filterUsers);
            document.getElementById('roleFilter').addEventListener('change', filterUsers);
            document.getElementById('statusFilter').addEventListener('change', filterUsers);

            // Render users
            filterUsers();
        }

        /**
         * Filter and render users based on search and filters
         */
        function filterUsers() {
            const searchTerm = document.getElementById('userSearchInput').value.toLowerCase();
            const roleFilter = document.getElementById('roleFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;

            let filteredUsers = usersData.filter(user => {
                const matchSearch = !searchTerm || 
                    user.username.toLowerCase().includes(searchTerm) ||
                    (user.full_name && user.full_name.toLowerCase().includes(searchTerm)) ||
                    (user.email && user.email.toLowerCase().includes(searchTerm)) ||
                    (user.code && user.code.toLowerCase().includes(searchTerm));
                
                const matchRole = !roleFilter || user.role === roleFilter;
                const matchStatus = !statusFilter || user.status === statusFilter;

                return matchSearch && matchRole && matchStatus;
            });

            renderUsersTable(filteredUsers);
        }

        /**
         * Render users table
         */
        function renderUsersTable(users) {
            const tbody = document.getElementById('usersTableBody');
            
            if (users.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <i class="bi bi-inbox" style="font-size: 48px; color: #ccc;"></i>
                            <p class="text-muted mt-2">No users found</p>
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = users.map(user => {
                const statusBadge = user.status === 'Active' 
                    ? '<span class="badge bg-success">Active</span>' 
                    : '<span class="badge bg-secondary">Inactive</span>';
                
                const roleBadge = {
                    'Student': '<span class="badge bg-primary">Student</span>',
                    'Lecturer': '<span class="badge bg-info">Lecturer</span>',
                    'Admin': '<span class="badge bg-danger">Admin</span>'
                }[user.role] || '<span class="badge bg-secondary">Unknown</span>';

                const createdAt = new Date(user.created_at).toLocaleDateString();

                return `
                    <tr>
                        <td>${user.id}</td>
                        <td><strong>${user.username}</strong></td>
                        <td>${user.full_name || 'N/A'}</td>
                        <td>${user.code || 'N/A'}</td>
                        <td>${roleBadge}</td>
                        <td>${user.department || 'N/A'}</td>
                        <td>${statusBadge}</td>
                        <td><small>${createdAt}</small></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-info" onclick="viewUserDetails(${user.id})" 
                                        title="View Details">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-warning" onclick="toggleUserStatus(${user.id}, '${user.status}')" 
                                        title="${user.status === 'Active' ? 'Deactivate' : 'Activate'}">
                                    <i class="bi bi-${user.status === 'Active' ? 'pause-circle' : 'play-circle'}"></i>
                                </button>
                                <button class="btn btn-outline-primary" onclick="showResetPasswordModal(${user.id}, '${user.username}')" 
                                        title="Reset Password">
                                    <i class="bi bi-key"></i>
                                </button>
                                <button class="btn btn-outline-danger" onclick="deleteUserConfirm(${user.id}, '${user.username}')" 
                                        title="Delete User">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        /**
         * View user details
         */
        async function viewUserDetails(userId) {
            showLoading(true);
            
            try {
                const response = await fetch(`${API_BASE_URL}/admin/users/${userId}`, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    showUserDetailsModal(result.data);
                } else {
                    showAlert(result.message || 'Failed to load user details', 'danger');
                }

            } catch (error) {
                console.error('View user details error:', error);
                showAlert('Network error', 'danger');
            } finally {
                showLoading(false);
            }
        }

        /**
         * Show user details modal
         */
        function showUserDetailsModal(user) {
            const roleDetails = user.role_details ? `
                <hr>
                <h6>Role Details</h6>
                ${Object.entries(user.role_details).map(([key, value]) => `
                    <div class="row mb-2">
                        <div class="col-4"><strong>${key.replace(/_/g, ' ').toUpperCase()}:</strong></div>
                        <div class="col-8">${value || 'N/A'}</div>
                    </div>
                `).join('')}
            ` : '';

            const modalHtml = `
                <div class="modal fade" id="userDetailsModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="bi bi-person-circle"></i> User Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row mb-2">
                                    <div class="col-4"><strong>ID:</strong></div>
                                    <div class="col-8">${user.id}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Username:</strong></div>
                                    <div class="col-8">${user.username}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Email:</strong></div>
                                    <div class="col-8">${user.email || 'N/A'}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Role:</strong></div>
                                    <div class="col-8"><span class="badge bg-primary">${user.role}</span></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Status:</strong></div>
                                    <div class="col-8"><span class="badge bg-${user.status === 'Active' ? 'success' : 'secondary'}">${user.status}</span></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Created At:</strong></div>
                                    <div class="col-8">${new Date(user.created_at).toLocaleString()}</div>
                                </div>
                                ${roleDetails}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Remove existing modal
            const existingModal = document.getElementById('userDetailsModal');
            if (existingModal) existingModal.remove();

            // Add and show new modal
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            const modal = new bootstrap.Modal(document.getElementById('userDetailsModal'));
            modal.show();
        }

        /**
         * Toggle user status (Active/Inactive)
         */
        async function toggleUserStatus(userId, currentStatus) {
            const newStatus = currentStatus === 'Active' ? 'Inactive' : 'Active';
            
            if (!confirm(`Are you sure you want to ${newStatus.toLowerCase()} this user?`)) {
                return;
            }

            showLoading(true);

            try {
                const response = await fetch(`${API_BASE_URL}/admin/users/${userId}/status`, {
                    method: 'PUT',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ status: newStatus })
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    showAlert(`User status updated to ${newStatus}`, 'success');
                    loadUsersPage(); // Reload users
                } else {
                    showAlert(result.message || 'Failed to update status', 'danger');
                }

            } catch (error) {
                console.error('Toggle user status error:', error);
                showAlert('Network error', 'danger');
            } finally {
                showLoading(false);
            }
        }

        /**
         * Show reset password modal
         */
        function showResetPasswordModal(userId, username) {
            const modalHtml = `
                <div class="modal fade" id="resetPasswordModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="bi bi-key"></i> Reset Password</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>Reset password for user: <strong>${username}</strong></p>
                                <div class="mb-3">
                                    <label class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="newPassword" minlength="6" required>
                                    <small class="text-muted">Minimum 6 characters</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" id="confirmPassword" minlength="6" required>
                                </div>
                                <div id="passwordError" class="text-danger"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" onclick="resetUserPassword(${userId})">
                                    <i class="bi bi-check"></i> Reset Password
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Remove existing modal
            const existingModal = document.getElementById('resetPasswordModal');
            if (existingModal) existingModal.remove();

            // Add and show new modal
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            const modal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
            modal.show();
        }

        /**
         * Reset user password
         */
        async function resetUserPassword(userId) {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const errorDiv = document.getElementById('passwordError');

            // Validate
            if (newPassword.length < 6) {
                errorDiv.textContent = 'Password must be at least 6 characters';
                return;
            }

            if (newPassword !== confirmPassword) {
                errorDiv.textContent = 'Passwords do not match';
                return;
            }

            showLoading(true);

            try {
                const response = await fetch(`${API_BASE_URL}/admin/users/${userId}/reset-password`, {
                    method: 'PUT',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ new_password: newPassword })
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    showAlert('Password reset successfully', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('resetPasswordModal')).hide();
                } else {
                    errorDiv.textContent = result.message || 'Failed to reset password';
                }

            } catch (error) {
                console.error('Reset password error:', error);
                errorDiv.textContent = 'Network error';
            } finally {
                showLoading(false);
            }
        }

        /**
         * Confirm and delete user
         */
        async function deleteUserConfirm(userId, username) {
            if (!confirm(`⚠️ WARNING: Are you sure you want to delete user "${username}"?\n\nThis will permanently delete:\n- User account\n- All related data\n- Cannot be undone\n\nType "DELETE" to confirm:`)) {
                return;
            }

            showLoading(true);

            try {
                const response = await fetch(`${API_BASE_URL}/admin/users/${userId}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    showAlert('User deleted successfully', 'success');
                    loadUsersPage(); // Reload users
                } else {
                    showAlert(result.message || 'Failed to delete user', 'danger');
                }

            } catch (error) {
                console.error('Delete user error:', error);
                showAlert('Network error', 'danger');
            } finally {
                showLoading(false);
            }
        }

        // ==================== STUDENTS MANAGEMENT PAGE ====================

        let studentsData = [];

        /**
         * Load Students Management page
         */
        async function loadStudentsPage() {
            showLoading(true);
            
            try {
                const response = await fetch(`${API_BASE_URL}/admin/students`, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });

                if (response.status === 401 || response.status === 403) {
                    showAlert('Unauthorized. Please login again.', 'danger');
                    setTimeout(() => window.location.href = '/capstone_project/login', 2000);
                    return;
                }

                const result = await response.json();

                if (response.ok && result.success) {
                    studentsData = result.data || [];
                    renderStudentsPage();
                    showAlert('Students loaded successfully', 'success');
                } else {
                    showAlert(result.message || 'Failed to load students', 'danger');
                }

            } catch (error) {
                console.error('Load students error:', error);
                showAlert('Network error. Unable to load students.', 'danger');
            } finally {
                showLoading(false);
            }
        }

        /**
         * Render Students page UI
         */
        function renderStudentsPage() {
            const contentArea = document.querySelector('.content-area');
            
            // Calculate statistics
            const totalStudents = studentsData.length;
            const activeStudents = studentsData.filter(s => s.account_status === 'Active').length;
            const withRegistrations = studentsData.filter(s => parseInt(s.total_registrations) > 0).length;
            const withApprovedTopics = studentsData.filter(s => parseInt(s.approved_registrations) > 0).length;
            
            contentArea.innerHTML = `
                <div id="alertContainer"></div>
                
                <!-- Stats Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <div>
                                    <div class="stat-card-value">${totalStudents}</div>
                                    <div class="stat-card-label">Total Students</div>
                                </div>
                                <div class="stat-card-icon bg-primary-gradient">
                                    <i class="bi bi-person-badge"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <div>
                                    <div class="stat-card-value">${activeStudents}</div>
                                    <div class="stat-card-label">Active Students</div>
                                </div>
                                <div class="stat-card-icon bg-success-gradient">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <div>
                                    <div class="stat-card-value">${withRegistrations}</div>
                                    <div class="stat-card-label">With Registrations</div>
                                </div>
                                <div class="stat-card-icon bg-info-gradient">
                                    <i class="bi bi-clipboard-check"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <div>
                                    <div class="stat-card-value">${withApprovedTopics}</div>
                                    <div class="stat-card-label">With Approved Topics</div>
                                </div>
                                <div class="stat-card-icon bg-success-gradient">
                                    <i class="bi bi-check2-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Students Table -->
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-person-badge"></i> Student List</h5>
                        <div class="d-flex gap-2">
                            <input type="text" id="studentSearchInput" class="form-control form-control-sm" 
                                   placeholder="Search students..." style="width: 250px;">
                            <select id="departmentFilter" class="form-select form-select-sm" style="width: 200px;">
                                <option value="">All Departments</option>
                                ${getUniqueDepartments().map(dept => `<option value="${dept}">${dept}</option>`).join('')}
                            </select>
                            <select id="studentStatusFilter" class="form-select form-select-sm" style="width: 150px;">
                                <option value="">All Status</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                            <button class="btn btn-primary btn-sm" onclick="exportData('students')">
                                <i class="bi bi-download"></i> Export CSV
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="studentsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Student Code</th>
                                        <th>Full Name</th>
                                        <th>Email</th>
                                        <th>Department</th>
                                        <th>Enrollment Year</th>
                                        <th>Status</th>
                                        <th>Registrations</th>
                                        <th>Current Topic</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="studentsTableBody">
                                    <!-- Students will be rendered here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;

            // Initialize filters
            document.getElementById('studentSearchInput').addEventListener('input', filterStudents);
            document.getElementById('departmentFilter').addEventListener('change', filterStudents);
            document.getElementById('studentStatusFilter').addEventListener('change', filterStudents);

            // Render students
            filterStudents();
        }

        /**
         * Get unique departments from students data
         */
        function getUniqueDepartments() {
            const departments = studentsData.map(s => s.department_name).filter(d => d);
            return [...new Set(departments)].sort();
        }

        /**
         * Filter and render students based on search and filters
         */
        function filterStudents() {
            const searchTerm = document.getElementById('studentSearchInput').value.toLowerCase();
            const departmentFilter = document.getElementById('departmentFilter').value;
            const statusFilter = document.getElementById('studentStatusFilter').value;

            let filteredStudents = studentsData.filter(student => {
                const matchSearch = !searchTerm || 
                    student.student_code.toLowerCase().includes(searchTerm) ||
                    student.full_name.toLowerCase().includes(searchTerm) ||
                    (student.email && student.email.toLowerCase().includes(searchTerm)) ||
                    (student.department_name && student.department_name.toLowerCase().includes(searchTerm));
                
                const matchDepartment = !departmentFilter || student.department_name === departmentFilter;
                const matchStatus = !statusFilter || student.account_status === statusFilter;

                return matchSearch && matchDepartment && matchStatus;
            });

            renderStudentsTable(filteredStudents);
        }

        /**
         * Render students table
         */
        function renderStudentsTable(students) {
            const tbody = document.getElementById('studentsTableBody');
            
            if (students.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="10" class="text-center py-4">
                            <i class="bi bi-inbox" style="font-size: 48px; color: #ccc;"></i>
                            <p class="text-muted mt-2">No students found</p>
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = students.map(student => {
                const statusBadge = student.account_status === 'Active' 
                    ? '<span class="badge bg-success">Active</span>' 
                    : '<span class="badge bg-secondary">Inactive</span>';

                const totalReg = parseInt(student.total_registrations) || 0;
                const approvedReg = parseInt(student.approved_registrations) || 0;
                const pendingReg = parseInt(student.pending_registrations) || 0;
                
                const registrationInfo = totalReg > 0 
                    ? `<small><span class="badge bg-success">${approvedReg} Approved</span> <span class="badge bg-warning">${pendingReg} Pending</span></small>`
                    : '<small class="text-muted">No registrations</small>';

                const currentTopic = student.current_topic 
                    ? `<small class="text-truncate d-inline-block" style="max-width: 200px;" title="${student.current_topic}">${student.current_topic}</small>`
                    : '<small class="text-muted">None</small>';

                return `
                    <tr>
                        <td>${student.id}</td>
                        <td><strong>${student.student_code}</strong></td>
                        <td>${student.full_name}</td>
                        <td><small>${student.email || 'N/A'}</small></td>
                        <td><small>${student.department_name}</small></td>
                        <td>${student.enrollment_year || 'N/A'}</td>
                        <td>${statusBadge}</td>
                        <td>${registrationInfo}</td>
                        <td>${currentTopic}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-info" onclick="viewStudentDetails(${student.id})" 
                                        title="View Details">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-primary" onclick="viewStudentUser(${student.user_id})" 
                                        title="View User Account">
                                    <i class="bi bi-person-circle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        /**
         * View student details
         */
        async function viewStudentDetails(studentId) {
            showLoading(true);
            
            try {
                const response = await fetch(`${API_BASE_URL}/admin/students/${studentId}`, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    showStudentDetailsModal(result.data);
                } else {
                    showAlert(result.message || 'Failed to load student details', 'danger');
                }

            } catch (error) {
                console.error('View student details error:', error);
                showAlert('Network error', 'danger');
            } finally {
                showLoading(false);
            }
        }

        /**
         * Show student details modal
         */
        function showStudentDetailsModal(student) {
            const registrationsList = student.registrations && student.registrations.length > 0 
                ? student.registrations.map(reg => `
                    <div class="border-bottom pb-2 mb-2">
                        <strong>${reg.topic_title}</strong><br>
                        <small>Lecturer: ${reg.lecturer_name} (${reg.lecturer_code})</small><br>
                        <small>Status: <span class="badge bg-${reg.status === 'Approved' ? 'success' : reg.status === 'Pending' ? 'warning' : 'secondary'}">${reg.status}</span></small><br>
                        <small>Registered: ${new Date(reg.registered_at).toLocaleDateString()}</small>
                    </div>
                `).join('')
                : '<p class="text-muted">No registrations</p>';

            const assignmentsList = student.assignments && student.assignments.length > 0 
                ? student.assignments.map(assign => `
                    <div class="border-bottom pb-2 mb-2">
                        <strong>${assign.topic_title}</strong><br>
                        <small>Lecturer: ${assign.lecturer_name}</small><br>
                        <small>Submissions: ${assign.submission_count}</small><br>
                        <small>Assigned: ${new Date(assign.assigned_at).toLocaleDateString()}</small>
                    </div>
                `).join('')
                : '<p class="text-muted">No assignments</p>';

            const modalHtml = `
                <div class="modal fade" id="studentDetailsModal" tabindex="-1">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title"><i class="bi bi-person-badge"></i> Student Details - ${student.full_name}</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="border-bottom pb-2 mb-3">Basic Information</h6>
                                        <div class="row mb-2">
                                            <div class="col-5"><strong>Student Code:</strong></div>
                                            <div class="col-7">${student.student_code}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-5"><strong>Full Name:</strong></div>
                                            <div class="col-7">${student.full_name}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-5"><strong>Email:</strong></div>
                                            <div class="col-7">${student.email || 'N/A'}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-5"><strong>Phone:</strong></div>
                                            <div class="col-7">${student.phone || 'N/A'}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-5"><strong>Department:</strong></div>
                                            <div class="col-7">${student.department_name} (${student.department_code})</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-5"><strong>Enrollment Year:</strong></div>
                                            <div class="col-7">${student.enrollment_year || 'N/A'}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-5"><strong>Account Status:</strong></div>
                                            <div class="col-7"><span class="badge bg-${student.account_status === 'Active' ? 'success' : 'secondary'}">${student.account_status}</span></div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-5"><strong>Username:</strong></div>
                                            <div class="col-7">${student.username}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-5"><strong>Created At:</strong></div>
                                            <div class="col-7">${new Date(student.created_at).toLocaleString()}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="border-bottom pb-2 mb-3">Registrations (${student.registrations ? student.registrations.length : 0})</h6>
                                        <div style="max-height: 300px; overflow-y: auto;">
                                            ${registrationsList}
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <h6 class="border-bottom pb-2 mb-3">Assignments (${student.assignments ? student.assignments.length : 0})</h6>
                                        <div style="max-height: 200px; overflow-y: auto;">
                                            ${assignmentsList}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary" onclick="viewStudentUser(${student.user_id})">
                                    <i class="bi bi-person-circle"></i> View User Account
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Remove existing modal
            const existingModal = document.getElementById('studentDetailsModal');
            if (existingModal) existingModal.remove();

            // Add and show new modal
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            const modal = new bootstrap.Modal(document.getElementById('studentDetailsModal'));
            modal.show();
        }

        /**
         * View student's user account (redirect to Users page with detail view)
         */
        async function viewStudentUser(userId) {
            // Close any open modal
            const studentModal = document.getElementById('studentDetailsModal');
            if (studentModal) {
                const modal = bootstrap.Modal.getInstance(studentModal);
                if (modal) modal.hide();
            }

            // Switch to users page and view user details
            navigateToPage('users');
            
            // Wait for page to load then show user details
            setTimeout(() => {
                viewUserDetails(userId);
            }, 500);
        }

        // ==================== LECTURERS MANAGEMENT PAGE ====================

        let lecturersData = [];

        /**
         * Load Lecturers Management page
         */
        async function loadLecturersPage() {
            showLoading(true);
            
            try {
                const response = await fetch(`${API_BASE_URL}/admin/lecturers`, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });

                if (response.status === 401 || response.status === 403) {
                    showAlert('Unauthorized. Please login again.', 'danger');
                    setTimeout(() => window.location.href = '/capstone_project/login', 2000);
                    return;
                }

                const result = await response.json();

                if (response.ok && result.success) {
                    lecturersData = result.data || [];
                    renderLecturersPage();
                    showAlert('Lecturers loaded successfully', 'success');
                } else {
                    showAlert(result.message || 'Failed to load lecturers', 'danger');
                }

            } catch (error) {
                console.error('Load lecturers error:', error);
                showAlert('Network error. Unable to load lecturers.', 'danger');
            } finally {
                showLoading(false);
            }
        }

        /**
         * Render Lecturers page UI
         */
        function renderLecturersPage() {
            const contentArea = document.querySelector('.content-area');
            
            // Calculate statistics
            const totalLecturers = lecturersData.length;
            const activeLecturers = lecturersData.filter(l => l.account_status === 'Active').length;
            const withTopics = lecturersData.filter(l => parseInt(l.total_topics) > 0).length;
            const withStudents = lecturersData.filter(l => parseInt(l.total_students) > 0).length;
            const totalTopics = lecturersData.reduce((sum, l) => sum + parseInt(l.total_topics || 0), 0);
            const totalStudents = lecturersData.reduce((sum, l) => sum + parseInt(l.total_students || 0), 0);
            
            contentArea.innerHTML = `
                <div id="alertContainer"></div>
                
                <!-- Stats Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <div>
                                    <div class="stat-card-value">${totalLecturers}</div>
                                    <div class="stat-card-label">Total Lecturers</div>
                                </div>
                                <div class="stat-card-icon bg-info-gradient">
                                    <i class="bi bi-person-workspace"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <div>
                                    <div class="stat-card-value">${activeLecturers}</div>
                                    <div class="stat-card-label">Active Lecturers</div>
                                </div>
                                <div class="stat-card-icon bg-success-gradient">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <div>
                                    <div class="stat-card-value">${totalTopics}</div>
                                    <div class="stat-card-label">Total Topics</div>
                                </div>
                                <div class="stat-card-icon bg-warning-gradient">
                                    <i class="bi bi-journal-text"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <div>
                                    <div class="stat-card-value">${totalStudents}</div>
                                    <div class="stat-card-label">Total Students Supervised</div>
                                </div>
                                <div class="stat-card-icon bg-primary-gradient">
                                    <i class="bi bi-people"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lecturers Table -->
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-person-workspace"></i> Lecturer List</h5>
                        <div class="d-flex gap-2">
                            <input type="text" id="lecturerSearchInput" class="form-control form-control-sm" 
                                   placeholder="Search lecturers..." style="width: 250px;">
                            <select id="lecturerDepartmentFilter" class="form-select form-select-sm" style="width: 200px;">
                                <option value="">All Departments</option>
                                ${getUniqueLecturerDepartments().map(dept => `<option value="${dept}">${dept}</option>`).join('')}
                            </select>
                            <select id="lecturerStatusFilter" class="form-select form-select-sm" style="width: 150px;">
                                <option value="">All Status</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="lecturersTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Lecturer Code</th>
                                        <th>Full Name</th>
                                        <th>Email</th>
                                        <th>Department</th>
                                        <th>Specialization</th>
                                        <th>Topics</th>
                                        <th>Students</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="lecturersTableBody">
                                    <!-- Lecturers will be rendered here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;

            // Initialize filters
            document.getElementById('lecturerSearchInput').addEventListener('input', filterLecturers);
            document.getElementById('lecturerDepartmentFilter').addEventListener('change', filterLecturers);
            document.getElementById('lecturerStatusFilter').addEventListener('change', filterLecturers);

            // Render lecturers
            filterLecturers();
        }

        /**
         * Get unique departments from lecturers data
         */
        function getUniqueLecturerDepartments() {
            const departments = lecturersData.map(l => l.department_name).filter(d => d);
            return [...new Set(departments)].sort();
        }

        /**
         * Filter and render lecturers based on search and filters
         */
        function filterLecturers() {
            const searchTerm = document.getElementById('lecturerSearchInput').value.toLowerCase();
            const departmentFilter = document.getElementById('lecturerDepartmentFilter').value;
            const statusFilter = document.getElementById('lecturerStatusFilter').value;

            let filteredLecturers = lecturersData.filter(lecturer => {
                const matchSearch = !searchTerm || 
                    lecturer.lecturer_code.toLowerCase().includes(searchTerm) ||
                    lecturer.full_name.toLowerCase().includes(searchTerm) ||
                    (lecturer.email && lecturer.email.toLowerCase().includes(searchTerm)) ||
                    (lecturer.specialization && lecturer.specialization.toLowerCase().includes(searchTerm)) ||
                    (lecturer.department_name && lecturer.department_name.toLowerCase().includes(searchTerm));
                
                const matchDepartment = !departmentFilter || lecturer.department_name === departmentFilter;
                const matchStatus = !statusFilter || lecturer.account_status === statusFilter;

                return matchSearch && matchDepartment && matchStatus;
            });

            renderLecturersTable(filteredLecturers);
        }

        /**
         * Render lecturers table
         */
        function renderLecturersTable(lecturers) {
            const tbody = document.getElementById('lecturersTableBody');
            
            if (lecturers.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="10" class="text-center py-4">
                            <i class="bi bi-inbox" style="font-size: 48px; color: #ccc;"></i>
                            <p class="text-muted mt-2">No lecturers found</p>
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = lecturers.map(lecturer => {
                const statusBadge = lecturer.account_status === 'Active' 
                    ? '<span class="badge bg-success">Active</span>' 
                    : '<span class="badge bg-secondary">Inactive</span>';

                const totalTopics = parseInt(lecturer.total_topics) || 0;
                const publishedTopics = parseInt(lecturer.published_topics) || 0;
                const draftTopics = parseInt(lecturer.draft_topics) || 0;
                const pendingApprovals = parseInt(lecturer.pending_approvals) || 0;
                
                const topicsInfo = totalTopics > 0 
                    ? `<small><span class="badge bg-success">${publishedTopics} Published</span> <span class="badge bg-secondary">${draftTopics} Draft</span></small>`
                    : '<small class="text-muted">No topics</small>';

                const studentsInfo = `<small><strong>${lecturer.total_students || 0}</strong> students${pendingApprovals > 0 ? ` <span class="badge bg-warning">${pendingApprovals} pending</span>` : ''}</small>`;

                return `
                    <tr>
                        <td>${lecturer.id}</td>
                        <td><strong>${lecturer.lecturer_code}</strong></td>
                        <td>${lecturer.full_name}</td>
                        <td><small>${lecturer.email || 'N/A'}</small></td>
                        <td><small>${lecturer.department_name}</small></td>
                        <td><small>${lecturer.specialization || 'N/A'}</small></td>
                        <td>${topicsInfo}</td>
                        <td>${studentsInfo}</td>
                        <td>${statusBadge}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-info" onclick="viewLecturerDetails(${lecturer.id})" 
                                        title="View Details">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-primary" onclick="viewLecturerUser(${lecturer.user_id})" 
                                        title="View User Account">
                                    <i class="bi bi-person-circle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        /**
         * View lecturer details
         */
        async function viewLecturerDetails(lecturerId) {
            showLoading(true);
            
            try {
                const response = await fetch(`${API_BASE_URL}/admin/lecturers/${lecturerId}`, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    showLecturerDetailsModal(result.data);
                } else {
                    showAlert(result.message || 'Failed to load lecturer details', 'danger');
                }

            } catch (error) {
                console.error('View lecturer details error:', error);
                showAlert('Network error', 'danger');
            } finally {
                showLoading(false);
            }
        }

        /**
         * Show lecturer details modal
         */
        function showLecturerDetailsModal(lecturer) {
            const topicsList = lecturer.topics && lecturer.topics.length > 0 
                ? lecturer.topics.map(topic => `
                    <div class="border-bottom pb-2 mb-2">
                        <strong>${topic.title}</strong><br>
                        <small>Status: <span class="badge bg-${topic.status === 'Published' ? 'success' : 'secondary'}">${topic.status}</span></small><br>
                        <small>Registrations: <span class="badge bg-info">${topic.registration_count}</span> 
                        (${topic.approved_count} approved, ${topic.pending_count} pending)</small><br>
                        <small>Max Students: ${topic.max_students || 'N/A'}</small><br>
                        <small>Created: ${new Date(topic.created_at).toLocaleDateString()}</small>
                    </div>
                `).join('')
                : '<p class="text-muted">No topics</p>';

            const studentsList = lecturer.students && lecturer.students.length > 0 
                ? lecturer.students.map(student => `
                    <div class="border-bottom pb-2 mb-2">
                        <strong>${student.student_name}</strong> (${student.student_code})<br>
                        <small>Topic: ${student.topic_title}</small><br>
                        <small>Department: ${student.department_name}</small><br>
                        <small>Registered: ${new Date(student.registered_at).toLocaleDateString()}</small>
                    </div>
                `).join('')
                : '<p class="text-muted">No students</p>';

            const modalHtml = `
                <div class="modal fade" id="lecturerDetailsModal" tabindex="-1">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header bg-info text-white">
                                <h5 class="modal-title"><i class="bi bi-person-workspace"></i> Lecturer Details - ${lecturer.full_name}</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="border-bottom pb-2 mb-3">Basic Information</h6>
                                        <div class="row mb-2">
                                            <div class="col-5"><strong>Lecturer Code:</strong></div>
                                            <div class="col-7">${lecturer.lecturer_code}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-5"><strong>Full Name:</strong></div>
                                            <div class="col-7">${lecturer.full_name}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-5"><strong>Email:</strong></div>
                                            <div class="col-7">${lecturer.email || 'N/A'}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-5"><strong>Phone:</strong></div>
                                            <div class="col-7">${lecturer.phone || 'N/A'}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-5"><strong>Department:</strong></div>
                                            <div class="col-7">${lecturer.department_name} (${lecturer.department_code})</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-5"><strong>Specialization:</strong></div>
                                            <div class="col-7">${lecturer.specialization || 'N/A'}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-5"><strong>Max Quota:</strong></div>
                                            <div class="col-7">${lecturer.max_quota || 'N/A'}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-5"><strong>Account Status:</strong></div>
                                            <div class="col-7"><span class="badge bg-${lecturer.account_status === 'Active' ? 'success' : 'secondary'}">${lecturer.account_status}</span></div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-5"><strong>Username:</strong></div>
                                            <div class="col-7">${lecturer.username}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-5"><strong>Created At:</strong></div>
                                            <div class="col-7">${new Date(lecturer.created_at).toLocaleString()}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="border-bottom pb-2 mb-3">Students (${lecturer.students ? lecturer.students.length : 0})</h6>
                                        <div style="max-height: 300px; overflow-y: auto;">
                                            ${studentsList}
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <h6 class="border-bottom pb-2 mb-3">Topics (${lecturer.topics ? lecturer.topics.length : 0})</h6>
                                        <div style="max-height: 250px; overflow-y: auto;">
                                            ${topicsList}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary" onclick="viewLecturerUser(${lecturer.user_id})">
                                    <i class="bi bi-person-circle"></i> View User Account
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Remove existing modal
            const existingModal = document.getElementById('lecturerDetailsModal');
            if (existingModal) existingModal.remove();

            // Add and show new modal
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            const modal = new bootstrap.Modal(document.getElementById('lecturerDetailsModal'));
            modal.show();
        }

        /**
         * View lecturer's user account (redirect to Users page with detail view)
         */
        async function viewLecturerUser(userId) {
            // Close any open modal
            const lecturerModal = document.getElementById('lecturerDetailsModal');
            if (lecturerModal) {
                const modal = bootstrap.Modal.getInstance(lecturerModal);
                if (modal) modal.hide();
            }

            // Switch to users page and view user details
            navigateToPage('users');
            
            // Wait for page to load then show user details
            setTimeout(() => {
                viewUserDetails(userId);
            }, 500);
        }

        // ==================== TOPICS MANAGEMENT PAGE ====================

        let topicsData = [];

        /**
         * Load Topics Management page
         */
        async function loadTopicsPage() {
            showLoading(true);
            
            try {
                const response = await fetch(`${API_BASE_URL}/admin/topics`, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });

                if (response.status === 401 || response.status === 403) {
                    showAlert('Unauthorized. Please login again.', 'danger');
                    setTimeout(() => window.location.href = '/capstone_project/login', 2000);
                    return;
                }

                const result = await response.json();

                if (response.ok && result.success) {
                    topicsData = result.data || [];
                    renderTopicsPage();
                    showAlert('Topics loaded successfully', 'success');
                } else {
                    showAlert(result.message || 'Failed to load topics', 'danger');
                }

            } catch (error) {
                console.error('Load topics error:', error);
                showAlert('Network error. Unable to load topics.', 'danger');
            } finally {
                showLoading(false);
            }
        }

        /**
         * Render Topics page UI
         */
        function renderTopicsPage() {
            const contentArea = document.querySelector('.content-area');
            
            // Calculate statistics
            const totalTopics = topicsData.length;
            const publishedTopics = topicsData.filter(t => t.status === 'Published').length;
            const draftTopics = topicsData.filter(t => t.status === 'Draft').length;
            const archivedTopics = topicsData.filter(t => t.status === 'Archived').length;
            const totalRegistrations = topicsData.reduce((sum, t) => sum + parseInt(t.total_registrations || 0), 0);
            const approvedRegistrations = topicsData.reduce((sum, t) => sum + parseInt(t.approved_registrations || 0), 0);
            
            contentArea.innerHTML = `
                <div id="alertContainer"></div>
                
                <!-- Stats Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <div>
                                    <div class="stat-card-value">${totalTopics}</div>
                                    <div class="stat-card-label">Total Topics</div>
                                </div>
                                <div class="stat-card-icon bg-warning-gradient">
                                    <i class="bi bi-journal-text"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <div>
                                    <div class="stat-card-value">${publishedTopics}</div>
                                    <div class="stat-card-label">Published Topics</div>
                                </div>
                                <div class="stat-card-icon bg-success-gradient">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <div>
                                    <div class="stat-card-value">${totalRegistrations}</div>
                                    <div class="stat-card-label">Total Registrations</div>
                                </div>
                                <div class="stat-card-icon bg-info-gradient">
                                    <i class="bi bi-clipboard-check"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <div>
                                    <div class="stat-card-value">${approvedRegistrations}</div>
                                    <div class="stat-card-label">Approved Registrations</div>
                                </div>
                                <div class="stat-card-icon bg-success-gradient">
                                    <i class="bi bi-check2-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Topics Table -->
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-journal-text"></i> Topic List</h5>
                        <div class="d-flex gap-2">
                            <input type="text" id="topicSearchInput" class="form-control form-control-sm" 
                                   placeholder="Search topics..." style="width: 250px;">
                            <select id="topicDepartmentFilter" class="form-select form-select-sm" style="width: 200px;">
                                <option value="">All Departments</option>
                                ${getUniqueTopicDepartments().map(dept => `<option value="${dept}">${dept}</option>`).join('')}
                            </select>
                            <select id="topicStatusFilter" class="form-select form-select-sm" style="width: 150px;">
                                <option value="">All Status</option>
                                <option value="Published">Published</option>
                                <option value="Draft">Draft</option>
                                <option value="Archived">Archived</option>
                            </select>
                            <button class="btn btn-primary btn-sm" onclick="exportData('topics')">
                                <i class="bi bi-download"></i> Export CSV
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="topicsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Lecturer</th>
                                        <th>Department</th>
                                        <th>Status</th>
                                        <th>Max Students</th>
                                        <th>Registrations</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="topicsTableBody">
                                    <!-- Topics will be rendered here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;

            // Initialize filters
            document.getElementById('topicSearchInput').addEventListener('input', filterTopics);
            document.getElementById('topicDepartmentFilter').addEventListener('change', filterTopics);
            document.getElementById('topicStatusFilter').addEventListener('change', filterTopics);

            // Render topics
            filterTopics();
        }

        /**
         * Get unique departments from topics data
         */
        function getUniqueTopicDepartments() {
            const departments = topicsData.map(t => t.department_name).filter(d => d);
            return [...new Set(departments)].sort();
        }

        /**
         * Filter and render topics based on search and filters
         */
        function filterTopics() {
            const searchTerm = document.getElementById('topicSearchInput').value.toLowerCase();
            const departmentFilter = document.getElementById('topicDepartmentFilter').value;
            const statusFilter = document.getElementById('topicStatusFilter').value;

            let filteredTopics = topicsData.filter(topic => {
                const matchSearch = !searchTerm || 
                    topic.title.toLowerCase().includes(searchTerm) ||
                    (topic.description && topic.description.toLowerCase().includes(searchTerm)) ||
                    (topic.lecturer_name && topic.lecturer_name.toLowerCase().includes(searchTerm)) ||
                    (topic.tags && topic.tags.toLowerCase().includes(searchTerm)) ||
                    (topic.department_name && topic.department_name.toLowerCase().includes(searchTerm));
                
                const matchDepartment = !departmentFilter || topic.department_name === departmentFilter;
                const matchStatus = !statusFilter || topic.status === statusFilter;

                return matchSearch && matchDepartment && matchStatus;
            });

            renderTopicsTable(filteredTopics);
        }

        /**
         * Render topics table
         */
        function renderTopicsTable(topics) {
            const tbody = document.getElementById('topicsTableBody');
            
            if (topics.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <i class="bi bi-inbox" style="font-size: 48px; color: #ccc;"></i>
                            <p class="text-muted mt-2">No topics found</p>
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = topics.map(topic => {
                const statusBadge = {
                    'Published': '<span class="badge bg-success">Published</span>',
                    'Draft': '<span class="badge bg-secondary">Draft</span>',
                    'Archived': '<span class="badge bg-warning">Archived</span>'
                }[topic.status] || '<span class="badge bg-secondary">Unknown</span>';

                const totalReg = parseInt(topic.total_registrations) || 0;
                const approvedReg = parseInt(topic.approved_registrations) || 0;
                const pendingReg = parseInt(topic.pending_registrations) || 0;
                const rejectedReg = parseInt(topic.rejected_registrations) || 0;
                
                const registrationInfo = totalReg > 0 
                    ? `<small>
                        <span class="badge bg-success" title="Approved">${approvedReg}</span> 
                        <span class="badge bg-warning" title="Pending">${pendingReg}</span>
                        ${rejectedReg > 0 ? `<span class="badge bg-danger" title="Rejected">${rejectedReg}</span>` : ''}
                    </small>`
                    : '<small class="text-muted">No registrations</small>';

                const titleTruncated = topic.title.length > 50 
                    ? `<span title="${topic.title}">${topic.title.substring(0, 50)}...</span>`
                    : topic.title;

                return `
                    <tr>
                        <td>${topic.id}</td>
                        <td><strong>${titleTruncated}</strong></td>
                        <td><small>${topic.lecturer_name}<br>${topic.lecturer_code}</small></td>
                        <td><small>${topic.department_name}</small></td>
                        <td>${statusBadge}</td>
                        <td>${topic.max_students || 'N/A'}</td>
                        <td>${registrationInfo}</td>
                        <td><small>${new Date(topic.created_at).toLocaleDateString()}</small></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-info" onclick="viewTopicDetails(${topic.id})" 
                                        title="View Details">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-warning" onclick="showChangeTopicStatusModal(${topic.id}, '${topic.status}')" 
                                        title="Change Status">
                                    <i class="bi bi-arrow-repeat"></i>
                                </button>
                                <button class="btn btn-outline-danger" onclick="deleteTopicConfirm(${topic.id}, '${topic.title.replace(/'/g, "\\'")}', ${approvedReg})" 
                                        title="Delete Topic">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        /**
         * View topic details
         */
        async function viewTopicDetails(topicId) {
            showLoading(true);
            
            try {
                const response = await fetch(`${API_BASE_URL}/admin/topics/${topicId}`, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    showTopicDetailsModal(result.data);
                } else {
                    showAlert(result.message || 'Failed to load topic details', 'danger');
                }

            } catch (error) {
                console.error('View topic details error:', error);
                showAlert('Network error', 'danger');
            } finally {
                showLoading(false);
            }
        }

        /**
         * Show topic details modal
         */
        function showTopicDetailsModal(topic) {
            const registrationsList = topic.registrations && topic.registrations.length > 0 
                ? topic.registrations.map(reg => `
                    <div class="border-bottom pb-2 mb-2">
                        <strong>${reg.student_name}</strong> (${reg.student_code})<br>
                        <small>Department: ${reg.student_department}</small><br>
                        <small>Email: ${reg.student_email || 'N/A'}</small><br>
                        <small>Status: <span class="badge bg-${reg.status === 'Approved' ? 'success' : reg.status === 'Pending' ? 'warning' : 'danger'}">${reg.status}</span></small><br>
                        <small>Registered: ${new Date(reg.registered_at).toLocaleDateString()}</small>
                    </div>
                `).join('')
                : '<p class="text-muted">No registrations</p>';

            const assignmentsList = topic.assignments && topic.assignments.length > 0 
                ? topic.assignments.map(assign => `
                    <div class="border-bottom pb-2 mb-2">
                        <strong>${assign.student_name}</strong> (${assign.student_code})<br>
                        <small>Submissions: ${assign.submission_count}</small><br>
                        <small>Assigned: ${new Date(assign.assigned_at).toLocaleDateString()}</small>
                    </div>
                `).join('')
                : '<p class="text-muted">No assignments</p>';

            const modalHtml = `
                <div class="modal fade" id="topicDetailsModal" tabindex="-1">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header bg-warning text-white">
                                <h5 class="modal-title"><i class="bi bi-journal-text"></i> Topic Details - ${topic.title}</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="border-bottom pb-2 mb-3">Topic Information</h6>
                                        <div class="row mb-2">
                                            <div class="col-4"><strong>ID:</strong></div>
                                            <div class="col-8">${topic.id}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-4"><strong>Title:</strong></div>
                                            <div class="col-8">${topic.title}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-12"><strong>Description:</strong></div>
                                            <div class="col-12"><small>${topic.description || 'N/A'}</small></div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-4"><strong>Status:</strong></div>
                                            <div class="col-8"><span class="badge bg-${topic.status === 'Published' ? 'success' : topic.status === 'Draft' ? 'secondary' : 'warning'}">${topic.status}</span></div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-4"><strong>Department:</strong></div>
                                            <div class="col-8">${topic.department_name} (${topic.department_code})</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-4"><strong>Max Students:</strong></div>
                                            <div class="col-8">${topic.max_students || 'N/A'}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-4"><strong>Tags:</strong></div>
                                            <div class="col-8">${topic.tags || 'None'}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-4"><strong>Created:</strong></div>
                                            <div class="col-8">${new Date(topic.created_at).toLocaleString()}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-4"><strong>Updated:</strong></div>
                                            <div class="col-8">${topic.updated_at ? new Date(topic.updated_at).toLocaleString() : 'N/A'}</div>
                                        </div>
                                        <h6 class="border-bottom pb-2 mb-3 mt-3">Lecturer Information</h6>
                                        <div class="row mb-2">
                                            <div class="col-4"><strong>Name:</strong></div>
                                            <div class="col-8">${topic.lecturer_name}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-4"><strong>Code:</strong></div>
                                            <div class="col-8">${topic.lecturer_code}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-4"><strong>Email:</strong></div>
                                            <div class="col-8">${topic.lecturer_email || 'N/A'}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="border-bottom pb-2 mb-3">Registrations (${topic.registrations ? topic.registrations.length : 0})</h6>
                                        <div style="max-height: 300px; overflow-y: auto;">
                                            ${registrationsList}
                                        </div>
                                        <h6 class="border-bottom pb-2 mb-3 mt-3">Assignments (${topic.assignments ? topic.assignments.length : 0})</h6>
                                        <div style="max-height: 200px; overflow-y: auto;">
                                            ${assignmentsList}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-warning" onclick="closeModalAndChangeStatus(${topic.id}, '${topic.status}')">
                                    <i class="bi bi-arrow-repeat"></i> Change Status
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Remove existing modal
            const existingModal = document.getElementById('topicDetailsModal');
            if (existingModal) existingModal.remove();

            // Add and show new modal
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            const modal = new bootstrap.Modal(document.getElementById('topicDetailsModal'));
            modal.show();
        }

        /**
         * Close topic details modal and show change status modal
         */
        function closeModalAndChangeStatus(topicId, currentStatus) {
            const detailsModal = document.getElementById('topicDetailsModal');
            if (detailsModal) {
                const modal = bootstrap.Modal.getInstance(detailsModal);
                if (modal) modal.hide();
            }
            
            setTimeout(() => {
                showChangeTopicStatusModal(topicId, currentStatus);
            }, 300);
        }

        /**
         * Show change topic status modal
         */
        function showChangeTopicStatusModal(topicId, currentStatus) {
            const statuses = ['Published', 'Draft', 'Archived'];
            const statusOptions = statuses.filter(s => s !== currentStatus)
                .map(s => `<option value="${s}">${s}</option>`).join('');

            const modalHtml = `
                <div class="modal fade" id="changeTopicStatusModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="bi bi-arrow-repeat"></i> Change Topic Status</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>Current status: <span class="badge bg-info">${currentStatus}</span></p>
                                <div class="mb-3">
                                    <label for="newTopicStatus" class="form-label">New Status:</label>
                                    <select class="form-select" id="newTopicStatus">
                                        ${statusOptions}
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" onclick="changeTopicStatus(${topicId})">
                                    <i class="bi bi-check"></i> Update Status
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            const existingModal = document.getElementById('changeTopicStatusModal');
            if (existingModal) existingModal.remove();

            document.body.insertAdjacentHTML('beforeend', modalHtml);
            const modal = new bootstrap.Modal(document.getElementById('changeTopicStatusModal'));
            modal.show();
        }

        /**
         * Change topic status
         */
        async function changeTopicStatus(topicId) {
            const newStatus = document.getElementById('newTopicStatus').value;
            
            showLoading(true);

            try {
                const response = await fetch(`${API_BASE_URL}/admin/topics/${topicId}/status`, {
                    method: 'PUT',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ status: newStatus })
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    showAlert(`Topic status updated to ${newStatus}`, 'success');
                    
                    // Close modal
                    const modal = document.getElementById('changeTopicStatusModal');
                    if (modal) {
                        const bsModal = bootstrap.Modal.getInstance(modal);
                        if (bsModal) bsModal.hide();
                    }
                    
                    // Reload topics
                    loadTopicsPage();
                } else {
                    showAlert(result.message || 'Failed to update status', 'danger');
                }

            } catch (error) {
                console.error('Change topic status error:', error);
                showAlert('Network error', 'danger');
            } finally {
                showLoading(false);
            }
        }

        /**
         * Delete topic confirmation
         */
        function deleteTopicConfirm(topicId, topicTitle, approvedCount) {
            if (approvedCount > 0) {
                showAlert('Cannot delete topic with approved registrations', 'danger');
                return;
            }

            if (!confirm(`⚠️ WARNING: Are you sure you want to delete topic "${topicTitle}"?\n\nThis will permanently delete:\n- The topic\n- All registrations\n- All assignments\n- All submissions\n- Cannot be undone`)) {
                return;
            }

            deleteTopic(topicId);
        }

        /**
         * Delete topic
         */
        async function deleteTopic(topicId) {
            showLoading(true);

            try {
                const response = await fetch(`${API_BASE_URL}/admin/topics/${topicId}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    showAlert('Topic deleted successfully', 'success');
                    loadTopicsPage(); // Reload topics
                } else {
                    showAlert(result.message || 'Failed to delete topic', 'danger');
                }

            } catch (error) {
                console.error('Delete topic error:', error);
                showAlert('Network error', 'danger');
            } finally {
                showLoading(false);
            }
        }

        // ==================== REGISTRATIONS MANAGEMENT PAGE ====================

        let registrationsData = [];

        /**
         * Load Registrations Management page
         */
        async function loadRegistrationsPage() {
            showLoading(true);
            
            try {
                const response = await fetch(`${API_BASE_URL}/admin/registrations`, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });

                if (response.status === 401 || response.status === 403) {
                    showAlert('Unauthorized. Please login again.', 'danger');
                    setTimeout(() => window.location.href = '/capstone_project/login', 2000);
                    return;
                }

                const result = await response.json();

                if (response.ok && result.success) {
                    registrationsData = result.data || [];
                    renderRegistrationsPage();
                    showAlert('Registrations loaded successfully', 'success');
                } else {
                    showAlert(result.message || 'Failed to load registrations', 'danger');
                }

            } catch (error) {
                console.error('Load registrations error:', error);
                showAlert('Network error. Unable to load registrations.', 'danger');
            } finally {
                showLoading(false);
            }
        }

        /**
         * Render Registrations page UI
         */
        function renderRegistrationsPage() {
            const contentArea = document.querySelector('.content-area');
            
            const pendingCount = registrationsData.filter(r => r.status === 'Pending').length;
            const approvedCount = registrationsData.filter(r => r.status === 'Approved').length;
            const rejectedCount = registrationsData.filter(r => r.status === 'Rejected').length;
            const withdrawnCount = registrationsData.filter(r => r.status === 'Withdrawn').length;

            contentArea.innerHTML = `
                <div id="alertContainer"></div>
                
                <!-- Stats Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <div>
                                    <div class="stat-card-value">${registrationsData.length}</div>
                                    <div class="stat-card-label">Total Registrations</div>
                                </div>
                                <div class="stat-card-icon bg-primary-gradient">
                                    <i class="bi bi-clipboard-check"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <div>
                                    <div class="stat-card-value">${pendingCount}</div>
                                    <div class="stat-card-label">Pending</div>
                                </div>
                                <div class="stat-card-icon bg-warning-gradient">
                                    <i class="bi bi-clock-history"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <div>
                                    <div class="stat-card-value">${approvedCount}</div>
                                    <div class="stat-card-label">Approved</div>
                                </div>
                                <div class="stat-card-icon bg-success-gradient">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <div>
                                    <div class="stat-card-value">${rejectedCount}</div>
                                    <div class="stat-card-label">Rejected</div>
                                </div>
                                <div class="stat-card-icon bg-danger-gradient">
                                    <i class="bi bi-x-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Registrations Table -->
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> Registration List</h5>
                        <div class="d-flex gap-2">
                            <input type="text" id="registrationSearchInput" class="form-control form-control-sm" 
                                   placeholder="Search registrations..." style="width: 250px;">
                            <select id="regStatusFilter" class="form-select form-select-sm" style="width: 150px;">
                                <option value="">All Status</option>
                                <option value="Pending">Pending</option>
                                <option value="Approved">Approved</option>
                                <option value="Rejected">Rejected</option>
                                <option value="Withdrawn">Withdrawn</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="registrationsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Student</th>
                                        <th>Topic</th>
                                        <th>Lecturer</th>
                                        <th>Department</th>
                                        <th>Status</th>
                                        <th>Registered</th>
                                        <th>Reviewed</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="registrationsTableBody">
                                    <!-- Registrations will be rendered here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Registration Details Modal -->
                <div class="modal fade" id="registrationDetailsModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="bi bi-info-circle"></i> Registration Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body" id="registrationDetailsBody">
                                <!-- Details will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Change Status Modal -->
                <div class="modal fade" id="changeStatusModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Change Registration Status</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" id="statusRegistrationId">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" id="newRegistrationStatus">
                                        <option value="Pending">Pending</option>
                                        <option value="Approved">Approved</option>
                                        <option value="Rejected">Rejected</option>
                                        <option value="Withdrawn">Withdrawn</option>
                                    </select>
                                </div>
                                <div class="mb-3" id="rejectionReasonGroup" style="display:none;">
                                    <label class="form-label">Rejection Reason</label>
                                    <textarea class="form-control" id="rejectionReason" rows="3" 
                                              placeholder="Enter reason for rejection..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" onclick="saveRegistrationStatus()">
                                    <i class="bi bi-check-circle"></i> Update Status
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Initialize filters
            document.getElementById('registrationSearchInput').addEventListener('input', filterRegistrations);
            document.getElementById('regStatusFilter').addEventListener('change', filterRegistrations);
            
            // Show/hide rejection reason field
            document.getElementById('newRegistrationStatus').addEventListener('change', function() {
                const reasonGroup = document.getElementById('rejectionReasonGroup');
                if (this.value === 'Rejected') {
                    reasonGroup.style.display = 'block';
                } else {
                    reasonGroup.style.display = 'none';
                }
            });

            // Render registrations
            filterRegistrations();
        }

        /**
         * Filter and render registrations
         */
        function filterRegistrations() {
            const searchTerm = document.getElementById('registrationSearchInput').value.toLowerCase();
            const statusFilter = document.getElementById('regStatusFilter').value;

            let filteredRegistrations = registrationsData.filter(reg => {
                const matchSearch = !searchTerm || 
                    reg.student_name.toLowerCase().includes(searchTerm) ||
                    reg.student_code.toLowerCase().includes(searchTerm) ||
                    reg.topic_title.toLowerCase().includes(searchTerm) ||
                    reg.lecturer_name.toLowerCase().includes(searchTerm);
                
                const matchStatus = !statusFilter || reg.status === statusFilter;

                return matchSearch && matchStatus;
            });

            renderRegistrationsTable(filteredRegistrations);
        }

        /**
         * Render registrations table
         */
        function renderRegistrationsTable(registrations) {
            const tbody = document.getElementById('registrationsTableBody');
            
            if (registrations.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox" style="font-size: 48px;"></i>
                            <p class="mt-2">No registrations found</p>
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = registrations.map(reg => {
                const statusBadge = getStatusBadge(reg.status);
                const registeredDate = new Date(reg.registered_at).toLocaleDateString('en-GB');
                const reviewedDate = reg.reviewed_at ? new Date(reg.reviewed_at).toLocaleDateString('en-GB') : '-';

                return `
                    <tr>
                        <td>${reg.id}</td>
                        <td>
                            <div><strong>${reg.student_name}</strong></div>
                            <small class="text-muted">${reg.student_code}</small>
                        </td>
                        <td>
                            <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" 
                                 title="${reg.topic_title}">${reg.topic_title}</div>
                        </td>
                        <td>
                            <div>${reg.lecturer_name}</div>
                            <small class="text-muted">${reg.lecturer_code}</small>
                        </td>
                        <td>${reg.department_name}</td>
                        <td>${statusBadge}</td>
                        <td><small>${registeredDate}</small></td>
                        <td><small>${reviewedDate}</small></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-info" onclick="viewRegistrationDetails(${reg.id})" 
                                        title="View Details">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-warning" onclick="openChangeStatusModal(${reg.id})" 
                                        title="Change Status">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-danger" onclick="deleteRegistration(${reg.id})" 
                                        title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        /**
         * Get status badge HTML
         */
        function getStatusBadge(status) {
            const badges = {
                'Pending': '<span class="badge bg-warning text-dark">Pending</span>',
                'Approved': '<span class="badge bg-success">Approved</span>',
                'Rejected': '<span class="badge bg-danger">Rejected</span>',
                'Withdrawn': '<span class="badge bg-secondary">Withdrawn</span>'
            };
            return badges[status] || `<span class="badge bg-secondary">${status}</span>`;
        }

        /**
         * View registration details
         */
        async function viewRegistrationDetails(registrationId) {
            showLoading(true);
            
            try {
                const response = await fetch(`${API_BASE_URL}/admin/registrations/${registrationId}`, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    const reg = result.data;
                    const registeredDate = new Date(reg.registered_at).toLocaleString('en-GB');
                    const reviewedDate = reg.reviewed_at ? new Date(reg.reviewed_at).toLocaleString('en-GB') : 'Not reviewed yet';

                    document.getElementById('registrationDetailsBody').innerHTML = `
                        <div class="row g-3">
                            <div class="col-md-6">
                                <h6 class="text-primary"><i class="bi bi-person"></i> Student Information</h6>
                                <table class="table table-sm">
                                    <tr><td><strong>Name:</strong></td><td>${reg.student_name}</td></tr>
                                    <tr><td><strong>Code:</strong></td><td>${reg.student_code}</td></tr>
                                    <tr><td><strong>Email:</strong></td><td>${reg.student_email || 'N/A'}</td></tr>
                                    <tr><td><strong>Phone:</strong></td><td>${reg.student_phone || 'N/A'}</td></tr>
                                    <tr><td><strong>Department:</strong></td><td>${reg.department_name}</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-success"><i class="bi bi-person-workspace"></i> Lecturer Information</h6>
                                <table class="table table-sm">
                                    <tr><td><strong>Name:</strong></td><td>${reg.lecturer_name}</td></tr>
                                    <tr><td><strong>Code:</strong></td><td>${reg.lecturer_code}</td></tr>
                                    <tr><td><strong>Email:</strong></td><td>${reg.lecturer_email || 'N/A'}</td></tr>
                                </table>
                            </div>
                            <div class="col-12">
                                <h6 class="text-info"><i class="bi bi-journal-text"></i> Topic Information</h6>
                                <table class="table table-sm">
                                    <tr><td style="width:150px;"><strong>Title:</strong></td><td>${reg.title}</td></tr>
                                    <tr><td><strong>Description:</strong></td><td>${reg.description || 'N/A'}</td></tr>
                                    <tr><td><strong>Topic Status:</strong></td><td>${getStatusBadge(reg.topic_status)}</td></tr>
                                </table>
                            </div>
                            <div class="col-12">
                                <h6 class="text-warning"><i class="bi bi-clipboard-check"></i> Registration Information</h6>
                                <table class="table table-sm">
                                    <tr><td style="width:150px;"><strong>Status:</strong></td><td>${getStatusBadge(reg.status)}</td></tr>
                                    <tr><td><strong>Registered At:</strong></td><td>${registeredDate}</td></tr>
                                    <tr><td><strong>Reviewed At:</strong></td><td>${reviewedDate}</td></tr>
                                    ${reg.rejection_reason ? `<tr><td><strong>Rejection Reason:</strong></td><td class="text-danger">${reg.rejection_reason}</td></tr>` : ''}
                                </table>
                            </div>
                        </div>
                    `;

                    const modal = new bootstrap.Modal(document.getElementById('registrationDetailsModal'));
                    modal.show();
                } else {
                    showAlert(result.message || 'Failed to load registration details', 'danger');
                }

            } catch (error) {
                console.error('Load registration details error:', error);
                showAlert('Network error', 'danger');
            } finally {
                showLoading(false);
            }
        }

        /**
         * Open change status modal
         */
        function openChangeStatusModal(registrationId) {
            const registration = registrationsData.find(r => r.id === registrationId);
            if (!registration) return;

            document.getElementById('statusRegistrationId').value = registrationId;
            document.getElementById('newRegistrationStatus').value = registration.status;
            document.getElementById('rejectionReason').value = registration.rejection_reason || '';
            
            // Show/hide rejection reason
            const reasonGroup = document.getElementById('rejectionReasonGroup');
            reasonGroup.style.display = registration.status === 'Rejected' ? 'block' : 'none';

            const modal = new bootstrap.Modal(document.getElementById('changeStatusModal'));
            modal.show();
        }

        /**
         * Save registration status
         */
        async function saveRegistrationStatus() {
            const registrationId = document.getElementById('statusRegistrationId').value;
            const newStatus = document.getElementById('newRegistrationStatus').value;
            const rejectionReason = document.getElementById('rejectionReason').value;

            if (!newStatus) {
                showAlert('Please select a status', 'warning');
                return;
            }

            if (newStatus === 'Rejected' && !rejectionReason.trim()) {
                showAlert('Please provide a rejection reason', 'warning');
                return;
            }

            showLoading(true);

            try {
                const response = await fetch(`${API_BASE_URL}/admin/registrations/${registrationId}/status`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        status: newStatus,
                        rejection_reason: newStatus === 'Rejected' ? rejectionReason : null
                    })
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    showAlert('Registration status updated successfully', 'success');
                    
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('changeStatusModal'));
                    modal.hide();
                    
                    // Reload registrations
                    loadRegistrationsPage();
                } else {
                    showAlert(result.message || 'Failed to update status', 'danger');
                }

            } catch (error) {
                console.error('Update status error:', error);
                showAlert('Network error', 'danger');
            } finally {
                showLoading(false);
            }
        }

        /**
         * Delete registration
         */
        async function deleteRegistration(registrationId) {
            if (!confirm('Are you sure you want to delete this registration? This action cannot be undone.')) {
                return;
            }

            showLoading(true);

            try {
                const response = await fetch(`${API_BASE_URL}/admin/registrations/${registrationId}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    showAlert('Registration deleted successfully', 'success');
                    loadRegistrationsPage(); // Reload registrations
                } else {
                    showAlert(result.message || 'Failed to delete registration', 'danger');
                }

            } catch (error) {
                console.error('Delete registration error:', error);
                showAlert('Network error', 'danger');
            } finally {
                showLoading(false);
            }
        }
        
        // ==================== REPORTS & ANALYTICS PAGE ====================

        let reportsData = null;

        /**
         * Load Reports & Analytics page
         */
        async function loadReportsPage() {
            showLoading(true);
            
            try {
                const response = await fetch(`${API_BASE_URL}/admin/reports`, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });

                if (response.status === 401 || response.status === 403) {
                    showAlert('Unauthorized. Please login again.', 'danger');
                    setTimeout(() => window.location.href = '/capstone_project/login', 2000);
                    return;
                }

                const result = await response.json();

                if (response.ok && result.success) {
                    reportsData = result.data || {};
                    renderReportsPage();
                    showAlert('Reports loaded successfully', 'success');
                } else {
                    showAlert(result.message || 'Failed to load reports', 'danger');
                }

            } catch (error) {
                console.error('Load reports error:', error);
                showAlert('Network error. Unable to load reports.', 'danger');
            } finally {
                showLoading(false);
            }
        }

        /**
         * Render Reports page UI
         */
        function renderReportsPage() {
            const contentArea = document.querySelector('.content-area');
            const overview = reportsData.overview || {};

            contentArea.innerHTML = `
                <div id="alertContainer"></div>
                
                <!-- Overview Stats -->
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <h5 class="mb-3"><i class="bi bi-speedometer2"></i> System Overview</h5>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <div>
                                    <div class="stat-card-value">${overview.total_users || 0}</div>
                                    <div class="stat-card-label">Total Users</div>
                                </div>
                                <div class="stat-card-icon bg-primary-gradient">
                                    <i class="bi bi-people"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <div>
                                    <div class="stat-card-value">${overview.total_students || 0}</div>
                                    <div class="stat-card-label">Students</div>
                                </div>
                                <div class="stat-card-icon bg-success-gradient">
                                    <i class="bi bi-person-badge"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <div>
                                    <div class="stat-card-value">${overview.total_lecturers || 0}</div>
                                    <div class="stat-card-label">Lecturers</div>
                                </div>
                                <div class="stat-card-icon bg-info-gradient">
                                    <i class="bi bi-person-workspace"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <div>
                                    <div class="stat-card-value">${overview.total_topics || 0}</div>
                                    <div class="stat-card-label">Topics</div>
                                </div>
                                <div class="stat-card-icon bg-warning-gradient">
                                    <i class="bi bi-journal-text"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <div>
                                    <div class="stat-card-value">${overview.total_registrations || 0}</div>
                                    <div class="stat-card-label">Registrations</div>
                                </div>
                                <div class="stat-card-icon bg-purple-gradient">
                                    <i class="bi bi-clipboard-check"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <div>
                                    <div class="stat-card-value">${overview.total_submissions || 0}</div>
                                    <div class="stat-card-label">Submissions</div>
                                </div>
                                <div class="stat-card-icon bg-danger-gradient">
                                    <i class="bi bi-upload"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Grid -->
                <div class="row g-3 mb-4">
                    <!-- Registration Status Distribution -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="bi bi-pie-chart"></i> Registration Status Distribution</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="registrationStatusChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Topics by Status -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="bi bi-bar-chart"></i> Topics by Status</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="topicStatusChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Registration Trends -->
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="bi bi-graph-up"></i> Monthly Registration Trends (Last 12 Months)</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="monthlyTrendsChart" height="100"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Department Analysis -->
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="bi bi-building"></i> Department Analysis</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="departmentChart" height="100"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Metrics -->
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <h5 class="mb-3"><i class="bi bi-speedometer"></i> Performance Metrics</h5>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <h3 class="text-primary">${reportsData.performance?.avg_review_time_hours?.toFixed(1) || 0} hrs</h3>
                                <p class="text-muted mb-0">Avg. Review Time</p>
                                <small class="text-muted">Time to review registrations</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <h3 class="text-success">${reportsData.performance?.approval_metrics?.approval_rate || 0}%</h3>
                                <p class="text-muted mb-0">Approval Rate</p>
                                <small class="text-muted">${reportsData.performance?.approval_metrics?.approved || 0} of ${reportsData.performance?.approval_metrics?.total || 0} approved</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <h3 class="text-info">${reportsData.performance?.avg_capacity_utilization?.toFixed(1) || 0}%</h3>
                                <p class="text-muted mb-0">Capacity Utilization</p>
                                <small class="text-muted">Average topic capacity usage</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Topics & Lecturer Productivity -->
                <div class="row g-3">
                    <!-- Top Topics -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="bi bi-trophy"></i> Top 10 Topics by Registrations</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Topic Title</th>
                                                <th>Lecturer</th>
                                                <th class="text-center">Registrations</th>
                                            </tr>
                                        </thead>
                                        <tbody id="topTopicsTable">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lecturer Productivity -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="bi bi-award"></i> Top 10 Lecturers by Productivity</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Lecturer</th>
                                                <th class="text-center">Topics</th>
                                                <th class="text-center">Students</th>
                                            </tr>
                                        </thead>
                                        <tbody id="lecturerProductivityTable">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Render all charts and tables
            renderReportsCharts();
            renderTopTopicsTable();
            renderLecturerProductivityTable();
        }

        /**
         * Render all report charts
         */
        function renderReportsCharts() {
            renderRegistrationStatusChart();
            renderTopicStatusChart();
            renderMonthlyTrendsChart();
            renderDepartmentChart();
        }

        /**
         * Render registration status pie chart
         */
        function renderRegistrationStatusChart() {
            const ctx = document.getElementById('registrationStatusChart');
            if (!ctx) return;

            const statusData = reportsData.registrations?.status_distribution || [];
            
            if (charts.registrationStatus) {
                charts.registrationStatus.destroy();
            }

            charts.registrationStatus = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: statusData.map(s => s.status),
                    datasets: [{
                        data: statusData.map(s => s.count),
                        backgroundColor: [
                            'rgba(255, 193, 7, 0.8)',  // Pending - Yellow
                            'rgba(40, 167, 69, 0.8)',  // Approved - Green
                            'rgba(220, 53, 69, 0.8)',  // Rejected - Red
                            'rgba(108, 117, 125, 0.8)' // Withdrawn - Gray
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const item = statusData[context.dataIndex];
                                    return `${item.status}: ${item.count} (${item.percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        /**
         * Render topic status bar chart
         */
        function renderTopicStatusChart() {
            const ctx = document.getElementById('topicStatusChart');
            if (!ctx) return;

            const topicData = reportsData.topics?.by_status || [];
            
            if (charts.topicStatus) {
                charts.topicStatus.destroy();
            }

            charts.topicStatus = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: topicData.map(t => t.status),
                    datasets: [{
                        label: 'Number of Topics',
                        data: topicData.map(t => t.count),
                        backgroundColor: [
                            'rgba(40, 167, 69, 0.8)',   // Published - Green
                            'rgba(255, 193, 7, 0.8)',   // Draft - Yellow
                            'rgba(108, 117, 125, 0.8)'  // Archived - Gray
                        ],
                        borderColor: [
                            'rgba(40, 167, 69, 1)',
                            'rgba(255, 193, 7, 1)',
                            'rgba(108, 117, 125, 1)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                precision: 0
                            }
                        }
                    }
                }
            });
        }

        /**
         * Render monthly trends line chart
         */
        function renderMonthlyTrendsChart() {
            const ctx = document.getElementById('monthlyTrendsChart');
            if (!ctx) return;

            const trendsData = reportsData.registrations?.monthly_trends || [];
            
            if (charts.monthlyTrends) {
                charts.monthlyTrends.destroy();
            }

            charts.monthlyTrends = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: trendsData.map(t => t.month),
                    datasets: [{
                        label: 'Registrations',
                        data: trendsData.map(t => t.count),
                        borderColor: 'rgba(102, 126, 234, 1)',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                precision: 0
                            }
                        }
                    }
                }
            });
        }

        /**
         * Render department analysis chart
         */
        function renderDepartmentChart() {
            const ctx = document.getElementById('departmentChart');
            if (!ctx) return;

            const deptData = reportsData.departments || [];
            
            if (charts.department) {
                charts.department.destroy();
            }

            charts.department = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: deptData.map(d => d.department_code),
                    datasets: [
                        {
                            label: 'Students',
                            data: deptData.map(d => d.student_count),
                            backgroundColor: 'rgba(40, 167, 69, 0.7)',
                            borderColor: 'rgba(40, 167, 69, 1)',
                            borderWidth: 2
                        },
                        {
                            label: 'Lecturers',
                            data: deptData.map(d => d.lecturer_count),
                            backgroundColor: 'rgba(23, 162, 184, 0.7)',
                            borderColor: 'rgba(23, 162, 184, 1)',
                            borderWidth: 2
                        },
                        {
                            label: 'Topics',
                            data: deptData.map(d => d.topic_count),
                            backgroundColor: 'rgba(255, 193, 7, 0.7)',
                            borderColor: 'rgba(255, 193, 7, 1)',
                            borderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                title: function(context) {
                                    const index = context[0].dataIndex;
                                    return deptData[index]?.department_name || '';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                precision: 0
                            }
                        }
                    }
                }
            });
        }

        /**
         * Render top topics table
         */
        function renderTopTopicsTable() {
            const tbody = document.getElementById('topTopicsTable');
            if (!tbody) return;

            const topTopics = reportsData.registrations?.top_topics || [];

            if (topTopics.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No data available</td></tr>';
                return;
            }

            tbody.innerHTML = topTopics.map((topic, index) => `
                <tr>
                    <td>${index + 1}</td>
                    <td>
                        <div style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" 
                             title="${topic.title}">${topic.title}</div>
                    </td>
                    <td><small>${topic.lecturer_name}</small></td>
                    <td class="text-center">
                        <span class="badge bg-primary">${topic.registration_count}</span>
                    </td>
                </tr>
            `).join('');
        }

        /**
         * Render lecturer productivity table
         */
        function renderLecturerProductivityTable() {
            const tbody = document.getElementById('lecturerProductivityTable');
            if (!tbody) return;

            const lecturers = reportsData.topics?.lecturer_productivity || [];

            if (lecturers.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No data available</td></tr>';
                return;
            }

            tbody.innerHTML = lecturers.map((lecturer, index) => `
                <tr>
                    <td>${index + 1}</td>
                    <td>
                        <div><strong>${lecturer.lecturer_name}</strong></div>
                        <small class="text-muted">${lecturer.lecturer_code}</small>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-warning text-dark">${lecturer.topics_created}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-success">${lecturer.students_supervised}</span>
                    </td>
                </tr>
            `).join('');
        }
        
        // ==================== SETTINGS PAGE ====================

        let settingsData = [];

        /**
         * Load Settings page
         */
        async function loadSettingsPage() {
            showLoading(true);
            
            try {
                const response = await fetch(`${API_BASE_URL}/admin/settings`, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });

                if (response.status === 401 || response.status === 403) {
                    showAlert('Unauthorized. Please login again.', 'danger');
                    setTimeout(() => window.location.href = '/capstone_project/login', 2000);
                    return;
                }

                const result = await response.json();

                if (response.ok && result.success) {
                    settingsData = result.data || [];
                    renderSettingsPage();
                    showAlert('Settings loaded successfully', 'success');
                } else {
                    showAlert(result.message || 'Failed to load settings', 'danger');
                }

            } catch (error) {
                console.error('Load settings error:', error);
                showAlert('Network error. Unable to load settings.', 'danger');
            } finally {
                showLoading(false);
            }
        }

        /**
         * Render Settings page UI
         */
        function renderSettingsPage() {
            const contentArea = document.querySelector('.content-area');

            // Group settings by category
            const categories = {
                'Academic': ['academic_year', 'semester', 'registration_deadline'],
                'Registration': ['max_topic_registrations_per_student', 'auto_approve_registrations', 'global_max_quota'],
                'Submission': ['submission_grace_period', 'evaluation_deadline_days', 'min_proposal_words'],
                'File Upload': ['max_file_size_mb', 'allowed_file_types'],
                'System': ['enable_notifications', 'contact_email', 'session_timeout_minutes', 'system_maintenance_mode']
            };

            contentArea.innerHTML = `
                <div id="alertContainer"></div>
                
                <!-- Page Header -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <h5 class="mb-2"><i class="bi bi-gear"></i> System Settings</h5>
                        <p class="text-muted mb-0">Manage system configuration and preferences</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <button class="btn btn-primary" onclick="openCreateSettingModal()">
                            <i class="bi bi-plus-circle"></i> Add New Setting
                        </button>
                    </div>
                </div>

                <!-- Settings by Category -->
                ${Object.keys(categories).map(category => renderCategoryCard(category, categories[category])).join('')}

                <!-- Other Settings -->
                ${renderOtherSettings(categories)}

                <!-- Edit Setting Modal -->
                <div class="modal fade" id="editSettingModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Edit Setting</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" id="editSettingId">
                                <div class="mb-3">
                                    <label class="form-label">Setting Key</label>
                                    <input type="text" class="form-control" id="editSettingKey" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Value</label>
                                    <div id="editSettingValueContainer"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" id="editSettingDescription" rows="2"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Data Type</label>
                                    <input type="text" class="form-control" id="editSettingType" readonly>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" onclick="saveSetting()">
                                    <i class="bi bi-check-circle"></i> Save Changes
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Create Setting Modal -->
                <div class="modal fade" id="createSettingModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Create New Setting</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Setting Key *</label>
                                    <input type="text" class="form-control" id="createSettingKey" 
                                           placeholder="e.g., new_feature_enabled">
                                    <small class="text-muted">Use lowercase with underscores</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Data Type *</label>
                                    <select class="form-select" id="createSettingType">
                                        <option value="string">String</option>
                                        <option value="integer">Integer</option>
                                        <option value="boolean">Boolean</option>
                                        <option value="date">Date</option>
                                        <option value="json">JSON</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Value *</label>
                                    <input type="text" class="form-control" id="createSettingValue" 
                                           placeholder="Enter value">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" id="createSettingDescription" rows="2" 
                                              placeholder="Describe what this setting does"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" onclick="createNewSetting()">
                                    <i class="bi bi-check-circle"></i> Create Setting
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        /**
         * Render category card
         */
        function renderCategoryCard(category, keys) {
            const categorySettings = settingsData.filter(s => keys.includes(s.setting_key));
            
            return `
                <div class="card mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-folder"></i> ${category} Settings</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 250px;">Setting Key</th>
                                        <th>Current Value</th>
                                        <th style="width: 300px;">Description</th>
                                        <th style="width: 100px;">Type</th>
                                        <th style="width: 150px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${categorySettings.length === 0 ? 
                                        '<tr><td colspan="5" class="text-center text-muted">No settings in this category</td></tr>' :
                                        categorySettings.map(setting => `
                                            <tr>
                                                <td><code>${setting.setting_key}</code></td>
                                                <td>${formatSettingValue(setting)}</td>
                                                <td><small class="text-muted">${setting.description || '-'}</small></td>
                                                <td><span class="badge bg-secondary">${setting.data_type}</span></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-primary" onclick="openEditSettingModal(${setting.id})" title="Edit">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <button class="btn btn-outline-danger" onclick="deleteSetting(${setting.id}, '${setting.setting_key}')" title="Delete">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        `).join('')
                                    }
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;
        }

        /**
         * Render other settings not in predefined categories
         */
        function renderOtherSettings(categories) {
            const categorizedKeys = Object.values(categories).flat();
            const otherSettings = settingsData.filter(s => !categorizedKeys.includes(s.setting_key));

            if (otherSettings.length === 0) {
                return '';
            }

            return renderCategoryCard('Other', otherSettings.map(s => s.setting_key));
        }

        /**
         * Format setting value for display
         */
        function formatSettingValue(setting) {
            let value = setting.setting_value;
            
            switch (setting.data_type) {
                case 'boolean':
                    const isTrue = value === 'true';
                    return `<span class="badge ${isTrue ? 'bg-success' : 'bg-secondary'}">${isTrue ? 'Enabled' : 'Disabled'}</span>`;
                
                case 'json':
                    try {
                        const parsed = JSON.parse(value);
                        return `<code style="font-size:0.85em">${JSON.stringify(parsed)}</code>`;
                    } catch (e) {
                        return `<code>${value}</code>`;
                    }
                
                case 'date':
                    return `<strong>${new Date(value).toLocaleDateString('en-GB')}</strong>`;
                
                case 'integer':
                    return `<strong>${value}</strong>`;
                
                default:
                    return `<span>${value}</span>`;
            }
        }

        /**
         * Open edit setting modal
         */
        function openEditSettingModal(settingId) {
            const setting = settingsData.find(s => s.id === settingId);
            if (!setting) return;

            document.getElementById('editSettingId').value = setting.id;
            document.getElementById('editSettingKey').value = setting.setting_key;
            document.getElementById('editSettingDescription').value = setting.description || '';
            document.getElementById('editSettingType').value = setting.data_type;

            // Create appropriate input based on data type
            const container = document.getElementById('editSettingValueContainer');
            container.innerHTML = createSettingInput(setting.data_type, setting.setting_value, 'editSettingValue');

            const modal = new bootstrap.Modal(document.getElementById('editSettingModal'));
            modal.show();
        }

        /**
         * Create appropriate input element based on data type
         */
        function createSettingInput(dataType, currentValue, inputId) {
            switch (dataType) {
                case 'boolean':
                    const isChecked = currentValue === 'true' ? 'checked' : '';
                    return `
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="${inputId}" ${isChecked} 
                                   onchange="document.getElementById('${inputId}Label').textContent = this.checked ? 'Enabled' : 'Disabled'">
                            <label class="form-check-label" for="${inputId}">
                                <span id="${inputId}Label">${currentValue === 'true' ? 'Enabled' : 'Disabled'}</span>
                            </label>
                        </div>
                    `;
                
                case 'date':
                    return `<input type="date" class="form-control" id="${inputId}" value="${currentValue}">`;
                
                case 'integer':
                    return `<input type="number" class="form-control" id="${inputId}" value="${currentValue}">`;
                
                case 'json':
                    try {
                        const formatted = JSON.stringify(JSON.parse(currentValue), null, 2);
                        return `<textarea class="form-control font-monospace" id="${inputId}" rows="5">${formatted}</textarea>`;
                    } catch (e) {
                        return `<textarea class="form-control font-monospace" id="${inputId}" rows="5">${currentValue}</textarea>`;
                    }
                
                default:
                    return `<input type="text" class="form-control" id="${inputId}" value="${currentValue}">`;
            }
        }

        /**
         * Save setting changes
         */
        async function saveSetting() {
            const settingId = document.getElementById('editSettingId').value;
            const dataType = document.getElementById('editSettingType').value;
            const description = document.getElementById('editSettingDescription').value;
            
            let settingValue;
            if (dataType === 'boolean') {
                settingValue = document.getElementById('editSettingValue').checked ? 'true' : 'false';
            } else {
                settingValue = document.getElementById('editSettingValue').value;
            }

            if (!settingValue && dataType !== 'boolean') {
                showAlert('Setting value is required', 'warning');
                return;
            }

            showLoading(true);

            try {
                const response = await fetch(`${API_BASE_URL}/admin/settings/${settingId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        setting_value: settingValue,
                        description: description
                    })
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    showAlert('Setting updated successfully', 'success');
                    
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editSettingModal'));
                    modal.hide();
                    
                    // Reload settings
                    loadSettingsPage();
                } else {
                    showAlert(result.message || 'Failed to update setting', 'danger');
                }

            } catch (error) {
                console.error('Update setting error:', error);
                showAlert('Network error', 'danger');
            } finally {
                showLoading(false);
            }
        }

        /**
         * Open create setting modal
         */
        function openCreateSettingModal() {
            document.getElementById('createSettingKey').value = '';
            document.getElementById('createSettingType').value = 'string';
            document.getElementById('createSettingValue').value = '';
            document.getElementById('createSettingDescription').value = '';

            const modal = new bootstrap.Modal(document.getElementById('createSettingModal'));
            modal.show();
        }

        /**
         * Create new setting
         */
        async function createNewSetting() {
            const settingKey = document.getElementById('createSettingKey').value.trim();
            const dataType = document.getElementById('createSettingType').value;
            const settingValue = document.getElementById('createSettingValue').value;
            const description = document.getElementById('createSettingDescription').value;

            if (!settingKey || !settingValue) {
                showAlert('Setting key and value are required', 'warning');
                return;
            }

            // Validate key format
            if (!/^[a-z0-9_]+$/.test(settingKey)) {
                showAlert('Setting key must contain only lowercase letters, numbers, and underscores', 'warning');
                return;
            }

            showLoading(true);

            try {
                const response = await fetch(`${API_BASE_URL}/admin/settings`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        setting_key: settingKey,
                        setting_value: settingValue,
                        data_type: dataType,
                        description: description
                    })
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    showAlert('Setting created successfully', 'success');
                    
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('createSettingModal'));
                    modal.hide();
                    
                    // Reload settings
                    loadSettingsPage();
                } else {
                    showAlert(result.message || 'Failed to create setting', 'danger');
                }

            } catch (error) {
                console.error('Create setting error:', error);
                showAlert('Network error', 'danger');
            } finally {
                showLoading(false);
            }
        }

        /**
         * Delete setting
         */
        async function deleteSetting(settingId, settingKey) {
            if (!confirm(`Are you sure you want to delete the setting "${settingKey}"? This action cannot be undone.`)) {
                return;
            }

            showLoading(true);

            try {
                const response = await fetch(`${API_BASE_URL}/admin/settings/${settingId}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    showAlert('Setting deleted successfully', 'success');
                    loadSettingsPage();
                } else {
                    showAlert(result.message || 'Failed to delete setting', 'danger');
                }

            } catch (error) {
                console.error('Delete setting error:', error);
                showAlert('Network error', 'danger');
            } finally {
                showLoading(false);
            }
        }
    </script>
</body>
</html>
