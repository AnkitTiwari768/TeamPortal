@extends('components.admin.layout')
@section('page-content')
<div class="container-fluid px-4 py-4">
	<div class="row">
		<div class="col-lg-12 col-md-12">
			<div class="card container-main-card" style="min-height:450px;">
				<div class="card-header d-flex">
					<div class="heading">
						<h1>{{$title}}</h1>						
					</div>
				</div>
				<div class="card-body pt-1 position-relative">    
					<div class="no-content">
						<img src="{{ asset('assets/ffo-admin/img/no-graphic.svg') }}" class="no-graphic">
						<h3>{{$customError}}</h3>					   
					</div>
				</div>
			</div>
		</div>
	</div>
</div>            
@endsection