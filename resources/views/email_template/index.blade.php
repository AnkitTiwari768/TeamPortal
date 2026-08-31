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
							<a href="{{ url('email-template/create') }}">
								<button type="button" class="btn btn-danger">
									<img src="{{asset('assets/img-new/add.svg')}}">
									Add Email Template
								</button>
							</a>
						</div>
					</div>
				</div>

				<div class="card-body">
			<table id="dataTable" class="table table-striped align-middle" width="100%">
					<thead class="table-primary">
						<tr>
							<th>#</th>
							<th>Template Key</th>
							<th>Subject</th>
							<th>Variables</th>
							<th>Status</th>
							<th class="text-center">Action</th>
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

    let editUrl = "{{ route('email-template-edit', ':id') }}";

    $('#dataTable').DataTable({
        processing: true,
        serverSide: false,
        responsive: true,

        ajax: {
            url: "{{ url('email-template') }}",
            type: "GET",
            dataSrc: "data"
        },

        columns: [
            {
                data: null,
                render: function (data, type, row, meta) {
                    return `<strong>${meta.row + 1}</strong>`;
                }
            },

            {
                data: "template_key",
                render: function(data){
                    return `<span class="fw-semibold text-dark">${data}</span>`;
                }
            },

            {
                data: "subject",
                render: function(data){
                    return `<div class="fw-semibold">${data}</div>`;
                }
            },

            {
                data: "variables",
                render: function(data){

                    if(!data) return '-';

                    try {
                        let obj = typeof data === "string" ? JSON.parse(data) : data;

                        let html = `<div class="bg-light p-2 rounded small" style="max-width:300px; white-space:pre-wrap;">`;

                        for (let key in obj) {
                            html += `<div><strong>${key}</strong>: ${obj[key]}</div>`;
                        }

                        html += `</div>`;
                        return html;

                    } catch(e){
                        return data;
                    }
                }
            },

            {
                data: "is_active",
                render: function (data) {
                    return data == 1
                        ? '<span class="badge rounded-pill bg-success px-3 py-2">Active</span>'
                        : '<span class="badge rounded-pill bg-danger px-3 py-2">Inactive</span>';
                }
            },

            {
                data: "id",
                className: "text-center",
                render: function (id) {
                    return `
                        <a href="${editUrl.replace(':id', id)}" 
                            class="btn btn-sm btn-outline-primary rounded-circle"
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