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
								<P>Dear {{$applicant_name}},<br /><br /> 
								Your application no {{$application_number}} for {{$script_title}} - Approved by Railways Central Board.<br />
								{!! $zone_data !!}<br /> 
								In case of any query please write to ffo@nfdcindia.com

								<!--We are pleased to inform you that we have received your application form for the title {{--$script_title--}} .<br /><br />Your Application reference number is {{--$application_number--}}--> <br /><br /><br />Kind regards, <br /><strong>Film Facilitation Office</strong></P>
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