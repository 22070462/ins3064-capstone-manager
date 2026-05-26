/**
 * API Helper Utility
 * 
 * Provides enhanced fetch functionality with:
 * - Automatic timeout handling
 * - Better error messages
 * - Retry logic
 * - Loading state management
 * 
 * @author Capstone Project Team
 * @version 1.0.0
 */

class APIHelper {
    /**
     * Default configuration
     */
    static config = {
        timeout: 30000, // 30 seconds
        retryAttempts: 2,
        retryDelay: 1000, // 1 second
        baseURL: window.location.pathname.includes('/public/') 
            ? '/capstone_project/public' 
            : '/capstone_project'
    };

    /**
     * Enhanced fetch with timeout and retry
     * 
     * @param {string} url - API endpoint URL
     * @param {object} options - Fetch options
     * @param {number} attempt - Current retry attempt (internal use)
     * @returns {Promise<Response>}
     */
    static async fetchWithTimeout(url, options = {}, attempt = 1) {
        // Create abort controller for timeout
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), this.config.timeout);

        // Merge options with defaults
        const fetchOptions = {
            ...options,
            signal: controller.signal,
            credentials: 'same-origin', // Always include session cookies
            headers: {
                'Accept': 'application/json',
                ...options.headers
            }
        };

        try {
            const response = await fetch(url, fetchOptions);
            clearTimeout(timeoutId);
            return response;

        } catch (error) {
            clearTimeout(timeoutId);

            // Handle timeout
            if (error.name === 'AbortError') {
                if (attempt < this.config.retryAttempts) {
                    console.warn(`Request timeout. Retrying (${attempt}/${this.config.retryAttempts})...`);
                    await this.delay(this.config.retryDelay);
                    return this.fetchWithTimeout(url, options, attempt + 1);
                }
                throw new Error('Request timeout. Please check your connection and try again.');
            }

            // Handle network errors
            if (error.message === 'Failed to fetch') {
                if (attempt < this.config.retryAttempts) {
                    console.warn(`Network error. Retrying (${attempt}/${this.config.retryAttempts})...`);
                    await this.delay(this.config.retryDelay);
                    return this.fetchWithTimeout(url, options, attempt + 1);
                }
                throw new Error('Network error. Please check your internet connection.');
            }

            throw error;
        }
    }

    /**
     * GET request
     * 
     * @param {string} endpoint - API endpoint (e.g., '/api/topics')
     * @param {object} options - Additional fetch options
     * @returns {Promise<object>} JSON response
     */
    static async get(endpoint, options = {}) {
        const url = `${this.config.baseURL}${endpoint}`;
        
        try {
            const response = await this.fetchWithTimeout(url, {
                method: 'GET',
                ...options
            });

            return await this.handleResponse(response);

        } catch (error) {
            return this.handleError(error, 'GET', endpoint);
        }
    }

    /**
     * POST request
     * 
     * @param {string} endpoint - API endpoint
     * @param {object} data - Request body data
     * @param {object} options - Additional fetch options
     * @returns {Promise<object>} JSON response
     */
    static async post(endpoint, data = {}, options = {}) {
        const url = `${this.config.baseURL}${endpoint}`;
        
        try {
            const response = await this.fetchWithTimeout(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    ...options.headers
                },
                body: JSON.stringify(data),
                ...options
            });

            return await this.handleResponse(response);

        } catch (error) {
            return this.handleError(error, 'POST', endpoint);
        }
    }

    /**
     * PUT request
     * 
     * @param {string} endpoint - API endpoint
     * @param {object} data - Request body data
     * @param {object} options - Additional fetch options
     * @returns {Promise<object>} JSON response
     */
    static async put(endpoint, data = {}, options = {}) {
        const url = `${this.config.baseURL}${endpoint}`;
        
        try {
            const response = await this.fetchWithTimeout(url, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    ...options.headers
                },
                body: JSON.stringify(data),
                ...options
            });

            return await this.handleResponse(response);

        } catch (error) {
            return this.handleError(error, 'PUT', endpoint);
        }
    }

    /**
     * DELETE request
     * 
     * @param {string} endpoint - API endpoint
     * @param {object} options - Additional fetch options
     * @returns {Promise<object>} JSON response
     */
    static async delete(endpoint, options = {}) {
        const url = `${this.config.baseURL}${endpoint}`;
        
        try {
            const response = await this.fetchWithTimeout(url, {
                method: 'DELETE',
                ...options
            });

            return await this.handleResponse(response);

        } catch (error) {
            return this.handleError(error, 'DELETE', endpoint);
        }
    }

    /**
     * Handle response and parse JSON
     * 
     * @param {Response} response - Fetch response
     * @returns {Promise<object>} Parsed JSON
     */
    static async handleResponse(response) {
        // Try to parse JSON
        let data;
        try {
            data = await response.json();
        } catch (e) {
            // If not JSON, get text
            const text = await response.text();
            throw new Error(`Invalid JSON response: ${text.substring(0, 100)}`);
        }

        // Handle HTTP errors
        if (!response.ok) {
            // Handle authentication errors
            if (response.status === 401) {
                console.error('Authentication required. Redirecting to login...');
                window.location.href = `${this.config.baseURL}/login`;
                throw new Error('Authentication required');
            }

            // Handle authorization errors
            if (response.status === 403) {
                throw new Error(data.message || 'You do not have permission to perform this action');
            }

            // Handle not found errors
            if (response.status === 404) {
                throw new Error(data.message || 'Resource not found');
            }

            // Handle server errors
            if (response.status >= 500) {
                console.error('Server error:', data);
                throw new Error(data.message || 'Server error. Please try again later.');
            }

            // Handle other errors
            throw new Error(data.message || `Request failed with status ${response.status}`);
        }

        return data;
    }

    /**
     * Handle errors
     * 
     * @param {Error} error - Error object
     * @param {string} method - HTTP method
     * @param {string} endpoint - API endpoint
     * @returns {object} Error response
     */
    static handleError(error, method, endpoint) {
        console.error(`${method} ${endpoint} failed:`, error);

        return {
            success: false,
            message: error.message || 'An unexpected error occurred',
            error: error
        };
    }

    /**
     * Delay helper for retry logic
     * 
     * @param {number} ms - Milliseconds to delay
     * @returns {Promise<void>}
     */
    static delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    /**
     * Set custom timeout
     * 
     * @param {number} ms - Timeout in milliseconds
     */
    static setTimeout(ms) {
        this.config.timeout = ms;
    }

    /**
     * Set retry attempts
     * 
     * @param {number} attempts - Number of retry attempts
     */
    static setRetryAttempts(attempts) {
        this.config.retryAttempts = attempts;
    }

    /**
     * Set base URL
     * 
     * @param {string} url - Base URL
     */
    static setBaseURL(url) {
        this.config.baseURL = url;
    }
}

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = APIHelper;
}
