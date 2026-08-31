@extends('components.admin.layout')
@section('page-content')
<div class="container-fluid">     
	<div class="row">
		<div class="col-md-12">
			<div class="card mb-4">
				<div class="card-header d-flex justify-content-between mb-3">
					<h6 class="box-heading heading-1">
					  @if(!empty($row->id))
							 {{ __('message.edit_department') }}
						@else
							 {{ __('message.add_department') }}
					  @endif

					</h6>
					
					@include('components.admin.buttons.back-button')
				</div>
				<div class="card-body">
					<form id="formId"> 
						<div class="row">
							<div class="col-4 mb-3">
								<div class="form-group">
									<label class="required">{{ __('message.department_name') }}</label>
									<input type="text" class="form-control txtOnly" name="name" id="name" placeholder="{{ __('message.department_name') }}"
										@if (isset($row) && isset($row['name']))
											value="{{ $row['name'] }}"
										@endif maxLength="{{config('constant.MAXLENGTH')}}">
									<span class="text-danger form-error" id="name_error"></span>
								</div>
							</div>
							
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
            var name = $("#name").val(); 
            var status = $("#status").val();
			var csrfToken = "{{ csrf_token() }}";
            
            @isset($id)
                var method = 'POST';
                var url = "{{ url('/departments/update/'. $id) }}";
            @else 
                var method = 'POST';
                var url = "{{ url('/departments') }}";
            @endisset

            var requestData = {
                url: url,
                method: method,
                body: {
                    name: name, 
                    status: status,
                    _token: csrfToken
                }
            };

            sendRequest(requestData, "{{ url('departments') }}");
        });
    </script>
@endsection
@endsection

