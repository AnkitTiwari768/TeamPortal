<!-- Single Workflow Modal -->
<style>
    .remove-doc {
        display: none
    }
</style>

<div class="modal fade" id="workflowModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="workflowModalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <input type="hidden" id="dreview_status">
            <input type="hidden" id="workflow_claim_id">
            <input type="hidden" id="workflow_batch_id">
            <input type="hidden" id="workflow_route">
            <input type="hidden" id="workflow_action">
            <input type="hidden" id="workflow_base_amount">
            <input type="hidden" id="workflow_gst_amount">


            <div class="modal-body">
                @php
                    $hasCARole = hasRole('ca') ? true : false;
                    $hasSNPRole = hasRole('snp') || hasRole('lsp') || hasRole('bnp') ? true : false;
                    $hasFinanceRole = hasRole('nsic-finance') ? true : false;
                @endphp

                @if ($hasCARole || $hasSNPRole)
                    <div class="input-box" id="input-box">
                        <div id="div_drfts">
                            @if ($hasSNPRole)
                                <?php /*<input type="hidden" id="workflow_document_category_id" value="{{$documentDeclarationCategory->id}}">
								
								<label class="form-label">Upload Declaration<a class="tooltip-ins"  href="#" data-toggle="tooltip" title="{{ $documentDeclarationCategory->informations }}"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
								
								<a href="{{ url('storage/app/download_format/Format for declaration by SNP On letter head.pdf') }}" download>Download Sample Pdf Format</a></label>*/
                                ?>

                                {{-- <input type="hidden" id="workflow_document_category_id"
                                    value="1c76b7bf-6649-4ad1-b16b-e2604753bf35"> --}}

                                <input type="hidden" id="workflow_document_category_id"
                                    value="{{ $documentCategory->id }}">



                                <div class="mt-2">
                                    <label class="form-label required">GST Type</label>
                                    <select id="gst_type" name="gst_type" class="form-select">
                                        <option value="">-- Select GST Type --</option>
                                        <option value="1">GST</option>
                                        <option value="2">CGST + SGST</option>
                                    </select>
                                </div>

                                <div class="mt-2" id="gst_percentage_wrapper" style="display:none;">
                                    <label class="form-label required">GST Percentage (%)</label>
                                    <input type="number" name="gst_percentage" id="gst_percentage" class="form-control"
                                        placeholder="Enter GST Percentage (%)" min="0" max="100"
                                        step="any" maxlength="3" oninput="validateThreeDigits(this)" />
                                </div>

                                <div id="cgst_sgst_wrapper" style="display:none;">
                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <label class="form-label required">CGST Percentage (%)</label>
                                            <input type="number" name="cgst_percentage" id="cgst_percentage"
                                                class="form-control" placeholder="Enter CGST Percentage" min="0"
                                                max="100" step="any" maxlength="3"
                                                oninput="validateThreeDigits(this)" />
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label required">SGST Percentage (%)</label>
                                            <input type="number" name="sgst_percentage" id="sgst_percentage"
                                                class="form-control" placeholder="Enter SGST Percentage" min="0"
                                                max="100" step="any" maxlength="3"
                                                oninput="validateThreeDigits(this)" />
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if ($hasCARole)
                                <input type="hidden" id="workflow_document_category_id"
                                    value="{{ $documentCategory->id ?? null }}">
                                <label class="form-label required">Upload CA Certificate<a class="tooltip-ins"
                                        href="#" data-toggle="tooltip"
                                        title="{{ $documentCategory->informations ?? null }}"><i
                                            class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    <a href="{{ url('storage/app/download_format/Annexure-Statutory-Auditor-Certificate.docx') }}"
                                        download>Predefined CA Certificate Format</a></label>
                            @endif

                            <label class="form-label required mt-3">Upload Invoice<a class="tooltip-ins" href="#"
                                    data-toggle="tooltip" title="{{ $documentCategory->informations ?? null }}"><i
                                        class="fa fa-question-circle" aria-hidden="true"></i></a>
                                <a href="{{ url('storage/app/download_format/InvoiceDoument.docx') }}"
                                    download>Predefined SNP Invoice Format</a>
                            </label>

                            <input type="file" class="form-control " id="upload" />
                            <!--<div class="upload_file_link"></div>-->
                            <input type="hidden" name="workflow_file_upload_id" id="workflow_file_upload_id" />
                            <div class="progress-upload progress-bg" style="display:none;">
                                <div id="loader-upload" style=""></div>
                                <div class="progress-bar"></div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($hasFinanceRole)
                    <div class="input-box mt-2" id="workflow_tds_div" style="display:none;">
                        <label class="form-label required" for="workflow_tds">TDS Amount</label>
                        <input type="number" min="0" step="any" class="form-control" id="workflow_tds"
                            name="tds" placeholder="Enter TDS Amount">
                    </div>
                    <div class="input-box mt-2" id="workflow_sgst_tds_div" style="display:none;">
                        <label class="form-label" for="workflow_sgst_tds">TDS on SGST Amount</label>
                        <input type="number" min="0" step="any" class="form-control"
                            id="workflow_sgst_tds" name="sgst" placeholder="Enter TDS on SGST Amount">
                    </div>
                    <div class="input-box mt-2" id="workflow_cgst_tds_div" style="display:none;">
                        <label class="form-label" for="workflow_cgst_tds">TDS on CGST Amount</label>
                        <input type="number" min="0" step="any" class="form-control"
                            id="workflow_cgst_tds" name="cgst" placeholder="Enter TDS on CGST Amount">
                    </div>
                    <div class="input-box mt-2" id="workflow_igst_tds_div" style="display:none;">
                        <label class="form-label" for="workflow_igst_tds">TDS on IGST Amount</label>
                        <input type="number" min="0" step="any" class="form-control"
                            id="workflow_igst_tds" name="igst" placeholder="Enter TDS on IGST Amount">
                    </div>
                @endif

                <div class="input-box mt-2">
                    <label class="form-label required" for="workflow_comments">Remarks</label>
                    <textarea class="form-control" id="workflow_comments" required></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn" id="workflowButton"></button>
                <!--<button type="button" class="btn" id="workflowButton" data-bs-dismiss="modal"></button>-->
            </div>
        </div>
    </div>
