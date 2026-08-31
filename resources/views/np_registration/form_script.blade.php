   <script>

    function initTooltip() {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
            if (!bootstrap.Tooltip.getInstance(el)) {
                new bootstrap.Tooltip(el);
            }
        });
    }        
       document.addEventListener('DOMContentLoaded', function() {
           // Handle conditional field visibility
           function handleConditionalFields() {
               document.querySelectorAll('[data-conditional-field]').forEach(function(element) {
                   const fieldName = element.getAttribute('data-conditional-field');
                   const expectedValue = element.getAttribute('data-conditional-value');
                   const field = document.querySelector(`[name="${fieldName}"]`);

                   if (field) {
                       const fieldValue = field.value;
                       const shouldShow = fieldValue === expectedValue;
                       element.style.display = shouldShow ? '' : 'none';

                       // Also hide/show required validation
                       const requiredInputs = element.querySelectorAll('[required]');
                       requiredInputs.forEach(input => {
                           input.required = shouldShow;
                       });
                   }
               });
           }

           // Listen for changes on fields that have dependent fields
           document.querySelectorAll('select, input[type="radio"], input[type="checkbox"]').forEach(function(
               field) {
               field.addEventListener('change', handleConditionalFields);
           });

           // Initial check
           handleConditionalFields();
       });
   </script>
   <script>
       var ONDC_DOMAIN_LIST = @json($domain_type ?? []);
       var ROLE_LIST = @json($roleList ?? []);
       var TRANSACTION_TYPES = @json(static_common_list($lists?->ondc_types ?? []));
       var PRODUCTION_STATUS = @json(static_common_list($lists?->production_status ?? []));
       var SERVICEABILITY_LIST = @json(static_common_list($lists?->serviceability ?? []));
       var STATES_LIST = @json(state_list() ?? []);
       var role_json = @json($roles ?? []);

       // Define the missing function
       function populateDomainDropdown(rowId, response) {
           const $select = $(`#role_row_${rowId} .ondc_category`);
           if (!$select.length) {
               console.error(`Domain dropdown not found for row ${rowId}`);
               return;
           }

           $select.empty().append('<option value="">Select</option>');

           if (ONDC_DOMAIN_LIST && ONDC_DOMAIN_LIST.length > 0) {
               ONDC_DOMAIN_LIST.forEach(item => {
                   const selected = (response.domain == item.id) ? "selected" : "";
                   $select.append(`<option value="${item.id}" ${selected}>${item.name}</option>`);
               });
           }
       }

       function populateRoleDropdown(rowId, response = {}) {
           const $select = $(`#role_row_${rowId} .ondc_role_select`);

           if ($select.length === 0) {
               console.error(`Role dropdown not found for row ${rowId}`);
               return;
           }

           $select.html('<option value="">Select</option>');

           if (ROLE_LIST && ROLE_LIST.length > 0) {
               ROLE_LIST.forEach(role => {
                   const selected = (response.role == role.id) ? "selected" : "";
                   $select.append(`<option value="${role.id}" ${selected}>${role.name}</option>`);
               });
           }
       }

       function populateTransactionTypeDropdown(rowId, response) {
           const $select = $(`#role_row_${rowId} .transaction-type-select`);
           $select.empty().append('<option value="">Select</option>');

           if (TRANSACTION_TYPES) {
               Object.entries(TRANSACTION_TYPES).forEach(([key, value]) => {
                   const selected = (response.transaction_type == key) ? "selected" : "";
                   $select.append(`<option value="${key}" ${selected}>${value}</option>`);
               });
           }
       }

       function populateStatusDropdown(rowId, response) {
           const $select = $(`#role_row_${rowId} .status-select`);
           //    $select.empty().append('<option value="">Select</option>');

           if (PRODUCTION_STATUS) {
               Object.entries(PRODUCTION_STATUS).forEach(([key, value]) => {
                   const selected = (response.status == key) ? "selected" : "";
                   $select.append(`<option value="${key}" ${selected}>${value}</option>`);
               });
           }
       }

       function populateServiceabilityDropdown(rowId, response) {
           const $select = $(`#role_row_${rowId} .serviceability-select`);
           $select.empty().append('<option value="">Select</option>');

           if (SERVICEABILITY_LIST) {
               Object.entries(SERVICEABILITY_LIST).forEach(([key, value]) => {
                   const selected = (response.status == key) ? "selected" : "";
                   $select.append(`<option value="${key}" ${selected}>${value}</option>`);
               });
           }
       }

       function addRoleComponent(response = {}) {

           const randomId = response.random_id || response.id || Date.now();
           const source = $("#role_add_more_template").html();

           if (!source) {
               console.error("Role template not found");
               return;
           }

           Mustache.parse(source);
           const rendered = Mustache.render(source, {
               randomId: randomId,
               response: response
           });

           $("#roleDetailsContainer").append(rendered);
           $(`#domain_${randomId}`).val(response.domain).trigger('change');
           $(`#ondc_domain_mapping_${randomId}`).val(response.ondc_domain_mapping).trigger('change');
           setTimeout(() => {
               $(`#serviceability_${randomId}`)
                   .val(response.serviceability)
                   .trigger('change');
           }, 1000);

           // Call all population functions
           populateDomainDropdown(randomId, response);
           populateRoleDropdown(randomId, response);
           populateTransactionTypeDropdown(randomId, response);
           populateStatusDropdown(randomId, response);
           populateServiceabilityDropdown(randomId, response);

           // Initialize Select2 for multi-select fields
           // $(`#role_row_${randomId}`).select2({
           //     placeholder: "Select states",
           //     allowClear: true
           // });

           updateRoleButtons();
           initTooltip();
       }

       $(document).ready(function() {
           // Initialize Select2 for main roles field
           $('#roles').select2({
               placeholder: "Select Roles",
               allowClear: true
           });

           // Show/hide fee type fields
           $('#fee_type').on('change', function() {
               const val = $(this).val();
               if (val === 'flat_fee') {
                   $('#flat_fee_div').show();
                   $('#subscription_div').hide();
               } else if (val === 'subscription') {
                   $('#subscription_div').show();
                   $('#flat_fee_div').hide();
               } else {
                   $('#flat_fee_div').hide();
                   $('#subscription_div').hide();
               }
           });

           // Clear error messages on input
           $(document).on('focus input change', 'input, textarea, select', function() {
               const fieldId = $(this).attr('id');
               if (fieldId) {
                   $(`#${fieldId}_error`).text('');
               }
           });

           // Initialize role components
           let roleData = []; // ✅ ALWAYS define first

           if (role_json && role_json.length > 0) {

               $.each(role_json, function(i, res) {
                   addRoleComponent(res);
               });

           } else {

               @if (isset($row['role_selection_details']))
                   roleData = @json($row['role_selection_details']);
                   console.log('Role Selection Details:', roleData);
               @endif

               @if (isset($snpData->role_selection_details))
                   roleData = @json($snpData->role_selection_details);
                   console.log('Role Selection Details:', roleData);
               @endif

               if (Array.isArray(roleData) && roleData.length > 0) {

                   let result = roleData.map(role => ({
                       domain: role.domain ?? '',
                       ondc_domain_mapping: role.ondc_domain_mapping ?? '',
                       role: role.role ?? '',
                       serviceability: role.serviceability ?? '',
                       status: role.status ?? '',
                       transaction_type: role.transaction_type ?? ''
                   }));

                   $.each(result, function(key, responseData) {
                       console.log(responseData);
                       addRoleComponent(responseData);
                   });

               } else {
                   // ✅ ADD MODE – empty form
                   addRoleComponent();
               }



           }

           // Handle file uploads
           $('.upload-pdf').on('change', function() {
               const file = this.files[0];
               if (!file) return;

               const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
               if (!allowedTypes.includes(file.type)) {
                   alert('Only PDF, JPG, JPEG, and PNG files are allowed.');
                   $(this).val('');
                   return;
               }

               const maxSize = 2 * 1024 * 1024; // 2MB
               if (file.size > maxSize) {
                   alert('File size should be less than 2MB.');
                   $(this).val('');
                   return;
               }

               const document_category_id = $(this).data('category-id');
               const hidden_file = $(this).data('hidden-file');
               const hidden_id = $(this).data('hidden-id');

               const formData = new FormData();
               formData.append('_token', '{{ csrf_token() }}');
               formData.append('document_category_id', document_category_id);
               formData.append('file', file);

               const $button = $(this);
               $button.prop('disabled', true);

               $.ajax({
                   url: "{{ url('upload-document-public') }}",
                   method: 'POST',
                   data: formData,
                   processData: false,
                   contentType: false,
                   success: function(response) {
                       if (response.status) {
                           toastr.success(response.message);
                           $(hidden_file).val(response.data.file_name);
                           $(hidden_id).val(response.data.file_id);
                       } else {
                           toastr.error(response.message || 'Upload failed');
                           $button.val('');
                       }
                   },
                   error: function(xhr) {
                       toastr.error('File upload failed.');
                       $button.val('');
                   },
                   complete: function() {
                       $button.prop('disabled', false);
                   }
               });
           });

           // Cancel button handler
           $("#cancel-button").on("click", function() {
               if (confirm('Are you sure you want to cancel? All unsaved data will be lost.')) {
                   $("#formId")[0].reset();
                   $('.form-error').text('');
                   // Reset Select2 fields
                   $('#roles').val(null).trigger('change');
                   toastr.info('Form has been reset.');
               }
           });

           // Agreement checkbox handler
           $("#agreecheck").on("change", function() {
               if ($(this).is(":checked")) {
                   $("#agreecheck_error").text("");
               }
           });

           // Form submission
           $("#formId").on("submit", function(event) {
               event.preventDefault();

               if (!validateForm()) {
                   toastr.error('Please fill all required fields correctly');
                   return;
               }

               if (!$("#agreecheck").is(":checked")) {
                   $("#agreecheck_error").text("You must agree to the declaration");
                   toastr.error('Please agree to the declaration');
                   return;
               }

               const formData = new FormData(this);
               formData.append('_token', '{{ csrf_token() }}');
               @isset($row['id'])

                   var url = "{{ url('/update-network-provider') }}";
               @else

                   var url = "{{ url('/register-network-provider') }}";
               @endisset

               $("#cover-spin").show();
               $("#signup-button").prop('disabled', true).text('Submitting...');

               $.ajax({
                   url: url,
                   type: 'POST',
                   data: formData,
                   processData: false,
                   contentType: false,
                   success: function(data) {
                       $("#cover-spin").hide();
                       $("#signup-button").prop('disabled', false).text('Submit');
                       console.log(data);
                       if (data.status) {
                           toastr.success(data.message || 'Registration successful!');
                           setTimeout(function() {
                               window.location.href = "{{ url('login') }}";
                           }, 2000);
                       } else {
                           if (data.errors) {
                               applyValidationErrors(data);
                               toastr.error('Please correct the errors in the form.');

                           } else {
                               toastr.error(data.message || 'Registration failed');
                           }
                       }
                   },
                   error: function(xhr) {
                       console.log(xhr.responseJSON.errors);
                       applyValidationErrors(xhr.responseJSON.errors);
                       $("#cover-spin").hide();
                       $("#signup-button").prop('disabled', false).text('Submit');
                       toastr.error(xhr.responseJSON.message);
                   }
               });
           });

           // Initialize fee type display
           $('#fee_type').trigger('change');
       });

       // Form validation function
       function validateForm() {
           let isValid = true;
           $('.form-error').text(''); // Clear previous errors

           // Check required fields
           $('.required').each(function() {
               const $label = $(this);
               const $field = $label.closest('.mb-0').find('input, select, textarea');
               const fieldId = $field.attr('id');

               if ($field.length && fieldId) {
                   if ($field.is(':checkbox')) {
                       if (!$field.is(':checked')) {
                           $(`#${fieldId}_error`).text("This field is required");
                           isValid = false;
                       }
                   } else if ($field.is('select') && fieldId != 'fee_type') {
                       if ($field.val() === '' || $field.val() === null || $field.val() === undefined) {
                           $(`#${fieldId}_error`).text("This field is required");
                           console.log('id:', fieldId);
                           isValid = false;
                       }
                   } else if ($field.is('input[type="file"]')) {
                       // File fields are optional unless they have the required attribute
                       if ($field.attr('required') && !$field.val()) {
                           $(`#${fieldId}_error`).text("This field is required");
                           console.log('id:', fieldId);
                           isValid = false;
                       }
                   } else {
                       if (!$field.val() || $field.val().trim() === '') {
                           $(`#${fieldId}_error`).text("This field is required");
                           console.log('id:', fieldId);
                           isValid = false;
                       }
                   }
               }
           });

           // Additional checks for fee-related fields if fee charge is 'yes'
           if ($("#fee_charge").val() === "yes") {
               // Check if the fee-related fields are filled
               $(".fee-related-field").each(function() {
                   const $field = $(this);
                   const fieldId = $field.attr('id');
                   if (!$field.val()) {
                       $(`#${fieldId}_error`).text("This field is required when fee charge is yes");
                       isValid = false;
                   }
               });
           }

           // Validate email fields
           $('input[type="text"][id*="email"]').each(function() {
               const email = $(this).val();
               const fieldId = $(this).attr('id');
               if (email && !isValidEmail(email)) {
                   $(`#${fieldId}_error`).text("Please enter a valid email address");
                   isValid = false;
               }
           });

           // Validate phone numbers
           $('input.numeric[id*="phone"], input.numeric[id*="contact"]').each(function() {
               const phone = $(this).val();
               const fieldId = $(this).attr('id');
               if (phone && !isValidPhone(phone)) {
                   $(`#${fieldId}_error`).text("Please enter a valid phone number (10-15 digits)");
                   isValid = false;
               }
           });

           return isValid;
       }


       function isValidEmail(email) {
           const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
           return re.test(email);
       }

       function isValidPhone(phone) {
           const re = /^[0-9]{10,15}$/;
           return re.test(phone);
       }

       function applyValidationErrors(errors) {
           if (errors) {
               $.each(errors, function(field, messages) {
                   const fieldId = field.replace(/\./g, '_') + '_error';
                   $(`#${fieldId}`).text(messages[0]);
               });
           }
       }

       // Role management functions
       $(document).on("click", ".add_more_role", function() {
           addRoleComponent();
       });

       $(document).on("click", ".remove_role_row", function() {
           $(this).closest(".role_row").remove();
           updateRoleButtons();
       });

       function updateRoleButtons() {
           const $rows = $("#roleDetailsContainer .role_row");

           $rows.find(".add_more_role").hide();
           $rows.find(".delete_role_button").show();

           const $firstRow = $rows.first();
           if ($firstRow.length) {
               $firstRow.find(".add_more_role").show();
               $firstRow.find(".delete_role_button").hide();
           }
       }

       $(document).on("change", ".ondc_category", function() {
           const selectedId = $(this).val();
           const $row = $(this).closest('.role_row');
           const $mappingInput = $row.find(".ondc_mapping_input");

           if (!selectedId) {
               $mappingInput.val("");
               return;
           }

           $.ajax({
               url: "{{ url('/get-ondc-domain-id') }}",
               type: "POST",
               data: {
                   id: selectedId,
                   _token: $('meta[name="csrf-token"]').attr("content")
               },
               success: function(res) {
                   if (res.status && res.data && res.data.length > 0) {
                       $mappingInput.val(res.data[0].ondc_domain_id);
                   } else {
                       $mappingInput.val("");
                   }
               },
               error: function() {
                   $mappingInput.val("");
               }
           });
       });


       $("#commercial_model_details_fee_type").on("change", function() {
           var feeType = $(this).val();
           if (feeType === "flat_fee") {
               $(".flat_fee_field").show();
               $(".subscription_field").hide();
           } else if (feeType === "subscription") {
               $(".subscription_field").show();
               $(".flat_fee_field").hide();
           }
       });


       $("#fee_charge").on("change", function() {
           var feeCharge = $(this).val();
           if (feeCharge == 'yes') {
               $("#specialOfferContainer").show().find("input, select").prop("required", true);
               $("#otherFeesContainer").show().find("input, select").prop("required", true);
               $("#commissionPerTransactionContainer").show().find("input, select").prop("required", true);
               $("#feeTypeContainer").show().find("input, select").prop("required", true);
           } else {
               $("#specialOfferContainer").hide().find("input, select").prop("required", false);
               $("#otherFeesContainer").hide().find("input, select").prop("required", false);
               $("#commissionPerTransactionContainer").hide().find("input, select").prop("required", false);
               $("#feeTypeContainer").hide().find("input, select").prop("required", false);
           }
       });


       // Auto-fill required fields before form submission (runs on signup button click)
       // $(document).on('click', '#signup-button', function() {
       //     // Fill label->field required pairs
       //     $('#formId .required').each(function() {
       //         var $label = $(this);
       //         var $field = $label.closest('.mb-0').find('input, select, textarea').first();
       //         if (!$field.length) return;

       //         // skip files
       //         if ($field.is(':file')) return;

       //         var val = $field.val();
       //         if (val !== null && val !== undefined && String(val).trim() !== '') return;

       //         // Selects: pick first non-empty option
       //         if ($field.is('select')) {
       //             var firstOpt = $field.find('option').not('[value=""], option[value=""]').first().val();
       //             if (firstOpt !== undefined) {
       //                 $field.val(firstOpt).trigger('change');
       //             }
       //             return;
       //         }

       //         var id = ($field.attr('id') || '').toLowerCase();
       //         var name = ($field.attr('name') || '').toLowerCase();

       //         // Heuristic defaults
       //         if (id.includes('email') || name.includes('email')) {
       //             $field.val('test@example.com');
       //         } else if (id.match(/phone|contact|mobile/) || name.match(/phone|contact|mobile/)) {
       //             $field.val('9999999999');
       //         } else if (id.match(/organization_id|account_number|flat_fee_amount|other_fees/) || name
       //             .match(/organization_id|account_number|flat_fee_amount|other_fees/)) {
       //             $field.val('123456');
       //         } else {
       //             $field.val('Sample Value');
       //         }
       //     });

       //     // Ensure main roles select has a value
       //     if ($('#roles').length && (!$('#roles').val() || $('#roles').val().length === 0)) {
       //         var firstRole = $('#roles option').not('[value=""], option[value=""]').first().val();
       //         if (firstRole !== undefined) {
       //             $('#roles').val([firstRole]).trigger('change');
       //         }
       //     }

       //     // Populate dynamic role rows (choose first option for each select, set serviceability)
       //     $('#roleDetailsContainer .role_row').each(function() {
       //         var $row = $(this);
       //         $row.find('select').each(function() {
       //             var $s = $(this);
       //             if (!$s.val() || $s.val() === '') {
       //                 var first = $s.find('option').not('[value=""], option[value=""]').first()
       //                     .val();
       //                 if (first !== undefined) {
       //                     $s.val(first).trigger('change');
       //                 }
       //             }
       //         });

       //         var $service = $row.find('.serviceability-select');
       //         if ($service.length && (!$service.val() || $service.val().length === 0)) {
       //             var firstState = $service.find('option').first().val();
       //             if (firstState !== undefined) {
       //                 $service.val([firstState]).trigger('change');
       //             }
       //         }
       //     });
       // });
   </script>
   <script id="role_add_more_template" type="x-tmpl-mustache">
    <div class="role_row card p-3 mb-3" id="role_row_@{{randomId}}" style="border:1px solid #ddd; border-radius:8px;">
        <div class="row">
            <div class="col-md-4">
            <div class="d-flex gap-3">
                <label class="form-label required">Domain / Product Type</label>
                <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Select business domain.">
                        <i class="fa fa-question-circle" aria-hidden="true"></i></a>
            </div>
                <select name="role_selection_details[@{{randomId}}][domain]" 
                        class="form-select ondc_category" id="domain_@{{randomId}}"
                        data-row-id="@{{randomId}}">
                    <option value="">Select</option>
                </select>
            </div>

            <div class="col-md-4">
                <div class="d-flex gap-3">
                    <label class="form-label required">ONDC Domain Mapping</label>
                    <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Map with ONDC domain.">
                        <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                </div>
                <input type="text" 
                    name="role_selection_details[@{{randomId}}][ondc_domain_mapping]" 
                    class="form-control ondc_mapping_input" readonly id="ondc_domain_mapping_@{{randomId}}"
                    value="@{{response.ondc_domain_mapping}}">
            </div>

            <div class="col-md-4">
                <div class="d-flex gap-3">
                    <label class="form-label required">Transaction Type</label>
                    <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Select transaction type.">
                    <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                </div>
                <select name="role_selection_details[@{{randomId}}][transaction_type]" class="form-select transaction-type-select">
                    <option value="">Select</option>
                </select>
            </div>

            <div class="col-md-4 mt-3">
                <div class="d-flex gap-3">
                    <label class="form-label required">Role</label>
                    <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Select role.">
                        <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                </div>
                <select name="role_selection_details[@{{randomId}}][role]"
                        class="form-select ondc_role_select"
                        data-row-id="@{{randomId}}">
                    <option value="">Select</option>
                </select>
            </div>

            <div class="col-md-4 mt-3">
                <div class="d-flex gap-3">
                    <label class="form-label required">Status</label>
                    <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Select status (Live/Test).">
                            <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                </div>
                <select name="role_selection_details[@{{randomId}}][status]" class="form-select status-select">
                    
                </select>
            </div>

            <div class="col-md-4 mt-3">
                <div class="d-flex gap-3">
                    <label class="form-label required">Serviceability</label>
                    <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Select service area.">
                        <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                </div>
                <select name="role_selection_details[@{{randomId}}][serviceability]" id="serviceability_@{{randomId}}"
                        class="form-select serviceability-select">
                </select>
            </div>

            <div class="col-md-12 mt-3 d-flex justify-content-end">
                <a class="add_more_role btn btn-outline-success btn-sm me-2" 
                href="javascript:void(0)">Add</a>

                <a class="remove_role_row btn btn-outline-danger btn-sm delete_role_button"
                    data-id="@{{randomId}}"
                    href="javascript:void(0)"
                    style="display:none;">Remove</a>
            </div>
        </div>
    </div>
</script>
