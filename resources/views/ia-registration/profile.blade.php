@extends('components.admin.content-layout')

@section('styles')
    <link href="{{ asset('assets/css/styles.css') }}" rel="stylesheet" />
    <style>
        .file-locked {
            pointer-events: none;
            background-color: #f8f9fa;
            opacity: 1;
        }

        .select-locked {
            pointer-events: none;
            background-color: #f8f9fa;
        }

        .disabled {
            background-color: #dddd !important;
            cursor: no-drop;
        }

        /* Print Button Styles */
        .print-btn-container {
            position: absolute;
            top: 15px;
            right: 35px;
            z-index: 1000;
        }

        .print-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 8px 18px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .print-btn:hover {
            background-color: #45a049;
            transform: scale(1.02);
        }

        .print-btn i {
            font-size: 16px;
        }
    </style>
@endsection

@section('card-content')
    <?php
    function editable($field, $editableFields)
    {
        return in_array($field, $editableFields);
    }
    ?>
    
    <div class="card-body pt-1" style="position: relative;">
        <!-- Print Button - Top Right -->
        <div class="print-btn-container">
            <button onclick="printForm()" class="print-btn" id="printBtn">
                <i class="fa fa-print"></i> Print
            </button>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form method="post" id="formId" class="mt-2 pt-3 formId">
                    @if (isset($id))
                        <input type="hidden" name="id" value="{{ $id }}">
                    @endif

                    <div class="row basic-detail">
                        <div class="col-lg-4 mb-3">
                            <div class="mb-0">
                                <label class="form-label required">Name of Associate/Organization</label>
                                <input type="text" class="input-text txtnumerichypenapercend disabled"
                                    name="organization_name" maxlength="100" placeholder="Name of Associate/Organization"
                                    id="organization_name"
                                    @if (isset($id) && !empty($row['organization_name'])) value="{{ $row['organization_name'] }}" @endif
                                    @if (!editable('organization_name', $editableFields)) readonly @endif>
                            </div>
                            <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                id="organization_name_error"></span>
                        </div>

                        <div class="col-lg-4 mb-3">
                            <div class="mb-0">
                                <label class="form-label required">Entity Type</label>
                                <div class="{{ !editable('entity_type', $editableFields) ? 'select-locked' : '' }}">
                                    <select name="entity_type_fake" id="entity_type" class="form-select text-dark disabled">
                                        <option value="">Select</option>
                                        @foreach ($lists->entity_type as $key => $value)
                                            <option value="{{ $key }}"
                                                @if (isset($row['entity_type']) && $row['entity_type'] == $key) selected @endif>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @if (!editable('entity_type', $editableFields))
                                    <input type="hidden" name="entity_type" value="{{ $row['entity_type'] ?? '' }}">
                                @endif
                            </div>
                            <span class="text-danger form-error font-12 mb-3" id="entity_type_error"></span>
                        </div>

                        <div class="col-lg-4 mb-3">
                            <div class="mb-0">
                                <label class="form-label required">Email of Entity</label>
                                <input type="text" class="input-text alphanumeric disabled" name="entity_email"
                                    maxlength="100" id="entity_email" placeholder="Email of Entity"
                                    @if (isset($id) && !empty($row['entity_email'])) value="{{ $row['entity_email'] }}" @endif
                                    @if (!editable('entity_email', $editableFields)) readonly @endif>
                            </div>
                            <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                id="entity_email_error"></span>
                        </div>
                    </div>

                    <div class="row contact_detail">
                        <div class="col-lg-4 mb-3">
                            <div class="mb-0">
                                <label class="form-label">Number of Members/Beneficiaries 
                                    <a class="tooltip-ins" href="#" data-toggle="tooltip"
                                        title="Number of members/beneficiaries associated with your association.">
                                        <i class="fa fa-question-circle" aria-hidden="true"></i>
                                    </a>
                                </label>
                                <input type="text" class="input-text integer" name="number_of_members" maxlength="10"
                                    id="number_of_members" placeholder="Number of Members/Beneficiaries"
                                    @if (isset($id) && !empty($row['number_of_members'])) value="{{ $row['number_of_members'] }}" @endif
                                    @if (!editable('number_of_members', $editableFields)) readonly @endif>
                            </div>
                            <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                id="number_of_members_error"></span>
                        </div>

                        <div class="col-lg-4 mb-3">
                            <div class="mb-0">
                                <label class="form-label">Registration No</label>
                                <input type="text" class="input-text alphaNumeric disabled" maxlength="100"
                                    name="registration_number" id="registration_number" placeholder="Registration No"
                                    @if (isset($id) && !empty($row['registration_number'])) value="{{ $row['registration_number'] }}" @endif
                                    @if (!editable('registration_number', $editableFields)) readonly @endif>
                            </div>
                            <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                id="registration_number_error"></span>
                        </div>

                        <div class="col-lg-4 mb-3">
                            <div class="mb-0">
                                <label class="form-label">Website</label>
                                <input type="text" class="input-text alphanumeric disabled" name="website" id="website"
                                    placeholder="Website"
                                    @if (isset($id) && !empty($row['website'])) value="{{ $row['website'] }}" @endif
                                    @if (!editable('website', $editableFields)) readonly @endif>
                            </div>
                            <span class="text-danger form-error font-12 font-12 font-12 mb-3" id="website_error"></span>
                        </div>

                        <div class="col-lg-4 mb-3">
                            <div class="mb-0">
                                <label class="form-label required">Contact No</label>
                                <input type="text" class="input-text numeric" name="contact_number" minlength="10"
                                    maxlength="10" id="contact_number" placeholder="Contact No"
                                    @if (isset($id) && !empty($row['contact_number'])) value="{{ $row['contact_number'] }}" @endif
                                    @if (!editable('contact_number', $editableFields)) readonly @endif>
                            </div>
                            <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                id="contact_number_error"></span>
                        </div>

                        <div class="col-lg-4 mb-3">
                            <div class="mb-0">
                                <label class="form-label">PAN/TAN</label>
                                <input type="text" class="input-text alphanumeric disabled" name="pan_number"
                                    minlength="10" maxlength="10" id="pan_number" placeholder="PAN"
                                    @if (isset($id) && !empty($row['pan_number'])) value="{{ $row['pan_number'] }}" @endif
                                    @if (!editable('pan_number', $editableFields)) readonly @endif>
                            </div>
                            <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                id="pan_number_error"></span>
                        </div>

                        <div class="col-lg-4 mb-3">
                            <div class="mb-0">
                                <label class="form-label required">State</label>
                                <div class="{{ !editable('state_id', $editableFields) ? 'select-locked' : '' }}">
                                    {{ Form::select(
                                        'state_id_fake',
                                        ['' => 'Select'] + remove_select_dynamic_common_list($lists?->states),
                                        $row['state_id'] ?? '',
                                        ['class' => 'form-select text-dark', 'id' => 'state_id', 'disabled' => true],
                                    ) }}
                                </div>
                                @if (!editable('state_id', $editableFields))
                                    <input type="hidden" name="state_id" value="{{ $row['state_id'] ?? '' }}">
                                @endif
                            </div>
                            <span class="text-danger form-error font-12 mb-3" id="state_id_error"></span>
                        </div>

                        <div class="col-lg-4 mb-3">
                            <div class="mb-0">
                                <label class="form-label required">District</label>
                                <div class="{{ !editable('district_id', $editableFields) ? 'select-locked' : '' }}">
                                    {{ Form::select(
                                        'district_id_fake',
                                        ['' => 'Select'] + remove_select_dynamic_common_list($lists?->district),
                                        $row['district_id'] ?? '',
                                        ['class' => 'form-select text-dark', 'id' => 'district_id', 'disabled' => true],
                                    ) }}
                                </div>
                                @if (!editable('district_id', $editableFields))
                                    <input type="hidden" name="district_id" value="{{ $row['district_id'] ?? '' }}">
                                @endif
                            </div>
                            <span class="text-danger form-error font-12 mb-3" id="district_id_error"></span>
                        </div>

                        <div class="col-lg-4 mb-3">
                            <div class="mb-0">
                                <label class="form-label required">Complete Address</label>
                                <input type="text" class="input-text alphanumeric" name="complete_address"
                                    id="complete_address" placeholder="Complete Address"
                                    @if (isset($id) && !empty($row['complete_address'])) value="{{ $row['complete_address'] }}" @endif
                                    @if (!editable('complete_address', $editableFields)) readonly @endif>
                            </div>
                            <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                id="complete_address_error"></span>
                        </div>

                        <div class="col-lg-4 mb-3">
                            <div class="mb-0">
                                <label class="form-label required">Contact Person Name</label>
                                <input type="text" class="input-text txtOnly" name="contact_person_name"
                                    id="contact_person_name" placeholder="Contact Person Name"
                                    @if (isset($id) && !empty($row['contact_person_name'])) value="{{ $row['contact_person_name'] }}" @endif
                                    @if (!editable('contact_person_name', $editableFields)) readonly @endif>
                            </div>
                            <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                id="contact_person_name_error"></span>
                        </div>

                        <div class="col-lg-4 mb-3">
                            <div class="mb-0">
                                <label class="form-label required">Designation of Contact Person</label>
                                <input type="text" class="input-text txtOnly" name="contact_person_designation"
                                    maxlength="15" id="contact_person_designation"
                                    placeholder="Designation of Contact Person"
                                    @if (isset($id) && !empty($row['contact_person_designation'])) value="{{ $row['contact_person_designation'] }}" @endif
                                    @if (!editable('contact_person_designation', $editableFields)) readonly @endif>
                            </div>
                            <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                id="contact_person_designation_error"></span>
                        </div>

                        <div class="col-lg-4 mb-3">
                            <div class="mb-0">
                                <label class="form-label required">Contact Person Phone No</label>
                                <input type="text" class="input-text integer" name="contact_person_phone"
                                    minlength="10" maxlength="10" id="contact_person_phone"
                                    placeholder="Contact Person Phone No"
                                    @if (isset($id) && !empty($row['contact_person_phone'])) value="{{ $row['contact_person_phone'] }}" @endif
                                    @if (!editable('contact_person_phone', $editableFields)) readonly @endif>
                            </div>
                            <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                id="contact_person_phone_error"></span>
                        </div>

                        <div class="col-lg-4 mb-3">
                            <div class="mb-0">
                                <label class="form-label required">Contact Person Email</label>
                                <input type="text" class="input-text alphanumeric" name="contact_person_email"
                                    id="contact_person_email" placeholder="Contact Person Email"
                                    @if (isset($id) && !empty($row['contact_person_email'])) value="{{ $row['contact_person_email'] }}" @endif
                                    @if (!editable('contact_person_email', $editableFields)) readonly @endif>
                            </div>
                            <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                id="contact_person_email_error"></span>
                        </div>

                        <div class="row">
                            <div class="col-lg-12 mb-3">
                                <div class="input-box">
                                    @if(isset($documents) && !empty($documents))
                                        @foreach ($documents as $doc)
                                            <div class="form-group col-md-4 mb-2">
                                                <label class="form-label required">
                                                    {{ $doc->document_category_name ?? '' }}
                                                    <a class="tooltip-ins" href="#" data-toggle="tooltip"
                                                        title="{{ $doc->informations ?? '' }}">
                                                        <i class="fa fa-question-circle"></i>
                                                    </a>
                                                </label>
                                                <input type="file"
                                                    class="form-control upload-pdf {{ !editable('authorization_document', $editableFields) ? 'file-locked' : '' }} disabled"
                                                    {{ !editable('authorization_document', $editableFields) ? 'readonly' : '' }}
                                                    document_category_id="{{ $doc->id ?? '' }}">
                                                @if (!empty($row['authorization_file_system_name']))
                                                    <div class="my-3">
                                                        <a href="{{ url('storage/app/' . ($row['authorization_file_path'] ?? '') . '/' . ($row['authorization_file_system_name'] ?? '')) }}"
                                                            target="_blank" class="text-primary">
                                                            <i class="fa fa-download"></i>
                                                            {{ $row['authorization_file_name'] ?? 'Download' }}
                                                        </a>
                                                    </div>
                                                    <input type="hidden" name="authorization_document"
                                                        value="{{ $row['authorization_file_system_name'] ?? '' }}">
                                                    <input type="hidden" name="authorization_document_id"
                                                        value="{{ $row['authorization_document_id'] ?? '' }}">
                                                @endif
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7">
                            <div class="action-bottom flex-row justify-content-center">
                                <button id="cancel-button" type="reset"
                                    class="btn btn-secondary btn-themed mt-3">Cancel</button>
                                <button id="signup-button" type="submit"
                                    class="btn btn-primary btn-themed mt-3">Submit</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @include('components.admin.popup.sms-email')
    @include('components.admin.file-upload')
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            $(document).on('focus input change', 'input, textarea, select', function() {
                let fieldId = $(this).attr('id');
                if (fieldId) {
                    $("#" + fieldId + "_error").text("");
                }
            });
        });

        // Print Function - New Window Approach
        function printForm() {
            // Get all form data
            var organization_name = $('#organization_name').val() || 'Not Provided';
            var entity_type = $('#entity_type option:selected').text() || 'Not Selected';
            var entity_email = $('#entity_email').val() || 'Not Provided';
            var number_of_members = $('#number_of_members').val() || 'Not Provided';
            var registration_number = $('#registration_number').val() || 'Not Provided';
            var website = $('#website').val() || 'Not Provided';
            var contact_number = $('#contact_number').val() || 'Not Provided';
            var pan_number = $('#pan_number').val() || 'Not Provided';
            var state_id = $('#state_id option:selected').text() || 'Not Selected';
            var district_id = $('#district_id option:selected').text() || 'Not Selected';
            var complete_address = $('#complete_address').val() || 'Not Provided';
            var contact_person_name = $('#contact_person_name').val() || 'Not Provided';
            var contact_person_designation = $('#contact_person_designation').val() || 'Not Provided';
            var contact_person_phone = $('#contact_person_phone').val() || 'Not Provided';
            var contact_person_email = $('#contact_person_email').val() || 'Not Provided';

            // Get document info
            var document_link = '';
            @if(!empty($row['authorization_file_system_name']))
                document_link = '{{ url('storage/app/' . ($row['authorization_file_path'] ?? '') . '/' . ($row['authorization_file_system_name'] ?? '')) }}';
                document_name = '{{ $row['authorization_file_name'] ?? 'Document' }}';
            @endif

            // Create print window
            var printWindow = window.open('', '_blank', 'width=1000,height=800,scrollbars=yes');
            
            // Write HTML content
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Associate Registration Details</title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            padding: 30px;
                            background: white;
                        }
                        .print-header {
                            text-align: center;
                            border-bottom: 2px solid #333;
                            padding-bottom: 15px;
                            margin-bottom: 25px;
                        }
                        .print-header h2 {
                            margin: 0;
                            color: #333;
                            font-size: 24px;
                        }
                        .print-header p {
                            margin: 5px 0 0 0;
                            color: #666;
                            font-size: 14px;
                        }
                        .print-section {
                            margin-bottom: 20px;
                        }
                        .print-section-title {
                            background: #f5f5f5;
                            padding: 8px 12px;
                            margin-bottom: 15px;
                            font-weight: bold;
                            font-size: 16px;
                            border-left: 4px solid #4CAF50;
                        }
                        .print-row {
                            display: flex;
                            flex-wrap: wrap;
                            margin-bottom: 8px;
                            padding: 5px 0;
                            border-bottom: 1px dashed #eee;
                        }
                        .print-label {
                            width: 30%;
                            font-weight: 600;
                            color: #555;
                            font-size: 14px;
                        }
                        .print-value {
                            width: 70%;
                            color: #000;
                            font-size: 14px;
                            word-break: break-word;
                        }
                        .print-document {
                            margin-top: 10px;
                            padding: 10px;
                            background: #f9f9f9;
                            border: 1px solid #ddd;
                            border-radius: 4px;
                        }
                        .print-document a {
                            color: #0066cc;
                            text-decoration: none;
                        }
                        .print-footer {
                            text-align: center;
                            margin-top: 30px;
                            padding-top: 15px;
                            border-top: 1px solid #ddd;
                            color: #999;
                            font-size: 12px;
                        }
                        .print-badge {
                            display: inline-block;
                            padding: 2px 8px;
                            background: #4CAF50;
                            color: white;
                            border-radius: 3px;
                            font-size: 12px;
                        }
                        @media print {
                            body { padding: 20px; }
                            .no-print { display: none; }
                            .print-row { border-bottom: 1px solid #ddd; }
                        }
                    </style>
                </head>
                <body>
                    <div class="print-header">
                        <h2>Associate Registration Details</h2>
                        <p>Generated on: ${new Date().toLocaleString()}</p>
                    </div>

                    <div class="print-section">
                        <div class="print-section-title">Basic Information</div>
                        <div class="print-row">
                            <div class="print-label">Name of Associate/Organization</div>
                            <div class="print-value">${organization_name}</div>
                        </div>
                        <div class="print-row">
                            <div class="print-label">Entity Type</div>
                            <div class="print-value">${entity_type}</div>
                        </div>
                        <div class="print-row">
                            <div class="print-label">Email of Entity</div>
                            <div class="print-value">${entity_email}</div>
                        </div>
                    </div>

                    <div class="print-section">
                        <div class="print-section-title">Contact & Registration Details</div>
                        <div class="print-row">
                            <div class="print-label">Number of Members/Beneficiaries</div>
                            <div class="print-value">${number_of_members}</div>
                        </div>
                        <div class="print-row">
                            <div class="print-label">Registration No</div>
                            <div class="print-value">${registration_number}</div>
                        </div>
                        <div class="print-row">
                            <div class="print-label">Website</div>
                            <div class="print-value">${website}</div>
                        </div>
                        <div class="print-row">
                            <div class="print-label">Contact No</div>
                            <div class="print-value">${contact_number}</div>
                        </div>
                        <div class="print-row">
                            <div class="print-label">PAN/TAN</div>
                            <div class="print-value">${pan_number}</div>
                        </div>
                    </div>

                    <div class="print-section">
                        <div class="print-section-title">Address Details</div>
                        <div class="print-row">
                            <div class="print-label">State</div>
                            <div class="print-value">${state_id}</div>
                        </div>
                        <div class="print-row">
                            <div class="print-label">District</div>
                            <div class="print-value">${district_id}</div>
                        </div>
                        <div class="print-row">
                            <div class="print-label">Complete Address</div>
                            <div class="print-value">${complete_address}</div>
                        </div>
                    </div>

                    <div class="print-section">
                        <div class="print-section-title">Contact Person Details</div>
                        <div class="print-row">
                            <div class="print-label">Contact Person Name</div>
                            <div class="print-value">${contact_person_name}</div>
                        </div>
                        <div class="print-row">
                            <div class="print-label">Designation of Contact Person</div>
                            <div class="print-value">${contact_person_designation}</div>
                        </div>
                        <div class="print-row">
                            <div class="print-label">Contact Person Phone No</div>
                            <div class="print-value">${contact_person_phone}</div>
                        </div>
                        <div class="print-row">
                            <div class="print-label">Contact Person Email</div>
                            <div class="print-value">${contact_person_email}</div>
                        </div>
                    </div>

                    @if(!empty($row['authorization_file_system_name']))
                    <div class="print-section">
                        <div class="print-section-title">Documents</div>
                        <div class="print-document">
                            <strong>Authorization Document:</strong><br>
                            <a href="${document_link}" target="_blank">${document_name}</a>
                        </div>
                    </div>
                    @endif

                    <div class="print-footer">
                        This is a system generated document. 
                        <span class="print-badge">Verified</span>
                    </div>

                    <script>
                        // Auto print
                        window.onload = function() {
                            window.print();
                            // Close window after print
                            window.onafterprint = function() {
                                setTimeout(function() {
                                    window.close();
                                }, 1000);
                            };
                        };
                    <\/script>
                </body>
                </html>
            `);
            
            printWindow.document.close();
        }

        // File upload handler
        $('.upload-pdf').on('change', function() {
            const file = this.files[0];
            if (!file) return;
            var document_category_id = $(this).attr('document_category_id');
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('document_category_id', document_category_id);
            formData.append('file', file);

            $.ajax({
                url: "{{ url('upload-document-public') }}",
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);
                        $('#authorization_document').val(response.data.file_name);
                        $('#authorization_document_id').val(response.data.file_id);
                    }
                },
                error: function(xhr, status, error) {
                    toastr.error('File upload failed.');
                }
            });
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("#formId").on("submit", function(event) {
            event.preventDefault();
            var formData = $("#formId").serializeArray();

            var method = 'POST';
            @isset($id)
                var url = "{{ url('/update-ia-profile/' . $id) }}";
            @else
                var url = "{{ url('/ia-registration') }}";
            @endisset
            var requestData = {
                url: url,
                method: method,
                body: formData,
            };

            sendRequest(requestData, "{{ url('profile') }}");
        });
    </script>
@endsection