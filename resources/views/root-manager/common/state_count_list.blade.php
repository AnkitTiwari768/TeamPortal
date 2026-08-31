@extends('components.admin.content-layout')

@section('card-content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<div class="card-body">
   

    <div class="table-responsive">
        <table class="table table-bordered table-hover w-100" id="stateCountTable">
            <thead class="table-dark">
                <tr>
                    <th width="5%">#</th>
                    <th width="25%">State Name</th>
                    <th width="20%">Count</th>
              
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
    $('#stateCountTable').DataTable($.extend(true, {}, DataTableConfig.defaults, {
        ajax: {
            url: "{{ route('state.count.msme.data') }}",
            type: "GET",
            dataSrc: 'data'
        },
        columns: [
            { data: 'sn' },
            { data: 'state_name'},
            { data: 'count'},
           
        ],
        buttons: DataTableConfig.getButtons(['csv', 'excel', 'reload'])
    }));
      showToast('Data loaded successfully!', 'success');
});
</script>
@endsection