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
								Your application no <strong>{{$application_number}}</strong> for <strong>{{$script_title}}</strong> has been submitted for Coproduction Permission. Please quote this Application No. for all future communications with us.<br />
								In case of any query please write to {{env('MAIL_FROM_ADDRESS')}}

								<br /><br /><br />Kind regards, <br /><strong>India Cine Hub</strong></P>
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</main>
</div>
</html>