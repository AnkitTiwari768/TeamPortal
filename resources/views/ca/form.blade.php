@extends('components.admin.content-layout')
@section('action-header')
<div class="btn-group drop-btn">
	@if(empty($isProfile))
		@include('components.admin.buttons.back-button')
	@endif
</div>
@endsection
@section('card-content') 
<div class="card-body pt-1">      
			<form id="formId">
				<div class="card mb-4">
					<div class="card-header d-flex justify-content-between"> 
						<h6 class="m-0 box-heading heading-1">
						@if(!empty($row['id']))
								@if(!empty($isProfile))
								{{ __('message.your_profile') }}
								@else
								{{ __('Edit CA') }}
								@endif
							@else
								{{ __('message.personal_details') }}
						@endif

						</h6>
					</div>
					
					
					
					<div class="card-body">
							<div class="row g-3">
								<div class="form-group col-md-4">
									<label class="form-label required">{{ __('message.first_name') }}</label>
									<input type="text" class="form-control txtOnly" maxlength="{{ config('constant.MAXLENGTH2') }}" name="first_name" id="first_name" placeholder="{{ __('message.first_name') }}"
										@if (isset($row) && isset($row['first_name']))
											value="{{ $row['first_name'] }}"
										@endif>
									<span class="text-danger form-error" id="first_name_error"></span>
								</div>
								@if (!isset($isCustom))
								<div class="form-group col-md-4">
									<label class="">{{ __('message.middle_name') }}</label>
									<input type="text" class="form-control txtOnly" maxlength="{{ config('constant.MAXLENGTH2') }}" name="middle_name" id="middle_name" placeholder="{{ __('message.middle_name') }}"
										@if (isset($row) && isset($row['middle_name']))
											value="{{ $row['middle_name'] }}"
										@endif>
									<span class="text-danger form-error" id="middle_name_error"></span>
								</div>
								@endif
								<div class="form-group col-md-4">
									<label class="form-label required">{{ __('message.last_name') }}</label>
									<input type="text" class="form-control txtOnly" maxlength="{{ config('constant.MAXLENGTH2') }}" name="last_name" id="last_name" placeholder="{{ __('message.last_name') }}"
										@if (isset($row) && isset($row['last_name']))
											value="{{ $row['last_name'] }}"
										@endif>
									<span class="text-danger form-error" id="last_name_error"></span>
								</div>
		
								
								<div class="form-group col-md-4 mb-3">
									<label class="form-label required label_email">{{__('message.email')}}</label>
									<div class="input-group">
										<input type="text" class="form-control" name="email" id="email" placeholder="{{__('message.email')}}" maxlength="{{ config('constant.EMAIL_LENGTH') }}"
											@if (isset($row) && isset($row['email']))
												value="{{ $row['email'] }}"
											@endif
										>
									</div>
									<span class="text-danger form-error" id="email_error"></span>
								</div> 
								
								
								<div class="form-group col-md-4 mb-3">
									<label class="form-label required label_mobile">{{__('message.mobile')}}</label>
									<div class="input-group">
										<input type="text" class="form-control numeric" name="mobile" id="mobile" placeholder="{{__('message.mobile')}}" maxlength="{{ config('constant.MOBILE_LENGTH') }}"
											@if (isset($row) && isset($row['mobile'])) value="{{ $row['mobile'] }}"@endif
										>
									</div> 
									<span class="text-danger form-error" id="mobile_error"></span>
								</div> 

							
							

							
								
								@if (!isset($row))
									<div class="form-group col-md-4">
										<label class="form-label required">{{ __('message.password') }}</label>
										<input type="password" class="form-control" maxlength="{{ config('constant.PASSWORDLENGTH') }}" name="password" id="password" placeholder="{{ __('message.password') }}"
											@if (isset($row) && isset($row['password']))
												value="{{ $row['password'] }}"
											@endif>
										<span class="text-danger form-error" id="password_error"></span>
									</div>
									
									<div class="form-group col-md-4">
										<label class="form-label required">{{ __('message.confirm_password') }}</label>
										<input type="password" class="form-control" maxlength="{{ config('constant.PASSWORDLENGTH') }}" name="password_confirmation" id="password_confirmation" placeholder="{{ __('message.confirm_password') }}"
											@if (isset($row) && isset($row['confirm_password']))
												value="{{ $row['confirm_password'] }}"
											@endif>
										<span class="text-danger form-error" id="password_confirmation_error"></span>
									</div>
								@endif
							</div>		
					</div>
					<div class="card-body"> 
						<div class="row g-3">

							<div class="form-action mt-3 mb-3">
								@include('components.admin.buttons.submit-button')
								{{-- @include('components.admin.buttons.cancel-button') --}}
								
								<a href="javascript:void(0);" class="btn btn-warning wave-effect has-ripple" onclick = "javascript:history.back(-1);">Cancel</a>
							</div>	
						</div>
						
					</div> 
				</div> 
			
			</form>
