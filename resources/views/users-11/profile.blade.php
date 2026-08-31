@extends('components.admin.content-layout')
@section('card-content')

<div class="card-body pt-1">
   <!-- tabs starts -->      
   <form id="formId">
      <div class="mb-4">
         <div class="card-body">
       
            <div class="row g-3 mb-3">
               <div class="form-group col-md-4">
                  <label class="form-label required">{{ __('message.first_name') }}</label>
                  <input type="text" class="form-control txtOnly" maxlength="{{ config('constant.MAXLENGTH2') }}" name="first_name" id="first_name" placeholder="{{ __('message.first_name') }}"
                  @if (isset($row) && isset($row['first_name']))
                  value="{{ $row['first_name'] }}"
                  @endif>
                  <span class="text-danger form-error" id="first_name_error"></span>
               </div>
               <div class="form-group col-md-4">
                  <label class="form-label required">{{ __('message.last_name') }}</label>
                  <input type="text" class="form-control txtOnly" maxlength="{{ config('constant.MAXLENGTH2') }}" name="last_name" id="last_name" placeholder="{{ __('message.last_name') }}"
                  @if (isset($row) && isset($row['last_name']))
                  value="{{ $row['last_name'] }}"
                  @endif>
                  <span class="text-danger form-error" id="last_name_error"></span>
               </div>
               <div class="form-group col-md-4" style="display: none;">
                  <label class="form-label required">{{ __('Organization/Production Company Name') }}</label>
                  <input type="text" class="form-control txtOnly" maxlength="{{ config('constant.MAXLENGTH2') }}" name="production_name" id="production_name" disabled placeholder="{{ __('Organization/Production Company Name') }}"
                  @if (isset($row) && isset($row['production_company_name']))
                  value="{{ $row['production_company_name'] }}"
                  @endif>
                  <span class="text-danger form-error" id="production_name_error"></span>
               </div>
               <div class="form-group col-md-4">
                  <label class="form-label required">{{ __('Country') }}</label>
                  {{ Form::select('country_id', country_list(), $row['country_id'] ?? null, ['class' => 'form-select', 'id' => 'country']) }}  
                  <span class="text-danger form-error" id="country_id_error"></span>
               </div>
               <div class="form-group col-md-4">
                  <label class="form-label required">{{ __('State') }}</label>
                  <input class="form-control" type="text" name="state" placeholder="State" id="state" @if (isset($row) && isset($row['state_id']))
                  value="{{ $row['state_id'] }}"
                  @endif>
                  <span class="text-danger form-error" id="state_error"></span>
               </div>
               <div class="form-group col-md-4">
                  <label class="form-label required">{{ __('City') }}</label>
                  <input class="form-control" type="text" name="district" placeholder="City" id="district"  @if (isset($row) && isset($row['city_id']))
                  value="{{ $row['city_id'] }}"
                  @endif>
                  <span class="text-danger form-error" id="district_error"></span>
               </div>
               <div class="form-group col-md-4">
                  <label class="form-label required">{{ __('Address Line 1') }}</label>
                  <input class="form-control" type="text" name="address_first" placeholder="Address Line 1" id="address_first" @if (isset($row) && isset($row['address_first']))
                  value="{{ $row['address_first'] }}"
                  @endif>
                  <span class="text-danger form-error" id="address_first_error"></span>
               </div>
               <div class="form-group col-md-4">
                  <label>{{ __('Address Line 2') }}</label>
                  <input class="form-control" type="text" name="address_second" placeholder="Address Line 2" id="address_second" @if (isset($row) && isset($row['address_second']))
                  value="{{ $row['address_second'] }}"
                  @endif>
                  <span class="text-danger form-error" id="address_second_error"></span>
               </div>
               <div class="form-group col-md-4">
                  <label class="form-label required">{{ __('Postal Code') }}</label>
                  <input class="form-control numericonly" type="text"  maxlength="10" name="postal_code" placeholder="Postal Code" id="postal_code" @if (isset($row) && isset($row['postal_code']))
                  value="{{ $row['postal_code'] }}"
                  @endif>
                  <span class="text-danger form-error" id="postal_code_error"></span>
               </div>
               <div class="form-group col-md-4 mb-3">
                  <label class="required label_mobile">{{__('message.mobile')}}</label>
                  <div class="input-group">
                     <!--<input type="text" class="form-control numeric" onChange="isMobileupdated(this)" name="mobile" id="mobile" placeholder="{{__('message.mobile')}}" maxlength="{{ config('constant.MOBILE_LENGTH') }}"
                     @if (isset($row) && isset($row['mobile'])) value="{{ $row['mobile'] }}"@endif
                     >-->
					 
					 <input type="text" class="form-control numeric" name="mobile" id="mobile" placeholder="{{__('message.mobile')}}" maxlength="{{ config('constant.MOBILE_LENGTH') }}"
                     @if (isset($row) && isset($row['mobile'])) value="{{ $row['mobile'] }}"@endif
                     >
                     <!-- <button type="button" class="input-group-text " id="mobile_btn" onclick="GenerateOTPButton(this)" disabled><i class="fa fa-check" aria-hidden="true"></i> Verified</button> -->
                  </div>
                  <span class="text-danger form-error" id="mobile_error"></span>
               </div>
               <!-- <div class="form-group col-md-4">
                  <label class="form-label">{{ __('Phone No') }}</label>
                  <input class="form-control numeric" type="text" name="alternate_mobile" placeholder="Phone No" maxlength="10" id="alternate_mobile" @if (isset($row) && isset($row['alternate_mobile']))
                  value="{{ $row['alternate_mobile'] }}"
                  @endif>
                  <span class="text-danger form-error" id="alternate_mobile_error"></span>
               </div> -->
               <div class="form-group col-md-4">
                  <label class="form-label required">{{ __('message.email') }}</label>
                  <div class="input-group">
                     <input type="text" class="form-control alphaNumeric" maxlength="{{ config('constant.MAXLENGTH2') }}" name="email" id="email" readonly placeholder="{{ __('message.email') }}"
                     @if (isset($row) && isset($row['email']))
                     value="{{ $row['email'] }}"
                     @endif>
                     <!--<button type="button" class="input-group-text " id="mobile_btn" onclick="GenerateOTPButton(this)" disabled>Verify</button>-->	
                     <span class="text-danger form-error" id="email_error"></span>
                  </div>
               </div>
            </div>
            <div class="mb-3">
               @include('components.admin.buttons.submit-button')
               @include('components.admin.buttons.cancel-button')
            </div>
         </div>
      </div>
   </form>
