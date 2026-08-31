<style>
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
        animation: msme-all-list-spin 1s linear infinite;
    }

    @keyframes msme-all-list-spin {
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

    .loading-disabled {
        pointer-events: none;
        opacity: 0.6;
    }

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

    form#msme_all_list_search_form {
        overflow: auto;
    }

    .select2-container--default .select2-selection--multiple {
        min-height: 38px !important;
    }

    .dataTables_processing {
        display: none !important;
    }

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
        animation: msme-all-list-spin 1s linear infinite;
    }

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

    .dataTables_info {
        float: left !important;
        padding-top: 10px !important;
    }

    .dataTables_paginate {
        float: right !important;
        padding-top: 10px !important;
    }

    .table-responsive {
        overflow-x: auto !important;
        clear: both !important;
    }

    .dataTables_wrapper {
        padding: 10px 0 !important;
    }

    .dataTables_length label {
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
        font-weight: normal !important;
    }

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

    /* ============================================
       SUMMARY CARDS - colorful stat cards
    ============================================ */

    .msme-all-list-stat-card {
        display: flex;
        align-items: center;
        gap: 16px;
        width: 100%;
        height: 100%;
        padding: 20px;
        border: none;
        border-radius: 14px;
        color: #fff;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .msme-all-list-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 22px rgba(0, 0, 0, 0.18);
    }

    .msme-all-list-stat-card__icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.25);
        font-size: 22px;
    }

    .msme-all-list-stat-card__body {
        min-width: 0;
    }

    .msme-all-list-stat-card__label {
        margin: 0 0 4px 0;
        font-size: 14px;
        font-weight: 600;
        letter-spacing: 0.2px;
        opacity: 0.92;
        text-transform: uppercase;
    }

    .msme-all-list-stat-card__value {
        margin: 0;
        font-size: 28px;
        font-weight: 700;
        line-height: 1;
    }

    .msme-all-list-stat-card--total {
        background: linear-gradient(135deg, #4e73f8 0%, #2541b2 100%);
    }

    .msme-all-list-stat-card--open {
        background: linear-gradient(135deg, #ff9a44 0%, #d9682a 100%);
    }

    .msme-all-list-stat-card--direct {
        background: linear-gradient(135deg, #16c79a 0%, #0e8f6f 100%);
    }

    .msme-all-list-stat-card--onboard {
        background: linear-gradient(135deg, #b06ab3 0%, #7b3f9e 100%);
    }

    /* ============================================
       EXPORT BUTTONS
    ============================================ */

    .msme-all-list-export-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .msme-all-list-export-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border: none;
        border-radius: 8px;
        color: #fff;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: opacity 0.2s ease, transform 0.2s ease;
    }

    .msme-all-list-export-btn:hover {
        opacity: 0.9;
        transform: translateY(-1px);
        color: #fff;
    }

    .msme-all-list-export-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .msme-all-list-export-btn--pdf {
        background: linear-gradient(135deg, #ff5f6d 0%, #c0392b 100%);
    }

    .msme-all-list-export-btn--excel {
        background: linear-gradient(135deg, #1d976c 0%, #0f6f4e 100%);
    }
</style>

<div class="loader-overlay" id="msmeAllListGlobalLoader">
    <div class="loader-container">
        <div class="loader"></div>
        <p class="loader-text">Loading...</p>
        <p class="loader-subtext">Please wait while we process your request</p>
    </div>
</div>
