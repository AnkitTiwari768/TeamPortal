@extends('components.front.auth-layout')
@section('auth-form')

	<div id="login-form-container" > 
	<h2>Forgot Password </h2>	

		<form id="formId" class="text-start" method="POST" action="{{ route('forget-password') }}"  autocomplete="off">
		@csrf
			<!--<div class="mb-3"> 
				<div class="form-group col-md-12">
					<span class="required mb-1">{{__('message.forgot_password_select')}}</span>
					<select class="form-control" name="otp_option" id="otp_option">
					<option value="">Select</option>
					<option value="email">By Email</option>
					<option value="mobile">By Mobile</option>
				</select> 
					<span class="text-danger form-error" id="otp_option_error"></span>
				</div> 
			</div>-->
	
			<div class="form-group mb-3 col-md-12 emailOption">
				<label class="required mb-1"> Enter New Password ?</label>
				<input type="email" class="form-control" name="email" id="email"  placeholder="Enter new password" required  autocomplete="off">
			</div>

			<div class="form-group mb-3 col-md-12">
				<label class="required mb-1"> Confirm New Password </label>
				<input type="text" class="form-control integer" name="mobile" id="mobile"  placeholder="Confirm new password" required  autocomplete="off">
			</div>

	
			<div class="mb-3">
				<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal" >  <i class="fa fa-sign-in" aria-hidden="true"></i> Reset My Password</button>  
			</div>	
			<div class="mb-3 links">
				<a href="{{url('/')}}" class="float-end"><?php echo __('message.front_back');?></a>  
			</div>						
		</form>
					
			
				
		
	</div>





@section('js')
 <script> 
   
      $('#otp_option').on('change', function(e) {  
        if ($(this).val() === 'mobile') {
          $('.emailOption').css('display', 'none');
          $('#resetBtn').removeAttr('disabled'); 
        }
        if ($(this).val() === 'email') {
          $('.mobileOption').css('display', 'none');
          $('#resetBtn').removeAttr('disabled'); 
        }
        $('.' + $(this).val()+'Option').css('display', '');
        $('#' + $(this).val()).val('');
      }) 
 
	 $('#forgot-password-button').on('click', function(e) {
        e.preventDefault(); 
       // otp_option = $('#otp_option').val();

	   //new code
	   var email = $("#email").val();
	   $.ajax({
			url: "{{ url('forget-password') }}",
			type: 'POST',
			data: { 
				"_token": $('meta[name="csrf-token"]').attr('content'),
				"email": email,
				"captcha": $("#captcha").val(),
			},
			success: function (data) {
				// Clear all previous errors
				$('.error_email').html('');
				$('.error_captcha').html('');
				$('#captcha').val('');
				$('#email').val('');
				$('#reload').click();
				//console.log(data);
				if(data.status){ 
					if (data.message) {
						toastr.success(data.message);
					}
				} else {
					const errors = data.errors;

					if (typeof errors === 'object') {
						$.each(errors, function (key, errorMessages) {
							if (key === 'email') {
								$('.error_email').html('<small class="text-danger">' + errorMessages[0] + '</small>');
							}
							if (key === 'captcha') {
								$('.error_captcha').html('<small class="text-danger">' + errorMessages[0] + '</small>');
							}
							toastr.error(errorMessages[0]);
						});
					} else {
						// errors is just a string
						toastr.error(errors); // Show general error
						$('.error_email').html('<small class="text-danger">' + errors + '</small>');
					}
				}
			},
			
			error: function(response) { 
				$('#reload').click();
				$('#captcha').val('');
				$('#email').val('');
				$("#forgot-password-button").removeClass("disabled").addClass("btn-primary");
				$("#forgot-password-button").html('<i class="fa fa-paper-plane-o" aria-hidden="true"></i> "{{__('message.send_password_reset_link')}}"');
				toastr.error(xhr.responseJSON.message);
			}
		});
	   //new code end

        //if (otp_option=='email') { 
			//$("#formId").submit();
           	// return false; 
       /// } 

        /*$.ajax('reset-password-mobile', {
            type: 'POST',  
            data: { 
                "_token": "{{ csrf_token() }}",
                 "option_type":  otp_option, 
                 "mobile":  $('#mobile').val().trim(),
                 "captcha":  $('#captcha').val().trim(),
                 },  // data to submit
            	success: function (data, status, xhr) { 
                if(data.status==false){
                    $('#error').html('Error: ' + data.message);
                    $(".errorMobile").show();  
                	$("#reload").trigger("click");
                }
                else {                      
                    $('#success').html(data.message);
                    window.setTimeout(function() {
		                window.location.href = data.url;
		            }, 2000);
                }
            },
            error: function (response) {
                    $('#error').html('Error: ' + response.responseJSON.errors.mobile[0]);
            }
        });   */  
          
    });
	$('#reload').click(function () {
        $.ajax({
            type: 'GET',
            url: "{{ url('refresh_captcha')}}",
            beforeSend: function () {
                $(".captcha-img").html('loading...');
            },
            success: function (data) {
                $(".captcha-img").html(data.captcha);
            }
        });
    });
 </script>   
@endsection
@endsection
