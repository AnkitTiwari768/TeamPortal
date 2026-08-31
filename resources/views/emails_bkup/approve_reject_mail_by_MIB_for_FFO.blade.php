<div id="layoutSidenav_content">
	<main>
		<div class="container-fluid px-4 py-4">
			<div class="row">
				<div class="col-lg-12 col-md-12">
					<div class="card container-main-card">
						<div class="card-header d-flex">
							<div class="heading">
								@if($status == 1)
									<h3>Application Has Been Approved By MIB</h3>
								@elseif($status == 3)
									<h3>Application Has Been Rejected By MIB</h3>
								@endif
							</div>
						</div>
						<div class="card-body pt-1 position-relative">    
						   <div class="no-content">
							@php
								if($workflow_type == 1 && $status == 1)
									$message = 'Application for permission to shoot in India has been approved by MIB.';
								if($workflow_type == 1 && $status == 3)
									$message = 'Application for permission to shoot in India has been rejected by MIB.';
								if($workflow_type == 2 && $status == 1)
									$message = 'Application for permission to shoot in Restricted area has been approved by MIB.';
								if($workflow_type == 2 && $status == 3)
									$message = 'Application for permission to shoot in Restricted area has been rejected by MIB.';
								if($workflow_type == 3 && $status == 1)
									$message = 'Application for grant of official coproduction status  has been approved by MIB.';
								if($workflow_type == 3 && $status == 3)
									$message = 'Application for grant of official coproduction status  has been rejected by MIB.';
							@endphp
								<P>Dear FFO,<br /><br />Application no {{$application_number}} for ({{$script_title}}) {{$message}}. Please vist the portal for further action<br /></p>
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</main>
</div>