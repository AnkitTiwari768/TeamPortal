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
                    <div class="card-header d-flex align-items-center py-3"
                        style="background: #ffffff; border-bottom: 1px solid #e2e8f0;">
                        <div class="heading">
                            <h1>
                                @if (!empty($row['id']))
                                    {{ __('fund_flow.edit_allocation') }}
                                @else
                                    {{ __('fund_flow.add_allocation') }}
                                @endif
                            </h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0" style="background: none; padding: 0;">
                                    <li class="breadcrumb-item"><a href="{{ url('dashboard') }}"
                                            style="color: #64748b; text-decoration: none;">{{ __('fund_flow.home') }}</a>
                                    </li>
                                    <li class="breadcrumb-item"><a href="{{ url('fund-allocations') }}"
                                            style="color: #64748b; text-decoration: none;">{{ __('fund_flow.allocation') }}</a>
                                    </li>
                                    <li class="breadcrumb-item active" aria-current="page"
                                        style="color: #0f172a; font-weight: 500;">
                                        @if (!empty($row['id']))
                                            {{ __('fund_flow.edit_allocation') }}
                                        @else
                                            {{ __('fund_flow.add_allocation') }}
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

                    {{-- ── Card Body ────────────────────────────────────── --}}
                    <div class="card-body p-0">
                        <form id="alloc-form">
                            @csrf
                            @if (!empty($row['id']))
                                <input type="hidden" name="_method" value="PUT">
                                <input type="hidden" name="id" value="{{ $row['id'] }}">
                            @endif

                            <div class="p-4">
                                {{-- ── Section 1: Period details ── --}}
                                <div class="form-section-card">
                                    <h5 class="form-section-title">
                                        <i class="fa fa-calendar"></i> Period Details
                                    </h5>
                                    <div class="row">
                                        {{-- ── Financial Year ─────────────────────────────────────────── --}}
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label
                                                    class="required form-label">{{ __('fund_flow.financial_year') }}</label>
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

                                        {{-- ── Duration ────────────────────────────────────────────────── --}}
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="required form-label">{{ __('fund_flow.duration') }}</label>
                                                <select name="duration_id" id="alloc-duration" class="form-select">
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
                                                <span class="text-danger form-error" id="alloc-duration_error"></span>
                                            </div>
                                        </div>

                                        {{-- ── Sub-Duration (conditional) ──────────────────────────────── --}}
                                        <div class="col-md-4" id="alloc-sub-duration-wrapper" style="display:none;">
                                            <div class="mb-3">
                                                <label
                                                    class="form-label required">{{ __('fund_flow.sub_duration') }}</label>
                                                <select class="form-select" id="alloc-sub-duration" name="sub_duration_id">
                                                    <option value="">{{ __('fund_flow.select_sub_duration') }}
                                                    </option>
                                                </select>
                                                <span class="text-danger form-error" id="alloc-sub-duration_error"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- ── Yearly Balance Banner ── --}}
                                <div id="alloc-yearly-balance-banner" class="alert alert-success d-flex align-items-center d-none" style="border-radius: 12px; margin-bottom: 1.5rem;">
                                    <i class="fa fa-info-circle me-2 text-success" style="font-size: 1.25rem;"></i>
                                    <span id="alloc-yearly-balance-text"></span>
                                </div>

                                {{-- ── Previous Balance Banner ── --}}
                                <div id="alloc-previous-balance-banner" class="alert alert-info d-flex align-items-center justify-content-between d-none" style="border-radius: 12px; margin-bottom: 1.5rem;">
                                    <div>
                                        <i class="fa fa-info-circle me-2 text-primary" style="font-size: 1.25rem;"></i>
                                        <span id="alloc-previous-balance-text">You have an available balance from previous month. Do you want to add it?</span>
                                    </div>
                                    <button type="button" class="btn btn-primary btn-sm px-4" id="btn-add-previous-balance">
                                        <i class="fa fa-plus me-1"></i> Add
                                    </button>
                                </div>
                                <input type="hidden" name="apply_carry_forward" id="alloc-apply-carry-forward" value="0">

                                {{-- ── Existing Allocation Note (shown instead of the Add Fresh Allocation button when an allocation already exists for this exact period) ── --}}
                                <div id="alloc-existing-allocation-note" class="alert alert-secondary d-flex align-items-center d-none" style="border-radius: 12px; margin-bottom: 1.5rem;">
                                    <i class="fa fa-check-circle me-2 text-secondary" style="font-size: 1.25rem;"></i>
                                    <span id="alloc-existing-allocation-text"></span>
                                </div>

                                {{-- ── Summary Cards: Opening Balance, Fresh Allocations, Total Available Funds ── --}}
                                <div class="row g-3 mb-4" id="alloc-summary-cards">
                                    {{-- Opening Balance Card --}}
                                    <div class="col-md-4 col-12">
                                        <div class="card p-3 shadow-sm h-100"
                                            style="border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff; transition: all 0.2s ease;">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="text-muted fw-semibold"
                                                    style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                                    {{ __('fund_flow.opening_balance') }}
                                                </span>
                                                <div class="p-2 rounded-circle" style="background: #e0f2fe; color: #0284c7;">
                                                    <i class="fa fa-university" style="font-size: 1.1rem;"></i>
                                                </div>
                                            </div>
                                            <div class="fw-bold text-dark mb-1" style="font-size: 1.5rem;" id="card-opening-balance">
                                                ₹ 0.00
                                            </div>
                                            <small class="text-muted" style="font-size: 0.75rem;">
                                                Carried forward funds
                                            </small>
                                        </div>
                                    </div>

                                    {{-- Fresh Allocations Card --}}
                                    <div class="col-md-4 col-12">
                                        <div class="card p-3 shadow-sm h-100"
                                            style="border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff; transition: all 0.2s ease;">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="text-muted fw-semibold"
                                                    style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                                    {{ __('fund_flow.fresh_allocations') }}
                                                </span>
                                                <div class="p-2 rounded-circle" id="fresh-allocation-toggle-btn"
                                                     style="background: #dbeafe; color: #2563eb; cursor: pointer;"
                                                     title="Add Fresh Allocation Amount"
                                                     data-bs-toggle="modal" data-bs-target="#freshAllocationModal">
                                                    <i class="fa fa-plus-circle" style="font-size: 1.1rem;"></i>
                                                </div>
                                            </div>
                                            <div class="fw-bold text-primary mb-1" style="font-size: 1.5rem;" id="card-fresh-allocations">
                                                ₹ 0.00
                                            </div>
                                            <small class="text-muted" style="font-size: 0.75rem;">
                                                Total fresh allocations for selected period
                                            </small>
                                        </div>
                                    </div>

                                    {{-- Total Available Funds Card --}}
                                    <div class="col-md-4 col-12">
                                        <div class="card p-3 shadow-sm h-100"
                                            style="border: 2px solid #3b82f6; border-radius: 12px; background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%); transition: all 0.2s ease;">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="text-primary fw-bold"
                                                    style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                                    {{ __('fund_flow.total_available_funds') }}
                                                </span>
                                                <div class="p-2 rounded-circle" style="background: #2563eb; color: #ffffff;">
                                                    <i class="fa fa-google-wallet" style="font-size: 1.1rem;"></i>
                                                </div>
                                            </div>
                                            <div class="fw-bold text-primary mb-1" style="font-size: 1.5rem;" id="card-total-available-funds">
                                                ₹ 0.00
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- ── Section 2: Allocation Table ── --}}
                                <div class="form-section-card">
                                    <h5 class="form-section-title">
                                        <i class="fa fa-list-ol"></i> Component Allocations
                                    </h5>
                                    <div class="row" id="personality">
                                        <div class="col-lg-12">
                                            <table class="table table-themed table-striped table-bordered mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('fund_flow.major_component') }}</th>
                                                        <th>{{ __('fund_flow.sub_component') }}</th>
                                                        <th style="width: 200px;" class="d-none">Fresh Allocation Amount</th>
                                                        <th style="width: 200px;">{{ __('fund_flow.amount_rs') }}</th>
                                                        <th style="width: 120px; text-align: center;">
                                                            {{ __('fund_flow.add_remove') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="alloc-rows-body">
                                                    {{-- Rows injected by Mustache template --}}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                {{-- ── Section 3: Financial & Sanction Details ── --}}
                                <div class="form-section-card">
                                    <h5 class="form-section-title">
                                        <i class="fa fa-file-text-o"></i> Sanction & Financial Details
                                    </h5>
                                    <div class="row">
                                        {{-- ── Total Amount ────────────────────────────────────────────── --}}
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label
                                                    class="required form-label">{{ __('fund_flow.total_amount_allocated_rs') }}</label>
                                                <input type="text" class="form-control numericOnly fw-bold text-primary"
                                                    name="total_amount" id="alloc-total-hidden" maxlength="15" readonly
                                                    placeholder="0.00" value="{{ $row['total_amount'] ?? '' }}"
                                                    style="background-color: #f8fafc; font-size: 1.1rem;">
                                                <input type="hidden" name="fresh_allocation_amount" id="alloc-fresh-allocation-hidden" value="{{ $row['total_amount_allocated'] ?? 0 }}">
                                                <span class="text-danger form-error" id="alloc-total_error"></span>
                                            </div>
                                        </div>

                                        {{-- ── Sanction Order Number ────────────────────────────────────── --}}
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label
                                                    class="required form-label">{{ __('fund_flow.sanction_order_number') }}</label>
                                                <input type="text" class="form-control alphaNumeric"
                                                    name="sanction_order_number" id="alloc-sanction-order-no"
                                                    maxlength="100" placeholder=""
                                                    value="{{ $row['sanction_order_number'] ?? '' }}">
                                                <span class="text-danger form-error"
                                                    id="alloc-sanction-order-no_error"></span>
                                            </div>
                                        </div>

                                        {{-- ── Sanction Order Date ──────────────────────────────────────── --}}
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label
                                                    class="required form-label">{{ __('fund_flow.sanction_order_date') }}</label>
                                                <input type="text" class="form-control" name="sanction_order_date"
                                                    id="alloc-sanction-order-date" placeholder="DD-MM-YYYY"
                                                    autocomplete="off"
                                                    value="{{ !empty($row['sanction_order_date']) ? date('d-m-Y', strtotime($row['sanction_order_date'])) : '' }}">
                                                <span class="text-danger form-error"
                                                    id="alloc-sanction-order-date_error"></span>
                                            </div>
                                        </div>

                                        {{-- ── Upload Document ──────────────────────────────────────────── --}}
                                        <div class="col-md-4">
                                            <div class="input-box mb-3">
                                                <label class="form-label">
                                                    {{ __('fund_flow.upload_document') }}
                                                    <a class="tooltip-ins" href="#" data-toggle="tooltip"
                                                        title="File must be PDF/Image and less than 2MB"
                                                        style="color: #64748b;">
                                                        <i class="fa fa-question-circle" aria-hidden="true"></i>
                                                    </a>
                                                </label>

                                                <input type="file" class="form-control" id="alloc-doc-trigger"
                                                    accept=".pdf,application/pdf" />
                                                <small class="text-muted d-block mt-1"
                                                    style="font-size: 0.75rem;">{{ __('fund_flow.note_pdf_only') }}</small>

                                                <div class="upload_file_link" id="alloc-doc-name">
                                                    @if (isset($docUrl) && (isset($id) || isset($row['id'])))
                                                        <div class="doc-badge-container">
                                                            <span class="doc-badge-label">
                                                                <i class="fa fa-paperclip"></i>
                                                                {{ __('fund_flow.attached_file') ?? 'Attachment:' }}
                                                            </span>
                                                            <a href="{{ route('fund-allocations.download-document', $id ?? $row['id']) }}"
                                                                target="_blank" class="btn-doc-download">
                                                                <i class="fa fa-download"></i>
                                                                {{ __('fund_flow.download_existing_document') }}
                                                            </a>
                                                            <a href="{{ route('fund-allocations.view-document', $id ?? $row['id']) }}"
                                                                target="_blank" class="btn-doc-view">
                                                                <i class="fa fa-file-pdf-o"></i>
                                                                {{ __('fund_flow.view_existing_document') }}
                                                            </a>
                                                        </div>
                                                    @endif
                                                </div>
                                                <span class="text-danger form-error" id="alloc-doc-trigger_error"></span>
                                                <input type="hidden" name="document_path" id="alloc-doc-hidden"
                                                    value="{{ $row['document_path'] ?? '' }}" />
                                                <div id="alloc-doc-loader" class="d-none mt-1">
                                                    <div class="spinner-border spinner-border-sm text-primary"
                                                        role="status">
                                                        <span class="visually-hidden">Uploading...</span>
                                                    </div>
                                                    <span class="ms-1">Uploading...</span>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- ── Remarks ──────────────────────────────────────────────────── --}}
                                        <div class="col-md-8">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('fund_flow.remarks') }}</label>
                                                <textarea class="form-control" name="remarks" id="alloc-remarks" rows="2"
                                                    placeholder="Write any additional remarks here...">{{ old('remarks', $row['remarks'] ?? '') }}</textarea>
                                                <span class="text-danger form-error" id="alloc-remarks_error"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-action">
                                @include('components.admin.buttons.submit-button')
                                @include('components.admin.buttons.cancel-button')
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>


    <script id="alloc_row_template" type="x-tmpl-mustache">
