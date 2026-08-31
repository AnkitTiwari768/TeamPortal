@extends('components.admin.content-layout')

@section('styles')
    <link href="{{ asset('assets/css/styles.css') }}" rel="stylesheet" />
@endsection

@section('action-header')
    <div class="btn-group drop-btn">
        @if (empty($isProfile))
            @include('components.admin.buttons.back-button')
        @endif
    </div>
@endsection

@section('card-content')
    <div class="card-body pt-1">
        <div class="card mb-4">
            <div class="card-body">
                @include('np_registration.form')
            </div>
        </div>
    </div>
    @include('components.admin.popup.sms-email')
    @include('components.admin.file-upload')
@endsection

@section('js')
    @include('np_registration.form_script')
@endsection
