@extends('components.admin.content-layout')
@section('card-content')

<!-- Back Button and Download Errors Button on Top-Right -->
<div class="d-flex justify-content-end mb-3" style="margin-top: -51px;">
    <button id="downloadErrorsBtn" class="btn btn-danger me-2" disabled>
        <i class="fa fa-download me-1"></i> Download Errors
        <span id="errorCountBadge" class="badge bg-light text-dark ms-1">0</span>
    </button>
    @if(acl('can-add'))
    <a href="{{ route('pmv-bulk-upload') }}" class="btn btn-success me-2">
        <i class="fa fa-upload me-1"></i> PMV Bulk Upload
    </a>
    @endif
  
</div>

<div class="card-body">
   <div class="row mb-3">
      <div class="col-md-3">
         <label><b>From Date</b></label>
        <input type="text" class="form-control filter_btn" name="from_date" id="fromDate" placeholder="From Date">
      </div>

      <div class="col-md-3">
         <label><b>To Date</b></label>
        <input type="text" class="form-control filter_btn" name="to_date" id="toDate" placeholder="To Date">        
      </div>
      
      <div class="col-md-3">
         <label><b>Type</b></label>
         <select id="bulkFilter" class="form-select">
            <option value="">All</option>
            <option value="0">Individual</option>
            <option value="1">Bulk</option>
         </select>
      </div>

      <div class="col-md-2 d-flex align-items-end">
         <button id="filterBtn" class="btn btn-primary me-2">Filter</button>
         <button id="resetBtn" class="btn btn-secondary">Reset</button>
      </div>
   </div>

   <div class="table-responsive">
      <table class="table table-bordered table-striped table-hover" id="dataTable" width="100%">
         <thead class="table-dark">
            <tr>
               <th width="40">
                  <input type="checkbox" id="selectAll">
               </th>
               <th>SN</th>
               <th>Category Name</th>
               <th width="120">Owner Name</th>
               <th>Store Name</th>
               <th>Mobile</th>
               <th>Email</th>
               <th>Type</th>
               <th>Is Bulk</th>
               <th>Created Date</th>
            </tr>
         </thead>
      </table>
   </div>
</div>

@endsection

