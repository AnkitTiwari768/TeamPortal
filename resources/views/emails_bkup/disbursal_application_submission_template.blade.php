@php
$message = '';
$type = '';
if($form_type==config('constant.FOREIGN_LIVE'))
{
	$message="foreign film live shoot";
}
if($form_type==config('constant.FOREIGN_LIVE_ANIMATION'))
{
	$message="foreign film annimation/post production";
}
if($form_type==config('constant.COPRODUCTION'))
{
	$message="coproduction live shoot";
}
if($form_type==config('constant.COPRODUCTION_ANIMATION'))
{
	$message="coproduction annimation/post production";
}
if($disbursal_type == 2){
    $type = 'Final';
}
if($disbursal_type == 1)
{
    $type = 'First';
}
@endphp
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
								<P>Dear {{$name}},<br /><br /> 
								Your {{$message}} {{$type}} disbursal application no {{$application_number}} for {{$script_title}} has been submitted successfully. Please quote this application reference no. for all future communications with us.<br />
								In case of any query please write to ich@nfdcindia.com

								<br /><br />Kind regards, <br /><strong>India Cine Hub</strong></P>
								
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</main>
</div>
</html>