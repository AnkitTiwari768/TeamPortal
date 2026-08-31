{{-- resources/views/components/global-loader.blade.php --}}
<style>
    /* Global Loader Styles */
    #globalLoader {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.85);
        z-index: 99999;
        justify-content: center;
        align-items: center;
        flex-direction: column;
    }

    #globalLoader.show {
        display: flex !important;
    }

    .loader-container {
        text-align: center;
        padding: 30px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        min-width: 200px;
    }

    .loader-spinner {
        width: 50px;
        height: 50px;
        margin: 0 auto 20px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    .loader-spinner-sm {
        width: 30px;
        height: 30px;
        margin: 0 auto 15px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    .loader-text {
        color: #333;
        font-size: 16px;
        font-weight: 500;
        margin: 0;
    }

    .loader-sub-text {
        color: #666;
        font-size: 13px;
        margin-top: 5px;
    }

    /* Table Loader */
    .table-loader {
        display: none;
        text-align: center;
        padding: 60px 20px;
        background: #fff;
        border-radius: 8px;
    }

    .table-loader.show {
        display: block;
    }

    .table-loader .loader-spinner {
        width: 40px;
        height: 40px;
        margin: 0 auto 15px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    .table-loader p {
        color: #666;
        font-size: 14px;
        margin: 0;
    }

    /* Button Loader */
    .btn-loader {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #3498db;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        margin-right: 8px;
        vertical-align: middle;
    }

    /* Page Loader Overlay */
    .page-loader-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.9);
        z-index: 99998;
        display: none;
        justify-content: center;
        align-items: center;
    }

    .page-loader-overlay.show {
        display: flex;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Pulse animation for loader text */
    .loader-pulse {
        animation: pulse 1.5s ease-in-out infinite;
    }

    @keyframes pulse {
        0% { opacity: 0.6; }
        50% { opacity: 1; }
        100% { opacity: 0.6; }
    }
</style>

{{-- HTML Structure --}}
<div id="globalLoader">
    <div class="loader-container">
        <div class="loader-spinner"></div>
        <p class="loader-text" id="loaderText">Loading...</p>
        <p class="loader-sub-text" id="loaderSubText">Please wait while we process your request</p>
    </div>
</div>

{{-- Table Loader (can be used inside tables) --}}
<div id="tableLoader" class="table-loader">
    <div class="loader-spinner"></div>
    <p>Loading data...</p>
</div>

{{-- JavaScript --}}
<script>
    (function() {
        'use strict';

        // ============================================
        // GLOBAL LOADER FUNCTIONS
        // ============================================

        /**
         * Show global loader with custom message
         * @param {string} message - Main loading message
         * @param {string} subMessage - Sub loading message (optional)
         */
        window.showLoader = function(message = 'Loading...', subMessage = 'Please wait while we process your request') {
            const loader = document.getElementById('globalLoader');
            if (loader) {
                const textEl = document.getElementById('loaderText');
                const subTextEl = document.getElementById('loaderSubText');
                
                if (textEl) textEl.textContent = message;
                if (subTextEl) subTextEl.textContent = subMessage;
                
                loader.classList.add('show');
                loader.style.display = 'flex';
            }
        };

        /**
         * Hide global loader
         */
        window.hideLoader = function() {
            const loader = document.getElementById('globalLoader');
            if (loader) {
                loader.classList.remove('show');
                loader.style.display = 'none';
            }
        };

        /**
         * Show table loader
         */
        window.showTableLoader = function() {
            const loader = document.getElementById('tableLoader');
            if (loader) {
                loader.classList.add('show');
                loader.style.display = 'block';
            }
            // Hide table if exists
            const table = document.getElementById('dataTable');
            if (table) {
                table.style.display = 'none';
            }
        };

        /**
         * Hide table loader
         */
        window.hideTableLoader = function() {
            const loader = document.getElementById('tableLoader');
            if (loader) {
                loader.classList.remove('show');
                loader.style.display = 'none';
            }
            // Show table if exists
            const table = document.getElementById('dataTable');
            if (table) {
                table.style.display = '';
            }
        };

        /**
         * Navigate to URL with loader
         * @param {string} url - URL to navigate to
         * @param {string} message - Loading message
         */
        window.navigateWithLoader = function(url, message = 'Redirecting...') {
            showLoader(message, 'Please wait while we redirect you');
            setTimeout(function() {
                window.location.href = url;
            }, 300);
        };

        /**
         * Show loader for AJAX requests
         */
        window.showAjaxLoader = function() {
            showLoader('Processing...', 'Please wait while we process your request');
        };

        /**
         * Hide loader for AJAX requests
         */
        window.hideAjaxLoader = function() {
            hideLoader();
        };

        /**
         * Show button loader
         * @param {string} buttonId - Button element ID
         * @param {string} originalText - Original button text
         */
        window.showButtonLoader = function(buttonId, originalText = 'Loading...') {
            const button = document.getElementById(buttonId);
            if (button) {
                button.disabled = true;
                button.innerHTML = `<span class="btn-loader"></span> ${originalText}`;
            }
        };

        /**
         * Hide button loader
         * @param {string} buttonId - Button element ID
         * @param {string} originalText - Original button text
         */
        window.hideButtonLoader = function(buttonId, originalText) {
            const button = document.getElementById(buttonId);
            if (button) {
                button.disabled = false;
                button.textContent = originalText || 'Submit';
            }
        };

        // ============================================
        // AUTO-HIDE ON PAGE LOAD
        // ============================================

        // Hide loader when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                hideLoader();
            });
        } else {
            hideLoader();
        }

        // Hide loader when all resources are loaded
        window.addEventListener('load', function() {
            hideLoader();
        });

        // ============================================
        // AJAX INTERCEPTORS (if jQuery is available)
        // ============================================

        if (typeof $ !== 'undefined') {
            // Show loader on AJAX start
            $(document).ajaxStart(function() {
                // Don't show loader for specific URLs
                const url = window.location.href;
                if (!url.includes('export') && !url.includes('download')) {
                    showLoader('Loading...', 'Please wait while we fetch data');
                }
            });

            // Hide loader on AJAX complete
            $(document).ajaxStop(function() {
                hideLoader();
            });

            // Hide loader on AJAX error
            $(document).ajaxError(function() {
                hideLoader();
            });
        }

        // ============================================
        // UTILITY FUNCTIONS
        // ============================================

        /**
         * Create a promise with loader
         * @param {Function} promiseFn - Function that returns a promise
         * @param {string} message - Loading message
         * @returns {Promise}
         */
        window.withLoader = function(promiseFn, message = 'Processing...') {
            showLoader(message);
            return promiseFn()
                .then(function(result) {
                    hideLoader();
                    return result;
                })
                .catch(function(error) {
                    hideLoader();
                    throw error;
                });
        };

        /**
         * Show loader for DataTable initialization
         */
        window.showDataTableLoader = function() {
            showTableLoader();
        };

        /**
         * Hide loader for DataTable initialization
         */
        window.hideDataTableLoader = function() {
            hideTableLoader();
            hideLoader();
        };

        console.log('Global Loader initialized successfully');
    })();
</script>