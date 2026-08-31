@extends('components.admin.content-layout')

@section('styles')
<style>
 .container-main-card .card-header {display: none !important;}
</style>
@endsection

@section('card-content')
<div class="card-body p-4 pt-2">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
                <div class="heading">
                    <h1 class="">{{ $title }}</h1>
            <p class="text-muted small mb-0">Manage and track all query communications here.</p>
                </div>
        </div>
       
        <div class="col-md-6 text-end">
            @if($claimSlug === 'claim-for-catalogue-creation')
            <a href="{{ url('claims') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fa fa-arrow-left me-1"></i> Back
            </a>
            @elseif ($claimSlug === 'claim-for-accounts-management')
            <a href="{{ url('accounts-claims') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fa fa-arrow-left me-1"></i> Back
            </a>
            @elseif ($claimSlug === 'claim-for-transportation-and-logistic')
            <a href="{{ url('logistics-transportation-claim') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fa fa-arrow-left me-1"></i> Back
            </a>
            @elseif ($claimSlug === 'claim-for-demand-generation')
            <a href="{{ url('demand-generation-claim') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fa fa-arrow-left me-1"></i> Back
            </a>
            @elseif ($claimSlug === 'claim-for-packaging')
            <a href="{{ url('packaging-support-claim') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fa fa-arrow-left me-1"></i> Back
            </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
            <i class="fa fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-hover w-100" id="qmsDataTable">
            <thead class="bg-light">
                <tr>
                    <th class="border-0">#</th>
                    <th class="border-0">Subject</th>
                    <th class="border-0">Sender</th>
                    <th class="border-0">Receiver</th>
                    <th class="border-0">Status</th>
                    <th class="border-0">Last Activity</th>
                    <th class="border-0 text-end">Action</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('js')
<script>
$(document).ready(function() {
    var config = {
        id: '#qmsDataTable',
        url: "{{ route('qms.index', ['claimSlug' => $claimSlug ?? '']) }}",
        order: {
            column: 5,
            direction: 'desc'
        },
        columns: [
            { data: null, name: 'serial_number', orderable: false, searchable: false, render: function(data, type, row, meta) {
                return serialNumber('#qmsDataTable', meta.row);
            }},
            { data: 'subject', name: 'subject', render: function(data) {
                return `<span class="fw-bold text-dark">${data}</span>`;
            }},
            { data: 'sender_name', name: 'sender_name' },
            { data: 'receiver_name', name: 'receiver_name' },
            { data: 'status', name: 'status', render: function(data) {
                // Capitalize for ticketBedge
                let status = data.charAt(0).toUpperCase() + data.slice(1);
                return ticketBedge(status);
            }},
            { data: 'updated_at', name: 'updated_at', render: function(data) {
                return moment(data).fromNow();
            }},
            { data: 'id', orderable: false, searchable: false, className: 'text-end', render: function(data) {
                return createActionButtons([
                    buttonView("{{ url('qms-show/') }}", data)
                ]);
            }}
        ]
    };

    dataTableInit(config);
});
</script>
@endpush
