<!DOCTYPE html>
<html>
<div id="layoutSidenav_content">
	<main>
		<div class="container-fluid px-4 py-4">
			<div class="row">
				<div class="col-lg-12 col-md-12">
					<div class="card container-main-card">
						
						<div class="card-body pt-1 position-relative">    
						   <div class="no-content">
						    @php
							
								$message = '';
								if($domestic == '1')
								{
									$message = $zone_data;
								}
								
							@endphp
								<P>Dear {{$applicant_name}},<br /><br /> 
								Your application no <strong>{{$application_number}}</strong> for <strong>{{$script_title}}</strong> has been submitted for Railways Permission. Please quote this Application No. for all future communications with us.<br />
								{!! $message !!}<br /> 
								In case of any query please write to {{env('MAIL_FROM_ADDRESS')}}

								<!--We are pleased to inform you that we have received your application form for the title {{--$script_title--}} .<br /><br />Your Application reference number is {{--$application_number--}}--> <br /><br /><br />Kind regards, <br /><strong>India Cine Hub</strong></P>
								<!--<a href="https://ffo.gov.in/contact-us" class="btn back-btn">Contact US</a>-->
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</main>
</div>
</html>