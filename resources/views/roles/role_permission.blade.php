@extends('components.admin.layout')
@section('page-content')

<div class="container-fluid">     
	<div class="row">
		<div class="col-md-12">
			<div class="card mb-4">
				<div class="card-header d-flex justify-content-between mb-3">
					<h6 class="box-heading heading-1">
					 {{__('message.role_permission')}} For {{$roleName}}
					</h6>				
					@include('components.admin.buttons.back-button')
				</div>
				
				
				<div class="card-body">
					<form id="formId">
						<!-- Removed the top button and added a heading here instead -->
						<div class="mb-3">
							<h5 class="text-primary">Assign Permissions to {{$roleName}}</h5>
							<hr>
						</div>
						
					   <div class="row">
							<div class="col-12 mb-3">
								<div class="form-group">
									@if(!$roleTypeId)
										<div class="alert alert-warning mb-0">{{ __('message.role_requires_role_type_for_permissions') }}</div>
									@elseif(empty($permissions))
										<div class="alert alert-info mb-0">{{ __('message.no_permission_available') }}</div>
									@else
										<div class="btn-group mb-2" role="group">
											<button type="button" class="btn btn-sm btn-outline-primary" id="select_all_role_permissions">
												<i class="fa fa-check-square-o" aria-hidden="true"></i> {{ __('message.select_all_permissions') }}
											</button>
											<button type="button" class="btn btn-sm btn-outline-danger" id="remove_all_role_permissions">
												<i class="fa fa-square-o" aria-hidden="true"></i> {{ __('message.remove_all_permissions') }}
											</button>
										</div>
										<ul id="tree1">
											@foreach($permissions as $child)

											@php
												if ($child['have_children'] == 0 && $child['have_permissions'] == 0) {
													continue;
												}
											@endphp

												<li>
												@if($child['have_permissions']==1 && $child['have_children']==0 )
														{{ $child['name'] }}
													@endif

													@if($child['have_permissions']==0 && $child['have_children']==1)
														{{ $child['name'] }}
												@endif



													@foreach($child['permissions'] as $resp)
													<ul>
														<li><label>{{ $resp->name }} <input type="checkbox" name="permissions[]" id="permissions" <?php if (in_array($resp->id, $rolePermissions)){echo "checked";}else{echo "";} ?> value="{{$resp->id}}" class="form-check-input"></label></li>

													</ul>
													@endforeach
													@if(count($child['children']))
														@include('roles.childrens',['childrens' => $child['children']])
													@endif
												</li>
											@endforeach

										</ul>
									@endif
									<span class="text-danger form-error" id="permissions_error"></span>
							</div>
						</div>

						 <div class="mb-3">
							<button type="submit" class="btn btn-success" {{ empty($permissions) ? 'disabled' : '' }}> <i class="fa fa-save"></i> Assign Permission</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

@section('js')
<link rel="stylesheet" href="{{ asset('assets/css/tree.css?ver='.time())}}"> 
<script src="{{ asset('assets/js/tree.js?ver='.time()) }}"></script>
<script> 
	
	/* $("#check_all").click(function(){
        if($(this).prop('checked')==true){
            $('input:checkbox').not(this).prop('checked', this.checked);    
        }else{
            $('input:checkbox').prop('checked', false);    
        }
    }); */

	$('#select_all_role_permissions').on('click', function () {
		$('input[name="permissions[]"]').prop('checked', true);
	});

	$('#remove_all_role_permissions').on('click', function () {
		$('input[name="permissions[]"]').prop('checked', false);
	});

	$('#formId').submit(function(event) {
        event.preventDefault();
		
		/* if ($('#check_all').is(":checked"))
		{
			var check_all=$('#check_all').val();
		}else{
			var check_all=0;	
		} */
		
		
		var selectedValues = [];
       $('input[name="permissions[]"]:checked').each(function() {
		selectedValues.push($(this).val());
       });
	   //console.log(selectedValues);
		
		var roleId = '{{$id}}';
		var url = '{{ url('role-permission') }}';
		var csrfToken = "{{ csrf_token() }}";
		var method = "POST";
		var requestData = {
        url: url,
        method: method,
        body: {
            role_id: roleId,
            permissions: selectedValues,
            _token: csrfToken
        }
    };
    sendRequest(requestData, "{{ url('roles') }}");
  });
</script>
@endsection
@endsection