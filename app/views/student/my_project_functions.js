/**
 * My Project Feature for Student Dashboard
 * Handles approved project display and progress tracking
 */

// Global state for My Project
let myProject = null;

/**
 * Load and display My Project section
 */
async function loadMyProject() {
    console.log('Loading My Project...');
    
    if (!currentStudentId) {
        console.error('Student ID not available');
        showAlert('Student information not found', 'danger');
        return;
    }

    try {
        const response = await fetch(`${API_BASE_URL}/topics/my-project/${currentStudentId}`, {
            method: 'GET',
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        });

        console.log('My Project API response status:', response.status);

        if (response.ok) {
            const result = await response.json();
            myProject = result.data;
            console.log('✓ My Project loaded:', myProject);
            renderMyProjectSection();
        } else {
            const errorText = await response.text();
            console.error('✗ Failed to load My Project:', response.status, errorText);
            renderMyProjectSection(); // Render empty state
        }
    } catch (error) {
        console.error('✗ Load My Project error:', error);
        renderMyProjectSection(); // Render empty state
    }
}

/**
 * Render My Project section
 */
function renderMyProjectSection() {
    const container = document.getElementById('myProjectContainer');
    
    if (!container) {
        console.error('My Project container not found');
        return;
    }

    if (!myProject) {
        // No approved project - show placeholder
        container.innerHTML = `
            <div class="text-center text-muted py-5">
                <i class="bi bi-folder-x" style="font-size: 64px;"></i>
                <h5 class="mt-3">No Approved Project Yet</h5>
                <p>Once your registration is approved, your project will appear here.</p>
                <button class="btn btn-primary mt-2" onclick="showBrowseTopicsSection()">
                    <i class="bi bi-search"></i> Browse Topics
                </button>
            </div>
        `;
        return;
    }

    // Display approved project
    const progressPercentage = myProject.progress_percentage || 0;
    const progressColor = getProgressColor(progressPercentage);
    const lastUpdated = myProject.progress_updated_at 
        ? new Date(myProject.progress_updated_at).toLocaleDateString()
        : 'Not yet updated';

    container.innerHTML = `
        <div class="row">
            <!-- Project Information Card -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-folder-open"></i> Project Details</h5>
                    </div>
                    <div class="card-body">
                        <h4 class="text-primary mb-3">${escapeHtml(myProject.topic_title)}</h4>
                        
                        <div class="mb-3">
                            <h6 class="text-muted"><i class="bi bi-card-text"></i> Description</h6>
                            <p>${escapeHtml(myProject.topic_description)}</p>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6 class="text-muted"><i class="bi bi-person-badge"></i> Supervisor</h6>
                                <p class="mb-1"><strong>${escapeHtml(myProject.lecturer_name)}</strong></p>
                                <p class="small text-muted mb-0">
                                    <i class="bi bi-envelope"></i> ${escapeHtml(myProject.lecturer_email)}<br>
                                    <i class="bi bi-telephone"></i> ${escapeHtml(myProject.lecturer_phone || 'N/A')}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted"><i class="bi bi-building"></i> Department</h6>
                                <p><strong>${escapeHtml(myProject.department_name)}</strong></p>
                                
                                <h6 class="text-muted"><i class="bi bi-calendar-check"></i> Approved Date</h6>
                                <p>${myProject.reviewed_at ? new Date(myProject.reviewed_at).toLocaleDateString() : 'N/A'}</p>
                            </div>
                        </div>

                        ${myProject.tags ? `
                        <div class="mb-3">
                            <h6 class="text-muted"><i class="bi bi-tags"></i> Tags</h6>
                            <div>
                                ${myProject.tags.split(',').map(tag => 
                                    `<span class="badge bg-info me-1">${escapeHtml(tag.trim())}</span>`
                                ).join('')}
                            </div>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>

            <!-- Progress Tracking Card -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-bar-chart-line"></i> Progress Tracking</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="progress-circle mb-3" style="width: 120px; height: 120px; margin: 0 auto;">
                                <svg viewBox="0 0 36 36" class="circular-chart ${progressColor}">
                                    <path class="circle-bg"
                                        d="M18 2.0845
                                        a 15.9155 15.9155 0 0 1 0 31.831
                                        a 15.9155 15.9155 0 0 1 0 -31.831"
                                    />
                                    <path class="circle"
                                        stroke-dasharray="${progressPercentage}, 100"
                                        d="M18 2.0845
                                        a 15.9155 15.9155 0 0 1 0 31.831
                                        a 15.9155 15.9155 0 0 1 0 -31.831"
                                    />
                                    <text x="18" y="20.35" class="percentage">${progressPercentage}%</text>
                                </svg>
                            </div>
                            <p class="small text-muted">Project Completion</p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><strong>Update Progress</strong></label>
                            <input type="range" class="form-range" id="progressSlider" 
                                   min="0" max="100" value="${progressPercentage}" step="5">
                            <div class="d-flex justify-content-between small text-muted">
                                <span>0%</span>
                                <span id="progressValue">${progressPercentage}%</span>
                                <span>100%</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><strong>Progress Notes</strong></label>
                            <textarea class="form-control" id="progressNotes" rows="4" 
                                      placeholder="Add notes about your progress...">${escapeHtml(myProject.progress_notes || '')}</textarea>
                        </div>

                        <button class="btn btn-success w-100" onclick="updateProgress()">
                            <i class="bi bi-save"></i> Save Progress
                        </button>

                        <div class="mt-3 text-center small text-muted">
                            Last updated: ${lastUpdated}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Project Timeline Card -->
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Project Timeline</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-date">${new Date(myProject.registered_at).toLocaleDateString()}</div>
                        <div class="timeline-content"><strong>Registered</strong> for project</div>
                    </div>
                    ${myProject.reviewed_at ? `
                    <div class="timeline-item">
                        <div class="timeline-date">${new Date(myProject.reviewed_at).toLocaleDateString()}</div>
                        <div class="timeline-content"><strong>Approved</strong> by supervisor</div>
                    </div>
                    ` : ''}
                    ${myProject.progress_updated_at ? `
                    <div class="timeline-item">
                        <div class="timeline-date">${new Date(myProject.progress_updated_at).toLocaleDateString()}</div>
                        <div class="timeline-content"><strong>Progress Updated</strong> to ${progressPercentage}%</div>
                    </div>
                    ` : ''}
                </div>
            </div>
        </div>
    `;

    // Add progress slider event listener
    const slider = document.getElementById('progressSlider');
    const valueDisplay = document.getElementById('progressValue');
    
    if (slider && valueDisplay) {
        slider.addEventListener('input', function() {
            valueDisplay.textContent = this.value + '%';
        });
    }
}

/**
 * Update project progress
 */
async function updateProgress() {
    const slider = document.getElementById('progressSlider');
    const notes = document.getElementById('progressNotes');

    if (!slider) {
        showAlert('Progress slider not found', 'danger');
        return;
    }

    const progress = parseInt(slider.value);
    const progressNotes = notes ? notes.value.trim() : '';

    if (progress < 0 || progress > 100) {
        showAlert('Progress must be between 0 and 100', 'warning');
        return;
    }

    try {
        showLoading(true);

        const response = await fetch(`${API_BASE_URL}/topics/my-project/progress`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                progress: progress,
                notes: progressNotes
            })
        });

        const result = await response.json();

        if (response.ok) {
            myProject = result.data;
            showAlert(result.message || 'Progress updated successfully', 'success');
            renderMyProjectSection();
        } else {
            showAlert(result.message || 'Failed to update progress', 'danger');
        }
    } catch (error) {
        console.error('Update progress error:', error);
        showAlert('Network error while updating progress', 'danger');
    } finally {
        showLoading(false);
    }
}

/**
 * Show My Project section (navigation)
 */
function showMyProjectSection() {
    // Hide all content sections
    const contentArea = document.querySelector('.content-area');
    if (contentArea) {
        contentArea.innerHTML = `
            <div class="section-card">
                <h4 class="mb-4">
                    <i class="bi bi-folder"></i> My Project
                </h4>
                <div id="myProjectContainer">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Load My Project data
    loadMyProject();
}

/**
 * Get progress bar color based on percentage
 */
function getProgressColor(percentage) {
    if (percentage < 30) return 'red';
    if (percentage < 60) return 'orange';
    if (percentage < 90) return 'blue';
    return 'green';
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
