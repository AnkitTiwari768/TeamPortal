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

       
            let originalState = $('.state_id').val();
            let originalDistrict = $('.district_id').attr('district-id');
            let originalSubDistrict = $('.sub_district_id').attr('sub-district-id');
            let originalEventFor = $('#event_for').val();

            $('#reset_button').click(function () {
                $("#formId")[0].reset();

                setTimeout(function () {
                    $('.state_id').val(originalState);

                    @isset($row['id'])
                        $('#event_for').val(originalEventFor).trigger('change');
                    @else
                        $('#event_for').val([]).trigger('change');
                    @endisset

                    loadDistrict(originalState);

                    setTimeout(function () {
                        $('.district_id').val(originalDistrict);

                        loadSubDistrict(originalDistrict);

                        setTimeout(function () {
                            $('.sub_district_id').val(originalSubDistrict);
                        }, 300);

                    }, 300);
                }, 100);
            });

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

       function validateScheduleDates() {
                const fy = $('select[name="financial_year"]').val();
                if (!fy) return true;

                const parts = fy.split('-');
                if (parts.length !== 2) return true;
                const startYear = parseInt(parts[0]);
                const endYear   = parseInt(parts[1]);

                let valid = true;

                $('.schedule-row').each(function(index) {
                    const startInput = $(this).find('input[name="start_date[]"]');
                    const endInput   = $(this).find('input[name="end_date[]"]');
                    const startVal   = startInput.val();
                    const endVal     = endInput.val();

                    // Clear previous errors
                    $(`#start_date_${index}_error`).text('');
                    $(`#end_date_${index}_error`).text('');

                    // Check start date year
                    if (startVal) {
                        const parts = startVal.split('-');
                        if (parts.length === 3) {
                            const year = parseInt(parts[2]);
                            if (year !== startYear && year !== endYear) {
                                $(`#start_date_${index}_error`).text('Start date year must be ' + fy);
                                valid = false;
                            }
                        }
                    }

                    // Check end date year
                    if (endVal) {
                        const parts = endVal.split('-');
                        if (parts.length === 3) {
                            const year = parseInt(parts[2]);
                            if (year !== startYear && year !== endYear) {
                                $(`#end_date_${index}_error`).text('End date year must be ' + fy);
                                valid = false;
                            }
                        }
                    }
                });

                return valid;
            }

         $(document).on('change', '.from_date, .to_date', function() {
            validateScheduleDates();
        });
        $("#formId").on("submit", function(event) {
            event.preventDefault();
            if (!validateScheduleDates()) {
                toastr.error('Please correct the schedule dates to match the financial year.');
                return;
            }
            if (!$("#formId")[0].checkValidity()) {
                $("#formId")[0].reportValidity();
                return;
            }

            var csrfToken = "{{ csrf_token() }}";

            var formData = $(this).serializeArray();
            var formattedData = {};
            $.each(formData, function(i, field) {
                if (field.name.includes("[]")) {
                    let key = field.name.replace("[]", "");
                    if (!formattedData[key]) {
                        formattedData[key] = [];
                    }
                    formattedData[key].push(field.value);
                } else {
                    formattedData[field.name] = field.value;
                }
            });

            @isset($row['id'])
                var method = 'POST';
                var url = "{{ url('/update-proposed-workshop/' . $row['id']) }}";
            @else
                var method = 'POST';
                var url = "{{ url('/store-proposed-workshop') }}";
            @endisset

            var requestData = {
                url: url,
                method: method,
                body: {
                    ...formattedData,
                    _token: csrfToken
                }
            };

            var districtId = formattedData.district_id;
            var eventId = "{{ $row['id'] ?? '' }}";
            var originalDistrictId = "{{ $row['district_id'] ?? '' }}";

            if (eventId && districtId === originalDistrictId) {
                sendRequest(requestData, "{{ url('proposed-workshop') }}");
                return;
            }

            $.post(BASE_URL + "/exist-district-workshop", {
                district_id: districtId,
                event_id: eventId,
                _token: csrfToken
            },

            function(res) {
                if (res.exists) {
                    if (confirm("A workshop has already been conducted for this district. Do you still want to continue?")) {
                        sendRequest(requestData, "{{ url('proposed-workshop') }}");
                    } else {
                        window.history.back();
                    }
                } else {
                    sendRequest(requestData, "{{ url('proposed-workshop') }}");
                }
            });
        });

        function initDatePickers() {
            $(".from_date,.to_date").datepicker({
                dateFormat: "dd-mm-yy",
                changeYear: true,
                changeMonth: true,
            });
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
            loadAttributeDropdownOptions(duration.toLowerCase(), "#sub_duration", dependency = true);
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
    </script>
@endsection
@endsection