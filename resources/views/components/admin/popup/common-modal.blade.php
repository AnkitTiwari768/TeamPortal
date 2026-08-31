<div class="modal fade" id="commonModal" tabindex="-1"
	aria-labelledby="commonModalLabel" aria-hidden="true"  style="display:none">
	<div class="modal-dialog modal-dialog-centered modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="commonModalLabel"></h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"
					aria-label="Close"></button>
			</div>
			<div class="modal-body pb-1">
			
				@if(isset($details))
				<table class="table table-bordered table-striped">
					<input type="hidden" id="modal-confirm-url" />
					<!--<input type="hidden" id="modal-confirm-app-id"/>-->
				    <input type="hidden" id="modal-form-type" />
					<input type="hidden" id="national_permission_application_id" value="{{$appId}}">
					<input type="hidden" id="workflow-type" value="{{$appId}}">
					<tr>
						<th>{{ __('national_application.application_id') }} </th>
						<th id="app-id">{{ $details->application_number }}</th>
					</tr>
					<tr>
						<th>Application Date</th>
						<th id="app-date">{{ $details->application_date }}</th>
					</tr>
					
					<tr class="legal-officer" style="display:none">
						<th>To<span style="color:red">*</span></th>
						<td>
							<input name="to_email" class="form-control" id="to_email"/>
							<span class="text-danger form-error" id="to_email_error"></span>
						</td>
					</tr>
					
					<tr class="legal-officer" style="display:none">
						<th>CC<span style="color:red">*</span></th>
						<td>
							<input name="cc_email" class="form-control" id="cc_email"/>
							<span class="text-danger form-error" id="cc_email_error"></span>
						</td>
					</tr>
					
					
					<tr>
						<th>Remarks<span style="color:red">*</span></th>
						<td>
							<textarea class="form-control" id="remarks" rows="4" placeholder="Write remarks"></textarea>
							<span class="text-danger form-error" id="remarks_error"></span>
						</td>
					</tr>
					<tr>
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
			<div class="modal-footer pt-0 border-0 justify-content-start restrictedAreaButton" style="display:none"> 
				@if(empty($lastRow))
					<button type="button" class="btn btn-primary action" data-action="{{\App\Enums\ReviewStatus::InProgress->value}}" id="forward">Mark as forwarded</button>
				@else
					@if($lastRow->status ==  App\Enums\ReviewStatus::InProgress->value || $lastRow->status ==  App\Enums\ReviewStatus::Draft->value)
						<button type="button" class="btn btn-success action" data-action="{{\App\Enums\ReviewStatus::Approve->value}}" id="approve">Approve</button>
						<button type="button" class="btn btn-danger action" data-action="{{\App\Enums\ReviewStatus::Reject->value}}" id="reject">Reject</button>
						<!--<button type="button" class="btn btn-primary action" data-action="{{\App\Enums\ReviewStatus::Draft->value}}" id="save">Save</button>-->
					@elseif($lastRow->status ==  App\Enums\ReviewStatus::Pending->value || $lastRow->status ==  App\Enums\ReviewStatus::Revert->value)
						<button type="button" class="btn btn-primary action" data-action="{{\App\Enums\ReviewStatus::InProgress->value}}" id="forward">Mark as forwarded</button>
					@endif
				@endif
			
				
			</div>
			
			<div class="modal-footer pt-0 border-0 justify-content-start coproductionButton" style="display:none"> 
				<button type="button" class="btn btn-primary action cp-forward" data-action="{{\App\Enums\ReviewStatus::InProgress->value}}" id="forward" style="display:none">Mark as forwarded</button>
				<button type="button" class="btn btn-success action cp-approve-reject" data-action="{{\App\Enums\ReviewStatus::Approve->value}}" id="approve" style="display:none">Approve</button>
				<button type="button" class="btn btn-danger action cp-approve-reject" data-action="{{\App\Enums\ReviewStatus::Reject->value}}" id="reject" style="display:none">Reject</button>
				
			
				
			</div>
			
				<div id="history" class="modal-body">
				</div>
		</div>
	</div>
</div>
<!-- Popup modal end here -->
