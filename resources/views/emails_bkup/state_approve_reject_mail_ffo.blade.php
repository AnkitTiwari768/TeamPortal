<div id="layoutSidenav_content">
	<main>
		<div class="container-fluid px-4 py-4">
			<div class="row">
				<div class="col-lg-12 col-md-12">
					<div class="card container-main-card">
						<div class="card-body pt-1 position-relative">    
						   <div class="no-content">
							@php
                                if($status == 1 )
									$message = 'State application for permission to shoot has been approved.';
								if($status == 3)
									$message = 'State application for permission to shoot has been rejected.';
								
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