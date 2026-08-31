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
        animation: category-wise-count-spin 1s linear infinite;
    }

    @keyframes category-wise-count-spin {
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

    form#category_wise_count_search_form {
        overflow: auto;
    }

    .select2-container--default .select2-selection--single {
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
        animation: category-wise-count-spin 1s linear infinite;
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
       FILTER CARD
    ============================================ */

    .category-wise-count-filter-card {
        background: linear-gradient(135deg, #f8f9fc 0%, #ffffff 100%);
        border: 1px solid #eef0f5;
        border-radius: 16px;
        padding: 22px 24px 6px;
        box-shadow: 0 4px 14px rgba(30, 41, 59, 0.06);
        transition: box-shadow 0.25s ease;
    }

    .category-wise-count-filter-card:hover {
        box-shadow: 0 8px 22px rgba(30, 41, 59, 0.1);
    }

    .category-wise-count-select-box label.form-label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
        font-size: 13px;
        color: #4a5568;
        letter-spacing: 0.2px;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .category-wise-count-select-box label.form-label i {
        color: #4e73f8;
        font-size: 13px;
    }

    .category-wise-count-select-box .select2-container--default .select2-selection--multiple {
        border: 1px solid #dde1ea;
        border-radius: 10px;
        min-height: 42px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .category-wise-count-select-box .select2-container--default .select2-selection--multiple:hover {
        border-color: #4e73f8;
    }

    .category-wise-count-select-box .select2-container--default.select2-container--focus .select2-selection--multiple,
    .category-wise-count-select-box .select2-container--default.select2-container--open .select2-selection--multiple {
        border-color: #4e73f8;
        box-shadow: 0 0 0 3px rgba(78, 115, 248, 0.15);
    }

    /* ============================================
       COUNT BADGE
    ============================================ */

    .category-wise-count-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 700;
        color: #fff;
        background: #4e73f8;
    }
</style>

<div class="loader-overlay" id="categoryWiseCountGlobalLoader">
    <div class="loader-container">
        <div class="loader"></div>
        <p class="loader-text">Loading...</p>
        <p class="loader-subtext">Please wait while we process your request</p>
    </div>
</div>
