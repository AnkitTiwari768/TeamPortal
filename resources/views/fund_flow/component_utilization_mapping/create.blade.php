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

        .table-themed {
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e2e8f0 !important;
        }

        .table-themed thead th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            border-bottom: 1px solid #e2e8f0 !important;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 12px;
        }

        .table-themed tbody td {
            padding: 12px;
            vertical-align: middle;
        }

        .alloc-row-item {
            transition: background-color 0.2s ease;
        }

        .alloc-row-item:hover {
            background-color: #f8fafc !important;
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

        .alloc-add-row-trigger img,
        .alloc-remove-row-trigger img {
            transition: transform 0.2s ease;
        }

        .alloc-add-row-trigger:hover img {
            transform: scale(1.15);
        }

        .alloc-remove-row-trigger:hover img {
            transform: scale(1.15);
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
    </style>

    <div class="container-fluid px-4 py-4">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card container-main-card">
                    <div class="card-header d-flex align-items-center py-3"
                        style="background: #ffffff; border-bottom: 1px solid #e2e8f0;">
                        <div class="heading">
                            <h1>
                                @if (!empty($row['id']))
                                    {{ __('fund_flow.edit_component_utilization_mapping') }}
                                @else
                                    {{ __('fund_flow.add_component_utilization_mapping') }}
                                @endif
                            </h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0" style="background: none; padding: 0;">
                                    <li class="breadcrumb-item"><a href="{{ url('dashboard') }}"
                                            style="color: #64748b; text-decoration: none;">{{ __('fund_flow.home') }}</a>
                                    </li>
                                    <li class="breadcrumb-item"><a href="{{ url('component-utilization-mapping') }}"
                                            style="color: #64748b; text-decoration: none;">{{ __('fund_flow.component_utilization_mapping') }}</a>
                                    </li>
                                    <li class="breadcrumb-item active" aria-current="page"
                                        style="color: #0f172a; font-weight: 500;">
                                        @if (!empty($row['id']))
                                            {{ __('fund_flow.edit_component_utilization_mapping') }}
                                        @else
                                            {{ __('fund_flow.add_component_utilization_mapping') }}
                                        @endif
                                    </li>
                                </ol>
                            </nav>
                        </div>
                        <div class="action-header ms-auto">
                            <div class="btn-group drop-btn">
                                @include('components.admin.buttons.back-button')
                            </div>
                        </div>
                    </div>

                    {{-- ── Card Body ── --}}
                    <div class="card-body p-0">
                        <form id="mapping-form">
                            @csrf
                            @if (!empty($row['id']))
                                <input type="hidden" name="id" value="{{ $row['id'] }}">
                            @endif

                            <div class="p-4" style="background-color: #f8fafc;">
                                <!-- Alert Info Banner -->
                                <div class="alert alert-info d-flex align-items-center py-3 px-4 mb-4" role="alert"
                                    style="background-color: #f0f9ff; border: 1px solid #e0f2fe; color: #0369a1; border-radius: 8px; font-size: 0.875rem;">
                                    <i data-feather="info" class="me-2"
                                        style="width: 18px; height: 18px; stroke-width: 2.5; flex-shrink: 0;"></i>
                                    <div style="font-weight: 500;">
                                        {{ __('fund_flow.component_utilization_mapping_desc') }}
                                    </div>
                                </div>

                                {{-- ── Section 1: Target Component Parameters ── --}}
                                <div class="form-section-card">
                                    <h5 class="form-section-title">
                                        <i class="fa fa-sliders"></i> {{ __('fund_flow.component_utilization_mapping') }}
                                    </h5>
                                    <div class="row">
                                        {{-- ── Financial Year ── --}}
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label
                                                    class="required form-label">{{ __('fund_flow.financial_year') }}</label>
                                                <select name="financial_year" class="form-select"
                                                    id="mapping-financial-year">
                                                    @foreach (financial_year() as $key => $value)
                                                        <option value="{{ $key }}"
                                                            {{ old('financial_year', $row['financial_year'] ?? '') == $key ? 'selected' : '' }}>
                                                            {{ $value }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <span class="text-danger form-error" id="financial_year_error"></span>
                                                <span class="text-danger form-error"
                                                    id="mapping-financial-year_error"></span>
                                            </div>
                                        </div>

                                        {{-- ── Duration ── --}}
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('fund_flow.duration') }}</label>
                                                <select name="duration_id" id="mapping-duration" class="form-select">
                                                    <option value="">{{ __('fund_flow.select') }}</option>
                                                    @if (!empty($duration))
                                                        @foreach ($duration as $value => $name)
                                                            <option value="{{ $value }}"
                                                                {{ old('duration_id', $row['duration_id'] ?? '') == $value ? 'selected' : '' }}>
                                                                {{ $name }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                                <span class="text-danger form-error" id="duration_id_error"></span>
                                                <span class="text-danger form-error" id="mapping-duration_error"></span>
                                            </div>
                                        </div>

                                        {{-- ── Sub-Duration (conditional) ── --}}
                                        <div class="col-md-4" id="mapping-sub-duration-wrapper" style="display:none;">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('fund_flow.sub_duration') }}</label>
                                                <select class="form-select" id="mapping-sub-duration" name="sub_duration_id">
                                                    <option value="">{{ __('fund_flow.select_sub_duration') }}</option>
                                                </select>
                                                <span class="text-danger form-error" id="sub_duration_id_error"></span>
                                                <span class="text-danger form-error" id="mapping-sub-duration_error"></span>
                                            </div>
                                        </div>

                                        {{-- ── Target Major Component ── --}}
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label
                                                    class="required form-label">{{ __('fund_flow.target_major_component') }}</label>
                                                <select name="target_major_component_id" id="target_major_component_id"
                                                    class="form-select">
                                                    <option value="">
                                                        {{ __('fund_flow.select_target_major_component') }}</option>
                                                    @if (!empty($majorComponents))
                                                        @foreach ($majorComponents as $value => $name)
                                                            <option value="{{ $value }}"
                                                                {{ old('target_major_component_id', $row['target_major_component_id'] ?? '') == $value ? 'selected' : '' }}>
                                                                {{ $name }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                                <span class="text-danger form-error"
                                                    id="target_major_component_id_error"></span>
                                            </div>
                                        </div>

                                        {{-- ── Target Sub Component ── --}}
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label
                                                    class="required form-label">{{ __('fund_flow.target_sub_component') }}</label>
                                                <select class="form-select" id="target_sub_component_id"
                                                    name="target_sub_component_id">
                                                    <option value="">
                                                        {{ __('fund_flow.select_target_sub_component') }}</option>
                                                </select>
                                                <span class="text-danger form-error"
                                                    id="target_sub_component_id_error"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Total Max Utilization Summary Box -->
                                <div class="row g-3 mb-4">
                                    <div class="col-md-12 col-12">
                                        <div class="card p-3 shadow-sm"
                                            style="border: 2px solid #0b3b82; border-radius: 12px; background-color: #ffffff; min-height: 80px; display: flex; flex-direction: column; justify-content: center;">
                                            <div class="text-primary fw-semibold mb-1"
                                                style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                                {{ __('fund_flow.total_max_utilization') }}</div>
                                            <div class="fw-bold text-primary" style="font-size: 1.5rem;"
                                                id="total-utilization-display">₹ 0.00</div>
                                            <input type="hidden" name="total_max_utilization_amount"
                                                id="total-utilization-hidden" value="0">
                                        </div>
                                    </div>
                                </div>

                                {{-- ── Section 2: Eligible Components Table ── --}}
                                <div class="form-section-card">
                                    <h5 class="form-section-title">
                                        <i class="fa fa-list-ol"></i>
                                        {{ __('fund_flow.eligible_components_for_utilization') }}
                                    </h5>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <table class="table table-themed table-striped table-bordered mb-0">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 250px;">
                                                            {{ __('fund_flow.eligible_major_component') }}</th>
                                                        <th class="required" style="width: 250px;">
                                                            {{ __('fund_flow.eligible_sub_component') }}</th>
                                                        <th style="width: 200px; text-align: right;">
                                                            {{ __('fund_flow.total_amount_allocated') }}</th>
                                                        <th style="width: 200px; text-align: right;">
                                                            {{ __('fund_flow.total_amount_distributed') }}</th>
                                                        <th style="width: 200px; text-align: right;">
                                                            {{ __('fund_flow.max_utilization_amount') }}</th>
                                                        <th style="width: 100px; text-align: center;">
                                                            {{ __('fund_flow.add_remove') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="eligible-rows-body">
                                                    {{-- Rows injected by Mustache template --}}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                {{-- ── Section 3: Remarks ── --}}
                                <div class="form-section-card">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('fund_flow.remarks') }}</label>
                                        <textarea name="remarks" id="remarks" class="form-control" rows="3" placeholder="Enter remarks (optional)..."
                                            style="border-radius: 8px;">{{ $row['remarks'] ?? '' }}</textarea>
                                        <span class="text-danger form-error" id="remarks_error"></span>
                                    </div>
                                </div>
                            </div>

                            {{-- Form Actions --}}
                            <div class="form-action">
                                <button type="submit" class="btn btn-primary px-4 py-2"
                                    style="background-color: #0b3b82; border-color: #0b3b82; border-radius: 8px;">
                                    <i class="fa fa-save"></i> {{ __('fund_flow.save_mapping') }}
                                </button>
                                <a href="{{ url('component-utilization-mapping') }}"
                                    class="btn btn-outline-secondary px-4 py-2" style="border-radius: 8px;">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Mustache template --}}
    <script id="eligible_row_template" type="x-tmpl-mustache">
    <tr class="alloc-row-item" data-row-id="@{{rowId}}">
        <td>
            <input type="hidden" name="eligible_lines[@{{rowId}}][id]" value="@{{lineId}}">
            <select class="form-select alloc-major-component"
                    name="eligible_lines[@{{rowId}}][eligible_major_component_id]"
                    id="alloc-major-@{{rowId}}"
                    data-row-id="@{{rowId}}">
                <option value="">-- Select Major Component --</option>
            </select>
            <div class="text-danger form-error" id="alloc-major-@{{rowId}}_error"></div>
        </td>
        <td>
            <select class="form-select alloc-sub-component"
                    name="eligible_lines[@{{rowId}}][eligible_sub_component_id]"
                    id="alloc-sub-@{{rowId}}"
                    data-row-id="@{{rowId}}">
                <option value="">-- Select Sub Component --</option>
            </select>
            <div class="text-danger form-error" id="alloc-sub-@{{rowId}}_error"></div>
        </td>
        <td>
            <input type="text"
                   class="form-control alloc-allocated-input text-end"
                   name="eligible_lines[@{{rowId}}][total_allocated_amount]"
                   id="alloc-allocated-@{{rowId}}"
                   placeholder="0.00"
                   readonly
                   style="background-color: #f1f5f9;"
                   value="@{{total_allocated_amount}}">
            <div class="text-danger form-error" id="alloc-allocated-@{{rowId}}_error"></div>
        </td>
        <td>
            <input type="text"
                   class="form-control alloc-distributed-input text-end"
                   name="eligible_lines[@{{rowId}}][total_distributed_amount]"
                   id="alloc-distributed-@{{rowId}}"
                   placeholder="0.00"
                   readonly
                   style="background-color: #f1f5f9;"
                   value="@{{total_distributed_amount}}">
            <div class="text-danger form-error" id="alloc-distributed-@{{rowId}}_error"></div>
        </td>
        <td>
            <input type="text"
                   class="form-control alloc-amount-input text-end"
                   name="eligible_lines[@{{rowId}}][max_utilization_amount]"
                   id="alloc-amount-@{{rowId}}"
                   placeholder="0.00"
                   value="@{{max_utilization_amount}}">
            <div class="text-danger form-error" id="alloc-amount-@{{rowId}}_error"></div>
        </td>
        <td style="text-align: center;">
            <div class="d-inline-block">
                <a class="alloc-add-row-trigger" href="javascript:" id="alloc-add-row">
                    <img src="{{ url('assets/img-new/add-btn.svg') }}" style="width: 24px; height: 24px;">
                </a>
            </div>
            @{{^lineId}}
            <div class="d-inline-block ms-2" id="alloc-remove-wrap-@{{rowId}}">
                <a class="alloc-remove-row-trigger" href="javascript:" data-row-id="@{{rowId}}">
                    <img src="{{ url('assets/img-new/dlt-btn.svg') }}" style="width: 24px; height: 24px;">
                </a>
            </div>
            @{{/lineId}}
        </td>
    </tr>
    </script>

