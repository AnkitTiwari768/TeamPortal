@extends('components.admin.content-layout')

@section('action-header')

	@php
		$addUserUrl = url('users/create');
		// Get current logged-in user's is_sub_user value
		$isSubUser = auth()->user()->is_sub_user ?? 0;
	@endphp

	{{-- Show Add User button only if user has permission AND is not a sub-user --}}
	@if (acl(config('permissions.user-create')) && $isSubUser != 1)
		<div class="btn-group drop-btn">
				<button
					type="button"
					class="btn btn-primary"
					autocomplete="off"
				  onclick="window.location = '{{$addUserUrl}}'">
				  	<img src="{{asset('assets/ffo-admin/img/add.svg')}}" />
				  	{{ __('message.add_user') }}
				</button>
		</div>
	@endif

@endsection

@section('card-content')

<div class="card-body">
	<form id="search_form" autocomplete="off">
	    <div class="filter-bar d-flex px-3 py-2 gap-3 align-items-center">

			<div class="select-box">
				<label>{{ __('message.role_module') }}</label>
				{{ Form::select('role_id', dynamic_common_list($details?->role_list), '', ['class' => 'form-select filter_btn', 'id' => 'role_id']) }}
			</div>

			<div class="select-box">
				<label>{{ __('message.status') }}</label>
				{{ Form::select('status', array(""=>__('message.select'))+status_list(), $row['status'] ?? null, ['class' => 'form-select filter_btn', 'id' => 'status']) }}
			</div>

            @if(hasRole('administrator'))
            <div class="select-box">
				<label>Sub User</label>
				{{ Form::select('is_sub_user', ['' => __('message.select'), '1' => 'Yes', '0' => 'No'], null, ['class' => 'form-select filter_btn', 'id' => 'is_sub_user']) }}
			</div>
			@endif

		   <a href="javascript:void(0)" class="clear-action" id="reset_btn" style="display: none;"> <img src="{{ asset('assets/ffo-admin/img/close-blue.svg') }}">Clear all</a>
	  </div>
	</form>
</div>

<div class="card-body table-loading-container">

	<div class="table-responsive">
		<table class="table table-bordered table-striped" id="dataTable" width="100%" cellspacing="0">
			<thead>
				<tr>
					<th>{{ __('message.sn') }}</th>
					<th>{{ __('message.full_name') }}</th>
					<th>{{ __('message.email') }}</th>
					<th>{{ __('message.mobile') }}</th>
					<th>{{ __('message.role_name') }}</th>
					<th>{{ __('message.status') }}</th>
					@if(hasRole('administrator'))
						<th>Sub User</th>
					@endif
					@if (acl(config('permissions.user-update')) || acl(config('permissions.user-view')) || acl(config('permissions.permission-button-view')))
						<th class="actions">{{ __('message.action') }}</th>
					@endif
				</tr>
			</thead>
		</table>
	</div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg border-0">

      <!-- Loader Overlay (centered inside modal) -->
      <div id="modal-loader-overlay">
        <div class="loader-content">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <div class="loader-text">
            <i class="fa fa-key me-2"></i>Updating Password...
          </div>
        </div>
      </div>

      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="changePasswordModalLabel">
          <i class="fa fa-key me-2"></i>Change Password
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="changePasswordForm" autocomplete="off">
        @csrf
        <input type="hidden" id="change_password_user_id" name="user_id">
        <div class="modal-body">
          <!-- Email (display only, not submitted) -->
          <div class="mb-3">
            <input type="hidden" class="form-control bg-light" id="change_password_email" readonly disabled>
          </div>

          <!-- Auto Generate Password Button -->
          <div class="mb-3" style="margin-left: 54%;">
            <button type="button" class="btn btn-outline-success btn-sm" id="btn_generate_password">
              <i class="fa fa-refresh me-1"></i> Auto Generate Password
            </button>
          </div>

          <!-- New Password -->
          <div class="mb-3">
            <label class="form-label required">New Password </label>
            <div class="input-group">
              <input type="password" class="form-control" id="change_password_new" name="password" placeholder="Enter new password">
              <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('change_password_new', 'eye_new')">
                <i class="fa fa-eye" id="eye_new"></i>
              </button>
            </div>
            <span class="text-danger error-msg" id="password_error"></span>
          </div>

          <!-- Confirm Password -->
          <div class="mb-3">
            <label class="form-label required">Confirm Password </label>
            <div class="input-group">
              <input type="password" class="form-control" id="change_password_confirm" name="password_confirmation" placeholder="Confirm new password">
              <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('change_password_confirm', 'eye_confirm')">
                <i class="fa fa-eye" id="eye_confirm"></i>
              </button>
            </div>
            <span class="text-danger error-msg" id="password_confirmation_error"></span>
          </div>
        </div>

        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="btn_submit_change_password">
            <span id="btn_password_text"><i class="fa fa-save me-1"></i> Update Password</span>
            <span id="btn_password_loader" style="display: none;">
              <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
              Processing...
            </span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@section('js')
