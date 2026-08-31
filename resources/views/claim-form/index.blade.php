@extends('components.admin.content-layout')

@section('card-content')
    <style>
        #dataTable_batch>tbody>tr>td:nth-child(3) {
            cursor: pointer;
            color: #0d6efd;
            text-decoration: underline;
            font-weight: 500;
        }

        #dataTable_batch>tbody>tr>td:nth-child(3):hover {
            color: #0a58ca;
        }
    </style>



    <div class="card-body">
        @if (hasRole('snp') ||
                hasRole('bnp') ||
                hasRole('lsp') ||
                hasRole('nsic-finance') ||
                hasRole('nsic') ||
                hasRole('nsic-maker') ||
                hasRole('nsic-checker') ||
                hasRole('ondc-admin'))
            <a class="btn btn-outline-info float-end rounded-pill px-4" href="{{ url('qms/' . ($claimSlug ?? '')) }}"
                slug="{{ $claimSlug }}" style="border-width: 2px; font-weight: 600;">
                <i class="fa fa-comments-o me-1"></i> Queries
                @php
                    $queryCount = app(\App\Domain\QMS\QueryService::class)->getNewQueriesCountForUser(auth()->id(), $claimSlug ?? null);
                @endphp
                @if ($queryCount > 0)
                    <span class="badge bg-danger rounded-pill ms-2" style="font-size: 0.75rem;">{{ $queryCount }}</span>
                @endif
            </a>
        @endif


        <input type="hidden" id="review_status" value="">
        <input type="hidden" id="claim_type_slug" value="{{ $claimSlug }}" />

        <ul class="nav nav-tabs" id="myTab" role="tablist">
            @php
                $statuses = $tabs['status'] ?? [];
                $defaultStatus = null;
            @endphp
            @foreach ($statuses as $key => $status)
                @if ($loop->first)
                    @if (hasRole('ca') || hasRole('ondc-admin') || hasRole('nsic') || hasRole('nsic-checker') || hasRole('nsic-finance'))
                        @php $defaultStatus = 'Pending'; @endphp
                    @else
                        @php $defaultStatus = $key; @endphp
                    @endif
                @endif

                @if (hasRole('snp') ||
                        hasRole('bnp') ||
                        hasRole('nsic-maker') ||
                        hasRole('administrator') ||
                        hasRole('lsp') ||
                        ($key !== 'Drafts' && $key !== 'Shared With CA' && $key !== 'Certified By CA'))
                    <li class="nav-item" role="presentation">
                        @if (hasRole('ca') && ($key == 'Rejected' || $key == 'Payment Completed'))
                        @elseif(hasRole('nsic-finance') && $key == 'Rejected')
                        @else
                            <button class="nav-link filter_btn" data-val="{{ $key }}" id="{{ $key }}-tab"
                                data-bs-toggle="tab" data-bs-target="#{{ $key }}" type="button" role="tab"
                                aria-controls="{{ $key }}" aria-selected="true">
                                <span class="badge bg-primary">{{ $status }}</span>
                                {{ hasRole('nsic-finance') && $key === 'Approved' ? 'Processed for Reimbursement' : $key }}
                            </button>
                        @endif
                    </li>
                @endif
            @endforeach
        </ul>



        <div id="claim_list_table_wrapper">
            @if ($claimSlug === 'claim-for-demand-generation')
                @include('claim-form.claim_list_demand')
            @elseif ($claimSlug === 'claim-for-ai-cataloguing')
                @include('claim-form.claim_list_ai_cataloguing')
            @else
                @include('claim-form.claim_list')
            @endif
        </div>



        <div id="batch_list_table_wrapper" style="display:none;">
            @include('claim-form.batch_list')
        </div>

        @if (!empty($declaration->is_declaration_required))
            @if ($declaration->is_declaration_required == 1)
                <p>
                    <input type="checkbox" name="is_declaration_agreed" id="is_declaration_agreed"
                        value="{{ $declaration->is_declaration_required == 1 ? 1 : '' }}">{{ $declaration->declaration_text }}
                </p>
            @endif
        @endif
    </div>



    <!-- Modal CSV -->
    <div class="modal fade" id="myCSVModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="modal-title mb-0">Add Bulk Claim</h5>
                    </div>

                    <div class="text-center flex-grow-1">
                        @if (($claimSlug ?? '') === 'claim-for-catalogue-creation')
                            <a href="{{ url('storage/app/download_format/claim_bulk_import.zip') }}"
                                class="btn btn-sm btn-outline-primary">Download Sample Format</a>
                        @elseif(($claimSlug ?? '') === 'claim-for-accounts-management')
                            <a href="{{ url('storage/app/download_format/account_bulk_import.zip') }}"
                                class="btn btn-sm btn-outline-primary">Download Sample Format</a>
                        @elseif(($claimSlug ?? '') === 'claim-for-transportation-and-logistic')
                            <a href="{{ url('storage/app/download_format/logistic_bulk_import.zip') }}"
                                class="btn btn-sm btn-outline-primary">Download Sample Format</a>
                        @else
                            <a href="javascript:void(0);" class="btn btn-sm btn-outline-secondary disabled">Unknown</a>
                        @endif
                    </div>

                    <div class="flex-grow-1 text-end">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <form id="bulk-upload">
                    @csrf
                    <div class="modal-body">
                        <p>
                            <select class="form-select" name="claim_type_id" id="claim_type_id">
                                <option value="">Select Claim Type</option>
                                @foreach ($claim_types ?? [] as $claim_type_id => $claim_type_name)
                                    <option value="{{ $claim_type_id }}"
                                        @if (($claimTypeIdValue ?? null) == $claim_type_id) selected @endif>
                                        {{ $claim_type_name }}
                                    </option>
                                @endforeach
                            </select>
                        </p>
                        <p>Select File : <input type="file" name="file" id="file" accept=".zip"></p>
                        <span class="text-primary">Please ensure the file is in the correct format by downloading the sample
                            format.</span>

                        <div id="csv-errors" style="color: red; font-family: Arial; padding: 10px;"></div>


                    </div>
                    <div class="modal-footer">
                        <button type="sbumit" class="btn btn-success">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>




    <!-- Modal for Payment -->
    <div class="modal fade" id="myPaymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="modal-title mb-0">Add Payment</h5>
                    </div>

                    <div class="flex-grow-1 text-end">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <form id="payment-form">
                    @csrf
                    <!--<input type="hidden" name="claim_id" id="claim_id">-->
                    <input type="hidden" name="pbatch_id" id="pbatch_id">
                    <div class="modal-body">
                        <p>PFMS Number<span class="text-danger">*</span> <input type="text" name="pfms_number" id="pfms_number" class="form-control"
                                placeholder="Enter PFMS Number"></p>
                        <p>Sanction Order Number <input type="text" name="sanction_order_number"
                                id="sanction_order_number" class="form-control"
                                placeholder="Enter Sanction Order Number"></p>
                        <p>Sanction Order Date 
                            <input type="text" id="sanction_order_date_display" class="form-control" placeholder="DD-MM-YYYY" autocomplete="off">
                            <input type="hidden" name="sanction_order_date" id="sanction_order_date">
                        </p>
                        <!-- <p>Amount <input type="text" name="amount" id="amount" class="form-control numeric"
                                                                                                                                                                                                            placeholder="Amount"></p>-->
                        <p>Comment
                            <textarea name="comment" id="comment" class="form-control"></textarea>
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="sbumit" class="btn btn-success">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    @include('claim-form.workflow_modal')

