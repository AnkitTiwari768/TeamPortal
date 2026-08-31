@php
if($msg_for==1){
	$message="foreign live shoot";
}
@endphp
<div id="layoutSidenav_content">
	<main>
		<div class="container-fluid px-4 py-4">
			<div class="row">
				<div class="col-lg-12 col-md-12">
					<div class="card container-main-card">
						<div class="card-header d-flex">
							<div class="heading">
								<h3>Application Form Submission Confirmation</h3>
							</div>
						</div>
						<div class="card-body pt-1 position-relative">    
						   <div class="no-content">
								<P>
								new interim {{$message}} application has been submitted for incentive. Movie Title - {{$script_title}}
								</P>
								
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</main>
</div>