<tr class="alloc-row-item" data-row-id="@{{rowId}}">
    <td>
        <input type="hidden" name="allocation_lines[@{{rowId}}][id]" value="@{{lineId}}">
        <select class="form-select alloc-major-component"
                name="allocation_lines[@{{rowId}}][major_component_id]"
                id="alloc-major-@{{rowId}}"
                data-row-id="@{{rowId}}">
            <option value="">-- Select --</option>
        </select>
        <div class="text-danger form-error" id="alloc-major-@{{rowId}}_error"></div>
    </td>
    <td>
        <select class="form-select alloc-sub-component"
                name="allocation_lines[@{{rowId}}][sub_component_id]"
                id="alloc-sub-@{{rowId}}"
                data-row-id="@{{rowId}}">
            <option value="">-- Select --</option>
        </select>
        <div class="text-danger form-error" id="alloc-sub-@{{rowId}}_error"></div>
        <div class="text-muted d-none mt-1" id="alloc-balance-info-@{{rowId}}" style="font-size: 0.75rem;">
            Current Allocation: <span id="alloc-balance-val-@{{rowId}}" class="fw-bold text-dark"></span>
        </div>
    </td>
    <td class="d-none">
        <input type="text"
               class="form-control alloc-fresh-amount-input text-end"
               id="alloc-fresh-amount-@{{rowId}}"
               placeholder="0.00"
               value=""
               disabled readonly>
    </td>
    <td>
        <input type="text"
               class="form-control alloc-amount-input text-end"
               name="allocation_lines[@{{rowId}}][amount]"
               id="alloc-amount-@{{rowId}}"
               placeholder="0.00"
               value="@{{amount}}"
               @{{#lineId}}disabled readonly@{{/lineId}}>
   
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

    {{-- ── Fresh Allocation Popup Modal ───────────────────────── --}}
    <div class="modal fade" id="freshAllocationModal" tabindex="-1" aria-labelledby="freshAllocationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 12px; border: 1px solid #e2e8f0;">
                <div class="modal-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; border-radius: 12px 12px 0 0;">
                    <h5 class="modal-title fw-bold" id="freshAllocationModalLabel" style="font-size: 1.1rem; color: #1e293b;">
                        <i class="fa fa-plus-circle text-primary me-2"></i> Add Fresh Allocation Amount
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="modal-fresh-allocation-input" class="form-label fw-bold">Fresh Allocation Amount (₹)</label>
                        <div class="input-group">
                            <span class="input-group-text fw-bold">₹</span>
                            <input type="text" class="form-control numericOnly fw-bold" id="modal-fresh-allocation-input" placeholder="0.00" autocomplete="off" style="font-size: 1.1rem;">
                        </div>
                        <small class="text-muted mt-1 d-block" style="font-size: 0.75rem;">
                            This fresh amount will be added to the Fresh Allocations card and updated in Total Available Funds.
                        </small>
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm px-4" id="btn-submit-fresh-allocation">
                        <i class="fa fa-check me-1"></i> Submit
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    @include('scripts/attribute_dependency_dropdown')
    @include('scripts/upload_file')
    <script>
        window.allocBaseUrl = "{{ url('/') }}";
        window.csrfToken = "{{ csrf_token() }}";
        window.allocAttrCodes = {
            majorComponent: "{{ config('allocation.major_component_code', 'major-components') }}",
            component: "{{ config('allocation.component_code', 'PLACEHOLDER_COMPONENT') }}",
            subComponent: "{{ config('allocation.sub_component_code', 'major-components') }}",
            duration: "{{ config('allocation.duration_code', 'duration') }}",
        };
        window.allocExistingLines = @json($lines ?? []);
        window.allocExistingSubDuration = "{{ $row['sub_duration_id'] ?? '' }}";

        var allocRowCounter = 0;
        var editMode = {{ !empty($row['id']) ? 'true' : 'false' }};

        var windowOpeningBalance = 0;
        var windowExistingFreshAllocations = 0;
        var windowUserFreshAllocation = {{ isset($row['total_amount_allocated']) ? (float) $row['total_amount_allocated'] : 0 }};

        function formatRupee(amount) {
            return '₹ ' + (new Intl.NumberFormat('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(amount || 0));
        }

        function allocUpdateSummaryCards() {
            var newAllocationsSum = 0;
            var currentBalanceSum = 0;
            
            $('.alloc-row-item').each(function() {
                var rowId = $(this).data('row-id');
                var $amtInput = $('#alloc-amount-' + rowId);
                var rowAmt = parseFloat(($amtInput.val() || '').replace(/,/g, '')) || 0;
                var alreadyAlloc = parseFloat($amtInput.data('already-allocated')) || 0;
                var isExistingLine = $amtInput.prop('disabled') || $amtInput.prop('readonly');

                newAllocationsSum += rowAmt;

                // Only offset for rows that already belong to THIS fund allocation
                // (locked/readonly, i.e. saved lineId) -- their amount is counted again
                // below via newAllocationsSum, so it must be added back here to avoid
                // double-subtracting it. A newly added row's "Current Allocation" is the
                // selected component's balance from OTHER unrelated allocations and must
                // never inflate this period's Total Available Funds.
                if (isExistingLine) {
                    currentBalanceSum += alreadyAlloc;
                }
            });

            var baseFreshAllocations = windowExistingFreshAllocations + windowUserFreshAllocation;
            var baseOpeningBalance = windowOpeningBalance + currentBalanceSum;
            var baseAvailableFunds = baseOpeningBalance + baseFreshAllocations;
            
            var totalAvailableFunds = baseAvailableFunds - newAllocationsSum;

            // The Opening Balance card always shows the period's own opening balance
            // (windowOpeningBalance) only, never mixed with any row's "Current Allocation".
            $('#card-opening-balance').text(formatRupee(windowOpeningBalance));
            $('#card-fresh-allocations').text(formatRupee(baseFreshAllocations));
            $('#card-total-available-funds').text(formatRupee(totalAvailableFunds));
            
            return {
                baseAvailable: baseAvailableFunds,
                remainingAvailable: totalAvailableFunds,
                openingBalance: baseOpeningBalance,
                freshAllocations: baseFreshAllocations,
                sumComponentAmounts: newAllocationsSum
            };
        }

        function allocValidateComponentAllocations() {
            var funds = allocUpdateSummaryCards();
            var isValid = true;

            // Check if Opening Balance == 0 and Fresh Allocation == 0
            $('.alloc-row-item').each(function() {
                var rowId = $(this).data('row-id');
                var $amtInput = $('#alloc-amount-' + rowId);
                var rowAmt = parseFloat(($amtInput.val() || '').replace(/,/g, '')) || 0;
                var $errLabel = $('#alloc-amount-' + rowId + '_error');
                
                $errLabel.text('');

                if (rowAmt > 0 && funds.openingBalance <= 0 && funds.freshAllocations <= 0) {
                    $errLabel.text('Please add Fresh Allocation first, then you can add Component Allocation.');
                    $amtInput.val('');
                    isValid = false;
                }
            });

            // Re-fetch funds in case values were cleared
            if (!isValid) {
                funds = allocUpdateSummaryCards();
            }

            // Check if total component allocations exceed base available funds
            if (funds.sumComponentAmounts > funds.baseAvailable && funds.baseAvailable >= 0) {
                $('.alloc-row-item').each(function() {
                    var rowId = $(this).data('row-id');
                    var $amtInput = $('#alloc-amount-' + rowId);
                    var rowAmt = parseFloat(($amtInput.val() || '').replace(/,/g, '')) || 0;
                    if (rowAmt > 0) {
                        $('#alloc-amount-' + rowId + '_error').text('Total component allocations (' + formatRupee(funds.sumComponentAmounts) + ') cannot exceed Total Available Funds (' + formatRupee(funds.baseAvailable) + ').');
                    }
                });
                isValid = false;
            }

            return isValid;
        }

        function allocUpdateExistingAllocationNote(hasExisting, existingAmount, hasEnclosingAvailable, enclosingAmount, enclosingDurationName, enclosingSubDurationName) {
            var $note = $('#alloc-existing-allocation-note');
            var $addBtn = $('#fresh-allocation-toggle-btn');

            // Ensure the + button is always visible
            $addBtn.removeClass('d-none');

            // An allocation already existing for this exact period (same Duration + Sub
            // Duration) is a legitimate top-up scenario (merging more funds into it), not
            // a duplicate to warn about -- so no note, and the "+ Add Fresh Allocation"
            // button stays available. Only the hasEnclosingAvailable case below (money
            // inherited from a genuinely different, coarser period) still shows a message.
            if (hasEnclosingAvailable && !editMode) {
                // No allocation directly at this exact period, but a coarser enclosing
                // period (e.g. the Half-Year this Quarter falls within) already has money
                // available -- name THAT source period (not the currently-selected one)
                // so the user knows where the amount is coming from, instead of letting
                // them create a duplicate/overlapping Fresh Allocation on top of it.
                var periodLabel = enclosingSubDurationName
                    ? (enclosingDurationName + ' - ' + enclosingSubDurationName)
                    : enclosingDurationName;
                $('#alloc-existing-allocation-text').text(
                    'You have funds available for ' + periodLabel + ' with amount ' + formatRupee(enclosingAmount) + '.'
                );
                $note.removeClass('d-none');
            } else {
                $note.addClass('d-none');
            }
        }

        // Clear everything on the form that's specific to the PREVIOUSLY selected
        // period, so switching Financial Year, Duration, or Sub Duration never leaves
        // stale Opening Balance, Category/Component rows/amounts, or banners from the
        // old selection mixed in with the newly selected period's data.
        // resetDurationSelection is true only when the Financial Year itself changed --
        // Duration/Sub Duration are cleared for re-selection in that case, but must NOT
        // be reset when this runs as a result of the user picking a Duration/Sub
        // Duration in the first place (that would fight their own selection).
        function allocResetPeriodSpecificState(resetDurationSelection) {
            if (resetDurationSelection) {
                $('#alloc-duration').val('');
                $('#alloc-sub-duration-wrapper').hide();
                $('#alloc-sub-duration').html('<option value="">{{ __('fund_flow.select_sub_duration') }}</option>');
            }

            // Fresh Allocation amount entered (via the modal) for the previous period.
            windowUserFreshAllocation = 0;
            $('#alloc-fresh-allocation-hidden').val(0);
            $('.alloc-fresh-amount-input').val('');

            // Category / Component allocation rows, each with amounts and per-row
            // "already allocated" balances tied to the previous period.
            $('#alloc-rows-body').empty();
            allocAddRow(false);

            // Banners/notes driven by the previous period's summary -- hide until the
            // fresh fetch below re-evaluates them for the newly selected period.
            $('#alloc-yearly-balance-banner').addClass('d-none');
            $('#alloc-previous-balance-banner').addClass('d-none');
            $('#alloc-existing-allocation-note').addClass('d-none');
            $('#fresh-allocation-toggle-btn').removeClass('d-none');
            $('#alloc-apply-carry-forward').val('0');

            allocClearErrors();
            allocUpdateSummaryCards();
        }

        function allocFetchPeriodSummary() {
            var fy = $('#alloc-financial-year').val();
            var durationId = $('#alloc-duration').val();
            var subDurationId = $('#alloc-sub-duration-wrapper').is(':visible') ? $('#alloc-sub-duration').val() : '';
            var allocationId = "{{ $row['id'] ?? '' }}";

            if (!fy || !durationId) {
                windowOpeningBalance = 0;
                windowExistingFreshAllocations = 0;
                allocUpdateSummaryCards();
                return;
            }

            $('#ajax-loader').show();
            $.ajax({
                url: window.allocBaseUrl + '/fund-allocation/period-summary',
                method: 'GET',
                data: {
                    financial_year: fy,
                    duration_id: durationId,
                    sub_duration_id: subDurationId,
                    allocation_id: allocationId
                },
                success: function(res) {
                    var data = (res && res.data) ? res.data : (res || {});
                    windowOpeningBalance = parseFloat(data.opening_balance) || 0;
                    windowExistingFreshAllocations = parseFloat(data.existing_fresh_allocations) || 0;
                    allocUpdateSummaryCards();
                    allocUpdateExistingAllocationNote(
                        !!data.has_existing_allocation,
                        parseFloat(data.existing_allocated_amount) || 0,
                        !!data.has_enclosing_available_balance,
                        parseFloat(data.enclosing_available_amount) || 0,
                        data.enclosing_duration_name,
                        data.enclosing_sub_duration_name
                    );
                    var durationText = $('#alloc-duration option:selected').text().trim();
                    if (durationText === 'Yearly') {
                        allocCheckYearlyBalance(fy);
                    } else {
                        $('#alloc-yearly-balance-banner').addClass('d-none');
                    }
                    // Let allocCheckPreviousBalance handle hiding the loader
                    allocCheckPreviousBalance(fy, durationId, subDurationId);
                },
                error: function() {
                    windowOpeningBalance = 0;
                    windowExistingFreshAllocations = 0;
                    allocUpdateSummaryCards();
                    allocUpdateExistingAllocationNote(false, 0, false, 0);
                    $('#alloc-previous-balance-banner').addClass('d-none');
                    $('#ajax-loader').hide();
                }
            });
        }

        function allocCheckPreviousBalance(fy, durationId, subDurationId) {
            $('#alloc-previous-balance-banner').addClass('d-none');
            $('#alloc-apply-carry-forward').val('0');

            if (!fy || !durationId) {
                $('#ajax-loader').hide();
                return;
            }

            $('#ajax-loader').show();
            $.ajax({
                url: window.allocBaseUrl + '/fund-allocation/check-previous-balance',
                method: 'GET',
                data: {
                    financial_year: fy,
                    duration_id: durationId,
                    sub_duration_id: subDurationId
                },
                success: function(res) {
                    var data = (res && res.data) ? res.data : (res || {});
                    if (data.has_previous_balance) {
                        var amt = parseFloat(data.amount) || 0;
                        if (amt > 0) {
                            // Monthly now goes through the same Note + Add button flow as
                            // Quarterly/Half-Yearly instead of silently auto-applying, so
                            // the same "crossing into the next half" behavior applies
                            // consistently for all months.
                            var formattedAmt = formatRupee(amt).replace('₹ ', '₹');
                            var msg = "You have an available balance from " + (data.source_sub_duration_name || 'previous period') + " (" + formattedAmt + "). Do you want to add it?";
                            $('#alloc-previous-balance-text').text(msg);
                            $('#btn-add-previous-balance').data('amount', amt);
                            $('#alloc-previous-balance-banner').removeClass('d-none');
                        }
                    }
                },
                complete: function() {
                    $('#ajax-loader').hide();
                }
            });
        }

        function allocCheckYearlyBalance(fy) {
            $('#alloc-yearly-balance-banner').addClass('d-none');
            if (!fy) return;

            $.ajax({
                url: window.allocBaseUrl + '/fund-allocation/yearly-note-balance',
                method: 'GET',
                data: { financial_year: fy },
                success: function(res) {
                    var data = (res && res.data) ? res.data : (res || {});
                    var total = parseFloat(data.total_balance) || 0;
                    if (total > 0) {
                        $('#alloc-yearly-balance-text').text('Available Balance from Current Financial Year : ' + formatRupee(total));
                        $('#alloc-yearly-balance-banner').removeClass('d-none');
                    }
                }
            });
        }

        function allocRecalcTotal() {
            var total = 0;
            $('input.alloc-amount-input').each(function() {
                var val = parseFloat($(this).val().replace(/,/g, ''));
                if (!isNaN(val) && val >= 0) total += val;
            });
            $('#alloc-total-hidden').val(total.toFixed(2));
            allocUpdateSummaryCards();
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

            $('#ajax-loader').show();
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
                },
                complete: function() {
                    $('#ajax-loader').hide();
                }
            });
        }

        function allocLoadSubComponents(majorId, rowId, selectedSubId) {
            allocLoadAttributeValues(
                window.allocAttrCodes.majorComponent,
                majorId,
                'alloc-sub-' + rowId,
                selectedSubId
            );
        }

        function allocAddRow(editMode, lineData) {
            var rowId = Date.now() + (++allocRowCounter);
            lineData = lineData || {};

            var source = $('#alloc_row_template').html();
            Mustache.parse(source);
            var rendered = Mustache.render(source, {
                rowId: rowId,
                lineId: lineData.id || '',
                amount: lineData.amount || '',
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
        }

        function allocRemoveRow(rowId) {
            if ($('.alloc-row-item').length <= 1) {
                toastr.warning('At least one allocation row is required.');
                return;
            }
            $('[data-row-id="' + rowId + '"].alloc-row-item').fadeOut(200, function() {
                $(this).remove();
                allocRecalcTotal();
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
        }

        function allocInitRowsEvents(editMode) {
            $('#freshAllocationModal').on('shown.bs.modal', function() {
                $('#modal-fresh-allocation-input').focus();
            });

            $(document).on('click', '#btn-submit-fresh-allocation', function() {
                var inputVal = $('#modal-fresh-allocation-input').val() || '';
                var freshAmt = parseFloat(inputVal.replace(/,/g, '')) || 0;

                // Set the value to the first row's fresh amount column
                var $firstFreshInput = $('.alloc-fresh-amount-input').first();
                if ($firstFreshInput.length) {
                    $firstFreshInput.val(freshAmt > 0 ? freshAmt.toFixed(2) : '');
                }

                windowUserFreshAllocation = freshAmt;
                $('#alloc-fresh-allocation-hidden').val(freshAmt.toFixed(2));
                allocUpdateSummaryCards();
                allocValidateComponentAllocations();

                // Clear the modal's own input once its value has been applied to the
                // Fresh Allocations card/hidden field above, so reopening the modal shows
                // a blank field instead of the amount that was already submitted.
                $('#modal-fresh-allocation-input').val('');

                var modalEl = document.getElementById('freshAllocationModal');
                var modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modal.hide();
            });

            $(document).on('click', '#btn-add-previous-balance', function() {
                var amt = parseFloat($(this).data('amount')) || 0;
                windowOpeningBalance += amt;
                $('#alloc-apply-carry-forward').val('1');
                $('#alloc-previous-balance-banner').addClass('d-none');
                allocUpdateSummaryCards();
                allocValidateComponentAllocations();
                toastr.success('Available balance added to Opening Balance.');
            });

            $(document).on('input', '#modal-fresh-allocation-input', function() {
                this.value = this.value.replace(/[^0-9.]/g, '');
            });

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
                $('#alloc-balance-info-' + rowId).addClass('d-none');
                if (majorId) allocLoadSubComponents(majorId, rowId, null);
                if (allocHasDuplicateRow()) toastr.warning('Duplicate combination detected.');
            });

            $(document).on('change', '.alloc-sub-component', function() {
                var rowId = $(this).data('row-id');
                if (allocHasDuplicateRow()) toastr.warning('Duplicate combination detected.');
                allocFetchComponentBalance(rowId);
            });

            $(document).on('input', '.alloc-amount-input', function() {
                this.value = this.value.replace(/[^0-9.]/g, '');
                allocRecalcTotal();
                allocValidateComponentAllocations();
            });

            $(document).on('focus', '.form-control, .form-select', function() {
                $(this).closest('td').find('.form-error').text('');
            });
        }

        function allocFetchComponentBalance(rowId) {
            var fy = $('#alloc-financial-year').val();
            var durationId = $('#alloc-duration').val();
            var subDurationId = $('#alloc-sub-duration-wrapper').is(':visible') ? $('#alloc-sub-duration').val() : '';
            var majorId = $('#alloc-major-' + rowId).val();
            var subId = $('#alloc-sub-' + rowId).val();

            if (!fy || !durationId || !majorId) {
                $('#alloc-balance-info-' + rowId).addClass('d-none');
                return;
            }

            $('#ajax-loader').show();
            $.ajax({
                url: window.allocBaseUrl + '/fund-allocation/component-balance',
                method: 'GET',
                data: {
                    financial_year: fy,
                    duration_id: durationId,
                    sub_duration_id: subDurationId,
                    major_component_id: majorId,
                    sub_component_id: subId
                },
                success: function(res) {
                    var data = (res && res.data) ? res.data : (res || {});
                    var bal = parseFloat(data.remaining_balance) || 0;
                    var totalAlloc = parseFloat(data.total_allocated) || 0;
                    var alreadyAlloc = parseFloat(data.already_allocated) || 0;
                    var isExisting = data.is_existing || false;
                    $('#alloc-amount-' + rowId).data('is-existing', isExisting);
                    $('#alloc-amount-' + rowId).data('already-allocated', alreadyAlloc);
                    
                    $('#alloc-balance-val-' + rowId).text(formatRupee(bal));
                    $('#alloc-balance-info-' + rowId).removeClass('d-none');
                    
                    allocUpdateSummaryCards();
                    
                    allocValidateComponentAllocations();
                    
                },
                error: function() {
                    $('#alloc-balance-info-' + rowId).addClass('d-none');
                },
                complete: function() {
                    $('#ajax-loader').hide();
                }
            });
        }

        function allocInitDurationDropdown() {
            $('#alloc-duration').on('change', function() {
                var duration = $(this).val();
                var durationText = $(this).find('option:selected').text().trim();

                $('#alloc-sub-duration-wrapper').hide();
                $('#alloc-sub-duration').html(
                    '<option value="">{{ __('fund_flow.select_sub_duration') }}</option>');

                // A different Duration means the Opening Balance, Category/Component
                // rows/amounts, and banners already on screen belong to the PREVIOUSLY
                // selected duration's period -- clear that stale state (but never the
                // Duration/Sub Duration selection itself, since the user just picked it)
                // before fetching the newly selected duration's data.
                if (!editMode) {
                    allocResetPeriodSpecificState(false);
                }

                if (!duration || durationText === 'Yearly') {
                    allocFetchPeriodSummary();
                    return;
                }

                $('#alloc-sub-duration-wrapper').show();
                loadAttributeDropdownOptions(duration.toLowerCase(), "#alloc-sub-duration", true);
                allocFetchPeriodSummary();
            });

            $(document).on('change', '#alloc-sub-duration', function() {
                // Same reasoning as the Duration handler above, for switching Sub
                // Duration (e.g. First Half -> Second Half, or Q1 -> Q4) while the
                // Duration itself stays the same.
                if (!editMode) {
                    allocResetPeriodSpecificState(false);
                }
                allocFetchPeriodSummary();
            });

            var existingDuration = $('#alloc-duration').val();
            var existingSubDuration = window.allocExistingSubDuration || null;

            if (existingDuration) {
                var durationText = $('#alloc-duration').find('option:selected').text().trim();
                if (durationText && durationText !== 'Yearly') {
                    var attrCode = window.allocAttrCodes.duration;
                    allocLoadAttributeValues(
                        attrCode,
                        existingDuration,
                        'alloc-sub-duration',
                        existingSubDuration,
                        function() {
                            $('#alloc-sub-duration-wrapper').show();
                            allocFetchPeriodSummary();
                        }
                    );
                } else {
                    allocFetchPeriodSummary();
                }
            } else {
                allocFetchPeriodSummary();
            }
        }

        function allocInitFileUpload() {
            $(document).on('change', '#alloc-doc-trigger', function() {
                var file = this.files[0];
                if (!file) return;

                var maxSize = 2 * 1024 * 1024;
                if (file.size > maxSize) {
                    toastr.error("Maximum allowed file size is 2 MB.");
                    $(this).val('');
                    return;
                }

                uploadFile({
                    input: $('#alloc-doc-trigger'),
                    url: window.allocBaseUrl + "/fund-allocation/upload-documents",
                    fieldName: 'file',
                    loader: $('#alloc-doc-loader'),
                    success: function(response) {
                        var fileId = response.data?.uploaded_ids;
                        $('#alloc-doc-hidden').val(fileId);
                        $('#alloc-doc-name').html(
                            '<span class="text-success"><i class="fa fa-check-circle"></i> Uploaded</span>'
                        );
                    },
                    error: function() {
                        $('#alloc-doc-hidden').val('');
                        $('#alloc-doc-name').html('');
                    }
                });
            });
        }

        function allocClearErrors() {
            $('.form-error').text('');
        }

        function allocSetError(fieldId, message) {
            $('#' + fieldId + '_error').text(message);
        }

        function allocShowServerErrors(errors) {
            if (!errors || typeof errors !== 'object') return;
            $.each(errors, function(key, messages) {
                var msg = Array.isArray(messages) ? messages[0] : messages;
                var parts = key.split('.');
                var $el = null;

                if (parts.length === 3 && parts[0] === 'allocation_lines') {
                    var pointer = parts[1];
                    var field = parts[2];

                    $el = $('#alloc-' + (field === 'major_component_id' ? 'major' : (field === 'sub_component_id' ?
                        'sub' : 'amount')) + '-' + pointer + '_error');

                    if (!$el || !$el.length) {
                        var index = parseInt(pointer, 10);
                        var $targetRow = $('.alloc-row-item').eq(index);
                        if ($targetRow.length) {
                            var realId = $targetRow.data('row-id');
                            $el = $('#alloc-' + (field === 'major_component_id' ? 'major' : (field ===
                                'sub_component_id' ? 'sub' : 'amount')) + '-' + realId + '_error');
                        }
                    }
                } else {
                    if (key === 'sub_duration_id') {
                        $el = $('#alloc-sub-duration_error');
                    } else if (key === 'duration_id') {
                        $el = $('#alloc-duration_error');
                    } else {
                        $el = $('#' + key + '_error');

                        if (!$el.length) {
                            var alias = key.replace(/_/g, '-');
                            $el = $('#alloc-' + alias + '_error');
                        }

                        if (!$el.length) {
                            if (key === 'sanction_order_number') {
                                $el = $('#alloc-sanction-order-no_error');
                            } else if (key === 'document_path') {
                                $el = $('#alloc-doc-trigger_error');
                            } else {
                                var flat = key.replace(/\./g, '_');
                                $el = $('#' + flat + '_error');
                            }
                        }
                    }
                }

                if ($el && $el.length) {
                    $el.text(msg);
                }
            });
        }

        function updateSanctionDatePickerRange() {
            var fy = $('#alloc-financial-year').val();
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

                $("#alloc-sanction-order-date").datepicker("option", {
                    minDate: minDate,
                    maxDate: maxDate
                });
            }
        }

        $(document).ready(function() {

            $("#alloc-sanction-order-date").datepicker({
                dateFormat: "dd-mm-yy",
                changeYear: true,
                changeMonth: true,
                maxDate: 0
            });

            $('#alloc-financial-year').on('change', function() {
                // A different Financial Year means Opening Balance, Duration/Sub Duration,
                // and any Category/Component rows already filled in all belong to the
                // PREVIOUS year's period -- clear that stale state before fetching the
                // newly selected year's data, instead of leaving it mixed in.
                if (!editMode) {
                    allocResetPeriodSpecificState(true);
                }

                updateSanctionDatePickerRange();
                allocFetchPeriodSummary();

                var currentDateStr = $("#alloc-sanction-order-date").val();
                if (currentDateStr) {
                    var parts = currentDateStr.split('-');
                    if (parts.length === 3) {
                        var d = parseInt(parts[0], 10);
                        var m = parseInt(parts[1], 10) - 1;
                        var y = parseInt(parts[2], 10);
                        var currentDate = new Date(y, m, d);

                        var min = $("#alloc-sanction-order-date").datepicker("option", "minDate");
                        var max = $("#alloc-sanction-order-date").datepicker("option", "maxDate");

                        if ((min && currentDate < min) || (max && currentDate > max)) {
                            $("#alloc-sanction-order-date").val('');
                            toastr.warning(
                                'Sanction date cleared as it lies outside the selected financial year.');
                        }
                    }
                }
            });

            updateSanctionDatePickerRange();

            allocInitDurationDropdown();
            allocInitRowsEvents(editMode);
            allocInitEdit(window.allocExistingLines, editMode);
            allocInitFileUpload();
            allocFetchPeriodSummary();

            $('#alloc-form').on('submit', function(e) {
                e.preventDefault();
                allocClearErrors();

                var hasError = false;
                var requiredFields = {
                    'alloc-financial-year': 'Financial Year is required.',
                    'alloc-duration': 'Duration is required.',
                    'alloc-sanction-order-no': 'Sanction Order Number is required.',
                    'alloc-sanction-order-date': 'Sanction Order Date is required.',
                };

                $.each(requiredFields, function(id, msg) {
                    var val = $('#' + id).val();
                    if (!val || !val.trim()) {
                        allocSetError(id, msg);
                        hasError = true;
                    }
                });

                var isSubDurationRequired = false;
                if ($('#alloc-sub-duration-wrapper').length) {
                    if ($('#alloc-sub-duration-wrapper').is(':visible') || $('#alloc-sub-duration-wrapper').css('display') !== 'none') {
                        isSubDurationRequired = true;
                    }
                }

                if (isSubDurationRequired) {
                    var subDurationVal = $('#alloc-sub-duration').val();
                    if (!subDurationVal || !subDurationVal.trim()) {
                        allocSetError('alloc-sub-duration', 'Sub Duration is required.');
                        hasError = true;
                    }
                }

                // Client-side range validation for sanction order date
                var dateStr = $('#alloc-sanction-order-date').val();
                var fy = $('#alloc-financial-year').val();
                if (dateStr && fy) {
                    var parts = fy.split('-');
                    if (parts.length === 2) {
                        var startYear = parseInt(parts[0], 10);
                        var endYear = parseInt(parts[1], 10);
                        var minDate = new Date(startYear, 3, 1);
                        var maxDate = new Date(endYear, 2, 31);

                        var dParts = dateStr.split('-');
                        if (dParts.length === 3) {
                            var d = parseInt(dParts[0], 10);
                            var m = parseInt(dParts[1], 10) - 1;
                            var y = parseInt(dParts[2], 10);
                            var sDate = new Date(y, m, d);

                            if (sDate < minDate || sDate > maxDate) {
                                allocSetError('alloc-sanction-order-date',
                                    'Sanction Order Date must be within selected Financial Year (' +
                                    fy + ').');
                                hasError = true;
                            }
                        }
                    }
                }

                if ($('.alloc-row-item').length === 0) {
                    toastr.error('Please add at least one allocation row.');
                    hasError = true;
                }

                if (allocHasDuplicateRow()) {
                    toastr.error('Duplicate row: Major + Sub-Component combination must be unique.');
                    hasError = true;
                }

                // Run the fund-limit check first -- it unconditionally clears each row's
                // amount error span before re-evaluating, so the per-row checks below (which
                // may set their own amount error) must run AFTER it or their messages would
                // be wiped out immediately.
                if (!allocValidateComponentAllocations()) {
                    toastr.error('Component allocation amount cannot exceed Total Available Funds.');
                    hasError = true;
                }

                $('.alloc-row-item').each(function() {
                    var rowId = $(this).data('row-id');
                    var major = $('#alloc-major-' + rowId).val();
                    var sub = $('#alloc-sub-' + rowId).val();
                    var amount = $('#alloc-amount-' + rowId);

                    if (!major) {
                        $('#alloc-major-' + rowId + '_error').text('Category field is required.');
                        hasError = true;
                    }
                    if (!sub) {
                        $('#alloc-sub-' + rowId + '_error').text('Component is required.');
                        hasError = true;
                    }
                    if (!amount.prop('disabled')) {
                        var amtVal = String(amount.val());
                        if (!amtVal.match(/^\d{1,16}(\.\d{1,2})?$/)) {
                            $('#alloc-amount-' + rowId + '_error').text('Enter a valid amount.');
                            hasError = true;
                        } else if (parseFloat(amtVal) <= 0) {
                            $('#alloc-amount-' + rowId + '_error').text('Amount must be greater than 0.');
                            hasError = true;
                        }
                    }
                });

                if (hasError) return;

                var formData = $('#alloc-form').serializeArray();
                var payload = {};

                $.each(formData, function(i, field) {
                    if (field.name === '_method' || field.name === '_token') return;

                    var match = field.name.match(/^allocation_lines\[(\d+)\]\[(.+)\]$/);
                    if (match) {
                        var rowKey = match[1];
                        var fieldKey = match[2];
                        if (!payload.allocation_lines) payload.allocation_lines = {};
                        if (!payload.allocation_lines[rowKey]) payload.allocation_lines[
                            rowKey] = {};
                        payload.allocation_lines[rowKey][fieldKey] = field.value;
                    } else {
                        payload[field.name] = field.value;
                    }
                });

                $('.alloc-row-item').each(function() {
                    var rowId = $(this).data('row-id');
                    var $amount = $('#alloc-amount-' + rowId);
                    if ($amount.prop('disabled') || $amount.prop('readonly')) {
                        var rowKey = String(rowId);
                        if (!payload.allocation_lines) payload.allocation_lines = {};
                        if (!payload.allocation_lines[rowKey]) payload.allocation_lines[
                            rowKey] = {};
                        payload.allocation_lines[rowKey]['amount'] = $amount.val();
                    }
                });

                payload.total_available_amount = allocUpdateSummaryCards().remainingAvailable;

                var submitUrl = editMode ? "{{ url('fund-allocation/store') }}/" +
                    "{{ $row['id'] ?? '' }}" : "{{ url('fund-allocation/store') }}";

                $('#ajax-loader').show();

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
                            toastr.success(result.message || 'Allocation saved successfully.');
                            // Navigate immediately (replacing the submit state in history so
                            // the back button doesn't return to a just-submitted form) so the
                            // list page's DataTable re-fetches and shows the new record
                            // straight away, instead of appearing to sit idle for a second.
                            window.location.replace("{{ url('fund-allocations') }}");
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
                        $('#ajax-loader').hide();
                    }
                });
            });
        });
    </script>
@endsection
