@php
if($status == 3){
    $type = 'Rejected';
}
if($status == 1)
{
    $type = 'Approved';
}
@endphp
<div id="layoutSidenav_content">
	<main>
		<div class="container-fluid px-4 py-4">
			<div class="row">
				<div class="col-lg-12 col-md-12">
					<div class="card container-main-card">
						 
						<div class="card-body pt-1 position-relative">    
						   <div class="no-content">
								<P>Dear User,<br /><br /> 
								Application no <strong>{{$application_number}}</strong> for <strong>{{$script_title}}</strong> - Your application for permission to shoot in Indian State has been {{$type}} by {{$department}} department. 
								<br /><br /><br />Kind regards, <br /><strong>India Cine Hub</strong></P>
								<!--<a href="https://ffo.gov.in/contact-us" class="btn back-btn">Contact US</a>-->
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</main>
</div>