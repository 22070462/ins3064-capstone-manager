/**
 * Student Dashboard - Enhanced Features
 * Version 3.0
 * 
 * New Features:
 * - Real-time status updates with auto-refresh
 * - Advanced error handling with retry logic
 * - Offline detection
 * - Toast notifications
 * - Network resilience
 * - Performance optimizations
 */

// ==================== CONFIGURATION ====================

const CONFIG = {
    AUTO_REFRESH_INTERVAL: 30000, // 30 seconds
    API_TIMEOUT: 10000, // 10 seconds
    MAX_RETRIES: 3,
    RETRY_DELAY: 2000, // 2 seconds
    DEBOUNCE_DELAY: 300, // 300ms for search
    TOAST_DURATION: 5000 // 5 seconds
};

// ==================== STATE MANAGEMENT ====================

const AppState = {
    lastRefresh: null,
    isRefreshing: false,
    isOnline: navigator.onLine,
    autoRefreshTimer: null,
    retryCount: 0,
    cache: {
        topics: null,
        registrations: null,
        lastUpdate: null
    }
};

// ==================== UTILITY FUNCTIONS ====================

/**
 * Fetch with timeout and retry
 */
async function fetchWithRetry(url, options = {}, retries = CONFIG.MAX_RETRIES) {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), CONFIG.API_TIMEOUT);
    
    try {
        const response = await fetch(url, {
            ...options,
            signal: controller.signal
        });
        clearTimeout(timeoutId);
        
        if (!response.ok && retries > 0) {
            console.log(`Retry attempt ${CONFIG.MAX_RETRIES - retries + 1}...`);
            await delay(CONFIG.RETRY_DELAY);
            return fetchWithRetry(url, options, retries - 1);
        }
        
        return response;
    } catch (error) {
        clearTimeout(timeoutId);
        
        if (error.name === 'AbortError') {
            console.error('Request timeout');
            throw new Error('Request timeout. Please try again.');
        }
        
        if (retries > 0) {
            console.log(`Retry attempt ${CONFIG.MAX_RETRIES - retries + 1} after error...`);
            await delay(CONFIG.RETRY_DELAY);
            return fetchWithRetry(url, options, retries - 1);
        }
        
        throw error;
    }
}

/**
 * Delay helper for retry logic
 */
function delay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

/**
 * Debounce function for search
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Check if data is stale
 */
function isDataStale() {
    if (!AppState.cache.lastUpdate) return true;
    const now = Date.now();
    const age = now - AppState.cache.lastUpdate;
    return age > CONFIG.AUTO_REFRESH_INTERVAL;
}

/**
 * Update last refresh timestamp
 */
function updateLastRefresh() {
    AppState.lastRefresh = new Date();
    AppState.cache.lastUpdate = Date.now();
    
    const lastUpdatedEl = document.getElementById('lastUpdated');
    if (lastUpdatedEl) {
        lastUpdatedEl.textContent = `Last updated: ${formatTime(AppState.lastRefresh)}`;
    }
}

/**
 * Format time for display
 */
function formatTime(date) {
    return date.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
}

// ==================== REAL-TIME UPDATES ====================

/**
 * Initialize auto-refresh
 */
function initAutoRefresh() {
    // Clear existing timer
    if (AppState.autoRefreshTimer) {
        clearInterval(AppState.autoRefreshTimer);
    }
    
    // Set up new timer
    AppState.autoRefreshTimer = setInterval(async () => {
        if (!AppState.isRefreshing && AppState.isOnline) {
            await refreshDashboardData(true); // Silent refresh
        }
    }, CONFIG.AUTO_REFRESH_INTERVAL);
    
    console.log('✓ Auto-refresh initialized');
}

/**
 * Refresh dashboard data
 */
async function refreshDashboardData(silent = false) {
    if (AppState.isRefreshing) {
        console.log('Refresh already in progress...');
        return;
    }
    
    AppState.isRefreshing = true;
    
    if (!silent) {
        showRefreshIndicator(true);
    }
    
    try {
        console.log('🔄 Refreshing dashboard data...');
        
        // Store old data to detect changes
        const oldRegistrations = [...studentRegistrations];
        
        // Load fresh data
        await loadAvailableTopics();
        await loadStudentRegistrations();
        
        // Update UI
        updateStatistics();
        renderRegistrationsList();
        
        // Detect status changes
        detectStatusChanges(oldRegistrations, studentRegistrations);
        
        // Update cache
        AppState.cache.topics = [...availableTopics];
        AppState.cache.registrations = [...studentRegistrations];
        updateLastRefresh();
        
        if (!silent) {
            showEnhancedToast('Refreshed', 'Dashboard data updated successfully', 'success', 2000);
        }
        
        console.log('✓ Dashboard refreshed successfully');
        
    } catch (error) {
        console.error('✗ Refresh error:', error);
        if (!silent) {
            showEnhancedToast('Refresh Failed', 'Could not update dashboard data', 'danger');
        }
    } finally {
        AppState.isRefreshing = false;
        if (!silent) {
            showRefreshIndicator(false);
        }
    }
}

