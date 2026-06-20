/**
 * Lecturer - My Students Management
 * Shows all students who have approved registrations with the lecturer
 */

let myStudentsData = [];
let studentsFilter = 'all'; // all, active, completed

/**
 * Show My Students Page
 */
async function showMyStudentsPage() {
    const pageContent = document.getElementById('pageContent');
    pageContent.innerHTML = `
        <div class="card">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-people"></i> My Students</h5>
                    <button class="btn btn-light btn-sm" onclick="refreshMyStudents()">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Statistics -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h3 id="totalStudentsCount">0</h3>
                                <p class="mb-0">Total Students</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h3 id="activeProjectsCount">0</h3>
                                <p class="mb-0">Active Projects</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h3 id="completedProjectsCount">0</h3>
                                <p class="mb-0">Completed Projects</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter and Search -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" id="studentSearchInput" 
                                   placeholder="Search by student name, code, or topic...">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <select class="form-select" id="studentFilterSelect" onchange="handleStudentFilter()">
                            <option value="all">All Students</option>
                            <option value="active">Active Projects</option>
                            <option value="completed">Completed Projects</option>
                        </select>
                    </div>
                </div>

                <!-- Students List -->
                <div id="studentsListContainer">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading students...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Details Modal -->
        <div class="modal fade" id="studentDetailsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="bi bi-person-badge"></i> Student Details</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="studentDetailsContent">
                        <!-- Content loaded dynamically -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Project Progress Modal -->
        <div class="modal fade" id="projectProgressModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="bi bi-graph-up"></i> Project Progress</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="projectProgressContent">
                        <!-- Content loaded dynamically -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Setup search
    document.getElementById('studentSearchInput').addEventListener('input', handleStudentSearch);

    // Load students data
    await loadMyStudents();
}

/**
 * Load My Students Data
 */
async function loadMyStudents() {
    try {
        if (!currentLecturerId) {
            showAlert('Lecturer information not found', 'danger');
            return;
        }

        const response = await fetch(`${API_BASE_URL}/lecturers/my-students`, {
            method: 'GET',
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        });

        if (response.ok) {
            const result = await response.json();
            myStudentsData = result.data || [];
            console.log('My students loaded:', myStudentsData.length);
            
            // Update statistics
            updateStudentsStatistics();
            
            // Render students list
            renderStudentsList();
        } else {
            const error = await response.json();
            showAlert(error.message || 'Failed to load students', 'danger');
            renderEmptyStudentsList();
        }
    } catch (error) {
        console.error('Load students error:', error);
        showAlert('Network error while loading students', 'danger');
        renderEmptyStudentsList();
    }
}

/**
 * Update Students Statistics
 */
function updateStudentsStatistics() {
    const total = myStudentsData.length;
    const active = myStudentsData.filter(s => s.project_status !== 'Completed').length;
    const completed = myStudentsData.filter(s => s.project_status === 'Completed').length;

    document.getElementById('totalStudentsCount').textContent = total;
    document.getElementById('activeProjectsCount').textContent = active;
    document.getElementById('completedProjectsCount').textContent = completed;
}

/**
 * Render Students List
 */
function renderStudentsList(filteredData = null) {
    const container = document.getElementById('studentsListContainer');
    const students = filteredData || myStudentsData;

    if (!students || students.length === 0) {
        renderEmptyStudentsList();
        return;
    }

    let html = '<div class="table-responsive"><table class="table table-hover align-middle">';
    html += `
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Student</th>
                <th>Topic</th>
                <th>Department</th>
                <th>Progress</th>
                <th>Status</th>
                <th>Registered Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
    `;

    students.forEach((student, index) => {
        const progress = student.progress || 0;
        const progressColor = progress < 30 ? 'danger' : progress < 70 ? 'warning' : 'success';
        const statusBadge = getProjectStatusBadge(student.project_status);

        html += `
            <tr>
                <td>${index + 1}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" 
                             style="width: 40px; height: 40px; font-weight: bold;">
                            ${student.student_name ? student.student_name.charAt(0).toUpperCase() : 'S'}
                        </div>
                        <div>
                            <div class="fw-bold">${escapeHtml(student.student_name || 'N/A')}</div>
                            <small class="text-muted">${escapeHtml(student.student_code || 'N/A')}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="fw-bold text-primary" style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        ${escapeHtml(student.topic_title || 'N/A')}
                    </div>
                </td>
                <td>${escapeHtml(student.department_name || 'N/A')}</td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div class="progress flex-grow-1" style="height: 20px; min-width: 100px;">
                            <div class="progress-bar bg-${progressColor}" role="progressbar" 
                                 style="width: ${progress}%" aria-valuenow="${progress}" 
                                 aria-valuemin="0" aria-valuemax="100">
                                ${progress}%
                            </div>
                        </div>
                    </div>
                </td>
                <td>${statusBadge}</td>
                <td>${formatDate(student.registered_at)}</td>
                <td>
                    <div class="btn-group btn-group-sm" role="group">
                        <button class="btn btn-outline-primary" onclick="viewStudentDetails(${student.student_id})" 
                                title="View Details">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-outline-success" onclick="viewProjectProgress(${student.registration_id})" 
                                title="View Progress">
                            <i class="bi bi-graph-up"></i>
                        </button>
                        <button class="btn btn-outline-info" onclick="contactStudent('${escapeHtml(student.student_email || '')}')" 
                                title="Contact Student">
                            <i class="bi bi-envelope"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });

    html += '</tbody></table></div>';
    container.innerHTML = html;
}

