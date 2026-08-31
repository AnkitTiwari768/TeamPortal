/**
 * allocation.js
 *
 * Uses: jQuery, Mustache.js, toastr, api.js
 * All cascade dropdowns go through ONE endpoint:
 *   GET /web/api/allocation/attribute-values/{codeOrId}?parent_id=X
 */

/* ============================================================
   TOTAL AMOUNT CALCULATOR
============================================================ */
function allocRecalcTotal() {
    var total = 0;
    $('input.alloc-amount-input').each(function () {
        var val = parseFloat($(this).val().replace(/,/g, ''));
        if (!isNaN(val) && val >= 0) total += val;
    });
    $('#alloc-total-hidden').val(total.toFixed(2));
}

/* ============================================================
   DUPLICATE ROW DETECTION
============================================================ */
function allocHasDuplicateRow() {
    var seen = {};
    var duplicate = false;
    $('.alloc-row-item').each(function () {
        var major = $(this).find('.alloc-major-component').val() || '';
        var sub = $(this).find('.alloc-sub-component').val() || '';
        if (!major) return;
        var key = major + '|' + sub;
        if (seen[key]) { duplicate = true; return false; }
        seen[key] = true;
    });
    return duplicate;
}

/* ============================================================
   GENERIC ATTRIBUTE VALUE LOADER
   Single function for all three cascade levels.
   All calls go through api.js -> apiRequest().

   window.allocAttrCodes must be set in the Blade view:
     window.allocAttrCodes = {
       majorComponent : config('allocation.major_component_code'),
       component      : config('allocation.component_code'),
       subComponent   : config('allocation.sub_component_code'),
     };
============================================================ */
function allocLoadAttributeValues(attrCode, parentId, selectId, selectedVal, callback) {
    var $sel = $('#' + selectId);
    $sel.prop('disabled', true).html('<option value="">Loading...</option>');

    var params = {};
    if (parentId) params.parent_id = parentId;

    apiRequest({
        url: window.allocBaseUrl + '/web/api/allocation/attribute-values/' + encodeURIComponent(attrCode),
        method: 'GET',
        params: params
    }).then(function (result) {
        var items = Array.isArray(result) ? result : (result.data || []);
        $sel.html('<option value="">-- Select --</option>');
        $.each(items, function (i, item) {
            var label = item.name || item.attribute_value || '';
            var sel = selectedVal && String(item.id) === String(selectedVal) ? ' selected' : '';
            $sel.append('<option value="' + item.id + '"' + sel + '>' + label + '</option>');
        });
        $sel.prop('disabled', false);
        if (typeof callback === 'function') callback();
    }).catch(function () {
        $sel.html('<option value="">-- Error loading --</option>').prop('disabled', false);
    });
}

/* Cascade level 2: Major Component -> Sub-Component */
function allocLoadSubComponents(majorId, rowId, selectedSubId) {
    allocLoadAttributeValues(
        window.allocAttrCodes.subComponent,
        majorId,
        'alloc-sub-' + rowId,
        selectedSubId
    );
}

/* ============================================================
   ADD A ROW using Mustache template
============================================================ */
var allocRowCounter = 0;

function allocAddRow(editMode, lineData) {
    var rowId = Date.now() + (++allocRowCounter);
    lineData = lineData || {};

    var source = $('#alloc_row_template').html();
    Mustache.parse(source);
    var rendered = Mustache.render(source, {
        rowId: rowId,
        lineId: lineData.id || '',
        amount: lineData.amount || '',
    });

    $('#alloc-rows-body').append(rendered);

    // Populate major component dropdown
    var $majorSel = $('#alloc-major-' + rowId);
    if ($majorSel.find('option').length <= 1) {
        allocLoadAttributeValues(
            window.allocAttrCodes.majorComponent,
            null,
            'alloc-major-' + rowId,
            lineData.major_component_id
        );
    }

    // Pre-fill cascade for edit mode
    if (lineData.major_component_id) {
        // Wait for major to load then cascade
        setTimeout(function () {
            if (lineData.sub_component_id) {
                allocLoadSubComponents(lineData.major_component_id, rowId, lineData.sub_component_id);
            }
        }, 600);
    }

    allocRecalcTotal();
}

/* ============================================================
   REMOVE A ROW
============================================================ */
function allocRemoveRow(rowId) {
    if ($('.alloc-row-item').length <= 1) {
        toastr.warning('At least one allocation row is required.');
        return;
    }
    $('[data-row-id="' + rowId + '"].alloc-row-item').fadeOut(200, function () {
        $(this).remove();
        allocRecalcTotal();
    });
}

/* ============================================================
   PRE-POPULATE ROWS (edit mode)
============================================================ */
function allocInitEdit(lines, editMode) {
    $('#alloc-rows-body').empty();
    if (lines && lines.length > 0) {
        $.each(lines, function (i, line) {
            allocAddRow(editMode, line);
        });
    } else {
        allocAddRow(editMode);
    }
}

