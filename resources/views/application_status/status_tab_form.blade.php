@extends('components.admin.layout')

@section('page-content')
<div class="container-fluid px-4 py-4">
    <div class="card container-main-card">
        <div class="card-header">
            <h1 class="mb-0">{{ $title }}</h1>
        </div>
        <div class="card-body">
            <form id="tabForm">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tab Label <span class="text-danger">*</span></label>
                        <input type="text" name="label" class="form-control" value="{{ $row->label ?? '' }}" required placeholder="e.g. MSME Registration">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tab Key (Slug) <span class="text-danger">*</span></label>
                        <input type="text" name="tab_key" class="form-control" value="{{ $row->tab_key ?? '' }}" required {{ isset($row) ? 'readonly' : '' }} placeholder="e.g. msme">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tracker Class <span class="text-danger">*</span></label>
                        <input type="text" name="tracker_class" class="form-control" value="{{ $row->tracker_class ?? '' }}" required placeholder="e.g. App\Web\ApplicationStatus\MsmeTracker">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="tab_order" class="form-control" value="{{ $row->tab_order ?? 0 }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Icon (FontAwesome)</label>
                        <input type="text" name="icon" class="form-control" value="{{ $row->icon ?? 'fa-search' }}" placeholder="e.g. fa-search">
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Public Hint Text</label>
                        <textarea name="hint_text" class="form-control" rows="2" placeholder="e.g. Please enter your registration details below...">{{ $row->hint_text ?? '' }}</textarea>
                    </div>

                    <div class="col-md-12">
                        <div class="row bg-light p-3 rounded mx-0">
                            <div class="col-md-3 mb-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_enabled" value="1" {{ (!isset($row) || $row->is_enabled) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold">Active Tab</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_timeline_enabled" value="1" {{ (isset($row) && $row->is_timeline_enabled) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold">Show Timeline</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_remarks_enabled" value="1" {{ (isset($row) && $row->is_remarks_enabled) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold">Show Remarks</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_receipt_enabled" value="1" {{ (isset($row) && $row->is_receipt_enabled) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold">Show Receipt</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 border-top pt-3">
                    <button type="submit" class="btn btn-primary px-4">SAVE TAB</button>
                    <a href="{{ url('status-tabs') }}" class="btn btn-light border px-4">CANCEL</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    $('#tabForm').on('submit', function(e) {
        e.preventDefault();
        var url = "{{ isset($row) ? route('status-tabs-update', $row->id) : route('status-tabs-store') }}";
        $.post(url, $(this).serialize(), function(res) {
            toastr.success(res.message);
            window.location.href = "{{ url('status-tabs') }}";
        }).fail(function(xhr) {
            toastr.error(xhr.responseJSON?.message || 'Error saving settings.');
        });
    });
</script>
@endsection
