<div id="layoutSidenav_content">
	<main>
		<div class="container-fluid px-4 py-4">
			<div class="row">
				<div class="col-lg-12 col-md-12">
					<div class="card container-main-card">
						<div class="card-header d-flex">
							<div class="heading">
								<h3>Application Modification Request</h3>
							</div>
						</div>
						<div class="card-body pt-1 position-relative">    
							@if($temp_type=='for_applicant')
						   <div class="no-content"> 
								<P>Dear Applicant,<br /><br /> 
								Your application no {{$application_number}} for {{$script_title}} has been assign modification request. Please update your application. 
In case of any query please write to ffo@nfdcindia.com
  <br /><br /><br />Kind regards, <br /><strong>Film Facilitation Office</strong></P> 
							</div>
							@else
							 <div class="no-content"> 
								<P>Dear FFO,<br /><br /> 
								Application modification has been completed for {{$script_title}} . Please take further action.  
  <br /><br /><br />Kind regards, <br /><strong>Film Facilitation Office</strong></P> 
							</div>
							@endif
						</div>
					</div>
				</div>
			</div>

		</div>
	</main>
</div>