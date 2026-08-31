@extends('components.admin.layout')

@section('page-content')
<div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="card container-main-card">

                {{-- ── Card Header ─────────────────────────────────── --}}
                <div class="card-header d-flex">
                    <div class="heading">
                        <h1>{{ __('Add Allocation') }}</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item"><a href="{{ url('web/allocation') }}">Allocation</a></li>
                                <li class="breadcrumb-item">Add Allocation</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="action-header ms-auto">
                        <div class="">
                            @include('components.admin.buttons.back-button')
                        </div>
                    </div>
                </div>

                {{-- ── Card Body ────────────────────────────────────── --}}
                <div class="card-body pt-1">
                    <form id="alloc-form">
                        @csrf

                        {{-- Reusable form component (header fields + allocation table) --}}
                        @include('web.allocation.components.form', [
                            'editMode'  => false,
                            'row'       => [],
                            'details'   => $details,
                            'durations' => $durations ?? [],
                        ])

                        <div class="form-action mt-3 mb-3">
                            @include('components.admin.buttons.submit-button')
                            @include('components.admin.buttons.cancel-button')
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- ── Mustache row template (matches existing project pattern) ──────── --}}
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
    </td>
    <td>
        <input type="text"
               class="form-control alloc-amount-input"
               name="allocation_lines[@{{rowId}}][amount]"
               id="alloc-amount-@{{rowId}}"
               placeholder="0.00"
               value="@{{amount}}">
        <div class="text-danger form-error" id="alloc-amount-@{{rowId}}_error"></div>
    </td>
    <td>
        <div class="d-inline-block">
        <a class="alloc-add-row-trigger" href="javascript:" id="alloc-add-row">
            <img src="{{ url('assets/img-new/add-btn.svg') }}">
        </a>
</div>
        <div class="text-right d-inline-block" style="margin-top:6px;" id="alloc-remove-wrap-@{{rowId}}">
            <a class="alloc-remove-row-trigger" href="javascript:" data-row-id="@{{rowId}}">
                <img src="{{ url('assets/img-new/dlt-btn.svg') }}">
            </a>
        </div>
    </td>
</tr>
</script>

@section('js')
    <script src="{{ asset('assets/js/allocation/api.js') }}"></script>
    <script src="{{ asset('assets/js/allocation/allocation.js') }}"></script>
    <script>
        window.allocBaseUrl = "{{ url('/') }}";
        window.csrfToken    = "{{ csrf_token() }}";

        // Attribute codes — backend reads these from config/allocation.php
        // Replace PLACEHOLDER_* values in config/allocation.php with real codes
        window.allocAttrCodes = {
            majorComponent : "{{ config('allocation.major_component_code', 'PLACEHOLDER_MAJOR_COMPONENT') }}",
            component      : "{{ config('allocation.component_code',       'PLACEHOLDER_COMPONENT') }}",
            subComponent   : "{{ config('allocation.sub_component_code',   'PLACEHOLDER_SUB_COMPONENT') }}",
            duration       : "{{ config('allocation.duration_code',        'duration') }}",
        };

        $(document).ready(function () {
            $("#alloc-sanction-order-date").datepicker({
                dateFormat: "dd-mm-yy",
                changeYear: true,
                changeMonth: true,
                maxDate: 0
            });

            allocInitDurationDropdown();
            allocInitRowsEvents(false);
            allocAddRow(false);    // first blank row

            allocInitFileUpload(
                'alloc-doc-trigger',
                "{{ url('web/allocation/upload-document') }}",
                'alloc-doc-hidden',
                'alloc-doc-name',
                'alloc-doc-progress'
            );

            $('#alloc-form').on('submit', function(e) {
                e.preventDefault();
                allocSubmitForm('alloc-form', "{{ url('web/allocation') }}", "{{ url('web/allocation') }}");
            });
        });
    </script>
@endsection
@endsection
