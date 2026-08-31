@extends('components.admin.layout')
@section('page-content')
<div class="container-fluid px-4 py-4">     
	<div class="row">
		<div class="col-lg-12 col-md-12">
			<div class="card container-main-card">
				<div class="card-header d-flex">
					<div class="heading">
						<h1>
							 {{ __('Complete MSE Registration') }}
						</h1>
						<nav aria-label="breadcrumb">
							
						</nav>	
					</div>
					<div class="action-header ms-auto">
						<!-- split button -->
						<div class="btn-group drop-btn">
							@include('components.admin.buttons.back-button')
						</div>
					</div>
					
				</div>
				<div class="card-body pt-1">
					<form id="formId"> 
						<div class="row">
							<div class="col-lg-4 mb-3">
								<div class="mb-0">
									<label class="form-label required">Udyam Number </label>
									<input type="text" class="form-control udyamNumber" name="udyam_no" maxlength="19" placeholder="Enter udyam number" id="udyam_no" required value="{{$detail->udyam_no}}">
								</div>
								<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="udyam_no_error"></span>
							</div>

							<div class="col-lg-4 mb-3">
								<div class="mb-0">
									<label class="form-label required">Mobile </label>
									<input type="text" class="form-control numeric" name="mobile" maxlength="10" placeholder="Enter Mobile" id="mobile" value="{{$detail->mobile}}" required readonly>
								</div>
								<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="mobile_error"></span>
							</div>

							<div class="col-lg-4 mb-3">
								<div class="mb-0">
									<button type="button" id="udyamDetails" class="input-text btn btn-primary btn-themed mt-4">Validate </button>
								</div>
							</div>
						</div>
						<div id="udyamDetailsContainer"></div>
												
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

@section('js');


<script>
	$.ajaxSetup({
		headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
	});


		$('#udyamDetails').on('click', function () {
			var udyam_no=$("#udyam_no").val();
			var mobile=$("#mobile").val();
            if(udyam_no == '' || mobile == ''){
                toastr.error('Please enter Udyam Registration No. and Mobile Number');
                return false;
            }
          
			$("#cover-spin").show();
            $.ajax({
                url: '{{ route("udyam-details") }}',
                type: 'POST',
				data:{udyam_no:udyam_no,mobile:mobile},
                success: function (response) {
					console.log(response);
					if(response.status==false){
						$("#cover-spin").hide();
						toastr.error(response.msg);
						$('#udyamDetailsContainer').html('');
					}else{
						$("#cover-spin").hide();
						$('#udyamDetailsContainer').html(response);
					}
                },
                error: function () {
					$("#cover-spin").hide();
					toastr.error('Udyam api is not working.Please try after some times');
                }
            });
        });
    
    
	
	$("#formId").on("submit", function (event) {
		event.preventDefault();
		var formData=$("#formId").serializeArray();   
		var method = 'POST';
		var url = "{{ url('/applicant-update/'. $detail->id) }}";

		var requestData = {
			url: url,
			method: method,
			body: formData
		};

		sendRequest(requestData, "{{ url('open-msme') }}");
	});

    


</script>
@endsection
@endsection

