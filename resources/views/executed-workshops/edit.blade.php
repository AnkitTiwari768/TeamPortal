@extends('components.admin.layout')
@section('page-content')
    <div class="container-fluid px-4 py-4">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card container-main-card">
                    <div class="card-header d-flex">
                        <div class="heading">
                            <h1>
                                @if (!empty($row['id']))
                                    {{ __('workshop.edit_executed_workshop') }}
                                @else
                                    {{ __('workshop.create_executed_workshop') }}
                                @endif
                            </h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">
                                        @if (!empty($row['id']))
                                            {{ __('workshop.edit_executed_workshop') }}
                                        @else
                                            {{ __('workshop.add_executed_workshop') }}
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

                    <div class="card-body pt-1">
                        <form id="formId" enctype="multipart/form-data" onsubmit="return false;">
                            @csrf
                            <fieldset disabled>
                                <div class="row">

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('workshop.financial_year') }}</label>
                                            <select name="financial_year" class="form-select">
                                                @foreach (financial_year() as $key => $value)
                                                    <option value="{{ $key }}"
                                                        {{ old('financial_year', $row['financial_year'] ?? '') == $key ? 'selected' : '' }}>
                                                        {{ $value }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger form-error" id="financial_year_error"></span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('workshop.duration') }}</label>
                                            <select name="duration" id="duration" class="form-select">
                                                <option value="">Select</option>
                                                @foreach ($duration as $value => $name)
                                                    <option value="{{ $value }}"
                                                        {{ old('duration', $row['duration'] ?? '') == $value ? 'selected' : '' }}>
                                                        {{ $name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger form-error" id="duration_error"></span>
                                        </div>
                                    </div>

                                    <div class="col-md-4" id="sub_duration_wrapper" style="display:none;">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('workshop.sub_duration') }}</label>
                                            <select name="sub_duration" id="sub_duration" class="form-select">
                                                <option value="">Select Sub Duration</option>
                                            </select>
                                            <span class="text-danger form-error" id="sub_duration_error"></span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('workshop.event_title') }}</label>
                                            <input type="text" class="form-control txtOnly" name="event_title"
                                                id="event_title" placeholder="{{ __('workshop.event_title') }}"
                                                value="{{ $row['event_title'] ?? '' }}">
                                            <span class="text-danger form-error" id="event_title_error"></span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('workshop.event_for') }}</label>
                                            {{ Form::select('event_for[]', event_for(), $row['event_for'] ?? null, ['class' => 'form-select', 'id' => 'event_for', 'multiple' => 'multiple']) }}
                                            <span class="text-danger form-error" id="event_for_error"></span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('workshop.workshop_category') }}</label>
                                            <select name="workshop_category" class="form-select">
                                                <option value="">Select Workshop Category</option>
                                                @foreach ($workshopCategories as $key => $value)
                                                    <option value="{{ $key }}"
                                                        {{ old('workshop_category', $row['workshop_category'] ?? '') == $key ? 'selected' : '' }}>
                                                        {{ $value }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger form-error" id="workshop_category_error"></span>
                                        </div>
                                    </div>

                                    <div class="col-md-4 remark" style="display:none">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('workshop.remark') }}</label>
                                            <input type="text" class="form-control txtOnly" name="remark" id="remark"
                                                placeholder="{{ __('workshop.remark') }}"
                                                value="{{ $row['remark'] ?? '' }}">
                                            <span class="text-danger form-error" id="remark_error"></span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('workshop.organizer_name') }}</label>
                                            <select name="organiser_name" class="form-select">
                                                <option value="">Select Organizer Name</option>
                                                @foreach ($organizerNames as $key => $value)
                                                    <option value="{{ $key }}"
                                                        {{ old('organiser_name', $row['organiser_name'] ?? '') == $key ? 'selected' : '' }}>
                                                        {{ $value }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger form-error" id="organiser_name_error"></span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('workshop.branch_office') }}</label>
                                            <select name="branch_office" class="form-select">
                                                <option value="">Select Branch Office</option>
                                                @foreach ($branchOffices as $key => $value)
                                                    <option value="{{ $key }}"
                                                        {{ old('branch_office', $row['branch_office'] ?? '') == $key ? 'selected' : '' }}>
                                                        {{ $value }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger form-error" id="branch_office_error"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-md-12 d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">{{ __('workshop.event_schedule') }}</h5>
                                        <button type="button" class="btn btn-sm btn-primary" id="addMoreBtn"
                                            style="display: none;">
                                            <i class="bi bi-plus"></i> {{ __('workshop.add_more') }}
                                        </button>
                                    </div>
                                </div>

                                <div id="scheduleContainer" class="mt-3 mb-3">
                                    <div class="card schedule-row border rounded bg-light" id="schedule_0">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ __('workshop.start_date') }}</label>
                                                        <input type="text" name="start_date[]"
                                                            class="form-control from_date" placeholder="DD-MM-YYYY"
                                                            value="{{ isset($row['schedules'][0]['start_date']) ? date('d-m-Y', strtotime($row['schedules'][0]['start_date'])) : '' }}">
                                                        <span class="text-danger form-error"
                                                            id="start_date_0_error"></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ __('workshop.end_date') }}</label>
                                                        <input type="text" name="end_date[]"
                                                            class="form-control to_date" placeholder="DD-MM-YYYY"
                                                            value="{{ isset($row['schedules'][0]['end_date']) ? date('d-m-Y', strtotime($row['schedules'][0]['end_date'])) : '' }}">
                                                        <span class="text-danger form-error" id="end_date_0_error"></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ __('workshop.start_time') }}</label>
                                                        <input type="time" name="start_time[]" class="form-control"
                                                            value="{{ $row['schedules'][0]['start_time'] ?? '' }}">
                                                        <span class="text-danger form-error"
                                                            id="start_time_0_error"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @if (!empty($row['schedules']))
                                        @foreach ($row['schedules'] as $index => $schedule)
                                            @if ($index > 0)
                                                <div class="row schedule-row border p-3 mb-2 rounded bg-light"
                                                    id="schedule_{{ $index }}">
                                                    <div class="col-md-4">
                                                        <div class="mb-3">
                                                            <label
                                                                class="form-label">{{ __('workshop.start_date') }}</label>
                                                            <input type="text" name="start_date[]"
                                                                class="form-control from_date" placeholder="DD-MM-YYYY"
                                                                value="{{ isset($schedule['start_date']) ? date('d-m-Y', strtotime($schedule['start_date'])) : '' }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="mb-3">
                                                            <label
                                                                class="form-label">{{ __('workshop.end_date') }}</label>
                                                            <input type="text" name="end_date[]"
                                                                class="form-control to_date" placeholder="DD-MM-YYYY"
                                                                value="{{ isset($schedule['end_date']) ? date('d-m-Y', strtotime($schedule['end_date'])) : '' }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="mb-3">
                                                            <label
                                                                class="form-label">{{ __('workshop.start_time') }}</label>
                                                            <input type="time" name="start_time[]"
                                                                class="form-control"
                                                                value="{{ $schedule['start_time'] ?? '' }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3" style="display: none;">
                                                        <button type="button" class="btn btn-danger btn-sm removeRow"
                                                            data-id="{{ $index }}">
                                                            {{ __('workshop.remove') }}
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    @endif
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('workshop.event_description') }}</label>
                                            <textarea name="event_description" id="event_description" rows="3" class="form-control"
                                                placeholder="{{ __('workshop.event_description') }}">{{ $row['event_description'] ?? '' }}</textarea>
                                            <span class="text-danger form-error" id="event_description_error"></span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('workshop.workshop_mode') }}</label>
                                            <select name="workshop_mode" class="form-select">
                                                <option value="">Select Workshop Mode</option>
                                                @foreach ($workshopModes as $key => $value)
                                                    <option value="{{ $key }}"
                                                        {{ old('workshop_mode', $row['workshop_mode'] ?? '') == $key ? 'selected' : '' }}>
                                                        {{ $value }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger form-error" id="workshop_mode_error"></span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('workshop.conducted_by') }}</label>
                                            <select name="conducted_by" class="form-select">
                                                <option value="">Select Conducted By</option>
                                                @foreach ($workshopConductBy as $key => $value)
                                                    <option value="{{ $key }}"
                                                        {{ old('conducted_by', $row['conducted_by'] ?? '') == $key ? 'selected' : '' }}>
                                                        {{ $value }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('workshop.target_audience') }}</label>
                                            <select name="target_audience" class="form-select">
                                                <option value="">Select Target Audience</option>
                                                @foreach ($workshopTargetAudience as $key => $value)
                                                    <option value="{{ $key }}"
                                                        {{ old('target_audience', $row['target_audience'] ?? '') == $key ? 'selected' : '' }}>
                                                        {{ $value }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('workshop.venue_address') }}</label>
                                            <input type="text" class="form-control" name="venue_address"
                                                id="venue_address" placeholder="{{ __('workshop.venue_address') }}"
                                                value="{{ $row['venue_address'] ?? '' }}">
                                            <span class="text-danger form-error" id="venue_address_error"></span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('workshop.state') }}</label>
                                            {{ Form::select('state_id', ['' => 'Select State'] + $stateList, $row['state_id'] ?? null, [
                                                'class' => 'form-select state_id',
                                                'id' => '',
                                                'state-id' => $row['state_id'] ?? '',
                                            ]) }}
                                            <span class="text-danger form-error" id="state_id_error"></span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('workshop.district') }}</label>
                                            {{ Form::select('district_id', district_list(), $row['district_id'] ?? null, ['class' => 'form-select district_id', 'id' => '', 'district-id' => $row['district_id'] ?? '']) }}
                                            <span class="text-danger form-error" id="district_id_error"></span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('workshop.sub_district') }}</label>
                                            <select id="" name="sub_district_id"
                                                class="form-select sub_district_id"
                                                sub-district-id="{{ $row['sub_district_id'] ?? '' }}">
                                                <option value="">Select</option>
                                            </select>
                                            <span class="text-danger form-error" id="sub_district_id_error"></span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('workshop.pincode') }}</label>
                                            <input type="text" class="form-control" name="pincode" id="pincode"
                                                placeholder="{{ __('workshop.pincode') }}"
                                                value="{{ $row['pincode'] ?? '' }}" maxlength="6">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('workshop.latitude') }}</label>
                                            <input type="text" class="form-control numeric" name="latitude"
                                                id="latitude" placeholder="{{ __('workshop.latitude') }}"
                                                value="{{ $row['latitude'] ?? '' }}">
                                            <span class="text-danger form-error" id="latitude_error"></span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('workshop.longitude') }}</label>
                                            <input type="text" class="form-control numeric" name="longitude"
                                                id="longitude" placeholder="{{ __('workshop.longitude') }}"
                                                value="{{ $row['longitude'] ?? '' }}">
                                            <span class="text-danger form-error" id="longitude_error"></span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <input type="hidden" id="uploaded_ids" name="uploaded_ids"
                                                value="{{ $row['uploaded_ids'] ?? '' }}">
                                            <label class="form-label">{{ __('workshop.upload_poster') }}</label>
                                            <input type="file" name="files[]" id="image_upload" multiple
                                                accept="image/*" class="form-control">
                                            <span class="text-danger form-error" id="uploaded_ids_error"></span>
                                            <span><b>Note: Accept only image files</b></span>
                                        </div>

                                        @if (!empty($uploadedImage) && count($uploadedImage) > 0)
                                            <div id="uploaded_images_preview" class="mt-3">
                                                @foreach ($uploadedImage as $img)
                                                    <div class="uploaded-img-box mb-2">
                                                        <img src="{{ asset('storage/app/uploads/event_image/' . $img->file_system_name) }}"
                                                            class="img-thumbnail"
                                                            style="width:120px; height:120px; object-fit:cover;">
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>

                            </fieldset>

                            <!-- Non-disabled fields -->
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="required form-label">Number of Participants</label>
                                        <input type="number" class="form-control" name="no_of_participants"
                                            id="no_of_participants" placeholder="Number of Participants"
                                            value="{{ $row['no_of_participants'] ?? '' }}" required>
                                        <span class="text-danger form-error" id="no_of_participants_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="required form-label">Expense Amount</label>
                                        <input type="number" step="0.01" class="form-control" name="expense_amount"
                                            id="expense_amount" placeholder="Expense Amount"
                                            value="{{ $row['expense_amount'] ?? '' }}" required>
                                        <span class="text-danger form-error" id="expense_amount_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="required form-label">5% NSIC Fee</label>
                                        <input type="number" step="0.01" class="form-control" name="nsic_fee"
                                            id="nsic_fee" placeholder="5% NSIC Fee"
                                            value="{{ $row['nsic_fee'] ?? '' }}" readonly>
                                        <span class="text-danger form-error" id="nsic_fee_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="required form-label">TDS Applicable</label>
                                        <select name="tds_applicable" id="tds_applicable" class="form-select" required>
                                            <option value="">Select</option>
                                            <option value="Yes"
                                                {{ old('tds_applicable', $row['tds_applicable'] ?? '') == 'Yes' ? 'selected' : '' }}>
                                                Yes</option>
                                            <option value="No"
                                                {{ old('tds_applicable', $row['tds_applicable'] ?? '') == 'No' ? 'selected' : '' }}>
                                                No</option>
                                        </select>
                                        <span class="text-danger form-error" id="tds_applicable_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-4" id="tds_percentage_wrapper" style="display: none;">
                                    <div class="mb-3">
                                        <label class="required form-label">TDS %</label>
                                        <input type="number" step="0.01" min="0" max="100"
                                            class="form-control" name="tds_percentage" id="tds_percentage"
                                            placeholder="TDS %" value="{{ $row['tds_percentage'] ?? '' }}">
                                        <span class="text-danger form-error" id="tds_percentage_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-4" id="net_amount_wrapper" style="display: none;">
                                    <div class="mb-3">
                                        <label class="form-label">Net Amount (Auto Calculated)</label>
                                        <input type="number" step="0.01" class="form-control" name="net_amount"
                                            id="net_amount" placeholder="Net Amount"
                                            value="{{ $row['net_amount'] ?? '' }}" readonly>
                                        <span class="text-danger form-error" id="net_amount_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="required form-label">Sanction Order number</label>
                                        <input type="text" class="form-control" name="sanction_order_number"
                                            id="sanction_order_number" placeholder="Sanction Order number"
                                            value="{{ $row['sanction_order_number'] ?? '' }}" required>
                                        <span class="text-danger form-error" id="sanction_order_number_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="required form-label">Sanction Order date</label>
                                        <input type="text" class="form-control to_date" name="sanction_order_date"
                                            id="sanction_order_date" placeholder="DD-MM-YYYY"
                                            value="{{ isset($row['sanction_order_date']) ? date('d-m-Y', strtotime($row['sanction_order_date'])) : '' }}"
                                            required>
                                        <span class="text-danger form-error" id="sanction_order_date_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Supporting document</label>
                                        <input type="hidden" name="supporting_document" id="supporting_document_hidden"
                                            value="{{ is_array($row['supporting_document'] ?? null) ? json_encode($row['supporting_document']) : ($row['supporting_document'] ?? '') }}">
                                        <input type="file" class="form-control" id="supporting_document_file"
                                            accept=".pdf,.xls,.xlsx,.csv">
                                        <span class="text-danger form-error" id="supporting_document_error"></span>
                                           <small class="form-text text-muted">Note: Accept only pdf or excel files</small>
                                            <!-- ⬇️ LOADER -->
                                            <div id="supporting_document_loader" class="d-none mt-1">
                                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                    <span class="visually-hidden">Uploading...</span>
                                                </div>
                                                <span class="ms-1">Uploading...</span>
                                            </div>

                                        <div id="supporting_document_preview" class="mt-2"
                                            style="{{ empty($row['supporting_document']) ? 'display: none;' : '' }}">
                                            <a href="{{ !empty($row['supporting_document_url']) ? $row['supporting_document_url'] : '#' }}"
                                                id="supporting_document_link" target="_blank">View Current Document</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label">Remarks</label>
                                        <textarea class="form-control" name="remarks" id="remarks" rows="2" placeholder="Remarks">{{ $row['remarks'] ?? '' }}</textarea>
                                        <span class="text-danger form-error" id="remarks_error"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-action mt-3 mb-3">
                                @include('components.admin.buttons.submit-button', [
                                    'id' => $row['id'] ?? null,
                                ])
                                <button type="button" id="reset_button" class="btn btn-warning wave-effect">
                                    <span class="btn-label"> <i class="fa fa-undo" aria-hidden="true"></i> </span>
                                    {{ __('message.reset') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    @section('js')
        @include('scripts/attribute_dependency_dropdown')
        @include('scripts/state_district_dropdown')
        @include('scripts/district_to_subdistrict_dropdown')
        @include('scripts/upload_file')
        <script>
            $(document).ready(function() {

              // ---------- Auto-calculate NSIC Fee ----------
                function updateNsicFee() {
                    var expense = parseFloat($('#expense_amount').val()) || 0;
                    var nsicFee = expense * 0.05;
                    $('#nsic_fee').val(nsicFee.toFixed(2));
                    calculateNetAmount(); // Recalculate Net Amount
                }

                $('#expense_amount').on('input', updateNsicFee);
               $('#expense_amount').on('change', updateNsicFee);
                // Trigger on page load if there's a value
                if ($('#expense_amount').val()) {
                    updateNsicFee();
                }
                // Store original values for reset
                let originalState = $('.state_id').val();
                let originalDistrict = $('.district_id').attr('district-id');
                let originalSubDistrict = $('.sub_district_id').attr('sub-district-id');
                let originalEventFor = $('#event_for').val();
                let originalDuration = $('#duration').val();
                let originalSubDuration = $('#sub_duration').val();
                let originalSupportingDocument = $('#supporting_document_hidden').val();
                let originalSupportingDocumentUrl = $('#supporting_document_link').attr('href');

                // ========== FIXED RESET BUTTON HANDLER ==========
                $('#reset_button').click(function() {
                    // 1. Reset all native form fields (including disabled ones)
                    $("#formId")[0].reset();
                    

                    // 2. Restore Select2 (event_for)
                    $('#event_for').val(originalEventFor).trigger('change');

                    // 3. Restore Duration & Sub‑Duration
                    $('#duration').val(originalDuration).trigger('change');

                    // 4. Restore Supporting Document
                    $('#supporting_document_hidden').val(originalSupportingDocument);
                    $('#supporting_document_file').val('');
                    if (originalSupportingDocument) {
                        $('#supporting_document_link').attr('href', originalSupportingDocumentUrl);
                        $('#supporting_document_preview').show();
                    } else {
                        $('#supporting_document_preview').hide();
                    }

                    // 5. Restore State, District, Sub‑District (with proper enable/disable)
                    var stateSelect = $('.state_id');
                    stateSelect.val(originalState);
                    var stateVal = stateSelect.val();
                    var selectedText = stateSelect.find('option:selected').text().trim().toLowerCase();

                    // First enable both (they will be disabled only if state is 'national')
                    $(".district_id").prop("disabled", false);
                    $(".sub_district_id").prop("disabled", false);

                    if (selectedText === "national") {
                        // National → disable both and clear options
                        $(".district_id").prop("disabled", true).html('<option value="">Select</option>');
                        $(".sub_district_id").prop("disabled", true).html('<option value="">Select</option>');
                    } else if (stateVal) {
                        // Load districts with originalDistrict pre‑selected
                        loadDistrict(stateVal, originalDistrict);
                        // After districts load, load sub‑district if originalDistrict exists
                        setTimeout(function() {
                            if (originalDistrict) {
                                loadSubDistrict(originalDistrict, originalSubDistrict);
                            } else {
                                $('.sub_district_id').html('<option value="">Select</option>');
                            }
                        }, 350);
                    } else {
                        // No state selected → clear both and leave enabled
                        $(".district_id").html('<option value="">Select</option>');
                        $(".sub_district_id").html('<option value="">Select</option>');
                    }

                    // 6. Reset file inputs (poster and supporting doc)
                    $('#image_upload').val('');
                    // Do not clear uploaded_ids if existing images should remain (comment out if needed)
                    // $('#uploaded_ids').val('');

                    // 7. Clear validation error messages
                    $('.form-error').text('');

                    // 8. Reset remark visibility
                    handleRemarkVisibility();

                    // 9. Reset schedules: clear all inputs (reset() already cleared the first row)
                    // but we also clear any dynamically added rows' inputs
                    //$('.schedule-row input').val('');

                    // 10. Re‑calculate TDS and Net Amount based on restored values
                    toggleTdsFields();
                    calculateNetAmount();

                    // 11. Re‑initialize datepickers
                    initDatePickers();
                });

                // ========== EXISTING FUNCTIONS (mostly unchanged) ==========

                initDatePickers();
                handleRemarkVisibility();

                $('#event_for').select2({
                    placeholder: "Select Category",
                    allowClear: true
                });

                let row = $('.schedule-row').length;

                $('#addMoreBtn').click(function() {
                    let totalRows = $('.schedule-row').length;
                    if (totalRows >= 5) {
                        toastr.error('You can add a maximum of 5 schedules only');
                        return;
                    }

                    let newRow = `
                <div class="row schedule-row border p-3 mb-2 rounded bg-light position-relative" id="schedule_${row}">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="required form-label">{{ __('workshop.start_date') }}</label>
                            <input type="text" name="start_date[]" class="form-control from_date" placeholder="DD-MM-YYYY">
                            <span class="text-danger form-error" id="start_date_${row}_error"></span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="required form-label">{{ __('workshop.end_date') }}</label>
                            <input type="text" name="end_date[]" class="form-control to_date" placeholder="DD-MM-YYYY">
                            <span class="text-danger form-error" id="end_date_${row}_error"></span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="required form-label">{{ __('workshop.start_time') }}</label>
                            <input type="time" name="start_time[]" class="form-control">
                            <span class="text-danger form-error" id="start_time_${row}_error"></span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-danger btn-sm removeRow" data-id="${row}">{{ __('workshop.remove') }}</button>
                    </div>
                </div>`;

                    $('#scheduleContainer').append(newRow);
                    initDatePickers();
                    row++;
                });

                $(document).on('click', '.removeRow', function() {
                    let id = $(this).data('id');
                    $("#schedule_" + id).remove();
                });
            });

            // ========== GLOBAL FUNCTIONS ==========

            function handleRemarkVisibility() {
                let isOtherSelected = $('#event_for option:selected:contains("Other")').length > 0;
                if (isOtherSelected) {
                    $('.remark').show();
                } else {
                    $('.remark').hide();
                    $('#remark').val('');
                }
            }

            $('#event_for').on('change', handleRemarkVisibility);

            function initDatePickers() {
                $(".from_date,.to_date").datepicker({
                    dateFormat: "dd-mm-yy",
                    changeYear: true,
                    changeMonth: true,
                });
            }

            // TDS and Net Amount logic (moved to global scope)
            function toggleTdsFields() {
                const tdsVal = $('#tds_applicable').val();
                if (tdsVal === 'Yes') {
                    $('#tds_percentage_wrapper').show();
                    $('#net_amount_wrapper').show();
                    $('#tds_percentage').prop('required', true);
                } else {
                    $('#tds_percentage_wrapper').hide();
                    $('#net_amount_wrapper').hide();
                    $('#tds_percentage').prop('required', false).val('');
                    $('#net_amount').val('');
                }
                calculateNetAmount();
            }

            function calculateNetAmount() {
                const expense = parseFloat($('#expense_amount').val()) || 0;
                //const nsicFee = parseFloat($('#nsic_fee').val()) || 0;
                const tdsVal = $('#tds_applicable').val();

                let netAmount = expense;

                if (tdsVal === 'Yes') {
                    const tdsPercent = parseFloat($('#tds_percentage').val()) || 0;
                    const tdsDeduction = (expense * tdsPercent) / 100;
                    netAmount = netAmount - tdsDeduction;
                }

                if ($('#tds_applicable').val() !== '') {
                    $('#net_amount').val(netAmount.toFixed(2));
                } else {
                    $('#net_amount').val('');
                }
            }

            $('#tds_applicable').on('change', toggleTdsFields);
            $('#expense_amount, #nsic_fee, #tds_percentage').on('input', calculateNetAmount);

            // Run on initial load
            toggleTdsFields();

            // ========== AJAX FORM SUBMIT ==========

            $("#formId").on("submit", function(event) {
                event.preventDefault();
                if (!$("#formId")[0].checkValidity()) {
                    $("#formId")[0].reportValidity();
                    return;
                }

                var url = "{{ url('/update-executed-workshop/' . ($row['id'] ?? '')) }}";
                var formData = new FormData(this);

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.status) {
                            toastr.success(response.message || 'Updated successfully!');
                            setTimeout(function() {
                                window.location.href = "{{ url('executed-workshop') }}";
                            }, 1000);
                        } else {
                            toastr.error(response.message || 'Something went wrong.');
                        }
                    },
                    error: function(xhr) {
                        $(".form-error").text("");
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON ? xhr.responseJSON.errors : null;
                            if (errors) {
                                $.each(errors, function(key, messages) {
                                    var errorField = $('#' + key + '_error');
                                    if (errorField.length) {
                                        errorField.text(messages[0]);
                                    } else {
                                        toastr.error(messages[0]);
                                    }
                                });
                            } else {
                                toastr.error('Validation failed.');
                            }
                        } else {
                            var errorMsg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : ((xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : 'Something went wrong.');
                            toastr.error(errorMsg);
                        }
                    }
                });
            });

            // ========== FILE UPLOADS ==========

            $('#image_upload').on('change', function() {
                uploadFile({
                    input: $('#image_upload'),
                    url: "{{ url('upload-proposed-workshop-image') }}",
                    fieldName: 'files',
                    multiple: true,
                    success(response) {
                        $('#uploaded_ids').val(
                            response.data.uploaded_ids.join(',')
                        );
                    },
                    error() {
                        $('#uploaded_ids').val('');
                    }
                });
            });

            $('#supporting_document_file').on('change', function() {
                uploadFile({
                    input: $('#supporting_document_file'),
                    url: "{{ url('upload-executed-workshop-document') }}",
                    fieldName: 'file',
                    loader: $('#supporting_document_loader'),
                    success(response) {
                        $('#supporting_document_hidden').val(response.data.id);
                        $('#supporting_document_link')
                            .attr(
                                'href',
                                "{{ asset('storage') }}/" + response.data.file_path
                            );
                        $('#supporting_document_preview').show();
                    },
                    error() {
                        $('#supporting_document_hidden').val('');
                        $('#supporting_document_preview').hide();
                    }
                });
            });

            // ========== CASCADING DROPDOWNS ==========

            const stateId = $(".state_id").attr("state-id");
            const districtId = $(".district_id").attr("district-id");
            const subDistrictId = $(".sub_district_id").attr("sub-district-id");

            $("#duration").on("change", function() {
                const duration = $(this).val();
                const durationText = $(this).find('option:selected').text().trim();

                if (!duration || durationText === 'Yearly') {
                    $("#sub_duration_wrapper").hide();
                    return;
                }

                $("#sub_duration_wrapper").show();
                //loadAttributeDropdownOptions(duration.toLowerCase(), "#sub_duration", true);
            });

            $(document).on('change', '.state_id', function() {
                let state = $(this).val();
                let selectedText = $(this).find("option:selected").text().trim().toLowerCase();

                $(".district_id").html("<option value=''>Select</option>");
                $(".sub_district_id").html("<option value=''>Select</option>");

                if (selectedText === "national") {
                    $(".district_id").prop("disabled", true);
                    $(".sub_district_id").prop("disabled", true);
                    return;
                }

                $(".district_id").prop("disabled", false);
                $(".sub_district_id").prop("disabled", false);

                if (state) {
                    if (state == stateId) {
                        loadDistrict(state, districtId);
                    } else {
                        loadDistrict(state);
                    }
                }
            });

            $(document).on('change', '.district_id', function() {
                let district = $(this).val();
                $(".sub_district_id").html("<option value=''>Select</option>");
                if (district) {
                    loadSubDistrict(district);
                } else {
                    $(".sub_district_id").prop("disabled", true);
                }
            });

            let selectedText = $(".state_id option:selected").text().trim().toLowerCase();
            if (selectedText === "national") {
                $(".district_id").prop("disabled", true);
                $(".sub_district_id").prop("disabled", true);
            }

            if (stateId && districtId) {
                loadDistrict(stateId, districtId);
            }

            const durationId = $("#duration").val();
            const subDurationId = "{{ $row['sub_duration'] ?? '' }}";

            if (durationId) {
                const durationText = $("#duration option:selected").text().trim();
                if (durationText && durationText !== 'Yearly') {
                    $("#sub_duration_wrapper").show();
                    loadAttributeDropdownOptions(durationId.toLowerCase(), "#sub_duration", true, subDurationId);
                }
            }
        </script>
    @endsection
@endsection