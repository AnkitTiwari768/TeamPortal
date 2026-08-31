@php
$message = '';
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
@endphp

<div id="layoutSidenav_content">
	<main>
		<div class="container-fluid px-4 py-4">
			<div class="row">
				<div class="col-lg-12 col-md-12">
					<div class="card container-main-card">
						<div class="card-header d-flex">
							
						</div>
						<div class="card-body pt-1 position-relative">    
						   <div class="no-content">
							
							@php
							$regards = '';
								if($type == 'Applicant')
								{
									$name = 'FFO';
								}else{
									$name = $name;
								}
								
							@endphp
							<P>Dear {{$name}},<br /><br />{{$message}} Interim Application no {{$application_number}} for  ({{$script_title}})  - A query has been raised, please visit the portal to resolve the query.<br /><br />
							@php
								if($type != 'Applicant')	
								$regards = 'Kind regards, <br /><strong>Film Facilitation Office</strong>';
								
							@endphp
							{!! $regards !!}
							</P>
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</main>
</div>