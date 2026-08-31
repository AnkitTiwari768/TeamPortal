@extends('components.admin.content-layout')
@section('action-header')
<div class="btn-group drop-btn"> 
		@include('components.admin.buttons.back-button') 
</div>
@endsection
@section('card-content') 
<div class="container-fluid">      
			<div class="card mb-4"> 
				
				
				<div class="card-body">
					<form id="formId">
						<div class="row">
							<div class="col-6 mb-3"> 
								<div class="form-group">
									<label class="form-label required">{{ __('message.role_name') }}</label>
									<input type="text" class="form-control alphaNumericSpace" name="name" id="name" placeholder="{{ __('message.role_name') }}"
										@if (isset($row) && isset($row['name']))
											value="{{ $row['name'] }}"
										@endif maxLength="{{config('constant.MAXLENGTH')}}">
									<span class="text-danger form-error" id="name_error"></span>
								</div>
							</div>
							<!-- <div class="col-6 mb-3"> 
								<div class="form-group">
									<label class="form-label required">{{ __('Department') }}</label>
									{{ Form::select('department', department_list(), $row['department'] ?? null, ['class' => 'form-control', 'id' => 'department']) }}
									<span class="text-danger form-error" id="department_error"></span>
								</div>
							</div> -->
							<div class="col-6 mb-3"> 
								<div class="form-group">
									<label class="form-label required">{{ __('message.status') }}</label>
									{{ Form::select('status', status_list(), $row['status'] ?? null, ['class' => 'form-select', 'id' => 'status']) }}   
									<span class="text-danger form-error" id="status_error"></span>
								</div> 
							</div>
							<div class="col-6 mb-3">
								<div class="form-group">
									<label class="form-label">{{ __('message.role_type') }}</label>
									{{ Form::select('role_type', $roleTypeOptions ?? role_type_list(), $row['role_type'] ?? null, ['class' => 'form-select', 'id' => 'role_type']) }}
									<span class="text-danger form-error" id="role_type_error"></span>
								</div>
							</div>
							@if (acl(config('permissions.permission-button-view')))
							<div class="col-12 mb-3" id="role_permission_section">
								<div class="form-group">
									<div class="d-flex align-items-center justify-content-between mb-2">
										<label class="form-label mb-0">{{ __('message.assign_permission_to_role') }}</label>
										<div class="btn-group" role="group">
											<button type="button" class="btn btn-sm btn-outline-primary" id="select_all_role_permissions" disabled>
												<i class="fa fa-check-square-o" aria-hidden="true"></i> {{ __('message.select_all_permissions') }}
											</button>
											<button type="button" class="btn btn-sm btn-outline-danger" id="remove_all_role_permissions" disabled>
												<i class="fa fa-square-o" aria-hidden="true"></i> {{ __('message.remove_all_permissions') }}
											</button>
										</div>
									</div>
									<hr class="mt-1">

									<div id="role_permission_hint" class="text-muted py-2">{{ __('message.select_role_type_to_load_permissions') }}</div>

									<div id="role_permission_loader" class="text-muted py-3" style="display:none;">
										<i class="fa fa-spinner fa-spin" aria-hidden="true"></i> {{ __('message.loading_permissions') }}
									</div>

									<div id="role_permission_error" class="alert alert-danger" style="display:none;"></div>

									<div id="role_permission_tree_wrapper"></div>

									<span class="text-danger form-error" id="permissions_error"></span>
								</div>
							</div>
							@endif
							<div class="col-12 mb-3" id="search_users"> 
								<div class="form-group">
									<label class="required">{{ __('message.search_user') }}</label>
										<select class="form-control js-example-basic-multiple" multiple="multiple" name="users[]" id="users" >
										
										</select>
									<span class="text-danger form-error" id="users_error"></span>
								</div>
							</div>
							
							<div class="col-12 mb-3">
								<label>{{ __('message.description') }}</label>
                                <textarea rows="3" class="form-control" name="description" id="description" placeholder="{{ __('message.description') }}"> @if (isset($row) && isset($row['description'])) {{ $row['description'] }}  @endif</textarea>
                                <span class="text-danger form-error" id="description_error"></span>
							</div>
							
						
						</div>
						 <div class="form-action mt-3 mb-3">
								@include('components.admin.buttons.submit-button')
								<!-- @include('components.admin.buttons.cancel-button') -->
								
							<button type="reset" class="btn btn-warning wave-effect">
								<span class="btn-label"> <i class="fa fa-undo" aria-hidden="true"></i> </span>  {{ __('message.reset') }}
							</button>

						
							</div>
					</form>
			</div> 
	</div>
