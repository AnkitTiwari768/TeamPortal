@extends('components.admin.layout')
@section('page-content')
<div class="container-fluid">     
	<div class="row">
		<div class="col-md-12">
			<div class="card shadow mb-4">
				<div class="card-header py-3 d-flex flex- align-items-center justify-content-between">
					<h6 class="m-0 font-weight-bold text-primary"> New Part</h6>
					<a href="https://utlhq.com/pcimh_live/parts" class="btn btn-sm btn-primary float-right"><i class="fa fa-angle-double-left"></i> Back</a>
				</div>
				<div class="card-body">
					<form id="formId">
						<div class="mb-3"> 
							<div class="form-group col-md-6">
								<label class="required">Part Name</label>
								<input type="text" class="form-control" name="name" id="name" maxlength="100" placeholder="Part Name">
								<span class="text-danger form-error" id="name_error"></span>
							</div>
						</div>
						
						<div class="mb-3"> 
							<div class="form-group col-md-6">
								<label class="required">Status</label>
								<select class="form-control" id="status" name="status">
								<option value="1">Active</option><option value="0">In-active</option>
								</select>   
								<span class="text-danger form-error" id="status_error"></span>
							</div> 
						</div>
						 <div class="mb-3">
							<button type="submit" id="submit-btn" class="btn btn-success">Create</button>                  
							<button type="reset" id="cancel-btn" class="btn btn-warning"> Reset</button>  
						</div>						
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- table design -->
<div class="container-fluid">
	<div class="card shadow mb-4">
		<div class="card-header py-3">
			<h6 class="m-0 font-weight-bold text-primary">Parts<span class="float-end">
			<a href="https://utlhq.com/pcimh_live/parts/create" class="btn btn-info btn-sm"><i class="fa fa-plus"></i> New Part</a>
			</span></h6>
		</div>
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
					<thead>
						<tr>
							<th>SN.</th>
							<th>Part Name</th>
							<th>Status</th>
							 <th>Action</th>
						</tr>
					</thead>
					<tr>
						<td>1</td>
						<td>dfvdbd</td>
						<td>dfbdb</td>
						<td>edit</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>

<script>
new DataTable('#dataTable');
</script>
@section('js');
@endsection
@endsection

