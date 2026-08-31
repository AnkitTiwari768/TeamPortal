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
        animation: snp-category-list-spin 1s linear infinite;
    }

    @keyframes snp-category-list-spin {
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

    form#snp_category_list_search_form {
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
        animation: snp-category-list-spin 1s linear infinite;
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

    .snp-category-list-filter-card {
        background: linear-gradient(135deg, #f8f9fc 0%, #ffffff 100%);
        border: 1px solid #eef0f5;
        border-radius: 16px;
        padding: 22px 24px 6px;
        box-shadow: 0 4px 14px rgba(30, 41, 59, 0.06);
        transition: box-shadow 0.25s ease;
    }

    .snp-category-list-filter-card:hover {
        box-shadow: 0 8px 22px rgba(30, 41, 59, 0.1);
    }

    .snp-category-list-select-box label.form-label {
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

    .snp-category-list-select-box label.form-label i {
        color: #4e73f8;
        font-size: 13px;
    }

    .snp-category-list-select-box .select2-container--default .select2-selection--single {
        border: 1px solid #dde1ea;
        border-radius: 10px;
        height: 42px;
        display: flex;
        align-items: center;
        padding: 0 8px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .snp-category-list-select-box .select2-container--default .select2-selection--single:hover {
        border-color: #4e73f8;
    }

    .snp-category-list-select-box .select2-container--default.select2-container--focus .select2-selection--single,
    .snp-category-list-select-box .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #4e73f8;
        box-shadow: 0 0 0 3px rgba(78, 115, 248, 0.15);
    }

    .snp-category-list-select-box .select2-selection__rendered {
        line-height: 40px !important;
        color: #2d3748 !important;
    }

    .snp-category-list-select-box .select2-selection__arrow {
        height: 40px !important;
    }

    /* ============================================
       EXPORT BUTTONS
    ============================================ */

    .snp-category-list-export-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .snp-category-list-export-btn {
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

    .snp-category-list-export-btn:hover {
        opacity: 0.9;
        transform: translateY(-1px);
        color: #fff;
    }

    .snp-category-list-export-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .snp-category-list-export-btn--pdf {
        background: linear-gradient(135deg, #ff5f6d 0%, #c0392b 100%);
    }

    .snp-category-list-export-btn--excel {
        background: linear-gradient(135deg, #1d976c 0%, #0f6f4e 100%);
    }

    /* ============================================
       VIEW (EYE) BUTTON
    ============================================ */

    .snp-category-list-view-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border: none;
        border-radius: 50%;
        background: #eef2ff;
        color: #4e73f8;
        cursor: pointer;
        transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
    }

    .snp-category-list-view-btn:hover {
        background: #4e73f8;
        color: #fff;
        transform: scale(1.1);
    }

    .snp-category-list-view-btn:active {
        transform: scale(0.96);
    }

    /* ============================================
       DETAILS MODAL
    ============================================ */

    .snp-category-list-modal-content {
        border-radius: 16px;
        border: none;
        overflow: hidden;
    }

    .snp-category-list-modal-content .modal-header {
        background: linear-gradient(135deg, #4e73f8 0%, #2541b2 100%);
        color: #fff;
        border-bottom: none;
        padding: 18px 24px;
    }

    .snp-category-list-modal-content .modal-header .modal-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
    }

    .snp-category-list-modal-content .btn-close {
        filter: brightness(0) invert(1);
    }

    .snp-category-list-modal-content .modal-body {
        padding: 24px;
    }

    .snp-category-list-detail-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 18px;
    }

    @media (max-width: 576px) {
        .snp-category-list-detail-grid {
            grid-template-columns: 1fr;
        }
    }

    .snp-category-list-detail-item {
        display: flex;
        flex-direction: column;
        gap: 6px;
        padding: 14px 16px;
        background: #f8f9fc;
        border: 1px solid #eef0f5;
        border-radius: 12px;
        transition: box-shadow 0.2s ease, transform 0.2s ease;
    }

    .snp-category-list-detail-item:hover {
        box-shadow: 0 4px 12px rgba(30, 41, 59, 0.08);
        transform: translateY(-2px);
    }

    .snp-category-list-detail-label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        color: #8a94a6;
    }

    .snp-category-list-detail-label i {
        color: #4e73f8;
        width: 14px;
    }

    .snp-category-list-detail-value {
        font-size: 15px;
        font-weight: 600;
        color: #2d3748;
        word-break: break-word;
    }

    /* ============================================
       ROLE BADGES
    ============================================ */

    .snp-category-list-role-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        color: #fff;
    }

    .snp-category-list-role-badge--bnp {
        background: #4e73f8;
    }

    .snp-category-list-role-badge--snp {
        background: #16c79a;
    }

    .snp-category-list-role-badge--lsp {
        background: #b06ab3;
    }
</style>

<div class="loader-overlay" id="snpCategoryListGlobalLoader">
    <div class="loader-container">
        <div class="loader"></div>
        <p class="loader-text">Loading...</p>
        <p class="loader-subtext">Please wait while we process your request</p>
    </div>
</div>