/**
 * Detect status changes and notify user
 */
function detectStatusChanges(oldRegs, newRegs) {
    if (!oldRegs || oldRegs.length === 0) return;
    
    newRegs.forEach(newReg => {
        const oldReg = oldRegs.find(r => r.id === newReg.id);
        
        if (oldReg && oldReg.status !== newReg.status) {
            // Status changed!
            const message = `Your registration for "${newReg.topic_title}" status changed from ${oldReg.status} to ${newReg.status}`;
            showEnhancedToast(
                'Status Changed!',
                message,
                newReg.status === 'Approved' ? 'success' : 
                newReg.status === 'Rejected' ? 'danger' : 'warning'
            );
            
            // Play notification sound (optional)
            playNotificationSound();
        }
    });
}

/**
 * Play notification sound
 */
function playNotificationSound() {
    try {
        const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZizoIGGS56+OgTwwOUKXh77ZnHQU7k9ryzokvBSJ1xe/glEQMES17z+CnNw');
        audio.volume = 0.3;
        audio.play().catch(() => {}); // Ignore errors
    } catch (error) {
        // Silent fail - not critical
    }
}

/**
 * Show refresh indicator
 */
function showRefreshIndicator(show) {
    const indicator = document.getElementById('refreshIndicator');
    if (indicator) {
        indicator.style.display = show ? 'inline-block' : 'none';
    }
}

/**
 * Manual refresh button handler
 */
function handleManualRefresh() {
    refreshDashboardData(false);
}

// ==================== OFFLINE DETECTION ====================

/**
 * Initialize offline detection
 */
function initOfflineDetection() {
    window.addEventListener('online', handleOnline);
    window.addEventListener('offline', handleOffline);
    
    // Check current status
    updateOnlineStatus();
    
    console.log('✓ Offline detection initialized');
}

/**
 * Handle online event
 */
function handleOnline() {
    console.log('🌐 Connection restored');
    AppState.isOnline = true;
    updateOnlineStatus();
    showEnhancedToast('Back Online', 'Connection restored. Refreshing data...', 'success', 3000);
    
    // Auto refresh when back online
    setTimeout(() => {
        refreshDashboardData(false);
    }, 1000);
}

/**
 * Handle offline event
 */
function handleOffline() {
    console.log('📡 Connection lost');
    AppState.isOnline = false;
    updateOnlineStatus();
    showEnhancedToast('Offline Mode', 'No internet connection. Some features may be limited.', 'warning', 5000);
}

/**
 * Update online status indicator
 */
function updateOnlineStatus() {
    const indicator = document.getElementById('onlineStatus');
    if (indicator) {
        if (AppState.isOnline) {
            indicator.innerHTML = '<i class="bi bi-wifi text-success"></i>';
            indicator.title = 'Online';
        } else {
            indicator.innerHTML = '<i class="bi bi-wifi-off text-danger"></i>';
            indicator.title = 'Offline';
        }
    }
}

// ==================== ENHANCED TOAST NOTIFICATIONS ====================

/**
 * Show enhanced toast with more options
 */
function showEnhancedToast(title, message, type = 'success', duration = CONFIG.TOAST_DURATION) {
    const icons = {
        success: 'check-circle-fill',
        danger: 'exclamation-triangle-fill',
        warning: 'exclamation-circle-fill',
        info: 'info-circle-fill'
    };
    
    const colors = {
        success: 'success',
        danger: 'danger',
        warning: 'warning',
        info: 'info'
    };
    
    const toastId = 'toast-' + Date.now();
    
    const toastHtml = `
        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 11000">
            <div id="${toastId}" class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header bg-${colors[type]} text-white">
                    <i class="bi bi-${icons[type]} me-2"></i>
                    <strong class="me-auto">${escapeHtml(title)}</strong>
                    <small class="text-white-50">${new Date().toLocaleTimeString()}</small>
                    <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    ${escapeHtml(message)}
                </div>
                <div class="progress" style="height: 3px;">
                    <div class="progress-bar bg-${colors[type]}" role="progressbar" style="width: 100%; transition: width ${duration}ms linear;"></div>
                </div>
            </div>
        </div>
    `;
    
    const container = document.createElement('div');
    container.innerHTML = toastHtml;
    document.body.appendChild(container);
    
    // Animate progress bar
    setTimeout(() => {
        const progressBar = container.querySelector('.progress-bar');
        if (progressBar) {
            progressBar.style.width = '0%';
        }
    }, 100);
    
    // Auto remove
    setTimeout(() => {
        const toast = document.getElementById(toastId);
        if (toast) {
            toast.classList.remove('show');
            setTimeout(() => container.remove(), 300);
        }
    }, duration);
}

// ==================== ENHANCED ERROR HANDLING ====================

/**
 * Global error handler
 */
