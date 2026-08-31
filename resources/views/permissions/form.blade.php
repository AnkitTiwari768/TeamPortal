@extends('components.admin.layout')
@section('page-content')
<div class="container-fluid">     
	<div class="row">
		<div class="col-md-12">
			<div class="card mb-4">
				<div class="card-header d-flex justify-content-between mb-3">
					<h6 class="box-heading heading-1">
					  @if(!empty($row->id))
							 {{ __('message.edit_permission') }}
						@else
							 {{ __('message.add_permission') }}
					  @endif
					</h6>					
					@include('components.admin.buttons.back-button')
				</div>
				<div class="card-body">
					<form id="formId">
						<div class="row g-3"> 
						
							<div class="col-6 mb-3"> 
								<div class="form-group">
									<label class="required">{{ __('message.select_parent_module') }}</label>
										<select id="module_id" name="module_id" class="form-control">
											<option value="">-- Select Module --</option>
											@foreach($modules as $module)
												<option value="{{ $module['id'] }}" {{ $module['id'] == @$row['module_id'] ? 'selected' : '' }}>{{ $module['name'] }}</option>
												@include('permissions.childrens', ['childrens' => $module['children'], 'prefix' => '  └─>'])
											@endforeach
										</select>
									<span class="text-danger form-error" id="module_id_error"></span>
								</div>
							</div>
							
							<div class="form-group col-md-6">
								<label class="required">{{ __('message.permission_name') }}</label>
								<input type="text" class="form-control" name="name" id="name" placeholder="{{ __('message.permission_name') }}"
                                    @if (isset($row) && isset($row['name']))
                                        value="{{ $row['name'] }}"
                                    @endif>
								<span class="text-danger form-error" id="name_error"></span>
							</div>
							
							
							<?php /*<div class="form-group col-md-6">
								<?php $role_id=array();
									if(!empty($row['permissionRole'])){
										foreach ($row['permissionRole'] as $key => $value) {
										   $role_id[]=$value->role_id;
										}
									}
                                ?>
								<label class="required">{{ __('message.role_module') }}</label>
								{{ Form::select('role_id', role_list($details['roles']), $role_id ?? null, ['class' => 'form-control', 'id' => 'role_id', 'multiple'=> 'multiple']) }}
								<span class="text-danger form-error" id="role_id_error"></span>
							</div>
							*/?>
							
							<div class="form-group col-md-4">
								<div class="form-group">
									<label class="required">{{ __('message.role_module') }}</label>
										<select class="form-control js-example-basic-multiple" multiple="multiple" name="roles[]" id="roles" >
										
										</select>
									<span class="text-danger form-error" id="roles_error"></span>
								</div>
							</div>
						
						
							<div class="form-group col-md-6">
								<label>{{ __('message.description') }}</label>
                                <textarea rows="3" class="form-control" name="description" id="description" placeholder="{{ __('message.description') }}"> @if (isset($row) && isset($row['description'])) {{ $row['description'] }}  @endif</textarea>
                                <span class="text-danger form-error" id="description_error"></span>
							</div> 
						
							 <div class="mb-3">
								@include('components.admin.buttons.submit-button')
								@include('components.admin.buttons.cancel-button')
							</div>
						</div>							
					</form>
				</div>
			</div>
		</div>
	</div>
</div>



@section('js');

 <script>
	    /*$(document).ready(function() {
		$("#role_id").select2({
			placeholder: '--Select--',
			//closeOnSelect: true,
			multiple: true,     
		});
       });*/
	   
	   $('#roles').select2({
		multiple: true,
		placeholder: "Select",
		  ajax: {
		    url : "{{ url('search-roles') }}",
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
				results: data.data
			  };
			},
		  },
		});
		
		 @if(!empty($row['id']))
			var rowValues=@json($permissionRoles);	
			$.each(rowValues, function( index, value ) {
				var $newOption = $("<option selected='selected'></option>").val(value.id).text(value.text);
				$("#roles").append($newOption).trigger('change'); 
			});
		 @endif
	   
        $("#formId").on("submit", function (event) {
            event.preventDefault();
			var module_id = $("#module_id").val();
            var name = $("#name").val();
			var roles = $("#roles").val();
			var description = ($("#description").val() !='') ?$("#description").val():'';
			var csrfToken = "{{ csrf_token() }}";
            
            @isset($id)
                var method = 'POST';
                var url = "{{ url('/permissions/update/'. $id) }}";
            @else 
                var method = 'POST';
                var url = "{{ url('/permissions') }}";
            @endisset
			
            var requestData = {
                url: url,
                method: method,
                body: {
					module_id:module_id,
                    name: name,
					roles: roles,
					description: description,
                    _token: csrfToken
                }
            };

            sendRequest(requestData, "{{ url('permissions') }}");
        });
    </script>
@endsection
@endsection
