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
                                    {{ __('fund_flow.edit_carry_forward') }}
                                @else
                                    {{ __('fund_flow.add_carry_forward') }}
                                @endif
                            </h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0" style="background: none; padding: 0;">
                                    <li class="breadcrumb-item"><a href="{{ url('dashboard') }}"
                                            style="color: #64748b; text-decoration: none;">{{ __('fund_flow.home') }}</a>
                                    </li>
                                    <li class="breadcrumb-item"><a href="{{ url('carry-forward-mapping') }}"
                                            style="color: #64748b; text-decoration: none;">{{ __('fund_flow.carry_forward_mapping') }}</a>
                                    </li>
                                    <li class="breadcrumb-item active" aria-current="page"
                                        style="color: #0f172a; font-weight: 500;">
                                        @if (!empty($row['id']))
                                            {{ __('fund_flow.edit_carry_forward') }}
                                        @else
                                            {{ __('fund_flow.add_carry_forward') }}
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
                        <form id="carry-form">
                            @csrf
                            @if (!empty($row['id']))
                                <input type="hidden" name="id" value="{{ $row['id'] }}">
                            @endif

                            <div class="p-4" style="background-color: #f8fafc;">
                                <!-- Alert Info Banner -->
                                <div class="alert alert-info d-flex align-items-center py-3 px-4 mb-4" role="alert" style="background-color: #f0f9ff; border: 1px solid #e0f2fe; color: #0369a1; border-radius: 8px; font-size: 0.875rem;">
                                    <i data-feather="info" class="me-2" style="width: 18px; height: 18px; stroke-width: 2.5; flex-shrink: 0;"></i>
                                    <div style="font-weight: 500;">
                                        Carry Forward is strictly manual. The system will never carry forward balances automatically. Carry Forward is permitted only within the same Financial Year.
                                    </div>
                                </div>

                                {{-- ── Section 1: Period details ── --}}
                                <div class="form-section-card">
                                    <h5 class="form-section-title">
                                        <i class="fa fa-calendar"></i> {{ __('fund_flow.carry_forward_parameters') }}
                                    </h5>
                                    <div class="row">
                                        {{-- ── Financial Year ── --}}
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="required form-label">{{ __('fund_flow.financial_year') }}</label>
                                                <select name="financial_year" class="form-select" id="alloc-financial-year">
                                                    @foreach (financial_year() as $key => $value)
                                                        <option value="{{ $key }}"
                                                            {{ old('financial_year', $row['financial_year'] ?? '') == $key ? 'selected' : '' }}>
                                                            {{ $value }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <span class="text-danger form-error" id="alloc-financial-year_error"></span>
                                            </div>
                                        </div>

                                        {{-- ── Carry Forward Type ── --}}
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="required form-label">{{ __('fund_flow.carry_forward_type') }}</label>
                                                <select name="from_duration_id" id="alloc-duration" class="form-select">
                                                    <option value="">{{ __('fund_flow.select') }}</option>
                                                    @if (!empty($duration))
                                                        @foreach ($duration as $value => $name)
                                                            <option value="{{ $value }}"
                                                                {{ old('from_duration_id', $row['from_duration_id'] ?? '') == $value ? 'selected' : '' }}>
                                                                {{ $name }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                                <input type="hidden" name="to_duration_id" id="alloc-to-duration" value="{{ $row['to_duration_id'] ?? '' }}">
                                                <span class="text-danger form-error" id="alloc-duration_error"></span>
                                            </div>
                                        </div>

                                        {{-- ── From Duration ── --}}
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label required">{{ __('fund_flow.from_duration') }}</label>
                                                <select class="form-select" id="alloc-from-sub-duration" name="from_sub_duration_id">
                                                    <option value="">{{ __('fund_flow.select_from_duration') }}</option>
                                                </select>
                                                <span class="text-danger form-error" id="alloc-from-sub-duration_error"></span>
                                            </div>
                                        </div>

                                        {{-- ── To Duration ── --}}
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label required">{{ __('fund_flow.to_duration') }}</label>
                                                <select class="form-select" id="alloc-to-sub-duration" name="to_sub_duration_id">
                                                    <option value="">{{ __('fund_flow.select_to_duration') }}</option>
                                                </select>
                                                <span class="text-danger form-error" id="alloc-to-sub-duration_error"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Total Closing & Carry Forward Boxes -->
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6 col-12">
                                        <div class="card p-3 shadow-sm" style="border: 1px solid #e2e8f0; border-radius: 12px; background-color: #ffffff; min-height: 80px; display: flex; flex-direction: column; justify-content: center;">
                                            <div class="text-muted fw-semibold mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">{{ __('fund_flow.total_closing_balance') }}</div>
                                            <div class="fw-bold text-dark" style="font-size: 1.5rem;" id="total-closing-display">₹ 0.00</div>
                                            <input type="hidden" id="total-closing-hidden" value="0">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <div class="card p-3 shadow-sm" style="border: 2px solid #2563eb; border-radius: 12px; background-color: #ffffff; min-height: 80px; display: flex; flex-direction: column; justify-content: center;">
                                            <div class="text-primary fw-semibold mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">{{ __('fund_flow.total_carry_forward') }}</div>
                                            <div class="fw-bold text-primary" style="font-size: 1.5rem;" id="total-carry-display">₹ 0.00</div>
                                            <input type="hidden" name="total_amount" id="total-carry-hidden" value="0">
                                        </div>
                                    </div>
                                </div>

                                {{-- ── Section 2: Carry Forward Table ── --}}
                                <div class="form-section-card">
                                    <h5 class="form-section-title">
                                        <i class="fa fa-list-ol"></i> {{ __('fund_flow.sub_component_closing_balances') }}
                                    </h5>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <table class="table table-themed table-striped table-bordered mb-0">
                                                <thead>
                                                    <tr>
                                                    <th class="required">{{ __('fund_flow.major_component') }}</th>
                                                    <th class="required">{{ __('fund_flow.sub_component') }}</th>
                                                    <th style="text-align: right;">{{ __('fund_flow.opening_balance') }}</th>
                                                        <th style="width: 250px; text-align: right;">{{ __('fund_flow.carry_forward_amount') }}</th>
                                                        <th style="width: 120px; text-align: center;">{{ __('fund_flow.add_remove') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="alloc-rows-body">
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
                                        <textarea name="remarks" class="form-control" rows="3" placeholder="Enter remarks (optional)..." style="border-radius: 8px;">{{ $row['remarks'] ?? '' }}</textarea>
                                        <span class="text-danger form-error" id="alloc-remarks_error"></span>
                                    </div>
                                </div>
                            </div>

                            {{-- Form Actions --}}
                            <div class="form-action">
                                <button type="submit" class="btn btn-primary px-4 py-2" style="background-color: #0b3b82; border-color: #0b3b82; border-radius: 8px;">
                                    <i class="fa fa-save"></i> Save Mapping
                                </button>
                                <a href="{{ url('carry-forward-mapping') }}" class="btn btn-outline-secondary px-4 py-2" style="border-radius: 8px;">
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
    <script id="carry_row_template" type="x-tmpl-mustache">
    <tr class="alloc-row-item" data-row-id="@{{rowId}}">
        <td>
            <input type="hidden" name="carry_forward_lines[@{{rowId}}][id]" value="@{{lineId}}">
            <select class="form-select alloc-major-component"
                    name="carry_forward_lines[@{{rowId}}][major_component_id]"
                    id="alloc-major-@{{rowId}}"
                    data-row-id="@{{rowId}}">
                <option value="">-- Select --</option>
            </select>
            <div class="text-danger form-error" id="alloc-major-@{{rowId}}_error"></div>
        </td>
        <td>
            <select class="form-select alloc-sub-component"
                    name="carry_forward_lines[@{{rowId}}][sub_component_id]"
                    id="alloc-sub-@{{rowId}}"
                    data-row-id="@{{rowId}}">
                <option value="">-- Select --</option>
            </select>
            <div class="text-danger form-error" id="alloc-sub-@{{rowId}}_error"></div>
        </td>
        <td>
            <input type="text"
                   class="form-control alloc-opening-input text-end"
                   name="carry_forward_lines[@{{rowId}}][opening_balance]"
                   id="alloc-opening-@{{rowId}}"
                   placeholder="0.00"
                   value="@{{opening_balance}}">
            <div class="text-danger form-error" id="alloc-opening-@{{rowId}}_error"></div>
        </td>
        <td>
            <input type="text"
                   class="form-control alloc-amount-input text-end"
                   name="carry_forward_lines[@{{rowId}}][carried_forward_amount]"
                   id="alloc-amount-@{{rowId}}"
                   placeholder="0.00"
                   value="@{{carried_forward_amount}}">
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
            duration: "{{ config('allocation.duration_code', 'duration') }}",
        };
        window.allocExistingLines = @json($lines ?? []);
        window.allocExistingFromSubDuration = "{{ $row['from_sub_duration_id'] ?? '' }}";
        window.allocExistingToSubDuration = "{{ $row['to_sub_duration_id'] ?? '' }}";

        var allocRowCounter = 0;
        var editMode = {{ !empty($row['id']) ? 'true' : 'false' }};

        window.loadedClosingBalances = [];

        function fetchClosingBalances() {
            var fy = $('#alloc-financial-year').val();
            var dur = $('#alloc-duration').val();
            var fromSub = $('#alloc-from-sub-duration').val();

            if (!fy || !dur || !fromSub) {
                window.loadedClosingBalances = [];
                updateTotalClosingBalanceCard();
                return;
            }

            $.ajax({
                url: window.allocBaseUrl + '/carry-forward-mapping/closing-balances',
                method: 'GET',
                data: {
                    financial_year: fy,
                    duration_id: dur,
                    sub_duration_id: fromSub
                },
                success: function(res) {
                    window.loadedClosingBalances = res.data || [];
                    updateTotalClosingBalanceCard();
                    updateAllRowsClosingBalances();
                }
            });
        }

        function updateTotalClosingBalanceCard() {
            var total = 0;
            if (window.loadedClosingBalances && window.loadedClosingBalances.length > 0) {
                window.loadedClosingBalances.forEach(function(b) {
                    total += parseFloat(b.closing_balance) || 0;
                });
            } else {
                $('input.alloc-opening-input').each(function() {
                    var val = parseFloat($(this).val().replace(/,/g, ''));
                    if (!isNaN(val) && val >= 0) total += val;
                });
            }
            $('#total-closing-display').text('₹ ' + total.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            $('#total-closing-hidden').val(total.toFixed(2));
        }

        function updateAllRowsClosingBalances() {
            $('.alloc-row-item').each(function() {
                var rowId = $(this).data('row-id');
                var majorId = $('#alloc-major-' + rowId).val();
                var subId = $('#alloc-sub-' + rowId).val();
                if (majorId && subId) {
                    var matched = window.loadedClosingBalances.find(function(b) {
                        return String(b.major_component_id) === String(majorId) && String(b.sub_component_id) === String(subId);
                    });
                    if (matched) {
                        var bal = parseFloat(matched.closing_balance);
                        $('#alloc-opening-' + rowId).val(bal.toFixed(2));
                    }
                }
            });
            allocRecalcTotal();
        }

        function allocRecalcTotal() {
            var totalCarry = 0;

            $('input.alloc-amount-input').each(function() {
                var val = parseFloat($(this).val().replace(/,/g, ''));
                if (!isNaN(val) && val >= 0) totalCarry += val;
            });

            $('#total-carry-display').text('₹ ' + totalCarry.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            $('#total-carry-hidden').val(totalCarry.toFixed(2));
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
                        var sel = selectedVal && String(item.id) === String(selectedVal) ? ' selected' : '';
                        $sel.append('<option value="' + item.id + '"' + sel + '>' + label + '</option>');
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

            var source = $('#carry_row_template').html();
            Mustache.parse(source);
            var rendered = Mustache.render(source, {
                rowId: rowId,
                lineId: lineData.id || '',
                opening_balance: lineData.opening_balance || '',
                carried_forward_amount: lineData.carried_forward_amount || '',
            });

            $('#alloc-rows-body').append(rendered);

            var $majorSel = $('#alloc-major-' + rowId);
            allocLoadAttributeValues(
                window.allocAttrCodes.majorComponent,
                null,
                'alloc-major-' + rowId,
                lineData.major_component_id,
                function() {
                    if (lineData.major_component_id && lineData.sub_component_id) {
                        allocLoadSubComponents(lineData.major_component_id, rowId, lineData.sub_component_id);
                    }
                }
            );

            allocRecalcTotal();
            updateTotalClosingBalanceCard();
        }

        function allocRemoveRow(rowId) {
            if ($('.alloc-row-item').length <= 1) {
                toastr.warning('At least one carry forward row is required.');
                return;
            }
            $('[data-row-id="' + rowId + '"].alloc-row-item').fadeOut(200, function() {
                $(this).remove();
                allocRecalcTotal();
                updateTotalClosingBalanceCard();
            });
        }

        function allocInitEdit(lines, editMode) {
            $('#alloc-rows-body').empty();
            if (lines && lines.length > 0) {
                $.each(lines, function(i, line) {
                    allocAddRow(editMode, line);
                });
            } else {
                allocAddRow(editMode);
            }
            updateTotalClosingBalanceCard();
        }

        function allocInitDurationDropdown() {
            $('#alloc-duration').on('change', function() {
                var duration = $(this).val();
                $('#alloc-to-duration').val(duration);

                $('#alloc-from-sub-duration').html('<option value="">{{ __('fund_flow.select_from_duration') }}</option>');
                $('#alloc-to-sub-duration').html('<option value="">{{ __('fund_flow.select_to_duration') }}</option>');

                if (!duration) {
                    window.loadedClosingBalances = [];
                    updateTotalClosingBalanceCard();
                    return;
                }

                allocLoadAttributeValues('duration', duration, 'alloc-from-sub-duration', window.allocExistingFromSubDuration, function() {
                    $('#alloc-from-sub-duration').trigger('change');
                });
                allocLoadAttributeValues('duration', duration, 'alloc-to-sub-duration', window.allocExistingToSubDuration);
            });

            $(document).on('change', '#alloc-financial-year, #alloc-from-sub-duration', function() {
                fetchClosingBalances();
            });

            var existingDuration = $('#alloc-duration').val();
            if (existingDuration) {
                $('#alloc-duration').trigger('change');
            } else {
                updateTotalClosingBalanceCard();
            }
        }

        function allocInitRowsEvents(editMode) {
            $(document).on('click', '.alloc-add-row-trigger', function() {
                allocAddRow(editMode);
            });

            $(document).on('click', '.alloc-remove-row-trigger', function() {
                allocRemoveRow($(this).data('row-id'));
            });

            $(document).on('change', '.alloc-major-component', function() {
                var rowId = $(this).data('row-id');
                var majorId = $(this).val();
                $('#alloc-sub-' + rowId).html('<option value="">-- Select Sub-Component --</option>');
                $('#alloc-opening-' + rowId).val('');
                if (majorId) allocLoadSubComponents(majorId, rowId, null);
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
                    var matched = window.loadedClosingBalances.find(function(b) {
                        return String(b.major_component_id) === String(majorId) && String(b.sub_component_id) === String(subId);
                    });
                    var bal = matched ? parseFloat(matched.closing_balance) : 0;
                    $('#alloc-opening-' + rowId).val(bal.toFixed(2));
                    allocRecalcTotal();
                } else {
                    $('#alloc-opening-' + rowId).val('');
                }
            });

            $(document).on('input', '.alloc-amount-input, .alloc-opening-input', function() {
                this.value = this.value.replace(/[^0-9.]/g, '');
                allocRecalcTotal();
            });

            $(document).on('focus', '.form-control, .form-select', function() {
                $(this).closest('td').find('.form-error').text('');
            });
        }

        function allocClearErrors() {
            $('.form-error').text('');
        }

        function allocSetError(fieldId, message) {
            var flatId = fieldId.replace(/-/g, '_');
            $('#' + fieldId + '_error').text(message);
            $('#' + flatId + '_error').text(message);
        }

        function allocShowServerErrors(errors) {
            if (!errors || typeof errors !== 'object') return;
            $.each(errors, function(key, messages) {
                var msg = Array.isArray(messages) ? messages[0] : messages;
                var parts = key.split('.');
                var $el = null;

                if (parts.length === 3 && parts[0] === 'carry_forward_lines') {
                    var pointer = parts[1];
                    var field = parts[2];
                    var fieldName = field === 'major_component_id' ? 'major' : (field === 'sub_component_id' ? 'sub' : (field === 'opening_balance' ? 'opening' : 'amount'));

                    var index = parseInt(pointer, 10);
                    var $targetRow = $('.alloc-row-item').eq(index);
                    if ($targetRow.length) {
                        var realId = $targetRow.data('row-id');
                        $el = $('#alloc-' + fieldName + '-' + realId + '_error');
                    }
                } else {
                    $el = $('#' + key + '_error');
                    if (!$el.length) {
                        var alias = key.replace(/_/g, '-');
                        $el = $('#alloc-' + alias + '_error');
                    }
                }

                if ($el && $el.length) {
                    $el.text(msg);
                }
            });
        }

        $(document).ready(function() {
            allocInitDurationDropdown();
            allocInitRowsEvents(editMode);
            allocInitEdit(window.allocExistingLines, editMode);

            $('#carry-form').on('submit', function(e) {
                e.preventDefault();
                allocClearErrors();

                var hasError = false;
                var requiredFields = {
                    'alloc-financial-year': 'Financial Year is required.',
                    'alloc-duration': 'Carry Forward Type is required.',
                    'alloc-from-sub-duration': 'From Duration is required.',
                    'alloc-to-sub-duration': 'To Duration is required.',
                };

                $.each(requiredFields, function(id, msg) {
                    var val = $('#' + id).val();
                    if (!val || !val.trim()) {
                        allocSetError(id, msg);
                        hasError = true;
                    }
                });

                if ($('.alloc-row-item').length === 0) {
                    toastr.error('Please add at least one row.');
                    hasError = true;
                }

                if (allocHasDuplicateRow()) {
                    toastr.error('Duplicate row: Major + Sub-Component combination must be unique.');
                    hasError = true;
                }

                var fromSub = $('#alloc-from-sub-duration').val();
                var toSub = $('#alloc-to-sub-duration').val();
                if (fromSub && toSub && fromSub === toSub) {
                    allocSetError('alloc-to-sub-duration', 'From Duration and To Duration must be different.');
                    toastr.error('From Duration and To Duration must be different.');
                    hasError = true;
                }

                if (hasError) return;

                var payload = {
                    financial_year: $('#alloc-financial-year').val(),
                    from_duration_id: $('#alloc-duration').val(),
                    to_duration_id: $('#alloc-to-duration').val(),
                    from_sub_duration_id: $('#alloc-from-sub-duration').val() || null,
                    to_sub_duration_id: $('#alloc-to-sub-duration').val() || null,
                    total_amount: $('#total-carry-hidden').val() || 0,
                    remarks: $('textarea[name="remarks"]').val() || null,
                    carry_forward_lines: []
                };

                $('.alloc-row-item').each(function() {
                    var rowId = $(this).data('row-id');
                    payload.carry_forward_lines.push({
                        id: $(this).find('input[name*="[id]"]').val() || null,
                        major_component_id: $('#alloc-major-' + rowId).val() || '',
                        sub_component_id: $('#alloc-sub-' + rowId).val() || null,
                        opening_balance: $('#alloc-opening-' + rowId).val() || 0,
                        carried_forward_amount: $('#alloc-amount-' + rowId).val() || '',
                    });
                });

                var submitUrl = editMode ? "{{ url('carry-forward-mapping/store') }}/" + "{{ $row['id'] ?? '' }}" : "{{ url('carry-forward-mapping/store') }}";

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
                        if (result && (result.success || result.status === true || result.message)) {
                            toastr.success(result.message || 'Saved successfully.');
                            setTimeout(function() {
                                window.location.href = "{{ url('carry-forward-mapping') }}";
                            }, 1200);
                        } else if (result && result.errors) {
                            allocShowServerErrors(result.errors);
                            toastr.error('Please fix the highlighted errors.');
                        } else {
                            toastr.error(result && result.message ? result.message : 'Something went wrong.');
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
