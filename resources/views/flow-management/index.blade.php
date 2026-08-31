@extends('components.admin.layout')
@section('page-content')
    <div class="container-fluid px-4 py-4">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card container-main-card">
                    <div class="card-header d-flex">
                        <div class="heading">
                            <h1>WorkFlow Management</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                                    <li class="breadcrumb-item"><a href="#">WorkFlow List</a></li>
                                </ol>
                            </nav>
                        </div>

                        <div class="action-header ms-auto">
                            <!-- split button -->
                            <div class="btn-group drop-btn">
                                <a href="{{ url('flow-management/create') }}">
                                    <button type="button" class="btn btn-danger">
                                        <img src="{{ asset('assets/img-new/add.svg') }}">Add WorkFlow
                                    </button></a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>S.No.</th>
                                        <th>Workflow Type</th>

                                        <!--<th>Role</th>
                            <th>Level</th>
                            <th>User Name</th>-->
                                        <th>{{ __('menu.is_active') }}</th>
                                        <th>{{ __('message.action') }}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            dataTableInit({
                id: "#dataTable",
                showExcelExport: false,
                order: {
                    column: 0,
                    direction: "ASC"
                },
                url: "{{ url('flow-management/datalist') }}",
                columns: [{
                        "orderable": false,
                        "render": function(data, type, full, meta) {
                            return serialNumber("#dataTable", meta.row);
                        }
                    },

                    {
                        "orderable": false,
                        "render": function(data, type, row) {
                            return row.workflow_type;
                        }
                    },


                    /*{
            "orderable": false,
            "render": function(data, type, row) {
                return row.r_name;
            }
          }, 

           {
            "orderable": false,
            "render": function(data, type, row) {
                return row.level;
            }
          },
          {
            "orderable": false,
            "render": function(data, type, row) {
                return row.u_name;
            }
          },*/

                    {
                        "orderable": false,
                        "render": function(data, type, row) {
                            return createActivationLabel(row.status);
                        }
                    },


                    {
                        "orderable": false,
                        "render": function(data, type, row) {
                            var action = '';
                            action += buttonEdit("{{ url('flow-management') }}", row.id);
                            return action;
                        }
                    }
                ]
            });
        });
    </script>
@endsection
