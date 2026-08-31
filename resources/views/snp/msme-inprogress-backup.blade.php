@extends('components.admin.layout')
@section('page-content')
<div class="container-fluid">     
	<div class="row">
		<div class="col-md-12">
			<div class="card shadow mb-4">
			
				<div class="card-header py-3 d-flex flex- align-items-center justify-content-between"> 
					<h6 class="m-0 font-weight-bold text-primary">MSME and SNP Mapping</h6>
					
				</div>
				<div class="card-body">
					<div class="row g-3">
						<div class="form-group col-md-12">
							<label class="form-label required">Enter the Seller Provider Id (Sent to your Mobile Number <span class="text-primary">{{$detail->mobile}}</span> via SMS and Email <span class="text-primary">{{$detail->email}})</label>
							<input type="text" class="form-control" name="seller_provider_id" id="seller_provider_id" placeholder="Enter the Seller Provider Id" required>
							<span class="text-danger form-error" id="seller_provider_id_error"></span>
						</div>
						
						<span id="otp_timer"></span>
						<a href="javascript:void(0);"  id="resend-otp" class="text-primary text-decoration-none" style="display: none; font-size: 13px;">
						  <i class="bi bi-arrow-repeat me-1"></i>Resend OTP
						</a>

						  <div class="form-group col-md-12" style="display:none" id="otp_div">
							<label class="form-label required">
							  Enter the 6 digit One Time Password (OTP)  
							  
							</label>
							<input type="password" class="form-control integer" name="otp" id="otp" placeholder="Enter 6 digit OTP" minlength="6" maxlength="6" required>
							<span class="text-danger form-error" id="otp_error"></span>
						  </div>
						  
						<div class="row mt-2">
						  <div class="form-group col-md-2" id="div_send_otp">
							<button type="button" class="btn btn-primary w-100" id="send-otp">Send OTP</button>
						  </div>
						  
						  <div class="form-group col-md-2" style="display:none" id="div_verify_otp">
							<button type="button" class="btn btn-primary w-100" id="verify-otp">Verify OTP</button>
						  </div>

						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('js')
<script>

 $("#send-otp").on("click", function(e) {
		e.preventDefault();
       $("#send-otp").attr("class", "btn btn-primary disabled");
       $("#send-otp").html("Processing...");
       var csrfToken = "{{ csrf_token() }}";
		  
	   var seller_provider_id = $("#seller_provider_id").val();
	   if (seller_provider_id) {
		   $("#cover-spin").show();
		   $.ajax({
			   url: "{{ url('send-otp') }}",
			   type: 'POST',
			   data: { 
				   "username":"{{$detail->mobile}}",
				   "email":"{{$detail->email}}",
				   "_token": csrfToken,
			   },
			  
			   success: function (data) { 
				   if (data.status) {
					   startTimer();
                       timerInterval();
					  $("#otp_div").show();
					  $('#div_send_otp').hide();
					  $('#div_verify_otp').show();
					  $("#cover-spin").hide();    
					   toastr.success(data.message);
				   }else{ 
						$("#send-otp").html('Send OTP');
						$("#send-otp").attr("class", "btn btn-primary");				   
						toastr.error(data.message);
				   }
					$("#cover-spin").hide();  
			   },
			   error: function(xhr, status, error) {
				   
				   $("#cover-spin").hide(); 
				   $("#send-otp").html('Send OTP');
					$("#send-otp").attr("class", "btn btn-primary");
				   toastr.error(xhr.responseJSON.message);
			   }
		   });
	   } 
	   else { 
			$("#cover-spin").hide(); 
			$("#send-otp").html('Send OTP');
			$("#send-otp").attr("class", "btn btn-primary");
			toastr.error("Please enter the required fields");
		   return;
	   }
   }); 
   
   
   
    $("#verify-otp").on("click", function(e) {
		e.preventDefault();
       $("#verify-otp").attr("class", "btn btn-primary");
       $("#verify-otp").html("Processing...");
	   $("#verify-otp").html('Verify OTP');
       var csrfToken = "{{ csrf_token() }}";
	   var password=$("#otp").val();  
	   var seller_provider_id = $("#seller_provider_id").val();
	   
	  
	   if (seller_provider_id) {
		   var crypto = cryptoJS(password);
		   
           if (crypto && crypto.ciphertext) {
			   //$("#otp").val(crypto.ciphertext); 
			   $("#cover-spin").show();
			   
			   $.ajax({
				   url: "{{ url('verify-otp') }}",
				   type: 'POST',
				   data: { 
					   "username":"{{$detail->mobile}}",
					   "otp":crypto.ciphertext,
					   "_token": csrfToken,
				   },
				  
				   success: function (data) {
                        //console.log(data);					   
					   if (data.status) {
						  $('#div_send_otp').hide();
						  $('#div_verify_otp').show();
						  $("#cover-spin").hide();    
						  updateMsmeProvider(seller_provider_id,csrfToken);
						  toastr.success(data.message);
					   }else{ 
					    //alert(1);
						$("#verify-otp").html('Verify OTP');
						$("#verify-otp").attr("class", "btn btn-primary");                       
						toastr.error(data.message);
					   }
						$("#cover-spin").hide();  
				   },
				   error: function(xhr, status, error) {
					   //alert(2);
					   $("#verify-otp").html('Verify OTP');
						$("#verify-otp").attr("class", "btn btn-primary");
					   $("#cover-spin").hide();    
					   toastr.error(xhr.responseJSON.message);
				   }
			   });
		   }
	   } 
	   else { 
			//alert(3);
			$("#cover-spin").hide(); 
			$("#verify-otp").html('Verify OTP');
			$("#verify-otp").attr("class", "btn btn-primary");
			toastr.error("Please enter the required fields");
		   return;
	   }
   });
   
   function updateMsmeProvider(seller_provider_id,csrfToken){
	   $.ajax({
		   url: "{{ url('msme-snp-mapping') }}",
		   type: 'POST',
		   data: { 
			   "msme_id":"{{$detail->id}}",
			   "seller_provider_id":seller_provider_id,
			   "_token": csrfToken,
		   },
		  
		   success: function (data) { 
			   if (data.status) {
				  $("#cover-spin").hide();    
				  window.location.href=data.url;
				  toastr.success(data.message);
			   }else{                        
				toastr.error(data.message);
			   }
				$("#cover-spin").hide();  
		   },
		   error: function(xhr, status, error) {
			   $("#cover-spin").hide();    
			   toastr.error(xhr.responseJSON.message);
		   }
	   });
   }
   
   
    function cryptoJS(secret) {
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
   
   
   
     $("#resend-otp").click(function(e) {
       e.preventDefault();
       $.ajax({
           type: "POST",
           url: "{{ url('send-otp') }}",
           data: {
               "username":"{{$detail->mobile}}",
				"email":"{{$detail->email}}",
               _token: $('meta[name="csrf-token"]').attr('content'),
           },
           success: function(data) {
               if (data.status) {
                    startTimer();
                    clearInterval(interval);
                    timerInterval();
					toastr.success(data.message);
                    $("#otp_timer").show();
                    $("#resend-otp").hide();
               }
           }
       });
   });
   
   
   var interval;
   function timerInterval(){
       var counter = 0;
       interval = setInterval(function() {  
       if (counter > {{ \App\Http\Services\VerificationService::CODE_EXPIRATION_TIME }}) {stopTimer();
           $("#otp_timer").hide();
           $("#resend-otp").show();
       }
       counter++;
   },1000);
   }
   
   
  
</script>
@endsection