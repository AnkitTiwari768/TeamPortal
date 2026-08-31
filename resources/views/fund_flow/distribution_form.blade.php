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
                                    {{ __('Edit Distribution') }}
                                @else
                                    {{ __('Add Distribution') }}
                                @endif
                            </h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                                    <li class="breadcrumb-item"><a href="#">
                                            @if (!empty($row['id']))
                                                {{ __('Edit Distribution') }}
                                            @else
                                                {{ __('Add Distribution') }}
                                            @endif
                                        </a></li>
                                </ol>
                            </nav>
                        </div>
                        <div class="action-header ms-auto">
                            <!-- split button -->
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
                                        <label class="required form-label">{{ __('Financial Year') }}</label>
                                        {!! Form::select('financial_year', financial_year(), $row['financial_year'] ?? null, [
                                            'class' => 'form-select',
                                            'id' => 'financial_year',
                                        ]) !!}
                                        <span class="text-danger form-error" id="financial_year_error"></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="required form-label">{{ __('Duration') }}</label>
                                        {!! Form::select('duration', duration(), $row['duration'] ?? null, [
                                            'class' => 'form-select',
                                            'id' => 'duration',
                                        ]) !!}
                                        <span class="text-danger form-error" id="duration_error"></span>
                                    </div>
                                </div>
                                <div class="col-md-4" id="duration_limit_wrapper" style="display:none;">
                                    <div class="mb-3">
                                        <label class="required form-label">{{ __('Select') }}</label>
                                        {!! Form::select('duration_limit', [], null, ['class' => 'form-select', 'id' => 'duration_limit']) !!}
                                        <span class="text-danger form-error" id="duration_limit_error"></span>
                                    </div>
                                </div>


                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="required form-label">{{ __('Major Component') }}</label>
                                        {!! Form::select(
                                            'major_component_id',
                                            dynamic_common_list($details?->majorcomponents),
                                            $row['major_component_id'] ?? null,
                                            ['class' => 'form-select', 'id' => 'major_component_id'],
                                        ) !!}
                                        <span class="text-danger form-error" id="major_component_id_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('Component') }}</label>
                                        {!! Form::select('component_id', dynamic_common_list($details?->components), $row['component_id'] ?? null, [
                                            'class' => 'form-select',
                                            'id' => 'component_id',
                                        ]) !!}
                                        <span class="text-danger form-error" id="component_id_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('Sub Component') }}</label>
                                        {!! Form::select(
                                            'sub_component_id',
                                            dynamic_common_list($details?->subcomponents),
                                            $row['sub_component_id'] ?? null,
                                            ['class' => 'form-select', 'id' => 'sub_component_id'],
                                        ) !!}
                                        <span class="text-danger form-error" id="sub_component_id_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('Total Allocation Amount Rs.') }}</label>
                                        <input type="text" class="form-control numericOnly" name="total_amount"
                                            id="total_amount" maxlength="10" readonly placeholder=""
                                            @if (isset($row) && isset($row['total_amount'])) value="{{ $row['total_amount'] }}" @endif>
                                        <span class="text-danger form-error" id="total_amount_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('Total Distributed Amount Rs.') }}</label>
                                        <input type="text" class="form-control numericOnly" name="total_distributed_amt"
                                            id="total_distributed_amt"  readonly placeholder="" value="" >
                                        <span class="text-danger form-error" id="total_distributed_amt_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('Remaining Balance Rs.') }}</label>
                                        <input type="text" class="form-control numericOnly" name="remaining_balance"
                                            id="remaining_balance" maxlength="10" readonly placeholder=""
                                            @if (isset($row) && isset($row['remaining_balance'])) value="{{ $row['remaining_balance'] }}" @endif>
                                        <span class="text-danger form-error" id="remaining_balance_error"></span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="required form-label">{{ __('Sanction Order Number') }}</label>
                                        <input type="text" class="form-control alphaNumeric" name="sanction_order_no"
                                            id="sanction_order_no" maxlength="50" placeholder=""
                                            @if (isset($row) && isset($row['sanction_order_no'])) value="{{ $row['sanction_order_no'] }}" @endif>
                                        <span class="text-danger form-error" id="sanction_order_no_error"></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="required form-label">{{ __('Sanction Order Date') }}</label>
                                        <input type="text" class="form-control" name="sanction_order_date"
                                            id="sanction_order_date"
                                            @if (isset($row) && isset($row['sanction_order_date'])) value="{{ !empty($row['sanction_order_date']) ? date('d-m-Y', strtotime($row['sanction_order_date'])) : '' }}" @endif>
                                        <span class="text-danger form-error" id="sanction_order_date_error"></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="required form-label">{{ __('Distribution Amount Rs.') }}</label>
                                        <input type="text" class="form-control alphaNumeric" name="amount_allocated"
                                            id="amount_allocated" maxlength="50" placeholder=""
                                            @if (isset($row) && isset($row['amount_allocated'])) value="{{ $row['amount_allocated'] }}" @endif>
                                        <span class="text-danger form-error" id="amount_allocated_error"></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="required form-label">{{ __('TDS%') }}</label>
                                        <input type="text" class="form-control numeric" name="tds"
                                            id="tds" maxlength="2" placeholder=""
                                            @if (isset($row) && isset($row['tds'])) value="{{ $row['tds'] }}" @endif>
                                        <span class="text-danger form-error" id="tds_error"></span>
                                        
                                    </div>
                                    <span id="tds_info" class="mt-2" style="display:none; color: green;"></span>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-box">
                                        <label class="form-label">Upload Document</label><a class="tooltip-ins"
                                            href="#" data-toggle="tooltip"
                                            title="File must be of pdf and less than 200kb"><i
                                                class="fa fa-question-circle" aria-hidden="true"></i></a>

                                        <input type="file" class="form-control" id="upload" />


                                        <div class="upload_file_link">

                                        </div>
                                        <span class="text-danger form-error" id="upload_document_error"></span>
                                        <input type="hidden" name="upload_document" id="upload_document"
                                            @if (isset($row) && !empty($row['upload_document'])) value="{{ $row['upload_document'] }}" @endif />
                                        <div class="progress-upload progress-bg" style="display:none;">
                                            <div id="loader-upload" style=""></div>
                                            <div class="progress-bar"></div>
                                        </div>
                                        @if (isset($row) && !empty($row['upload_document']))
                                            <a href="{{ url($row['allocationDocument']) }}"
                                                download="{{ $row['upload_document_original_name'] }}">
                                                {{ $row['upload_document_original_name'] }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('Remarks') }}</label>
                                        <textarea class="form-control" name="remarks" id="remarks">
