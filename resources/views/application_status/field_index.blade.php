@extends('components.admin.layout')

@section('page-content')
<div class="container-fluid px-4 py-4">
    <div class="card container-main-card">
        <div class="card-header d-flex align-items-center">
            <div class="heading">
                <h1>Fields for: {{ $tab->label }}</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('status-tabs') }}">Status Tabs</a></li>
                        <li class="breadcrumb-item active">Fields</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <button class="btn btn-danger" onclick="openFieldModal()">
                    <i class="fa fa-plus me-1"></i> Add Field
                </button>
            </div>
        </div>
        <div class="card-body pt-0">
             <table id="dataTable" class="table datatable table-striped" width="100%">
                <thead>
                    <tr>
                        <th>Sort</th>
                        <th>Key</th>
                        <th>Label</th>
                        <th>Placeholder</th>
                        <th>Type</th>
                        <th title="Show in search popup">In Result</th>
                        <th class="actions">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

{{-- Dynamic Field Modal --}}
<div class="modal fade" id="fieldModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="fieldForm" class="modal-content">
            @csrf
            <input type="hidden" name="status_tab_id" value="{{ $tab->id }}">
            <input type="hidden" name="id" id="field_id">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">Field Settings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-7 mb-3">
                        <label class="form-label">Field Label (Display Name)</label>
                        <input type="text" name="field_label" id="field_label" class="form-control" required placeholder="e.g. Enterprise Name">
                    </div>
                    <div class="col-md-5 mb-3">
                        <label class="form-label">Database Key</label>
                        <input type="text" name="field_key" id="field_key" class="form-control" required placeholder="e.g. enterprise_name">
                    </div>
                    <div class="col-md-7 mb-3">
                        <label class="form-label">Placeholder Text</label>
                        <input type="text" name="placeholder" id="placeholder" class="form-control" placeholder="e.g. Type enterprise name">
                    </div>
                    <div class="col-md-5 mb-3">
                        <label class="form-label">Type</label>
                        <select name="field_type" id="field_type" class="form-select">
                            <option value="text">Text / Default</option>
                            <option value="email">Email</option>
                            <option value="number">Number Only</option>
                            <option value="date">Date Picker</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <div class="row bg-light p-2 rounded mx-0">
                             <div class="col-6">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_searchable" id="is_searchable" value="1" checked>
                                    <label class="form-check-label fw-bold small">Enable Search</label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_required" id="is_required" value="1" checked>
                                    <label class="form-check-label fw-bold small">Req. Input</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_visible_in_popup" id="is_visible_in_popup" value="1" checked>
                                    <label class="form-check-label fw-bold small text-primary">Visible in Popup</label>
                                </div>
                                <div class="mb-2">
                                    <label class="small fw-bold mb-0">Sort Order</label>
                                    <input type="number" name="sort_order" id="sort_order" class="form-control form-control-sm" value="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="submit" class="btn btn-primary px-4">SAVE FIELD</button>
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
        url: "{{ route('fields-datalist', $tab->id) }}",
        columns: [
            { "data": "sort_order", "orderable": true },
            { "data": "field_key", "orderable": true },
            { "data": "field_label", "orderable": true },
            { "data": "placeholder", "orderable": true, "render": function(d) { return d || '—'; } },
            { "data": "field_type", "orderable": true },
            { 
               "data": "is_visible_in_popup", 
               "orderable": true,
               "render": function(d) { return d == 1 ? '<span class="text-success fw-bold">Visible</span>' : '<span class="text-muted">Hidden</span>'; }
            },
            {
                "orderable": false,
                "render": function (data, type, row) {
                    var edit = '<a href="javascript:void(0)" class="btn btn-sm btn-primary m-1 edit-field" onclick=\'openEditModal(' + JSON.stringify(row) + ')\'><img src="{{ asset("assets/img-new/edit.svg") }}"></a>';
                    var del = buttonDelete("{{ url('fields-delete') }}", row.id);
                    return createActionButtons([edit, del]);
                }
            }
        ]
    });

    function openFieldModal() {
        $('#fieldForm')[0].reset(); 
        $('#field_id').val(''); 
        $('#is_searchable, #is_required, #is_visible_in_popup').prop('checked', true);
        $('#fieldModal').modal('show');
    }

    function openEditModal(row) {
        $('#field_id').val(row.id);
        $('#field_label').val(row.field_label);
        $('#field_key').val(row.field_key);
        $('#placeholder').val(row.placeholder);
        $('#field_type').val(row.field_type);
        $('#sort_order').val(row.sort_order);
        $('#is_searchable').prop('checked', row.is_searchable == 1);
        $('#is_required').prop('checked', row.is_required == 1);
        $('#is_visible_in_popup').prop('checked', row.is_visible_in_popup == 1);
        $('#fieldModal').modal('show');
    }

    $('#fieldForm').on('submit', function(e) {
        e.preventDefault();
        var id = $('#field_id').val();
        var url = id ? "{{ url('fields-update') }}/" + id : "{{ route('fields-store') }}";
        $.post(url, $(this).serialize(), function(res) {
            toastr.success(res.message); 
            $('#fieldModal').modal('hide'); 
            $('#dataTable').DataTable().ajax.reload();
        }).fail(function(xhr) {
            toastr.error(xhr.responseJSON?.message || 'Error processing request.');
        });
    });
</script>
@endsection
