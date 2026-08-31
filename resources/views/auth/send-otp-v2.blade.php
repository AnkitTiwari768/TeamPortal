@extends('components.front.auth-layout-v2')
@section('auth-form')
		
	<div class="otp-screen">
      <div class="d-flex align-items-center mb-4">
        <img src="{{ asset('assets/img/msme-logo.png') }}" loading="lazy" class="img-fluid" style="max-width: 250px;">

    </div>
	<h3 id="otp-heading"></h3>

        <h4>{{$title}}</h4>
        <p>Enter the OTP received on the registered mobile number 
        <strong id="otp-mobile"></strong>
    </p>

    <div class="otp-inputs">
        <input type="password" maxlength="1" class="otp-box">
        <input type="password" maxlength="1" class="otp-box">
        <input type="password" maxlength="1" class="otp-box">
        <input type="password" maxlength="1" class="otp-box">
        <input type="password" maxlength="1" class="otp-box">
        <input type="password" maxlength="1" class="otp-box">
    </div>

    <div class="resend-text">
        <a class="resend-otp" style="cursor:pointer">Resend OTP</a> in <span id="timer">01:59</span>
    </div>

        <button class="verify-btn">Verify</button>

    </div>
@endsection
@section('js')
   <script src="{{ asset('assets/js/send-otp.js') }}"></script>

    <script>
        window.OTP.init({
            heading: "{{ $heading ?? 'Forgot Udyam Registeration  Number We have  Got You !' }}",
            mobile: "{{ $mobile }}",
            verifyUrl: "{{ $verifyUrl }}",
            resendUrl: "{{ $resendUrl }}",
            redirectUrl: "{{ $redirectUrl }}"
        });
    </script>
@endsection