@section('js')
    @include('scripts/attribute_dependency_dropdown')
    <script>
        window.allocBaseUrl = "{{ url('/') }}";
        window.csrfToken = "{{ csrf_token() }}";
        window.allocAttrCodes = {
            majorComponent: "{{ config('allocation.major_component_code', 'major-components') }}",
            subComponent: "{{ config('allocation.sub_component_code', 'sub-components') }}",
        };
        window.allocExistingLines = @json($lines ?? []);
        window.allocExistingTargetSub = "{{ $row['target_sub_component_id'] ?? '' }}";

        var allocRowCounter = 0;
        var editMode = {{ !empty($row['id']) ? 'true' : 'false' }};
        window.loadedComponentBalances = [];

        function fetchComponentBalances() {
            var fy = $('#mapping-financial-year').val();
            var dur = $('#mapping-duration').val();
            var subDur = $('#mapping-sub-duration-wrapper').is(':visible') ? $('#mapping-sub-duration').val() : '';

            if (!fy) {
                window.loadedComponentBalances = [];
                return;
            }

            $.ajax({
                url: window.allocBaseUrl + '/component-utilization-mapping/component-balances',
                method: 'GET',
                data: {
                    financial_year: fy,
                    duration_id: dur,
                    sub_duration_id: subDur
                },
                success: function(res) {
                    window.loadedComponentBalances = res.data || [];
                    updateAllRowsBalances();
                }
            });
        }

        function allocInitDurationDropdown() {
            $('#mapping-duration').on('change', function() {
                var duration = $(this).val();
                var durationText = $(this).find('option:selected').text().trim();

                $('#mapping-sub-duration-wrapper').hide();
                $('#mapping-sub-duration').html('<option value="">{{ __('fund_flow.select_sub_duration') }}</option>');

                if (!duration || durationText === 'Yearly') {
                    fetchComponentBalances();
                    return;
                }

                $('#mapping-sub-duration-wrapper').show();
                loadAttributeDropdownOptions(duration.toLowerCase(), "#mapping-sub-duration", true);
                fetchComponentBalances();
            });

            $(document).on('change', '#mapping-sub-duration', function() {
                fetchComponentBalances();
            });

            var existingDuration = $('#mapping-duration').val();
            var existingSubDuration = "{{ $row['sub_duration_id'] ?? '' }}";

            if (existingDuration) {
                var durationText = $('#mapping-duration').find('option:selected').text().trim();
                if (durationText !== 'Yearly') {
                    $('#mapping-sub-duration-wrapper').show();
                    if (existingSubDuration) {
                        loadAttributeDropdownOptions(existingDuration.toLowerCase(), "#mapping-sub-duration", true, function() {
                            $('#mapping-sub-duration').val(existingSubDuration);
                            fetchComponentBalances();
                        });
                    } else {
                        loadAttributeDropdownOptions(existingDuration.toLowerCase(), "#mapping-sub-duration", true);
                    }
                }
            }
        }

        function updateAllRowsBalances() {
            $('.alloc-row-item').each(function() {
                var rowId = $(this).data('row-id');
                var majorId = $('#alloc-major-' + rowId).val();
                var subId = $('#alloc-sub-' + rowId).val();
                if (majorId) {
                    var matched = window.loadedComponentBalances.find(function(b) {
                        if (subId) {
                            return String(b.major_component_id) === String(majorId) && String(b
                                .sub_component_id) === String(subId);
                        }
                        return String(b.major_component_id) === String(majorId);
                    });
                    if (matched) {
                        var alloc = parseFloat(matched.total_allocated_amount) || 0;
                        var dist = parseFloat(matched.total_distributed_amount) || 0;
                        $('#alloc-allocated-' + rowId).val(alloc.toFixed(2));
                        $('#alloc-distributed-' + rowId).val(dist.toFixed(2));
                    }
                }
            });
        }

        function allocRecalcTotal() {
            var totalUtil = 0;

            $('input.alloc-amount-input').each(function() {
                var val = parseFloat($(this).val().replace(/,/g, ''));
                if (!isNaN(val) && val >= 0) totalUtil += val;
            });

            $('#total-utilization-display').text('₹ ' + totalUtil.toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            $('#total-utilization-hidden').val(totalUtil.toFixed(2));
        }

        function allocHasDuplicateRow() {
            var seen = {};
            var duplicate = false;
            $('.alloc-row-item').each(function() {
                var major = $(this).find('.alloc-major-component').val() || '';
                var sub = $(this).find('.alloc-sub-component').val() || '';
                if (!major) return;
                var key = major + '|' + sub;
                if (seen[key]) {
                    duplicate = true;
                    return false;
                }
                seen[key] = true;
            });
            return duplicate;
        }

        function allocLoadAttributeValues(attrCode, parentId, selectId, selectedVal, callback) {
            var $sel = $('#' + selectId);
            $sel.prop('disabled', true).html('<option value="">Loading...</option>');

            var params = {};
            if (parentId) params.parent_id = parentId;

            $.ajax({
                url: window.allocBaseUrl + '/fund-allocation/attribute-values/' + encodeURIComponent(attrCode),
                method: 'GET',
                data: params,
                headers: {
                    'Accept': 'application/json'
                },
                success: function(result) {
                    var items = Array.isArray(result) ? result : (result.data || []);
                    $sel.html('<option value="">-- Select --</option>');
                    $.each(items, function(i, item) {
                        var label = item.name || item.attribute_value || '';
                        var sel = selectedVal && String(item.id) === String(selectedVal) ? ' selected' :
                            '';
                        $sel.append('<option value="' + item.id + '"' + sel + '>' + label +
                        '</option>');
                    });
                    $sel.prop('disabled', false);
                    if (typeof callback === 'function') callback();
                },
                error: function() {
                    $sel.html('<option value="">-- Error loading --</option>').prop('disabled', false);
                }
            });
        }

        function allocLoadSubComponents(majorId, rowId, selectedSubId) {
            allocLoadAttributeValues(
                window.allocAttrCodes.subComponent,
                majorId,
                'alloc-sub-' + rowId,
                selectedSubId
            );
        }

        function allocAddRow(editMode, lineData) {
            var rowId = Date.now() + (++allocRowCounter);
            lineData = lineData || {};

            var source = $('#eligible_row_template').html();
            Mustache.parse(source);
            var rendered = Mustache.render(source, {
                rowId: rowId,
                lineId: lineData.id || '',
                total_allocated_amount: lineData.total_allocated_amount ? parseFloat(lineData
                    .total_allocated_amount).toFixed(2) : '',
                total_distributed_amount: lineData.total_distributed_amount ? parseFloat(lineData
                    .total_distributed_amount).toFixed(2) : '',
                max_utilization_amount: lineData.max_utilization_amount || '',
            });

            $('#eligible-rows-body').append(rendered);

            allocLoadAttributeValues(
                window.allocAttrCodes.majorComponent,
                null,
                'alloc-major-' + rowId,
                lineData.eligible_major_component_id,
                function() {
                    if (lineData.eligible_major_component_id && lineData.eligible_sub_component_id) {
                        allocLoadSubComponents(lineData.eligible_major_component_id, rowId, lineData
                            .eligible_sub_component_id);
                    }
                }
            );

            allocRecalcTotal();
        }

        function allocRemoveRow(rowId) {
            if ($('.alloc-row-item').length <= 1) {
                toastr.warning('At least one eligible component row is required.');
                return;
            }
            $('[data-row-id="' + rowId + '"].alloc-row-item').fadeOut(200, function() {
                $(this).remove();
                allocRecalcTotal();
            });
        }

        function allocInitEdit(lines, editMode) {
            $('#eligible-rows-body').empty();
            if (lines && lines.length > 0) {
                $.each(lines, function(i, line) {
                    allocAddRow(editMode, line);
                });
            } else {
                allocAddRow(editMode);
            }
        }

        function allocInitRowsEvents(editMode) {
            $(document).on('click', '.alloc-add-row-trigger', function() {
                allocAddRow(editMode);
            });

            $(document).on('click', '.alloc-remove-row-trigger', function() {
                allocRemoveRow($(this).data('row-id'));
            });

            $(document).on('change', '#target_major_component_id', function() {
                var majorId = $(this).val();
                $('#target_sub_component_id').html(
                    '<option value="">{{ __('fund_flow.select_target_sub_component') }}</option>');
                if (majorId) {
                    loadAttributeDropdownOptions(majorId, '#target_sub_component_id', true);
                }
            });

            $(document).on('change', '#mapping-financial-year', function() {
                fetchComponentBalances();
            });

            $(document).on('change', '.alloc-major-component', function() {
                var rowId = $(this).data('row-id');
                var majorId = $(this).val();
                $('#alloc-sub-' + rowId).html('<option value="">-- Select Sub Component --</option>');
                $('#alloc-allocated-' + rowId).val('');
                $('#alloc-distributed-' + rowId).val('');
                if (majorId) {
                    allocLoadSubComponents(majorId, rowId, null);
                    var matched = window.loadedComponentBalances.find(function(b) {
                        return String(b.major_component_id) === String(majorId);
                    });
                    if (matched) {
                        var alloc = parseFloat(matched.total_allocated_amount) || 0;
                        var dist = parseFloat(matched.total_distributed_amount) || 0;
                        $('#alloc-allocated-' + rowId).val(alloc.toFixed(2));
                        $('#alloc-distributed-' + rowId).val(dist.toFixed(2));
                    }
                }
                if (allocHasDuplicateRow()) toastr.warning('Duplicate combination detected.');
            });

            $(document).on('change', '.alloc-sub-component', function() {
                var rowId = $(this).data('row-id');
                var majorId = $('#alloc-major-' + rowId).val();
                var subId = $(this).val();

                if (allocHasDuplicateRow()) {
                    toastr.warning('Duplicate combination detected.');
                    return;
                }

                if (majorId && subId) {
                    var matched = window.loadedComponentBalances.find(function(b) {
                        return String(b.major_component_id) === String(majorId) && String(b
                            .sub_component_id) === String(subId);
                    });
                    if (matched) {
                        var alloc = parseFloat(matched.total_allocated_amount) || 0;
                        var dist = parseFloat(matched.total_distributed_amount) || 0;
                        $('#alloc-allocated-' + rowId).val(alloc.toFixed(2));
                        $('#alloc-distributed-' + rowId).val(dist.toFixed(2));
                    }
                }
            });

            $(document).on('input', '.alloc-amount-input', function() {
                // Prevent typing negative minus sign
                this.value = this.value.replace(/[^0-9.]/g, '');
                allocRecalcTotal();
            });

            $(document).on('focus change input', '.form-control, .form-select', function() {
                $(this).closest('td').find('.form-error').text('');
                $('#' + $(this).attr('id') + '_error').text('');
                var fieldName = $(this).attr('name');
                if (fieldName) {
                    $('#' + fieldName.replace(/_/g, '-') + '_error').text('');
                }
            });
        }

        function allocClearErrors() {
            $('.form-error').text('');
        }

        function allocSetError(fieldId, message) {
            $('#' + fieldId + '_error').text(message);
            var flatId = fieldId.replace(/-/g, '_');
            $('#' + flatId + '_error').text(message);
        }

        function allocShowServerErrors(errors) {
            if (!errors || typeof errors !== 'object') return;
            $.each(errors, function(key, messages) {
                var msg = Array.isArray(messages) ? messages[0] : messages;
                var parts = key.split('.');

                if (parts.length === 3 && parts[0] === 'eligible_lines') {
                    var index = parseInt(parts[1], 10);
                    var field = parts[2];
                    var fieldName = 'amount';
                    if (field === 'eligible_major_component_id') {
                        fieldName = 'major';
                    } else if (field === 'eligible_sub_component_id') {
                        fieldName = 'sub';
                    } else if (field === 'max_utilization_amount') {
                        fieldName = 'amount';
                    } else if (field === 'total_allocated_amount') {
                        fieldName = 'allocated';
                    } else if (field === 'total_distributed_amount') {
                        fieldName = 'distributed';
                    }

                    var $targetRow = $('.alloc-row-item').eq(index);
                    if ($targetRow.length) {
                        var realId = $targetRow.data('row-id');
                        var $el = $('#alloc-' + fieldName + '-' + realId + '_error');
                        if ($el.length) {
                            $el.text(msg);
                        }
                    }
                } else {
                    var $el = $('#' + key + '_error');
                    if (!$el.length) {
                        $el = $('#mapping-' + key.replace(/_/g, '-') + '_error');
                    }
                    if (!$el.length) {
                        $el = $('#' + key.replace(/_/g, '-') + '_error');
                    }
                    if (!$el.length) {
                        var alias = key.replace(/_/g, '-');
                        $el = $('#alloc-' + alias + '_error');
                    }
                    if ($el && $el.length) {
                        $el.text(msg);
                    }
                }
            });
        }

        $(document).ready(function() {
            allocInitRowsEvents(editMode);
            allocInitEdit(window.allocExistingLines, editMode);
            allocInitDurationDropdown();

            var existingTargetMajor = $('#target_major_component_id').val();
            if (existingTargetMajor && window.allocExistingTargetSub) {
                loadAttributeDropdownOptions(existingTargetMajor, '#target_sub_component_id', true, function() {
                    $('#target_sub_component_id').val(window.allocExistingTargetSub);
                });
            }

            $('#mapping-form').on('submit', function(e) {
                e.preventDefault();
                allocClearErrors();

                var hasError = false;

                // Client-side required checks
                var fy = $('#mapping-financial-year').val();
                if (!fy || !fy.trim()) {
                    allocSetError('financial_year', 'Financial Year is required.');
                    allocSetError('mapping-financial-year', 'Financial Year is required.');
                    hasError = true;
                }

                var targetMajor = $('#target_major_component_id').val();
                if (!targetMajor || !targetMajor.trim()) {
                    allocSetError('target_major_component_id', 'Target Major Component is required.');
                    hasError = true;
                }

                if ($('.alloc-row-item').length === 0) {
                    toastr.error('Please add at least one eligible component row.');
                    hasError = true;
                }

                if (allocHasDuplicateRow()) {
                    toastr.error(
                        'Duplicate row: Eligible Major + Sub Component combination must be unique.');
                    hasError = true;
                }

                // Check for negative amounts & required row fields
                $('.alloc-row-item').each(function(index) {
                    var rowId = $(this).data('row-id');
                    var majorId = $('#alloc-major-' + rowId).val();
                    var maxAmt = $('#alloc-amount-' + rowId).val();

                    if (!majorId) {
                        allocSetError('alloc-major-' + rowId,
                            'Eligible Major Component is required.');
                        hasError = true;
                    }

                    if (maxAmt === '' || maxAmt === null) {
                        allocSetError('alloc-amount-' + rowId,
                            'Maximum utilization amount is required.');
                        hasError = true;
                    } else if (parseFloat(maxAmt) < 0) {
                        allocSetError('alloc-amount-' + rowId, 'Negative amount is not allowed.');
                        toastr.error('Negative amount is not allowed.');
                        hasError = true;
                    }
                });

                if (hasError) return;

                var payload = {
                    financial_year: $('#mapping-financial-year').val(),
                    duration_id: $('#mapping-duration').val() || null,
                    sub_duration_id: $('#mapping-sub-duration').val() || null,
                    target_major_component_id: $('#target_major_component_id').val(),
                    target_sub_component_id: $('#target_sub_component_id').val() || null,
                    remarks: $('textarea[name="remarks"]').val() || null,
                    total_max_utilization_amount: $('#total-utilization-hidden').val() || 0,
                    eligible_lines: []
                };

                $('.alloc-row-item').each(function() {
                    var rowId = $(this).data('row-id');
                    var lineId = $(this).find('input[name*="[id]"]').val() || null;
                    var majorId = $('#alloc-major-' + rowId).val() || '';
                    var subId = $('#alloc-sub-' + rowId).val() || null;
                    var allocatedAmt = $('#alloc-allocated-' + rowId).val() || 0;
                    var distributedAmt = $('#alloc-distributed-' + rowId).val() || 0;
                    var maxAmt = $('#alloc-amount-' + rowId).val() || '';

                    payload.eligible_lines.push({
                        id: lineId,
                        eligible_major_component_id: majorId,
                        eligible_sub_component_id: subId,
                        total_allocated_amount: allocatedAmt,
                        total_distributed_amount: distributedAmt,
                        max_utilization_amount: maxAmt,
                    });
                });

                var submitUrl = editMode ? "{{ url('component-utilization-mapping/store') }}/" +
                    "{{ $row['id'] ?? '' }}" : "{{ url('component-utilization-mapping/store') }}";

                $('#cover-spin').show();

                $.ajax({
                    url: submitUrl,
                    method: 'POST',
                    data: JSON.stringify(payload),
                    contentType: 'application/json',
                    headers: {
                        'X-CSRF-TOKEN': window.csrfToken
                    },
                    success: function(result) {
                        if (result && (result.success || result.status === true || result
                                .message)) {
                            toastr.success(result.message || 'Saved successfully.');
                            setTimeout(function() {
                                window.location.href =
                                    "{{ url('component-utilization-mapping') }}";
                            }, 1200);
                        } else if (result && result.errors) {
                            allocShowServerErrors(result.errors);
                            toastr.error('Please fix the highlighted errors.');
                        } else {
                            toastr.error(result && result.message ? result.message :
                                'Something went wrong.');
                        }
                    },
                    error: function(xhr) {
                        var err = xhr.responseJSON;
                        if (err && err.errors) {
                            allocShowServerErrors(err.errors);
                            toastr.error('Validation failed. Please fix the errors.');
                        } else {
                            toastr.error('An unexpected error occurred. Please try again.');
                        }
                    },
                    complete: function() {
                        $('#cover-spin').hide();
                    }
                });
            });
        });
    </script>
@endsection
@endsection