/* ============================================================
   EVENT DELEGATION
============================================================ */
function allocInitRowsEvents(editMode) {

    $(document).on('click', '.alloc-add-row-trigger', function () {
        allocAddRow(editMode);
    });

    $(document).on('click', '.alloc-remove-row-trigger', function () {
        allocRemoveRow($(this).data('row-id'));
    });

    $(document).on('change', '.alloc-major-component', function () {
        var rowId = $(this).data('row-id');
        var majorId = $(this).val();
        $('#alloc-sub-' + rowId).html('<option value="">-- Select Sub-Component --</option>');
        if (majorId) allocLoadSubComponents(majorId, rowId, null);
        if (allocHasDuplicateRow()) toastr.warning('Duplicate combination detected.');
    });

    $(document).on('change', '.alloc-sub-component', function () {
        if (allocHasDuplicateRow()) toastr.warning('Duplicate combination detected.');
    });

    $(document).on('input', '.alloc-amount-input', function () {
        this.value = this.value.replace(/[^0-9.]/g, '');
        allocRecalcTotal();
    });

    $(document).on('focus', '.form-control, .form-select', function () {
        $(this).closest('td').find('.form-error').text('');
    });
}

/* ============================================================
   DURATION -> SUB-DURATION DEPENDENCY
============================================================ */
function allocInitDurationDropdown() {
    $('#alloc-duration').on('change', function () {
        var parentVal = $(this).val();
        var attrCode = window.allocAttrCodes.duration;

        $('#alloc-sub-duration-wrapper').hide();
        $('#alloc-sub-duration').html('<option value="">-- Select --</option>');

        if (attrCode && parentVal) {
            allocLoadAttributeValues(attrCode, parentVal, 'alloc-sub-duration', null, function () {
                if ($('#alloc-sub-duration option').length > 1) {
                    $('#alloc-sub-duration-wrapper').show();
                } else {
                    $('#alloc-sub-duration-wrapper').hide();
                }
            });
        }
    });

    // ── On edit page load: re-populate sub-duration if duration already set ──
    var existingDuration = $('#alloc-duration').val();
    var existingSubDuration = window.allocExistingSubDuration || null;

    if (existingDuration && existingSubDuration) {
        var attrCode = window.allocAttrCodes.duration;
        allocLoadAttributeValues(
            attrCode,
            existingDuration,
            'alloc-sub-duration',
            existingSubDuration,
            function () {
                if ($('#alloc-sub-duration option').length > 1) {
                    $('#alloc-sub-duration-wrapper').show();
                }
            }
        );
    }
}

/* ============================================================
   FILE UPLOAD
============================================================ */
function allocInitFileUpload(inputId, uploadUrl, hiddenInputId, displayId, progressWrapId) {
    $(document).on('change', '#' + inputId, function () {
        var file = this.files[0];
        if (!file) return;

        // Enforce strict 2MB limit (user definition)
        var maxSize = 2 * 1024 * 1024;
        if (file.size > maxSize) {
            toastr.error("Maximum allowed file size is 2 MB.");
            $(this).val(''); // Clear chosen file instantly
            return;
        }

        var $progressWrap = $('#' + progressWrapId);
        var $progressBar = $progressWrap.find('.progress-bar');
        var $display = $('#' + displayId);

        $progressWrap.show();
        $progressBar.css('width', '0%');

        var pct = 0;
        var interval = setInterval(function () {
            pct = Math.min(pct + 10, 85);
            $progressBar.css('width', pct + '%');
        }, 100);

        var formData = new FormData();
        formData.append('file', file);

        apiUploadFile(uploadUrl, formData)
            .then(function (result) {
                clearInterval(interval);
                $progressBar.css('width', '100%');
                $('#' + hiddenInputId).val(result.file_name || result.filename || result.path || '');
                $display.html(file.name);
                toastr.success('File uploaded successfully.');
            })
            .catch(function (err) {
                clearInterval(interval);
                toastr.error((err && err.body && err.body.message) || 'File upload failed.');
            })
            .finally(function () {
                setTimeout(function () { $progressWrap.hide(); }, 800);
            });
    });
}

/* ============================================================
   VALIDATION HELPERS
============================================================ */
function allocClearErrors() {
    $('.form-error').text('');
}

function allocSetError(fieldId, message) {
    $('#' + fieldId + '_error').text(message);
}

