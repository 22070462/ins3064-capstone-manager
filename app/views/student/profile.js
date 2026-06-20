/**
 * Student Profile Management
 * Handles profile viewing and updating
 */

let profileData = null;

/**
 * Show Profile Section
 */
async function showProfileSection() {
    console.log('=== showProfileSection START ===');
    
    const contentArea = document.querySelector('.content-area');
    
    // Show loading state
    contentArea.innerHTML = `
        <div class="d-flex justify-content-center align-items-center" style="min-height: 400px;">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    try {
        // Load profile data
        await loadProfileData();
        
        // Render profile UI
        renderProfileUI();
        
        console.log('✓ Profile section loaded successfully');
    } catch (error) {
        console.error('✗ Error loading profile section:', error);
        contentArea.innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i>
                Failed to load profile: ${error.message}
            </div>
        `;
    }
    
    console.log('=== showProfileSection END ===');
}

/**
 * Load Profile Data from API
 */
async function loadProfileData() {
    try {
        // Get current user info
        const userResponse = await fetch(`${API_BASE_URL}/auth/me`, {
            method: 'GET',
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        });

        if (!userResponse.ok) {
            throw new Error('Failed to load user information');
        }

        const userResult = await userResponse.json();
        const userData = userResult.data;

        // Get student details
        const studentResponse = await fetch(`${API_BASE_URL}/students/${userData.student_id}`, {
            method: 'GET',
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        });

        if (!studentResponse.ok) {
            throw new Error('Failed to load student profile');
        }

        const studentResult = await studentResponse.json();
        
        profileData = {
            user: userData,
            student: studentResult.data
        };

        console.log('Profile data loaded:', profileData);
        
    } catch (error) {
        console.error('Load profile error:', error);
        throw error;
    }
}

/**
 * Render Profile UI
 */
function renderProfileUI() {
    const contentArea = document.querySelector('.content-area');
    const student = profileData.student;
    const user = profileData.user;
    
    contentArea.innerHTML = `
        <!-- Alert Container -->
        <div id="profileAlertContainer"></div>

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="bi bi-person-circle"></i> My Profile</h3>
            <button class="btn btn-primary" onclick="enableEditMode()">
                <i class="bi bi-pencil"></i> Edit Profile
            </button>
        </div>

        <!-- Profile Content -->
        <div class="row">
            <!-- Left Column - Profile Card -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <!-- Avatar -->
                        <div class="mb-3">
                            <div class="mx-auto" style="width: 150px; height: 150px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 64px; font-weight: bold;">
                                ${student.full_name ? student.full_name.charAt(0).toUpperCase() : 'S'}
                            </div>
                        </div>
                        
                        <!-- Name and Role -->
                        <h4 class="mb-1">${escapeHtml(student.full_name || 'N/A')}</h4>
                        <p class="text-muted mb-2">
                            <i class="bi bi-person-badge"></i> Student
                        </p>
                        <p class="text-muted mb-3">
                            <small><i class="bi bi-hash"></i> ${escapeHtml(student.student_code || 'N/A')}</small>
                        </p>
                        
                        <!-- Status Badge -->
                        <div class="mb-3">
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> ${escapeHtml(user.status || 'Active')}
                            </span>
                        </div>
                        
                        <!-- Quick Info -->
                        <div class="text-start mt-4">
                            <h6 class="mb-3"><i class="bi bi-info-circle"></i> Quick Info</h6>
                            <div class="mb-2">
                                <small class="text-muted">Department:</small>
                                <div><strong>${escapeHtml(student.department_name || 'N/A')}</strong></div>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">Enrollment Year:</small>
                                <div><strong>${escapeHtml(student.enrollment_year || 'N/A')}</strong></div>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">Username:</small>
                                <div><strong>${escapeHtml(user.username || 'N/A')}</strong></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Password Change Card -->
                <div class="card shadow-sm mt-3">
                    <div class="card-body">
                        <h6 class="mb-3"><i class="bi bi-shield-lock"></i> Security</h6>
                        <button class="btn btn-outline-primary w-100" onclick="showChangePasswordModal()">
                            <i class="bi bi-key"></i> Change Password
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Column - Profile Details -->
            <div class="col-lg-8">
                <!-- Personal Information -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-person"></i> Personal Information</h5>
                    </div>
                    <div class="card-body">
                        <form id="profileForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="fullName" value="${escapeHtml(student.full_name || '')}" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Student Code</label>
                                    <input type="text" class="form-control" value="${escapeHtml(student.student_code || '')}" readonly disabled>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" value="${escapeHtml(student.email || '')}" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" class="form-control" id="phone" value="${escapeHtml(student.phone || '')}" readonly>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Department</label>
                                    <input type="text" class="form-control" value="${escapeHtml(student.department_name || '')}" readonly disabled>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Enrollment Year</label>
                                    <input type="text" class="form-control" value="${escapeHtml(student.enrollment_year || '')}" readonly disabled>
                                </div>
                            </div>

                            <!-- Edit Mode Buttons (Hidden by default) -->
                            <div id="editModeButtons" style="display: none;">
                                <hr>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-primary" onclick="saveProfile()">
                                        <i class="bi bi-check-circle"></i> Save Changes
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="cancelEditMode()">
                                        <i class="bi bi-x-circle"></i> Cancel
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Account Information -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-gear"></i> Account Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" value="${escapeHtml(user.username || '')}" readonly disabled>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Account Status</label>
                                <input type="text" class="form-control" value="${escapeHtml(user.status || '')}" readonly disabled>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Account Created</label>
                                <input type="text" class="form-control" value="${formatDate(student.created_at)}" readonly disabled>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Updated</label>
                                <input type="text" class="form-control" value="${formatDate(student.updated_at)}" readonly disabled>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Enable Edit Mode
 */
function enableEditMode() {
    // Enable editable fields
    document.getElementById('fullName').removeAttribute('readonly');
    document.getElementById('email').removeAttribute('readonly');
    document.getElementById('phone').removeAttribute('readonly');
    
    // Show edit mode buttons
    document.getElementById('editModeButtons').style.display = 'block';
    
    // Hide edit button
    const editBtn = document.querySelector('button[onclick="enableEditMode()"]');
    if (editBtn) {
        editBtn.style.display = 'none';
    }
    
    showProfileAlert('You can now edit your profile information.', 'info');
}

/**
 * Cancel Edit Mode
 */
function cancelEditMode() {
    // Reload profile to reset values
    renderProfileUI();
    showProfileAlert('Changes cancelled.', 'secondary');
}

/**
 * Save Profile
 */
async function saveProfile() {
    try {
        // Get form values
        const fullName = document.getElementById('fullName').value.trim();
        const email = document.getElementById('email').value.trim();
        const phone = document.getElementById('phone').value.trim();
        
        // Validate
        if (!fullName) {
            showProfileAlert('Full name is required.', 'danger');
            return;
        }
        
        if (!email) {
            showProfileAlert('Email is required.', 'danger');
            return;
        }
        
        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showProfileAlert('Please enter a valid email address.', 'danger');
            return;
        }
        
        // Show loading
        showLoading(true);
        
        // Update profile
        const response = await fetch(`${API_BASE_URL}/students/${profileData.student.id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                full_name: fullName,
                email: email,
                phone: phone
            })
        });
        
        showLoading(false);
        
        if (response.ok) {
            const result = await response.json();
            
            // Reload profile data
            await loadProfileData();
            renderProfileUI();
            
            showProfileAlert('Profile updated successfully!', 'success');
        } else {
            const error = await response.json();
            showProfileAlert(error.message || 'Failed to update profile', 'danger');
        }
        
    } catch (error) {
        showLoading(false);
        console.error('Save profile error:', error);
        showProfileAlert('An error occurred while saving profile.', 'danger');
    }
}

