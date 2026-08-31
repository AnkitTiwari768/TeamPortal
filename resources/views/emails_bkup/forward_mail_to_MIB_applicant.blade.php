<div id="layoutSidenav_content">
	<main>
		<div class="container-fluid px-4 py-4">
			<div class="row">
				<div class="col-lg-12 col-md-12">
					<div class="card container-main-card">
						
						<div class="card-body pt-1 position-relative">    
						   <div class="no-content">
							@php
                            if($type == 1 )
									$message = 'Application has been forwarded.';
								
								if($type == 2 )
									$message = 'Application for Restricted area has been forwarded.';
								
								if($type == 3 )
									$message = 'Application for grant of official coproduction status has been forwarded.';
							@endphp
								<P>Dear {{$applicant_name}},<br /><br />Application no {{$application_number}} for ({{$script_title}}) - Proposal forwared to the Ministry of Information and Broadcasting. In case of any query please write to ffo@nfdcindia.com<br />Kind regards, <br /><strong>Film Facilitation Office</strong></P>
								
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</main>
</div>