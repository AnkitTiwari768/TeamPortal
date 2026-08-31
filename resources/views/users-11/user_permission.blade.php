@extends('components.admin.layout')
@section('page-content')

<div class="container-fluid">     
	<div class="row">
		<div class="col-md-12">
			<div class="card mb-4">
				<div class="card-header d-flex justify-content-between mb-3">
					<h6 class="box-heading heading-1">
					 {{__('message.user_permission')}} For {{$user_name}}
					</h6>				
					@include('components.admin.buttons.back-button')
				</div>
				
				<!-- ADDED HEADING HERE -->
				<div class="card-body">
					<!-- Main Heading -->
					<h4 class="mb-4 text-primary">Manage User Permissions</h4>
					<!-- OR you can use this if you want a sub-heading -->
					<!-- <h6 class="mb-3 text-muted">Assign permissions to {{$user_name}}</h6> -->
					
					<form id="formId">
					   <div class="row">
							<div class="col-12 mb-3"> 
								<div class="form-group">
									<ul id="tree1">
										@foreach($permissions as $child)
											
										@php 
											if ($child['have_children'] == 0 && $child['have_permissions'] == 0) {
												continue;
											}
										@endphp
										
											<li>
											{{ $child['name'] }}
												
												
												@foreach($child['permissions'] as $resp)
												<ul>
													<li>
													<label>{{ $resp->name }} <input type="checkbox" name="permissions[]" id="permissions" <?php if (in_array($resp->id, $user_permissions)){echo "checked";}else{echo "";} ?> value="{{$resp->id}}" class="form-check-input">
													</label>
													</li>
												</ul>
												@endforeach
												@if(count($child['children']))
													@include('users.childrens',['childrens' => $child['children']])
												@endif
											</li>
										@endforeach

									</ul>
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

@section('js');
<link rel="stylesheet" href="{{ asset('assets/css/tree.css?ver='.time())}}"> 
<script src="{{ asset('assets/js/tree.js?ver='.time()) }}"></script>
<script> 
	$('#formId').submit(function(event) {
        event.preventDefault();
		var selectedValues = [];
       $('input[type="checkbox"]:checked').each(function() {
		selectedValues.push($(this).val());
       });
	   
		var userId = '{{$id}}';
		var url = '{{ url('user-permissions') }}';
		var csrfToken = "{{ csrf_token() }}";
		var method = "POST";
		var requestData = {
        url: url,
        method: method,
        body: {
            user_id: userId,
            permissions: selectedValues,
            _token: csrfToken
        }
    };
    sendRequest(requestData, "{{ url('users') }}");
  });
</script>
@endsection
@endsection