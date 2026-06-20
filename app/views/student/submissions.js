/**
 * Submissions Module
 * Handles student submission functionality including:
 * - Viewing milestones
 * - Uploading submissions
 * - Viewing submission history
 * - Managing submitted files
 */

// Global state for submissions
let currentMilestones = [];
let currentSubmissions = [];
let studentAssignment = null;

/**
 * Initialize submissions section
 */
async function initializeSubmissions() {
    console.log('Initializing submissions section...');
    showLoading(true);
    
    try {
        // Check if student has an approved project
        await loadStudentAssignment();
        
        if (!studentAssignment) {
            showNoProjectWarning();
            return;
        }
        
        // Load milestones and submissions
        await Promise.all([
            loadMilestones(),
            loadSubmissions()
        ]);
        
        renderSubmissionsUI();
        
    } catch (error) {
        console.error('Initialize submissions error:', error);
        showAlert('Failed to load submissions data', 'danger');
    } finally {
        showLoading(false);
    }
}

/**
 * Load student's assignment
 */
async function loadStudentAssignment() {
    try {
        const response = await fetch(`${API_BASE_URL}/topics/my-project/${currentStudentId}`, {
            method: 'GET',
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        });

        if (response.ok) {
            const result = await response.json();
            studentAssignment = result.data;
            console.log('Student assignment loaded:', studentAssignment);
        } else {
            console.log('No approved project found');
            studentAssignment = null;
        }
    } catch (error) {
        console.error('Load assignment error:', error);
        studentAssignment = null;
    }
}

/**
 * Load milestones with submission status
 */
async function loadMilestones() {
    try {
        const response = await fetch(`${API_BASE_URL}/submissions/milestones`, {
            method: 'GET',
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        });

        if (response.ok) {
            const result = await response.json();
            currentMilestones = result.data || [];
            console.log('Milestones loaded:', currentMilestones.length);
        } else {
            console.error('Failed to load milestones:', response.status);
            currentMilestones = [];
        }
    } catch (error) {
        console.error('Load milestones error:', error);
        currentMilestones = [];
    }
}

/**
 * Load student's submissions
 */
async function loadSubmissions() {
    try {
        const response = await fetch(`${API_BASE_URL}/submissions/my-submissions`, {
            method: 'GET',
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        });

        if (response.ok) {
            const result = await response.json();
            currentSubmissions = result.data || [];
            console.log('Submissions loaded:', currentSubmissions.length);
        } else {
            console.error('Failed to load submissions:', response.status);
            currentSubmissions = [];
        }
    } catch (error) {
        console.error('Load submissions error:', error);
        currentSubmissions = [];
    }
}

/**
 * Show warning when student doesn't have approved project
 */
function showNoProjectWarning() {
    const contentArea = document.querySelector('.content-area');
    contentArea.innerHTML = `
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <strong>No Approved Project</strong>
            <p class="mb-0 mt-2">You need to have an approved project before you can submit work. 
            Please register for a topic and wait for lecturer approval.</p>
        </div>
        
        <div class="text-center py-5">
            <i class="bi bi-folder-x" style="font-size: 72px; color: #ccc;"></i>
            <h4 class="mt-3">No Project Assignment Yet</h4>
            <p class="text-muted">Register for a topic to get started with your capstone project.</p>
            <button class="btn btn-primary mt-3" onclick="showBrowseTopicsSection()">
                <i class="bi bi-search"></i> Browse Topics
            </button>
        </div>
    `;
}

/**
 * Render submissions UI
 */
function renderSubmissionsUI() {
    const contentArea = document.querySelector('.content-area');
    
    contentArea.innerHTML = `
        <!-- Project Info -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-folder-fill text-primary"></i> Current Project</h5>
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Title:</strong> ${studentAssignment.topic_title}</p>
                        <p class="mb-1"><strong>Lecturer:</strong> ${studentAssignment.lecturer_name}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Status:</strong> <span class="badge bg-success">Approved</span></p>
                        <p class="mb-1"><strong>Assigned:</strong> ${formatDate(studentAssignment.assigned_at)}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Milestones & Submissions -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-list-check"></i> Milestones & Submissions</h5>
            </div>
            <div class="card-body">
                <div id="milestonesContainer">
                    ${renderMilestones()}
                </div>
            </div>
        </div>
    `;
}

/**
 * Render milestones list
 */