/**
 * Show Change Password Modal
 */
function showChangePasswordModal() {
    // Create modal HTML
    const modalHtml = `
        <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="changePasswordModalLabel">
                            <i class="bi bi-key"></i> Change Password
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="passwordAlertContainer"></div>
                        
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
                                <input type="password" class="form-control" id="confirmPassword" required>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="changePassword()">
                            <i class="bi bi-check-circle"></i> Change Password
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('changePasswordModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
    modal.show();
}

/**
 * Change Password
 */
async function changePassword() {
    try {
        // Get form values
        const currentPassword = document.getElementById('currentPassword').value;
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        
        // Validate
        if (!currentPassword || !newPassword || !confirmPassword) {
            showPasswordAlert('All fields are required.', 'danger');
            return;
        }
        
        if (newPassword.length < 6) {
            showPasswordAlert('New password must be at least 6 characters.', 'danger');
            return;
        }
        
        if (newPassword !== confirmPassword) {
            showPasswordAlert('New passwords do not match.', 'danger');
            return;
        }
        
        // Show loading
        showLoading(true);
        
        // Change password
        const response = await fetch(`${API_BASE_URL}/auth/change-password`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                current_password: currentPassword,
                new_password: newPassword
            })
        });
        
        showLoading(false);
        
        if (response.ok) {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('changePasswordModal'));
            modal.hide();
            
            // Show success message
            showProfileAlert('Password changed successfully!', 'success');
        } else {
            const error = await response.json();
            showPasswordAlert(error.message || 'Failed to change password', 'danger');
        }
        
    } catch (error) {
        showLoading(false);
        console.error('Change password error:', error);
        showPasswordAlert('An error occurred while changing password.', 'danger');
    }
}

/**
 * Show Profile Alert
 */
function showProfileAlert(message, type = 'info') {
    const alertContainer = document.getElementById('profileAlertContainer');
    if (!alertContainer) return;
    
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-triangle' : 'info-circle'}"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    alertContainer.innerHTML = alertHtml;
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        alertContainer.innerHTML = '';
    }, 5000);
}

/**
 * Show Password Alert (in modal)
 */
function showPasswordAlert(message, type = 'info') {
    const alertContainer = document.getElementById('passwordAlertContainer');
    if (!alertContainer) return;
    
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-triangle' : 'info-circle'}"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    alertContainer.innerHTML = alertHtml;
}

/**
 * Format Date
 */
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    
    const date = new Date(dateString);
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    
    return date.toLocaleDateString('en-US', options);
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
