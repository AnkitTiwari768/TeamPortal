{{--
    Component: components/table.blade.php
    Reusable listing DataTable shell.

    Variables expected:
        $tableId   — HTML id attribute (default: alloc-datatable)
        $columns   — array of column headers
        $editMode  — bool (default false)
--}}
@php
    $tableId = $tableId ?? 'alloc-datatable';
    $columns  = $columns  ?? [
        '#', 'Financial Year', 'Duration', 'Sub Duration',
        'Sanction Order No.', 'Sanction Order Date', 'Total Amount', 'Actions'
    ];
@endphp

<div class="alloc-table-wrapper">
    <table id="{{ $tableId }}" class="alloc-table display nowrap" style="width:100%;">
        <thead>
            <tr>
                @foreach($columns as $col)
                    <th>{{ $col }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            {{-- Populated by DataTables via AJAX --}}
        </tbody>
    </table>
</div>
