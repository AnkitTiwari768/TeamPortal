{{--
Reusable form component — used by create.blade.php and edit.blade.php
Variables: $row (array), $editMode (bool), $details (object), $financialYears, $durations
--}}
@php
    $editMode = $editMode ?? false;
    $row = $row ?? [];
    $subDurations = $subDurations ?? [];

    // Build a clean document URL for existing files
    $docUrl = null;
    if (!empty($row['document_path'])) {
        $path = $row['document_path'];
        // Use the controller route if we have an ID
        if (!empty($row['id'])) {
             $docUrl = route('allocation.document', $row['id']);
        } elseif (str_starts_with($path, 'http')) {
            $docUrl = $path;
        } else {
            $docUrl = asset($path);
        }
    }
@endphp

<div class="row">
    {{-- ── Financial Year ─────────────────────────────────────────── --}}
    <div class="col-md-4">
        <div class="mb-3">
            <label class="required form-label">{{ __('Financial Year') }}</label>
            {!! Form::select('financial_year', financial_year(), $row['financial_year'] ?? null, [
    'class' => 'form-select',
    'id' => 'alloc-financial-year',
]) !!}
            <span class="text-danger form-error" id="alloc-financial-year_error"></span>
        </div>
    </div>

    {{-- ── Duration ────────────────────────────────────────────────── --}}
    <div class="col-md-4">
        <div class="mb-3">
            <label class="required form-label">{{ __('Duration') }}</label>
            {!! Form::select('duration_id', $durations ?? [], $row['duration_id'] ?? null, [
    'class' => 'form-select',
    'id' => 'alloc-duration',
    'placeholder' => '-- Select --',
]) !!}
            <span class="text-danger form-error" id="alloc-duration_error"></span>
        </div>
    </div>

    {{-- ── Sub-Duration (conditional) ──────────────────────────────── --}}
    <div class="col-md-4" id="alloc-sub-duration-wrapper"
        style="{{ count($subDurations) > 0 ? '' : 'display:none;' }}">
        <div class="mb-3">
            <label class="form-label">{{ __('Sub Duration') }}</label>
            <select class="form-select" id="alloc-sub-duration" name="sub_duration_id">
                <option value="">-- Select --</option>
                @foreach($subDurations as $sd)
                    <option value="{{ $sd->id }}" {{ ($row['sub_duration_id'] ?? '') == $sd->id ? 'selected' : '' }}>
                        {{ $sd->name ?? $sd->attribute_value ?? '' }}
                    </option>
                @endforeach
            </select>
            <span class="text-danger form-error" id="alloc-sub-duration_error"></span>
        </div>
    </div>

    {{-- ── Allocation Table ─────────────────────────────────────────── --}}
    <div class="row" id="personality">
        <div class="col-lg-12">
            <table class="table table-themed table-striped table-bordered">
                <thead>
                    <tr>
                        <th>{{ __('Major Component') }}</th>
                        <th>{{ __('Sub Component') }}</th>
                        <th>{{ __('Amount Rs.') }}</th>
                        <th>{{ __('national_application.add-remove') }}</th>
                    </tr>
                </thead>
                <tbody id="alloc-rows-body">
                    {{-- Rows injected by allocation.js --}}
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Total Amount ────────────────────────────────────────────── --}}
    <div class="col-md-4">
        <div class="mb-3">
            <label class="required form-label">{{ __('Total Amount Allocated Rs.') }}</label>
            <input type="text" class="form-control numericOnly" name="total_amount" id="alloc-total-hidden"
                maxlength="15" readonly placeholder="0.00" value="{{ $row['total_amount'] ?? '' }}">
            <span class="text-danger form-error" id="alloc-total_error"></span>
        </div>
    </div>

    {{-- ── Sanction Order Number ────────────────────────────────────── --}}
    <div class="col-md-4">
        <div class="mb-3">
            <label class="required form-label">{{ __('Sanction Order Number') }}</label>
            <input type="text" class="form-control alphaNumeric" name="sanction_order_number"
                id="alloc-sanction-order-no" maxlength="100" placeholder=""
                value="{{ $row['sanction_order_number'] ?? '' }}">
            <span class="text-danger form-error" id="alloc-sanction-order-no_error"></span>
        </div>
    </div>

    {{-- ── Sanction Order Date ──────────────────────────────────────── --}}
    <div class="col-md-4">
        <div class="mb-3">
            <label class="required form-label">{{ __('Sanction Order Date') }}</label>
            <input type="text" class="form-control" name="sanction_order_date" id="alloc-sanction-order-date"
                placeholder="DD-MM-YYYY" autocomplete="off"
                value="{{ !empty($row['sanction_order_date']) ? date('d-m-Y', strtotime($row['sanction_order_date'])) : '' }}">
            <span class="text-danger form-error" id="alloc-sanction-order-date_error"></span>
        </div>
    </div>

    {{-- ── Upload Document ──────────────────────────────────────────── --}}
    <div class="col-md-4">
        <div class="input-box">
            <label class="form-label">
                {{ __('Upload Document') }}
                <a class="tooltip-ins" href="#" data-toggle="tooltip" title="File must be PDF/Image and less than 2MB">
                    <i class="fa fa-question-circle" aria-hidden="true"></i>
                </a>
            </label>

            <input type="file" class="form-control" id="alloc-doc-trigger" accept=".pdf,application/pdf" />
            <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Note: Accept only PDF file.</small>

            <div class="upload_file_link" id="alloc-doc-name">
                @if($docUrl)
                    <a href="{{ $docUrl }}" target="_blank">
                        View existing document
                    </a>
                @endif
            </div>
            <span class="text-danger form-error" id="alloc-doc-trigger_error"></span>
            <input type="hidden" name="document_path" id="alloc-doc-hidden" value="{{ $row['document_path'] ?? '' }}" />
            <div class="progress-upload progress-bg" id="alloc-doc-progress" style="display:none;">
                <div id="loader-upload" style=""></div>
                <div class="progress-bar" style="width:0%"></div>
            </div>
        </div>
    </div>

    {{-- ── Remarks ──────────────────────────────────────────────────── --}}
    <div class="col-md-4">
        <div class="mb-3">
            <label class="form-label">{{ __('Remarks') }}</label>
            <textarea class="form-control" name="remarks" id="alloc-remarks"
                rows="2">{{ $row['remarks'] ?? '' }}</textarea>
            <span class="text-danger form-error" id="alloc-remarks_error"></span>
        </div>
    </div>
</div>