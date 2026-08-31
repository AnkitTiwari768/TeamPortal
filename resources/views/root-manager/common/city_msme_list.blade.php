@extends('components.admin.content-layout')

@section('card-content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<div class="card-body">
   
    <!-- Tab Navigation -->
    <ul class="nav nav-tabs nav-fill mb-4" id="msmeTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="total-tab" data-bs-toggle="tab" data-bs-target="#total" type="button" role="tab"  style="background: #25c8b0; color: #fff;">
                <i class="fas fa-chart-bar me-2"></i>
                Total MSME Count
                <span class="badge bg-secondary ms-2" id="totalCount">0</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="female-tab" data-bs-toggle="tab" data-bs-target="#female" type="button" role="tab" style="background: #339788; color: #fff;">
                <i class="fas fa-female me-2"></i>
                Female MSME Count
                <span class="badge bg-secondary ms-2" id="femaleCount">0</span>
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content">
        <!-- Total MSME Tab -->
        <div class="tab-pane fade" id="total" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-bordered table-hover w-100" id="totalDataTable">
                    <thead class="table-dark">
                        <tr>
                            <th width="5%">#</th>
                            <th width="60%">Tier-1 City</th>
                            <th width="35%">Total Registrations</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <!-- Female MSME Tab -->
        <div class="tab-pane fade show active" id="female" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-bordered table-hover w-100" id="femaleDataTable">
                    <thead class="table-dark">
                        <tr>
                            <th width="5%">#</th>
                            <th width="60%">Tier-1 City</th>
                            <th width="35%">Female Registrations</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
@include('common.libraries')
@include('common.datatables-css')

@endsection

@section('js')
@include('common.datatables-js')

<script>
$(document).ready(function() {
    // Show success message on page load
    if (typeof showToast !== 'undefined') {
        showToast('Data loaded successfully!', 'success');
    }

    function applyRowColors(api, countField) {
        api.rows().every(function() {
            var count = this.data()[countField];
            $(this.node()).removeClass('row-high row-medium row-low row-very-low');
            if (count >= 100) $(this.node()).addClass('row-high');
            else if (count >= 50) $(this.node()).addClass('row-medium');
            else if (count >= 20) $(this.node()).addClass('row-low');
            else $(this.node()).addClass('row-very-low');
        });
    }

    function updateBadge(table, badgeId) {
        var count = table.rows().count();
        var badge = $(badgeId).text(count);
        badge.removeClass('badge-count-high badge-count-medium badge-count-low bg-secondary');
        if (count > 50) badge.addClass('badge-count-high');
        else if (count > 20) badge.addClass('badge-count-medium');
        else if (count > 0) badge.addClass('badge-count-low');
        else badge.addClass('bg-secondary');
    }

    var commonCols = [
        { data: null, className: 'text-center', render: (d, t, r, m) => m.row + 1 },
        { data: 'tier_1_city', className: 'text-capitalize' }
    ];

    // Total Table
    var totalTable = $('#totalDataTable').DataTable({
        processing: true, serverSide: false,
        ajax: { 
            url: "{{ route('total-msme-datalist') }}", 
            type: "GET", 
            dataSrc: 'data',
            error: function() {
                if (typeof showToast !== 'undefined') {
                    showToast('Error loading total MSME data!', 'error');
                }
            }
        },
        columns: [
            commonCols[0], commonCols[1],
            { data: 'total_mse_registrations', className: 'text-end fw-bold',
              render: (d) => `<span class="badge ${d>=100?'bg-success':d>=50?'bg-info':d>=20?'bg-warning':'bg-secondary'} rounded-pill fs-6 p-2"><i class="fas fa-chart-bar me-1"></i> ${parseInt(d).toLocaleString('en-IN')}</span>` }
        ],
        order: [[2, 'desc']],
        dom: '<"row"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        buttons: [
            { extend: 'csvHtml5', text: '<i class="fas fa-file-csv me-2"></i> CSV', className: 'btn btn-success btn-sm', exportOptions: { columns: [0,1,2] } },
            { extend: 'excelHtml5', text: 'Excel', className: 'btn btn-primary btn-sm', exportOptions: { columns: [0,1,2] } },
            { text: '<i class="fas fa-sync-alt me-2"></i> Reload', className: 'btn btn-info btn-sm', action: (e, dt) => dt.ajax.reload() }
        ],
        language: { search: ' Search:', searchPlaceholder: "Search by city name...", lengthMenu: '<i class="fas fa-list me-1"></i> Show _MENU_ entries' },
        drawCallback: function() { applyRowColors(this.api(), 'total_mse_registrations'); updateBadge(this.api(), '#totalCount'); },
        initComplete: function() { 
            $('.dataTables_filter input').addClass('form-control form-control-sm'); 
            $('.dataTables_length select').addClass('form-select form-select-sm');
            if (typeof showToast !== 'undefined') {
                showToast('Total MSME table loaded!', 'success');
            }
        }
    });

    // Female Table
    var femaleTable = $('#femaleDataTable').DataTable({
        processing: true, serverSide: false,
        ajax: { 
            url: "{{ route('city-msme-datalist') }}", 
            type: "GET", 
            dataSrc: 'data',
            error: function() {
                if (typeof showToast !== 'undefined') {
                    showToast('Error loading female MSME data!', 'error');
                }
            }
        },
        columns: [
            commonCols[0], commonCols[1],
            { data: 'female_mse_registrations', className: 'text-end fw-bold',
              render: (d) => `<span class="badge ${d>=100?'bg-success':d>=50?'bg-info':d>=20?'bg-warning':'bg-secondary'} rounded-pill fs-6 p-2"><i class="fas fa-female me-1"></i> ${parseInt(d).toLocaleString('en-IN')}</span>` }
        ],
        order: [[2, 'desc']],
        dom: '<"row"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        buttons: [
            { extend: 'csvHtml5', text: '<i class="fas fa-file-csv me-2"></i> CSV', className: 'btn btn-success btn-sm', exportOptions: { columns: [0,1,2] } },
            { extend: 'excelHtml5', text: 'Excel', className: 'btn btn-primary btn-sm', exportOptions: { columns: [0,1,2] } },
            { text: '<i class="fas fa-sync-alt me-2"></i> Reload', className: 'btn btn-info btn-sm', action: (e, dt) => dt.ajax.reload() }
        ],
        language: { search: ' Search:', searchPlaceholder: "Search by city name...", lengthMenu: '<i class="fas fa-list me-1"></i> Show _MENU_ entries' },
        drawCallback: function() { applyRowColors(this.api(), 'female_mse_registrations'); updateBadge(this.api(), '#femaleCount'); },
        initComplete: function() { 
            $('.dataTables_filter input').addClass('form-control form-control-sm'); 
            $('.dataTables_length select').addClass('form-select form-select-sm');
        }
    });

    // Tab change events with toast
    $('#total-tab').on('shown.bs.tab', function() { 
        totalTable.ajax.reload(); 
        $(this).find('.badge').removeClass('bg-secondary').addClass('bg-light text-dark'); 
        $('#female-tab').find('.badge').removeClass('bg-light text-dark').addClass('bg-secondary');
        if (typeof showToast !== 'undefined') {
            showToast('Loading total MSME data...', 'success');
        }
    });
    
    $('#female-tab').on('shown.bs.tab', function() { 
        femaleTable.ajax.reload(); 
        $(this).find('.badge').removeClass('bg-secondary').addClass('bg-light text-dark'); 
        $('#total-tab').find('.badge').removeClass('bg-light text-dark').addClass('bg-secondary');
        if (typeof showToast !== 'undefined') {
            showToast('Loading female MSME data...', 'success');
        }
    });
});
</script>
@endsection