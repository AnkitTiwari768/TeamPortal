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
								
								if($type == 3)
									$message = 'Application for grant of official coproduction status has been forwarded.';
								
							@endphp
								<p>Application no {{$application_number}} A new proposal for shooting permission of the international film  ({{$script_title}})  has been forwarded by the FFO for consideration.<br /><br />Kind regards, <br /><strong>Film Facilitation Office</strong></p>
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</main>
</div>