</div>
@include('components.admin.popup.sms-email')					
@section('js');
<script>	  

function isMobileupdated(inputElement){
	var InputType = inputElement.getAttribute('name');		  
	var InputVal = inputElement.value;
	var readonly=inputElement.getAttribute('readonly');
	var primary_mobile='{{ $row['mobile'] }}';
	if(InputVal.length==10 && !readonly){  
		if(primary_mobile==InputVal){ 
			$('#submit').removeAttr('disabled'); 
			$('#'+InputType+'_btn').attr('disabled',true); 
			$('#'+InputType+'_btn').html('<i class="fa fa-check" aria-hidden="true"></i> Verified'); 
			$('#'+InputType+'_btn').removeClass('bg-primary text-white'); 
			return false;
		}
		$('#submit').attr('disabled',true);
		$('#'+InputType+'_btn').removeAttr('disabled'); 
		$('#'+InputType+'_btn').html('Verify'); 
		$('#'+InputType+'_btn').addClass('bg-primary text-white'); 
	}else{ 
		$('#submit').removeAttr('disabled'); 
		$('#'+InputType+'_btn').attr('disabled',true); 
		$('#'+InputType+'_btn').html('<i class="fa fa-check" aria-hidden="true"></i> Verified'); 
		$('#'+InputType+'_btn').removeClass('bg-primary text-white'); 
	} 

	var phoneNumber = document.getElementById('mobile').value;
  var alternateNumber = document.getElementById('alternate_mobile').value;

  // Check if the phone number and alternate number are the same.
  if (phoneNumber === alternateNumber) {
    alert('Phone number and alternate number cannot be the same.');
    $('#'+InputType).val(''); 
    $('#'+InputType+'_btn').attr('disabled',true); 
  }
}

function cancelRequest() { 
   $("#cancel-btn").trigger("click");
   $('#CommonModal').modal('hide');
}

   $(document).ready(function () {		
   $("#cancel-btn").click(function(){ 
   	$('#submit').removeAttr('disabled');
   	$('#mobile_btn').attr('disabled',true); 
	$('#mobile_btn').html('<i class="fa fa-check" aria-hidden="true"></i> Verified'); 
	$('#mobile_btn').removeClass('bg-primary text-white'); 
   });
   	$('input[name="production_type"]').on('change', function(e) {
   			var production_type = $('input[name=production_type]:checked').val();
   		
   			if(production_type == "{{ \App\Enums\ProductionType::Domestic->value }}"){
   				$(".representative_type").hide();
   			} else {
   				$(".representative_type").show();
   			}
   			
   		});
   
   	$("#formId").on("submit", function (event) {
   		event.preventDefault();  
   		var formData=$("#formId").serializeArray(); 
   		//To set formdata in case of update profile 
   			var method = 'POST';
   			@isset($isProfile)
   				var url = "{{ url('profile/update/'. $id) }}"; 
   			@else 
   				var url = "{{ url('/users/update/'. $id) }}";
   			@endisset
   		 
   		
   		var requestData = {
   			url: url,
   			method: method,
   			body:formData,   
   		};
   		 
   		@if(isset($isProfile)) 
   		   sendRequest(requestData, "{{ url('profile') }}");
   		@else 
   		   sendRequest(requestData, "{{ url('users') }}");
   		@endif 
   	});  
   	 
   });
   		
       
</script>
@endsection
@endsection