function setupGlobalErrorHandler() {
    window.addEventListener('unhandledrejection', event => {
        console.error('Unhandled promise rejection:', event.reason);
        showEnhancedToast(
            'Unexpected Error',
            'Something went wrong. Please refresh the page.',
            'danger'
        );
    });
    
    window.addEventListener('error', event => {
        console.error('Global error:', event.error);
    });
    
    console.log('✓ Global error handler initialized');
}

/**
 * Enhanced fetch wrapper with better error handling
 */
async function enhancedFetch(url, options = {}) {
    // Check online status
    if (!AppState.isOnline) {
        throw new Error('No internet connection. Please check your network.');
    }
    
    try {
        const response = await fetchWithRetry(url, {
            ...options,
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                ...options.headers
            }
        });
        
        // Check for HTTP errors
        if (!response.ok) {
            let errorMessage = `HTTP ${response.status}`;
            
            try {
                const errorData = await response.json();
                errorMessage = errorData.message || errorMessage;
            } catch (e) {
                // Could not parse error JSON
            }
            
            throw new Error(errorMessage);
        }
        
        return await response.json();
        
    } catch (error) {
        console.error('Enhanced fetch error:', error);
        throw error;
    }
}

// ==================== DEBOUNCED SEARCH ====================

/**
 * Setup debounced search
 */
function setupDebouncedSearch() {
    const searchInput = document.getElementById('searchTopics');
    if (searchInput) {
        const debouncedSearch = debounce(() => {
            applyFilters();
        }, CONFIG.DEBOUNCE_DELAY);
        
        searchInput.addEventListener('input', debouncedSearch);
        console.log('✓ Debounced search initialized');
    }
}

// ==================== CACHE MANAGEMENT ====================

/**
 * Get cached data if available
 */
function getCachedData(key) {
    if (AppState.cache[key] && !isDataStale()) {
        console.log(`Using cached ${key}`);
        return AppState.cache[key];
    }
    return null;
}

/**
 * Set cached data
 */
function setCachedData(key, data) {
    AppState.cache[key] = data;
    AppState.cache.lastUpdate = Date.now();
}

/**
 * Clear cache
 */
function clearCache() {
    AppState.cache = {
        topics: null,
        registrations: null,
        lastUpdate: null
    };
    console.log('✓ Cache cleared');
}

// ==================== ENHANCED UI COMPONENTS ====================

/**
 * Add refresh controls to dashboard
 */
function addRefreshControls() {
    // Add to top bar if exists
    const topBar = document.querySelector('.top-bar .user-menu');
    if (topBar && !document.getElementById('refreshControls')) {
        const controlsHtml = `
            <div id="refreshControls" class="d-flex align-items-center gap-2">
                <span id="onlineStatus" title="Connection Status">
                    <i class="bi bi-wifi text-success"></i>
                </span>
                <span id="refreshIndicator" style="display: none;">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Refreshing...</span>
                    </div>
                </span>
                <button class="btn btn-sm btn-outline-primary" onclick="handleManualRefresh()" title="Refresh Now">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
                <small class="text-muted" id="lastUpdated">Never</small>
            </div>
        `;
        topBar.insertAdjacentHTML('afterbegin', controlsHtml);
        console.log('✓ Refresh controls added');
    }
}

// ==================== PERFORMANCE MONITORING ====================

/**
 * Log performance metrics
 */
function logPerformanceMetrics() {
    if (window.performance && window.performance.timing) {
        const timing = window.performance.timing;
        const loadTime = timing.loadEventEnd - timing.navigationStart;
        const domReady = timing.domContentLoadedEventEnd - timing.navigationStart;
        
        console.log('📊 Performance Metrics:');
        console.log(`   Page Load: ${loadTime}ms`);
        console.log(`   DOM Ready: ${domReady}ms`);
    }
}

// ==================== INITIALIZATION ====================

/**
 * Initialize all enhancements
 */
function initEnhancements() {
    console.log('🚀 Initializing enhanced features...');
    
    // Setup global error handler
    setupGlobalErrorHandler();
    
    // Initialize offline detection
    initOfflineDetection();
    
    // Add refresh controls
    addRefreshControls();
    
    // Initialize auto-refresh
    initAutoRefresh();
    
    // Setup debounced search (if on browse topics page)
    setupDebouncedSearch();
    
    // Log performance
    logPerformanceMetrics();
    
    // Update last refresh
    updateLastRefresh();
    
    console.log('✅ All enhancements initialized successfully!');
}

// ==================== CLEANUP ====================

/**
 * Cleanup on page unload
 */
window.addEventListener('beforeunload', () => {
    if (AppState.autoRefreshTimer) {
        clearInterval(AppState.autoRefreshTimer);
    }
    window.removeEventListener('online', handleOnline);
    window.removeEventListener('offline', handleOffline);
});

// ==================== AUTO-INIT ====================

// Initialize enhancements when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initEnhancements);
} else {
    initEnhancements();
}

console.log('✅ Student Dashboard Enhancements v3.0 loaded');
