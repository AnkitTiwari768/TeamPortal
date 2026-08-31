@extends('components.admin.content-layout')

@section('card-content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<div class="card-body">
  
    <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped w-100" id="msmeSnpTable">
            <thead class="table-dark">
                <tr>
                    <th width="5%">#</th>
                    <th width="10%">MSME ID</th>
                    <th width="10%">Mobile</th>
                    <th width="12%">Udyam No</th>
                    <th width="18%">Enterprise Name</th>
                    <th width="10%">SNP ID</th>
                    <th width="15%">SNP Name</th>
                    <th width="20%">Organization</th>
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
    $('#msmeSnpTable').DataTable($.extend(true, {}, DataTableConfig.defaults, {
        ajax: {
            url: "{{ route('msme.snp.data') }}",
            type: "GET",
            dataSrc: 'data'
        },
        columns: [
            { data: 'sn' },
            { data: 'msme_id' },
            { data: 'mobile' },
            { data: 'udyam_no' },
            { data: 'enterprise_name'},
            { data: 'snp_id'},
            { data: 'snp_name'},
            { data: 'organization_name'}
        ],
        buttons: DataTableConfig.getButtons(['csv', 'excel',  'reload']),
        language: { searchPlaceholder: "Search by MSME, SNP, Mobile..." }
    }));
    showToast('Data loaded successfully!', 'success');
});
</script>
@endsection