@extends('components.admin.layout')

@section('page-content')

<div class="container-fluid"> 

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">   
				 <h6 class="m-0 font-weight-bold text-primary">
                        @isset($id)
                            {{ __('slider.edit_slider') }}
                        @else 
                            {{ __('slider.add_slider') }}
                        @endisset
                    </h6>
				@include('components.admin.buttons.back-button')
                </div>
                <div class="card-body">
                    <form id="form">
                        <div class="row"> 
                           
							<div class="col-md-6 mb-3">
                                <label class="required">{{ __('slider.title_en') }}</label>
                                <input type="text" class="form-control" name="title_en" id="title_en" placeholder="{{ __('slider.title_en') }} {{ __('slider.title_en_mr') }}"
                                    @if (!empty($row['title_en']))
                                        value="{{ $row['title_en'] }}"
                                    @endif
                                >
                                <span class="text-danger form-error" id="title_en_error"></span>
                            </div>
							
							<div class="col-md-6 mb-3">
                                <label class="required">{{ __('slider.title_mr') }} {{ __('slider.title_mr_mr') }}</label>
                                <input type="text" class="form-control" name="title_mr" id="title_mr" placeholder="{{ __('slider.title_mr') }} {{ __('slider.title_mr_mr') }}"
                                    @if (!empty($row['title_mr']))
                                        value="{{ $row['title_mr'] }}"
                                    @endif
                                >
                                <span class="text-danger form-error" id="title_mr_error"></span>
                            </div>
							
							<div class="col-md-6 mb-3">
                                <label class="required">{{ __('slider.type') }} {{ __('slider.type_mr') }}</label>
                                 {!! Form::select('type',['' => __('slider.select')." ".__('slider.select_mr') ]+$slider_type, $row['type'] ?? null, ['class' => 'form-control', 'id' => 'type']) !!}  

                                 

                                <span class="text-danger form-error" id="type_error"></span>
                            </div> 
							
							<div class="col-md-6 mb-3">
                                <label class="required" id="url_class">{{ __('slider.url') }} {{ __('slider.url_mr') }}</label>
                                <input type="text" class="form-control" name="url" id="url" placeholder="{{ __('slider.url') }} {{ __('slider.url_mr') }}"
                                    @if (!empty($row['url']))
                                        value="{{ $row['url'] }}"
                                    @endif
                                >
                                <span class="text-danger form-error" id="url_error"></span>
                            </div>
							
							
							<div class="col-md-6 mb-3" style="display:none" id="top_slider">
                                <label class="required" id="class_top_slider">{{ __('slider.sort_order') }} {{ __('slider.sort_order_mr') }}</label>
                                {!! Form::select('sort_order',['' => __('slider.select')." ".__('slider.select_mr') ]+ $top_slider_sort_order, $row['sort_order'] ?? null, ['class' => 'form-control top_sort_order', 'id' => 'sort_order']) !!}   

                                 

                                <span class="text-danger form-error" id="sort_order_error"></span>
                            </div> 
							
							
							<div class="col-md-6 mb-3" style="display:none" id="bottom_slider">
                                <label class="required" id="class_bottom_slider">{{ __('slider.sort_order') }} {{ __('slider.sort_order_mr') }}</label>
                                {!! Form::select('sort_order',['' => __('slider.select')." ".__('slider.select_mr') ]+ $bottom_slider_sort_order, $row['sort_order'] ?? null, ['class' => 'form-control bootom_sort_order', 'id' => 'sort_order']) !!}   
                                <span class="text-danger form-error" id="sort_order_error"></span>
                            </div> 
							
                        
                            <div class="col-md-6 mb-3">
                                <label class="required">{{ __('slider.status') }} {{ __('slider.status_mr') }}</label>
                                {{ Form::select('is_active', status_list(), $row['is_active'] ?? null, ['class' => 'form-control', 'id' => 'is_active']) }}   
                                <span class="text-danger form-error" id="is_active_error"></span>
                            </div> 
							
							<div class="col-md-6 mb-3">
                                <label class="required">{{ __('slider.upload_image') }} {{ __('slider.upload_image_mr') }}</label>
								<input type="file" id="images" name="images" class="form-control" accept="image/*"/>
                                <input type="hidden" name="images_file_uploads" id="images_file_uploads" value="@isset($row) {{ $row['images'] }} @endisset" />
                                <div class="progress progress-bg mt-3" style="display:none;"><div id="loader" style="width:300px;"></div><div class="progress-bar"></div></div>
                                <span class="text-danger form-error" id="images_error"></span>
                            </div>
							<div class="col-md-6 mb-3 preview" 
							@if (empty($row['images'])) style="display:none" @endif >
                                <img id="preview" src="@isset($row) {{ url($row['images'])}} @else {{ url('img/preview.png') }} @endisset" class="img-thumbnail" style="width:160px; height: 120px" />
                            </div>
							 
                        </div>
				

						@include('components.admin.buttons.submit-button')
						@include('components.admin.buttons.cancel-button')
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
    
	
    <script>
          $("#form").on("submit", function (event) {
            event.preventDefault();
            var title_en = $("#title_en").val();
			var title_mr = $("#title_mr").val();
			var urls = $("#url").val();
			var type = $("#type").val();
			var sort_order = $("#sort_order").val();
            var is_active = $("#is_active").val();
			var images = $("#images_file_uploads").val();
			
			var csrfToken = "{{ csrf_token() }}";
            
            @isset($id)
                var method = 'PUT';
                var url = "{{ url('/sliders/'. $id) }}";
            @else 
                var method = 'POST';
                var url = "{{ url('/sliders') }}";
            @endisset

            var requestData = {
                url: url,
                method: method,
                body: {
					title_en: title_en,
					title_mr: title_mr,
					url: urls,
					type:type,
					images:images,
					sort_order: sort_order,
                    is_active:is_active,
                    _token: csrfToken
                }
            };

            sendRequest(requestData, "{{ url('sliders') }}");
        });
		
		var type = $('#type').val();
		if(type !=''){
			type_function(type);
		}
		$("#type").on("change", function() {
			var type = $('#type').val();
			type_function(type);
		});
		
		function type_function(type){
			if(type==1){
				$("#top_slider").show();
				$("#bottom_slider").hide();
				//$("#sort_order option:selected").val("");
				$(".bottom_sort_order").removeAttr("id");
				$(".top_sort_order").attr("id","sort_order");
				$("label#class_top_slider").addClass("required");
				$("label#class_bottom_slider").removeClass("required");
				$("label#url_class").removeClass("required");
				
			}else{
				$("#bottom_slider").show();
				$("#top_slider").hide();
				//$("#sort_order option:selected").val("");
				$(".top_sort_order").removeAttr("id");
				$(".bottom_sort_order").attr("id","sort_order");
				$("label#class_top_slider").removeClass("required");
				$("label#class_bottom_slider").addClass("required");
				$("label#url_class").addClass("required");
			}
		}
		
		
		
		$("#images").on("change", function() {
            var images = $('#images')[0].files;
			var type = $('#type').val();
			if(type ==''){
				alert("Please select type");
				return false;
			}
            if(images.length > 0) {
                var formData = new FormData();
                formData.append('images', images[0]);
				formData.append('type',type);
                formData.append('_token','{{ csrf_token() }}');
                $.ajax({
					xhr: function() {
					var xhr = new window.XMLHttpRequest();         
					xhr.upload.addEventListener("progress", function(element) {
					}, false);
					return xhr;
					},
                    url: "{{url('SliderImage')}}",
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
					if (response.status) {
						var data = response.data;
						$("#images_file_uploads").val("storage/app/"+data);
						$(".preview").show();
						$("#preview").attr("src", "{{ url('/storage/app')}}" +"/"+ data);
						toastr.success("Successfully uploaded.");
						$('.progress').delay(1000).fadeOut('slow');

					} else {
						$('.progress').delay(1000).fadeOut('slow');
						var errors = response.errors;
                            for (var prop in errors) {
                                toastr.error(errors[prop][0]);
                            }
						if(response.message !=null){
							toastr.error(response.message);
						}
						return;
					}
					},
					error: function(response){
						console.log(response.errors);
					}
                    /*success: function(response){
                        if (response.status) {
                            var data = response.data;
                                
								var output = '';
                                //output += '<img src="'+BASE_URL+"/storage/app/"+data+'" class="img-thumbnail" style="width:120px; height: 120px" /><i class="fa fa-times text-danger p-1 delete-image" style="cursor: pointer;" onclick="deleteSliderImage(\''+"storage/app/"+data+'\',this);"></i>';
								
								//output += '<img src="'+BASE_URL+"/storage/app/"+data+'" class="img-thumbnail" style="width:120px; height: 120px" /><i class="fa fa-times text-danger p-1 delete-image" style="cursor: pointer;" onclick="deleteSliderImage(\''+"storage/app/"+data+'\',this);"></i>';
                               
								$("#images-container").html(output);
								$("#images_file_uploads").val("storage/app/"+data);
								 toastr.success("File has uploaded successfully!");
								 $('.progress').delay(1000).fadeOut('slow');
								 //$('#loader').hide();
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
							$("#images-container").remove();
							$("#images").val('');
							$("#images_file_uploads").val("");
                        }
                    },
                    error: function(response){
                        console.log("error : " + JSON.stringify(response) );
                    }*/
                });
            }
        });
		
		
		$("#btn-browse-images").click(function() {
            $("#images").trigger("click");
        });
		
		function deleteSliderImage(files, element) {
			var type = $('#type').val();
            $.ajax({
                url: "{{ url('deleteSliderImage') }}",
                method: "POST",
                dataType: "json",
                data: {
                    files: files,
					type:type,
                    _token: "{{ csrf_token() }}"
                },
                success: function (response) {
					 $("#images-container").remove();
                     $("#images").val('');
                     $("#images_file_uploads").val("");
                }   
            });
        }
		
		
    </script>

@endsection









