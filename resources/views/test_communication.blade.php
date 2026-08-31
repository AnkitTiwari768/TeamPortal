@extends('components.admin.layout')

@section('page-content')
<style>
    .test-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        background: #ffffff;
        transition: transform 0.2s;
    }
    .nav-tabs-custom {
        border-bottom: 2px solid #e9ecef;
    }
    .nav-tabs-custom .nav-link {
        border: none;
        color: #6c757d;
        font-weight: 600;
        padding: 12px 24px;
        position: relative;
        transition: color 0.3s;
    }
    .nav-tabs-custom .nav-link.active {
        color: #0d6efd;
        background: transparent;
    }
    .nav-tabs-custom .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 3px;
        background-color: #0d6efd;
        border-radius: 3px;
    }
    .nav-tabs-custom .nav-link:hover:not(.active) {
        color: #495057;
    }
    .required::after {
        content: " *";
        color: #dc3545;
        font-weight: bold;
    }
    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 6px;
    }
    .form-control, .form-select {
        border-radius: 8px;
        padding: 10px 14px;
        border: 1px solid #ced4da;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    .form-control:focus, .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
    }
    .btn-send {
        border-radius: 8px;
        padding: 10px 24px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }
    .btn-send:active {
        transform: scale(0.96);
    }
    .template-preview-box {
        background-color: #f8f9fa;
        border: 1px dashed #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }
    .badge-info-custom {
        background-color: #e2f0fe;
        color: #0d6efd;
        font-weight: 500;
        font-size: 0.85rem;
        padding: 6px 12px;
        border-radius: 6px;
    }
    /* Full width modifications */
    .full-width-container {
        width: 100%;
        max-width: 100%;
        padding-left: 0;
        padding-right: 0;
        margin: 0;
    }
    .full-width-card {
        width: 100%;
        border-radius: 0;
    }
    .container-fluid.px-4.py-4 {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    .row.justify-content-center {
        margin-left: 0;
        margin-right: 0;
    }
    .col-xl-9.col-lg-10 {
        max-width: 100%;
        flex: 0 0 100%;
        padding-left: 0;
        padding-right: 0;
    }
    .container-main-card {
        margin: 0;
        border-radius: 0;
        box-shadow: none;
        border-bottom: 1px solid #e9ecef;
    }
    .card-header {
        padding: 1.5rem 2rem !important;
    }
    .card-body.p-4 {
        padding: 2rem !important;
    }
    .px-4 {
        padding-left: 2rem !important;
        padding-right: 2rem !important;
    }
    /* Loading animation for buttons */
    .btn-loading {
        opacity: 0.7;
        cursor: wait;
    }
    /* Success/Error highlight animation */
    @keyframes highlightFlash {
        0% { background-color: #ffffff; border-color: #ced4da; }
        30% { background-color: #d4edda; border-color: #28a745; }
        100% { background-color: #ffffff; border-color: #ced4da; }
    }
    .success-highlight {
        animation: highlightFlash 0.8s ease;
    }
    @keyframes errorFlash {
        0% { background-color: #ffffff; border-color: #ced4da; }
        30% { background-color: #f8d7da; border-color: #dc3545; }
        100% { background-color: #ffffff; border-color: #ced4da; }
    }
    .error-highlight {
        animation: errorFlash 0.8s ease;
    }
    /* Card success animation */
    @keyframes cardSuccess {
        0% { box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); }
        50% { box-shadow: 0 4px 30px rgba(40, 167, 69, 0.3); border-left: 4px solid #28a745; }
        100% { box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); }
    }
    @keyframes cardError {
        0% { box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); }
        50% { box-shadow: 0 4px 30px rgba(220, 53, 69, 0.3); border-left: 4px solid #dc3545; }
        100% { box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); }
    }
    .card-success-animation {
        animation: cardSuccess 0.8s ease;
    }
    .card-error-animation {
        animation: cardError 0.8s ease;
    }
    /* Responsive */
    @media (max-width: 768px) {
        .card-header {
            padding: 1rem !important;
        }
        .card-body.p-4 {
            padding: 1rem !important;
        }
        .px-4 {
            padding-left: 1rem !important;
            padding-right: 1rem !important;
        }
        .nav-tabs-custom .nav-link {
            padding: 8px 12px;
            font-size: 0.9rem;
        }
        .d-flex.gap-4 {
            flex-wrap: wrap;
            gap: 1rem !important;
        }
        .text-end {
            text-align: center !important;
        }
        .btn-send {
            width: 100%;
            justify-content: center;
        }
    }
    /* Reset button style */
    .btn-reset {
        border-radius: 8px;
        padding: 10px 20px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-right: 12px;
        background-color: #6c757d;
        border-color: #6c757d;
        color: white;
        transition: all 0.3s ease;
    }
    .btn-reset:hover {
        background-color: #5a6268;
        transform: translateY(-1px);
    }
    .action-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        flex-wrap: wrap;
    }
    /* Toast customization */
    .toast-success {
        background-color: #28a745 !important;
    }
    .toast-error {
        background-color: #dc3545 !important;
    }
</style>

<div class="container-fluid px-4 py-4 full-width-container">
    <div class="row justify-content-center">
        <div class="col-xl-9 col-lg-10">
            <div class="card test-card container-main-card full-width-card" id="mainCard">
                <!-- HEADER -->
                <div class="card-header d-flex align-items-center justify-content-between bg-white border-0 py-3 px-4">
                    <div>
                        <h4 class="mb-1 fw-bold text-dark">{{ $title }}</h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Communication Test</li>
                            </ol>
                        </nav>
                    </div>
                </div>

                <!-- TABS NAVIGATION -->
                <div class="px-4">
                    <ul class="nav nav-tabs nav-tabs-custom" id="communicationTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="email-tab" data-bs-toggle="tab" data-bs-target="#email-pane" type="button" role="tab" aria-controls="email-pane" aria-selected="true">
                                <i class="fa fa-envelope me-2"></i> Email Test
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="sms-tab" data-bs-toggle="tab" data-bs-target="#sms-pane" type="button" role="tab" aria-controls="sms-pane" aria-selected="false">
                                <i class="fa fa-comment me-2"></i> SMS Test
                            </button>
                        </li>
                    </ul>
                </div>

                <!-- TAB PANES -->
                <div class="card-body p-4">
                    <div class="tab-content" id="communicationTabsContent">
                        
                        <!-- EMAIL TEST PANE -->
                        <div class="tab-pane fade show active" id="email-pane" role="tabpanel" aria-labelledby="email-tab">
                            <form id="emailTestForm">
                                <div class="mb-4">
                                    <label for="to_email" class="form-label required">Recipient Email ID</label>
                                    <input type="email" class="form-control" id="to_email" name="to_email" placeholder="enter recipient email address (e.g. user@example.com)" required>
                                    <small class="text-muted">Enter a valid email address to receive test email</small>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label required">Email Mode</label>
                                    <div class="d-flex gap-4 flex-wrap">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="email_mode" id="email_mode_simple" value="simple" checked>
                                            <label class="form-check-label font-weight-normal" for="email_mode_simple">
                                                <i class="fa fa-file-text me-1"></i> Simple Test Email
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="email_mode" id="email_mode_custom" value="custom">
                                            <label class="form-check-label font-weight-normal" for="email_mode_custom">
                                                <i class="fa fa-pencil-alt me-1"></i> Custom Subject & Body
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="email_mode" id="email_mode_template" value="template">
                                            <label class="form-check-label font-weight-normal" for="email_mode_template">
                                                <i class="fa fa-database me-1"></i> Template Email
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- SIMPLE EMAIL FIELDS -->
                                <div id="email_simple_fields">
                                    <div class="mb-3">
                                        <label for="email_simple_subject" class="form-label required">Subject</label>
                                        <input type="text" class="form-control" id="email_simple_subject" name="subject_simple" value="Utility Communication Test Email">
                                    </div>
                                    <div class="mb-3">
                                        <label for="email_simple_body" class="form-label required">Body Content</label>
                                        <textarea class="form-control" id="email_simple_body" name="body_simple" rows="4">This is a test email sent to verify the SMTP configuration and ensure successful email delivery.</textarea>
                                    </div>
                                </div>

                                <!-- CUSTOM EMAIL FIELDS -->
                                <div id="email_custom_fields" style="display: none;">
                                    <div class="mb-3">
                                        <label for="email_custom_subject" class="form-label required">Subject</label>
                                        <input type="text" class="form-control" id="email_custom_subject" name="subject_custom" placeholder="Enter custom subject">
                                    </div>
                                    <div class="mb-3">
                                        <label for="email_custom_body" class="form-label required">HTML / Text Body</label>
                                        <textarea class="form-control" id="email_custom_body" name="body_custom" rows="6" placeholder="Enter custom HTML or plain text body"></textarea>
                                    </div>
                                </div>

                                <!-- TEMPLATE EMAIL FIELDS -->
                                <div id="email_template_fields" style="display: none;">
                                    <div class="mb-3">
                                        <label for="email_template_select" class="form-label required">Select Email Template</label>
                                        <select class="form-select" id="email_template_select" name="template_key">
                                            <option value="">-- Choose Template --</option>
                                            @foreach($emailTemplates as $tpl)
                                                <option value="{{ $tpl->template_key }}" data-vars="{{ json_encode($tpl->variables) }}" data-subject="{{ $tpl->subject }}">
                                                    {{ $tpl->subject }} ({{ $tpl->template_key }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div id="email_tpl_preview" class="template-preview-box" style="display: none;">
                                        <div class="mb-2"><strong>Template Key:</strong> <span id="email_tpl_key_lbl" class="text-secondary"></span></div>
                                        <div><strong>Default Subject:</strong> <span id="email_tpl_subject_lbl" class="text-secondary"></span></div>
                                    </div>

                                    <div id="email_template_variables_container" class="mb-4">
                                        <!-- Dynamic inputs for template variables will be loaded here -->
                                    </div>
                                </div>

                                <div class="action-buttons mt-4">
                                    <button type="button" class="btn btn-reset" id="resetEmailForm">
                                        <i class="fa fa-undo-alt"></i> Reset Form
                                    </button>
                                    <button type="submit" class="btn btn-primary btn-send" id="btnSendEmail">
                                        <i class="fa fa-paper-plane"></i> Send Test Email
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- SMS TEST PANE -->
                       <!-- SMS TEST PANE -->
<div class="tab-pane fade" id="sms-pane" role="tabpanel" aria-labelledby="sms-tab">
    <form id="smsTestForm">
        <div class="mb-4">
            <label for="mobile" class="form-label required">Recipient Mobile Number</label>
            <input type="text" class="form-control" id="mobile" name="mobile" placeholder="enter 10-digit mobile number (e.g. 9876543210)" required>
            <small class="text-muted">Enter 10-digit mobile number with country code</small>
        </div>

        <div class="mb-4">
            <label class="form-label required">SMS Mode</label>
            <div class="d-flex gap-4 flex-wrap">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="sms_mode" id="sms_mode_simple" value="simple" checked>
                    <label class="form-check-label font-weight-normal" for="sms_mode_simple">
                        <i class="fa fa-paper-plane me-1"></i> Simple Test SMS
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="sms_mode" id="sms_mode_template" value="template">
                    <label class="form-check-label font-weight-normal" for="sms_mode_template">
                        <i class="fa fa-database me-1"></i> Template SMS (from DB)
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="sms_mode" id="sms_mode_custom" value="custom">
                    <label class="form-check-label font-weight-normal" for="sms_mode_custom">
                        <i class="fa fa-cog me-1"></i> Custom DLT SMS
                    </label>
                </div>
            </div>
        </div>

        <!-- SIMPLE SMS FIELDS -->
        <div id="sms_simple_fields">
            <div class="mb-3">
                <label for="sms_simple_message" class="form-label required">Message Content</label>
                <textarea class="form-control" id="sms_simple_message" name="message" rows="4" placeholder="Enter your test message here">This is a test SMS sent from the testing utility dashboard.</textarea>
                <small class="text-muted">This will use the default DLT entity and template ID for testing</small>
            </div>
        </div>

        <!-- TEMPLATE SMS FIELDS -->
        <div id="sms_template_fields" style="display: none;">
            <div class="mb-3">
                <label for="sms_template_select" class="form-label required">Select SMS Template</label>
                <select class="form-select" id="sms_template_select" name="template_name">
                    <option value="">-- Choose Template --</option>
                    @foreach($smsTemplates as $tpl)
                        <option value="{{ $tpl->template_name }}" 
                            data-content="{{ $tpl->template_content }}" 
                            data-entity="{{ $tpl->entity_id }}" 
                            data-template="{{ $tpl->template_id }}"
                            data-varcount="{{ $tpl->template_variables }}">
                            {{ $tpl->template_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div id="sms_tpl_preview" class="template-preview-box" style="display: none;">
                <div class="mb-2"><strong>DLT Principal Entity ID:</strong> <span id="sms_tpl_entity_lbl" class="badge-info-custom"></span></div>
                <div class="mb-2"><strong>DLT Template ID:</strong> <span id="sms_tpl_id_lbl" class="badge-info-custom"></span></div>
                <div><strong>Message Content:</strong> <div id="sms_tpl_content_lbl" class="text-secondary mt-1 p-2 bg-white rounded border small"></div></div>
            </div>

            <div id="sms_template_variables_container" class="mb-4">
                <!-- Dynamic inputs for template variables will be loaded here -->
            </div>
        </div>

        <!-- CUSTOM SMS FIELDS -->
        <div id="sms_custom_fields" style="display: none;">
            <div class="row mb-3">
                <div class="col-md-6 mb-3">
                    <label for="entity_id" class="form-label required">DLT Principal Entity ID</label>
                    <input type="text" class="form-control" id="entity_id" name="entity_id" placeholder="Enter DLT Entity ID">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="template_id" class="form-label required">DLT Template ID</label>
                    <input type="text" class="form-control" id="template_id" name="template_id" placeholder="Enter DLT Template ID">
                </div>
            </div>
            <div class="mb-3">
                <label for="sms_custom_message" class="form-label required">Message Content</label>
                <textarea class="form-control" id="sms_custom_message" name="message" rows="4" placeholder="Enter the exact SMS content that matches your DLT Template"></textarea>
            </div>
        </div>

        <div class="action-buttons mt-4">
            <button type="button" class="btn btn-reset" id="resetSmsForm">
                <i class="fa fa-undo-alt"></i> Reset Form
            </button>
            <button type="submit" class="btn btn-primary btn-send" id="btnSendSms">
                <i class="fa fa-paper-plane"></i> Send Test SMS
            </button>
        </div>
    </form>
</div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>

// Function to completely reset email form
function resetEmailFormCompletely() {
    // Reset all form fields
    $('#emailTestForm')[0].reset();
    
    // Reset to simple mode
    $('#email_simple_fields').show();
    $('#email_custom_fields, #email_template_fields').hide();
    $('input[name="email_mode"][value="simple"]').prop('checked', true);
    
    // Reset template select and preview
    $('#email_template_select').val('');
    $('#email_tpl_preview').hide();
    $('#email_template_variables_container').empty();
    
    // Set default values for simple mode
    $('#email_simple_subject').val('Utility Communication Test Email');
    $('#email_simple_body').val('This is a test email sent from the testing utility dashboard to verify SMTP settings.');
    
    // Clear custom fields
    $('#email_custom_subject').val('');
    $('#email_custom_body').val('');
    
    // Remove any highlights
    $('#to_email').removeClass('success-highlight error-highlight');
    
    console.log('Email form reset complete');
}

// Function to completely reset SMS form
// Function to completely reset SMS form
function resetSmsFormCompletely() {
    // Reset all form fields
    $('#smsTestForm')[0].reset();
    
    // Reset to simple mode
    $('#sms_simple_fields').show();
    $('#sms_template_fields, #sms_custom_fields').hide();
    $('input[name="sms_mode"][value="simple"]').prop('checked', true);
    
    // Reset template select and preview
    $('#sms_template_select').val('');
    $('#sms_tpl_preview').hide();
    $('#sms_template_variables_container').empty();
    
    // Set default simple message
    $('#sms_simple_message').val('This is a test SMS sent from the testing utility dashboard.');
    
    // Clear custom fields
    $('#entity_id').val('');
    $('#template_id').val('');
    $('#sms_custom_message').val('');
    
    // Remove any highlights
    $('#mobile').removeClass('success-highlight error-highlight');
    
    console.log('SMS form reset complete');
}

// Highlight field with animation
function highlightField(element, type) {
    element.addClass(type === 'success' ? 'success-highlight' : 'error-highlight');
    setTimeout(function() {
        element.removeClass(type === 'success' ? 'success-highlight' : 'error-highlight');
    }, 800);
}

// Animate card on success/error
function animateCard(type) {
    const card = $('#mainCard');
    card.addClass(type === 'success' ? 'card-success-animation' : 'card-error-animation');
    setTimeout(function() {
        card.removeClass(type === 'success' ? 'card-success-animation' : 'card-error-animation');
    }, 800);
}

// Show success message and reset form
function handleSuccess(response, formType, resetFunction) {
    animateCard('success');
    
    if (response.status) {
        // Show success toast
        toastr.success(response.message, 'Success!', {
            closeButton: true,
            progressBar: true,
            timeOut: 3000
        });
        
        // Show SweetAlert
        Swal.fire({
            icon: 'success',
            title: formType === 'email' ? 'Email Sent Successfully!' : 'SMS Sent Successfully!',
            text: response.message,
            timer: 2000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
        
        // COMPLETELY RESET THE FORM
        resetFunction();
        
        return true;
    }
    return false;
}

// Show error message
function handleError(error, formType, resetFunction) {
    animateCard('error');
    
    let errorMessage = 'An unexpected error occurred.';
    if (error && error.message) {
        errorMessage = error.message;
    } else if (typeof error === 'string') {
        errorMessage = error;
    }
    
    toastr.error(errorMessage, 'Error!', {
        closeButton: true,
        progressBar: true,
        timeOut: 5000
    });
    
    Swal.fire({
        icon: 'error',
        title: formType === 'email' ? 'Email Failed!' : 'SMS Failed!',
        text: errorMessage,
        confirmButtonText: 'Try Again',
        confirmButtonColor: '#0d6efd'
    });
}

$(document).ready(function() {
    
    // Reset button handlers
    $('#resetEmailForm').on('click', function() {
        resetEmailFormCompletely();
        toastr.info('Email form has been reset', 'Reset', { timeOut: 2000 });
    });
    
    $('#resetSmsForm').on('click', function() {
        resetSmsFormCompletely();
        toastr.info('SMS form has been reset', 'Reset', { timeOut: 2000 });
    });
    
    // ==========================================
    // EMAIL TAB LOGIC
    // ==========================================
    
    // Toggle Email Mode Fields
    $('input[name="email_mode"]').on('change', function() {
        let mode = $(this).val();
        $('#email_simple_fields, #email_custom_fields, #email_template_fields').hide();
        
        if (mode === 'simple') {
            $('#email_simple_fields').show();
        } else if (mode === 'custom') {
            $('#email_custom_fields').show();
        } else if (mode === 'template') {
            $('#email_template_fields').show();
            $('#email_template_select').trigger('change');
        }
    });

    // Handle Email Template Selection
    $('#email_template_select').on('change', function() {
        let selectedOption = $(this).find('option:selected');
        let templateKey = selectedOption.val();
        let container = $('#email_template_variables_container');
        container.empty();

        if (!templateKey) {
            $('#email_tpl_preview').hide();
            return;
        }

        let subject = selectedOption.attr('data-subject');
        let varsJson = selectedOption.attr('data-vars');
        
        $('#email_tpl_key_lbl').text(templateKey);
        $('#email_tpl_subject_lbl').text(subject);
        $('#email_tpl_preview').show();

        try {
            let variables = JSON.parse(varsJson);
            if (variables && Object.keys(variables).length > 0) {
                container.append('<h5 class="fw-bold mb-3 mt-2 text-dark"><i class="fa fa-code-branch me-2"></i>Template Variables</h5>');
                let rowHtml = '<div class="row">';
                for (let key in variables) {
                    rowHtml += `
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">${key}</label>
                            <input type="text" class="form-control tpl-email-var" data-varname="${key}" placeholder="Enter value for ${key}" required>
                        </div>
                    `;
                }
                rowHtml += '</div>';
                container.append(rowHtml);
            } else {
                container.append('<div class="alert alert-light border small text-muted"><i class="fa fa-info-circle me-1"></i>This template has no variable placeholders.</div>');
            }
        } catch(e) {
            console.error('Error parsing email template variables', e);
        }
    });

    // Handle Email Form Submission
    $('#emailTestForm').on('submit', function(e) {
        e.preventDefault();
        
        let toEmail = $('#to_email').val().trim();
        if (!toEmail) {
            highlightField($('#to_email'), 'error');
            toastr.error('Please enter recipient email address', 'Validation Error');
            return;
        }
        
        let emailMode = $('input[name="email_mode"]:checked').val();
        let subject = '';
        let body = '';
        let templateKey = '';
        let variables = {};

        if (emailMode === 'simple') {
            subject = $('#email_simple_subject').val();
            body = $('#email_simple_body').val();
        } else if (emailMode === 'custom') {
            subject = $('#email_custom_subject').val().trim();
            body = $('#email_custom_body').val().trim();
            if (!subject || !body) {
                toastr.error('Please fill both subject and body for custom email', 'Validation Error');
                highlightField($('#email_custom_subject'), 'error');
                return;
            }
        } else if (emailMode === 'template') {
            templateKey = $('#email_template_select').val();
            if (!templateKey) {
                toastr.error('Please select an email template', 'Validation Error');
                return;
            }
            $('.tpl-email-var').each(function() {
                let key = $(this).attr('data-varname');
                let val = $(this).val();
                variables[key] = val;
            });
        }

        let btn = $('#btnSendEmail');
        let originalHtml = btn.html();
        btn.prop('disabled', true).addClass('btn-loading').html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending...');

        $.ajax({
            url: "{{ route('web.test-email-test') }}",
            type: "POST",
            data: {
                to_email: toEmail,
                email_mode: emailMode,
                subject: subject,
                body: body,
                template_key: templateKey,
                variables: variables,
                _token: "{{ csrf_token() }}"
            },
            success: function(res) {
                btn.prop('disabled', false).removeClass('btn-loading').html(originalHtml);
                highlightField($('#to_email'), 'success');
                
                if (res.status) {
                    handleSuccess(res, 'email', resetEmailFormCompletely);
                } else {
                    handleError(res, 'email', resetEmailFormCompletely);
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).removeClass('btn-loading').html(originalHtml);
                highlightField($('#to_email'), 'error');
                
                let errorMsg = 'An error occurred while sending the email.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                }
                handleError({ message: errorMsg }, 'email', resetEmailFormCompletely);
            }
        });
    });


    // ==========================================
    // SMS TAB LOGIC
    // ==========================================

   // ==========================================
// SMS TAB LOGIC
// ==========================================

// Toggle SMS Mode Fields
$('input[name="sms_mode"]').on('change', function() {
    let mode = $(this).val();
    $('#sms_simple_fields, #sms_template_fields, #sms_custom_fields').hide();
    
    if (mode === 'simple') {
        $('#sms_simple_fields').show();
    } else if (mode === 'template') {
        $('#sms_template_fields').show();
        $('#sms_template_select').trigger('change');
    } else if (mode === 'custom') {
        $('#sms_custom_fields').show();
    }
});

// Update SMS template selection handler
$('#sms_template_select').on('change', function() {
    let selectedOption = $(this).find('option:selected');
    let templateName = selectedOption.val();
    let container = $('#sms_template_variables_container');
    container.empty();

    if (!templateName) {
        $('#sms_tpl_preview').hide();
        return;
    }

    let content = selectedOption.attr('data-content');
    let entityId = selectedOption.attr('data-entity');
    let templateId = selectedOption.attr('data-template');
    let varCount = parseInt(selectedOption.attr('data-varcount')) || 0;

    $('#sms_tpl_entity_lbl').text(entityId || 'N/A');
    $('#sms_tpl_id_lbl').text(templateId || 'N/A');
    $('#sms_tpl_content_lbl').text(content || 'No content available');
    $('#sms_tpl_preview').show();

    if (varCount > 0) {
        container.append('<h5 class="fw-bold mb-3 mt-2 text-dark"><i class="fa fa-tags me-2"></i>Template Placeholders</h5>');
        let rowHtml = '<div class="row">';
        for (let i = 1; i <= varCount; i++) {
            rowHtml += `
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Placeholder {#var#} ${i}</label>
                    <input type="text" class="form-control tpl-sms-var" data-index="${i-1}" placeholder="Value for variable ${i}" required>
                </div>
            `;
        }
        rowHtml += '</div>';
        container.append(rowHtml);
    } else {
        container.append('<div class="alert alert-light border small text-muted"><i class="fa fa-info-circle me-1"></i>This template has no dynamic {#var#} placeholders.</div>');
    }
});

// SMS Form Submission Handler
$('#smsTestForm').on('submit', function(e) {
    e.preventDefault();

    let mobile = $('#mobile').val().trim();
    if (!mobile) {
        highlightField($('#mobile'), 'error');
        toastr.error('Please enter mobile number', 'Validation Error');
        return;
    }
    
    let smsMode = $('input[name="sms_mode"]:checked').val();
    let message = '';
    let entityId = '';
    let templateId = '';
    let templateName = '';
    let variables = [];

    if (smsMode === 'simple') {
        message = $('#sms_simple_message').val().trim();
        if (!message) {
            toastr.error('Please enter a message for simple SMS', 'Validation Error');
            highlightField($('#sms_simple_message'), 'error');
            return;
        }
    } else if (smsMode === 'custom') {
        message = $('#sms_custom_message').val().trim();
        entityId = $('#entity_id').val().trim();
        templateId = $('#template_id').val().trim();
        if (!message || !entityId || !templateId) {
            toastr.error('Please fill all custom DLT fields (Entity ID, Template ID, and Message)', 'Validation Error');
            return;
        }
    } else if (smsMode === 'template') {
        templateName = $('#sms_template_select').val();
        if (!templateName) {
            toastr.error('Please select an SMS template', 'Validation Error');
            return;
        }
        let varsArray = [];
        $('.tpl-sms-var').each(function() {
            let idx = parseInt($(this).attr('data-index'));
            varsArray[idx] = $(this).val();
        });
        variables = varsArray;
    }

    let btn = $('#btnSendSms');
    let originalHtml = btn.html();
    btn.prop('disabled', true).addClass('btn-loading').html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending...');

    $.ajax({
        url: "{{ route('web.test-sms-test') }}",
        type: "POST",
        data: {
            mobile: mobile,
            sms_mode: smsMode,
            message: message,
            entity_id: entityId,
            template_id: templateId,
            template_name: templateName,
            variables: variables,
            _token: "{{ csrf_token() }}"
        },
        success: function(res) {
            btn.prop('disabled', false).removeClass('btn-loading').html(originalHtml);
            highlightField($('#mobile'), 'success');
            
            if (res.status) {
                handleSuccess(res, 'sms', resetSmsFormCompletely);
            } else {
                handleError(res, 'sms', resetSmsFormCompletely);
            }
        },
        error: function(xhr) {
            btn.prop('disabled', false).removeClass('btn-loading').html(originalHtml);
            highlightField($('#mobile'), 'error');
            
            let errorMsg = 'An error occurred while sending the SMS.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            } else if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMsg = xhr.responseJSON.error;
            }
            handleError({ message: errorMsg }, 'sms', resetSmsFormCompletely);
        }
    });
});
});
</script>
@endsection