/**
 * Render Empty Students List
 */
function renderEmptyStudentsList() {
    const container = document.getElementById('studentsListContainer');
    container.innerHTML = `
        <div class="text-center text-muted py-5">
            <i class="bi bi-people" style="font-size: 64px;"></i>
            <h5 class="mt-3">No Students Found</h5>
            <p>You don't have any students with approved registrations yet.</p>
        </div>
    `;
}

/**
 * Handle Student Filter
 */
function handleStudentFilter() {
    const filterValue = document.getElementById('studentFilterSelect').value;
    studentsFilter = filterValue;

    let filtered = myStudentsData;

    if (filterValue === 'active') {
        filtered = myStudentsData.filter(s => s.project_status !== 'Completed');
    } else if (filterValue === 'completed') {
        filtered = myStudentsData.filter(s => s.project_status === 'Completed');
    }

    renderStudentsList(filtered);
}

/**
 * Handle Student Search
 */
function handleStudentSearch() {
    const searchTerm = document.getElementById('studentSearchInput').value.toLowerCase().trim();

    if (!searchTerm) {
        handleStudentFilter(); // Apply current filter
        return;
    }

    let filtered = myStudentsData.filter(student => {
        return (
            (student.student_name && student.student_name.toLowerCase().includes(searchTerm)) ||
            (student.student_code && student.student_code.toLowerCase().includes(searchTerm)) ||
            (student.topic_title && student.topic_title.toLowerCase().includes(searchTerm)) ||
            (student.department_name && student.department_name.toLowerCase().includes(searchTerm))
        );
    });

    // Apply current filter on search results
    if (studentsFilter === 'active') {
        filtered = filtered.filter(s => s.project_status !== 'Completed');
    } else if (studentsFilter === 'completed') {
        filtered = filtered.filter(s => s.project_status === 'Completed');
    }

    renderStudentsList(filtered);
}

/**
 * View Student Details
 */
async function viewStudentDetails(studentId) {
    try {
        showLoading(true);

        const response = await fetch(`${API_BASE_URL}/students/${studentId}`, {
            method: 'GET',
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        });

        showLoading(false);

        if (response.ok) {
            const result = await response.json();
            const student = result.data;

            // Render student details
            const content = document.getElementById('studentDetailsContent');
            content.innerHTML = `
                <div class="row">
                    <div class="col-md-4 text-center mb-3">
                        <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" 
                             style="width: 120px; height: 120px; font-size: 48px; font-weight: bold;">
                            ${student.full_name ? student.full_name.charAt(0).toUpperCase() : 'S'}
                        </div>
                        <h5 class="mt-3">${escapeHtml(student.full_name || 'N/A')}</h5>
                        <p class="text-muted">${escapeHtml(student.student_code || 'N/A')}</p>
                        <span class="badge bg-success">Active</span>
                    </div>
                    <div class="col-md-8">
                        <h6 class="border-bottom pb-2 mb-3">Personal Information</h6>
                        <div class="row mb-2">
                            <div class="col-sm-4 text-muted">Email:</div>
                            <div class="col-sm-8">${escapeHtml(student.email || 'N/A')}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 text-muted">Phone:</div>
                            <div class="col-sm-8">${escapeHtml(student.phone || 'N/A')}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 text-muted">Department:</div>
                            <div class="col-sm-8">${escapeHtml(student.department_name || 'N/A')}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 text-muted">Enrollment Year:</div>
                            <div class="col-sm-8">${escapeHtml(student.enrollment_year || 'N/A')}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 text-muted">Account Created:</div>
                            <div class="col-sm-8">${formatDate(student.created_at)}</div>
                        </div>
                    </div>
                </div>
            `;

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('studentDetailsModal'));
            modal.show();
        } else {
            const error = await response.json();
            showAlert(error.message || 'Failed to load student details', 'danger');
        }
    } catch (error) {
        showLoading(false);
        console.error('View student details error:', error);
        showAlert('Network error while loading student details', 'danger');
    }
}

/**
 * View Project Progress
 */
