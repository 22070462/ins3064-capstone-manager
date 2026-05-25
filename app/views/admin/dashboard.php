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

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardData();
            updateClock();
            setInterval(updateClock, 1000);
            
            // Logout button
            document.getElementById('logoutBtn').addEventListener('click', handleLogout);
        });

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
                    
                    renderStats(result.data);
                    renderCharts(result.data);
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
    </script>
</body>
</html>