@section('js')
    <script>
        var hasCARole = {{ hasRole('ca') ? 'true' : 'false' }};
        var hasSNPRole =
            {{ hasRole('snp') || hasRole('lsp') || hasRole('bnp') || hasRole('nsic-maker') ? 'true' : 'false' }};
        var hasONDCRole = {{ hasRole('ondc-admin') ? 'true' : 'false' }};
        var hasNSICRole = {{ hasRole('nsic') ? 'true' : 'false' }};
        var hasFinanceRole = {{ hasRole('nsic-finance') || hasRole('nsic-checker') ? 'true' : 'false' }};



        $('#myPaymentModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget); // Button/link that triggered modal
            /*var claim_id = button.attr('claim-id'); 
            $('#claim_id').val(claim_id);*/

            var batch_id = button.attr('batch-id');
            $('#pbatch_id').val(batch_id);
        });

        $('#myPaymentModal').on('hidden.bs.modal', function() {
            $('#pfms_number').val('');
            $('#sanction_order_number').val('');
            $('#sanction_order_date').val('');
            $('#sanction_order_date_display').val('');
            $('#comment').val('');
        });

        $(document).ready(function() {
            $("#sanction_order_date_display").datepicker({
                dateFormat: "dd-mm-yy",
                changeYear: true,
                changeMonth: true,
                maxDate: 0,
                altField: "#sanction_order_date",
                altFormat: "yy-mm-dd"
            });
        });


        $('#myCSVModal').on('hidden.bs.modal', function() {
            $('#csv-errors').html(''); // clear the error messages
            $('#file').val('');
        });

        $(document).on('change', '#bulk-upload', function(e) {
            e.preventDefault();
            let fileName = e.target.files[0]?.name; // get selected file name
            if (fileName) {
                let ext = fileName.split('.').pop().toLowerCase();
                if (ext !== 'zip') {
                    alert("Only .zip files are allowed!");
                    $('#file').val('');
                }
            }
        });

        $(document).on('submit', '#bulk-upload', function(e) {
            e.preventDefault();

            document.getElementById('csv-errors').innerHTML = '';
            var claimTypeId = document.getElementById('claim_type_id').value;

            var form = document.getElementById('bulk-upload');
            var formData = new FormData(form);
            formData.append('claim_type_id', claimTypeId);
            if (claimTypeId == '') {
                toastr.error("Please select claim type.");
                return;
            }

            var fileInput = document.getElementById('file');
            if (!fileInput.files.length) {
                toastr.error("Please select a file to upload.");
                return;
            }

            $.ajax({
                url: "{{ url('claims/bulk-import') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $("#ajax-loader").show();
                },
                success: function(res) {
                    toastr.success(res.message);
                    $("#csv-errors").html('');
                    setTimeout(function() {
                        window.location.href = "{{ url('claims') }}";
                    }, 2000);
                },
                error: function(xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message);
                        displayCsvErrors(xhr.responseJSON);
                    } else {
                        toastr.error("Something went wrong.");
                        document.getElementById('csv-errors').innerHTML = '';
                    }
                },
                complete: function() {
                    $("#ajax-loader").hide();
                }
            });
        });

        // Function to format and display errors
        function displayCsvErrors(data) {
            let errorHtml = `<strong>${data.message}</strong><ul>`;

            for (let cell in data.errors) {
                data.errors[cell].forEach(msg => {
                    errorHtml += `<li><strong>Cell ${cell}</strong>: ${msg}</li>`;
                });
            }

            errorHtml += `</ul>`;
            document.getElementById('csv-errors').innerHTML = errorHtml;
        }


        $(document).on('submit', '#payment-form', function(e) {
            e.preventDefault();
            //var claimId = document.getElementById('claim_id').value;
            var batchId = document.getElementById('pbatch_id').value;
            var pfms_number = document.getElementById('pfms_number').value;
            var sanction_order_number = document.getElementById('sanction_order_number').value;
            var sanction_order_date = document.getElementById('sanction_order_date').value;
            // var amount = document.getElementById('amount').value;
            var comment = document.getElementById('comment').value;
            var claim_type_slug = $("#claim_type_slug").val();

            /*if (claimId == '') {
                toastr.error("Claim ID is missing.");
                return;
            }*/

            if (pfms_number == '') {
                toastr.error("Please enter PFMS Number.");
                return;
            }

            /*if (amount == '') {
                toastr.error("Please enter Amount.");
                return;
            }*/

            $.ajax({
                url: "{{ url('batch-payment-completed') }}",
                //url: "{{ url('claims/payment') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    //claim_id: claimId,
                    batch_id: batchId,
                    pfms_number: pfms_number,
                    sanction_order_number: sanction_order_number,
                    sanction_order_date: sanction_order_date,
                    claim_type: claim_type_slug,
                    //amount: amount,
                    comments: comment
                },
                success: function(res) {
                    var doRedirect = function() {
                        if (claim_type_slug === 'claim-for-demand-generation') {
                            window.location.href = "{{ url('demand-generation-claim') }}";
                        } else if (claim_type_slug === 'claim-for-ai-cataloguing') {
                            window.location.href = "{{ url('ai-cataloguing-claim') }}";
                        } else if (claim_type_slug === 'claim-for-catalogue-creation') {
                            window.location.href = "{{ url('claims') }}";
                        } else if (claim_type_slug === 'claim-for-accounts-management') {
                            window.location.href = "{{ url('accounts-claims') }}";
                        } else if (claim_type_slug === 'claim-for-packaging') {
                            window.location.href = "{{ url('packaging-support-claim') }}";
                        } else if (claim_type_slug === 'claim-for-transportation-and-logistic') {
                            window.location.href = "{{ url('logistics-transportation-claim') }}";
                        } else {
                            window.location.href = "{{ url('claims') }}";
                        }
                    };

                    if (res.message && (res.message.indexOf('warning') !== -1 || res.message.indexOf(
                            'Warning') !== -1)) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Attention',
                                text: res.message,
                                confirmButtonText: 'OK'
                            }).then(function() {
                                doRedirect();
                            });
                        } else {
                            alert(res.message);
                            doRedirect();
                        }
                    } else {
                        toastr.success(res.message);
                        setTimeout(function() {
                            doRedirect();
                        }, 2000);
                    }
                },
                error: function(xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error("Something went wrong.");
                    }
                }
            });
        });


        $(document).on('click', '#move_to_drafts', function(e) {
            var claimId = $(this).attr('claim-id');
            if (confirm("Are you sure you want to move to drafts this record?")) {
                $.ajax({
                    url: "{{ url('claim-move-to-drafts') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        claim_id: claimId,
                    },
                    success: function(res) {
                        toastr.success(res.message);
                        setTimeout(function() {
                            window.location.href = "{{ url($redirectUrl) }}";
                        }, 2000);
                    },
                    error: function(xhr) {
                        toastr.error("Something went wrong.");
                    }
                });
            }
        });



        @isset($defaultStatus)
            $("#review_status").val("{{ $defaultStatus }}");
            $(".filter_btn[data-val='{{ $defaultStatus }}']").addClass("active");
        @endisset



        var defaultStatus = "{{ $defaultStatus }}";
        console.log('defaultStatus:', defaultStatus);
        if (defaultStatus === 'Drafts') {
            $('#claim_list_table_wrapper').show();
            $('#batch_list_table_wrapper').hide();
            $('#reject_claim_list_table_wrapper').hide();
        } else {
            $('#claim_list_table_wrapper').hide();
            $('#batch_list_table_wrapper').show();
            $('#reject_claim_list_table_wrapper').hide();
        }



        // $('.filter_btn').on('click', function() {
        //     var selectedTab = $(this).data('val');
        //     $("#review_status").val(selectedTab);

        //     if (selectedTab === 'Drafts') {
        //         $('#claim_list_table_wrapper').show();
        //         $('#batch_list_table_wrapper').hide();
        // 		$('#reject_claim_list_table_wrapper').hide();
        // 		oTable.draw();
        //     } else if(selectedTab === 'Rejected'){

        //         $('#claim_list_table_wrapper').hide();
        //         $('#batch_list_table_wrapper').hide();
        // 		$('#reject_claim_list_table_wrapper').show();
        // 		oTable.draw();
        //     } else {
        //         $('#claim_list_table_wrapper').hide();
        //         $('#batch_list_table_wrapper').show();
        // 		$('#reject_claim_list_table_wrapper').hide();
        // 		oTable.draw();
        //     }
        // });

        let claimTable = null;
        let rejectTable = null;
        let batchTable = null;

        $(document).ready(function() {

            let loaded = {
                Drafts: false,
                Rejected: false,
                Batch: false
            };

            $('.filter_btn').on('click', function() {

                let key = $(this).data('val');
                $('#review_status').val(key);

                $('#claim_list_table_wrapper').hide();
                $('#reject_claim_list_table_wrapper').hide();
                $('#batch_list_table_wrapper').hide();

                $('.filter_btn').removeClass('active');
                $(this).addClass('active');

                let claimTypeSlug = $('#claim_type_slug').val();
                console.log('Filter clicked. Key:', key, 'ClaimTypeSlug:', claimTypeSlug);

                if (key === 'Drafts') {
                    $('#claim_list_table_wrapper').show();

                    if (!loaded.Drafts) {
                        console.log('Initializing claim table. Is initClaimTable defined?',
                            typeof initClaimTable);
                        claimTable = initClaimTable();
                        loaded.Drafts = true;
                    } else {
                        claimTable.ajax.reload();
                    }

                } else if (key === 'Rejected') {
                    $('#reject_claim_list_table_wrapper').show();

                    if (!loaded.Rejected) {
                        rejectTable = initRejectTable();
                        loaded.Rejected = true;
                    } else {
                        rejectTable.ajax.reload();
                    }

                } else {
                    $('#batch_list_table_wrapper').show();

                    if (!loaded.Batch) {
                        batchTable = initBatchTable();
                        loaded.Batch = true;
                    } else {
                        batchTable.ajax.reload();
                    }

                    if (batchTable) {
                        var showCheckbox = (hasONDCRole || hasNSICRole || hasFinanceRole) && (key ===
                            'Pending');
                        batchTable.column(0).visible(showCheckbox);
                    }
                }
            });

            $(".filter_btn[data-val='{{ $defaultStatus }}']").trigger('click');
        });




        /*$(".filter_btn").on("click", function() {
                const status = $(this).data("val");
                $("#review_status").val(status);
                $(".filter_btn").removeClass("active");
                $(this).addClass("active");
                oTable.draw();
            });*/







        <?php /*function viewTHistory(id) {
                   return `<a 
                     href="#" data-id="${id}" 
                     class="btn btn-sm btn-warning btn-circle m-1 timeline-request" rel="tooltip" title="History"
                     ><i class="fa fa-history"></i>
                   </a>`;
               } */
        ?>


        function initReadMoreToggle() {
            if (window.readMoreHandlerAttached) return;

            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.toggle-text');
                if (!btn) return;

                e.preventDefault();

                let td = btn.closest('td');
                let shortText = td.querySelector('.short-text');
                let fullText = td.querySelector('.full-text');

                if (!shortText || !fullText) return;

                shortText.classList.toggle('d-none');
                fullText.classList.toggle('d-none');

                btn.textContent =
                    btn.textContent.trim() === 'Read More' ?
                    'Read Less' :
                    'Read More';
            });

            window.readMoreHandlerAttached = true;
        }

        initReadMoreToggle();

        $(document).ready(function() {

            let hash = window.location.hash.replace('#', '');

            if (hash) {
                let tabBtn = $('.filter_btn[data-val="' + hash + '"]');

                if (tabBtn.length) {
                    tabBtn.trigger('click');
                }
            }

        });
    </script>
@endsection

<div id="reject_claim_list_table_wrapper" style="display:none;">
    @if ($claimSlug === 'claim-for-demand-generation')
        @include('claim-form.reject_claim_list_demand_generation')
    @elseif ($claimSlug === 'claim-for-ai-cataloguing')
        @include('claim-form.reject_claim_list_ai_cataloguing')
    @else
        @include('claim-form.reject_claim_list')
    @endif
</div>
@endsection
