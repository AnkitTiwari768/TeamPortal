@php
$message = '';
if($form_type==config('constant.FOREIGN_LIVE'))
{
	$message="of foreign film live shoot";
}
if($form_type==config('constant.FOREIGN_LIVE_ANIMATION'))
{
	$message="of foreign film annimation/post production";
}
if($form_type==config('constant.COPRODUCTION'))
{
	$message="of coproduction live shoot";
}
if($form_type==config('constant.COPRODUCTION_ANIMATION'))
{
	$message="of coproduction annimation/post production";
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
								<P>
								Application no {{$application_number}} for {{$script_title}}  - New Interim application {{$message}} submitted. <br />
								
								</P>
								
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</main>
</div>