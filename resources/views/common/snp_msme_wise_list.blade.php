@extends('components.admin.content-layout')

@section('card-content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<div class="card-body">
    
    <!-- DataTable -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover w-100" id="snpMsmeTable">
            <thead class="table-dark">
                <tr>
                    <th width="5%">#</th>
                    <th width="10%">SNP ID</th>
                    <th width="10%">Organization ID</th>
                    <th width="30%">Organization Name</th>
                    <th width="15%">Open MSME</th>
                    <th width="15%">Direct Selection by MSE</th>
                    <th width="15%">Onboarded MSE</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
@endsection

@section('css')
@include('common.libraries')
@include('common.datatables-css')
@endsection

@section('js')
@include('common.datatables-js')
<script>
$(document).ready(function() {
    var table = $('#snpMsmeTable').DataTable($.extend(true, {}, DataTableConfig.defaults, {
        ajax: {
            url: "{{ route('snp.msme.data') }}",
            type: "GET",
            dataSrc: 'data'
        },
        columns: [
            { data: 'sn' },
            { data: 'snp_id' },
            { data: 'organization_id' },
            { data: 'organization_name' },
            { data: 'open_msme' },
            { data: 'direct_selection_by_mse' },
            { data: 'onboarded_msme' }
        ],
        buttons: DataTableConfig.getButtons(['csv', 'excel', 'reload'])
    }));
       showToast('Data loaded successfully!', 'success');
});
</script>
@endsection