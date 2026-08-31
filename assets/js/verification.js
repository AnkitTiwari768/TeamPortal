let countdownInterval;
let remainingTime = $('meta[name="timer_time"]').attr('content');


function isInputMobileType(inputElement){
	var InputType = inputElement.getAttribute('name');		  
	var InputVal = inputElement.value;
	var readonly=inputElement.getAttribute('readonly');
	if(InputVal.length==10 && !readonly){  
		$('#'+InputType+'_btn').removeAttr('disabled'); 
		$('#'+InputType+'_btn').html('Verify'); 
		$('#'+InputType+'_btn').addClass('bg-primary text-white'); 
	}else{ 
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

function isInputEmailType(inputElement){
	var InputType = inputElement.getAttribute('name');		  
	var InputVal = inputElement.value;
	var readonly=inputElement.getAttribute('readonly'); 
	if(validateEmail(InputVal) && !readonly){ 
		$('#'+InputType+'_btn').removeAttr('disabled'); 
		$('#'+InputType+'_btn').addClass('bg-primary text-white'); 
	}else{ 
		$('#'+InputType+'_btn').attr('disabled',true); 
		$('#'+InputType+'_btn').removeClass('bg-primary text-white'); 
	} 

	var Email = document.getElementById('email').value;
  var alternateEmail = document.getElementById('alternate_email').value;
  // Check if the email and alternate email are the same.
  if (Email === alternateEmail) {
    alert('Email and alternate Email cannot be the same.');
    $('#'+InputType).val(''); 
    $('#'+InputType+'_btn').attr('disabled',true); 
  }
}

const validateEmail = (email) => {
  return String(email)
    .toLowerCase()
    .match(
      /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|.(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
    );
};


function GenerateOTPButton(inputElement){ 
		var InputType = inputElement.getAttribute('id');
		var InputVal =InputType.split("_btn");	  
		var Label = $('.label_'+InputVal[0]).text();  	
		$(".title").text(Label);
 	   var keyValue=$("#"+InputVal[0]).val();
		$('#CommonModal').modal({backdrop: 'static',keyboard: false}); 
        $.ajax({
            url: BASE_URL+'/sent-otp',
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            method: 'POST',
            data: {keyValue:keyValue,type:InputVal[0]},
			beforeSend:function(){  
                $('#'+InputType).html('Sending&nbsp;&nbsp;<i class="fa fa-spinner fa-spin""></i>').attr('disabled','disabled');
            },
            success: function (response) { 
            	if(response.status === true){
					$("#keyValue").val(keyValue);
            		$('#keyType').attr('id-btn',  InputType);
            		$('#keyType').attr('id-field',  InputVal[0]);
					$('#CommonModal').modal('show'); 
					startTimer(InputType);
			   		toastr.success('OTP has been sent');
				}else{ 
					$('#'+InputType).html('Verify').attr('disabled',false);
				    $('#'+InputVal[0]+'_error').html(response.errors);
					toastr.error(response.errors);
				}

            }
        });
}



function verifyButton(type){
		var otp = $("#otp").val();  
		var keyValue = $("#keyValue").val();   
		var btn_id = $("#keyType").attr('id-btn');   
		var field_id = $("#keyType").attr('id-field');
		
		if (otp != '' && keyValue != '') {
			$.ajax({
				url: BASE_URL+'/verify-otp',
				headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                method: 'POST',
                data: {otp: otp,keyValue:keyValue,type:field_id},
				beforeSend:function(){  
                    $("#verify_btn").html('Wait <i class="fa fa-spinner fa-spin""></i>').attr('disabled','disabled');
                },
                success: function (response) { 
                	if(response.status === true){
                			$('#'+field_id+'_error').html(' ');  
											$('#submit').removeAttr('disabled'); 
	                    $('#CommonModal').modal('hide'); 
	                    $('#'+field_id).attr('readonly',true);
	                    $("#"+btn_id).attr('disabled',true);
	                    $('#'+btn_id).html(`<span class="verified-check"><img src="${BASE_URL}/assets/img/done_verify.svg"> Verified</span>`).attr('disabled',true);
	                    //check_verifications(); 

								stopTimer();
	                    $("#otp").val('');
				   		toastr.success('OTP has been verified');				   		 
					}else{  
						$("#otp_error").html(response.errors); 
					} 
                }
            });
  		}else{
			$("#otp_error").html('The otp field is required.'); 
		} 
	}


	function startTimer(InputType) {
		countdownInterval = setInterval( ()=>{ updateTimer(InputType); }, 1000); 
    }

	function stopTimer() {
		clearInterval(countdownInterval);
		remainingTime = $('meta[name="timer_time"]').attr('content');
		updateTimerDisplay(); 
	}

	function updateTimer(InputType) {
		if (remainingTime > 0) {
			remainingTime--;
			updateTimerDisplay();
		} else { 
			 $('#CommonModal').modal('hide');   
			 $('#'+InputType).html('Verify').attr('disabled',false);
			 toastr.error('OTP Timer Expired, Please try again for OTP.');
			 $("#resend-otp").show();
			 $("#otp_timer").show();
			 $('#change-password-btn'). prop("disabled", true);
				stopTimer(); 

		}
	}

	function updateTimerDisplay() {
		const minutes = Math.floor(remainingTime / 60);
		const seconds = remainingTime % 60;
		const formattedTime = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
		$("#otp_timer").html(formattedTime);
		//timerDisplay.textContent = formattedTime;
	}

	/*$('#CommonModal').on('hidden.bs.modal', function () {
		var InputType = $("#keyType").attr('id-field');
		$("#"+InputType+"_btn").html('Verify').attr('disabled',false);
		stopTimer();  
		
	});*/
/*function check_verifications(status){
	if(status==true){
		$('#submit-btn').removeAttr("disabled");			
	}else{
		return false;
	}
}*/