function renderMilestones() {
    if (!currentMilestones || currentMilestones.length === 0) {
        return `
            <div class="text-center py-4 text-muted">
                <i class="bi bi-inbox" style="font-size: 48px;"></i>
                <p class="mt-2">No milestones available yet.</p>
            </div>
        `;
    }

    return currentMilestones.map((milestone, index) => {
        const isDeadlinePassed = new Date(milestone.deadline) < new Date();
        const hasSubmission = milestone.submission_id !== null;
        const isGraded = milestone.status === 'Graded';
        
        return `
            <div class="milestone-card mb-4 ${hasSubmission ? 'milestone-submitted' : ''}" data-milestone-id="${milestone.id}">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="mb-1">
                            <span class="badge bg-secondary me-2">#${index + 1}</span>
                            ${milestone.title}
                        </h6>
                        <p class="text-muted mb-2" style="font-size: 14px;">${milestone.description || 'No description'}</p>
                    </div>
                    <div class="text-end">
                        ${getSubmissionStatusBadge(milestone)}
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <small class="text-muted">Deadline:</small>
                        <div class="${isDeadlinePassed ? 'text-danger' : 'text-success'} fw-bold">
                            <i class="bi bi-calendar-event"></i> ${formatDate(milestone.deadline)}
                            ${isDeadlinePassed ? '<span class="badge bg-danger ms-2">Passed</span>' : ''}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Weight:</small>
                        <div class="fw-bold">
                            <i class="bi bi-percent"></i> ${milestone.weight_percentage}%
                        </div>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Status:</small>
                        <div class="fw-bold">
                            ${hasSubmission ? '<i class="bi bi-check-circle text-success"></i>' : '<i class="bi bi-x-circle text-muted"></i>'}
                            ${milestone.status}
                        </div>
                    </div>
                </div>

                ${hasSubmission ? renderSubmissionDetails(milestone) : ''}
                
                <div class="mt-3">
                    ${hasSubmission ? 
                        (isGraded ? 
                            '<button class="btn btn-sm btn-secondary" disabled><i class="bi bi-lock-fill"></i> Graded - Cannot Modify</button>' :
                            '<button class="btn btn-sm btn-danger" onclick="deleteSubmission(' + milestone.submission_id + ')"><i class="bi bi-trash"></i> Delete Submission</button>'
                        ) :
                        '<button class="btn btn-sm btn-primary" onclick="showSubmitModal(' + milestone.id + ', \'' + escapeHtml(milestone.title) + '\')"><i class="bi bi-upload"></i> Submit Work</button>'
                    }
                </div>
            </div>
        `;
    }).join('');
}

/**
 * Get submission status badge
 */
function getSubmissionStatusBadge(milestone) {
    if (!milestone.submission_id) {
        return '<span class="badge bg-secondary">Not Submitted</span>';
    }
    
    if (milestone.status === 'Graded') {
        return `<span class="badge bg-success">Graded (${milestone.total_score || 0} pts)</span>`;
    }
    
    if (milestone.is_late) {
        return '<span class="badge bg-warning">Submitted (Late)</span>';
    }
    
    return '<span class="badge bg-info">Submitted</span>';
}

/**
 * Render submission details
 */
function renderSubmissionDetails(milestone) {
    return `
        <div class="alert alert-info mb-0">
            <h6 class="alert-heading"><i class="bi bi-file-earmark-check"></i> Your Submission</h6>
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><strong>File:</strong> ${milestone.file_name}</p>
                    <p class="mb-1"><strong>Submitted:</strong> ${formatDateTime(milestone.submitted_at)}</p>
                </div>
                <div class="col-md-6">
                    ${milestone.submission_comments ? `<p class="mb-1"><strong>Comments:</strong> ${milestone.submission_comments}</p>` : ''}
                    ${milestone.status === 'Graded' ? 
                        `<p class="mb-1"><strong>Score:</strong> <span class="badge bg-success">${milestone.total_score || 0} points</span></p>` : 
                        '<p class="mb-1 text-muted"><i>Awaiting evaluation...</i></p>'
                    }
                </div>
            </div>
            <a href="${milestone.file_url}" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                <i class="bi bi-download"></i> Download File
            </a>
        </div>
    `;
}

/**
 * Show submit modal
 */
