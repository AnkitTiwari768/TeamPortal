@extends('components.admin.layout')
@section('page-content')
<div class="container-fluid">     
	<div class="row">
		<div class="col-md-12">
			<div class="card shadow mb-4">
				<div class="card-header py-3 d-flex flex- align-items-center justify-content-between"> 
					<h6 class="m-0 font-weight-bold text-primary">{{ __('message.personal_details') }}</h6>
					@include('components.admin.buttons.back-button')
				</div>
				<div class="card-body">
					<form id="formId">
						<div class="row g-3">
							@if(!empty($row['first_name']))
							<div class="form-group col-md-4">
								<label><strong>{{ __('message.first_name') }} :</strong></label>
								{{ $row['first_name'] }}
							</div>
							@endif
							@if(!empty($row['middle_name']))
							<div class="form-group col-md-4">
								<label><strong>{{ __('message.middle_name') }} :</strong></label>
								{{ $row['middle_name'] }}
							</div>
							@endif
							@if(!empty($row['last_name']))
							<div class="form-group col-md-4">
								<label><strong>{{ __('message.last_name') }} :</strong></label>
								{{ $row['last_name'] }}
							</div>
							@endif
							@if(!empty($row['username']))
							<div class="form-group col-md-4">
								<label><strong>{{ __('message.username') }} :</strong></label>
								{{ $row['username'] }}
							</div>
							
							@endif
							@if(!empty($row['email']))
							<div class="form-group col-md-4">
								<label><strong>{{ __('message.email') }} :</strong></label>
								{{ $row['email'] }}
							</div>
							@endif
							@if(!empty($row['alternate_email']))
							<div class="form-group col-md-4">
								<label><strong>{{ __('message.alternate_email') }} :</strong></label>
								{{ $row['alternate_email'] }}
							</div>
							@endif
							@if(!empty($row['mobile']))
							<div class="form-group col-md-4">
								<label><strong>{{ __('message.mobile') }} :</strong></label>
								{{ $row['mobile'] }}
							</div>
							@endif
							@if(!empty($row['alternate_mobile']))
							<div class="form-group col-md-4">
								<label><strong>{{ __('message.alternate_mobile') }} :</strong></label>
								{{ $row['alternate_mobile'] }}
							</div>
							@endif
							
						</div>						
					</form>
				</div>
			</div>
			<div class="card shadow mb-4">
				<div class="card-header py-3 d-flex flex- align-items-center justify-content-between"> 
					<h6 class="m-0 font-weight-bold text-primary">{{ __('message.position_details') }}</h6>
				</div>
				<div class="card-body"> 
					<div class="row g-3">
						@if(!empty($row['category_id']))
						<div class="form-group col-md-4">
							<label><strong>{{ __('message.category_module') }} :</strong></label>
							{{ $row['category_name'] }}
						</div>
						@endif
						@if(!empty($row['roles']))
						<div class="form-group col-md-4">
							<label><strong>{{ __('message.role_name') }} :</strong></label>
								<span class="badge bg-primary">{{ implode(" , ",json_decode($row['roles']))}}</span>
							<?php /*{{ 
								array_map(function($role) {
									return '<label class="badge bg-succes">'. $role .'</label>';
								}, 
								json_decode($row['roles'])
								) 
							}} */?>
						</div>
						@endif
						@if(!empty($row['department_id']))
						<div class="form-group col-md-4">
							<label><strong>{{ __('message.department_name') }} :</strong></label>
							{{ $row['department_name'] }}
						</div>
						@endif
						@if(!empty($row['designation_id']))
						<div class="form-group col-md-4">
							<label><strong>{{ __('message.designation_name') }} :</strong></label>
							{{ $row['designation_name'] }}
						</div>
						@endif
						@if(!empty($row['address']))
						<div class="form-group col-md-4">
							<label><strong>{{ __('message.address') }} :</strong></label>
							{{ $row['address'] }}
						</div>
						@endif
						@if(!empty($row['postal_code']))
						<div class="form-group col-md-4">
							<label><strong>{{ __('message.pincode') }} :</strong></label>
							{{ $row['postal_code'] }}
						</div>
						@endif

						@if(!empty($row['country_id']))
						<div class="form-group col-md-4">
							<label><strong>{{ __('message.country_name') }} :</strong></label>
							{{ $row['country_name'] }}
						</div>
						@endif
						@if(!empty($row['state_id']))
						<div class="form-group col-md-4">
							<label><strong>{{ __('message.state_name') }} :</strong></label>
							{{ $row['state_name'] }}
						</div>
						@endif
						@if(!empty($row['district_id']))
						<div class="form-group col-md-4">
							<label><strong>{{ __('message.district_name') }} :</strong></label>
							{{ $row['district_name'] }}
						</div>
						@endif
						<div class="form-group col-md-4">
							<label><strong>{{ __('message.status') }} :</strong></label>
							<?php if($row['status']==config('constant.ACTIVE')){ echo "Active";}else{ echo 'In-active'; } ?>
						</div>
					</div>
				</div>
			</div>

			<!-- Assigned Permissions Card -->
			<div class="card shadow mb-4">
				<div class="card-header py-3 d-flex align-items-center justify-content-between">
					<h6 class="m-0 font-weight-bold text-primary">
						{{ __('message.assigned_permissions') }}
						<span class="badge bg-secondary ms-1">{{ count($assignedPermissions ?? []) }}</span>
					</h6>
					@if (acl(config('permissions.permission-button-view')))
						<a href="{{ url('user-permissions/'. $id .'/edit') }}" class="btn btn-primary btn-sm">
							<i class="fa fa-edit"></i> {{ __('message.update_permission') }}
						</a>
					@endif
				</div>
				<div class="card-body">
					@php
						$permissions = !empty($assignedPermissions) ? collect($assignedPermissions)->groupBy('module_name') : collect();
					@endphp

					@if($permissions->isEmpty())
						<div class="alert alert-info mb-0">{{ __('message.no_permission_available') }}</div>
					@else
						<div class="table-responsive">
							<table class="table table-bordered table-striped">
								<thead>
									<tr>
										<th style="width: 30%;">{{ __('message.module_name') }}</th>
										<th>{{ __('message.permission_name') }}</th>
									</tr>
								</thead>
								<tbody>
									@foreach($permissions as $moduleName => $modulePermissions)
										<tr>
											<td class="fw-bold">{{ $moduleName ?: '-' }}</td>
											<td>
												@foreach($modulePermissions as $permission)
													<span class="badge bg-secondary me-1 mb-1">{{ $permission['name'] }}</span>
												@endforeach
											</td>
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					@endif
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

