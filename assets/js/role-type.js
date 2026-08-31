/**
 * Role Type add/edit screen.
 *
 * Loads the module -> permission tree over AJAX (so the form paints immediately and the
 * tree gets its own loader), keeps the Select All / Remove All controls in sync, and posts
 * the form through the shared sendRequest() helper.
 *
 * Expects ROLE_TYPE_CONFIG to be defined by role-types/form.blade.php.
 */
$(document).ready(function () {

    if (typeof ROLE_TYPE_CONFIG === 'undefined') {
        return;
    }

    var isSubmitting = false;

    var $treeWrapper = $('#permission_tree_wrapper');
    var $treeLoader = $('#permission_tree_loader');
    var $treeError = $('#permission_tree_error');
    var $selectAllBtn = $('#select_all_permissions');
    var $removeAllBtn = $('#remove_all_permissions');

    // ========================================
    // PERMISSION TREE
    // ========================================
    function showTreeLoader() {
        $treeError.hide().text('');
        $treeWrapper.hide().empty();
        $treeLoader.show();
        togglePermissionButtons(false);
    }

    function hideTreeLoader() {
        $treeLoader.hide();
        $treeWrapper.show();
    }

    function togglePermissionButtons(enabled) {
        $selectAllBtn.prop('disabled', !enabled);
        $removeAllBtn.prop('disabled', !enabled);
    }

    function showTreeError(message) {
        $treeLoader.hide();
        $treeWrapper.hide().empty();
        $treeError.text(message).show();
        togglePermissionButtons(false);
    }

    function loadPermissionTree() {
        if (!ROLE_TYPE_CONFIG.hasPermissionSection) {
            return;
        }

        var url = ROLE_TYPE_CONFIG.permissionTreeUrl;

        if (ROLE_TYPE_CONFIG.roleTypeId) {
            url += '/' + ROLE_TYPE_CONFIG.roleTypeId;
        }

        showTreeLoader();

        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (responseData) {
                if (!responseData.status || !responseData.data || typeof responseData.data.html === 'undefined') {
                    showTreeError(responseData.message || ROLE_TYPE_CONFIG.permissionLoadFailedMessage);
                    return;
                }

                $treeWrapper.html(responseData.data.html);
                hideTreeLoader();

                // tree.js self-initialises #tree1 on page load, so an AJAX-injected tree has
                // to be initialised by hand.
                if ($.fn.treed && $treeWrapper.find('#tree1').length) {
                    $treeWrapper.find('#tree1').treed();
                    hideEmptyBranches();
                }

                togglePermissionButtons($treeWrapper.find('.role-type-permission').length > 0);
            },
            error: function (err) {
                var message = ROLE_TYPE_CONFIG.permissionLoadFailedMessage;

                if (err && err.responseJSON && err.responseJSON.message) {
                    message = err.responseJSON.message;
                }

                showTreeError(message);
            }
        });
    }

    function hideEmptyBranches() {
        $treeWrapper.find('.branch').each(function () {
            if ($(this).find('ul li').length === 0) {
                $(this).hide();
            }
        });
    }

    function selectedPermissions() {
        var selected = [];

        $treeWrapper.find('input[name="permissions[]"]:checked').each(function () {
            selected.push($(this).val());
        });

        return selected;
    }

    $selectAllBtn.on('click', function () {
        $treeWrapper.find('input[name="permissions[]"]').prop('checked', true);
        $('#permissions_error').text('');
    });

    $removeAllBtn.on('click', function () {
        $treeWrapper.find('input[name="permissions[]"]').prop('checked', false);
        $('#permissions_error').text('');
    });

    $(document).on('change', '.role-type-permission', function () {
        $('#permissions_error').text('');
    });

    // Reset must also put the checkboxes back to the server-side state, which for an edit
    // means the currently saved permission set -- a plain form reset would only restore the
    // markup's initial checked attributes for inputs that existed at page load.
    $('#reset_form').on('click', function () {
        $('.form-error').text('');
        loadPermissionTree();
    });

    // ========================================
    // SUBMIT
    // ========================================
    $('#formId').on('submit', function (event) {
        event.preventDefault();

        if (isSubmitting) {
            return false;
        }

        isSubmitting = true;

        var requestData = {
            url: ROLE_TYPE_CONFIG.submitUrl,
            method: 'POST',
            body: {
                name: $('#name').val(),
                _token: ROLE_TYPE_CONFIG.csrfToken
            }
        };

        if ($('#status').length) {
            requestData.body.status = $('#status').val();
        }

        if (ROLE_TYPE_CONFIG.hasPermissionSection) {
            // Flags that the tree was rendered, so the backend can tell "every permission
            // was unchecked" apart from "this user never saw the permission section".
            requestData.body.permissions_submitted = 1;
            requestData.body.permissions = selectedPermissions();
        }

        sendRequest(requestData, ROLE_TYPE_CONFIG.redirectUrl);

        // sendRequest() re-enables the submit button on any failure; release our own guard
        // slightly later so a double click cannot slip through in between.
        setTimeout(function () {
            isSubmitting = false;
        }, 1500);
    });

    loadPermissionTree();
});
