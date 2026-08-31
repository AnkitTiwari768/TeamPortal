@extends('components.admin.layout')
@section('page-content')

    <div class="container-fluid px-4 py-4">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card container-main-card">
                    <div class="card-header d-flex">
                        <div class="heading">
                            <h1>{{ __('workshop.executed_workshop_list') }}</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a
                                            href="{{ url('dashboard') }}">{{ __('workshop.dashboard') }}</a></li>
                                    <li class="breadcrumb-item"><a
                                            href="#">{{ __('workshop.executed_workshop_list') }}</a></li>
                                </ol>
                            </nav>
                        </div>
                        @if (acl('workshop-management-create'))
                            <div class="action-header ms-auto">
                                <!-- split button -->
                                <div class="btn-group drop-btn">
                                    <a href="{{ url('create-executed-workshop') }}">
                                        <button type="button" class="btn btn-primary">
                                            <img src="{{ asset('assets/img-new/add.svg') }}">
                                            {{ __('workshop.add_executed_workshop') }}
                                        </button>
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="card-body pt-1">
                        <!-- tab content -->
                        <div class="tab-content view-application-tab-content" id="nav-tabContent">
                            <div class="tab-pane fade show active" id="national-p" role="tabpanel"
                                aria-labelledby="nav-home-tab">

                                <!-- table start -->
                                <table id="dataTable" class="table datatable table-striped" width="100%">
                                    <thead>
                                        <tr>
                                            <!-- <th><input type="checkbox" id="selectAll"></th> -->
                                            <th>{{ __('workshop.s_no') }}</th>
                                            <th>{{ __('workshop.event_title') }}</th>
                                            <th>{{ __('workshop.organiser') }}</th>
                                            <th>{{ __('workshop.event_for') }}</th>
                                            <th>{{ __('workshop.venue_address') }}</th>
                                            <th>{{ __('workshop.from_date') }}</th>
                                            <th>{{ __('workshop.date_to') }}</th>
                                            <th class="actions">{{ __('workshop.action') }}</th>
                                        </tr>
                                    </thead>
                                </table>
                                <!-- table end -->
                            </div>
                        </div>
                        <!-- tab ends -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@section('js')
    ;
    <script>
        var selectedRows = {};
        var tableId = "#dataTable";

        // Show loader immediately on page load
        $('#ajax-loader').show();

        // Bind events before initialization to catch the initial AJAX request
        $(tableId).on('preXhr.dt', function() {
            $('#ajax-loader').show();
        });

        $(tableId).on('xhr.dt draw.dt', function() {
            $('#ajax-loader').hide();
        });

        dataTableInit({
            id: "#dataTable",
            showExcelExport: true,
            order: {
                column: 1,
                direction: "desc"
            },
            url: "{{ url('executed-workshop-list') }}",
            columns: [{
                    "orderable": false,
                    "render": function(data, type, full, meta) {
                        return serialNumber("#dataTable", meta.row);
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.event_title;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.organizer_name ?? '-';
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        if (!row.event_for) {
                            return '-';
                        }
                        var eventForText = row.event_for.charAt(0).toUpperCase() + row.event_for.slice(1);
                        return '<div style="white-space: normal; word-break: break-word; min-width: 150px;">' +
                            eventForText + '</div>';
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.venue_address;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.start_date;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.end_date;
                    }
                },
                /*{
                	"orderable": true,
                	"render": function (data, type, row) {
                		return moment(row.created_at).format("DD-MM-YYYY");
                	}
                },*/
                {
                    "orderable": false,
                    "render": function(data, type, row) {
                        var del = '';
                        @if (acl('workshop-management-delete'))
                            var del = buttonDelete("{{ url('delete-executed-workshop') }}", row.id);
                        @endif

                        var viewBtn = '';
                        @if (acl('workshop-management-view'))
                            var viewBtn = buttonView("{{ url('view-executed-workshop') }}", row.id);
                        @endif

                        var pedit = '';
                        @if (acl('workshop-management-edit'))
                            var pedit = buttonEdit("{{ url('edit-executed-workshop') }}", row.id);
                        @endif
                        // console.log(del);
                        // console.log(viewBtn);
                        var actions = createActionButtons([pedit, viewBtn, del]);
                        //alert(actions);
                        return actions;
                    }
                }
            ]
        });

        // Search Debouncing
        function debounce(func, wait) {
            var timeout;
            return function() {
                var context = this,
                    args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    func.apply(context, args);
                }, wait);
            };
        }

        $(tableId).on('init.dt', function() {
            var oTable = $(tableId).DataTable();
            $('.dataTables_filter input')
                .off()
                .on('keyup input', debounce(function() {
                    oTable.search(this.value).draw();
                }, 500));
        });
    </script>
@endsection



@endsection