@section('js')
<script>
    // ============================================
    // DEBOUNCE FUNCTION
    // ============================================
    function debounce(func, wait) {
        var timeout;
        return function() {
            var context = this,
                args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                func.apply(context, args);
            }, wait);
        };
    }

    // ============================================
    // SHOW/HIDE AJAX LOADER
    // ============================================
    function showLoader() {
        $('#ajax-loader').show();
    }

    function hideLoader() {
        $('#ajax-loader').hide();
    }

    // ============================================
    // GET DATATABLE INSTANCE
    // ============================================
    function getTable() {
        return $('#dataTable').DataTable();
    }

    // ============================================
    // RELOAD TABLE
    // ============================================
    function reloadTable() {
        var oTable = getTable();
        oTable.ajax.reload(null, false);
    }

    // ============================================
    // UPDATE ERROR BUTTON STATE
    // ============================================
    function updateErrorButton(count) {
        var $btn = $('#downloadErrorsBtn');
        
        // Update badge
        $('#errorCountBadge').text(count);
        
        if (count > 0) {
            // Enable button and show badge
            $btn.prop('disabled', false);
            $('#errorCountBadge').show();
            $btn.removeClass('btn-secondary').addClass('btn-danger');
            // Restore original button HTML
            $btn.html('<i class="fa fa-download me-1"></i> Download Errors <span id="errorCountBadge" class="badge bg-light text-dark ms-1">' + count + '</span>');
        } else {
            // Disable button and hide badge
            $btn.prop('disabled', true);
            $('#errorCountBadge').hide();
            $btn.removeClass('btn-danger').addClass('btn-secondary');
            $btn.html('<i class="fa fa-download me-1"></i> Download Errors <span id="errorCountBadge" class="badge bg-light text-dark ms-1">0</span>');
        }
    }

    // ============================================
    // CHECK ERROR COUNT
    // ============================================
    function checkErrorCount() {
        showLoader();
        $.ajax({
            url: "{{ route('pmv.error.count') }}",
            type: "GET",
            success: function(response) {
                var count = response.count || 0;
                updateErrorButton(count);
                hideLoader();
            },
            error: function() {
                updateErrorButton(0);
                hideLoader();
            }
        });
    }

    // ============================================
    // DOCUMENT READY
    // ============================================
    $(document).ready(function() {
        // Show loader initially
        showLoader();

        // ============================================
        // DATE PICKER INITIALIZATION
        // ============================================
        $("#fromDate").datepicker({
            dateFormat: "dd-mm-yy",
            changeYear: true,
            changeMonth: true,
            onSelect: function(selected) {
                $("#toDate").datepicker("option", "minDate", selected);
            }
        });
        
        $("#toDate").datepicker({
            dateFormat: "dd-mm-yy",
            changeYear: true,
            changeMonth: true,
            onSelect: function(selected) {
                $("#fromDate").datepicker("option", "maxDate", selected);
            }
        });

        // ============================================
        // INITIALIZE DATATABLE
        // ============================================
        let table = $('#dataTable').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            pageLength: 10,
            lengthMenu: [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ],
            
            dom: "<'row'<'col-sm-12 col-md-6'lB><'col-sm-12 col-md-6'f>>" +
                "<'row dt-row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",

            ajax: {
                url: "{{ url('pmvDataList') }}",
                type: "GET",
                data: function(d) {
                    d.from_date = $('#fromDate').val();
                    d.to_date = $('#toDate').val();
                    d.is_bulk = $('#bulkFilter').val();
                },
                beforeSend: function() {
                    showLoader();
                },
                complete: function() {
                    hideLoader();
                }
            },

            order: [
                [9, 'desc']
            ],

            columns: [
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    render: function(data) {
                        return '<input type="checkbox" class="rowCheckbox" value="' + data + '">';
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    width: "60px",
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'category_name',
                    defaultContent: '-'
                },
                {
                    data: 'owner_name',
                    defaultContent: '-'
                },
                {
                    data: 'store_name',
                    defaultContent: '-'
                },
                {
                    data: 'mobile',
                    defaultContent: '-'
                },
                {
                    data: 'email',
                    defaultContent: '-'
                },
                {
                    data: 'type',
                    render: function(data) {
                        if (data == 1) return '<span class="badge bg-primary">SNP</span>';
                        if (data == 2) return '<span class="badge bg-info text-dark">IA</span>';
                        if (data == 3) return '<span class="badge bg-secondary">Individual</span>';
                        return '-';
                    }
                },
                {
                    data: 'is_bulk',
                    render: function(data) {
                        return data == 1
                            ? '<span class="badge bg-success">Bulk</span>'
                            : '<span class="badge bg-warning text-dark">Individual</span>';
                    }
                },
                {
                    data: 'created_at',
                    width: "150px"
                }
            ],

            buttons: [
                {
                    extend:'excelHtml5',
                    text:'<i class="fa fa-file-excel-o" aria-hidden="true"></i> Excel',
                    exportOptions:{
                        columns:[1,2,3,4,5,6,7,8,9],
                        rows:function(idx,data,node){
                            let selected = $('.rowCheckbox:checked').length;
                            if(selected === 0){
                                return true;
                            }
                            return $(node).find('.rowCheckbox').prop('checked');
                        }
                    }
                },
                {
                    extend:'pdfHtml5',
                    text:'<i class="fa fa-file-pdf-o" aria-hidden="true"></i> Pdf',
                    orientation:'landscape',
                    pageSize:'A4',
                    exportOptions:{
                        columns:[1,2,3,4,5,6,7,8,9],
                        rows:function(idx,data,node){
                            let selected = $('.rowCheckbox:checked').length;
                            if(selected === 0){
                                return true;
                            }
                            return $(node).find('.rowCheckbox').prop('checked');
                        }
                    }
                }
            ]
        });

        // ============================================
        // DATATABLE EVENTS FOR LOADER
        // ============================================
        $(document).on('preXhr.dt', '#dataTable', function() {
            showLoader();
        });

        $(document).on('xhr.dt draw.dt', '#dataTable', function() {
            hideLoader();
        });

        // ============================================
        // SEARCH WITH DEBOUNCE
        // ============================================
        $(document).on('init.dt', '#dataTable', function() {
            var oTable = getTable();
            var searchInput = $('.dataTables_filter input');
            
            // Remove existing event handlers
            searchInput.off('keyup input');
            
            // Apply debounced search
            searchInput.on('keyup input', debounce(function() {
                showLoader();
                oTable.search(this.value).draw();
            }, 500));
        });

        // ============================================
        // FILTER BUTTON WITH DEBOUNCE
        // ============================================
        var debouncedFilter = debounce(function() {
            reloadTable();
        }, 300);

        $('#filterBtn').on('click', function() {
            debouncedFilter();
        });

        // ============================================
        // BULK FILTER CHANGE WITH DEBOUNCE
        // ============================================
        $('#bulkFilter').on('change', debounce(function() {
            reloadTable();
        }, 300));

        // ============================================
        // DATE CHANGES WITH DEBOUNCE
        // ============================================
        $('#fromDate, #toDate').on('change', debounce(function() {
            reloadTable();
        }, 300));

        // ============================================
        // RESET BUTTON
        // ============================================
        $('#resetBtn').on('click', function() {
            $('#fromDate').val('');
            $('#toDate').val('');
            $('#bulkFilter').val('');
            // Reset datepicker limits
            $("#fromDate").datepicker("option", "maxDate", null);
            $("#toDate").datepicker("option", "minDate", null);
            // Clear search input
            $('.dataTables_filter input').val('');
            var oTable = getTable();
            oTable.search('').draw();
            oTable.ajax.reload(null, false);
        });

        // ============================================
        // SELECT ALL CHECKBOX
        // ============================================
        $('#selectAll').on('click', function() {
            let rows = table.rows({
                'search': 'applied'
            }).nodes();
            $('input.rowCheckbox', rows).prop('checked', this.checked);
        });

        // ============================================
        // DOWNLOAD ERRORS BUTTON
        // ============================================
        $('#downloadErrorsBtn').on('click', function() {
            var $btn = $(this);
            
            // Check if button is disabled
            if ($btn.prop('disabled')) {
                toastr.warning('No failed records available to download.');
                return;
            }
            
            // Show loading state
            $btn.html('<i class="fa fa-spinner fa-spin me-1"></i> Downloading...');
            $btn.prop('disabled', true);
            
            showLoader();
            
            // Get error count again before download
            $.ajax({
                url: "{{ route('pmv.error.count') }}",
                type: "GET",
                success: function(response) {
                    if (response.count == 0) {
                        toastr.warning('No failed records found to download.');
                        updateErrorButton(0);
                        hideLoader();
                        return;
                    }
                    
                    // If errors exist, download the file in same tab
                    var downloadUrl = "{{ route('pmv.download.errors.excel') }}";
                    
                    // Use hidden iframe or form submit for same tab download
                    var $form = $('<form>', {
                        action: downloadUrl,
                        method: 'GET',
                        target: '_self'
                    });
                    
                    $('body').append($form);
                    $form.submit();
                    $form.remove();
                    
                    toastr.success('Error records downloaded successfully!');
                    hideLoader();
                    
                    // Reset button after 2 seconds
                    setTimeout(function() {
                        checkErrorCount();
                    }, 2000);
                },
                error: function() {
                    toastr.error('Failed to download error records');
                    hideLoader();
                    checkErrorCount();
                }
            });
        });

        // ============================================
        // CHECK ERROR COUNT ON PAGE LOAD
        // ============================================
        checkErrorCount();

        // ============================================
        // AUTO REFRESH ERROR COUNT AFTER IMPORT
        // ============================================
        $(document).on('importComplete', function() {
            checkErrorCount();
        });

        // ============================================
        // HIDE LOADER AFTER INITIAL LOAD
        // ============================================
        setTimeout(function() {
            hideLoader();
        }, 1500);

    }); // END DOCUMENT READY
</script>



@endsection