function showSubmitModal(milestoneId, milestoneTitle) {
    const modalHTML = `
        <div class="modal fade" id="submitModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="bi bi-upload"></i> Submit Work</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <h6 class="mb-3">Milestone: ${milestoneTitle}</h6>
                        
                        <form id="submitForm">
                            <input type="hidden" id="milestoneId" value="${milestoneId}">
                            
                            <div class="mb-3">
                                <label for="submissionFile" class="form-label">Upload File <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="submissionFile" required>
                                <small class="text-muted">Accepted formats: PDF, DOC, DOCX, ZIP (Max 10MB)</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="submissionComments" class="form-label">Comments (Optional)</label>
                                <textarea class="form-control" id="submissionComments" rows="3" 
                                    placeholder="Add any notes about your submission..."></textarea>
                            </div>
                            
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i>
                                <small>Make sure your submission is complete before uploading. You can only submit once per milestone.</small>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="submitWork()">
                            <i class="bi bi-upload"></i> Submit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('submitModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('submitModal'));
    modal.show();
}

/**
 * Submit work
 */
async function submitWork() {
    const milestoneId = document.getElementById('milestoneId').value;
    const fileInput = document.getElementById('submissionFile');
    const comments = document.getElementById('submissionComments').value;
    
    // Validate file
    if (!fileInput.files || fileInput.files.length === 0) {
        showAlert('Please select a file to upload', 'warning');
        return;
    }
    
    const file = fileInput.files[0];
    const maxSize = 10 * 1024 * 1024; // 10MB
    
    if (file.size > maxSize) {
        showAlert('File size exceeds 10MB limit', 'danger');
        return;
    }
    
    // Validate file type
    const allowedTypes = ['application/pdf', 'application/msword', 
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 
        'application/zip'];
    
    if (!allowedTypes.includes(file.type)) {
        showAlert('Invalid file type. Please upload PDF, DOC, DOCX, or ZIP file', 'danger');
        return;
    }
    
    showLoading(true);
    
    try {
        // In a real implementation, you would upload the file to server first
        // For this demo, we'll simulate file upload with a fake path
        const fileUrl = '/uploads/submissions/' + new Date().getFullYear() + '/' + 
                        (new Date().getMonth() + 1) + '/' + file.name;
        
        const data = {
            milestone_id: parseInt(milestoneId),
            file_url: fileUrl,
            file_name: file.name,
            comments: comments || null
        };
        
        const response = await fetch(`${API_BASE_URL}/submissions/submit`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (response.ok) {
            showAlert(result.message || 'Submission uploaded successfully!', 'success');
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('submitModal'));
            modal.hide();
            
            // Reload submissions
            await initializeSubmissions();
        } else {
            showAlert(result.message || 'Failed to submit work', 'danger');
        }
        
    } catch (error) {
        console.error('Submit work error:', error);
        showAlert('An error occurred while submitting your work', 'danger');
    } finally {
        showLoading(false);
    }
}

/**
 * Delete submission
 */
async function deleteSubmission(submissionId) {
    if (!confirm('Are you sure you want to delete this submission? This action cannot be undone.')) {
        return;
    }
    
    showLoading(true);
    
    try {
        const response = await fetch(`${API_BASE_URL}/submissions/${submissionId}`, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        });
        
        const result = await response.json();
        
        if (response.ok) {
            showAlert(result.message || 'Submission deleted successfully', 'success');
            
            // Reload submissions
            await initializeSubmissions();
        } else {
            showAlert(result.message || 'Failed to delete submission', 'danger');
        }
        
    } catch (error) {
        console.error('Delete submission error:', error);
        showAlert('An error occurred while deleting submission', 'danger');
    } finally {
        showLoading(false);
    }
}

/**
 * Show submissions section (called from navigation)
 */
function showSubmissionsSection() {
    // Update page title
    document.querySelector('.top-bar h2').innerHTML = '<i class="bi bi-upload"></i> Submissions';
    
    // Initialize submissions
    initializeSubmissions();
}

/**
 * Format date helper
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
 * Format datetime helper
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
 * Escape HTML helper
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Add CSS for milestone cards
const submissionStyles = `
    <style>
        .milestone-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            background: #f8f9fa;
            transition: all 0.3s;
        }
        
        .milestone-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .milestone-submitted {
            border-left: 4px solid #28a745;
        }
        
        .milestone-card .alert {
            border-radius: 6px;
        }
    </style>
`;

// Inject styles
document.head.insertAdjacentHTML('beforeend', submissionStyles);
