@extends('components.admin.content-layout')

@section('card-content')
<div class="card-body">
    <div class="filter-section mb-4 p-3 border rounded">
        <h5>Select Period</h5>
        <form id="filterForm">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label>Financial Year</label>
                    <select name="financial_year" id="financial_year" class="form-select" required>
                        <option value="">Select Financial Year</option>
                        @foreach($financialYears as $key => $val)
                            <option value="{{ $key }}" {{ $key == '2026-2027' ? 'selected' : '' }}>{{ $val }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Duration</label>
                    <select name="duration" id="duration" class="form-select">
                        <option value="">Select Duration</option>
                        @foreach($duration as $key => $val)
                            <option value="{{ $key }}">{{ $val }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Sub Duration</label>
                    <select name="sub_duration" id="sub_duration" class="form-select">
                        <option value="">Select Sub Duration</option>
                        @foreach($subDurations as $sd)
                            <option value="{{ $sd->id }}" data-parent="{{ $sd->parent_id }}" class="sub-duration-option">{{ $sd->name }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- <div class="col-md-3">
                    <button type="button" class="btn btn-warning wave-effect has-ripple" id="resetFiltersBtn">
                        <i class="fa fa-undo"></i> Reset
                    </button>
                </div> -->
            </div>
        </form>
    </div>

    <div class="components-section mb-4 p-3 border rounded" style="display: none;" id="componentsContainer">
        <h5 id="componentsTitle">Fund Allocated</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="componentsTable">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Component</th>
                        <th>Duration</th>
                        <th>Sub Duration</th>
                        <th>ALLOCATED AMOUNT</th>
                        <th>RELEASED AMOUNT</th>
                        <th>REMAINING BALANCE</th>
                        <th>ALLOCATE TO (Category > Component)</th>
                        <th>AMOUNT TO BE ALLOCATED (₹)</th>
                        <th>ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Populated by JS -->
                </tbody>
            </table>
        </div>
    </div>

    <div class="history-section mb-4 p-3 border rounded">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Mapping History</h5>
            <!-- <button type="button" class="btn btn-primary btn-sm" id="printHistoryBtn">
                <i class="fa fa-print"></i> Print
            </button> -->
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="historyTable">
                <thead>
                    <tr>
                        <th>FY</th>
                        <th>DURATION</th>
                        <th>SUB DURATION</th>
                        <th>SOURCE (Category > Component)</th>
                        <th>ALLOCATED TO (Category > Component)</th>
                        <th>AMOUNT</th>
                        <th>STATUS</th>
                        <th>CREATED BY</th>
                        <th>CREATED DATE</th>
                        <th>ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $item)
                        <tr>
                            <td>{{ $item->mapping->financial_year ?? '-' }}</td>
                            <td>{{ $attributeNames->get($item->mapping->duration, '-') }}</td>
                            <td>{{ $attributeNames->get($item->mapping->sub_duration, '-') }}</td>
                            <td>{{ $attributeNames->get($item->source_major_component, '-') }} > {{ $attributeNames->get($item->source_sub_component, '-') }}</td>
                            <td>{{ $attributeNames->get($item->target_category, '-') }} > {{ $attributeNames->get($item->target_sub_component, '-') }}</td>
                            <td>₹{{ number_format($item->amount_to_be_allocated, 2) }}</td>
                            <td><span class="badge bg-success">{{ $item->status }}</span></td>
                            <td>{{ $userNames->get($item->created_by, '-') }}</td>
                            <td>{{ $item->created_at->format('d M Y') }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info view-history-btn" 
                                    data-fy="{{ $item->mapping->financial_year ?? '-' }}"
                                    data-duration="{{ $attributeNames->get($item->mapping->duration, '-') }}"
                                    data-subduration="{{ $attributeNames->get($item->mapping->sub_duration, '-') }}"
                                    data-source="{{ $attributeNames->get($item->source_major_component, '-') }} &gt; {{ $attributeNames->get($item->source_sub_component, '-') }}"
                                    data-target="{{ $attributeNames->get($item->target_category, '-') }} &gt; {{ $attributeNames->get($item->target_sub_component, '-') }}"
                                    data-amount="₹{{ number_format($item->amount_to_be_allocated, 2) }}"
                                    data-status="{{ $item->status }}"
                                    data-createdby="{{ $userNames->get($item->created_by, '-') }}"
                                    data-createddate="{{ $item->created_at->format('d M Y') }}">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                      
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('#historyTable').DataTable();
    let allComponents = [];
    let targetDropdownOptions = '<option value="">-- Select Component --</option>';
    const allSubDurations = @json($subDurations);

    // Fetch all components on page load
    $.ajax({
        url: "{{ route('component-utilization.get-components') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}"
        },
        beforeSend: function() {
            $('#ajax-loader').show();
        },
        success: function(response) {
            allComponents = response.components;
            
            response.target_components.forEach(function(category) {
                targetDropdownOptions += `<optgroup label="${category.category_name}">`;
                category.sub_components.forEach(function(sub) {
                    targetDropdownOptions += `<option value="${category.category_id}|${sub.id}">${sub.name}</option>`;
                });
                targetDropdownOptions += `</optgroup>`;
            });
            
            // Re-run filter in case FY is already selected (e.g. browser back button)
            filterComponents();
        },
        complete: function() {
            $('#ajax-loader').hide();
        }
    });

    function filterComponents() {
        let fy = $('#financial_year').val();
        let durationId = $('#duration').val();
        let subDurationSelect = $('#sub_duration');
        let currentSubVal = subDurationSelect.val();

        // Handle sub-duration dropdown logic locally by rebuilding options
        subDurationSelect.empty().append('<option value="">Select Sub Duration</option>');
        
        if (durationId) {
            allSubDurations.forEach(function(sd) {
                if (String(sd.parent_id) === String(durationId)) {
                    let selected = (String(sd.id) === String(currentSubVal)) ? 'selected' : '';
                    subDurationSelect.append(`<option value="${sd.id}" ${selected}>${sd.name}</option>`);
                }
            });
        }
        
        let subDurationId = subDurationSelect.val();

        if (!fy) {
            $('#componentsContainer').hide();
            return;
        }

        // Compare against keys from financialYears array
        let filtered = allComponents.filter(c => String(c.financial_year) === String(fy));
        
        if (durationId) {
            filtered = filtered.filter(c => String(c.duration_id) === String(durationId));
        }
        
        if (subDurationId) {
            filtered = filtered.filter(c => String(c.sub_duration_id) === String(subDurationId));
        }

        renderTable(filtered, fy, durationId, subDurationId);
    }

    function renderTable(components, fy, durationId, subDurationId) {
        $('#componentsContainer').show();
        let title = `Fund Allocated — ${fy}`;
        if(durationId) title += `, ${$('#duration option:selected').text()}`;
        if(subDurationId) title += ` (${$('#sub_duration option:selected').text()})`;
        $('#componentsTitle').text(title);

        let tbody = $('#componentsTable tbody');
        tbody.empty();

        components.forEach(function(comp, index) {
            let tr = `<tr>
                <td>${comp.major_component}</td>
                <td>${comp.sub_component}</td>
                <td>${comp.duration_name}</td>
                <td>${comp.sub_duration_name}</td>
                <td>₹${comp.allocated_amount.toLocaleString('en-IN')}</td>
                <td>₹${comp.released_amount.toLocaleString('en-IN')}</td>
                <td>₹${comp.remaining_balance.toLocaleString('en-IN')}</td>
                <td>
                    <select class="form-select target-select">
                        ${targetDropdownOptions}
                    </select>
                </td>
                <td>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" class="form-control amount-input" data-remaining="${comp.remaining_balance}" value="0">
                    </div>
                </td>
                <td>
                    <button class="btn btn-primary submit-row-btn" data-fy="${comp.financial_year}" data-duration="${comp.duration_id || ''}" data-subduration="${comp.sub_duration_id || ''}" data-major="${comp.major_component_id}" data-sub="${comp.sub_component_id}" data-allocated="${comp.allocated_amount}" data-released="${comp.released_amount}" data-remaining="${comp.remaining_balance}">Submit</button>
                </td>
            </tr>`;
            tbody.append(tr);
        });
    }

    $('#financial_year, #duration, #sub_duration').on('change', function() {
        $('#ajax-loader').show();
        
        // Use a small timeout to allow the browser to render the loader before blocking the thread with filtering/rendering
        setTimeout(function() {
            try {
                filterComponents();
            } catch (error) {
                console.error("Filtering error: ", error);
                if (window.toastr) {
                    toastr.error('An error occurred while filtering data.');
                }
            } finally {
                $('#ajax-loader').hide();
            }
        }, 50);
    });

    $('#resetFiltersBtn').on('click', function() {
        $('#ajax-loader').show();
        
        setTimeout(function() {
            try {
                // Reset dropdowns
                $('#financial_year').val('');
                $('#duration').val('');
                $('#sub_duration').empty().append('<option value="">Select Sub Duration</option>');
                
                // Clear any validation states
                $('.amount-input, .target-select').removeClass('is-invalid');
                $('.invalid-feedback').remove();

                // Clear table and hide container
                $('#componentsTable tbody').empty();
                $('#componentsContainer').hide();
                $('#componentsTitle').text('Components');

                // if (window.toastr) {
                //     toastr.success('Filters reset successfully.');
                // }
            } catch (error) {
                console.error("Reset error: ", error);
                if (window.toastr) {
                    toastr.error('An error occurred while resetting filters.');
                }
            } finally {
                $('#ajax-loader').hide();
            }
        }, 50);
    });

    // Real-time validation for amount field
    $(document).on('input', '.amount-input', function() {
        let input = $(this);
        let amount = parseFloat(input.val()) || 0;
        let remaining = parseFloat(input.data('remaining')) || 0;
        
        // Remove existing validation
        input.removeClass('is-invalid');
        input.closest('.input-group').next('.invalid-feedback').remove();

        if (amount <= 0) {
            input.addClass('is-invalid');
            input.closest('.input-group').after('<div class="invalid-feedback d-block">Amount must be greater than 0.</div>');
        } else if (amount > remaining) {
            input.addClass('is-invalid');
            input.closest('.input-group').after('<div class="invalid-feedback d-block">Cannot exceed remaining balance.</div>');
        }
    });

    // Remove validation messages on correction for target select
    $(document).on('change', '.target-select', function() {
        $(this).removeClass('is-invalid');
        $(this).next('.invalid-feedback').remove();
    });

    $(document).on('click', '.submit-row-btn', function() {
        let btn = $(this);
        let tr = btn.closest('tr');
        let amountInput = tr.find('.amount-input');
        let targetSelect = tr.find('.target-select');
        let amount = parseFloat(amountInput.val()) || 0;
        let remaining = parseFloat(amountInput.data('remaining')) || 0;
        let target = targetSelect.val();

        // Clear existing validation
        amountInput.removeClass('is-invalid');
        targetSelect.removeClass('is-invalid');
        amountInput.closest('.input-group').next('.invalid-feedback').remove();
        targetSelect.next('.invalid-feedback').remove();

        let hasError = false;

        if (amount <= 0) {
            amountInput.addClass('is-invalid');
            amountInput.closest('.input-group').after('<div class="invalid-feedback d-block">Amount must be greater than 0.</div>');
            hasError = true;
        } else if (amount > remaining) {
            amountInput.addClass('is-invalid');
            amountInput.closest('.input-group').after('<div class="invalid-feedback d-block">Cannot exceed remaining balance.</div>');
            hasError = true;
        }

        if (!target) {
            targetSelect.addClass('is-invalid');
            targetSelect.after('<div class="invalid-feedback d-block">Please select a target component.</div>');
            hasError = true;
        }

        if (hasError) {
            toastr.error('Please fix the validation errors.');
            return;
        }

        let targetParts = target.split('|');
        
        let data = {
            _token: "{{ csrf_token() }}",
            financial_year: btn.data('fy'),
            duration: btn.data('duration') || null,
            sub_duration: btn.data('subduration') || null,
            source_major_component: btn.data('major'),
            source_sub_component: btn.data('sub'),
            allocated_amount: btn.data('allocated'),
            released_amount: btn.data('released'),
            remaining_balance: btn.data('remaining'),
            amount_to_be_allocated: amount,
            target_category: targetParts[0],
            target_sub_component: targetParts[1],
            remarks: ''
        };

        $.ajax({
            url: "{{ route('component-utilization.store') }}",
            type: "POST",
            data: data,
            beforeSend: function() {
                $('#ajax-loader').show();
                btn.prop('disabled', true);
            },
            success: function(res) {
                if(res.success) {
                    toastr.success('Component Utilization Mapping Successfully Saved!');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    toastr.error(res.message || 'Something went wrong.');
                    btn.prop('disabled', false);
                }
            },
            error: function(err) {
                toastr.error('Error saving data.');
                btn.prop('disabled', false);
            },
            complete: function() {
                $('#ajax-loader').hide();
            }
        });
    });

    $('#printHistoryBtn').on('click', function() {
        var table = $('#historyTable').DataTable();
        var originalLength = table.page.len();
        
        // Show all rows to ensure everything is printed
        table.page.len(-1).draw();
        
        setTimeout(function() {
            var printContent = document.getElementById('historyTable').outerHTML;
            var win = window.open('', '', 'width=900,height=650');
            win.document.write('<html><head><title>Mapping History</title>');
            win.document.write('<style>');
            win.document.write('body { font-family: sans-serif; margin: 20px; }');
            win.document.write('table { width: 100%; border-collapse: collapse; font-size: 12px; }');
            win.document.write('th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }');
            win.document.write('th { background-color: #f2f2f2; }');
            win.document.write('</style>');
            win.document.write('</head><body>');
            win.document.write('<h2>Mapping History</h2>');
            win.document.write(printContent);
            win.document.write('</body></html>');
            win.document.close();
            
            setTimeout(function() {
                win.print();
                win.close();
                // Revert to original pagination length
                table.page.len(originalLength).draw();
            }, 250);
        }, 100);
    });

    // View Mapping History Modal
    $(document).on('click', '.view-history-btn', function() {
        $('#modal-fy').text($(this).data('fy'));
        $('#modal-duration').text($(this).data('duration'));
        $('#modal-subduration').text($(this).data('subduration'));
        $('#modal-source').text($(this).data('source'));
        $('#modal-target').text($(this).data('target'));
        $('#modal-amount').text($(this).data('amount'));
        $('#modal-status').html('<span class="badge bg-success">' + $(this).data('status') + '</span>');
        $('#modal-createdby').text($(this).data('createdby'));
        $('#modal-createddate').text($(this).data('createddate'));
        
        $('#viewHistoryModal').modal('show');
    });

});
</script>

