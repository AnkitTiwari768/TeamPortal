<div id="layoutSidenav_content">
	<main>
		<div class="container-fluid px-4 py-4">
			<div class="row">
				<div class="col-lg-12 col-md-12">
					<div class="card container-main-card">
						<div class="card-body pt-1 position-relative">    
						   <div class="no-content" style="font-family: calibri;">
							@php
								if($status == 1 )
									$message = ' for permission to shoot has been approved.';
								if($status == 3)
									$message = ' for permission to shoot has been rejected.';
								
							@endphp
								<P>Dear {{$applicant_name}},<br /><br />Application No. <strong>{{$application_number}}</strong> for <strong>({{$script_title}})</strong> {{$message}}.<br /><br />
								 In case of any query  please write to ich@nfdcindia.com<br /><br />Kind regards, <br /><strong>India Cine Hub</strong></P>
								 
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</main>
</div>