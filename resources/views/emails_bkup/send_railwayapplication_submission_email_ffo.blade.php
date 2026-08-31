<div id="layoutSidenav_content">
	<main>
		<div class="container-fluid px-4 py-4">
			<div class="row">
				<div class="col-lg-12 col-md-12">
					<div class="card container-main-card">
						<div class="card-header d-flex">
							
						</div>
						<div class="card-body pt-1 position-relative">    
						   <div class="no-content">
								<P>
								@php
								$message = '';
								if($domestic == '1')
								{
									$message = $zone_data;
								}
							
								@endphp
								Application no <strong>{{$application_number}}</strong> for <strong>{{$script_title}}</strong>  - New application submitted to Railways for shooting permission <br />
								{!! $message !!}

								 <!--<br /><br /><br />Kind regards, <br /><strong>Film Facilitation Office</strong>--></P>
								
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</main>
</div>