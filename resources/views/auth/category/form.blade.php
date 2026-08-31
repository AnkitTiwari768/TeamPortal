@extends('components.admin.layout')
@section('page-content')

<div class="container-fluid">     
	<div class="row">
		<div class="col-md-12">
			<div class="card mb-4">
				<div class="card-header d-flex justify-content-between mb-3">
					<h6 class="box-heading heading-1">
					  @if(!empty($row->id))
							 {{ __('message.edit_category') }}
						@else
							 {{ __('message.add_category') }}
					  @endif

					</h6>
					@include('components.admin.buttons.back-button')
				</div>
				<div class="card-body">
					<form id="formId">
						<div class="row">
							<div class="col-4 mb-3"> 
								<div class="form-group ">
									<label class="required">{{ __('message.category_name') }}</label>
									<input type="text" class="form-control alphaNumericSpace" name="name" id="name" placeholder="{{ __('message.category_name') }}"
										@if (isset($row) && isset($row['name']))
											value="{{ $row['name'] }}"
										@endif maxLength="{{config('constant.MAXLENGTH')}}" >
									<span class="text-danger form-error" id="name_error"></span>
								</div> 
							</div>
							
							<div class="col-4 mb-3"> 
								<div class="form-group ">
									<label class="required">{{ __('message.level') }}</label>
									<input type="text" class="form-control szna integer" name="level" id="level" placeholder="{{ __('message.level') }}"
										@if (isset($row) && isset($row['level']))
											value="{{ $row['level'] }}"
										@endif maxLength="10" >
									<span class="text-danger form-error" id="level_error"></span>
								</div> 
							</div>
							
							<!--<div class="form-group col-md-6">
								<label>{{ __('message.description') }}</label>
                                <textarea rows="3" class="form-control" name="description" id="description" placeholder="{{ __('message.description') }}"> @if (isset($row) && isset($row['description'])) {{ $row['description'] }}  @endif</textarea>
                                <span class="text-danger form-error" id="description_error"></span>
							</div> -->
							
							<div class="col-4 mb-3"> 
								<div class="form-group ">
									<label class="required">{{ __('message.status') }}</label>
									{{ Form::select('status', status_list($details?->status), $row['status'] ?? null, ['class' => 'form-control', 'id' => 'status']) }} 
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
			 var level = $("#level").val();
			  var description = $("#description").val();
            var status = $("#status").val();
			var csrfToken = "{{ csrf_token() }}";
            
            @isset($id)
                var method = 'POST';
                var url = "{{ url('/categories/update/'. $id) }}";
            @else 
                var method = 'POST';
                var url = "{{ url('/categories') }}";
            @endisset

            var requestData = {
                url: url,
                method: method,
                body: {
                    name: name,
					level:level,
					description:description,
                    status: status,
                    _token: csrfToken
                }
            };

            sendRequest(requestData, "{{ url('categories') }}");
        });
    </script>
@endsection
@endsection