<div class="modal fade" id="viewHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden;">
            <div class="modal-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                <h5 class="modal-title fw-bold" style="font-size: 1.1rem; color: #1e293b;">
                    <i class="fa fa-list-alt text-primary me-2"></i> Mapping Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row mb-4">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <span class="text-muted fw-semibold d-block mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Financial Year</span>
                        <div class="fw-bold text-dark fs-6" id="modal-fy"></div>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted fw-semibold d-block mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Duration</span>
                        <div class="fw-bold text-dark fs-6" id="modal-duration"></div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <span class="text-muted fw-semibold d-block mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Sub Duration</span>
                        <div class="fw-bold text-dark fs-6" id="modal-subduration"></div>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted fw-semibold d-block mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Amount To Be Allocated</span>
                        <div class="fw-bold text-primary fs-5" id="modal-amount"></div>
                    </div>
                </div>

                <div class="row mb-4 p-3 rounded" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <span class="text-muted fw-semibold d-block mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Source (Category &gt; Component)</span>
                        <div class="fw-bold text-dark fs-6" id="modal-source"></div>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted fw-semibold d-block mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Target (Category &gt; Component)</span>
                        <div class="fw-bold text-dark fs-6" id="modal-target"></div>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <span class="text-muted fw-semibold d-block mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Status</span>
                        <div id="modal-status"></div>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <span class="text-muted fw-semibold d-block mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Created By</span>
                        <div class="fw-bold text-dark" id="modal-createdby"></div>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted fw-semibold d-block mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Created Date</span>
                        <div class="fw-bold text-dark" id="modal-createddate"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection
