<script>
// DataTable Common Configuration
var DataTableConfig = {
    defaults: {
        processing: true,
        serverSide: false,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        // Fixed dom - buttons, length menu, and filter in same row
        dom: '<"row"<"col-sm-12 col-md-3"B><"col-sm-12 col-md-3"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            search: 'Search:',
            searchPlaceholder: "Search...",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "No entries found",
            infoFiltered: "(filtered from _MAX_ total entries)",
            zeroRecords: "No matching records found",
            paginate: {
                first: '<i class="fas fa-angle-double-left"></i>',
                last: '<i class="fas fa-angle-double-right"></i>',
                next: '<i class="fas fa-angle-right"></i>',
                previous: '<i class="fas fa-angle-left"></i>'
            }
        },
        drawCallback: function() {
            $('#snpMsmeTable tbody tr, #stateMsmeTable tbody tr, #msmeSnpTable tbody tr, #totalDataTable tbody tr, #femaleDataTable tbody tr').each(function(index) {
                $(this).css('animation', 'fadeIn 0.3s ease ' + (index * 0.05) + 's');
            });
            $('.dataTables_paginate > .pagination').addClass('flex-wrap justify-content-end');
        },
        initComplete: function() {
            $('.dataTables_filter input').addClass('form-control form-control-sm');
            $('.dataTables_length select').addClass('form-select form-select-sm');
            // Add icon to search label
            $('.dataTables_filter label').contents().first().replaceWith('Search: ');
            // Style buttons container
            $('.dt-buttons').addClass('d-flex gap-2');
        }
    },
    
    getButtons: function(buttonsArray) {
        var buttons = {
            csv: {
                extend: 'csvHtml5',
                text: '<i class="fas fa-file-csv me-2"></i> CSV',
                className: 'btn btn-success btn-sm',
                exportOptions: { columns: ':visible' }
            },
            excel: {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel me-2"></i> Excel',
                className: 'btn btn-primary btn-sm',
                exportOptions: { columns: ':visible' }
            },
            reload: {
                text: '<i class="fas fa-sync-alt me-2"></i> Reload',
                className: 'btn btn-info btn-sm',
                action: function(e, dt, node, config) {
                    dt.ajax.reload();
                    showToast('Data reloaded successfully!', 'success');
                }
            },
          
        };
        
        var result = [];
        buttonsArray.forEach(function(btn) {
            if (buttons[btn]) result.push(buttons[btn]);
        });
        return result;
    }
};

// Helper function to create badge with icons
function createBadge(value, icon, type) {
    var badgeClass = 'badge-open';
    var iconClass = icon;
    
    if (type === 'direct') {
        badgeClass = 'badge-direct';
        iconClass = icon || 'hand-pointer';
    } else if (type === 'onboarded') {
        badgeClass = 'badge-onboarded';
        iconClass = icon || 'check-circle';
    } else if (type === 'primary') {
        badgeClass = 'badge-open';
        iconClass = icon || 'chart-line';
    }
    
    if (!iconClass.startsWith('fa-')) {
        iconClass = 'fa-' + iconClass;
    }
    
    return '<span class="badge ' + badgeClass + ' rounded-pill fs-6 p-2">' +
           '<i class="fas ' + iconClass + ' me-1"></i> ' + 
           parseInt(value).toLocaleString('en-IN') + 
           '</span>';
}

// Toast notification with icons
function showToast(message, type) {
    var icon = type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle');
    var bgClass = type === 'success' ? 'bg-success' : (type === 'error' ? 'bg-danger' : 'bg-info');
    
    var toastHtml = `
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
            <div class="toast align-items-center text-white ${bgClass} border-0" role="alert" data-bs-autohide="true" data-bs-delay="3000">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas ${icon} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        </div>
    `;
    
    $('body').append(toastHtml);
    var toast = new bootstrap.Toast($('.toast').last());
    toast.show();
    $('.toast').on('hidden.bs.toast', function() { 
        $(this).parent().remove(); 
    });
}

// Update badge count
function updateBadgeCount(table, badgeId) {
    if (table && table.rows) {
        var count = table.rows().count();
        $(badgeId).text(count);
    }
}

// Function to check if Font Awesome is loaded
function checkFontAwesome() {
    if (typeof $ !== 'undefined') {
        var testIcon = $('<i class="fas fa-home"></i>');
        $('body').append(testIcon);
        var width = testIcon.width();
        testIcon.remove();
        
        if (width === 0) {
            console.warn('Font Awesome icons may not be loaded properly');
            $('head').append('<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.5.1/css/all.css">');
        }
    }
}

// Run when document is ready
$(document).ready(function() {
    checkFontAwesome();
});
</script>