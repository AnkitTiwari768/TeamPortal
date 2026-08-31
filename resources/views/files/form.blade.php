@extends('components.admin.layout')

@section('page-content')
<style>
.vl {
  border-left: 1px solid green;
  height: 100px;
}
</style>
<!-- <div class="container-fluid"> 

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">   
				 <h6 class="m-0 font-weight-bold text-primary">{{ __('media.add_file') }}</h6>
				@include('components.admin.buttons.back-button')
                </div>
                <div class="card-body"> -->
       <div class="container-fluid px-4 py-4">     
	<div class="row">
		<div class="col-lg-12 col-md-12">
			<div class="card container-main-card">
				<div class="card-header d-flex">
					<div class="heading">
						 <h1 class="m-0 font-weight-bold text-primary">{{ __('media.add_file') }}</h1>
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="#">Home</a></li> 
								<li class="breadcrumb-item"><a href="#">{{ __('media.add_file') }}</a></li>
							</ol>
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

                        <div class="row"> 
                            <div class="col-md-6 mb-3 border-right" style="border-right:10px solid #e3e6f0!important">
							<b><label>{{ __('media.file_en') }} </label></b>
							<hr>
							 <form id="uform">
                                <label class="required">{{ __('media.select_folder_en') }} </label>
								<select name="select_folder" id="select_folder" class="form-control">
								<option value="">{{ __('media.select_folder_en') }}  </option>
									<?php 
										$normalized_folders = [];
										foreach ($folders as $key => $folder)
										{
											
											$normalized_folders[] = pathinfo($folder, PATHINFO_DIRNAME);
										}
										$normalized_folders = array_values(array_unique($normalized_folders));
									?>
									
									<?php foreach($normalized_folders as $key=>$nz_folder){ ?>
										<option value="<?php echo str_replace("storage/app/uploads/pages","pages",$nz_folder);?>"><?php echo str_replace("storage/app/uploads/pages","pages",$nz_folder);?></option>
									<?php } ?>
							   </select>
							    <br>
                                <label class="required">{{ __('media.file_upload_en') }}  </label>
                                <input type="file" class="form-control" name="images" id="images">
                                <div class="progress progress-bg" style="display:none;"><div id="loader"></div><div class="progress-bar"></div></div>
		
							  </form>
							  <small class="mt-2">{{ __('media.maximum_file') }}</small>
                            </div>
                           

                            <div class="col-md-6 mb-3">
							 <b><label>{{ __('media.create_folder_en') }}  </label></b>
							 <hr>
							 <form id="create_folder">
                                <label class="required">{{ __('media.select_folder_en') }}  </label>
                                <select name="select_folders" id="select_folders" class="validate[required] form-control">
										<option value="">{{ __('media.select_folder_en') }}   </option>
										
										<?php 
											$normalized_folders = [];
											foreach ($folders as $key => $folder)
											{
												if($folder !='img/pages/dir/.htaccess')
												{
												$normalized_folders[] = dirname($folder, PATHINFO_DIRNAME);
												}
											}
											$normalized_folders = array_values(array_unique($normalized_folders));
										?>
										
										<?php foreach($normalized_folders as $key=>$nz_folder){ ?>
										
										<option value="<?php echo str_replace("storage/app/uploads/pages","pages",$nz_folder);?>"><?php echo str_replace("storage/app/uploads/pages","pages",$nz_folder);?></option>
										<?php } ?>
									</select>
									<span class="text-danger form-error" id="select_folder_error"></span>
								<br>
                                <label class="required">{{ __('media.folder_name_en') }}  </label>
                                <input type="text" class="form-control txtOnly" name="folder_name" id="folder_name" placeholder="{{ __('media.folder_name_en') }}">
                                <span class="text-danger form-error" id="folder_name_error"></span>
								<br>
									@include('components.admin.buttons.submit-button')
									@include('components.admin.buttons.cancel-button')
								 </form>
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
     //for upload folder
		$("#images").on("change", function() {
            var images = $('#images')[0].files;
			var select_folder = $('#select_folder').val();
			if(select_folder ==''){
				alert("Please select select folder");
				$("#images").val('');
				return false;
			}
            if(images.length > 0) {
                var formData = new FormData();
                formData.append('images', images[0]);
				formData.append('select_folder',select_folder);
                formData.append('_token','{{ csrf_token() }}');
                $.ajax({
					xhr: function() {
					var xhr = new window.XMLHttpRequest();         
					xhr.upload.addEventListener("progress", function(element) {
					}, false);
					return xhr;
					},
                    url: "{{url('/media/UploadFile')}}",
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
					beforeSend: function(){
						$('.progress').show();
						$('#loader').html('<i class="fa fa-spinner fa-spin"></i> <span>wait in processing...</span>');
					},
                    success: function(response){
						
                        if (response.status !=false) {
							 toastr.success("File has uploaded successfully!");
							 $('.progress').delay(1000).fadeOut('slow');
							 $('#loader').hide();
							 $("#images").val('');
                        }
                        else {
							var errors = response.errors;
                            for (var prop in errors) {
                                toastr.error(errors[prop][0]);
                            }
							if(response.message !=null){
								toastr.error(response.message);
							}
							$('.progress').delay(1000).fadeOut('slow');
							$("#images").val('');
                        }
                    },
                    error: function(response){
                        toastr.error("File not uploaded successfully!");
                    }
                });
            }
        });
	
    //for create folder
	$("#create_folder").on("submit", function (event) {
		event.preventDefault();
		var select_folders = $("#select_folders").val();
		var folder_name = $("#folder_name").val();
		var csrfToken = "{{ csrf_token() }}";
		var method = 'POST';
		var url = "{{ url('/media/store') }}";
		var requestData = {
			url: url,
			method: method,
			body: {
				select_folders: select_folders,
				folder_name: folder_name,
				_token: csrfToken
			}
		};

		sendRequest(requestData, "{{ url('media') }}");
	});
	
$(".txtOnly").on("keypress keyup blur input paste",function (e) {
	  var regex = new RegExp("^[a-zA-Z-_]+$");
	  var key = String.fromCharCode(!e.charCode ? e.which : e.charCode);
		if (!regex.test(key)) {
		   e.preventDefault();
		   return false;
		}
	return true;
});

</script>

@endsection









