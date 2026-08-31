@extends('components.admin.layout')
@section('page-content')
<div class="container-fluid">     
	<div class="row">
		<div class="col-md-12">
			<div class="card mb-4">
				<div class="card-header d-flex justify-content-between mb-3">
					<h6 class="box-heading heading-1">
					  @if(!empty($row['id']))
							 {{ __('message.edit_module') }}
						@else
							 {{ __('message.add_module') }}
					  @endif
					</h6>					
					@include('components.admin.buttons.back-button')
				</div>
				
				<div class="card-body">
					<form id="formId">
						<div class="row">
							
							<div class="col-4 mb-3"> 
								<div class="form-group">
									<label>{{ __('message.select_parent_module') }}</label>
									<select id="parent_id" name="parent_id" class="form-control">
										<option value="">-- Select Module --</option>
										@foreach($modules as $module)
											<option value="{{ $module['id'] }}" {{ $module['id'] == @$row['parent_id'] ? 'selected' : '' }}>{{ $module['name'] }}</option>
											@include('modules.childrens', ['childrens' => $module['children'], 'prefix' => '     └─>'])
										@endforeach
									</select>
									<span class="text-danger form-error" id="parent_id_error"></span>
								</div>
							</div>
							
							<div class="col-4 mb-3"> 
								<div class="form-group">
									<label class="required">{{ __('message.module_name') }}</label>
									<input type="text" class="form-control txtOnly" name="name" id="name" placeholder="{{ __('message.module_name') }}"
										@if (isset($row) && isset($row['name']))
											value="{{ $row['name'] }}"
										@endif maxLength="{{config('constant.MAXLENGTH')}}">
									<span class="text-danger form-error" id="name_error"></span>
								</div>
							</div>
							
							<div class="col-4 mb-3"> 
								<div class="form-group">
									<label class="required">{{ __('message.url') }}</label>
									<input type="text" class="form-control" name="url" id="url" placeholder="{{ __('message.url') }}"
										@if (isset($row) && isset($row['url']))
											value="{{ $row['url'] }}"
										@endif>
									<span class="text-danger form-error" id="url_error"></span>
								</div>
							</div>
							
							<div class="col-4 mb-3"> 
								<div class="form-group">
									<label class="required">{{ __('message.icon') }}</label>
									<input type="text" class="form-control" name="icon" id="icon" placeholder="{{ __('message.icon') }}"
										@if (isset($row) && isset($row['icon']))
											value="{{ $row['icon'] }}"
										@endif>
									<span class="text-danger form-error" id="icon_error"></span>
								</div>
							</div>
							<div class="col-4 mb-3"> 
								<div class="form-group">
									<label class="required">{{ __('message.sort_order') }}</label>
									<input type="text" class="form-control" name="sort_order" id="sort_order" placeholder="{{ __('message.sort_order') }}"
										@if (isset($row) && isset($row['sort_order']))
											value="{{ $row['sort_order'] }}"
										@endif>
									<span class="text-danger form-error" id="sort_order_error"></span>
								</div>
							</div>
							
							
							<!--<div class="form-group col-md-6">
								<label>{{ __('message.description') }}</label>
                                <textarea rows="3" class="form-control" name="description" id="description" placeholder="{{ __('message.description') }}"> @if (isset($row) && isset($row['description'])) {{ $row['description'] }}  @endif</textarea>
                                <span class="text-danger form-error" id="description_error"></span>
							</div> -->
							
							<div class="col-4 mb-3"> 
								<div class="form-group">
									<label class="required">{{ __('message.status') }}</label>
									{{ Form::select('status', status_list(), $row['status'] ?? null, ['class' => 'form-control', 'id' => 'status']) }}   
									<span class="text-danger form-error" id="status_error"></span>
								</div> 
							</div>
						</div>
						 <div class="mb-3">
							@include('components.admin.buttons.submit-button')
							@include('components.admin.buttons.cancel-button')
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

@section('js');
 <script>
        $("#formId").on("submit", function (event) {
            event.preventDefault();
			var parent_id=$("#parent_id").val();
            var name = $("#name").val();
			var url_name = $("#url").val();
			var icon = $("#icon").val();
			var description = $("#description").val();
            var status = $("#status").val();
            var sort_order = $("#sort_order").val();
			var csrfToken = "{{ csrf_token() }}";
            
            @isset($id)
                var method = 'POST';
                var url = "{{ url('/modules/update/'. $id) }}";
            @else 
                var method = 'POST';
                var url = "{{ url('/modules') }}";
            @endisset

            var requestData = {
                url: url,
                method: method,
                body: {
					parent_id:parent_id,
                    name: name,
					url:url_name,
					icon:icon,
					description:description,
                    status: status,
                    sort_order: sort_order,
                    _token: csrfToken
                }
            };

            sendRequest(requestData, "{{ url('modules') }}");
        });
    </script>
@endsection
@endsection