function allocShowServerErrors(errors) {
    if (!errors || typeof errors !== 'object') return;
    $.each(errors, function (key, messages) {
        var msg = Array.isArray(messages) ? messages[0] : messages;
        var parts = key.split('.');
        var $el = null;

        if (parts.length === 3 && parts[0] === 'allocation_lines') {
            var pointer = parts[1]; // Could be dynamic rowId OR sequential array index (0, 1, 2)
            var field = parts[2];

            // Strategy A: Look up directly by the dynamic row identifier provided
            $el = $('#alloc-' + (field === 'major_component_id' ? 'major' : (field === 'sub_component_id' ? 'sub' : 'amount')) + '-' + pointer + '_error');

            // Strategy B: Fallback to zero-based index targeting if direct lookup returned empty node (Laravel re-indexed)
            if (!$el || !$el.length) {
                var index = parseInt(pointer, 10);
                var $targetRow = $('.alloc-row-item').eq(index);
                if ($targetRow.length) {
                    var realId = $targetRow.data('row-id');
                    $el = $('#alloc-' + (field === 'major_component_id' ? 'major' : (field === 'sub_component_id' ? 'sub' : 'amount')) + '-' + realId + '_error');
                }
            }
        } else {
            // High-Fidelity Mapping Algorithm for Global Form Header Fields

            // 1. Try absolute literal key
            $el = $('#' + key + '_error');

            // 2. Try localized allocation hyphenated prefix standard (e.g. financial_year -> alloc-financial-year_error)
            if (!$el.length) {
                var alias = key.replace(/_/g, '-');
                $el = $('#alloc-' + alias + '_error');
            }

            // 3. Final explicit edge-case resolution (Handles structural variances and suffixes)
            if (!$el.length) {
                if (key === 'duration_id') {
                    $el = $('#alloc-duration_error');
                } else if (key === 'sub_duration_id') {
                    $el = $('#alloc-sub-duration_error');
                } else if (key === 'sanction_order_number') {
                    $el = $('#alloc-sanction-order-no_error'); // Matches "no" abbreviation in form
                } else if (key === 'document_path') {
                    $el = $('#alloc-doc-trigger_error');
                } else {
                    // Ultimate fallback to flat dot conversion
                    var flat = key.replace(/\./g, '_');
                    $el = $('#' + flat + '_error');
                }
            }
        }

        if ($el && $el.length) {
            $el.text(msg);
        }
    });
}

/* ============================================================
   FORM SUBMISSION
============================================================ */
function allocSubmitForm(formId, submitUrl, redirectUrl) {
    allocClearErrors();

    var hasError = false;

    var requiredFields = {
        'alloc-financial-year': 'Financial Year is required.',
        'alloc-duration': 'Duration is required.',
        'alloc-sanction-order-no': 'Sanction Order Number is required.',
        'alloc-sanction-order-date': 'Sanction Order Date is required.',
    };

    $.each(requiredFields, function (id, msg) {
        var val = $('#' + id).val();
        if (!val || !val.trim()) {
            allocSetError(id, msg);
            hasError = true;
        }
    });

    if ($('.alloc-row-item').length === 0) {
        toastr.error('Please add at least one allocation row.');
        hasError = true;
    }

    if (allocHasDuplicateRow()) {
        toastr.error('Duplicate row: Major + Sub-Component combination must be unique.');
        hasError = true;
    }

    $('.alloc-row-item').each(function () {
        var rowId = $(this).data('row-id');
        var major = $('#alloc-major-' + rowId).val();
        var amount = $('#alloc-amount-' + rowId);

        if (!major) {
            $('#alloc-major-' + rowId + '_error').text('Major Component is required.');
            hasError = true;
        }
        if (!amount.prop('disabled') && !String(amount.val()).match(/^\d+(\.\d{1,2})?$/)) {
            $('#alloc-amount-' + rowId + '_error').text('Enter a valid amount.');
            hasError = true;
        }
    });

    if (hasError) return;

    var formData = $('#' + formId).serializeArray();
    var payload = {};

    $.each(formData, function (i, field) {
        // Skip _method and _token
        if (field.name === '_method' || field.name === '_token') return;

        var match = field.name.match(/^allocation_lines\[(\d+)\]\[(.+)\]$/);
        if (match) {
            var rowKey = match[1];
            var fieldKey = match[2];
            if (!payload.allocation_lines) payload.allocation_lines = {};
            if (!payload.allocation_lines[rowKey]) payload.allocation_lines[rowKey] = {};
            payload.allocation_lines[rowKey][fieldKey] = field.value;
        } else {
            payload[field.name] = field.value;
        }
    });

    // ── Re-include disabled amount fields (they are skipped by serializeArray) ──
    $('.alloc-row-item').each(function () {
        var rowId = $(this).data('row-id');
        var $amount = $('#alloc-amount-' + rowId);
        if ($amount.prop('disabled') || $amount.prop('readonly')) {
            var rowKey = String(rowId);
            if (!payload.allocation_lines) payload.allocation_lines = {};
            if (!payload.allocation_lines[rowKey]) payload.allocation_lines[rowKey] = {};
            payload.allocation_lines[rowKey]['amount'] = $amount.val();
        }
    });

    $('#cover-spin').show();

    apiRequest({ url: submitUrl, method: 'POST', data: payload })
        .then(function (result) {
            if (result && (result.success || result.status === true || result.message)) {
                toastr.success(result.message || 'Allocation saved successfully.');
                setTimeout(function () { window.location.href = redirectUrl; }, 1200);
            } else if (result && result.errors) {
                allocShowServerErrors(result.errors);
                toastr.error('Please fix the highlighted errors.');
            } else {
                toastr.error(result && result.message ? result.message : 'Something went wrong.');
            }
        })
        .catch(function (err) {
            if (err && err.body && err.body.errors) {
                allocShowServerErrors(err.body.errors);
                toastr.error('Validation failed. Please fix the errors.');
            } else {
                toastr.error('An unexpected error occurred. Please try again.');
            }
        })
        .finally(function () {
            $('#cover-spin').hide();
        });
}