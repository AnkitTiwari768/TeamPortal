<!-- Font Awesome -->
<link rel="stylesheet"
   href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
<style>
   .dt-button.buttons-excel {
   background: #198754 !important;
   border: none !important;
   color: #fff !important;
   padding: 8px 18px !important;
   border-radius: 8px !important;
   font-weight: 600 !important;
   }
   .dt-button.buttons-pdf {
   background: #dc3545 !important;
   border: none !important;
   color: #fff !important;
   padding: 8px 18px !important;
   border-radius: 8px !important;
   font-weight: 600 !important;
   }
   .dt-button:hover {
   opacity: 0.9;
   }
   /* =========================
   Tier Filter Design
   ========================== */
   .custom-filter {
   height: 48px !important;
   border-radius: 10px !important;
   font-size: 16px !important;
   font-weight: 500;
   border: 1px solid #ced4da !important;
   padding-left: 14px !important;
   min-width: 250px;
   }
   .custom-filter:focus {
   box-shadow: 0 0 0 0.15rem rgba(13,110,253,.20) !important;
   border-color: #0d6efd !important;
   }
   /* =========================
   Search Button
   ========================== */
   .search-btn {
   background: #0d6efd !important;
   color: #fff !important;
   border: none !important;
   padding: 11px 24px !important;
   border-radius: 10px !important;
   font-size: 16px;
   font-weight: 600;
   }
   .search-btn:hover {
   background: #0b5ed7 !important;
   color: #fff !important;
   }
   /* =========================
   Reset Button
   ========================== */
   .reset-btn {
   background: #6c757d !important;
   color: #fff !important;
   border: none !important;
   padding: 11px 24px !important;
   border-radius: 10px !important;
   font-size: 16px;
   font-weight: 600;
   }
   .reset-btn:hover {
   background: #5c636a !important;
   color: #fff !important;
   }
   /* =========================
   Export Buttons
   ========================== */
   div.dt-buttons {
   display: inline-flex;
   gap: 14px;
   margin-left: -50%;
   align-items: center;
   }
   /* Remove default datatable style */
   .dt-button {
   margin-right: 0 !important;
   }
   /* Table Header */
   #dataTable thead {
   background: #1f4b8f;
   color: #fff;
   }
   #dataTable thead th {
   font-size: 15px;
   font-weight: 600;
   white-space: nowrap;
   }



       /* Show custom loader when processing */

    .table-loading-container {
        position: relative;
    }

    #table-loader {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        z-index: 100;
        display: none;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        border-radius: 4px;
        min-height: 200px;
    }

    .modern-spinner {
        width: 50px;
        height: 50px;
        border: 4px solid rgba(0, 123, 255, 0.1);
        border-left-color: #007bff;
        border-right-color: #007bff;
        border-radius: 50%;
        animation: spin-loader 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
        box-shadow: 0 0 10px rgba(0, 123, 255, 0.15);
    }

    .loading-text {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        font-size: 1rem;
        color: #495057;
        font-weight: 550;
        margin-top: 15px;
        letter-spacing: 0.5px;
        animation: pulse-loader 1.8s ease-in-out infinite;
    }

    @keyframes spin-loader {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    @keyframes pulse-loader {
        0%, 100% { opacity: 0.6; }
        50% { opacity: 1; }
    }

    /* Hide default Datatables processing label */
    .dataTables_processing {
        display: none !important;
    }
 /* Show custom loader when processing */


.table-responsive {
    overflow-x: auto;
    white-space: nowrap;
}

#dataTable th,
#dataTable td {
    white-space: nowrap;
    vertical-align: middle;
}

.btn-secondary {
    --bs-btn-color: #fff;
    --bs-btn-bg: #784a4a !important;
    --bs-btn-border-color: #6c757d;
    --bs-btn-hover-color: #fff;
    --bs-btn-hover-bg: #5c636a;
    --bs-btn-hover-border-color: #565e64;
    --bs-btn-focus-shadow-rgb: 130, 138, 145;
    --bs-btn-active-color: #fff;
    --bs-btn-active-bg: #565e64;
    --bs-btn-active-border-color: #51585e;
    --bs-btn-active-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.125);
    --bs-btn-disabled-color: #fff;
    --bs-btn-disabled-bg: #6c757d;
    --bs-btn-disabled-border-color: #6c757d;
}
</style>