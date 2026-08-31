@extends('components.admin.layout')
@section('page-content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<div class="container-fluid px-4 py-4">     
	<div class="row">
		<div class="col-lg-12 col-md-12">
			<div class="card container-main-card">
				<div class="card-header d-flex">
					<div class="heading">
						<h1>
						@if(!empty($row->id))
							 {{ __('Edit Workflow Transition') }}
						@else
							 {{ __('Add Workflow Transition') }}
						@endif
						</h1>
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="#">Home</a></li> 
								<li class="breadcrumb-item"><a href="#">@if(!empty($row->id))
							 {{ __('Edit Workflow Transition') }}
						@else
							 {{ __('Add Workflow Transition') }}
						@endif</a></li>
							</ol>
						</nav>	
					</div>
					<div class="action-header ms-auto">
						<div class="btn-group drop-btn">
							@include('components.admin.buttons.back-button')
						</div>
					</div>
				</div>
				<div class="card-body pt-1">
					<form id="formId"> 
						<div class="row">
                            <div class="col-md-4">
								<div class="mb-3">
									<label class="required form-label">{{ __('Workflow Type') }}</label>
									{{ Form::select('workflow_type_id', ['' => 'Select Workflow Type'] + $workflowTypes->toArray(), $row['workflow_type_id'] ?? null, ['class' => 'form-select', 'id' => 'workflow_type_id']) }}
									<span class="text-danger form-error" id="workflow_type_id_error"></span>
								</div>
							</div>

							<div class="col-md-4">
								<div class="mb-3">
									<label class="required form-label">{{ __('From State') }}</label>
                                    <select class="form-select" id="from_state_id" name="from_state_id">
                                        <option value="">Select State</option>
                                    </select>
									<span class="text-danger form-error" id="from_state_id_error"></span>
								</div>
							</div>

                            <div class="col-md-4">
								<div class="mb-3">
									<label class="required form-label">{{ __('To State') }}</label>
                                    <select class="form-select" id="to_state_id" name="to_state_id">
                                        <option value="">Select State</option>
                                    </select>
									<span class="text-danger form-error" id="to_state_id_error"></span>
								</div>
							</div>

                            <div class="col-md-4">
								<div class="mb-3">
									<label class="required form-label">{{ __('Action') }}</label>
									<input type="text" class="form-control" name="action" id="action" placeholder="{{ __('Enter Action Name') }}"
										@if (isset($row) && isset($row['action']))
											value="{{ $row['action'] }}"
										@endif maxLength="100">
									<span class="text-danger form-error" id="action_error"></span>
								</div>
							</div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="required form-label">{{ __('Allowed Roles') }}</label>
                                  @php
                                        $selectedRoles = [];

                                        if (isset($row)) {
                                            if (is_string($row->allowed_roles)) {
                                                $selectedRoles = json_decode($row->allowed_roles, true) ?? [];
                                            } elseif (is_array($row->allowed_roles)) {
                                                $selectedRoles = $row->allowed_roles;
                                            }

                                            // Trim all values
                                            $selectedRoles = array_map(function ($role) {
                                                return is_string($role) ? trim($role) : $role;
                                            }, $selectedRoles);
                                        }
                                    @endphp
                                    <select class="form-control" id="allowed_roles" name="allowed_roles[]" multiple="multiple">
                                        @foreach($roles as $role)
                                         @php
                                            $roleName = '';
                                            if ($role->slug === 'ondc-admin') {
                                                $roleName = 'ONDC Admin';
                                            } elseif ($role->slug === 'snp') {
                                                $roleName = 'SNP';
                                            } elseif ($role->slug === 'bnp') {
                                                $roleName = 'BNP';
                                            } elseif ($role->slug === 'lsp') {
                                                $roleName = 'LSP';
                                            }
                                            else {
                                                $roleName = $role->name;
                                            }
                                        
                                        @endphp
                                            
                                            <option value="{{ $roleName }}"
                                                @if(in_array($roleName, $selectedRoles)) selected @endif>
                                                {{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="text-danger form-error" id="allowed_roles_error"></span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Shown Roles') }}</label>
                                  @php
                                        $selectedShownRoles = [];

                                        if (isset($row)) {
                                            if (is_string($row->shown_roles)) {
                                                $selectedShownRoles = json_decode($row->shown_roles, true) ?? [];
                                            } elseif (is_array($row->shown_roles)) {
                                                $selectedShownRoles = $row->shown_roles;
                                            }

                                            // Trim all values
                                            $selectedShownRoles = array_map(function ($role) {
                                                return is_string($role) ? trim($role) : $role;
                                            }, $selectedShownRoles);
                                        }
                                    @endphp
                                    <select class="form-control" id="shown_roles" name="shown_roles[]" multiple="multiple">
                                        @foreach($roles as $role)
                                         @php
                                            $roleName = '';
                                            if ($role->slug === 'ondc-admin') {
                                                $roleName = 'ONDC Admin';
                                            } elseif ($role->slug === 'snp') {
                                                $roleName = 'SNP';
                                            } elseif ($role->slug === 'bnp') {
                                                $roleName = 'BNP';
                                            } elseif ($role->slug === 'lsp') {
                                                $roleName = 'LSP';
                                            }
                                            else {
                                                $roleName = $role->name;
                                            }
                                        
                                        @endphp
                                            
                                            <option value="{{ $roleName }}"
                                                @if(in_array($roleName, $selectedShownRoles)) selected @endif>
                                                {{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="text-danger form-error" id="shown_roles_error"></span>
                                </div>
                            </div>
                            
							<div class="col-md-4"> 

								<div class="mb-3 mt-4 form-check">
                                    <input type="checkbox" class="form-check-input" id="auto_execute" name="auto_execute" value="1" @if(isset($row) && $row['auto_execute']) checked @endif>
									<label class="form-check-label" for="auto_execute">{{ __('Auto Execute') }}</label>
								</div> 
							</div>
						</div>
							<div class="form-action mt-3 mb-3">
								@include('components.admin.buttons.submit-button')
								@include('components.admin.buttons.cancel-button')
							</div>						
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
 <script>
        var allStates = @json($workflowStates);
        
        $('#workflow_type_id').on('change', function() {
            var selectedType = $(this).val();
            var options = '<option value="">Select State</option>';
            allStates.forEach(function(state) {
                if (state.workflow_type_id == selectedType) {
                    options += '<option value="' + state.id + '">' + state.label + '</option>';
                }
            });
            $('#from_state_id').html(options);
            $('#to_state_id').html(options);
            
            @if(isset($row))
                if(selectedType == '{{ $row->workflow_type_id }}') {
                    $('#from_state_id').val('{{ $row->from_state_id }}');
                    $('#to_state_id').val('{{ $row->to_state_id }}');
                }
            @endif
        });

        $(document).ready(function() {
            $('#allowed_roles').select2({ placeholder: "Select Roles", width: '100%', allowClear: true });
            $('#shown_roles').select2({ placeholder: "Select Shown Roles", width: '100%', allowClear: true });
            
            if ($('#workflow_type_id').val()) {
                $('#workflow_type_id').trigger('change');
            }
        });

        $("#formId").on("submit", function (event) {
            event.preventDefault();
            var workflow_type_id = $("#workflow_type_id").val(); 
            var from_state_id = $("#from_state_id").val();
            var to_state_id = $("#to_state_id").val();
            var action = $("#action").val();
            var allowed_roles = $("#allowed_roles").val();
            var shown_roles = $("#shown_roles").val();
            var auto_execute = $("#auto_execute").is(':checked') ? 1 : 0;
			var csrfToken = "{{ csrf_token() }}";
            
            @isset($row->id)
                var method = 'POST';
                var url = "{{ url('/workflow-transitions-update/'. $row->id) }}";
            @else 
                var method = 'POST';
                var url = "{{ url('/workflow-transitions') }}";
            @endisset

            var requestData = {
                url: url,
                method: method,
                body: {
                    workflow_type_id: workflow_type_id, 
                    from_state_id: from_state_id,
                    to_state_id: to_state_id,
                    action: action,
                    allowed_roles: allowed_roles,
                    shown_roles: shown_roles,
                    auto_execute: auto_execute,
                    _token: csrfToken
                }
            };

            sendRequest(requestData, "{{ url('workflow-transitions') }}");
        });
    </script>
@endsection
@endsection
