@extends('components.admin.content-layout')

@section('page-content')
    @include('dashboard.components.role-dashboard-layout', [
        'roleIdPrefix' => 'nsic'
    ])
@endsection

@section('js')
    <script src="https://code.highcharts.com/highcharts.js"></script>
    @include('dashboard.datepicker')

    {{-- Include our new shared filters --}}
    @include('dashboard.components.shared-filters-js', [
        'tabPrefix' => 'nsic'
    ])

    @include('dashboard.dashboard-js')
@endsection
