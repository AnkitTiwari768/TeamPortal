@extends('components.admin.layout')
@section('page-content')

<div class="container-fluid px-4 py-4">
	<div class="row">
		<div class="col-lg-12 col-md-12">
			<div class="card container-main-card" style="min-height:450px;">
				<div class="card-header d-flex">
					<div class="heading">
						<h1>Application Query</h1>						
					</div>
					
				</div>
				<div class="card-body pt-1 position-relative">    
				<div class="action-header ms-auto">
					<a href="javascript:void(0);" onclick = "javascript:history.back(-1);" class="btn btn-sm btn-primary" style="float:right;"><img src="{{ asset('assets/ffo-admin/img/arrow-back-w.svg') }}"> Back</a>
				</div>

					<div class="no-content">
						<img src="{{ asset('assets/ffo-admin/img/no-graphic.svg') }}" class="no-graphic">
						<h3>No Query Found.</h3>	
						<a href="{{url('/add-application-queries')}}/{{$appId}}" class="btn btn-sm btn-primary"> <i class="fa fa-plus" aria-hidden="true"></i> Add Query</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>  

@section('js');
@endsection
@endsection