</div>

@section('js');
<script>
	var ROLE_CONFIG = {
		roleTypePermissionsUrl: "{{ url('roles/role-type-permissions') }}",
		@isset($id)
			roleId: "{{ $id }}",
		@else
			roleId: null,
		@endisset
		initialRoleTypeId: "{{ $row['role_type'] ?? '' }}",
		selectRoleTypeMessage: "{{ __('message.select_role_type_to_load_permissions') }}",
		noPermissionMessage: "{{ __('message.no_permission_available') }}",
		permissionLoadFailedMessage: "{{ __('message.permission_load_failed') }}"
	};
</script>
<script src="{{ asset('assets/js/role-form.js?ver='.time()) }}"></script>
<link rel="stylesheet" href="{{ asset('assets/css/tree.css?ver='.time())}}">
<script src="{{ asset('assets/js/tree.js?ver='.time()) }}"></script>
 <script>

		@if(!empty($row['id']))
			$("#category_id").prop("disabled", true);
			$('#category_id').css("background","#e9ecef");
		@endif

        $('#users').select2({
			multiple: true,
			placeholder: "Select",
		  ajax: {
		    url : "{{ url('search-users') }}",
			method : 'GET',
			dataType: 'json',
			delay: 250,
			//tags: false,
			 data: function (params) {
			  var query = {
				search: params.term,
			  }
			  return query;
			}, 
			processResults: function (data) {
			  return {
				results: data.result
			  };
			},
		  },
		});
		
		
		 


		var is_check=$('#is_user_mapped').is(":checked");
		 is_user_mapped(is_check);

		function is_user_mapped(is_check){
			 if (is_check==true)
			{
			     $("#search_users").show();
			}else{
				 $("#search_users").hide();
			}
		}
		
		 $('#is_user_mapped').change(function() {
			var is_check=$('#is_user_mapped').is(":checked");
			is_user_mapped(is_check);
		});
 
        $("#formId").on("submit", function (event) {
            event.preventDefault();

            if (window.roleFormIsSubmitting) {
                return false;
            }
            window.roleFormIsSubmitting = true;

            var name = $("#name").val();
				var department = $("#department").val();
				var description = $("#description").val();

				if ($('#is_user_mapped').is(":checked"))
				{
				    var is_user_mapped =1;
				}else{
					var is_user_mapped =0;
				}

				var users = $("#users").val();
            var category_id = $("#category_id").val();
            var status = $("#status").val();
				var description = $("#description").val();
				var csrfToken = "{{ csrf_token() }}";

            @isset($id)
                var method = 'POST';
                var url = "{{ url('/roles/update/'. $id) }}";
            @else
                var method = 'POST';
                var url = "{{ url('/roles') }}";
            @endisset

            var requestData = {
                url: url,
                method: method,
                body: {
                    name: name,
					department: department,
					is_user_mapped:is_user_mapped,
					users:users,
					category_id : category_id,
					description: description,
                    status: status,
                    role_type: $("#role_type").val(),
                    _token: csrfToken
                }
            };

            // Only claim the permission section was submitted when a Role Type is selected
            // AND the tree is actually rendered on the page (the current user may lack
            // permission-button-view rights). Otherwise there is no tree to have edited,
            // and the role's existing permissions (if any) must be left untouched.
            if ($("#role_type").val() && $('#role_permission_tree_wrapper').length) {
                requestData.body.permissions_submitted = 1;
                var selectedPermissions = [];
                $('#role_permission_tree_wrapper input[name="permissions[]"]:checked').each(function () {
                    selectedPermissions.push($(this).val());
                });
                requestData.body.permissions = selectedPermissions;
            }

            sendRequest(requestData, "{{ url('roles') }}");

            setTimeout(function () {
                window.roleFormIsSubmitting = false;
            }, 1500);
        });
    </script>
@endsection
@endsection