<script>
// ========================================
// DEBOUNCE FUNCTION
// ========================================
function debounce(func, wait) {
    var timeout;
    return function() {
        var context = this,
            args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(function() {
            func.apply(context, args);
        }, wait);
    };
}

// ========================================
// TOGGLE PASSWORD VISIBILITY
// ========================================
function togglePasswordVisibility(inputId, eyeId) {
    var input = document.getElementById(inputId);
    var eye   = document.getElementById(eyeId);
    if (input.type === "password") {
        input.type = "text";
        eye.classList.remove("fa-eye");
        eye.classList.add("fa-eye-slash");
    } else {
        input.type = "password";
        eye.classList.remove("fa-eye-slash");
        eye.classList.add("fa-eye");
    }
}

// ========================================
// GENERATE PASSWORD
// ========================================
function generatePassword() {
    var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789@#$!';
    var suffix = '';

    for (var i = 1; i < 4; i++) {
        suffix += chars[Math.floor(Math.random() * chars.length)];
    }

    return 'Team@' + suffix;
}

// ========================================
// CHANGE PASSWORD BUTTON
// ========================================
function buttonChangePassword(id, email) {
    return `<a
      href="javascript:void(0)"
      onclick="openChangePasswordModal('${id}', '${email}')"
      class="btn btn-sm btn-circle m-1 view-action-btn"
      style="background-color: #f39c12; color: #fff;"
      rel="tooltip"
      title="Change Password"
    ><i class="fa fa-key" style="color: white;"></i></a>`;
}

// ========================================
// OPEN CHANGE PASSWORD MODAL
// ========================================
function openChangePasswordModal(id, email) {
    $('#changePasswordForm')[0].reset();
    $('.error-msg').text('');

    $('#eye_new').removeClass('fa-eye-slash').addClass('fa-eye');
    $('#eye_confirm').removeClass('fa-eye-slash').addClass('fa-eye');
    $('#change_password_new').attr('type', 'password');
    $('#change_password_confirm').attr('type', 'password');

    $('#change_password_user_id').val(id);
    $('#change_password_email').val(email);
    $('#changePasswordModal').modal('show');
}

// ========================================
// GET DATATABLE INSTANCE
// ========================================
function getTable() {
    return $('#dataTable').DataTable();
}

// ========================================
// RELOAD TABLE
// ========================================
function reloadTable() {
    var oTable = getTable();
    oTable.ajax.reload(null, false);
}

// ========================================
// SHOW/HIDE CLEAR BUTTON
// ========================================
function showClearButton() {
    var hasFilter = false;
    $('#role_id, #status, #is_sub_user').each(function() {
        if ($(this).val() && $(this).val() !== '') {
            hasFilter = true;
            return false;
        }
    });
    if (hasFilter) {
        $('#reset_btn').show();
    } else {
        $('#reset_btn').hide();
    }
}

// ========================================
// SHOW/HIDE AJAX LOADER
// ========================================
function showLoader() {
    $('#ajax-loader').show();
    $('#dataTable_wrapper').addClass('blur-background');
}

function hideLoader() {
    $('#ajax-loader').hide();
    $('#dataTable_wrapper').removeClass('blur-background');
}

