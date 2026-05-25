<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Topic Registration - Capstone Project Management</title>
    
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .registration-card {
            max-width: 800px;
            margin: 0 auto;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .form-label {
            font-weight: 600;
            color: #333;
        }
        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 40px;
            font-weight: 600;
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }
        .topic-card {
            border-left: 4px solid #667eea;
            transition: all 0.3s;
        }
        .topic-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateX(5px);
        }
        .badge-tag {
            background-color: #e3f2fd;
            color: #1976d2;
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 0.85rem;
            margin-right: 5px;
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

    <div class="container">
        <!-- Page Header -->
        <div class="text-center mb-4">
            <h1 class="text-white mb-2">
                <i class="bi bi-journal-bookmark-fill"></i> Topic Registration
            </h1>
            <p class="text-white-50">Register for your capstone project topic</p>
        </div>

        <!-- Registration Form Card -->
        <div class="card registration-card">
            <div class="card-header py-3">
                <h4 class="mb-0">
                    <i class="bi bi-pencil-square"></i> Register for Topic
                </h4>
            </div>
            <div class="card-body p-4">
                <!-- Alert Container -->
                <div id="alertContainer"></div>

                <!-- Registration Form -->
                <form id="registrationForm" novalidate>
                    <!-- Student Selection -->
                    <div class="mb-4">
                        <label for="studentId" class="form-label">
                            <i class="bi bi-person-fill text-primary"></i> Select Student
                        </label>
                        <select class="form-select form-select-lg" id="studentId" name="student_id" required>
                            <option value="">-- Choose Student --</option>
                            <!-- Options will be loaded dynamically -->
                        </select>
                        <div class="invalid-feedback">
                            Please select a student.
                        </div>
                    </div>

                    <!-- Topic Selection -->
                    <div class="mb-4">
                        <label for="topicId" class="form-label">
                            <i class="bi bi-lightbulb-fill text-warning"></i> Select Topic
                        </label>
                        <select class="form-select form-select-lg" id="topicId" name="topic_id" required>
                            <option value="">-- Choose Topic --</option>
                            <!-- Options will be loaded dynamically -->
                        </select>
                        <div class="invalid-feedback">
                            Please select a topic.
                        </div>
                        <!-- Topic Details Display -->
                        <div id="topicDetails" class="mt-3" style="display: none;">
                            <div class="card topic-card">
                                <div class="card-body">
                                    <h6 class="card-title text-primary" id="topicTitle"></h6>
                                    <p class="card-text small text-muted" id="topicDescription"></p>
                                    <div id="topicTags"></div>
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            <i class="bi bi-building"></i> <span id="topicDepartment"></span> |
                                            <i class="bi bi-person-badge"></i> <span id="topicLecturer"></span>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lecturer Selection -->
                    <div class="mb-4">
                        <label for="lecturerId" class="form-label">
                            <i class="bi bi-person-badge-fill text-success"></i> Select Supervisor
                        </label>
                        <select class="form-select form-select-lg" id="lecturerId" name="lecturer_id" required>
                            <option value="">-- Choose Lecturer --</option>
                            <!-- Options will be loaded dynamically -->
                        </select>
                        <div class="invalid-feedback">
                            Please select a lecturer.
                        </div>
                        <!-- Lecturer Quota Display -->
                        <div id="lecturerQuota" class="mt-2" style="display: none;">
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> 
                                Available Slots: <span id="quotaInfo" class="fw-bold"></span>
                            </small>
                        </div>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="agreeTerms" required>
                            <label class="form-check-label" for="agreeTerms">
                                I confirm that the information provided is accurate and I understand the registration policies.
                            </label>
                            <div class="invalid-feedback">
                                You must agree before submitting.
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg btn-register" id="submitBtn">
                            <i class="bi bi-check-circle-fill"></i> Submit Registration
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                            <i class="bi bi-arrow-clockwise"></i> Reset Form
                        </button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-muted text-center">
                <small>
                    <i class="bi bi-shield-check"></i> Your registration will be reviewed by the lecturer
                </small>
            </div>
        </div>

        <!-- Registration Status Card -->
        <div class="card registration-card mt-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="bi bi-clock-history"></i> My Registrations
                </h5>
            </div>
            <div class="card-body">
                <div id="registrationHistory">
                    <p class="text-muted text-center">Loading registration history...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        // Configuration
        const API_BASE_URL = '/capstone_project/public/api';
        
        // Global variables
        let studentsData = [];
        let topicsData = [];
        let lecturersData = [];
        let currentStudentId = null;

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadStudents();
            loadTopics();
            loadLecturers();
            setupEventListeners();
        });

        /**
         * Setup event listeners
         */
        function setupEventListeners() {
            // Form submission
            document.getElementById('registrationForm').addEventListener('submit', handleFormSubmit);

            // Student selection change
            document.getElementById('studentId').addEventListener('change', function() {
                currentStudentId = this.value;
                if (currentStudentId) {
                    loadRegistrationHistory(currentStudentId);
                    checkEligibility(currentStudentId);
                }
            });

            // Topic selection change
            document.getElementById('topicId').addEventListener('change', function() {
                const topicId = this.value;
                if (topicId) {
                    displayTopicDetails(topicId);
                } else {
                    document.getElementById('topicDetails').style.display = 'none';
                }
            });

            // Lecturer selection change
            document.getElementById('lecturerId').addEventListener('change', function() {
                const lecturerId = this.value;
                if (lecturerId) {
                    displayLecturerQuota(lecturerId);
                } else {
                    document.getElementById('lecturerQuota').style.display = 'none';
                }
            });
        }

        /**
         * Load students from API
         */
        async function loadStudents() {
            try {
                // For demo purposes, we'll use dummy data
                // In production, fetch from API: /api/students
                studentsData = [
                    { id: 1, student_code: 'STD2024001', full_name: 'John Michael Smith' },
                    { id: 2, student_code: 'STD2024002', full_name: 'Emily Rose Johnson' },
                    { id: 3, student_code: 'STD2024003', full_name: 'Michael David Brown' },
                    { id: 4, student_code: 'STD2024004', full_name: 'Sarah Elizabeth Davis' },
                    { id: 5, student_code: 'STD2024005', full_name: 'James Robert Wilson' },
                    { id: 6, student_code: 'STD2024006', full_name: 'Jessica Marie Martinez' },
                    { id: 7, student_code: 'STD2024007', full_name: 'Daniel Christopher Anderson' },
                    { id: 8, student_code: 'STD2024008', full_name: 'Ashley Nicole Taylor' }
                ];

                const select = document.getElementById('studentId');
                studentsData.forEach(student => {
                    const option = document.createElement('option');
                    option.value = student.id;
                    option.textContent = `${student.student_code} - ${student.full_name}`;
                    select.appendChild(option);
                });
            } catch (error) {
                console.error('Error loading students:', error);
                showAlert('Failed to load students', 'danger');
            }
        }

        /**
         * Load topics from API
         */
        async function loadTopics() {
            try {
                // For demo purposes, we'll use dummy data
                // In production, fetch from: GET /api/topics
                topicsData = [
                    {
                        id: 1,
                        title: 'AI-Powered Chatbot for Customer Service',
                        description: 'Develop an intelligent chatbot using NLP and machine learning',
                        department_name: 'Computer Science',
                        lecturer_name: 'Dr. Robert James Anderson',
                        tags: 'AI, NLP, Python, TensorFlow'
                    },
                    {
                        id: 2,
                        title: 'Blockchain-Based Supply Chain Management',
                        description: 'Create a decentralized supply chain tracking system',
                        department_name: 'Computer Science',
                        lecturer_name: 'Dr. Robert James Anderson',
                        tags: 'Blockchain, Ethereum, Solidity, Web3'
                    },
                    {
                        id: 3,
                        title: 'Cloud-Native Microservices Architecture',
                        description: 'Design and implement a scalable microservices application',
                        department_name: 'Information Technology',
                        lecturer_name: 'Prof. Maria Elena Garcia',
                        tags: 'Microservices, Docker, Kubernetes, Spring Boot'
                    }
                ];

                const select = document.getElementById('topicId');
                topicsData.forEach(topic => {
                    const option = document.createElement('option');
                    option.value = topic.id;
                    option.textContent = topic.title;
                    select.appendChild(option);
                });
            } catch (error) {
                console.error('Error loading topics:', error);
                showAlert('Failed to load topics', 'danger');
            }
        }

        /**
         * Load lecturers from API
         */
        async function loadLecturers() {
            try {
                // For demo purposes, we'll use dummy data
                lecturersData = [
                    { id: 1, lecturer_code: 'LEC001', full_name: 'Dr. Robert James Anderson', max_quota: 8, current_count: 2 },
                    { id: 2, lecturer_code: 'LEC002', full_name: 'Prof. Maria Elena Garcia', max_quota: 6, current_count: 4 },
                    { id: 3, lecturer_code: 'LEC003', full_name: 'Dr. William Thomas Lee', max_quota: 7, current_count: 3 },
                    { id: 4, lecturer_code: 'LEC004', full_name: 'Dr. Jennifer Anne White', max_quota: 5, current_count: 5 }
                ];

                const select = document.getElementById('lecturerId');
                lecturersData.forEach(lecturer => {
                    const option = document.createElement('option');
                    option.value = lecturer.id;
                    const available = lecturer.max_quota - lecturer.current_count;
                    option.textContent = `${lecturer.lecturer_code} - ${lecturer.full_name} (${available} slots available)`;
                    if (available <= 0) {
                        option.disabled = true;
                        option.textContent += ' - FULL';
                    }
                    select.appendChild(option);
                });
            } catch (error) {
                console.error('Error loading lecturers:', error);
                showAlert('Failed to load lecturers', 'danger');
            }
        }

        /**
         * Display topic details
         */
        function displayTopicDetails(topicId) {
            const topic = topicsData.find(t => t.id == topicId);
            if (topic) {
                document.getElementById('topicTitle').textContent = topic.title;
                document.getElementById('topicDescription').textContent = topic.description;
                document.getElementById('topicDepartment').textContent = topic.department_name;
                document.getElementById('topicLecturer').textContent = topic.lecturer_name;
                
                // Display tags
                const tagsContainer = document.getElementById('topicTags');
                tagsContainer.innerHTML = '';
                if (topic.tags) {
                    topic.tags.split(',').forEach(tag => {
                        const badge = document.createElement('span');
                        badge.className = 'badge-tag';
                        badge.textContent = tag.trim();
                        tagsContainer.appendChild(badge);
                    });
                }
                
                document.getElementById('topicDetails').style.display = 'block';
            }
        }

        /**
         * Display lecturer quota information
         */
        function displayLecturerQuota(lecturerId) {
            const lecturer = lecturersData.find(l => l.id == lecturerId);
            if (lecturer) {
                const available = lecturer.max_quota - lecturer.current_count;
                const quotaInfo = document.getElementById('quotaInfo');
                quotaInfo.textContent = `${available} / ${lecturer.max_quota}`;
                quotaInfo.className = available > 2 ? 'fw-bold text-success' : 'fw-bold text-warning';
                document.getElementById('lecturerQuota').style.display = 'block';
            }
        }

        /**
         * Check student eligibility
         */
        async function checkEligibility(studentId) {
            // In production, call: GET /api/topics/eligibility/{studentId}
            // For demo, we'll skip this check
        }

        /**
         * Load registration history
         */
        async function loadRegistrationHistory(studentId) {
            const container = document.getElementById('registrationHistory');
            container.innerHTML = '<p class="text-muted text-center">Loading...</p>';

            // In production, call: GET /api/topics/registrations/{studentId}
            // For demo, show placeholder
            setTimeout(() => {
                container.innerHTML = `
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No previous registrations found.
                    </div>
                `;
            }, 500);
        }

        /**
         * Handle form submission
         */
        async function handleFormSubmit(event) {
            event.preventDefault();
            
            // Validate form
            const form = event.target;
            if (!form.checkValidity()) {
                event.stopPropagation();
                form.classList.add('was-validated');
                return;
            }

            // Get form data
            const formData = {
                student_id: parseInt(document.getElementById('studentId').value),
                topic_id: parseInt(document.getElementById('topicId').value),
                lecturer_id: parseInt(document.getElementById('lecturerId').value)
            };

            // Show loading
            showLoading(true);
            disableSubmitButton(true);

            try {
                // Make API call using Fetch API
                const response = await fetch(`${API_BASE_URL}/topics/register`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                // Parse JSON response
                const result = await response.json();

                // Handle response
                if (response.ok && result.success) {
                    // Success
                    showAlert(result.message, 'success');
                    showToast('Success!', result.message, 'success');
                    
                    // Reset form
                    form.reset();
                    form.classList.remove('was-validated');
                    document.getElementById('topicDetails').style.display = 'none';
                    document.getElementById('lecturerQuota').style.display = 'none';
                    
                    // Reload registration history
                    if (currentStudentId) {
                        loadRegistrationHistory(currentStudentId);
                    }
                } else {
                    // Error from API
                    showAlert(result.message || 'Registration failed', 'danger');
                    showToast('Error', result.message || 'Registration failed', 'danger');
                }

            } catch (error) {
                console.error('Registration error:', error);
                showAlert('Network error. Please check your connection and try again.', 'danger');
                showToast('Error', 'Network error occurred', 'danger');
            } finally {
                showLoading(false);
                disableSubmitButton(false);
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
            
            // Auto dismiss after 5 seconds
            setTimeout(() => {
                const alert = alertContainer.querySelector('.alert');
                if (alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            }, 5000);
        }

        /**
         * Show Bootstrap toast notification
         */
        function showToast(title, message, type) {
            const toastContainer = document.getElementById('toastContainer') || createToastContainer();
            
            const toastHtml = `
                <div class="toast align-items-center text-white bg-${type} border-0" role="alert">
                    <div class="d-flex">
                        <div class="toast-body">
                            <strong>${title}</strong><br>${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `;
            
            toastContainer.insertAdjacentHTML('beforeend', toastHtml);
            const toastElement = toastContainer.lastElementChild;
            const toast = new bootstrap.Toast(toastElement);
            toast.show();
            
            // Remove toast element after hidden
            toastElement.addEventListener('hidden.bs.toast', () => {
                toastElement.remove();
            });
        }

        /**
         * Create toast container if not exists
         */
        function createToastContainer() {
            const container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
            return container;
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
         * Enable/disable submit button
         */
        function disableSubmitButton(disable) {
            const btn = document.getElementById('submitBtn');
            if (disable) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';
            } else {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Submit Registration';
            }
        }

        /**
         * Reset form
         */
        function resetForm() {
            const form = document.getElementById('registrationForm');
            form.reset();
            form.classList.remove('was-validated');
            document.getElementById('topicDetails').style.display = 'none';
            document.getElementById('lecturerQuota').style.display = 'none';
            document.getElementById('alertContainer').innerHTML = '';
        }
    </script>
</body>
</html>