async function viewProjectProgress(registrationId) {
    try {
        showLoading(true);

        const response = await fetch(`${API_BASE_URL}/lecturers/student-progress/${registrationId}`, {
            method: 'GET',
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        });

        showLoading(false);

        if (response.ok) {
            const result = await response.json();
            const progressData = result.data;

            // Render progress details
            const content = document.getElementById('projectProgressContent');
            content.innerHTML = `
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h6>Project: ${escapeHtml(progressData.topic_title || 'N/A')}</h6>
                        <p class="text-muted">Student: ${escapeHtml(progressData.student_name || 'N/A')}</p>
                    </div>
                </div>

                <!-- Overall Progress -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-bold">Overall Progress</span>
                        <span class="fw-bold">${progressData.overall_progress || 0}%</span>
                    </div>
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: ${progressData.overall_progress || 0}%" 
                             aria-valuenow="${progressData.overall_progress || 0}" 
                             aria-valuemin="0" aria-valuemax="100">
                            ${progressData.overall_progress || 0}%
                        </div>
                    </div>
                </div>

                <!-- Milestones -->
                <h6 class="mb-3">Project Milestones</h6>
                <div id="milestonesProgressList">
                    ${renderMilestonesProgress(progressData.milestones || [])}
                </div>

                <!-- Recent Submissions -->
                <h6 class="mb-3 mt-4">Recent Submissions</h6>
                <div id="recentSubmissions">
                    ${renderRecentSubmissions(progressData.submissions || [])}
                </div>
            `;

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('projectProgressModal'));
            modal.show();
        } else {
            const error = await response.json();
            showAlert(error.message || 'Failed to load project progress', 'danger');
        }
    } catch (error) {
        showLoading(false);
        console.error('View project progress error:', error);
        showAlert('Network error while loading project progress', 'danger');
    }
}

/**
 * Render Milestones Progress
 */
function renderMilestonesProgress(milestones) {
    if (!milestones || milestones.length === 0) {
        return '<p class="text-muted">No milestones defined yet.</p>';
    }

    let html = '<div class="list-group">';
    milestones.forEach(milestone => {
        const statusBadge = getMilestoneStatusBadge(milestone.status);
        html += `
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">${escapeHtml(milestone.title)}</h6>
                        <small class="text-muted">Due: ${formatDate(milestone.deadline)}</small>
                    </div>
                    <div>
                        ${statusBadge}
                    </div>
                </div>
                <div class="progress mt-2" style="height: 10px;">
                    <div class="progress-bar ${milestone.progress >= 100 ? 'bg-success' : 'bg-primary'}" 
                         style="width: ${milestone.progress || 0}%"></div>
                </div>
            </div>
        `;
    });
    html += '</div>';
    return html;
}

/**
 * Render Recent Submissions
 */
function renderRecentSubmissions(submissions) {
    if (!submissions || submissions.length === 0) {
        return '<p class="text-muted">No submissions yet.</p>';
    }

    let html = '<div class="table-responsive"><table class="table table-sm">';
    html += '<thead><tr><th>Milestone</th><th>Submitted</th><th>Status</th><th>Grade</th></tr></thead><tbody>';
    
    submissions.forEach(sub => {
        const statusBadge = getSubmissionStatusBadge(sub.status);
        html += `
            <tr>
                <td>${escapeHtml(sub.milestone_title)}</td>
                <td>${formatDate(sub.submitted_at)}</td>
                <td>${statusBadge}</td>
                <td>${sub.grade ? sub.grade + '/100' : '-'}</td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    return html;
}

/**
 * Contact Student (Email)
 */
function contactStudent(email) {
    if (!email) {
        showAlert('Student email not available', 'warning');
        return;
    }
    window.location.href = `mailto:${email}`;
}

/**
 * Refresh My Students
 */
async function refreshMyStudents() {
    await loadMyStudents();
    showAlert('Students list refreshed', 'success');
}

/**
 * Get Project Status Badge
 */
function getProjectStatusBadge(status) {
    const badges = {
        'Active': '<span class="badge bg-success">Active</span>',
        'Completed': '<span class="badge bg-primary">Completed</span>',
        'On Hold': '<span class="badge bg-warning">On Hold</span>',
        'Cancelled': '<span class="badge bg-danger">Cancelled</span>'
    };
    return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
}

/**
 * Get Milestone Status Badge
 */
function getMilestoneStatusBadge(status) {
    const badges = {
        'Not Started': '<span class="badge bg-secondary">Not Started</span>',
        'In Progress': '<span class="badge bg-primary">In Progress</span>',
        'Submitted': '<span class="badge bg-info">Submitted</span>',
        'Completed': '<span class="badge bg-success">Completed</span>',
        'Overdue': '<span class="badge bg-danger">Overdue</span>'
    };
    return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
}

/**
 * Get Submission Status Badge
 */
function getSubmissionStatusBadge(status) {
    const badges = {
        'Submitted': '<span class="badge bg-info">Submitted</span>',
        'Graded': '<span class="badge bg-success">Graded</span>',
        'Revision Required': '<span class="badge bg-warning">Revision Required</span>'
    };
    return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
}

/**
 * Format Date
 */
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}

/**
 * Escape HTML
 */
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
