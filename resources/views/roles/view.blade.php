@extends('components.admin.layout')
@section('page-content')
<div class="container-fluid">
	<div class="row">
		<div class="col-md-12">
			<!-- Role Details Card -->
			<div class="card shadow mb-4">
				<div class="card-header py-3 d-flex align-items-center justify-content-between">
					<h6 class="m-0 font-weight-bold text-primary">{{ __('message.role_details') }}</h6>
					<div class="d-flex gap-2">
						@if (acl(config('permissions.role-update')))
							<a href="{{ url('roles/'. $id .'/edit') }}" class="btn btn-primary btn-sm">
								<i class="fa fa-edit"></i> {{ __('message.edit_role') }}
							</a>
						@endif
						@include('components.admin.buttons.back-button')
					</div>
				</div>
				<div class="card-body">
					<div class="row mb-3 align-items-center border-bottom pb-3">
						<div class="col-sm-4 col-md-3 text-muted fw-bold">{{ __('message.role_name') }}</div>
						<div class="col-sm-8 col-md-9 text-dark">{{ $row['name'] ?? '-' }}</div>
					</div>

					<div class="row mb-3 align-items-center border-bottom pb-3">
						<div class="col-sm-4 col-md-3 text-muted fw-bold">{{ __('message.role_type') }}</div>
						<div class="col-sm-8 col-md-9">
							@if(!empty($row['role_type_name']))
								<span class="badge bg-primary px-2 py-1">{{ $row['role_type_name'] }}</span>
							@else
								<span class="text-muted fst-italic">{{ __('message.no_role_type_assigned') }}</span>
							@endif
						</div>
					</div>

					<div class="row mb-3 align-items-center border-bottom pb-3">
						<div class="col-sm-4 col-md-3 text-muted fw-bold">{{ __('message.status') }}</div>
						<div class="col-sm-8 col-md-9">
							@php
								$status = $row['status'] ?? 0;
								$statusText = $status == 1 ? __('message.active') : __('message.in_active');
								$statusClass = $status == 1 ? 'badge bg-success' : 'badge bg-danger';
							@endphp
							<span class="{{ $statusClass }} px-2 py-1">{{ $statusText }}</span>
						</div>
					</div>

					@if(!empty($row['description']))
					<div class="row mb-3 align-items-center border-bottom pb-3">
						<div class="col-sm-4 col-md-3 text-muted fw-bold">{{ __('message.description') }}</div>
						<div class="col-sm-8 col-md-9 text-dark">{{ $row['description'] }}</div>
					</div>
					@endif

					<div class="row mb-3 align-items-center border-bottom pb-3">
						<div class="col-sm-4 col-md-3 text-muted fw-bold">{{ __('message.created_by') }}</div>
						<div class="col-sm-8 col-md-9 text-dark">{{ $row['created_by_name'] ?? $row['created_by'] ?? '-' }}</div>
					</div>

					<div class="row mb-3 align-items-center pb-1">
						<div class="col-sm-4 col-md-3 text-muted fw-bold">{{ __('message.created_at') }}</div>
						<div class="col-sm-8 col-md-9 text-dark">{{ isset($row['created_at']) ? date('d-m-Y', strtotime($row['created_at'])) : '-' }}</div>
					</div>
				</div>
			</div>

			<!-- Assigned Permissions Card -->
			<div class="card shadow mb-4">
				<div class="card-header py-3 d-flex align-items-center justify-content-between">
					<h6 class="m-0 font-weight-bold text-primary">
						{{ __('message.assigned_permissions') }}
						<span class="badge bg-secondary ms-1">{{ count($row['permissions'] ?? []) }}</span>
					</h6>
					@if (acl(config('permissions.permission-button-view')))
						<a href="{{ url('role-permission/'. $id .'/edit') }}" class="btn btn-primary btn-sm">
							<i class="fa fa-edit"></i> {{ __('message.update_permission') }}
						</a>
					@endif
				</div>
				<div class="card-body">
					@php
						$permissions = !empty($row['permissions']) ? collect($row['permissions'])->groupBy('module_name') : collect();
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
