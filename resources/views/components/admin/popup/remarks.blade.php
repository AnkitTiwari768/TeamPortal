<!-- Modal -->
<div class="modal fade " id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="confirmationModallLabel">Forward To SE</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body pb-1">
          
          <h6 id="msg-modal"></h6>
          <input type="hidden" id="modal-confirm-url" />
          <input type="hidden" id="modal-confirm-assignment-id" />
          <input type="hidden" id="modal-confirm-app-id" />
          <input type="hidden" id="modal-confirm-to-user-id" />
          <input type="hidden" id="modal-workflow-type"  />
          <input type="hidden" id="csrf-token" value="{{ csrf_token() }}">

          
          <table class="table table-bordered table-striped" id="form-div">
            <tr>
              <th>{{ __('national_application.application_id') }}</th>
              <td id="application_no"></td>
            </tr>
            <tr>
              <th>Application Date</th>
              <td id="app_date"></td>
            </tr>
            <tr class="current_remarks">
              <th>Remarks<span style=" color: red;">*</span></th>
              <td>
                <input type="text" class="form-control required" placeholder="Write remarks" id="current_remarks" name="current_remarks">
                <span class="text-danger form-error" id="remarks_error"></span>
              </td>
            </tr>
            <tr class="upload_doc_se">
              <th>Upload Document</th>
              <td>							
                <input type="file" class="form-control" name="upload_doc" id="upload_doc_se" accept="application/pdf" placeholder="Upload Document" />
                <small>{{ __('message.file_note') }}</small>
				<span class="text-danger form-error" id="upload_doc_error"></span>
                <input type="hidden" name="uploaded_documents[]" id="uploaded_documents" value="[]" />
                <div class="progress-upload progress-bg mt-3" style="display:none;">
                <div id="loader-upload" style="width:347px;"></div>
                <div class="progress-bar"></div>
                </div>
              </td>
            </tr>
          </table>
          <ul class="downloads d-flex gap-2" id="fileList"></ul>
        </div>
        <div class="modal-footer pt-0 border-0 justify-content-start" id="action-button" >
          <button type="button" class="btn btn-success confirm-request" data-dismiss="modal" id="approve" data-action="{{\App\Enums\ReviewStatus::Approve->value}}" >Approve</button>
          <button type="button" class="btn btn-danger confirm-request" data-dismiss="modal" id="reject" data-action="{{\App\Enums\ReviewStatus::Reject->value}}">Reject</button>
         
          <button type="button" class="btn btn-primary confirm-request" id="draft" data-action="{{\App\Enums\ReviewStatus::InProgress->value}}">Save</button>
          <button type="button" class="btn btn-primary confirm-request" data-dismiss="modal" id="forward" data-action="{{\App\Enums\ReviewStatus::InProgress->value}}" >Forward</button>
        </div>
        <div id="history" class="modal-body">
          </div>
      </div>
    </div>
  </div>
@include('components.admin.bootstrap-dialog')
  <script>

    $("#upload_doc_se").on("change", function() {
			fileUploadWithLoader({
				url: "{{url('upload-document')}}",
				selector: "#upload_doc_se",
				fileFieldName: 'file',
				hiddenInputSelector: '#uploaded_documents',
				progressElementSelector: '.progress-upload',
				loaderElementSelector: '#loader-upload',
				loadingContent: 'uploading...',
				successMessage: 'Successfully uploaded.',
				errorMessage: 'Error could not upload!',
        isMultiple: true,
        showFileName:''
			});
     
		});
		
		//To delete Document 
		$(document).on('click', '.remove-doc', function() {
			var delete_doc_file = $(this).closest('li').attr('uploaded-doc-name');
			var delete_doc_id = $(this).closest('li').attr('id');
			var uploaded_documents =  JSON.parse($(this).parent('li').parent().siblings('.table').find("#uploaded_documents").val());
			var csrfToken = "{{ csrf_token() }}";
			var method = 'POST';
			var url = "{{ url('/delete-document') }}";
			
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
					$('#upload_doc_se').val('');
					document.getElementById(delete_doc_id).remove();
					
				}
			}
			});
	
		/*if(confirm('??')){	
			var delete_doc_file = $(this).closest('li').attr('uploaded-doc-name');
			var delete_doc_id = $(this).closest('li').attr('id');
			var uploaded_documents =  JSON.parse($(this).parent('li').parent().siblings('.table').find("#uploaded_documents").val());
			var csrfToken = "{{ csrf_token() }}";
			var method = 'POST';
			var url = "{{ url('/delete-document') }}";
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
	  $('#upload_doc_se').val('');
	  document.getElementById(delete_doc_id).remove();
		}
		$(this).parent('li').parent().siblings('.table').find("#upload_doc_se").val('');
		var updatedArr = removeItemOnce(uploaded_documents, delete_doc_file);
		updatedArr = JSON.stringify(updatedArr);
		//console.log(updatedArr);
		$(this).parent('li').parent().siblings('.table').find("#uploaded_documents").val(updatedArr);
		$(this).closest('li').remove();
		//$('#upload_doc_se').val('');
		*/
		});
		
	function removeValueFromArray(array, valueToRemove) {
		return array.filter(item => item !== valueToRemove);
	}
    function removeItemOnce(arr, value) {
			 var index = arr.indexOf(value);
			  if (index > -1) {
				arr.splice(index, 1);
			  }
			  return arr;
			console.log(arr);
		}
		
  </script>
  