// ========================================
// DOCUMENT READY
// ========================================
$(document).ready(function () {
    // Show loader initially
    showLoader();

    // ========================================
    // AUTO GENERATE PASSWORD
    // ========================================
    $('#btn_generate_password').on('click', function () {
        var pwd = generatePassword();

        $('#change_password_new').val(pwd).attr('type', 'text');
        $('#change_password_confirm').val(pwd).attr('type', 'text');

        $('#eye_new').removeClass('fa-eye').addClass('fa-eye-slash');
        $('#eye_confirm').removeClass('fa-eye').addClass('fa-eye-slash');

        $('#password_error').text('');
        $('#password_confirmation_error').text('');
    });

    // ========================================
    // DATATABLE INITIALIZATION
    // ========================================
    var table = dataTableInit({
        id: "#dataTable",
        showExcelExport: true,
        order: { column: 1, direction: "desc" },
        url: "{{ url('users/datalist') }}",
        beforeLoad: function() {
            showLoader();
        },
        afterLoad: function() {
            hideLoader();
        },
        columns: [
            {
                "orderable": false,
                "render": function (data, type, full, meta) {
                    return serialNumber("#dataTable", meta.row);
                }
            },
            {
                "orderable": true,
                "render": function (data, type, row) { return row.full_name; }
            },
            {
                "orderable": true,
                "render": function (data, type, row) { return row.email; }
            },
            {
                "orderable": true,
                "render": function (data, type, row) { return row.mobile; }
            },
            {
                "orderable": true,
                "render": function (data, type, row) { return row.roles; }
            },
            {
                "orderable": true,
                "render": function (data, type, row) { return createActivationLabel(row.status); }
            }
            @if(hasRole('administrator'))
            ,{
                "orderable": true,
                "render": function (data, type, row) {
                    return row.is_sub_user === 'Yes'
                        ? '<div class="status"><span class="badge bg-primary">Yes</span></div>'
                        : '<div class="status"><span class="badge bg-danger">No</span></div>';
                }
            }
            @endif
            @if (acl(config('permissions.user-update')) || acl(config('permissions.user-view')) || acl(config('permissions.permission-button-view')))
            ,{
                "orderable": false,
                "render": function (data, type, row) {
                    var pedit = '';
                    @if (acl(config('permissions.user-update')))
                        @if(hasRole('administrator'))
                            if (row.is_sub_user_raw != 1) {
                                pedit = buttonEdit("{{ url('users') }}", row.id);
                            }
                        @else
                            pedit = buttonEdit("{{ url('users') }}", row.id);
                        @endif
                    @endif

                    var pview = '';
                    @if (acl(config('permissions.user-view')))
                        pview = buttonView("{{ url('users') }}", row.id);
                    @endif

                 var ppermission = '';

                @if (acl(config('permissions.permission-button-view')))
                    @if(hasRole('administrator'))
                        if (row.is_sub_user_raw != 1) {
                            ppermission = buttonUserPermission("{{ url('user-permissions') }}", row.id);
                        }
                    @else
                        ppermission = buttonUserPermission("{{ url('user-permissions') }}", row.id);
                    @endif
                @endif

                    var pchangePassword = '';
                    @if (acl(config('permissions.user-update')))
                        @if(hasRole('administrator'))
                            pchangePassword = buttonChangePassword(row.id, row.email);
                        @endif
                    @endif

                    return createActionButtons([pedit, pview, ppermission, pchangePassword]);
                }
            }
            @endif
        ],
        filters: ["role_id", "status"@if(hasRole('administrator')), "is_sub_user"@endif]
    });

    // ========================================
    // SEARCH WITH DEBOUNCE
    // ========================================
    $(document).on('init.dt', '#dataTable', function() {
        var oTable = getTable();
        var searchInput = $('.dataTables_filter input');

        // Remove existing event handlers
        searchInput.off('keyup input');

        // Apply debounced search
        searchInput.on('keyup input', debounce(function() {
            showLoader();
            oTable.search(this.value).draw();
        }, 500));
    });

    // ========================================
    // DATATABLE EVENTS FOR LOADER
    // ========================================
    $(document).on('preXhr.dt', '#dataTable', function() {
        showLoader();
    });

    $(document).on('draw.dt', '#dataTable', function() {
        hideLoader();
    });

    $(document).on('xhr.dt', '#dataTable', function(e, settings, json, xhr) {
        if (xhr && xhr.status !== 200) {
            hideLoader();
        }
    });

    // ========================================
    // FILTER CHANGES WITH DEBOUNCE
    // ========================================
    var debouncedReload = debounce(function() {
        reloadTable();
        showClearButton();
    }, 300);

    $('#role_id, #status, #is_sub_user').on('change', function() {
        debouncedReload();
    });

    // ========================================
    // RESET BUTTON
    // ========================================
    $('#reset_btn').on('click', function() {
        $('#search_form')[0].reset();
        $('#role_id').val('');
        $('#status').val('');
        $('#is_sub_user').val('');
        $(this).hide();

        // Clear search input as well
        $('.dataTables_filter input').val('');

        var oTable = getTable();
        oTable.search('').draw();
        oTable.ajax.reload(null, false);
    });

    // ========================================
    // SHOW CLEAR BUTTON ON FILTER CHANGE
    // ========================================
    $('#role_id, #status, #is_sub_user').on('change', function() {
        showClearButton();
    });

    // ========================================
    // CHANGE PASSWORD FORM SUBMIT
    // ========================================
    $("#changePasswordForm").on("submit", function (event) {
        event.preventDefault();
        $('.error-msg').text('');

        $('#modal-loader-overlay').fadeIn(300);
        $('#btn_submit_change_password').prop('disabled', true);
        $('#btn_password_text').hide();
        $('#btn_password_loader').show();

        var formData = {
            user_id              : $("#change_password_user_id").val(),
            password             : $("#change_password_new").val(),
            password_confirmation: $("#change_password_confirm").val(),
            _token               : '{{ csrf_token() }}'
        };

        $.ajax({
            url   : "{{ url('/users/change-password') }}",
            method: 'POST',
            data  : formData,
            success: function (response) {
                $('#modal-loader-overlay').fadeOut(300);
                $('#btn_submit_change_password').prop('disabled', false);
                $('#btn_password_text').show();
                $('#btn_password_loader').hide();

                if (response.status) {
                    toastr.success(response.message || "Password updated successfully.");
                    $('#changePasswordModal').modal('hide');
                    reloadTable();
                } else {
                    if (response.errors) {
                        $.each(response.errors, function (key, val) {
                            $('#' + key + '_error').text(val[0]);
                        });
                    } else {
                        toastr.error(response.message || "Something went wrong.");
                    }
                }
            },
            error: function (xhr) {
                $('#modal-loader-overlay').fadeOut(300);
                $('#btn_submit_change_password').prop('disabled', false);
                $('#btn_password_text').show();
                $('#btn_password_loader').hide();

                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    $.each(xhr.responseJSON.errors, function (key, val) {
                        $('#' + key + '_error').text(val[0]);
                    });
                } else {
                    toastr.error("Something went wrong. Please try again.");
                }
            }
        });
    });

    // ========================================
    // RESET MODAL ON CLOSE
    // ========================================
    $('#changePasswordModal').on('hidden.bs.modal', function () {
        $('#modal-loader-overlay').fadeOut(300);
        $('#btn_submit_change_password').prop('disabled', false);
        $('#btn_password_text').show();
        $('#btn_password_loader').hide();
        $('.error-msg').text('');
    });

    // ========================================
    // INITIAL CLEAR BUTTON STATE
    // ========================================
    showClearButton();


}); // END DOCUMENT READY
</script>

