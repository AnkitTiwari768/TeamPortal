@extends('components.admin.layout')

@section('page-content')
    <style>
        .form-section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 0.5rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-section-title i {
            color: #3b82f6;
        }

        .form-section-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.01);
            margin-bottom: 1.5rem;
            transition: box-shadow 0.2s ease, border-color 0.2s ease;
        }

        .form-section-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.04), 0 4px 6px -2px rgba(0, 0, 0, 0.02);
        }

        .form-error {
            font-size: 0.8rem;
            font-weight: 500;
            margin-top: 0.25rem;
            display: block;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .card.container-main-card {
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
        }

        .form-action {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
            background: #f8fafc;
            padding: 1.25rem 1.5rem;
            border-top: 1px solid #e2e8f0;
            border-radius: 0 0 12px 12px;
        }

        .doc-badge-container {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            border: 1px solid #e2e8f0;
            padding: 8px 12px;
            border-radius: 8px;
            margin-top: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
            flex-wrap: wrap;
        }

        .doc-badge-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .btn-doc-download {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: #ffffff !important;
            border: none;
            font-size: 0.75rem;
            font-weight: 500;
            padding: 6px 12px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
            text-decoration: none;
        }

        .btn-doc-download:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(37, 99, 235, 0.3);
            filter: brightness(1.1);
        }

        .btn-doc-view {
            background: #ffffff;
            color: #475569 !important;
            border: 1px solid #cbd5e1;
            font-size: 0.75rem;
            font-weight: 500;
            padding: 5px 12px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-doc-view:hover {
            background: #f8fafc;
            color: #1e293b !important;
            border-color: #94a3b8;
            transform: translateY(-1px);
        }
    </style>

    <div class="container-fluid px-4 py-4">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card container-main-card">
                    <div class="card-header d-flex align-items-center py-3" style="background: #ffffff; border-bottom: 1px solid #e2e8f0;">
                        <div class="heading">
                            <h1>{{ $title }}</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0" style="background: none; padding: 0;">
                                    <li class="breadcrumb-item"><a href="{{ url('dashboard') }}" style="color: #64748b; text-decoration: none;">{{ __('fund_flow.home') }}</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('dist.index') }}" style="color: #64748b; text-decoration: none;">{{ __('fund_flow.fund_distribution') }}</a></li>
                                    <li class="breadcrumb-item active" aria-current="page" style="color: #0f172a; font-weight: 500;">{{ $title }}</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="action-header ms-auto">
                            <div class="btn-group drop-btn">
                                <a href="{{ route('dist.index') }}" class="btn btn-secondary">
                                    <i class="fa fa-arrow-left me-1"></i> Back to List
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <form id="dist-form" autocomplete="off">
                            @csrf
                            <input type="hidden" name="id" id="dist-id" value="{{ $id ?? '' }}">

                            <div class="p-4">
                                {{-- ── Section 1: Dimensions ── --}}
                                <div class="form-section-card">
                                    <h5 class="form-section-title">
                                        <i class="fa fa-calendar"></i> 1. Duration And Component
                                    </h5>
                                    <div class="row g-3">
                                        {{-- Financial Year --}}
                                        <div class="col-md-4">
                                            <label class="required form-label fw-semibold small">{{ __('fund_flow.financial_year') }}</label>
                                            <select name="financial_year" id="fy" class="form-select trigger-pool" @disabled(isset($row))>
                                                <option value="">Select Financial Year</option>
                                                @foreach ($financialYears as $key => $value)
                                                    <option value="{{ $key }}" {{ (isset($row) && $row['financial_year'] == $key) ? 'selected' : '' }}>
                                                        {{ $value }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger form-error" id="financial_year_error"></span>
                                        </div>

                                        {{-- Duration --}}
                                        <div class="col-md-4">
                                            <label class="required form-label fw-semibold small">{{ __('fund_flow.duration') }}</label>
                                            <select name="duration_id" id="duration" class="form-select trigger-pool" @disabled(isset($row))>
                                                <option value="">Select Duration</option>
                                                @foreach ($durations as $d)
                                                    <option value="{{ $d->id }}" {{ (isset($row) && $row['duration_id'] == $d->id) ? 'selected' : '' }}>
                                                        {{ $d->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger form-error" id="duration_id_error"></span>
                                        </div>

                                        {{-- Sub-Duration --}}
                                        <div class="col-md-4" id="sub_duration_container" style="{{ (isset($subDurations) && count($subDurations) > 0) ? '' : 'display:none;' }}">
                                            <label class="form-label fw-semibold small">{{ __('fund_flow.sub_duration') }}</label>
                                            <select name="sub_duration_id" id="sub_duration" class="form-select trigger-pool" @disabled(isset($row))>
                                                <option value="">Select Sub Duration</option>
                                                @if(isset($subDurations))
                                                    @foreach($subDurations as $sd)
                                                        <option value="{{ $sd->id }}" {{ (isset($row) && $row['sub_duration_id'] == $sd->id) ? 'selected' : '' }}>
                                                            {{ $sd->name }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <span class="text-danger form-error" id="sub_duration_id_error"></span>
                                        </div>

                                        {{-- Major Component --}}
                                        <div class="col-md-6">
                                            <label class="required form-label fw-semibold small">{{ __('fund_flow.major_component') }}</label>
                                            <select name="major_component_id" id="major_component" class="form-select trigger-pool" @disabled(isset($row))>
                                                <option value="">Select Major Component</option>
                                                @foreach ($majorComponents as $mc)
                                                    <option value="{{ $mc->id }}" {{ (isset($row) && $row['major_component_id'] == $mc->id) ? 'selected' : '' }}>
                                                        {{ $mc->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger form-error" id="major_component_id_error"></span>
                                        </div>

                                        {{-- Sub Component --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold small">{{ __('fund_flow.sub_component') }}</label>
                                            <select name="sub_component_id" id="sub_component" class="form-select trigger-pool" @disabled(isset($row))>
                                                <option value="">Select Sub Component</option>
                                                @if(isset($subComponents))
                                                    @foreach($subComponents as $sc)
                                                        <option value="{{ $sc->id }}" {{ (isset($row) && $row['sub_component_id'] == $sc->id) ? 'selected' : '' }}>
                                                            {{ $sc->name }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <span class="text-danger form-error" id="sub_component_id_error"></span>
                                        </div>
                                    </div>
                                </div>

                                {{-- ── Section 2: Real-time Balance Overview Cards ── --}}
                                <div id="pool-balance-banner" class="row g-3 mb-4" style="display: none;">
                                    {{-- Allocated Amount Card --}}
                                    <div class="col-md-3 col-12">
                                        <div class="card p-3 shadow-sm h-100"
                                            style="border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff; transition: all 0.2s ease;">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="text-muted fw-semibold"
                                                    style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                                    Initial/Fresh Allocated
                                                </span>
                                                <div class="p-2 rounded-circle" style="background: #eff6ff; color: #2563eb;">
                                                    <i class="fa fa-pie-chart" style="font-size: 1.1rem;"></i>
                                                </div>
                                            </div>
                                            <div class="fw-bold text-dark mb-1" style="font-size: 1.5rem;" id="disp-pool-allocated">
                                                ₹0.00
                                            </div>
                                            <small class="text-muted" style="font-size: 0.75rem;">Total Pool Allocation</small>
                                        </div>
                                    </div>

                                    {{-- Amount Received from Other Component Card --}}
                                    <div class="col-md-3 col-12">
                                        <div class="card p-3 shadow-sm h-100"
                                            style="border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff; transition: all 0.2s ease;">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="text-muted fw-semibold"
                                                    style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                                    Amount Received
                                                </span>
                                                <div class="p-2 rounded-circle" style="background: #e0e7ff; color: #4f46e5;">
                                                    <i class="fa fa-arrow-down" style="font-size: 1.1rem;"></i>
                                                </div>
                                            </div>
                                            <div class="fw-bold text-dark mb-1" style="font-size: 1.5rem;" id="disp-pool-received">
                                                ₹0.00
                                            </div>
                                            <small class="text-muted" style="font-size: 0.75rem;">From Other Component</small>
                                        </div>
                                    </div>

                                    {{-- Distributed Amount Card --}}
                                    <div class="col-md-3 col-12">
                                        <div class="card p-3 shadow-sm h-100"
                                            style="border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff; transition: all 0.2s ease;">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="text-muted fw-semibold"
                                                    style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                                    {{ __('fund_flow.distributed_amount') }}
                                                </span>
                                                <div class="p-2 rounded-circle" style="background: #f0fdf4; color: #16a34a;">
                                                    <i class="fa fa-paper-plane" style="font-size: 1.1rem;"></i>
                                                </div>
                                            </div>
                                            <div class="fw-bold text-dark mb-1" style="font-size: 1.5rem;" id="disp-pool-distributed">
                                                ₹0.00
                                            </div>
                                            <small class="text-muted" style="font-size: 0.75rem;">Total Disbursed Funds</small>
                                        </div>
                                    </div>

                                    {{-- Available Balance Card --}}
                                    <div class="col-md-3 col-12">
                                        <div class="card p-3 shadow-sm h-100"
                                            style="border: 2px solid #2563eb; border-radius: 12px; background: #ffffff; transition: all 0.2s ease;">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="text-primary fw-semibold"
                                                    style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                                    {{ __('fund_flow.available_balance') }}
                                                </span>
                                              <div class="p-2 rounded-circle" style="background: #f0fdf4; color: #16a34a;">
                                                    <i class="fa fa-paper-plane" style="font-size: 1.1rem;"></i>
                                                </div>
                                            </div>
                                            <div class="fw-bold text-primary mb-1" style="font-size: 1.5rem;" id="disp-pool-remaining">
                                                ₹0.00
                                            </div>
                                            <small class="text-muted" style="font-size: 0.75rem;">Net Available for Distribution</small>
                                        </div>
                                    </div>
                                </div>

                                {{-- ── Section 3: Numerics Matrix ── --}}
                                <div class="form-section-card">
                                    <h5 class="form-section-title">
                                        <i class="fa fa-calculator"></i> 2. Financial Distribution Matrix
                                    </h5>
                                    <div class="row g-3">
                                        {{-- Distribution Amount --}}
                                        <div class="col-md-4">
                                            <label class="required form-label fw-semibold small">Distribution Amount (₹)</label>
                                            <input type="number" step="0.01" name="distribution_amount" id="amount" class="form-control fw-bold calc-trigger" placeholder="0.00" value="{{ $row['distribution_amount'] ?? '' }}" @disabled(isset($row))>
                                            <span class="text-danger form-error" id="distribution_amount_error"></span>
                                        </div>

                                        {{-- TDS Percentage --}}
                                        <div class="col-md-4 d-none">
                                            <label class="required form-label fw-semibold small">TDS Percentage (%)</label>
                                            <div class="input-group">
                                                <input type="number" step="0.01" name="tds_percentage" id="tds_pct" class="form-control calc-trigger" placeholder="0.00" value="{{ $row['tds_percentage'] ?? '0' }}">
                                                <span class="input-group-text bg-light">%</span>
                                            </div>
                                            <span class="text-danger form-error" id="tds_percentage_error"></span>
                                        </div>

                                        {{-- Calculated display fields --}}
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold small">&nbsp;</label>
                                            <div class="d-flex align-items-center bg-light rounded border border-dashed" style="height:38px;">
                                                <div class="d-flex align-items-center justify-content-center w-50 h-100 small border-end d-none">
                                                    <span class="text-muted lh-1" style="font-size:0.7rem;">TDS Amt: </span> &nbsp;
                                                    <strong id="calc-tds" class="text-danger lh-1">₹{{ number_format((float)($row['tds_amount'] ?? 0), 2) }}</strong>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-center w-100 h-100 small">
                                                    <span class="text-muted lh-1" style="font-size:0.7rem;">Net Pay: </span> &nbsp;
                                                    <strong id="calc-net" class="text-success lh-1">₹{{ number_format((float)($row['net_payable_amount'] ?? 0), 2) }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- ── Section 4: Details & Logistics ── --}}
                                <div class="form-section-card">
                                    <h5 class="form-section-title">
                                        <i class="fa fa-file-text-o"></i> 3. Sanction & Document Details
                                    </h5>
                                    <div class="row g-3">
                                        {{-- Sanction Order Number --}}
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold small">Sanction Order No.</label>
                                            <input type="text" name="sanction_order_number" class="form-control" placeholder="Order details" value="{{ $row['sanction_order_number'] ?? '' }}">
                                            <span class="text-danger form-error" id="sanction_order_number_error"></span>
                                        </div>

                                        {{-- Sanction Order Date --}}
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold small">Sanction Order Date</label>
                                            <input type="text" name="sanction_order_date" id="sanction_order_date" class="form-control datepicker" placeholder="DD-MM-YYYY" autocomplete="off" value="{{ !empty($row['sanction_order_date']) ? date('d-m-Y', strtotime($row['sanction_order_date'])) : '' }}">
                                            <span class="text-danger form-error" id="sanction_order_date_error"></span>
                                        </div>

                                        {{-- Upload Document --}}
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold small">Upload Document</label>
                                            <input type="file" id="file_upload_raw" class="form-control" accept=".pdf,application/pdf">
                                            <input type="hidden" name="upload_document" id="upload_document_hidden" value="{{ $row['upload_document'] ?? '' }}">
                                            <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">{{ __('fund_flow.note_pdf_only') }}</small>
                                            
                                            <div id="upload-status" class="mt-2">
                                                @if(isset($row) && !empty($row['upload_document']))
                                                    <div class="doc-badge-container">
                                                        <span class="doc-badge-label">
                                                            <i class="fa fa-paperclip"></i>
                                                            Current:
                                                        </span>
                                                        <a href="{{ route('dist.document', $row['id']) }}" target="_blank" class="btn-doc-view">
                                                            <i class="fa fa-eye"></i> View Existing Document
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>

                                            <div id="doc-loader" class="d-none mt-1">
                                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                    <span class="visually-hidden">Uploading...</span>
                                                </div>
                                                <span class="ms-1">Uploading...</span>
                                            </div>
                                        </div>

                                        {{-- Remarks --}}
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold small">Remarks</label>
                                            <textarea name="remarks" id="remarks" class="form-control" rows="2" placeholder="Write any additional remarks here...">{{ $row['remarks'] ?? '' }}</textarea>
                                            <span class="text-danger form-error" id="remarks_error"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Ghost Inputs for Disabled Fields on Edit Mode --}}
                            @if(isset($row))
                                <input type="hidden" name="financial_year" value="{{ $row['financial_year'] }}">
                                <input type="hidden" name="duration_id" value="{{ $row['duration_id'] }}">
                                <input type="hidden" name="sub_duration_id" value="{{ $row['sub_duration_id'] }}">
                                <input type="hidden" name="major_component_id" value="{{ $row['major_component_id'] }}">
                                <input type="hidden" name="sub_component_id" value="{{ $row['sub_component_id'] }}">
                                <input type="hidden" name="distribution_amount" value="{{ $row['distribution_amount'] }}">
                            @endif

                            <div class="form-action">
                                <button type="submit" class="btn btn-primary shadow-sm" id="save-btn">
                                    <i class="fa fa-save me-1"></i> {{ isset($row) ? 'Update Record' : 'Submit Transaction' }}
                                </button>
                                <a href="{{ route('dist.index') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- AJAX Loader -->
    <div id="ajax-loader" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.7); z-index: 9999; text-align: center;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
            <i class="fa fa-spinner fa-spin fa-3x" style="color: #2563eb;"></i>
            <div class="mt-2 fw-bold" style="color: #2563eb;">Loading Data...</div>
        </div>
    </div>

@section('js')
    @include('scripts/upload_file')
    <script>
        window.csrfToken = "{{ csrf_token() }}";
        var editMode = {{ isset($row) ? 'true' : 'false' }};

        // Real-time Pool Check
        function checkPoolBalance() {
            var fy = $('#fy').val();
            var dur = $('#duration').val();
            var maj = $('#major_component').val();

            if (fy && dur && maj) {
                $('#ajax-loader').show();
                $.ajax({
                    url: "{{ url('fund-distributions/api/fetch-pool') }}",
                    method: 'POST',
                    data: {
                        _token: window.csrfToken,
                        financial_year: fy,
                        duration_id: dur,
                        sub_duration_id: $('#sub_duration').val(),
                        major_component_id: maj,
                        sub_component_id: $('#sub_component').val()
                    },
                    success: function (res) {
                        var $banner = $('#pool-balance-banner');
                        $banner.slideDown(200);

                        if (res.found) {
                            var receivedAmt = res.utilization_amount ? parseFloat(res.utilization_amount) : 0;
                            $('#disp-pool-received').text('₹' + receivedAmt.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                            $('#disp-pool-remaining').text('₹' + res.remaining_balance.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                            $('#disp-pool-allocated').text('₹' + res.total_allocated.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                            $('#disp-pool-distributed').text('₹' + res.total_distributed.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                            $banner.removeClass('alert-danger border-danger').addClass('alert-light border');
                            $('#disp-pool-remaining').removeClass('text-danger').addClass('text-dark');
                            if (res.remaining_balance < 0) {
                                $('#disp-pool-remaining').addClass('text-danger');
                            }
                        } else {
                            $('#disp-pool-received').text('₹0.00');
                            $('#disp-pool-remaining').text('No Allocation Detected').addClass('text-danger');
                            $('#disp-pool-allocated').text('₹0.00');
                            $('#disp-pool-distributed').text('₹0.00');
                            $banner.removeClass('alert-light').addClass('alert-danger border-danger');
                        }
                    },
                    error: function () {
                        $('#pool-balance-banner').slideUp(100);
                    },
                    complete: function() {
                        $('#ajax-loader').hide();
                    }
                });
            } else {
                $('#pool-balance-banner').slideUp(100);
                $('#ajax-loader').hide();
            }
        }

        // Dropdown Cascade helper
        function loadCascadeDropdown(attrCode, parentId, selectId, placeholder, selectedVal) {
            var $sel = $('#' + selectId);
            $sel.html('<option value="">Loading...</option>').prop('disabled', true);

            if (!parentId) {
                $sel.html('<option value="">' + placeholder + '</option>').prop('disabled', false);
                return;
            }

            $.ajax({
                url: "{{ url('fund-allocation/attribute-values') }}/" + encodeURIComponent(attrCode),
                method: 'GET',
                data: {
                    parent_id: parentId
                },
                headers: {
                    'Accept': 'application/json'
                },
                success: function(result) {
                    var items = Array.isArray(result) ? result : (result.data || []);
                    var html = '<option value="">' + placeholder + '</option>';
                    $.each(items, function(i, item) {
                        var label = item.name || item.attribute_value || '';
                        var sel = selectedVal && String(item.id) === String(selectedVal) ? 'selected' : '';
                        html += '<option value="' + item.id + '" ' + sel + '>' + label + '</option>';
                    });
                    $sel.html(html).prop('disabled', false);
                },
                error: function() {
                    $sel.html('<option value="">' + placeholder + '</option>').prop('disabled', false);
                }
            });
        }

        // Datepicker setup and limits
        function initDatePickerLimits() {
            var fy = $('#fy').val();
            if (!fy) {
                return;
            }
            var parts = fy.split('-');
            if (parts.length === 2) {
                var startYear = parseInt(parts[0], 10);
                var endYear = parseInt(parts[1], 10);

                var minDate = new Date(startYear, 3, 1);
                var maxDate = new Date(endYear, 2, 31);

                var today = new Date();
                if (maxDate > today) {
                    maxDate = today;
                }

                $("#sanction_order_date").datepicker("option", {
                    minDate: minDate,
                    maxDate: maxDate
                });
            }
        }

        $(document).ready(function() {
            // Setup base datepicker
            $("#sanction_order_date").datepicker({
                dateFormat: "dd-mm-yy",
                changeYear: true,
                changeMonth: true,
                maxDate: 0
            });
            initDatePickerLimits();

            // Calculations
            $('.calc-trigger').on('input', function () {
                var amt = parseFloat($('#amount').val()) || 0;
                var pct = parseFloat($('#tds_pct').val()) || 0;
                var tds = (amt * pct) / 100;
                var net = amt - tds;

                $('#calc-tds').text('₹' + tds.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                $('#calc-net').text('₹' + net.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            });

            // Cascading Durations
            $('#duration').on('change', function () {
                var parentId = $(this).val();
                var durationText = $(this).find('option:selected').text().trim();

                $('#sub_duration_container').hide();
                $('#sub_duration').html('<option value="">Select Sub Duration</option>');

                if (!parentId || durationText === 'Yearly') {
                    checkPoolBalance();
                    return;
                }

                loadCascadeDropdown('duration', parentId, 'sub_duration', 'Select Sub Duration', null);
                $('#sub_duration_container').show();
            });

            // Cascading Components
            $('#major_component').on('change', function () {
                var compId = $(this).val();
                loadCascadeDropdown('major-components', compId, 'sub_component', 'Select Sub Component', null);
                checkPoolBalance();
            });

            $('.trigger-pool').on('change', function () {
                checkPoolBalance();
            });

            $('#fy').on('change', function () {
                initDatePickerLimits();
            });

            // PDF uploader
            $('#file_upload_raw').on('change', function() {
                var file = this.files[0];
                if (!file) return;

                if (file.type !== 'application/pdf') {
                    toastr.error('Only PDF files are allowed.');
                    this.value = '';
                    return;
                }

                var maxSize = 5 * 1024 * 1024; // 5MB
                if (file.size > maxSize) {
                    toastr.error("Maximum allowed file size is 5 MB.");
                    $(this).val('');
                    return;
                }

                uploadFile({
                    input: $('#file_upload_raw'),
                    url: "{{ url('fund-distributions/api/upload-asset') }}",
                    fieldName: 'file',
                    loader: $('#doc-loader'),
                    success: function(response) {
                        var fileSystemName = response.data?.file_system_name;
                        $('#upload_document_hidden').val(fileSystemName);
                        $('#upload-status').html(
                            '<div class="doc-badge-container"><span class="doc-badge-label text-success"><i class="fa fa-check"></i> Uploaded Successfully</span></div>'
                        );
                    },
                    error: function() {
                        $('#upload_document_hidden').val('');
                        $('#upload-status').html('<span class="text-danger">File upload failed.</span>');
                    }
                });
            });

            // Form Submit handler
            $('#dist-form').on('submit', function (e) {
                e.preventDefault();
                $('.form-error').text('');
                var $btn = $('#save-btn');
                var initialText = $btn.html();
                $btn.prop('disabled', true).html('<i class="fa fa-circle-o-notch fa-spin"></i> Submitting...');

                var dataId = $('#dist-id').val();
                var postUrl = dataId ? "{{ url('fund-distributions/store') }}/" + dataId : "{{ url('fund-distributions/store') }}";

                // Serialize form values
                var formData = $(this).serializeArray();
                var jsonData = {};
                $.map(formData, function (n, i) {
                    jsonData[n['name']] = n['value'];
                });

                $.ajax({
                    url: postUrl,
                    method: 'POST',
                    data: jsonData,
                    dataType: 'json',
                    success: function(res) {
                        toastr.success(res.message || "Record saved successfully.");
                        window.location.href = "{{ route('dist.index') }}";
                    },
                    error: function(xhr) {
                        $btn.prop('disabled', false).html(initialText);
                        if (xhr.status === 422 && xhr.responseJSON) {
                            var errors = xhr.responseJSON.errors || {};
                            $.each(errors, function(key, val) {
                                $('#' + key + '_error').text(val[0]);
                            });
                            toastr.warning("Please correct the errors on the form.");
                        } else {
                            toastr.error(xhr.responseJSON?.message || "An error occurred while saving the record.");
                        }
                    }
                });
            });

            // If editing, trigger the pool balance check on load
            if (editMode) {
                setTimeout(function() {
                    checkPoolBalance();
                }, 500);
            }
        });
    </script>
@endsection

@endsection
