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
                                    {{ __('workshop.edit_proposed_workshop') }}
                                @else
                                    {{ __('workshop.create_proposed_workshop') }}
                                @endif
                            </h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">
                                        @if (!empty($row['id']))
                                            {{ __('workshop.edit_proposed_workshop') }}
                                        @else
                                            {{ __('workshop.add_proposed_workshop') }}
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
                        <form id="formId">
                            <div class="row">

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="required form-label">{{ __('workshop.financial_year') }}</label>
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
                                        <label class="required form-label">{{ __('workshop.duration') }}</label>
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
                                        <label class="form-label required">{{ __('workshop.sub_duration') }}</label>
                                        <select name="sub_duration" id="sub_duration" class="form-select">
                                            <option value="">Select Sub Duration</option>
                                        </select>
                                        <span class="text-danger form-error" id="sub_duration_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="required form-label">{{ __('workshop.event_title') }}</label>
                                        <input type="text" class="form-control txtOnly" name="event_title"
                                            id="event_title" placeholder="{{ __('workshop.event_title') }}" value="{{ $row['event_title'] ?? '' }}">
                                        <span class="text-danger form-error" id="event_title_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="required form-label">{{ __('workshop.event_for') }}</label>
                                        {{ Form::select('event_for[]', event_for(), $row['event_for'] ?? null, ['class' => 'form-select', 'id' => 'event_for', 'multiple' => 'multiple']) }}
                                        <span class="text-danger form-error" id="event_for_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="required form-label">{{ __('workshop.workshop_category') }}</label>
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
                                            placeholder="{{ __('workshop.remark') }}" value="{{ $row['remark'] ?? '' }}">
                                        <span class="text-danger form-error" id="remark_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="required form-label">{{ __('workshop.organizer_name') }}</label>
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
                                        <label class="required form-label">{{ __('workshop.branch_office') }}</label>
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
                                    <button type="button" class="btn btn-sm btn-primary" id="addMoreBtn">
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
                                                    <label class="required form-label">{{ __('workshop.start_date') }}</label>
                                                    <input type="text" name="start_date[]" class="form-control from_date"
                                                        placeholder="DD-MM-YYYY" value="{{ isset($row['schedules'][0]['start_date']) ? date('d-m-Y', strtotime($row['schedules'][0]['start_date'])) : '' }}">
                                                    <span class="text-danger form-error" id="start_date_0_error"></span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="required form-label">{{ __('workshop.end_date') }}</label>
                                                    <input type="text" name="end_date[]" class="form-control to_date"
                                                        placeholder="DD-MM-YYYY" value="{{ isset($row['schedules'][0]['end_date']) ? date('d-m-Y', strtotime($row['schedules'][0]['end_date'])) : '' }}">
                                                    <span class="text-danger form-error" id="end_date_0_error"></span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="required form-label">{{ __('workshop.start_time') }}</label>
                                                    <input type="time" name="start_time[]" class="form-control"
                                                        value="{{ $row['schedules'][0]['start_time'] ?? '' }}">
                                                    <span class="text-danger form-error" id="start_time_0_error"></span>
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
                                                        <label class="required form-label">{{ __('workshop.start_date') }}</label>
                                                        <input type="text" name="start_date[]"
                                                            class="form-control from_date" placeholder="DD-MM-YYYY"
                                                            value="{{ isset($schedule['start_date']) ? date('d-m-Y', strtotime($schedule['start_date'])) : '' }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="required form-label">{{ __('workshop.end_date') }}</label>
                                                        <input type="text" name="end_date[]"
                                                            class="form-control to_date" placeholder="DD-MM-YYYY"
                                                            value="{{ isset($schedule['end_date']) ? date('d-m-Y', strtotime($schedule['end_date'])) : '' }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="required form-label">{{ __('workshop.start_time') }}</label>
                                                        <input type="time" name="start_time[]" class="form-control"
                                                            value="{{ $schedule['start_time'] ?? '' }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <button type="button"
                                                        class="btn btn-danger btn-sm removeRow"
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
                                        <label class="form-label required">{{ __('workshop.event_description') }}</label>
                                        <textarea name="event_description" id="event_description" rows="3" class="form-control" placeholder="{{ __('workshop.event_description') }}">{{ $row['event_description'] ?? '' }}</textarea>
                                        <span class="text-danger form-error" id="event_description_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="required form-label">{{ __('workshop.workshop_mode') }}</label>
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
                                        <label class="form-label required">{{ __('workshop.venue_address') }}</label>
                                        <input type="text" class="form-control" name="venue_address"
                                            id="venue_address" placeholder="{{ __('workshop.venue_address') }}" value="{{ $row['venue_address'] ?? '' }}">
                                        <span class="text-danger form-error" id="venue_address_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label required">{{ __('workshop.state') }}</label>
                                        {{ Form::select(
                                            'state_id',
                                            ['' => 'Select State'] + $stateList,
                                            $row['state_id'] ?? null,
                                            [
                                                'class' => 'form-select state_id',
                                                'id' => '',
                                                'state-id' => $row['state_id'] ?? ''
                                            ]
                                        ) }}
                                        <span class="text-danger form-error" id="state_id_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label required">{{ __('workshop.district') }}</label>
                                        {{ Form::select('district_id', district_list(), $row['district_id'] ?? null, ['class' => 'form-select district_id', 'id' => '','district-id' => $row['district_id'] ?? '']) }}
                                        <span class="text-danger form-error" id="district_id_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label required">{{ __('workshop.sub_district') }}</label>
                                        <select id="" name="sub_district_id" class="form-select sub_district_id" sub-district-id="{{ $row['sub_district_id'] ?? '' }}">
                                            <option value="">Select</option>
                                        </select>
                                        <span class="text-danger form-error" id="sub_district_id_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('workshop.pincode') }}</label>
                                        <input type="text" class="form-control" name="pincode" id="pincode"
                                            placeholder="{{ __('workshop.pincode') }}" value="{{ $row['pincode'] ?? '' }}" maxlength="6">
                                        <span class="text-danger form-error" id="pincode_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('workshop.latitude') }}</label>
                                        <input type="text" class="form-control numeric" name="latitude"
                                            id="latitude" placeholder="{{ __('workshop.latitude') }}" value="{{ $row['latitude'] ?? '' }}">
                                        <span class="text-danger form-error" id="latitude_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('workshop.longitude') }}</label>
                                        <input type="text" class="form-control numeric" name="longitude"
                                            id="longitude" placeholder="{{ __('workshop.longitude') }}" value="{{ $row['longitude'] ?? '' }}">
                                        <span class="text-danger form-error" id="longitude_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <input type="hidden" id="uploaded_ids" name="uploaded_ids"
                                            value="{{ $row['uploaded_ids'] ?? '' }}">
                                        <label class="form-label">{{ __('workshop.upload_poster') }}</label>
                                        <input type="file" name="files[]" id="image_upload" multiple accept="image/*"
                                            class="form-control">
                                        <span class="text-danger form-error" id="uploaded_ids_error"></span>
                                         <small class="form-text text-muted">Note: Accept only image files</small>
                                         <!-- ⬇️ LOADER -->
                                            <div id="poster_loader" class="d-none mt-1">
                                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                    <span class="visually-hidden">Uploading...</span>
                                                </div>
                                                <span class="ms-1">Uploading...</span>
                                            </div>
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

                            <div class="form-action mt-3 mb-3">
                                @include('components.admin.buttons.submit-button')
                                <button type="button" id="reset_button" class="btn btn-warning wave-effect">
                                    <span class="btn-label"> <i class="fa fa-undo" aria-hidden="true"></i> </span>  {{ __('message.reset') }}
                                </button>
                            </div>
                        </form>
                    </div>
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

            // Store original values for reset
            let originalState = $('.state_id').val();
            let originalDistrict = $('.district_id').attr('district-id');
            let originalSubDistrict = $('.sub_district_id').attr('sub-district-id');
            let originalEventFor = $('#event_for').val();
            let originalDuration = $('#duration').val();
            let originalSubDuration = $('#sub_duration').val();

            // ========== FIXED RESET BUTTON HANDLER ==========
            $('#reset_button').click(function () {
    // Reset all native form fields (inputs, selects, textareas) to original values
    $("#formId")[0].reset();

    // Restore Select2 for event_for
    $('#event_for').val(originalEventFor).trigger('change');
  
    // Restore Duration without triggering change event
    $('#duration').val(originalDuration);
    var durationVal = originalDuration;
    var durationText = $('#duration option:selected').text().trim();

    // Handle Sub‑Duration based on Duration value
    if (!durationVal || durationText === 'Yearly') {
        $("#sub_duration_wrapper").hide();
        $('#sub_duration').val('');
    } else {
        $("#sub_duration_wrapper").show();
        // Load options and set the saved originalSubDuration as selected
        //loadAttributeDropdownOptions(durationVal.toLowerCase(), "#sub_duration", true, originalSubDuration || '');
    }

    // Restore State, District, Sub‑District
    var stateSelect = $('.state_id');
    stateSelect.val(originalState);
    var stateVal = stateSelect.val();
    var selectedText = stateSelect.find('option:selected').text().trim().toLowerCase();

    $(".district_id").prop("disabled", false);
    $(".sub_district_id").prop("disabled", false);

    if (selectedText === "national") {
        $(".district_id").prop("disabled", true).html('<option value="">Select</option>');
        $(".sub_district_id").prop("disabled", true).html('<option value="">Select</option>');
    } else if (stateVal) {
        loadDistrict(stateVal, originalDistrict);
        setTimeout(function() {
            if (originalDistrict) {
                loadSubDistrict(originalDistrict, originalSubDistrict);
            } else {
                $('.sub_district_id').html('<option value="">Select</option>');
            }
            toggleSubDistrictValidation();
        }, 350);
    } else {
        $(".district_id").html('<option value="">Select</option>');
        $(".sub_district_id").html('<option value="">Select</option>');
    }

    // Reset file input (poster)
    $('#image_upload').val('');
    // Optionally clear uploaded_ids if you want to remove previously uploaded images
    // $('#uploaded_ids').val('');

    // Clear all validation error messages
    $('.form-error').text('');

    // Reset remark visibility based on event_for selection
    handleRemarkVisibility();

    // Re‑initialize datepickers (if they were destroyed)
    initDatePickers();
    updateDatepickerBounds();
});

            // ========== EXISTING FUNCTIONS (unchanged) ==========
            initDatePickers();
            updateDatepickerBounds();
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
                updateDatepickerBounds();
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

        function getSelectedBounds() {
            const fy = $('select[name="financial_year"]').val();
            const durationText = $("#duration option:selected").text().trim().toLowerCase();
            const subDurationText = $("#sub_duration option:selected").text().trim().toLowerCase();

            if (!fy || !durationText || (durationText !== 'yearly' && (!subDurationText || subDurationText === 'select sub duration' || subDurationText === 'select' || subDurationText === ''))) {
                return null;
            }

            const parts = fy.split('-');
            if (parts.length !== 2) return null;
            const startYear = parseInt(parts[0]);
            const endYear   = parseInt(parts[1]);

            let startMonth = 4; // April
            let endMonth = 3;   // March
            let sYear = startYear;
            let eYear = endYear;
            let endDay = 31;

            if (durationText === 'yearly') {
                startMonth = 4;
                endMonth = 3;
                sYear = startYear;
                eYear = endYear;
                endDay = 31;
            } else if (durationText === 'half yearly' || durationText === 'half-yearly' || durationText === 'half year' || durationText === 'half_yearly') {
                if (subDurationText.includes('first')) {
                    startMonth = 4;
                    endMonth = 9;
                    sYear = startYear;
                    eYear = startYear;
                    endDay = 30;
                } else if (subDurationText.includes('second')) {
                    startMonth = 10;
                    endMonth = 3;
                    sYear = startYear;
                    eYear = endYear;
                    endDay = 31;
                }
            } else if (durationText === 'quarterly') {
                if (subDurationText.includes('first')) {
                    startMonth = 4;
                    endMonth = 6;
                    sYear = startYear;
                    eYear = startYear;
                    endDay = 30;
                } else if (subDurationText.includes('second')) {
                    startMonth = 7;
                    endMonth = 9;
                    sYear = startYear;
                    eYear = startYear;
                    endDay = 30;
                } else if (subDurationText.includes('third')) {
                    startMonth = 10;
                    endMonth = 12;
                    sYear = startYear;
                    eYear = startYear;
                    endDay = 31;
                } else if (subDurationText.includes('fourth')) {
                    startMonth = 1;
                    endMonth = 3;
                    sYear = endYear;
                    eYear = endYear;
                    endDay = 31;
                }
            } else if (durationText === 'monthly') {
                const months = {
                    'january': 1, 'february': 2, 'march': 3, 'april': 4,
                    'may': 5, 'june': 6, 'july': 7, 'august': 8,
                    'september': 9, 'october': 10, 'november': 11, 'december': 12
                };
                for (let m in months) {
                    if (subDurationText.includes(m)) {
                        startMonth = months[m];
                        endMonth = months[m];
                        sYear = (startMonth >= 4) ? startYear : endYear;
                        eYear = sYear;
                        endDay = new Date(eYear, endMonth, 0).getDate();
                        break;
                    }
                }
            }

            return {
                minDate: new Date(sYear, startMonth - 1, 1),
                maxDate: new Date(eYear, endMonth - 1, endDay)
            };
        }

        function validateScheduleDates() {
            const bounds = getSelectedBounds();
            if (!bounds) return true;

            function parseDdMmYyyy(val) {
                const p = val.split('-');
                if (p.length !== 3) return null;
                const d = new Date(parseInt(p[2]), parseInt(p[1]) - 1, parseInt(p[0]));
                return isNaN(d.getTime()) ? null : d;
            }

            let valid = true;

            $('.schedule-row').each(function(index) {
                const startInput = $(this).find('input[name="start_date[]"]');
                const endInput   = $(this).find('input[name="end_date[]"]');
                const startVal   = startInput.val();
                const endVal     = endInput.val();

                $(`#start_date_${index}_error`).text('');
                $(`#end_date_${index}_error`).text('');

                if (startVal) {
                    const startDate = parseDdMmYyyy(startVal);
                    if (startDate && (startDate.setHours(0,0,0,0) < bounds.minDate.setHours(0,0,0,0) || startDate.setHours(0,0,0,0) > bounds.maxDate.setHours(0,0,0,0))) {
                        $(`#start_date_${index}_error`).text('Start date must fall within selected Financial Year, Duration, and Sub Duration.');
                        valid = false;
                    }
                }

                if (endVal) {
                    const endDate = parseDdMmYyyy(endVal);
                    if (endDate && (endDate.setHours(0,0,0,0) < bounds.minDate.setHours(0,0,0,0) || endDate.setHours(0,0,0,0) > bounds.maxDate.setHours(0,0,0,0))) {
                        $(`#end_date_${index}_error`).text('End date must fall within selected Financial Year, Duration, and Sub Duration.');
                        valid = false;
                    }
                }
            });

            return valid;
        }

        function updateDatepickerBounds() {
            const bounds = getSelectedBounds();
            const dateInputs = $(".from_date, .to_date");

            if (!bounds) {
                dateInputs.prop('disabled', true);
                if (dateInputs.hasClass('hasDatepicker')) {
                    dateInputs.datepicker('option', 'minDate', null);
                    dateInputs.datepicker('option', 'maxDate', null);
                }
            } else {
                dateInputs.prop('disabled', false);
                if (dateInputs.hasClass('hasDatepicker')) {
                    dateInputs.datepicker('option', 'minDate', bounds.minDate);
                    dateInputs.datepicker('option', 'maxDate', bounds.maxDate);
                } else {
                    dateInputs.datepicker({
                        dateFormat: "dd-mm-yy",
                        changeYear: true,
                        changeMonth: true,
                        minDate: bounds.minDate,
                        maxDate: bounds.maxDate
                    });
                }
            }
            validateScheduleDates();
        }

        $(document).on('change', '.from_date, .to_date', function() {
            validateScheduleDates();
        });

        $(document).on('change', 'select[name="financial_year"], #duration, #sub_duration', function() {
            updateDatepickerBounds();
        });

        $(document).ajaxComplete(function(event, xhr, settings) {
            if (settings.url && settings.url.includes("get-attribute-options")) {
                updateDatepickerBounds();
            }
        });

        function resetSubmitButton() {
            const btn = $("#submit_button");
            btn.prop("disabled", false);
            btn.html(btn.data("originalHtml"));
        }

        $("#formId").on("submit", function (e) {
            e.preventDefault();

            const btn = $("#submit_button");

            if (btn.prop("disabled")) {
                return;
            }

            if (!btn.data("originalHtml")) {
                btn.data("originalHtml", btn.html());
            }

            btn.prop("disabled", true);
            btn.html(`
                <span class="spinner-border spinner-border-sm me-1"></span>
                Please Wait...
            `);

            requestAnimationFrame(function () {

                if (!validateScheduleDates()) {
                    toastr.error('Please correct the schedule dates to match the financial year.');
                    resetSubmitButton();
                    return;
                }

                if (!$("#formId")[0].checkValidity()) {
                    $("#formId")[0].reportValidity();
                    resetSubmitButton();
                    return;
                }

                let csrfToken = "{{ csrf_token() }}";
                let formData = $("#formId").serializeArray();
                let formattedData = {};

                $.each(formData, function (i, field) {
                    if (field.name.includes("[]")) {
                        let key = field.name.replace("[]", "");
                        if (!formattedData[key]) formattedData[key] = [];
                        formattedData[key].push(field.value);
                    } else {
                        formattedData[field.name] = field.value;
                    }
                });

                @isset($row['id'])
                    let url = "{{ url('/update-proposed-workshop/' . $row['id']) }}";
                @else
                    let url = "{{ url('/store-proposed-workshop') }}";
                @endisset

                let requestData = {
                    url: url,
                    method: "POST",
                    body: {
                        ...formattedData,
                        _token: csrfToken
                    }
                };

                let districtId = formattedData.district_id;
                let eventId = "{{ $row['id'] ?? '' }}";
                let originalDistrictId = "{{ $row['district_id'] ?? '' }}";

                if (eventId && districtId === originalDistrictId) {
                    sendRequest(requestData, "{{ url('proposed-workshop') }}");
                    return;
                }

                $.ajax({
                    url: BASE_URL + "/exist-district-workshop",
                    type: "POST",
                    data: {
                        district_id: districtId,
                        event_id: eventId,
                        _token: csrfToken
                    },
                    success: function (res) {
                        if (res.exists) {
                            if (confirm("A workshop has already been conducted for this district. Do you still want to continue?")) {
                                sendRequest(requestData, "{{ url('proposed-workshop') }}");
                            } else {
                                resetSubmitButton();
                            }
                        } else {
                            sendRequest(requestData, "{{ url('proposed-workshop') }}");
                        }
                    },
                    error: function () {
                        resetSubmitButton();
                        toastr.error("Something went wrong.");
                    }
                });
            });
        });

        function initDatePickers() {
            const bounds = getSelectedBounds();
            const options = {
                dateFormat: "dd-mm-yy",
                changeYear: true,
                changeMonth: true,
            };
            if (bounds) {
                options.minDate = bounds.minDate;
                options.maxDate = bounds.maxDate;
            }
            $(".from_date,.to_date").datepicker(options);

            if (!bounds) {
                $(".from_date,.to_date").prop('disabled', true);
            }
        }

        $('#image_upload').on('change', function () {
            uploadFile({
                input: $('#image_upload'),
                url: "{{ url('upload-proposed-workshop-image') }}",
                fieldName: 'files',
                multiple: true,
                loader: $('#poster_loader'),
                success: function (response) {
                    $('#uploaded_ids').val(
                        response.data.uploaded_ids.join(',')
                    );
                },
                error: function () {
                    $('#uploaded_ids').val('');
                }
            });
        });

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
            loadAttributeDropdownOptions(duration.toLowerCase(), "#sub_duration", true);
        });

        $(document).on('change', '.state_id', function () {
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

        $(document).on('change', '.district_id', function () {
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

        function toggleSubDistrictValidation() {
            var $sub = $('.sub_district_id');
            if ($sub.prop('disabled') || $sub.closest('.col-md-4').is(':hidden')) {
                $sub.removeAttr('required');
                $sub.closest('.mb-3').find('label').removeClass('required');
                return;
            }
            var hasRealOptions = $sub.find('option').length > 1;
            if (hasRealOptions) {
                $sub.attr('required', 'required');
                $sub.closest('.mb-3').find('label').addClass('required');
            } else {
                $sub.removeAttr('required');
                $sub.closest('.mb-3').find('label').removeClass('required');
            }
        }

        $(document).on('change', '.district_id', function() {
            setTimeout(toggleSubDistrictValidation, 300);
        });

        $(document).ready(function() {
            setTimeout(toggleSubDistrictValidation, 500);
        });
    </script>
@endsection
@endsection