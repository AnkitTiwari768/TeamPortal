<div id="layoutSidenav_content">
	<main>
		<div class="container-fluid px-4 py-4">
			<div class="row">
				<div class="col-lg-12 col-md-12">
					<div class="card container-main-card">
						
						<div class="card-body pt-1 position-relative">    
						   <div class="no-content">
								<P>
									@php
										$name = '';
										if($type == 'Applicant')
										{
											$name = $applicant_name;
										}else{
											$name = 'FFO';
										}
									@endphp
									Dear {{$name}},<br /><br /> 
									Application no {{$application_number}} for {{$script_title}} - New ASI application submitted. 
									@php 
										if($type == 'Applicant')
										{@endphp
											In case of any query please write to ffo@nfdcindia.com
											<br /><br /><br />Kind regards, <br /><strong>India Cine Hub</strong>
											@php	}
									@endphp
								</P>
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</main>
</div>