<style>
/* Table Loading Container */
.table-loading-container {
    position: relative;
    min-height: 300px;
}

/* Blur effect on the table wrapper */
#dataTable_wrapper.blur-background {
    filter: blur(4px);
    pointer-events: none;
    user-select: none;
    transition: filter 0.3s ease;
}

#dataTable_wrapper {
    transition: filter 0.3s ease;
}

/* Hide default Datatables processing label */
.dataTables_processing {
    display: none !important;
}

/* Modal overlay loader - Perfectly Centered */
#modal-loader-overlay {
    display: none;
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    border-radius: 8px;
    z-index: 1060;
    justify-content: center;
    align-items: center;
}

#modal-loader-overlay .loader-content {
    text-align: center;
    padding: 20px;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

#modal-loader-overlay .spinner-border {
    width: 3rem;
    height: 3rem;
    margin-bottom: 15px;
}

#modal-loader-overlay .loader-text {
    font-size: 16px;
    color: #333;
    font-weight: 500;
}

/* Ensure modal-content has position relative */
.modal-content {
    position: relative !important;
    overflow: hidden;
}

/* For modal-dialog to work properly */
.modal-dialog {
    display: flex;
    align-items: center;
    min-height: calc(100% - 1rem);
}
</style>
@endsection

@endsection