@if (isset($row) && isset($row['remarks']))
{{ $row['remarks'] }}
@endif
</textarea>
                                        <span class="text-danger form-error" id="remarks_error"></span>
                                    </div>
                                </div>
                            </div>
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

@section('js')



    <script>
        $(document).ready(function() {

            getTotalAndRemainingAmount();

            $('#major_component_id').on('change', function() {
                var majorComponentID = $(this).val(); // get selected value
                getComponent(majorComponentID);
            });

            $('#component_id').on('change', function() {
                var componentID = $(this).val(); // get selected value
                getSubComponent(componentID);
            });

            let halfYearly = @json(half_yearly());
            let quarterly = @json(quaterly());
            let monthly = @json(month_list());

            $('#duration').on('change', function() {
                var duration = $(this).val();
                var $limit = $('#duration_limit');
                $limit.empty().append('<option value="">Select</option>');

                if (duration === 'Half Yearly') {
                    $.each(halfYearly, function(k, v) {
                        $limit.append('<option value="' + k + '">' + v + '</option>');
                    });
                    $('#duration_limit_wrapper').show();
                } else if (duration === 'Quarterly') {
                    $.each(quarterly, function(k, v) {
                        $limit.append('<option value="' + k + '">' + v + '</option>');
                    });
                    $('#duration_limit_wrapper').show();
                } else if (duration === 'Monthly') {
                    $.each(monthly, function(k, v) {
                        $limit.append('<option value="' + k + '">' + v + '</option>');
                    });
                    $('#duration_limit_wrapper').show();
                } else {
                    $('#duration_limit_wrapper').hide();
                }
            });

            // ----------------
            // Edit case
            // ----------------
            @if (isset($row) && $row['duration_limit'] != null)
                // Trigger change so options load
                $('#duration').trigger('change');

                // Now set value after a short delay to ensure options are ready
                setTimeout(function() {
                    $('#duration_limit').val("{{ $row['duration_limit'] }}");
                }, 100);
            @endif
        });

        $("#upload").on("change", function() {
            fileUploadWithLoader({
                url: "{{ url('upload-allocation') }}",
                selector: "#upload",
                fileFieldName: 'file',
                hiddenInputSelector: '#upload_document',
                progressElementSelector: '.progress-upload',
                loaderElementSelector: '#loader-upload',
                loadingContent: 'uploading...',
                successMessage: 'Successfully uploaded.',
                errorMessage: 'Invalid file type',
                showFileName: '.upload_file_link'
            });
        });

        function deleteFile(documentId, type) {
            if (confirm('Do you really want to delete?')) {
                $.ajax({
                    url: "{{ url('/allocation-delete-documents') }}",
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        id: documentId,
                        document_type: type
                    },
                    success: function(response) {
                        if (response == true) {
                            toastr.success('Document has been deleted.');
                            $("." + type + "_file_link").html('');
                            $("#" + type + "_document").val('');
                            $("#" + type).val('');
                        } else {
                            toastr.error('Something went wrong.');
                        }
                    }
                });
            }
        }

        function getTotalAndRemainingAmount() {
            var financial_year = $('#financial_year').val();
            var major_component_id = $('#major_component_id').val();
            var component_id = $('#component_id').val();
            var sub_component_id = $('#sub_component_id').val();

            if (!financial_year || !major_component_id) {
                return; // skip if required fields missing
            }

            $.ajax({
                url: "{{ url('/get-total-allocation') }}",
                method: "POST",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    financial_year: financial_year,
                    major_component_id: major_component_id,
                    component_id: component_id,
                    sub_component_id: sub_component_id || null
                },
                success: function(res) {
                    if (res) {
                        $('#total_amount').val(res.total);
                        $('#remaining_balance').val(res.remaining);
                        $('#total_distributed_amt').val(res.total_distributed_amt);
                    }
                }
            });
        }

        // Bind change events
        $('#financial_year, #major_component_id, #component_id, #sub_component_id').on('change', function() {
            getTotalAndRemainingAmount();
        });



        $("#formId").on("submit", function(event) {
            event.preventDefault();
            var formData = $("#formId").serializeArray();
            @if (isset($row) && isset($row['id']))
                formData.push({
                    name: "id",
                    value: "{{ $row['id'] }}"
                });
                var method = 'POST';
                var url = "{{ url('/store-fund-distribution/' . $row['id']) }}";
            @else
                var method = 'POST';
                var url = "{{ url('/store-fund-distribution/') }}";
            @endif

            var requestData = {
                url: url,
                method: method,
                body: formData
            };

            sendRequest(requestData, "{{ url('fund-distributions') }}");
        });

        $("#sanction_order_date").datepicker({
            dateFormat: "dd-mm-yy",
            changeYear: true,
            changeMonth: true,
            maxDate: 0
        });

        $('#amount_allocated, #tds').on('input', function () {
            let amount = +$('#amount_allocated').val() || 0;
            let tds = +$('#tds').val() || 0;

            if (amount && tds) {
                let tdsAmt = (amount * tds) / 100;
                let payable = amount - tdsAmt;

                $('#tds_info').html(
                    `Payable: <b>${payable}</b> (TDS: ${tdsAmt}) <br>
                    <small>Note: Remaining balance is calculated on total distributed amount before TDS deduction.</small>`
                ).show();
            } 
            else {
                $('#tds_info').hide();
            }
        });
    </script>
@endsection
@endsection
