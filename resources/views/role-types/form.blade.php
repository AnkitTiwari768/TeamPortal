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
					<form id="formId" autocomplete="off">
						<div class="row">
							<div class="col-6 mb-3">
								<div class="form-group">
									<label class="form-label required">{{ __('message.role_type_name') }}</label>
									<input type="text" class="form-control alphaNumericSpace" name="name" id="name" placeholder="{{ __('message.role_type_name') }}"
										@if (isset($row) && isset($row['name']))
											value="{{ $row['name'] }}"
										@endif maxLength="{{config('constant.MAXLENGTH')}}">
									<span class="text-danger form-error" id="name_error"></span>
									<span class="text-danger form-error" id="slug_error"></span>
								</div>
							</div>

							@isset($id)
							<div class="col-6 mb-3">
								<div class="form-group">
									<label class="form-label required">{{ __('message.status') }}</label>
									{{ Form::select('status', status_list(), $row['status'] ?? null, ['class' => 'form-select', 'id' => 'status']) }}
									<span class="text-danger form-error" id="status_error"></span>
								</div>
							</div>
							@endisset

							@if (acl(config('permissions.role-type-permission-view')))
							<div class="col-12 mb-3">
								<div class="form-group">
									<div class="d-flex align-items-center justify-content-between mb-2">
										<label class="form-label mb-0">{{ __('message.assign_permission_to_role_type') }}</label>
										<div class="btn-group" role="group">
											<button type="button" class="btn btn-sm btn-outline-primary" id="select_all_permissions" disabled>
												<i class="fa fa-check-square-o" aria-hidden="true"></i> {{ __('message.select_all_permissions') }}
											</button>
											<button type="button" class="btn btn-sm btn-outline-danger" id="remove_all_permissions" disabled>
												<i class="fa fa-square-o" aria-hidden="true"></i> {{ __('message.remove_all_permissions') }}
											</button>
										</div>
									</div>
									<hr class="mt-1">

									<div id="permission_tree_loader" class="text-muted py-3" style="display:none;">
										<i class="fa fa-spinner fa-spin" aria-hidden="true"></i> {{ __('message.loading_permissions') }}
									</div>

									<div id="permission_tree_error" class="alert alert-danger" style="display:none;"></div>

									<div id="permission_tree_wrapper"></div>

									<span class="text-danger form-error" id="permissions_error"></span>
								</div>
							</div>
							@endif

						</div>
						 <div class="form-action mt-3 mb-3">
								@include('components.admin.buttons.submit-button')

							<button type="reset" class="btn btn-warning wave-effect" id="reset_form">
								<span class="btn-label"> <i class="fa fa-undo" aria-hidden="true"></i> </span>  {{ __('message.reset') }}
							</button>

							</div>
					</form>
			</div>
	</div>
</div>

@section('js');
<link rel="stylesheet" href="{{ asset('assets/css/tree.css?ver='.time())}}">
<script src="{{ asset('assets/js/tree.js?ver='.time()) }}"></script>
<script>
	var ROLE_TYPE_CONFIG = {
		permissionTreeUrl: "{{ url('role-types/permission-tree') }}",
		@isset($id)
			roleTypeId: "{{ $id }}",
			submitUrl: "{{ url('role-types/update/'. $id) }}",
		@else
			roleTypeId: null,
			submitUrl: "{{ url('role-types') }}",
		@endisset
		redirectUrl: "{{ url('role-types') }}",
		csrfToken: "{{ csrf_token() }}",
		hasPermissionSection: {{ acl(config('permissions.role-type-permission-view')) ? 'true' : 'false' }},
		permissionLoadFailedMessage: "{{ __('message.permission_load_failed') }}"
	};
</script>
<script src="{{ asset('assets/js/role-type.js?ver='.time()) }}"></script>
@endsection
@endsection
