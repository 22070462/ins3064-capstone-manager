/**
 * Security Helpers
 * 
 * Provides security functions to prevent XSS and other vulnerabilities
 * 
 * @author  Capstone Project Team
 * @version 1.0.0
 */

/**
 * Escape HTML special characters to prevent XSS attacks
 * 
 * Converts potentially dangerous characters to HTML entities:
 * - & → &amp;
 * - < → &lt;
 * - > → &gt;
 * - " → &quot;
 * - ' → &#039;
 * 
 * @param {string} str - String to escape
 * @returns {string} Escaped string safe for HTML insertion
 */
function escapeHtml(str) {
    if (str === null || str === undefined) {
        return '';
    }
    
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

/**
 * Sanitize HTML but allow specific safe tags
 * 
 * WARNING: Use with caution! Only for trusted content.
 * This is NOT a complete XSS filter.
 * 
 * @param {string} html - HTML string to sanitize
 * @param {Array<string>} allowedTags - Tags to allow (default: ['b', 'i', 'em', 'strong'])
 * @returns {string} Sanitized HTML
 */
function sanitizeHtml(html, allowedTags = ['b', 'i', 'em', 'strong']) {
    const div = document.createElement('div');
    div.innerHTML = html;
    
    // Remove all tags except allowed ones
    const allElements = div.querySelectorAll('*');
    allElements.forEach(el => {
        if (!allowedTags.includes(el.tagName.toLowerCase())) {
            // Replace element with its text content
            el.replaceWith(document.createTextNode(el.textContent));
        }
    });
    
    return div.innerHTML;
}

/**
 * Validate and sanitize URL to prevent javascript: protocol injection
 * 
 * @param {string} url - URL to validate
 * @returns {string|null} Sanitized URL or null if invalid
 */
function sanitizeUrl(url) {
    if (!url) return null;
    
    // Block dangerous protocols
    const dangerousProtocols = ['javascript:', 'data:', 'vbscript:', 'file:'];
    const urlLower = url.toLowerCase().trim();
    
    for (const protocol of dangerousProtocols) {
        if (urlLower.startsWith(protocol)) {
            console.warn('Blocked dangerous URL protocol:', protocol);
            return null;
        }
    }
    
    // Allow http, https, mailto, and relative URLs
    if (urlLower.startsWith('http://') || 
        urlLower.startsWith('https://') || 
        urlLower.startsWith('mailto:') ||
        urlLower.startsWith('/') ||
        urlLower.startsWith('#')) {
        return url;
    }
    
    // Default to relative URL
    return '/' + url;
}

/**
 * Validate integer input
 * 
 * @param {*} value - Value to validate
 * @param {number} min - Minimum allowed value
 * @param {number} max - Maximum allowed value
 * @returns {number|null} Validated integer or null if invalid
 */
function validateInteger(value, min = null, max = null) {
    const num = parseInt(value, 10);
    
    if (isNaN(num)) {
        return null;
    }
    
    if (min !== null && num < min) {
        return null;
    }
    
    if (max !== null && num > max) {
        return null;
    }
    
    return num;
}

/**
 * Validate email format
 * 
 * @param {string} email - Email to validate
 * @returns {boolean} True if valid email format
 */
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(String(email).toLowerCase());
}

/**
 * Generate CSRF token (placeholder - should come from server)
 * 
 * @returns {string|null} CSRF token from meta tag or null
 */
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : null;
}

/**
 * Add CSRF token to fetch headers
 * 
 * @param {Headers} headers - Fetch API headers object
 * @returns {Headers} Headers with CSRF token added
 */
function addCsrfToken(headers) {
    const token = getCsrfToken();
    if (token) {
        headers.append('X-CSRF-TOKEN', token);
    }
    return headers;
}

/**
 * Safe JSON parse with error handling
 * 
 * @param {string} jsonString - JSON string to parse
 * @param {*} defaultValue - Default value if parsing fails
 * @returns {*} Parsed JSON or default value
 */
function safeJsonParse(jsonString, defaultValue = null) {
    try {
        return JSON.parse(jsonString);
    } catch (e) {
        console.error('JSON parse error:', e);
        return defaultValue;
    }
}

/**
 * Truncate string to max length with ellipsis
 * 
 * @param {string} str - String to truncate
 * @param {number} maxLength - Maximum length
 * @returns {string} Truncated string
 */
function truncateString(str, maxLength = 100) {
    if (!str || str.length <= maxLength) {
        return str;
    }
    return str.substring(0, maxLength - 3) + '...';
}

// Export functions for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        escapeHtml,
        sanitizeHtml,
        sanitizeUrl,
        validateInteger,
        validateEmail,
        getCsrfToken,
        addCsrfToken,
        safeJsonParse,
        truncateString
    };
}
