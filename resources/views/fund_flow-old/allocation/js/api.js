/**
 * api.js — Centralized API handler for the Allocation Module.
 *
 * RULE: ALL API calls MUST go through apiRequest().
 *       Do NOT create any additional fetch/$.ajax functions.
 */

/**
 * Perform a fetch-based HTTP request.
 *
 * @param {object} options
 * @param {string}  options.url     — Endpoint URL
 * @param {string}  [options.method='GET'] — HTTP verb
 * @param {object|null} [options.data=null]  — JSON body (for POST/PUT/PATCH)
 * @param {object}  [options.params={}]  — URL query params (for GET)
 * @returns {Promise<any>}  Resolved JSON response
 */
async function apiRequest({ url, method = 'GET', data = null, params = {} }) {
    try {
        // Append query string for GET requests
        if (method === 'GET' && Object.keys(params).length > 0) {
            const qs = new URLSearchParams(params).toString();
            url = url + (url.includes('?') ? '&' : '?') + qs;
        }

        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                    ? document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    : (window.csrfToken || ''),
            },
        };

        if (data && method !== 'GET') {
            options.body = JSON.stringify(data);
        }

        const response = await fetch(url, options);

        if (!response.ok) {
            const errBody = await response.json().catch(() => ({}));
            throw { status: response.status, body: errBody };
        }

        return await response.json();

    } catch (error) {
        console.error('[Allocation] API Error:', error);
        throw error;
    }
}

/**
 * Upload a file using multipart/form-data (MUST bypass JSON for file uploads).
 *
 * @param {string}   url
 * @param {FormData} formData
 * @returns {Promise<any>}
 */
async function apiUploadFile(url, formData) {
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                    ? document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    : (window.csrfToken || ''),
            },
            body: formData,
        });

        if (!response.ok) {
            const errBody = await response.json().catch(() => ({}));
            throw { status: response.status, body: errBody };
        }

        return await response.json();
    } catch (error) {
        console.error('[Allocation] Upload Error:', error);
        throw error;
    }
}
