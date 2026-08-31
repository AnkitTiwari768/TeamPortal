@php 

  $scripts = [
    'assets/js/jquery-3.7.0.min.js',
    'assets/js/jquery-migrate-3.4.0.min.js',
    'assets/js/bootstrap.bundle.min.js',
    'toastr/toastr.min.js',
    'assets/js/crypto-js.min.js',
    'assets/js/jquery-ui.js',
    'assets/js/common.js',
    'assets/js/verification.js',
    'assets/js/front-script.js',
    'assets/js/validations.js',
    'assets/js/select2.min.js'
  ];

@endphp


@if ($scripts)

  @foreach ($scripts as $script)
  
    <script src="{{ asset($script . '?ver=' . time()) }}"></script>
  
  @endforeach

@endif

@include('components.admin.popup.sms-email')