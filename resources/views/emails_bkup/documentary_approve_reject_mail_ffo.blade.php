<div id="layoutSidenav_content">
	<main>
		<div class="container-fluid px-4 py-4">
			<div class="row">
				<div class="col-lg-12 col-md-12">
					<div class="card container-main-card">
						<div class="card-body pt-1 position-relative">    
						   <div class="no-content">
							@php
                                if($status == 1)
									$message = 'for grant of official coproduction status  has been approved by MIB.';
								if($status == 3)
									$message = 'for grant of official coproduction status has been rejected by MIB.';
							@endphp
								<P>Dear FFO,<br /><br />Documentary Application no {{$application_number}} for ({{$script_title}}) {{$message}}. Please vist the portal for further action<br /></p>
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</main>
</div>