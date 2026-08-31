@extends('components.admin.layout')
@section('page-content')
<main>
<div class="container-fluid px-4 py-4">
<div class="row">
	<div class="col-lg-12 col-md-12">
		<div class="card container-main-card">
			<div class="card-header d-flex">
				<div class="heading">
					<h1>View Application query for "<i>{{$appData['script_title']}}</i>"</h1>
					<h3 class="form-sub-heading mt-2 mb-3"><span class="fw-bold">Application Ref. Id :
								{{$appData['national_permission_application_id']}}</span>
							</h3>
				</div>
				<div class="action-header ms-auto">
					<a href="javascript:void(0);" onclick = "javascript:history.back(-1);" class="btn btn-sm btn-primary"><img src="{{ asset('assets/ffo-admin/img/arrow-back-w.svg') }}"> Back</a>
				</div>

			</div>
			<div class="card-body pt-1 position-relative">
				
				<div class="row">
					<div class="col-lg-6">
						<form id="formId">
							<div class="row">
								<div class="col-lg-12 mb-3">
									<div class="input-box">
										<label class="form-label"><strong>Subject :- </strong></label>
										{{$row->subject}}
									</div>
								</div>
								<div class="col-lg-12 mb-3">
									<div class="input-box">
										<label class="form-label">Add your comments/remarks</label><span style="color:red;">*</span>
										<textarea class="form-control" rows="5" placeholder="" name="remarks" id="remarks"
										@if (isset($row) && $row->is_closed==1)
											disabled
										@endif 
										></textarea>
										<span class="text-danger form-error" id="remarks_error"></span>
									</div>
								</div>
								<div class="col-lg-12 mb-3">
								<div class="input-box">
										<label class="form-label">Upload document</label>
										<input type="file" class="form-control" name="upload_doc" id="upload_doc" accept="application/pdf" @if (isset($row) && $row->is_closed==1)
											disabled
										@endif >
										<small style="color:blue;font-size: 12px;"> {{ __('message.file_note') }}</small>
								<span class="text-danger form-error" id="upload_doc_error"></span>
										
									</div>
								</div>
								<div class="col-lg-12 mb-3">
									<!--<label class="form-label">Uploaded Documents : </label>	-->											
									<input type="hidden" name="uploaded_documents" id="uploaded_documents" value="[]" />
									<div class="progress-upload progress-bg mt-3" style="display:none;">
									<div id="loader-upload" style="width:347px;"></div>
									<div class="progress-bar"></div>
									</div>
									<ul class="downloads d-flex gap-2" id="fileList"></ul>
								</div>



								<div class="form-action mt-3 mb-3 d-flex align-items-center">
								
									<button id="cancel-btn" class="btn me-2 btn-warning" type="reset">Reset</button> 
									@if (isset($row) && $row->is_closed==0)
										<button type="submit" id="submit-btn" class="btn next-button">Send Query</button>
									@endif  
									
								</div>


							</div>
						</form>

					</div>
					<div class="col-lg-6">
						<div class="card query-card">
							<div class="card-header" style="background-color:beige;">
								<h5>Remarks<a href="javascript:void(0);" onclick="CreatePDFfromHTML('pdf-details','{{$appData['script_title']}}-Query',['doclist'])"><img src="{{ asset('assets/img-new/download-ico-p.svg')}}"></a></h5>
							</div>
							<div class="card-body pdf-details">
							<div style="display:none;" id='remarks-heading'> <h5>Remarks for <i>"{{$appData['script_title']}}"</i> </h5><span>Application Ref. Id : {{$appData['national_permission_application_id']}}</span><br /></div>
								<div class="query-box">
								@php $class='';@endphp
									@foreach($applicationQueryThread as $key=>$val)
									 @if(AuthId()==$val['created_by'])
										 @php $class = 'query-user d-flex justify-content-end'; $innerClass='end';	$outerClass='ffo-remark-col'; $queryBoxClass = 'query-box-right'; @endphp	
									
									@else
										@php $class = 'query-user d-flex justify-content-start';$innerClass='start';$outerClass='ffo-remark-col1'; $queryBoxClass = 'query-box-left'; @endphp	
									@endif
									<div class="query-item">
									<div class="{{$outerClass}}">
										<div class="{{$class}}">
											<span><img src="{{ asset('assets/ffo-admin/img/query-user.svg') }}"></span>
											<div class="content ms-2 justify-content-{{$innerClass}}">
												<h6 class="m-0">{{$val['heading']}} 
												@if(isset($val['created_name'])) 
														@if(AuthId()==$val['created_by'])
															<!--( You )-->
														@else
															( {{$val['created_name']}} )
														@endif
													@endif</h6>
												<p>{{$val['created']}}</p>
											</div>											
										</div>
										<p class="query" style="font-size:13px;">{!! nl2br($val['remark']) !!}</p>
										@if(count($val['documents'])>0)
										<!--<p class="query"><strong>Documents Attached</strong></p>-->
									<div class="action-btns-query text-{{$innerClass}} doclist">
										@foreach($val['documents'] as $docKey=>$docVal)
										
											<a class="btn btn-query-attach" style="font-size:11px;" target="_blank" href="{{ url('/download-file') }}/{{$docVal->file_id}}">
											{{$docVal->document_original_name}}&nbsp;&nbsp;<img src="{{ asset('assets/img-new/download-ico-p.svg')}}">
											</a>
											
										
										@endforeach
									</div>
										@endif
									</div>
									</div>
									@endforeach
								</div>
							</div>
						</div>
					</div>
				</div>
			   
			</div>
		</div>
	</div>
