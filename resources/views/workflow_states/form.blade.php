@extends('components.admin.layout')
@section('page-content')
<div class="container-fluid px-4 py-4">     
	<div class="row">
		<div class="col-lg-12 col-md-12">
			<div class="card container-main-card">
				<div class="card-header d-flex">
					<div class="heading">
						<h1>
						@if(!empty($row->id))
							 {{ __('Edit Workflow State') }}
						@else
							 {{ __('Add Workflow State') }}
						@endif
						</h1>
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="#">Home</a></li> 
								<li class="breadcrumb-item"><a href="#">@if(!empty($row->id))
							 {{ __('Edit Workflow State') }}
						@else
							 {{ __('Add Workflow State') }}
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
									<label class="required form-label">{{ __('State Key') }}</label>
									<input type="text" class="form-control" name="state_key" id="state_key" placeholder="{{ __('Enter State Key') }}"
										@if (isset($row) && isset($row['state_key']))
											value="{{ $row['state_key'] }}"
										@endif maxLength="100">
									<span class="text-danger form-error" id="state_key_error"></span>
								</div>
							</div>

                            <div class="col-md-4">
								<div class="mb-3">
									<label class="required form-label">{{ __('State Value') }}</label>
									<input type="number" class="form-control" name="state_value" id="state_value" placeholder="{{ __('Enter State Value') }}"
										@if (isset($row) && isset($row['state_value']))
											value="{{ $row['state_value'] }}"
										@endif max="255">
									<span class="text-danger form-error" id="state_value_error"></span>
								</div>
							</div>

                            <div class="col-md-4">
								<div class="mb-3">
									<label class="required form-label">{{ __('Label') }}</label>
									<input type="text" class="form-control" name="label" id="label" placeholder="{{ __('Enter Label') }}"
										@if (isset($row) && isset($row['label']))
											value="{{ $row['label'] }}"
										@endif maxLength="255">
									<span class="text-danger form-error" id="label_error"></span>
								</div>
							</div>

							<div class="col-md-2"> 
								<div class="mb-3 mt-4 form-check">
                                    <input type="checkbox" class="form-check-input" id="is_initial" name="is_initial" value="1" @if(isset($row) && $row['is_initial']) checked @endif>
									<label class="form-check-label" for="is_initial">{{ __('Is Initial') }}</label>
								</div> 
							</div>

                            <div class="col-md-2"> 
								<div class="mb-3 mt-4 form-check">
                                    <input type="checkbox" class="form-check-input" id="is_final" name="is_final" value="1" @if(isset($row) && $row['is_final']) checked @endif>
									<label class="form-check-label" for="is_final">{{ __('Is Final') }}</label>
								</div> 
							</div>

                            <div class="col-md-12 mt-4">
                                <h5 class="mb-3">{{ __('Shown Roles (Visible Tabs)') }}</h5>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Role (e.g. BNP, ONDC Admin)') }}</th>
                                            <th>{{ __('Tab (e.g. pending, approved)') }}</th>
                                            <th><button type="button" class="btn btn-sm btn-success" id="add-shown-role">+ Add</button></th>
                                        </tr>
                                    </thead>
                                    <tbody id="shown-roles-tbody">
                                        @php
                                            $shownRoles = isset($row) && $row['shown_roles'] ? (is_string($row['shown_roles']) ? json_decode($row['shown_roles'], true) : $row['shown_roles']) : [];
                                        @endphp
                                        @if(is_array($shownRoles) && count($shownRoles) > 0)
                                            @foreach($shownRoles as $sr)
                                                <tr>
                                                    <td><input type="text" class="form-control sr-role" value="{{ $sr['role'] ?? '' }}" placeholder="e.g. BNP"></td>
                                                    <td><input type="text" class="form-control sr-tab" value="{{ $sr['tab'] ?? '' }}" placeholder="e.g. pending"></td>
                                                    <td><button type="button" class="btn btn-sm btn-danger remove-shown-role">X</button></td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
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
 <script>
        $("#formId").on("submit", function (event) {
            event.preventDefault();
            var workflow_type_id = $("#workflow_type_id").val(); 
            var state_key = $("#state_key").val();
            var state_value = $("#state_value").val();
            var label = $("#label").val();
            var is_initial = $("#is_initial").is(':checked') ? 1 : 0;
            var is_final = $("#is_final").is(':checked') ? 1 : 0;
            
            var shown_roles = [];
            $('#shown-roles-tbody tr').each(function() {
                var role = $.trim($(this).find('.sr-role').val());
                var tab = $.trim($(this).find('.sr-tab').val());
                if (role && tab) {
                    shown_roles.push({ role: role, tab: tab });
                }
            });

			var csrfToken = "{{ csrf_token() }}";
            
            @isset($row->id)
                var method = 'POST';
                var url = "{{ url('/workflow-states-update/'. $row->id) }}";
            @else 
                var method = 'POST';
                var url = "{{ url('/workflow-states') }}";
            @endisset

            var requestData = {
                url: url,
                method: method,
                body: {
                    workflow_type_id: workflow_type_id, 
                    state_key: state_key,
                    state_value: state_value,
                    label: label,
                    is_initial: is_initial,
                    is_final: is_final,
                    shown_roles: shown_roles,
                    _token: csrfToken
                }
            };

            sendRequest(requestData, "{{ url('workflow-states') }}");
        });

        // Simple slug generation logic for state_key
        $('#label').on('input', function() {
            @if(empty($row->id))
            var text = $(this).val();
            var slug = text.toLowerCase()
                .replace(/ /g, '_')
                .replace(/[^\w_]+/g, '');
            $('#state_key').val(slug);
            @endif
        });

        $('#add-shown-role').click(function() {
            var tr = `<tr>
                        <td><input type="text" class="form-control sr-role" placeholder="e.g. BNP"></td>
                        <td><input type="text" class="form-control sr-tab" placeholder="e.g. pending"></td>
                        <td><button type="button" class="btn btn-sm btn-danger remove-shown-role">X</button></td>
                      </tr>`;
            $('#shown-roles-tbody').append(tr);
        });

        $(document).on('click', '.remove-shown-role', function() {
            $(this).closest('tr').remove();
        });
    </script>
@endsection
@endsection
