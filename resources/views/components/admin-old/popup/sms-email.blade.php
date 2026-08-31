

<div class="modal fade otp-modal" id="CommonModal" tabindex="-1" aria-labelledby="popModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header border-0 justify-content-center">
			<h5 class="modal-title" id="exampleModalLabel">Please verify the OTP</h5> 
			 <!-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="close"></button> -->
		  </div>
		  <div class="modal-body pt-0 px-5"> 
			<form>
			  <div class="text-center">
				<label for="recipient-name" class="col-form-label"> Please enter the OTP sent to you </label>
				<input type="password" name="otp" class="form-control otp-field integer kmw-disabled mb-2" id="otp" placeholder="OTP" maxlength="6">
				<input type="hidden" id="keyValue">
				<input type="hidden" id="keyType">
				<span class="text-danger form-error" id="otp_error"></span>
				<p>
				OTP will expire within <span class="fw-bold" id="otp_timer"></span> <!--{{ numberPrecision(\App\Http\Services\VerificationService::CODE_EXPIRATION_TIME/60,1) }}--> minutes  
				</p>
			  </div>
			</form>
		  </div>
		  <div class="align-items-center border-0 d-flex justify-content-center modal-footer p-0 pb-4 flex-column" style="display:block">
			<!--button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button-->
			<!--<div class="float-start">
				<button type="button" class="btn btn-primary" onclick="resendButton(this)" id="resendmbtn">Resend OTP</button>
			</div>-->
			
				<button type="button" class="btn btn-primary submit-btn" onclick="verifyButton(this)" id="verifymbtn">Verify</button>
				<!-- <button type="button" class="btn mt-2" data-bs-dismiss="modal" style="background: transparent;" aria-label="close" onclick="cancelRequest()">Close</button> 
				<button type="button" class="btn mt-2" data-bs-dismiss="modal" style="background: transparent;" aria-label="close">Close</button>-->
		  </div>
		</div>
	</div>
</div>