</div>

</div>
@include('components.admin.popup.htmltopdf');
@include('components.admin.bootstrap-dialog');
</main>
<script>
$("#formId").on("submit", function (event) {
	event.preventDefault();
	var national_permission_application_id = "{{ $row->national_permission_application_id }}"; 
	var csrfToken = "{{ csrf_token() }}";
	var remarks = $("#remarks").val();	
	var applicant_id = "{{ $row->applicant_id }}";	
	var workflow_type = "{{ (int)$row->query_type}}";
	var files = JSON.parse($("#uploaded_documents").val());	
	var method = 'POST';
	var url = "{{ url('/add-application-remarks') }}";


	var requestData = {
		url: url,
		method: method,
		body: {
			national_permission_application_id: national_permission_application_id,
			_token: csrfToken,
			remarks:remarks,
			documents :files?files:'',
			applicant_id :applicant_id,
			applicant_query_id :"{{$row->id}}",
			workflow_type:workflow_type
		}
	};
	var response = sendRequest(requestData, "{{ url('/view-application-queries') }}/{{$row->id}}");
	//sendLegalRequest(requestData, "");
	
});
$("#upload_doc").on("change", function() {
	//var response = fileUploadWithLoader({
	var response = assignFileUploadWithLoader({
		url: "{{url('upload-query-docs')}}",
		selector: "#upload_doc",
		fileFieldName: 'file',
		hiddenInputSelector: '#uploaded_documents',
		progressElementSelector: '.progress-upload',
		loaderElementSelector: '#loader-upload',
		loadingContent: 'uploading...',
		successMessage: 'Successfully uploaded.',
		errorMessage: 'Error could not upload!',
		isMultiple: true
	});
	
});


//To delete Document 
$(document).on('click', 'ul#fileList li a.remove-doc', function() {
		var delete_doc_file = $(this).closest('li').attr('uploaded-doc-name');
		var delete_doc_id = $(this).closest('li').attr('id');
		var csrfToken = "{{ csrf_token() }}";
		var method = 'POST';
		var url = "{{ url('/delete-query-docs') }}";
		BootstrapDialog.confirm({
		title: 'Delete File?',
		message: 'Do you really want to delete?',
		callback: function(result){
			if(result) {
				event.preventDefault();
				var requestData = {
					url: url,
					method: method,
					body: {
						file_name : delete_doc_file,
						_token: csrfToken
					}
				};
				sendRequest(requestData, "");
				const arrayFromString = JSON.parse($('#uploaded_documents').val());
				var newVal = removeValueFromArray(arrayFromString, delete_doc_file) ;		
				$('#uploaded_documents').val(JSON.stringify(newVal));
				$('#upload_doc').val('');
				
				document.getElementById(delete_doc_id).remove();
				enableSubmit();
			}
		}
		});	
	/*if(confirm('Do you realy want to delete the file?')){
		var delete_doc_file = $(this).closest('li').attr('uploaded-doc-name');
		var csrfToken = "{{ csrf_token() }}";
		var method = 'POST';
		var url = "{{ url('/delete-query-docs') }}";
		var requestData = {
			url: url,
			method: method,
			body: {
				file_name : delete_doc_file,
				_token: csrfToken
			}
		};
		sendRequest(requestData, "");
		$(this).closest('li').remove();
		const arrayFromString = JSON.parse($('#uploaded_documents').val());
		var newVal = removeValueFromArray(arrayFromString, delete_doc_file) ;		
		$('#uploaded_documents').val(JSON.stringify(newVal));
		$('#upload_doc').val('');
		enableSubmit();
	}*/	
});
function removeValueFromArray(array, valueToRemove) {
    return array.filter(item => item !== valueToRemove);
}


