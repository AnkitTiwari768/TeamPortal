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
                                    {{ __('Edit Allocation') }}
                                @else
                                    {{ __('Add Allocation') }}
                                @endif
                            </h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                                    <li class="breadcrumb-item"><a href="#">
                                            @if (!empty($row['id']))
                                                {{ __('Edit Allocation') }}
                                            @else
                                                {{ __('Add Allocation') }}
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
                                <?php /*<div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="required form-label">{{ __('Total Amount Allocated') }}</label>
                                        <input type="text" class="form-control numericOnly" name="total_amount_allocated"
                                            id="total_amount_allocated" maxlength="10" readonly placeholder=""
                                            @if (isset($row) && isset($row['total_amount_allocated'])) value="{{ $row['total_amount_allocated'] }}" @endif>
                                        <span class="text-danger form-error" id="total_amount_allocated_error"></span>
                                    </div>
                                </div> */
                                ?>

                                <div class="row" id="personality">
                                    <div class="col-lg-12">
                                        <table class="table table-themed table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Major Component</th>
                                                    <th>Component</th>
                                                    <th>Sub Component</th>
                                                    <th>Amount Rs.</th>
                                                    <th>{{ __('national_application.add-remove') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody id="container_personality">
                                                <!-- Add rows dynamically using the template -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="required form-label">{{ __('Total Amount Allocated Rs.') }}</label>
                                        <input type="text" class="form-control numericOnly" name="total_amount_allocated"
                                            id="total_amount_allocated" maxlength="10" readonly placeholder=""
                                            @if (isset($row) && isset($row['total_amount_allocated'])) value="{{ $row['total_amount_allocated'] }}" @endif>
                                        <span class="text-danger form-error" id="total_amount_allocated_error"></span>
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
                                    <div class="input-box">
                                        <label class="form-label">Upload Document</label><a class="tooltip-ins"
                                            href="#" data-toggle="tooltip"
                                            title="File must be of pdf and less than 200kb"><i class="fa fa-question-circle"
                                                aria-hidden="true"></i></a>

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

    <script id="add_more_personality_template" type="x-tmpl-mustache">
    <tr class="delete_personality_row" id="delete_personality_row@{{randomId}}">
        <td>
			<input type="hidden" name="personalities_details[@{{randomId}}][id]" value="@{{response.id}}">
            <select class="form-select major_component" data-randomid="@{{randomId}}" name="personalities_details[@{{randomId}}][major_component_id]" id="major_component_id@{{randomId}}">
                @foreach(dynamic_common_list($details?->majorcomponents) as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
            <div class="text-danger form-error" id="major_component_id_@{{randomId}}_error"></div>
        </td>
        <td>
            <select class="form-select component" data-randomid="@{{randomId}}" name="personalities_details[@{{randomId}}][component_id]" id="component_id@{{randomId}}">
                @foreach(dynamic_common_list($details?->components) as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
            <div class="text-danger form-error" id="component_id_@{{randomId}}_error"></div>
        </td>
		<td>
            <select class="form-select subcomponent" data-randomid="@{{randomId}}" name="personalities_details[@{{randomId}}][sub_component_id]" id="sub_component_id@{{randomId}}">
                @foreach(dynamic_common_list($details?->subcomponents) as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
            <div class="text-danger form-error" id="sub_component_id_@{{randomId}}_error"></div>
        </td>
		<td>
            <input type="text" class="form-control" maxlength="80" name="personalities_details[@{{randomId}}][amount]" id="amount@{{randomId}}" placeholder="" value="@{{response.amount}}">
            <div class="text-danger form-error" id="amount_@{{randomId}}_error"></div>
        </td>
        <td>
         <a class="delete_personality_row_addMore" id="add_more_personality" href="javascript:">  <img src="{{url('assets/img-new/add-btn.svg')}}"></a>
            <div class="delete_personality_button text-right" style="display:none; margin-top: 10px;" id="component_more_1_@{{randomId}}">
				<a class="remove_personality_component_more@{{randomId}}" id="remove_personality_component_more@{{randomId}}" href="javascript:">
                   <img src="{{url('assets/img-new/dlt-btn.svg')}}">
                </a>
            </div>
        </td>
    </tr>
</script>

    <script>
        $(document).ready(function() {
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

        $(document).on('change', '.major_component', function() {
            var majorComponentID = $(this).val();
            var randomId = $(this).data('randomid'); // get randomId from data attribute
            getComponent(majorComponentID, randomId);
        });

        $(document).on('change', '.component', function() {
            var componentID = $(this).val();
            var randomId = $(this).data('randomid'); // get randomId from data attribute
            getSubComponent(componentID, randomId);
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


        $("#formId").on("submit", function(event) {
            event.preventDefault();
            var formData = $("#formId").serializeArray();
            @if (isset($row) && isset($row['id']))
                formData.push({
                    name: "id",
                    value: "{{ $row['id'] }}"
                });
                var method = 'POST';
                var url = "{{ url('/store-fund-allocation/' . $row['id']) }}";
            @else
                var method = 'POST';
                var url = "{{ url('/store-fund-allocation/') }}";
            @endif

            var requestData = {
                url: url,
                method: method,
                body: formData
            };

            sendRequest(requestData, "{{ url('fund-allocations') }}");
        });

        //moustaque
        // var personalities_json = <?php echo $row['map_details'] ?? '{}'; ?>;
        var personalities_json = @json($row['map_details'] ?? []);
        var personalities_details = (personalities_json && personalities_json.length > 0) ? personalities_json : '';

        //console.log(personalities_details);
        var counter = 0;

        function addComponent(containerId, templateId, clickButtonId, deleteButtonClass, deleteRowPrefix, componentType,
            response) {
            var randomId = Date.now();

            function componentFunction(randomId, counter, response) {

                var source = $("#" + templateId).html();
                Mustache.parse(source);
                var rendered = Mustache.render(source, {
                    randomId: randomId,
                    counter: counter,
                    response: response,
                });

                $("#" + containerId).append(rendered);
                enableSubmit();

                $('#major_component_id' + randomId).val(response.major_component_id).trigger('change');
                /*setTimeout(function() {
                    $('#component_id' + randomId).val(response.component_id).trigger('change');
                }, 500);
                setTimeout(function() {
                    $('#sub_component_id' + randomId).val(response.sub_component_id).trigger('change');
                }, 1000);*/

                /*getComponent(response.major_component_id, randomId, response.component_id);
                getSubComponent(response.component_id, randomId, response.sub_component_id);*/
                
                getComponent(
                    response.major_component_id,
                    randomId,
                    response.component_id,
                    function () {

                        getSubComponent(
                            response.component_id,
                            randomId,
                            response.sub_component_id
                        );

                    }
                );

                enableDeleteButton(deleteButtonClass, deleteRowPrefix, componentType);
                deleteComponentMore(randomId, deleteRowPrefix, componentType, deleteButtonClass);
            }

            if (response != null && response.length) {
                $.each(response, function(key, responseData) {
                    //componentFunction(key, counter, responseData);
                    var randomId = Date.now() + key;
                    counter++;
                    componentFunction(randomId, counter, responseData);
                });
            } else {
                componentFunction(randomId, counter, response);
            }

            $("#" + clickButtonId).click(function() {
                randomId = Date.now();
                //console.log(".noc_document"+randomId+"_file_link");
                componentFunction(randomId, counter, response);
                $(".noc_document" + randomId + "_file_link").html(' ');
            });

            $(document).on('focus', '.form-control, .form-select', function() {
                // Find the closest parent 'td' and then find the '.form-error' element within it
                $(this).closest('td').find('.form-error').text('');
            });
        }


        // Usage for Personality
        addComponent("container_personality", "add_more_personality_template", "add_more_personality","delete_personality_row", "personality_component_more", "delete_personality", personalities_details);

        // Function to calculate total
        function calculateTotalAmount() {
            let total = 0;
            $("input[name^='personalities_details'][name$='[amount]']").each(function() {
                let val = parseFloat($(this).val());
                if (!isNaN(val)) {
                    total += val;
                }
            });
            $("#total_amount_allocated").val(total);
        }

        // Recalculate whenever an amount field changes
        $(document).on("input", "input[name^='personalities_details'][name$='[amount]']", function() {
            calculateTotalAmount();
        });

        // Also recalc after a new row is added (since new fields appear dynamically)
        $(document).on("click", "#add_more_personality", function() {
            setTimeout(calculateTotalAmount, 200);
        });

        // If rows can be removed
        $(document).on("click", "[id^=remove_personality_component_more]", function() {
            setTimeout(calculateTotalAmount, 200);
        });


        $("#sanction_order_date").datepicker({
            dateFormat: "dd-mm-yy",
            changeYear: true,
            changeMonth: true,
            maxDate: 0
        });
    </script>
@endsection
@endsection
