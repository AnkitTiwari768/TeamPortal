@extends('components.admin.layout')
@section('page-content')

    <div class="container-fluid">
        <div class="card container-main-card mt-3 mb-4">
            <div class="card-header d-flex justify-content-between">
                <div class="heading">
                <h1>IA Pending List</h1>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>S.N.</th>
                                <th>Organization Name</th>
                                <th>Entity Type</th>
                                <th>Email of Entity</th>
                                <th>Registration No</th>
                                <th>Contact No</th>
                                <th>{{ __('message.status') }}</th>
                                <th class="actions">{{ __('message.action') }}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

@section('js')
    ;
    <script>
        dataTableInit({
            id: "#dataTable",
            showExcelExport: true,
            order: {
                column: 1,
                direction: "asc"
            },
            url: "{{ url('pending-ia') }}",
            columns: [

                {
                    "orderable": false,
                    "render": function(data, type, full, meta) {
                        return serialNumber("#dataTable", meta.row);
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.organization_name;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.entity_type;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.entity_email;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.registration_number;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.contact_number;
                    }
                },

                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.statusWithLabel;
                    }
                },
                @if (acl('ia-view-details'))
                    {
                        "orderable": false,
                        "render": function(data, type, row) {
                            var view = '';
                            @if (acl('ia-view-details'))
                                view = buttonView("{{ url('/ia-view-details') }}", row.id);
                            @endif

                            var actions = createActionButtons([view]);

                            return actions;
                        }
                    }
                @endif
            ]
        });
    </script>
@endsection
@endsection