var docs = [];
    function asyncFileUploadLegal(asyncFileUploadOptions) {
        const {
            url,
			selector,
            fileFieldName,
            formData,
            hiddenInputSelector,
            progressElementSelector,
            loaderElementSelector,
            loadingContent,
            successMessage,
            errorMessage,
			isMultiple
        } = asyncFileUploadOptions;

        $.ajax({
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(element) {}, false);
                return xhr;
            },
            url: url,
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            beforeSend: function() {
                $(progressElementSelector).show();
                $(loaderElementSelector).html(
                    `<i class="fa fa-spinner fa-spin"></i> <span>${loadingContent}</span>`);

            },
            success: function(response) {
                if (response.status) {
                    var data = response.data;
					if(isMultiple){
						var documents = $(hiddenInputSelector).val();
						
						documents = JSON.parse(documents);
						documents.push(data.file_system_name);
						$(hiddenInputSelector).val(JSON.stringify(documents));
						var children = ''; 
				        var input =  $(selector).prop('files');
						children += '<li id ="'+ response.data.id+ '" uploaded-doc-name ='+ response.data.file_system_name+ '>' + input[0]['name'] + '<a href="javascript:void(0);" class="remove-doc"><img src="{{ asset('assets/ffo-admin/img/delete-ico-p.svg') }}"></a></li>';
						$("#fileList").append(children);
					} else {
						$(hiddenInputSelector).val(data?.file_system_name);
					}
                    
                    toastr.success(successMessage);
                    $(progressElementSelector).delay(1000).fadeOut('slow');
				
                } else {
                    $(progressElementSelector).delay(1000).fadeOut('slow');
                    /*changes start by Abhishek*/
					var prevValue = $(hiddenInputSelector).val();
                    $(hiddenInputSelector).val(prevValue);
					/*changes end by Abhishek*/
                    var errors = response.errors;
                    toastr.error(errors.file[0]);
					if(errors.file[0]){
						$(selector).val('');
					}
                    return;
                }
            },
            error: function(response) {
                /*changes start by Abhishek*/
                //$(hiddenInputSelector).val('');
				var prevValue = $(hiddenInputSelector).val();
				$(hiddenInputSelector).val(prevValue);
				/*changes end by Abhishek*/
                toastr.error(errorMessage);
                return;
            }
        });
    }

    function assignFileUploadWithLoader(fileUploadOptions) {
        const {
            url,
            selector,
            fileFieldName,
            hiddenInputSelector,
            progressElementSelector,
            loaderElementSelector,
            loadingContent,
            successMessage,
            errorMessage,
			isMultiple
        } = fileUploadOptions;

        const images = $(selector)[0].files;

        if (!images.length > 0) {
            throw new Error("Invalid file upload..");
        }

        const formData = new FormData();

        formData.append(fileFieldName, images[0]);
        formData.append('_token', '{{ csrf_token() }}');

        asyncFileUploadLegal({
            url,
			selector,
            fileFieldName,
            formData,
            hiddenInputSelector,
            progressElementSelector,
            loaderElementSelector,
            loadingContent,
            successMessage,
            errorMessage,
			isMultiple
        });
    }
	
	
</script>
@endsection