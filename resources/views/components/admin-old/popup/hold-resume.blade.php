  <!-- Popup modal start here -->
  <div class="modal fade" id="holdModal" tabindex="-1"
	aria-labelledby="holdModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="holdModalLabel"></h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"
					aria-label="Close"></button>
			</div>
			<div class="modal-body pb-1">
				
				@if(isset($details))
				<table class="table table-bordered table-striped">
					<input type="hidden" id="modal-confirm-url-hold" />
					<input type="hidden" id="modal-form-type-hold" />
				    <input type="hidden" id="modal-workflow-type" />
					<input type="hidden" id="national_permission_application_id" value="{{$appId}}">
					<tr>
						<th>{{ __('national_application.application_id') }}</th>
						<th id="app-id">{{ $details->application_number }}</th>
					</tr>
					<tr>
						<th>Application Date</th>
						<th id="app-date">{{ $details->application_date }}</th>
					</tr>
					
					
					<tr class="remarks">
						<th>Remarks<span style="color:red">*</span></th>
						<td>
							<textarea class="form-control" id="remarks" rows="5" placeholder="Write remarks"></textarea>
							<span class="text-danger form-error" id="remarks_error"></span>
						</td>
					</tr>
					<tr class="upload_document">
						<th>Upload Document</th>
						<td>
							<input type="file"  name="upload_doc" id="upload_doc" accept="pdfy/*" class="form-control upload_doc" placeholder="Upload Document" >
							<small>{{ __('national_application.file_note') }}</small> 
							<span class="text-danger form-error upload_doc_error" id="upload_doc_error"></span>
							 <input type="hidden" name="uploaded_documents" id="uploaded_documents" class="uploaded_documents" value="[]" />
						     <div class="progress-upload progress-bg mt-3" style="display:none;">
							 <div id="loader-upload" style="width:347px;" class="loader-upload"></div>
							 <div class="progress-bar"></div>
							 </div>
						</td>
					</tr>
					
				</table>
				@endif
				<ul class="downloads d-flex gap-2" id="fileList">
				</ul>

			</div>
			<div class="modal-footer pt-0 border-0 justify-content-start "> 
						<button type="button" class="btn btn-primary hold-resume-action " data-action="{{\App\Enums\ReviewStatus::InProgress->value}}" id="resume-action" style="display:none">Resume</button>
						<button type="button" class="btn btn-primary hold-resume-action " data-action="{{\App\Enums\ReviewStatus::Hold->value}}" id="hold-action" style="display:none">Hold</button>
			</div>
			
			<div id="mib-history">
			</div>
		</div>
	</div>
</div>
<!-- Popup modal end here -->

