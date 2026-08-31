@extends('components.admin.layout')

@section('page-content')
<div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="card container-main-card">

                <div class="card-header d-flex">
                    <div class="heading">
                        <h1>{{ __('Edit Allocation') }}</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item"><a href="{{ url('web/allocation') }}">Allocation</a></li>
                                <li class="breadcrumb-item">Edit Allocation</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="action-header ms-auto">
                        <div class="btn-group drop-btn">
                            @include('components.admin.buttons.back-button')
                        </div>
                    </div>
                </div>

                <div class="px-3 pt-2">
                    <div class="alert alert-warning alert-sm mb-0" style="font-size:.87rem; padding:.5rem 1rem;">
                        <i class="fa fa-lock"></i>
                        <strong>Edit Mode:</strong> Amount fields are locked and cannot be changed.
                    </div>
                </div>

                <div class="card-body pt-2">
                    <form id="alloc-form">
                        @csrf
                        <input type="hidden" name="_method" value="PUT">
                        <input type="hidden" name="id" value="{{ $row['id'] ?? '' }}">

                        @include('web.allocation.components.form', [
                            'editMode'  => true,
                            'row'       => $row,
                            'details'   => $details,
                            'durations' => $durations ?? [],
                        ])

                        <div class="form-action mt-3 mb-3">
                            @php $id = $row['id'] ?? null; @endphp
                            @include('components.admin.buttons.submit-button')
                            <!-- @include('components.admin.buttons.cancel-button') -->
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
    </td>
    <td>
        <input type="text"
               class="form-control alloc-amount-input"
               name="allocation_lines[@{{rowId}}][amount]"
               id="alloc-amount-@{{rowId}}"
               placeholder="0.00"
               value="@{{amount}}"
               @{{#lineId}}disabled readonly@{{/lineId}}>
        <div class="text-danger form-error" id="alloc-amount-@{{rowId}}_error"></div>
    </td>
    <td>
        <a class="alloc-add-row-trigger" href="javascript:" id="alloc-add-row">
            <img src="{{ url('assets/img-new/add-btn.svg') }}">
        </a>
        @{{^lineId}}
        <div class="text-right" style="margin-top:6px;" id="alloc-remove-wrap-@{{rowId}}">
            <a class="alloc-remove-row-trigger" href="javascript:" data-row-id="@{{rowId}}">
                <img src="{{ url('assets/img-new/dlt-btn.svg') }}">
            </a>
        </div>
        @{{/lineId}}
    </td>
</tr>
</script>

@php
    $existingLines = collect($lines ?? [])->map(function ($l) {
        return [
            'id'                 => $l->id,
            'major_component_id' => $l->major_component_id,
            'sub_component_id'   => $l->sub_component_id,
            'amount'             => $l->amount,
        ];
    })->values()->toArray();
@endphp

@section('js')
    <script src="{{ asset('assets/js/allocation/api.js') }}"></script>
    <script src="{{ asset('assets/js/allocation/allocation.js') }}"></script>
    <script>
        window.allocBaseUrl = "{{ url('/') }}";
        window.csrfToken    = "{{ csrf_token() }}";

        window.allocAttrCodes = {
            majorComponent : "{{ config('allocation.major_component_code', 'PLACEHOLDER_MAJOR_COMPONENT') }}",
            component      : "{{ config('allocation.component_code',       'PLACEHOLDER_COMPONENT') }}",
            subComponent   : "{{ config('allocation.sub_component_code',   'PLACEHOLDER_SUB_COMPONENT') }}",
            duration       : "{{ config('allocation.duration_code',        'duration') }}",
        };

        window.allocExistingLines = @json($existingLines);

        $(document).ready(function () {
            $("#alloc-sanction-order-date").datepicker({
                dateFormat: "dd-mm-yy",
                changeYear: true,
                changeMonth: true,
                maxDate: 0
            });
// Pass existing sub_duration_id so JS can pre-select it on load
window.allocExistingSubDuration = "{{ $row['sub_duration_id'] ?? '' }}";
            allocInitDurationDropdown();
            allocInitRowsEvents(true);
            allocInitEdit(window.allocExistingLines, true);

            allocInitFileUpload(
                'alloc-doc-trigger',
                "{{ url('web/allocation/upload-document') }}",
                'alloc-doc-hidden',
                'alloc-doc-name',
                'alloc-doc-progress'
            );

            $('#alloc-form').on('submit', function(e) {
                e.preventDefault();
                allocSubmitForm(
                    'alloc-form',
                    "{{ url('web/allocation/' . ($row['id'] ?? '')) }}",
                    "{{ url('web/allocation') }}"
                );
            });
        });
    </script>
@endsection
@endsection