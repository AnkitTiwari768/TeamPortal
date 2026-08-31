@extends('components.admin.layout')

@section('page-content')
<div class="container-fluid px-4 py-4">
    <div class="card container-main-card">
        <div class="card-header d-flex align-items-center">
            <div class="heading">
                <h1>Stages for: {{ $tab->label }}</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('status-tabs') }}">Status Tabs</a></li>
                        <li class="breadcrumb-item active">Timeline Stages</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <button class="btn btn-danger" onclick="openTimelineModal()">
                    <i class="fa fa-plus me-1"></i> Add Stage
                </button>
            </div>
        </div>
        <div class="card-body pt-0">
             <table id="dataTable" class="table datatable table-striped" width="100%">
                <thead>
                    <tr>
                        <th>Sequence</th>
                        <th>Stage Key</th>
                        <th>Label</th>
                        <th>Status Color</th>
                        <th>Status</th>
                        <th class="actions">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

{{-- Stage Modal --}}
<div class="modal fade" id="timelineModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="timelineForm" class="modal-content">
            @csrf
            <input type="hidden" name="status_tab_id" value="{{ $tab->id }}">
            <input type="hidden" name="id" id="timeline_id">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">Stage Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Stage Label</label>
                        <input type="text" name="stage_label" id="stage_label" class="form-control" required placeholder="e.g. Under Verification">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Sequence</label>
                        <input type="number" name="sequence" id="sequence" class="form-control" value="1" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Stage Key (Internal)</label>
                        <input type="text" name="stage_key" id="stage_key" class="form-control" required placeholder="e.g. pending">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Progress Color</label>
                        <select name="status_color" id="status_color" class="form-select">
                            <option value="success">Success (Green)</option>
                            <option value="warning">Warning (Yellow)</option>
                            <option value="info">Info (Blue)</option>
                            <option value="danger">Danger (Red)</option>
                            <option value="primary">Primary (Navy)</option>
                            <option value="secondary">Secondary (Grey)</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="is_enabled" id="is_timeline_enabled" value="1" checked>
                            <label class="form-check-label fw-bold">Enable this Stage</label>
                        </div>
                        <p class="text-muted small mt-1">This stage will only appear in the timeline if it is enabled and correctly ordered by sequence.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="submit" class="btn btn-primary px-4">SAVE STAGE</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script>
    dataTableInit({
        id: "#dataTable",
        order: {
            column: 0,
            direction: "asc"
        },
        url: "{{ route('timelines-datalist', $tab->id) }}",
        columns: [
            { "data": "sequence", "orderable": true },
            { "data": "stage_key", "orderable": true },
            { "data": "stage_label", "orderable": true },
            { 
               "data": "status_color", 
               "orderable": true,
               "render": function(d) { return '<span class="badge bg-' + (d || 'success') + '">' + (d || 'success').toUpperCase() + '</span>'; }
            },
            {
                "orderable": true,
                "render": function (data, type, row) {
                    return row.is_enabled == 1 ? '<span class="text-success">Active</span>' : '<span class="text-danger">Inactive</span>';
                }
            },
            {
                "orderable": false,
                "render": function (data, type, row) {
                    var edit = '<a href="javascript:void(0)" class="btn btn-sm btn-primary m-1 edit-timeline" onclick=\'openEditModal(' + JSON.stringify(row) + ')\'><img src="{{ asset("assets/img-new/edit.svg") }}"></a>';
                    var del = buttonDelete("{{ url('timelines-delete') }}", row.id);
                    return createActionButtons([edit, del]);
                }
            }
        ]
    });

    function openTimelineModal() {
        $('#timelineForm')[0].reset(); $('#timeline_id').val(''); $('#is_timeline_enabled').prop('checked', true); $('#timelineModal').modal('show');
    }

    function openEditModal(row) {
        $('#timeline_id').val(row.id);
        $('#stage_label').val(row.stage_label);
        $('#stage_key').val(row.stage_key);
        $('#sequence').val(row.sequence);
        $('#status_color').val(row.status_color || 'success');
        $('#is_timeline_enabled').prop('checked', row.is_enabled == 1);
        $('#timelineModal').modal('show');
    }

    $('#timelineForm').on('submit', function(e) {
        e.preventDefault();
        var id = $('#timeline_id').val();
        var url = id ? "{{ url('timelines-update') }}/" + id : "{{ route('timelines-store') }}";
        $.post(url, $(this).serialize(), function(res) {
            toastr.success(res.message); $('#timelineModal').modal('hide'); $('#dataTable').DataTable().ajax.reload();
        }).fail(function(xhr) {
            toastr.error(xhr.responseJSON?.message || 'Error processing request.');
        });
    });
</script>
@endsection
