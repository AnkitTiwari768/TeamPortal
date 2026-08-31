@extends('components.admin.content-layout')

@section('card-content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<div class="card-body">
    

    <div class="table-responsive">
        <table class="table table-bordered table-hover w-100" id="stateMsmeTable">
            <thead class="table-dark">
                <tr>
                    <th width="5%">#</th>
                    <th width="25%">Category Name</th>
                    <th width="20%">Open MSME</th>
                    <th width="25%">Direct Selection by MSE</th>
                    <th width="25%">Onboarded MSE</th>
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
    $('#stateMsmeTable').DataTable($.extend(true, {}, DataTableConfig.defaults, {
        ajax: {
            url: "{{ route('category.wise.msme.data') }}",
            type: "GET",
            dataSrc: 'data'
        },
        columns: [
            { data: 'sn' },
            { data: 'product_category_name'},
            { data: 'open_msme'},
            { data: 'selected_msme'},
            { data: 'onboarded_msme'}
        ],
        buttons: DataTableConfig.getButtons(['csv', 'excel', 'reload'])
    }));
      showToast('Data loaded successfully!', 'success');
});
</script>
@endsection