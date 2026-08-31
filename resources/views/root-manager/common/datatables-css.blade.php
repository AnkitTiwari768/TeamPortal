<style>
/* Modern DataTables styling */
.dataTables_wrapper {
    padding: 0;
    margin-top: 1rem;
}

/* Card styling */
.card-body {
    padding: 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

/* Card body inner content */
.table-responsive {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

/* Page Header */
.card-title {
    font-size: 1.75rem;
    font-weight: 700;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Table styling */
.table {
    margin-bottom: 0;
    border-radius: 10px;
    overflow: hidden;
}

.table thead th {
    border-bottom: 2px solid #dee2e6;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
    vertical-align: middle;
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: white;
    padding: 1rem;
}

.table tbody tr {
    transition: all 0.3s ease;
}

.table tbody tr:hover {
    background: linear-gradient(90deg, #f8f9fa 0%, #e9ecef 100%);
    transform: translateX(5px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.table tbody td {
    vertical-align: middle;
    padding: 0.875rem;
    font-size: 0.9rem;
}

/* Badge styling */
.badge {
    padding: 6px 12px;
    font-size: 0.8rem;
    font-weight: 600;
    border-radius: 20px;
    display: inline-block;
    min-width: 60px;
    text-align: center;
}

.badge-open {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.badge-direct {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.badge-onboarded {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
}

/* DataTables controls styling */
.dataTables_wrapper .dataTables_length select,
.dataTables_wrapper .dataTables_filter input {
    padding: 0.5rem 0.75rem;
    border-radius: 8px;
    border: 1px solid #ced4da;
    transition: all 0.3s ease;
}

.dataTables_wrapper .dataTables_filter input {
    width: 250px;
    padding-left: 2rem;
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="%236c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>');
    background-repeat: no-repeat;
    background-position: 10px center;
}

.dataTables_wrapper .dataTables_filter input:focus,
.dataTables_wrapper .dataTables_length select:focus {
    border-color: #667eea;
    outline: 0;
    box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
}

/* Button styling */
.dt-buttons .btn {
    border-radius: 8px;
    padding: 0.5rem 1.25rem;
    margin-right: 0.5rem;
    transition: all 0.3s ease;
    font-weight: 600;
    font-size: 0.85rem;
}

.dt-buttons .btn-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
    color: white;
}

.dt-buttons .btn-primary {
    background: linear-gradient(135deg, #007bff 0%, #6610f2 100%);
    border: none;
    color: white;
}

.dt-buttons .btn-info {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    border: none;
    color: white;
}

.dt-buttons .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* Pagination styling */
.dataTables_paginate .pagination {
    gap: 0.25rem;
    margin-top: 1rem;
}

.dataTables_paginate .page-link {
    border-radius: 8px;
    padding: 0.5rem 0.875rem;
    color: #667eea;
    transition: all 0.3s ease;
    border: none;
    margin: 0 2px;
}

.dataTables_paginate .page-link:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    transform: translateY(-2px);
}

.dataTables_paginate .page-item.active .page-link {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-color: transparent;
    transform: scale(1.05);
    color: white;
    box-shadow: 0 4px 10px rgba(102, 126, 234, 0.4);
}

/* Loading overlay */
.dataTables_processing {
    background: rgba(0,0,0,0.8);
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    color: white;
    font-weight: 600;
    padding: 1rem 2rem;
    text-align: center;
}

/* Responsive */
@media (max-width: 768px) {
    .card-body {
        padding: 1rem;
    }
    
    .table-responsive {
        padding: 1rem;
    }
    
    .dt-buttons .btn {
        padding: 0.375rem 0.75rem;
        font-size: 0.75rem;
    }
    
    .dataTables_wrapper .dataTables_filter input {
        width: 180px;
    }
}

/* Animation */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.table-responsive {
    animation: fadeIn 0.5s ease;
}


<style>
/* DataTables styling */
.dataTables_wrapper {
    padding: 0;
    margin-top: 1rem;
}

.card-body {
    padding: 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.table-responsive {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.card-title {
    font-size: 1.75rem;
    font-weight: 700;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.table {
    margin-bottom: 0;
    border-radius: 10px;
    overflow: hidden;
}

.table thead th {
    border-bottom: 2px solid #dee2e6;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
    vertical-align: middle;
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: white;
    padding: 1rem;
}

.table tbody tr {
    transition: all 0.3s ease;
}

.table tbody tr:hover {
    background: linear-gradient(90deg, #f8f9fa 0%, #e9ecef 100%);
    transform: translateX(5px);
}

.table tbody td {
    vertical-align: middle;
    padding: 0.875rem;
    font-size: 0.9rem;
}

/* Badge styling */
.badge {
    padding: 6px 12px;
    font-size: 0.8rem;
    font-weight: 600;
    border-radius: 20px;
    display: inline-block;
    min-width: 60px;
    text-align: center;
}

.badge-open {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.badge-direct {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.badge-onboarded {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
}

/* Checkbox styling */
.form-check-input {
    cursor: pointer;
    width: 18px;
    height: 18px;
    margin: 0;
}

.form-check-input:checked {
    background-color: #667eea;
    border-color: #667eea;
}

.table-active {
    background-color: rgba(102, 126, 234, 0.15) !important;
}

/* Selected actions bar */
.selected-actions-bar {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    padding: 10px 20px;
    border-radius: 50px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    display: none;
    align-items: center;
    gap: 15px;
    animation: slideUp 0.3s ease;
}

.selected-actions-bar.show {
    display: flex;
}

.selected-actions-bar .count {
    font-weight: bold;
    font-size: 16px;
}

.selected-actions-bar .btn {
    padding: 5px 12px;
    font-size: 12px;
    margin: 0;
}

@keyframes slideUp {
    from {
        transform: translateY(100px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Button styling */
.dt-buttons {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}

.dataTables_length {
    display: inline-block;
    margin-left: 15px;
}

.dataTables_filter {
    display: inline-block;
    float: right;
}

.dataTables_filter input {
    margin-left: 8px;
}

/* Responsive */
@media (max-width: 768px) {
    .dataTables_length, .dataTables_filter {
        display: block;
        float: none;
        text-align: center;
        margin: 10px 0;
    }
    
    .selected-actions-bar {
        bottom: 10px;
        right: 10px;
        left: 10px;
        border-radius: 10px;
        justify-content: center;
    }
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.table-responsive {
    animation: fadeIn 0.5s ease;
}
</style>
</style>