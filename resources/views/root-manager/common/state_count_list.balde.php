@extends('components.admin.content-layout')

@section('card-content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<div class="card-body">
   
<div id="tableLoader" class="text-center py-5">
    <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
    <p class="mt-3">Loading data...</p>
</div>
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
<style>
#tableLoader{
    min-height:300px;
    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:center;
}
</style>
@endsection

@section('css')
@include('common.libraries')
@include('common.datatables-css')
@endsection

@section('js')
@include('common.datatables-js')
<script>
$(document).ready(function () {

    var table = $('#stateCountTable').DataTable(
        $.extend(true, {}, DataTableConfig.defaults, {

            processing: true,

            ajax: {
                url: "{{ route('state.count.msme.data') }}",
                type: "GET",
                dataSrc: function(json) {

                    $('#tableLoader').hide();
                    $('#tableWrapper').show();

                    return json.data;
                },
                error: function() {
                    $('#tableLoader').hide();

                    showToast('Failed to load data!', 'error');
                }
            },

            columns: [
                { data: 'sn' },
                { data: 'state_name' },
                { data: 'count' }
            ],

            buttons: DataTableConfig.getButtons([
                'csv',
                'excel',
                'reload'
            ]),

            initComplete: function () {
                $('#tableLoader').hide();
                $('#tableWrapper').show();

                showToast('Data loaded successfully!', 'success');
            }
        })
    );

});
</script>
@endsection