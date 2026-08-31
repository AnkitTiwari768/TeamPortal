@extends('components.admin.layout')

@section('page-content')
<div class="container-fluid px-4 py-4">
	<div class="row">
		<div class="col-lg-12 col-md-12">
			<div class="card container-main-card">

				<div class="card-header d-flex">
					<div class="heading">
						<h4>{{ $title }}</h4>
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item"><a href="#">{{$title}}</a></li>
							</ol>
						</nav>
					</div>

					<div class="action-header ms-auto">
						<div class="btn-group drop-btn">
							<a href="{{ url('notification-template/create') }}">
								<button type="button" class="btn btn-danger">
									<img src="{{asset('assets/img-new/add.svg')}}">
									Add Notification Template
								</button>
							</a>
						</div>
					</div>
				</div>

				<div class="card-body">
					<table id="dataTable" class="table table-striped" width="100%">
						<thead>
							<tr>
								<th>#</th>
								<th>Stage</th>
								<th>Template Key</th>
								<th>Trigger</th>
								<th>Type</th>
								<th>Status</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>

			</div>
		</div>
	</div>
</div>
@endsection


@section('js')
<script>
$(document).ready(function () {
let editUrl = "{{ route('notification-template-edit', ':id') }}";
    $('#dataTable').DataTable({
        processing: true,
        serverSide: false, // 🔥 important

        ajax: {
            url: "{{ url('notification-template') }}",
            type: "GET",
            dataSrc: "data" // controller must return {data:[]}
        },

        columns: [
            {
                data: null,
                render: function (data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            { data: "stage" },
            { data: "template_key" },
            { data: "trigger_point" },
           

			{
                data: "type",
                render: function (data) {
                    return data == 1
                        ? '<span class="badge bg-success">User</span>'
                        : '<span class="badge bg-danger">Role</span>';
                }
            },
            {
                data: "status",
                render: function (data) {
                    return data == 1
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>';
                }
            },
           {
				data: "id",
				render: function (id) {
					return `
						 <a href="${editUrl.replace(':id', id)}" 
						class="btn btn-sm btn-outline-primary"
						title="Edit">
							<i class="fa fa-pencil"></i>
						</a>
					`;
				}
			}
        ]
    });

});
</script>
@endsection