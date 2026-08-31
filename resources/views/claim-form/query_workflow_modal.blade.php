<style>
.remove-doc{display:none}
</style>
<div class="modal fade" id="workflowModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="workflowModalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <input type="hidden" id="workflow_batch_id">
			
			
            <div class="modal-body">
			  
				
			<div class="input-box" id="input-box">
				<div id="div_drfts">
						<?php /*<input type="hidden" id="workflow_document_category_id" value="{{$documentDeclarationCategory->id}}">
						
						<label class="form-label">Upload Declaration<a class="tooltip-ins"  href="#" data-toggle="tooltip" title="{{ $documentDeclarationCategory->informations }}"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
						
						<a href="{{ url('storage/app/download_format/Format for declaration by SNP On letter head.pdf') }}" download>Download Sample Pdf Format</a></label>*/?>

						<input type="hidden" id="workflow_document_category_id" value="1c76b7bf-6649-4ad1-b16b-e2604753bf35">

						<label class="form-label">Upload Invoice<a class="tooltip-ins"  href="#" data-toggle="tooltip" title=""><i class="fa fa-question-circle" aria-hidden="true"></i></a>
							
				

						<input type="file" class="form-control" id="upload"/>
						<!--<div class="upload_file_link"></div>-->
						<input type="hidden" name="workflow_file_upload_id" id="workflow_file_upload_id" /> 
						<div class="progress-upload progress-bg" style="display:none;">
							<div id="loader-upload" style=""></div>
							<div class="progress-bar"></div>
						</div> 
						</div>
					</div>
				
						
				<div class="input-box mt-2">		
					Remarks <textarea class="form-control" id="workflow_comments" required></textarea>
				</div>
            </div>
			
            <div class="modal-footer">
                <button type="button" class="btn" id="workflowButton" data-bs-dismiss="modal"></button>
            </div>
        </div>
    </div>
</div>


@push('js')
<script>
	$.ajaxSetup({
		headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
	});
	
	$('#workflowModal').on('hidden.bs.modal', function() {
		$('#upload').val('');
	});


	$("#upload").on("change", function(e) {
		fileUploadWithLoader({
		  url: "{{url('upload-ca-certificate')}}",
		  selector: "#upload",
		  fileFieldName: 'file',
		  hiddenInputSelector: '#workflow_file_upload_id',
		  progressElementSelector: '.progress-upload',
		  loaderElementSelector: '#loader-upload',
		  loadingContent: 'uploading...',
		  successMessage: 'Successfully uploaded.',
		  errorMessage: 'Invalid file type',
		  showFileName:'.upload_file_link'
		});
	});

	// Open modal dynamically
	$('#workflowModal').on('show.bs.modal', function(event) {
		var button = $(event.relatedTarget); 
		var batch_id = button.attr('batch-id'); 
		var claim_id = button.attr('claim-id'); 
		

		// Set modal values
		$('#workflow_batch_id').val(batch_id);
		$('#workflow_claim_id').val(claim_id);

		$('#workflowModalTitle').text('Proceed');
		$('#workflowButton').text('Proceed').removeClass('btn-success btn-info btn-danger').addClass('btn-primary');
			
	});



	$(document).on("click", "#workflowButton", function(e) {
		e.preventDefault();

		var batchId = $('#workflow_batch_id').val();
		var comments = $('#workflow_comments').val();
		var workflow_file_upload_id = $('#workflow_file_upload_id').val();
		var workflow_document_category_id = $('#workflow_document_category_id').val();

		if(comments.trim() === '' || !batchId){
			toastr.error("Please enter comments");
			return false;
		}

		$.ajax({
			url: "{{ url('batch-query-proceed') }}",
			type: 'POST',
			data: {batch_id: batchId, comments: comments,file_upload_id:workflow_file_upload_id,document_category_id:workflow_document_category_id },
			success: function(res) {
				toastr.success(res.message);
				setTimeout(function() {
					window.location.reload();
				}, 1000);
			},
			error: function(xhr) {
				toastr.error(xhr.responseJSON.message);
			}
		});
	});


	// Clear modal on close
	$('#workflowModal').on('hidden.bs.modal', function () {
		$(this).find('textarea').val('');
		$(this).find('select').prop('selectedIndex', 0);
	});
</script>
@endpush
