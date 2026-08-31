@extends('components.admin.layout')
@section('page-content')
<div class="container-fluid">     
	<div class="row">
		<div class="col-md-12">
			<div class="card shadow mb-4">
			
				<div class="card-header py-3 d-flex flex- align-items-center justify-content-between"> 
					<h6 class="m-0 font-weight-bold text-primary">MSME and SNP Mapping</h6>
					@include('components.admin.buttons.back-button')
				</div>
				<div class="card-body">
					<div class="row g-3">
						<div class="form-group col-md-12">
							<label class="form-label required">Enter the BPPId.ProviderID </label>
							<input type="text" class="form-control" name="bpp_id" id="bpp_id" placeholder="Enter the BPPId.ProviderID" required>
							<span class="text-danger form-error" id="bpp_id_error"></span>
						</div>
						<div class="row mt-2">

						  <div class="form-group col-md-2">
							<button type="button" class="btn btn-primary w-100" id="mapping">Submit</button>
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
   
    $("#mapping").on("click", function(e) {
		e.preventDefault();
       var csrfToken = "{{ csrf_token() }}";
	   var bpp_id = $("#bpp_id").val();
	   if (bpp_id) {
		$("#cover-spin").show();
		updateMsmeProvider(bpp_id,csrfToken);
		toastr.success(data.message);
					   
	   } 
   });
   
   function updateMsmeProvider(bpp_id,csrfToken){
	   $.ajax({
		   url: "{{ url('msme-snp-mapping') }}",
		   type: 'POST',
		   data: { 
			   "msme_id":"{{$detail->id}}",
			   "bpp_id":bpp_id,
			   "_token": csrfToken,
		   },
		  
		   success: function (data) { 
			   if (data.status) {
				  $("#cover-spin").hide();    
				   toastr.success(data.message);
					setTimeout(function(){
						window.location.href = data.url;
					}, 1200);
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

</script>
@endsection