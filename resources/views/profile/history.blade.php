@extends('components.admin.content-layout')

@section('action-header')
    <div class="btn-group drop-btn">

    </div>
@endsection

@php

@endphp

@section('card-content')
    <div class="card-body pt-1">
        <div class="card mb-4">
            <div class="card-body">
                <table class="table table-striped table-hover">
                    <tr>
                        <th>Profile Revised On</th>
                        <th>View Snapshot</th>
                    </tr>
                    @foreach ($revisions as $revision)
                        <tr>
                            <td>{{ date('d-m-Y h:i A', strtotime($revision->created_at)) }}</td>
                            <td><a href="{{ url('profile-snapshot/' . $revision->id) }}">View</a></td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
@endsection