</div>


@push('js')
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#workflowModal').on('hidden.bs.modal', function() {
            $('#upload').val('');
        });


        $("#upload").on("change", function(e) {

            /*e.preventDefault();
            let fileName = e.target.files[0]?.name;
            if (fileName) {
            	let ext = fileName.split('.').pop().toLowerCase();
            	if (ext !== 'pdf') {
            		alert("Only .pdf files are allowed!");
            		$('#upload').val('');
            	}
            }*/

            fileUploadWithLoader({
                url: "{{ url('upload-ca-certificate') }}",
                selector: "#upload",
                fileFieldName: 'file',
                hiddenInputSelector: '#workflow_file_upload_id',
                progressElementSelector: '.progress-upload',
                loaderElementSelector: '#loader-upload',
                loadingContent: 'uploading...',
                successMessage: 'Successfully uploaded.',
                errorMessage: 'Invalid file type',
                showFileName: '.upload_file_link'
            });
        });

        // Open modal dynamically
        $('#workflowModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var batch_id = button.attr('batch-id');
            var claim_id = button.attr('claim-id');

            var route = button.attr('route');
            var action = button.attr('action');

            var upload_ca_certificate = button.attr('upload-ca-certificate');

            var dreview_status = $("#review_status").val();

            var base_amount = button.attr('base-amount');
            var gst_amount = button.attr('gst-amount');

            // Set modal values
            $('#workflow_batch_id').val(batch_id);
            $('#workflow_claim_id').val(claim_id);
            $('#workflow_route').val(route);
            $('#workflow_action').val(action);
            $("#dreview_status").val(dreview_status);
            $('#workflow_base_amount').val(base_amount);
            $('#workflow_gst_amount').val(gst_amount);

            if (claim_id != undefined && claim_id != '') {
                $("#input-box").hide();
            }

            if (action === 'move-to-drafts') {
                $("#div_drfts").hide();
            }

            // Update modal title and button
            if (action === 'approve') {
                $('#workflowModalTitle').text('Approve');
                $('#workflowButton')
                    .text('Approve')
                    .removeClass('btn-info btn-primary btn-danger')
                    .addClass('btn-success');
            } else if (action === 'revert') {
                $("#input-box").hide();
                $('#workflowModalTitle').text('Revert');
                $('#workflowButton')
                    .text('Revert')
                    .removeClass('btn-success btn-primary btn-danger')
                    .addClass('btn-info');
            } else if (action === 'resend-to-ca') {
                $("#input-box").hide();
                $('#workflowModalTitle').text('Re-send to CA');
                $('#workflowButton')
                    .text('Re-send to CA')
                    .removeClass('btn-success btn-info btn-danger')
                    .addClass('btn-primary');
            } else if (action === 'forward-to-ondc') {
                $('#workflowModalTitle').text('Forward to ONDC');
                $('#workflowButton')
                    .text('Forward to ONDC')
                    .removeClass('btn-success btn-info btn-danger')
                    .addClass('btn-primary');
            } else if (action === 'reject') {
                $('#workflowModalTitle').text('Reject');
                $('#workflowButton')
                    .text('Reject')
                    .removeClass('btn-success btn-info btn-primary')
                    .addClass('btn-danger');
            } else if (action === 'forward-to-nsic-by-ondc') {
                $('#workflowModalTitle').text('Forward to NSIC');
                $('#workflowButton')
                    .text('Forward to NSIC')
                    .removeClass('btn-success btn-info btn-danger')
                    .addClass('btn-primary');
            } else if (action === 'batch-claim-revert-to-snp') {
                $('#workflowModalTitle').text('Revert to SNP');
                $('#workflowButton')
                    .text('Revert to SNP')
                    .removeClass('btn-success btn-info btn-danger')
                    .addClass('btn-primary');
            } else if (action === 'batch-claim-revert-to-ondc') {
                $('#workflowModalTitle').text('Revert to ONDC');
                $('#workflowButton')
                    .text('Revert to ONDC')
                    .removeClass('btn-success btn-info btn-danger')
                    .addClass('btn-primary');
            } else if (action === 'forward-to-nsic-finance') {
                $('#workflowModalTitle').text('Forward to NSIC Finance');
                $('#workflowButton')
                    .text('Forward to NSIC Finance')
                    .removeClass('btn-success btn-info btn-danger')
                    .addClass('btn-primary');
            } else if (action === 'batch-final-approval') {
                $('#workflowModalTitle').text('Approve');
                $('#workflowButton')
                    .text('Approve')
                    .removeClass('btn-success btn-info btn-danger')
                    .addClass('btn-primary');
            } else if (action === 'resend-to-nsic') {
                $('#workflowModalTitle').text('Re-send to NSIC');
                $('#workflowButton')
                    .text('Re-send to NSIC')
                    .removeClass('btn-success btn-info btn-danger')
                    .addClass('btn-primary');
            } else if (action === 'resend-nsic-to-finance') {
                $('#workflowModalTitle').text('Re-Send To Finance');
                $('#workflowButton')
                    .text('Re-Send To Finance')
                    .removeClass('btn-success btn-info btn-danger')
                    .addClass('btn-primary');
            } else if (action === 'proceed-batch-workflow-to-ca') {
                $("#input-box").hide();
                $('#workflowModalTitle').text('Proceed');
                $('#workflowButton')
                    .text('Proceed')
                    .removeClass('btn-success btn-info btn-danger')
                    .addClass('btn-primary');
                @if ($hasFinanceRole)
                    $('#workflow_tds_div').hide();
                    $('#workflow_sgst_tds_div').hide();
                    $('#workflow_cgst_tds_div').hide();
                    $('#workflow_igst_tds_div').hide();
                @endif
            } else if (action === 'proceed-batch-workflow-sent-to-finance') {
                $("#input-box").hide();
                $('#workflowModalTitle').text('Proceed');
                $('#workflowButton')
                    .text('Proceed')
                    .removeClass('btn-success btn-info btn-danger')
                    .addClass('btn-primary');
                @if ($hasFinanceRole)
                    $('#workflow_tds_div').hide();
                    $('#workflow_sgst_tds_div').hide();
                    $('#workflow_cgst_tds_div').hide();
                    $('#workflow_igst_tds_div').hide();
                @endif
            } else if (action === 'proceed-batch-workflow') {
                $('#workflowModalTitle').text('Proceed');
                $('#workflowButton')
                    .text('Proceed')
                    .removeClass('btn-success btn-info btn-danger')
                    .addClass('btn-primary');
                @if ($hasFinanceRole)
                    $('#workflow_tds_div').show();
                    $('#workflow_sgst_tds_div').show();
                    $('#workflow_cgst_tds_div').show();
                    $('#workflow_igst_tds_div').show();
                @endif
            } else if (action === 'invoice-query') {
                $('#workflowModalTitle').text('Send Invoice Re-Upload Request');
                $('#workflowButton')
                    .text('Send Request')
                    .removeClass('btn-success btn-info btn-danger')
                    .addClass('btn-primary');

            } else if (action === 'query') {
                $('#workflowModalTitle').text('Send Query Request');
                $('#workflowButton')
                    .text('Send Request')
                    .removeClass('btn-success btn-info btn-danger')
                    .addClass('btn-primary');

            } else {
                $('#workflowModalTitle').text('Move to Drafts');
                $('#workflowButton')
                    .text('Move to Drafts')
                    .removeClass('btn-success btn-info btn-danger')
                    .addClass('btn-primary');
            }
        });


        // Handle button click
        /*$('#workflowModal').on('hide.bs.modal', function (event) {
        	event.preventDefault();
        	return false;
        });*/

        $(document).on("click", "#workflowButton", function(e) {
            e.preventDefault();

            var batchId = $('#workflow_batch_id').val();
            var claimId = $('#workflow_claim_id').val();
            var route = $('#workflow_route').val();
            var comments = $('#workflow_comments').val();
            var action = $('#workflow_action').val();
            var workflow_file_upload_id = $('#workflow_file_upload_id').val();
            var workflow_document_category_id = $('#workflow_document_category_id').val();
            var dreview_status = $('#dreview_status').val();

            var workflow_tds = $('#workflow_tds').val();
            var workflow_sgst_tds = $('#workflow_sgst_tds').val();
            var workflow_cgst_tds = $('#workflow_cgst_tds').val();
            var workflow_igst_tds = $('#workflow_igst_tds').val();

            var claimSlug = $("#claim_type_slug").val();


            /*if ( /*___directives_script_0___
            			) {
            				if (!workflow_file_upload_id || workflow_file_upload_id === '' || !batchId) {
            					e.preventDefault();
            					toastr.error("Please upload document");
            					return false;
            				}
            			endif */

            if (!comments || comments.trim() === '' || !batchId) {
                e.preventDefault();
                toastr.error("Please enter comments");
                return false;
            }

            if ($('#workflow_tds').length > 0 && $('#workflow_tds').is(':visible')) {
                if (action !== 'reject' && action !== 'revert') {
                    // if (!workflow_tds || workflow_tds.trim() === '') {
                    //     e.preventDefault();
                    //     toastr.error("Please enter TDS amount");
                    //     return false;
                    // }

                    var baseAmount = parseFloat($('#workflow_base_amount').val()) || 0;
                    var gstAmount = parseFloat($('#workflow_gst_amount').val()) || 0;
                    var maxTdsAllowed = baseAmount + gstAmount;

                    var totalTds = (parseFloat(workflow_tds) || 0) +
                        (parseFloat(workflow_sgst_tds) || 0) +
                        (parseFloat(workflow_cgst_tds) || 0) +
                        (parseFloat(workflow_igst_tds) || 0);

                    if (totalTds > maxTdsAllowed) {
                        e.preventDefault();
                        toastr.error("Total TDS amount (" + totalTds.toFixed(2) +
                            ") cannot be greater than Base Amount + GST Amount (" +
                            maxTdsAllowed.toFixed(2) + ").");
                        return false;
                    }
                }
            }

            var gstType = $('#gst_type').val();
            var gstPercentage = $('#gst_percentage').val();
            var cgstPercentage = $('#cgst_percentage').val();
            var sgstPercentage = $('#sgst_percentage').val();

            if ($('#gst_type').length > 0 && $('#gst_type').is(':visible')) {
                if (!gstType || gstType === '') {
                    toastr.error("Please select GST Type.");
                    return false;
                }
                if (gstType === '1') {
                    if (!gstPercentage || gstPercentage === '') {
                        toastr.error("Please enter GST percentage.");
                        return false;
                    }
                    var gstNum = parseFloat(gstPercentage);
                    if (isNaN(gstNum) || gstNum < 0 || gstNum > 100) {
                        toastr.error("GST percentage must be between 0 and 100.");
                        return false;
                    }
                } else if (gstType === '2') {
                    if (!cgstPercentage || cgstPercentage === '') {
                        toastr.error("Please enter CGST percentage.");
                        return false;
                    }
                    if (!sgstPercentage || sgstPercentage === '') {
                        toastr.error("Please enter SGST percentage.");
                        return false;
                    }
                    var cgstNum = parseFloat(cgstPercentage);
                    var sgstNum = parseFloat(sgstPercentage);
                    if (isNaN(cgstNum) || cgstNum < 0 || cgstNum > 100) {
                        toastr.error("CGST percentage must be between 0 and 100.");
                        return false;
                    }
                    if (isNaN(sgstNum) || sgstNum < 0 || sgstNum > 100) {
                        toastr.error("SGST percentage must be between 0 and 100.");
                        return false;
                    }
                    if (cgstNum + sgstNum > 100) {
                        toastr.error("CGST + SGST total cannot exceed 100%.");
                        return false;
                    }
                }
            }

            $.ajax({
                url: route,
                type: 'POST',
                data: {
                    action: action,
                    batch_id: batchId,
                    claim_id: claimId,
                    claim_type: claimSlug,
                    comments: comments,
                    file_upload_id: workflow_file_upload_id,
                    document_category_id: workflow_document_category_id,
                    review_status: dreview_status,
                    tds: workflow_tds,
                    sgst: workflow_sgst_tds,
                    cgst: workflow_cgst_tds,
                    igst: workflow_igst_tds,
                    gst_type: gstType,
                    gst_percentage: gstPercentage,
                    cgst_percentage: cgstPercentage,
                    sgst_percentage: sgstPercentage
                },
                success: function(res) {
                    console.log(res);
                    if (res.message && (res.message.indexOf('warning') !== -1 || res.message.indexOf(
                            'Warning') !== -1)) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Attention',
                                text: res.message,
                                confirmButtonText: 'OK'
                            }).then(function() {
                                window.location.href = "{{ url($redirectUrl) }}";
                            });
                        } else {
                            alert(res.message);
                            window.location.href = "{{ url($redirectUrl) }}";
                        }
                    } else {
                        toastr.success(res.message);
                        setTimeout(function() {
                            window.location.href = "{{ url($redirectUrl) }}";
                        }, 1000);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON.message);
                }
            });
        });


        // Validation function for max 3 digits
        function validateThreeDigits(input) {
            var value = input.value;
            if (value.length > 3) {
                input.value = value.slice(0, 3);
            }
            if (!/^\d*\.?\d*$/.test(input.value)) {
                input.value = input.value.replace(/[^0-9.]/g, '');
            }
        }

        // GST Type dropdown toggle
        $(document).on('change', '#gst_type', function() {
            let type = $(this).val();

            $('#gst_percentage_wrapper').hide();
            $('#cgst_sgst_wrapper').hide();

            $('#gst_percentage').val('').removeClass('is-invalid');
            $('#cgst_percentage').val('').removeClass('is-invalid');
            $('#sgst_percentage').val('').removeClass('is-invalid');
            $('#gst-feedback, #cgst-feedback, #sgst-feedback').remove();

            if (type === '1') {
                $('#gst_percentage_wrapper').show();
            } else if (type === '2') {
                $('#cgst_sgst_wrapper').show();
            }
        });

        // Clear modal on close
        $('#workflowModal').on('hidden.bs.modal', function() {
            $(this).find('textarea').val('');
            $(this).find('select').prop('selectedIndex', 0);
            $('#workflow_tds').val('');
            $('#workflow_sgst_tds').val('');
            $('#workflow_cgst_tds').val('');
            $('#workflow_igst_tds').val('');
            $('#workflow_base_amount').val('');
            $('#workflow_gst_amount').val('');
            $('#gst_type').val('');
            $('#gst_percentage').val('');
            $('#cgst_percentage').val('');
            $('#sgst_percentage').val('');
            $('#gst_percentage_wrapper').hide();
            $('#cgst_sgst_wrapper').hide();
        });
    </script>
@endpush
