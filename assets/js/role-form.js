/**
 * Add/Edit Role screen.
 *
 * Loads the permission tree scoped to the selected Role Type over AJAX -- switching Role
 * Type re-fetches the tree from the server, so a permission that isn't offered by the new
 * type can never be left silently checked. Keeps the Select All / Remove All controls in
 * sync and merges the currently-assigned permissions (via role_permissions) into the
 * checked state, exactly like the Role Type add/edit screen does for its own tree.
 *
 * Expects ROLE_CONFIG to be defined by roles/form.blade.php.
 */
$(document).ready(function () {

    if (typeof ROLE_CONFIG === 'undefined') {
        return;
    }

    var $roleType = $('#role_type');
    var $section = $('#role_permission_section');
    var $hint = $('#role_permission_hint');
    var $loader = $('#role_permission_loader');
    var $error = $('#role_permission_error');
    var $wrapper = $('#role_permission_tree_wrapper');
    var $selectAllBtn = $('#select_all_role_permissions');
    var $removeAllBtn = $('#remove_all_role_permissions');

    function togglePermissionButtons(enabled) {
        $selectAllBtn.prop('disabled', !enabled);
        $removeAllBtn.prop('disabled', !enabled);
    }

    function showHint(message) {
        $loader.hide();
        $error.hide().text('');
        $wrapper.hide().empty();
        $hint.text(message).show();
        togglePermissionButtons(false);
    }

    function showLoader() {
        $hint.hide();
        $error.hide().text('');
        $wrapper.hide().empty();
        $loader.show();
        togglePermissionButtons(false);
    }

    function showTree(html) {
        $hint.hide();
        $loader.hide();
        $error.hide().text('');
        $wrapper.html(html).show();

        if ($.fn.treed && $wrapper.find('#tree1').length) {
            $wrapper.find('#tree1').treed();
            $wrapper.find('.branch').each(function () {
                if ($(this).find('ul li').length === 0) {
                    $(this).hide();
                }
            });
        }

        togglePermissionButtons($wrapper.find('input[name="permissions[]"]').length > 0);
    }

    function showError(message) {
        $hint.hide();
        $loader.hide();
        $wrapper.hide().empty();
        $error.text(message).show();
        togglePermissionButtons(false);
    }

    function loadPermissionTree(roleTypeId) {
        if (!roleTypeId) {
            showHint(ROLE_CONFIG.selectRoleTypeMessage);
            return;
        }

        showLoader();

        var url = ROLE_CONFIG.roleTypePermissionsUrl + '/' + roleTypeId;
        if (ROLE_CONFIG.roleId) {
            url += '/' + ROLE_CONFIG.roleId;
        }

        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (responseData) {
                if (!responseData.status || !responseData.data || typeof responseData.data.html === 'undefined') {
                    showError(responseData.message || ROLE_CONFIG.permissionLoadFailedMessage);
                    return;
                }

                showTree(responseData.data.html);
            },
            error: function (err) {
                var message = ROLE_CONFIG.permissionLoadFailedMessage;

                if (err && err.responseJSON && err.responseJSON.message) {
                    message = err.responseJSON.message;
                }

                showError(message);
            }
        });
    }

    // The permission block is only rendered server-side for users with
    // permission-button-view rights (see roles/form.blade.php) -- when it's missing there
    // is nothing to wire up, and no tree fetch should fire at all.
    if (!$section.length) {
        return;
    }

    $roleType.on('change', function () {
        $('#role_type_error').text('');
        $('#permissions_error').text('');
        loadPermissionTree($(this).val());
    });

    $selectAllBtn.on('click', function () {
        $wrapper.find('input[name="permissions[]"]').prop('checked', true);
        $('#permissions_error').text('');
    });

    $removeAllBtn.on('click', function () {
        $wrapper.find('input[name="permissions[]"]').prop('checked', false);
        $('#permissions_error').text('');
    });

    if (ROLE_CONFIG.initialRoleTypeId) {
        loadPermissionTree(ROLE_CONFIG.initialRoleTypeId);
    } else {
        showHint(ROLE_CONFIG.selectRoleTypeMessage);
    }
});
