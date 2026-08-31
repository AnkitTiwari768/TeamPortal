@php
$message = '';
$type = '';
if($form_type==config('constant.FOREIGN_LIVE'))
{
	$message="Foreign film live shoot";
}
if($form_type==config('constant.FOREIGN_LIVE_ANIMATION'))
{
	$message="Foreign film annimation/post production";
}
if($form_type==config('constant.COPRODUCTION'))
{
	$message="Coproduction live shoot";
}
if($form_type==config('constant.COPRODUCTION_ANIMATION'))
{
	$message="Coproduction annimation/post production";
}
if($disbursal_type == 2){
    $type = 'Final';
}
if($disbursal_type == 1)
{
    $type = 'First';
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
							@php
                            if($status == 1)
									$msg = 'has been approved.';
								if($status == 3)
									$msg = 'has been rejected.';
								
							@endphp
								<P>Dear ICH,<br /><br />{{$message}} {{$type}} Disbursal Application no {{$application_number}} for ({{$script_title}}) {{$msg}}. Please vist the portal for further action<br /></p>
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</main>
</div>