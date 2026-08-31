@extends('components.admin.layout')
@section('page-content')
    <div class="container-fluid px-4 py-4">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card container-main-card">
                    <div class="card-header d-flex">
                        <div class="heading">
                            <h1>
                                @if (!empty($row->id))
                                    {{ __('workshop.edit_admin_workshop') }}
                                @else
                                    {{ __('workshop.create_admin_workshop') }}
                                @endif
                            </h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                   	<li class="breadcrumb-item"><a href="{{url('dashboard')}}">{{ __('workshop.dashboard') }}</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">
                                        @if (!empty($row->id))
                                            {{ __('workshop.edit_admin_workshop') }}
                                        @else
                                            {{ __('workshop.create_admin_workshop') }}
                                        @endif
                                    </li>
                                </ol>
                            </nav>
                        </div>
                        <div class="action-header ms-auto">
                            <div class="btn-group drop-btn">
                              <button type="button" class="btn btn-danger" onclick="history.back();">
                                                <i class="fa fa-angle-double-left"></i> Back
                              </button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body pt-1">
                        <form id="formId">
                            @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="required form-label">Financial Year</label>
                                    <select name="financial_year"
                                            class="form-select"
                                            {{ isset($row) ? 'disabled' : '' }}>
                                            @foreach (financial_year() as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ old('financial_year', $row->financial_year ?? '') == $key ? 'selected' : '' }}>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if(isset($row))
                                            <input type="hidden" name="financial_year" value="{{ $row->financial_year }}">
                                        @endif
                                        <span class="text-danger form-error" id="financial_year_error"></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="required form-label">Duration</label>
                                        <select name="duration" id="duration"
                                                class="form-select"
                                                {{ isset($row) ? 'disabled' : '' }}>

                                                <option value="">Select</option>
                                                @foreach ($duration as $d)
                                                    <option value="{{ $d->id }}"
                                                        {{ old('duration', $row->duration ?? '') == $d->id ? 'selected' : '' }}>
                                                        {{ $d->attribute_value }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @if(isset($row->id))
                                                <input type="hidden" name="duration" value="{{ $row->duration }}">
                                            @endif
                                        <span class="text-danger form-error" id="duration_error"></span>
                                    </div>
                                </div>
                                <div class="col-md-4" id="sub_duration_wrapper" style="display:none;">
                                    <div class="mb-3">
                                        <label class="form-label required">Sub Duration</label>
                                        <select name="sub_duration" id="sub_duration" class="form-select">
                                            <option value="">Select Sub Duration</option>
                                        </select>
                                        <span class="text-danger form-error" id="sub_duration_error"></span>
                                        <input type="hidden" name="has_sub_duration" id="has_sub_duration" value="0">
                                        <!-- @if(isset($row->id))
                                         
                                            <input type="hidden" name="sub_duration" value="{{ $row->sub_duration }}">
                                        @endif -->
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="required form-label">Workshop Title</label>
                                        <input type="text" class="form-control" name="workshop_title" placeholder="Workshop Title"
                                            id="workshop_title" value="{{ old('title', $row->title ?? '') }}">
                                        <span class="text-danger form-error" id="workshop_title_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="required form-label">Date of Workshop</label>
                                        <input type="text" name="workshop_date" class="form-control" id="workshop_date" placeholder="Date of Workshop"  value="{{ old('workshop_date', isset($row->workshop_date) ? date('d-m-Y', strtotime($row->workshop_date)) : '') }}">
                                        <span class="text-danger form-error" id="workshop_date_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label required">{{ __('workshop.state') }}</label>
                                        {{ Form::select('state_id',state_list(),old('state_id', $row->state_id ?? null),['class' => 'form-select state_id','id' => '','state-id' => $row->state_id ?? '']) }}
                                        <span class="text-danger form-error" id="state_id_error"></span>
                                    </div>

                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label required">{{ __('workshop.district') }}</label>
                                           @php
                                                $districts = app(\App\Http\Api\V1\AdminWorkshop\AdminWorkshopService::class)->district_list($row->state_id ?? null);
                                             @endphp
                                                {{ Form::select('district_id',$districts,old('district_id', $row->district_id ?? null),['class' => 'form-select district_id', 'id' => '','district-id' => $row->district_id ?? '']) }}

                                        <span class="text-danger form-error" id="district_id_error"></span>
                                    </div>

                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label required">{{ __('workshop.sub_district') }}</label>
                                        <?php /*{{ Form::select('sub_district_id', sub_district_list(), $row['sub_district_id'] ?? null, ['class' => 'form-select sub_district_id', 'id' => 'sub_district_id']) }} */ ?>
                                        <select id="" name="sub_district_id" class="form-select sub_district_id"  sub-district-id="{{ $row->sub_district_id ?? '' }}">
                                            <option value="">Select</option>
                                        </select>
                                        <span class="text-danger form-error" id="sub_district_id_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label required">Workshop Mode</label>
                                        <select name="workshop_mode" id="workshop_mode" class="form-select">
                                            <option value="">Select</option>
                                            @foreach ($mode as $m)
                                                <option value="{{ $m->id }}"
                                                    {{ old('workshop_mode', $row->workshop_mode ?? '') == $m->id ? 'selected' : '' }}>
                                                    {{ $m->attribute_value }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <span class="text-danger form-error" id="workshop_mode_error"></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label required">Organizer Name</label>
                                        <select name="organizer_name" id="organizer_name" class="form-select">
                                            <option value="">Select</option>
                                            @foreach ($organizer as $o)
                                                <option value="{{ $o->id }}"
                                                    {{ old('organizer_name', $row->organizer_name ?? '') == $o->id ? 'selected' : '' }}>
                                                    {{ $o->attribute_value }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <span class="text-danger form-error" id="organizer_name_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="required form-label">{{ __('workshop.branch_office') }}</label>
                                       <?php /* <input type="text" class="form-control txtOnly" name="organiser_name"
                                            id="organiser_name" placeholder="Enter Organizer Name" value="{{ $row['organiser_name'] ?? '' }}"> */?>
                                            {{ Form::select('branch_office', ['' => 'Select'] + $branch_office, $row->branch_office_id ?? null, ['class' => 'form-select', 'id' => 'branch_office']) }}
                                        <span class="text-danger form-error" id="branch_office_error"></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Conducted By</label>
                                        <select name="conducted_by" id="conducted_by" class="form-select">
                                            <option value="">Select</option>
                                             @foreach ($conductedBy as $cb)
                                                <option value="{{ $cb->id }}"
                                                    {{ old('conducted_by', $row->conducted_by ?? '') == $cb->id ? 'selected' : '' }}>
                                                    {{ $cb->attribute_value }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <span class="text-danger form-error" id="conducted_by_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-4 other_box" style="display:none;">
                                    <div class="mb-3">
                                        <label class="form-label">Other</label>
                                        <input type="text" name="conducted_by_other" id="conducted_by_other" value="{{ $row->conduct_by_other ?? ''}}" class="form-control">
                                           
                                        <span class="text-danger form-error" id="conducted_by_other_error"></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Target Audience</label>
                                        <select name="target_audience" id="target_audience" class="form-select">
                                            <option value="">Select</option>
                                              @foreach ($target_audience as $ta)
                                                    <option value="{{ $ta->id }}"
                                                        {{ old('target_audience', $row->target_audience ?? '') == $ta->id ? 'selected' : '' }}>
                                                        {{ $ta->attribute_value }}
                                                    </option>
                                              @endforeach
                                        </select>
                                        <span class="text-danger form-error" id="target_audience_error"></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Number of Participants</label>
                                        <input type="number" name="number_of_participants" class="form-control numeric" placeholder="Number of Participants"
                                            id="number_of_participants" value="{{ old('number_of_participants', $row->number_of_participants ?? '') }}">
                                        <span class="text-danger form-error" id="number_of_participants_error"></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label required">Expense Amount</label>
                                        <input type="text" name="expense_amount" class="form-control numeric" placeholder="Expense Amount"
                                            id="expense_amount"   value="{{ old('expense_amount', $row->expense_amount ?? '') }}"
                                            {{ isset($row) ? 'disabled' : '' }}>

                                     @if(isset($row->id))
                                        <input type="hidden" name="expense_amount" value="{{ $row->expense_amount }}">
                                    @endif
                                        <span class="text-danger form-error" id="expense_amount_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">5% of NSIC Fee</label>
                                        <input type="text" name="nsic_fees" class="form-control numeric" placeholder="0" id="nsic_fees"   value="{{ $row->nsic_fees ?? ''}}" readonly style="cursor:not-allowed">
                                        <span class="text-danger form-error" id="nsic_fees_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">TDS Applicable</label>
                                        <select name="tds_applicable" id="tds_applicable" class="form-select">
                                            <option value="">Select</option>
                                            <option value="1" {{ old('tds_applicable', $row->tds_applicable ?? '') == '1' ? 'selected' : '' }}>
                                                Yes</option>
                                            <option value="0" {{ old('tds_applicable', $row->tds_applicable ?? '') == '0' ? 'selected' : '' }}>
                                                No</option>
                                        </select>
                                        <span class="text-danger form-error" id="tds_applicable_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-4" id="tds_percentage_wrapper" style="display:none;">
                                    <div class="mb-3">
                                        <label class="form-label required">TDS %</label>
                                        <input type="text" name="tds_percentage" class="form-control numeric" placeholder="TDS %"
                                            id="tds_percentage" step="0.01" min="0" max="100"
                                            placeholder="" value="{{ old('tds_percentage', $row->tds_percentage ?? '') }}">
                                        <span class="text-danger form-error" id="tds_percentage_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-4" id="net_amount_wrapper" style="display:none;">
                                    <div class="mb-3">
                                        <label class="form-label">Net Amount (Auto Calculated)</label>
                                        <input type="text" name="net_amount" class="form-control bg-light" placeholder="Net Amount (Auto Calculated)"
                                            id="net_amount" readonly placeholder="" value="{{ old('net_amount', $row->net_amount ?? '') }}">
                                        <span class="text-danger form-error" id="net_amount_error"></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Sanction Order Number</label>
                                        <input type="text" name="sanction_order_number" class="form-control" placeholder="Sanction Order Number"
                                            id="sanction_order_number" value="{{ old('sanction_order_number', $row->sanction_order_no ?? '') }}">
                                        <span class="text-danger form-error" id="sanction_order_number_error"></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Sanction Order Date</label>
                                        <input type="text" name="sanction_order_date" class="form-control" placeholder="Sanction Order Date"
                                            id="sanction_order_date" value="{{ old('sanction_order_date', isset($row->sanction_order_date) ? date('d-m-Y', strtotime($row->sanction_order_date)) : '') }}">
                                        <span class="text-danger form-error" id="sanction_order_date_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <input type="hidden" id="uploaded_ids" name="uploaded_ids" value="{{ $row->uploaded_ids ?? '' }}">
                                        <label class="form-label">Upload Supporting Documents</label>
                                        <input type="file" name="files[]" id="image_upload" multiple  accept=".pdf,.xls,.xlsx,.csv"
                                            class="form-control">
                                        <span class="text-danger form-error" id="uploaded_ids_error"></span>
                                            <small class="form-text text-muted">Note: Accept only pdf or excel files</small>

                                    </div>
                                    @if (!empty($uploadedImage) && count($uploadedImage) > 0)
                                        <div class="mt-3">
                                            @foreach ($uploadedImage as $img)
                                                <div class="d-flex align-items-center justify-content-between mb-2 p-2 border rounded">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fa fa-file-pdf text-danger me-2"></i>
                                                        <span>{{ $img->file_name }}</span>
                                                    </div>
                                                   <a href="{{ asset('storage/app/' . $img->file_path . '/' . $img->file_system_name) }}"
                                                        download="{{ $img->file_name }}"
                                                        class="btn btn-sm btn-success">
                                                            <i class="fa fa-download"></i>
                                                        </a>

                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Remarks</label>
                                        <input type="text" name="remarks" class="form-control" id="remarks" placeholder="Remarks" value="{{ old('remarks', $row->remarks ?? '') }}">
                                        <span class="text-danger form-error" id="remarks_error"></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required">Venue</label>
                                            <textarea name="venue" id="venue" class="form-control" rows="3"  placeholder="Venue" >{{ old('venue', $row->venue ?? '') }}</textarea>
                                            <span class="text-danger form-error" id="venue_error"></span>
                                        </div>
                                </div>

                            </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">Workshop Description</label>
                        <textarea name="workshop_description" id="workshop_description" rows="3" class="form-control" placeholder="Workshop Description">{{ $row->workshop_description ?? '' }}</textarea>
                                        <span class="text-danger form-error" id="workshop_description_error"></span>
                                    </div>
                                </div>




                          	<div class="form-action mt-3 mb-3 d-flex justify-content-end gap-2">
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

    <script>
        $(document).ready(function() {

            let originalState = $('.state_id').val();
            let originalDistrict = $('.district_id').attr('district-id');
            let originalSubDistrict = $('.sub_district_id').attr('sub-district-id');

            $('#reset_button').click(function () {

                $("#formId")[0].reset();
                    setTimeout(function () {
                        $('.state_id').val(originalState);

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


            function calculateNetAmount() {
                // Get Expense Amount
                var expenseValue = $('#expense_amount').val();
                var expense = parseFloat(expenseValue);
                if (isNaN(expense)) {
                    expense = 0;
                }

                // Get TDS Applicable (Yes/No)
                var tdsYesNo = $('#tds_applicable').val();

                // Get TDS Percentage
                var tdsPercentValue = $('#tds_percentage').val();
                var tdsPercent = parseFloat(tdsPercentValue);
                if (isNaN(tdsPercent)) {
                    tdsPercent = 0;
                }

                var tdsAmount = 0;

                // Calculate only if TDS is Yes (1)
                if (tdsYesNo === '1' && tdsPercent > 0) {
                    tdsAmount = (expense * tdsPercent) / 100;
                }

                // Final Net Amount
                var netAmount = expense - tdsAmount;

                // Display result
                if (netAmount > 0) {
                    $('#net_amount').val(netAmount.toFixed(2));
                } else {
                    $('#net_amount').val('');
                }
            }

            $('#expense_amount').on('keyup input', function() {
                calculateNetAmount();
            });

            $('#tds_percentage').on('keyup input', function() {
                calculateNetAmount();
            });

            $('#tds_applicable').on('change', function() {
                var selectedValue = $(this).val();

                if (selectedValue === '1') {
                    // Show the fields
                    $('#tds_percentage_wrapper').show();
                    $('#net_amount_wrapper').show();
                } else {
                    // Hide the fields
                    $('#tds_percentage_wrapper').hide();
                    $('#net_amount_wrapper').hide();

                    // {{ __('workshop.remove') }} and clear the percentage field
                    $('#tds_percentage').val('');

                    calculateNetAmount();
                }
            });

            if ($('#tds_applicable').val() === '1') {
                $('#tds_percentage_wrapper').show();
                $('#net_amount_wrapper').show();
            }

            $(document).on('keypress', 'input', function(e) {
                if (e.which == 13) {
                    e.preventDefault();
                    return false;
                }
            });

            $("#submit").on("click", function(event) {
                event.preventDefault();
                if (!$("#formId")[0].checkValidity()) {
                    $("#formId")[0].reportValidity();
                    return;
                }

                $('.form-error').text('');

                var formData = new FormData($('#formId')[0]);
                formData.delete('net_amount');

                var csrfToken = "{{ csrf_token() }}";

                var districtId = $('.district_id').val();
                var eventId = "{{ $row->id ?? '' }}";
                var originalDistrictId = "{{ $row->district_id ?? '' }}";

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                @isset($row->id)
                    var url = "{{ url('update-admin-workshop/' . $row->id) }}";
                @else
                    var url = "{{ url('store-admin-workshop') }}";
                @endisset

                function saveWorkshop() {
                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success !== false) {
                                toastr.success(response.message || 'Workshop saved successfully!');
                                setTimeout(function() {
                                    window.location.href = "{{ url('admin-workshop') }}";
                                }, 1200);
                            } else {
                                toastr.error(response.message || 'Something went wrong.');
                            }
                        },
                        error: function(xhr) {
                            if (xhr.status === 422) {
                                var errors = xhr.responseJSON && xhr.responseJSON.errors
                                    ? xhr.responseJSON.errors
                                    : {};

                                $.each(errors, function(field, messages) {
                                    var el = $('#' + field + '_error');
                                    if (el.length) {
                                        el.text(messages[0]);
                                    } else {
                                        toastr.error(messages[0]);
                                    }
                                });
                            } else {
                                toastr.error('Something went wrong. Please try again.');
                            }
                        }
                    });
                }

                if (eventId && districtId === originalDistrictId) {
                    saveWorkshop();
                    return;
                }

                $.post(BASE_URL + "/exist-district-workshop-admin", {
                    district_id: districtId,
                    event_id: eventId,
                    _token: csrfToken
                }, function(res) {

                        if (res.exists) {
                            if (confirm("A workshop has already been conducted for this district. Do you still want to continue?")) {
                                saveWorkshop();
                            } else {
                                window.history.back();
                            }
                        } else {
                            saveWorkshop();
                        }
                    });
            });

            $("#sanction_order_date").datepicker({
                dateFormat: "dd-mm-yy",
                changeYear: true,
                changeMonth: true,
                maxDate: 0
            });

            $("#workshop_date").datepicker({
                dateFormat: "dd-mm-yy",
                changeYear: true,
                changeMonth: true
            });

            $('#image_upload').on('change', function() {
                const files = this.files;
                if (!files.length) return;

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                for (let i = 0; i < files.length; i++) {
                    // Allow only PDF files
                    // if (files[i].type !== 'application/pdf') {
                    //     toastr.error(files[i].name + ' is not a PDF file.');
                    //     $('#image_upload').val(''); // reset input
                    //     return;
                    // }
                    formData.append('files[]', files[i]);
                }

                $.ajax({
                    url: '{{ url('upload-supporting-document') }}',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.data && response.data.uploaded_ids) {
                             toastr.success(response.message || 'Image uploaded successfully!');
                            let ids = response.data.uploaded_ids.join(',');
                            $('#uploaded_ids').val(ids);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors || {};
                            $.each(errors, function(key, messages) {
                                messages.forEach(function(msg) {
                                    toastr.error(msg);
                                });
                            });
                        } else {
                            toastr.error('File upload failed. Please try again.');
                        }
                    }
                });
            });

            
            const stateId = $(".state_id").attr("state-id");
            const districtId = $(".district_id").attr("district-id");
            const subDistrictId = $(".sub_district_id").attr("sub-district-id");

            function loadDistrict(state_id,selectedDistrict = null) {
                $.get(BASE_URL + '/getDistrict/' + state_id, function(res) {
                    let html = "<option value=''>Select</option>";

                    $.each(res.data, function(i, row) {
                        html += `<option value="${row.id}">${row.name}</option>`;
                    });

                    $(".district_id").html(html);

                    /*if (districtId) {
                        $(".district_id").val(districtId);
                        loadSubDistrict(districtId);
                    }*/
                if (selectedDistrict) {
                        $(".district_id").val(selectedDistrict);
                        loadSubDistrict(selectedDistrict, subDistrictId);
                    }
                });
            }

            function loadSubDistrict(district_id, selectedSubDistrict = null) {
                $.get(BASE_URL + '/get-sub-district/' + district_id, function(res) {
                    let html = "<option value=''>Select</option>";

                    if (!res.data || res.data.length === 0) {
                        $(".sub_district_id").html(html);
                        $(".sub_district_id").prop("disabled", true);
                        return;
                    }

                    $.each(res.data, function(i, row) {
                        html += `<option value="${row.id}">${row.name}</option>`;
                    });

                    $(".sub_district_id").html(html);
                    $(".sub_district_id").prop("disabled", false);

                    if (selectedSubDistrict) {
                        $(".sub_district_id").val(selectedSubDistrict);
                    }
                });
            }

            $(".state_id").change(function () {
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

            $(".district_id").change(function () {
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

        });

        $(document).ready(function () {

            function loadSubDuration(durationId, selected = '') {

                if (!durationId) {
                    $('#sub_duration_wrapper').hide();
                    $('#sub_duration').html('<option value="">Select Sub Duration</option>');
                    return;
                }

                $.ajax({
                    url: "{{ url('get-sub-duration') }}/" + durationId,
                    type: "GET",
                    success: function (res) {

                        let options = '<option value="">Select Sub Duration</option>';

                        $('#sub_duration').prop('disabled', false); // reset first

                        if (res && res.length > 0) {

                            $.each(res, function (i, item) {
                                let isSelected = (selected == item.id) ? 'selected' : '';
                                options += `<option value="${item.id}" ${isSelected}>${item.attribute_value}</option>`;
                            });

                            $('#sub_duration').html(options);
                            $('#sub_duration_wrapper').show();

                        } else {

                            $('#sub_duration').html(options).prop('disabled', true);
                            $('#sub_duration_wrapper').hide();
                        }
                    }
                });
            }

            // ON CHANGE
            $('#duration').on('change', function () {
                let durationId = $(this).val();

                if (!durationId) {
                    $('#sub_duration_wrapper').hide();
                    $('#sub_duration').val('');
                    return;
                }

                loadSubDuration(durationId);
            });

            // EDIT MODE
            @if(isset($row->duration))
                loadSubDuration(
                    "{{ $row->duration }}",
                    "{{ old('sub_duration', $row->sub_duration ?? '') }}"
                );
            @endif

        });

        $('#conducted_by').change(function () {
            var selectedText = $("#conducted_by option:selected").text().trim().toLowerCase();

            if (selectedText.toLowerCase() == 'others') {
                $('.other_box').show();
            } else {
                $('.other_box').hide();
                $('.other_box').find('input, textarea, select').val('');
            }
        }).trigger('change');

        var percentage = 5;

        $('#expense_amount').on('input', function () {
            let amount = $(this).val() || 0;
            let fee = (amount * percentage) / 100;
            $('#nsic_fees').val(fee);
        });

    </script>


@endsection
@endsection
