<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    /* ============================================
       LOADER STYLES
    ============================================ */
    
    /* Global Loader Overlay - Full screen loader */
    .loader-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 99999;
    }

    .loader-overlay.active {
        display: flex;
    }

    .loader-container {
        background: white;
        padding: 30px 40px;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        text-align: center;
        min-width: 200px;
    }

    .loader {
        border: 6px solid #f3f3f3;
        border-radius: 50%;
        border-top: 6px solid #3498db;
        border-right: 6px solid #e74c3c;
        border-bottom: 6px solid #2ecc71;
        border-left: 6px solid #f39c12;
        width: 50px;
        height: 50px;
        margin: 0 auto 15px auto;
        -webkit-animation: spin 1s linear infinite;
        animation: spin 1s linear infinite;
    }

    @-webkit-keyframes spin {
        0% { -webkit-transform: rotate(0deg); }
        100% { -webkit-transform: rotate(360deg); }
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .loader-text {
        color: #333;
        font-size: 16px;
        font-weight: 500;
        margin: 0;
    }

    .loader-subtext {
        color: #666;
        font-size: 13px;
        margin-top: 5px;
    }

    /* Disable pointer events while loading */
    .loading-disabled {
        pointer-events: none;
        opacity: 0.6;
    }

    /* Clear button */
    .clear-action {
        text-decoration: none;
        color: #3498db;
        font-weight: 500;
        padding: 8px 15px;
        border-radius: 6px;
        transition: all 0.3s;
        cursor: pointer;
    }

    .clear-action:hover {
        background: #f0f8ff;
        color: #2980b9;
    }

    .clear-action img {
        margin-right: 5px;
        vertical-align: middle;
    }

    .toggle-text {
        text-decoration: none !important;
        color: #3498db;
        cursor: pointer;
    }
    .toggle-text:hover {
        text-decoration: underline !important;
    }

    form#search_form {
        overflow: auto;
    }

    .select2-container--default .select2-selection--multiple {
        min-height: 38px !important;
    }

    /* DataTable Processing - Completely hide */
    .dataTables_processing {
        display: none !important;
    }

    /* Filter Loader */
    .filter-loader {
        display: none;
        margin-left: 10px;
        color: #3498db;
    }

    .filter-loader.active {
        display: inline-block;
    }

    .filter-loader i {
        font-size: 20px;
        animation: spin 1s linear infinite;
    }

    /* ============================================
       DATATABLE UI FIXES
    ============================================ */
    
    /* Fix for DataTable length (Show dropdown) alignment */
    .dataTables_length {
        float: left !important;
        margin-bottom: 10px;
    }

    .dataTables_length select {
        display: inline-block !important;
        width: auto !important;
        padding: 4px 8px !important;
        margin: 0 5px !important;
        border: 1px solid #ddd !important;
        border-radius: 4px !important;
        height: 34px !important;
    }

    /* Fix for DataTable filter (Search) alignment */
    .dataTables_filter {
        float: right !important;
        margin-bottom: 10px;
    }

    .dataTables_filter input {
        display: inline-block !important;
        width: auto !important;
        padding: 4px 8px !important;
        margin-left: 5px !important;
        border: 1px solid #ddd !important;
        border-radius: 4px !important;
        height: 34px !important;
    }

    /* Fix for DataTable info and pagination */
    .dataTables_info {
        float: left !important;
        padding-top: 10px !important;
    }

    .dataTables_paginate {
        float: right !important;
        padding-top: 10px !important;
    }

    /* Fix for table container */
    .table-responsive {
        overflow-x: auto !important;
        clear: both !important;
    }

    /* Ensure proper spacing */
    .dataTables_wrapper {
        padding: 10px 0 !important;
    }

    /* Fix for DataTable length dropdown positioning */
    .dataTables_length label {
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
        font-weight: normal !important;
    }

    /* Responsive fixes */
    @media (max-width: 767px) {
        .dataTables_length,
        .dataTables_filter,
        .dataTables_info,
        .dataTables_paginate {
            float: none !important;
            text-align: center !important;
            width: 100% !important;
            margin-bottom: 10px !important;
        }
        
        .dataTables_filter input {
            width: 80% !important;
        }
    }
	.dataTables_length {
    float: left !important;
    margin-bottom: 10px;
}

	.dataTables_filter {
		float: right !important;
		margin-bottom: 10px;
	}

	.dataTables_info {
		float: left !important;
		padding-top: 10px !important;
	}

	.dataTables_paginate {
		float: right !important;
		padding-top: 10px !important;
	}
</style>
<!-- ============================================
     GLOBAL LOADER - Full screen
============================================ -->
<div class="loader-overlay" id="globalLoader">
    <div class="loader-container">
        <div class="loader"></div>
        <p class="loader-text">Loading...</p>
        <p class="loader-subtext">Please wait while we process your request</p>
    </div>
</div>

<!-- ============================================
     MAIN CONTENT
============================================ -->