</div>

							
@section('js');

 <script>
		 
@if(isset($isProfile))
	
		
	$("#roles").prop("disabled", true);

	@endif


//$('#submit-btn').attr("disabled", true);
@isset($id)
	$('#submit-btn').attr("disabled", false);
@endisset 
		

$(document).ready(function () { 
	
    function crypto(secret) {
		if (secret.length > 0) {
			var salt = CryptoJS.enc.Hex.parse("{{ $crypto_salt }}");
			var iv = CryptoJS.enc.Hex.parse("{{ $crypto_iv }}");
			var key = CryptoJS.PBKDF2(
				"{{ $crypto_key }}", 
				salt, { 
					hasher: CryptoJS.algo.SHA512, 
					keySize: {{ $crypto_key_size }}, 
					iterations: {{ $crypto_iterations }} 
				}
			); 
			var encrypted = CryptoJS.AES.encrypt(secret, key, {iv: iv});
			var encryptedData = {
				ciphertext : CryptoJS.enc.Base64.stringify(encrypted.ciphertext),
				salt : CryptoJS.enc.Hex.stringify(salt),
				iv : CryptoJS.enc.Hex.stringify(iv)    
			};  
			return encryptedData;
		}
	} 
	   
		
	
		
	$("#formId").on("submit", function (event) {
    event.preventDefault();

    var formData = $("#formId").serializeArray();

    @if (!isset($row))
        var password = $("#password").val();
        var password_confirmation = $("#password_confirmation").val();

        formData = formData.filter(function (item) {
            return item.name !== 'password';
        });

        formData = formData.filter(function (item) {
            return item.name !== 'password_confirmation';
        });
    @endif

    @if (!isset($row))
        if (password) {
            formData.push({ name: 'password', value: crypto(password).ciphertext });
        }

        if (password_confirmation) {
            formData.push({ name: 'password_confirmation', value: crypto(password_confirmation).ciphertext });
        }
    @endif

    @if (isset($isProfile))
        formData = [];
        var first_name = $("#first_name").val();
        var middle_name = $("#middle_name").val();
        var last_name = $("#last_name").val();
        var email = $("#email").val();
        var mobile = $("#mobile").val();
        // var postal_code = $("#postal_code").val();
        // var landline_number = $("#landline_number").val();

        formData.push({ name: 'first_name', value: first_name });
        formData.push({ name: 'middle_name', value: middle_name });
        formData.push({ name: 'last_name', value: last_name });
        formData.push({ name: 'email', value: email });
        formData.push({ name: 'mobile', value: mobile });
        // formData.push({ name: 'postal_code', value: postal_code });
        // formData.push({ name: 'landline_number', value: landline_number });
        formData.push({ name: 'isProfile', value: true });
    @endif

    @isset($id)
        var method = 'POST';

        @isset($isProfile)
            @if (isset($isCustom) && $isCustom)
                var url = "{{ url('update-custom-user-profile/' . $id) }}";
            @else
                var url = "{{ url('profile/update/' . $id) }}";
            @endif
        @else
            var url = "{{ url('/ca-user/update/' . $id) }}"; // <---- updated here
        @endisset

    @else
        var method = 'POST';
        var url = "{{ url('/ca-user') }}"; // <---- updated here
    @endisset

    var requestData = {
        url: url,
        method: method,
        body: formData,
    };

    @if(isset($isProfile))
        sendRequest(requestData, "{{ url('profile') }}");
    @else
        sendRequest(requestData, "{{ url('ca-user') }}");
    @endif
});

	
	$("#cancel-btnnn").on("click", function(){
		$("#preview").attr("src", "{{ url('img/preview.png') }}");
		$('#submit-photo-btn').prop("disabled", true);
	});
	


});
		
    </script>
@endsection
@endsection

