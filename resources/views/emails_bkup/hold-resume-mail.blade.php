<div id="layoutSidenav_content">
	<main>
		<div class="container-fluid px-4 py-4">
			<div class="row">
				<div class="col-lg-12 col-md-12">
					<div class="card container-main-card">
						
						<div class="card-body pt-1 position-relative">    
						   <div class="no-content">
							@php
                            	if($type == 10 )
									$msg = 'put on hold';
								
								if($type == 6 )
									$msg = 'resumed';

								if($application_type == 1 )
									$application = 'Application';
								if($application_type == 5 )
									$application = 'Coproduction Application';
								if($application_type == 'documentary' )
									$application = 'Documentary Application';
								
								
							@endphp
								<P>Dear {{$applicant_name}},<br /><br />{{$application}} No. {{$application_number}} for ({{$script_title}}) has been {{$msg}} by ICH. In case of any query please write to ich@nfdcindia.com<br /><br />Kind regards, <br /><strong>India Cine Hub</strong></P>
								
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</main>
</div>