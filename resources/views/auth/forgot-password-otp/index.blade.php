@extends('components.front.auth-layout-v2')

@section('auth-form')
    <div id="forgot-password-box">
        <div class="d-flex align-items-center mb-4">
            <img src="{{ asset('assets/img/msme-logo.png') }}" loading="lazy" class="img-fluid" style="max-width: 250px;">
        </div>

        <h3 class="fw-bold fs-4 text-dark mb-3">Forgot Password?</h3>
        <p class="text-muted small mb-4">Enter your registered email ID to receive a One-Time Password.</p>

        <form id="forgotPasswordForm" autocomplete="off">
            @csrf
            <div class="mb-3">
                <label class="form-label">Registered Email ID <span class="text-danger">*</span></label>
                <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email ID"  autocomplete="new-password">
                <span class="text-danger form-error" id="email_error"></span>
            </div>

            @if(config('settings.enable_captcha', true))
                <div class="mb-4">
                    <label class="form-label">Enter Captcha Code <span class="text-danger">*</span></label>
                    <div class="d-flex align-items-center gap-2">
                        <span class="captcha-img">{!! captcha_img() !!}</span>
                        <a class="c-reload reload" id="reload" style="cursor:pointer;">
                            <i class="fa fa-refresh" aria-hidden="true"></i>
                        </a>
                        <input type="text" name="captcha" id="captcha" class="form-control" placeholder="Enter captcha code" autocomplete="off">
                    </div>
                </div>
            @endif

            <button type="button" class="btn btn-primary-custom w-100 text-white" id="sendOtpButton">Send OTP</button>

            <div class="text-center mt-4">
                <a href="{{ route('login') }}" class="text-decoration-none" style="color:#1e2a78;font-size:14px;"><i
                        class="fa fa-arrow-left"></i> Back to Login</a>
            </div>
        </form>
    </div>
@endsection

@push('js')
    <script>
        // Function to show loader on button
        function showLoader(button, text) {
            var originalText = button.html();
            button.data('original-text', originalText);
            button.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true" style="color: #000000ff; border-right-color: rgba(255, 215, 0, 0.2);"></span>' + text);
            button.attr('disabled', true);
        }

        // Function to restore button
        function restoreButton(button) {
            var originalText = button.data('original-text');
            if (originalText) {
                button.html(originalText);
            }
            button.attr('disabled', false);
        }

        $(document).ready(function () {
            $('#reload').click(function () {
                reloadCaptcha({
                    captchaImageElement: ".captcha-img"
                });
            });

            $("#sendOtpButton").on("click", function (e) {
                e.preventDefault();
                var email = $("#email").val();
                var captcha = $("#captcha").val();

                if (!email) {
                    toastr.error("Email is required");
                    return;
                }

                var btn = $(this);
                showLoader(btn, "Sending OTP...");

                $.ajax({
                    url: "{{ route('forgot-password-otp-send') }}",
                    type: 'POST',
                    data: {
                        "_token": $('meta[name="csrf-token"]').attr('content'),
                        "email": email,
                        "captcha": captcha,
                    },
                    success: function (data) {
                        restoreButton(btn);
                        if (data.status) {
                            toastr.success(data.message);
                            setTimeout(function () {
                                window.location.href = "{{ route('forgot-password-otp-verify') }}";
                            }, 1000);
                        } else {
                            $('#reload').click();
                            $('#captcha').val('');
                            if (data.errors && typeof data.errors === 'object') {
                                $.each(data.errors, function (key, val) {
                                    toastr.error(val[0]);
                                });
                            } else if (data.message) {
                                toastr.error(data.message);
                            }
                        }
                    },
                    error: function (xhr) {
                        restoreButton(btn);
                        $('#reload').click();
                        $('#captcha').val('');
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            toastr.error(xhr.responseJSON.message);
                        } else {
                            toastr.error("An error occurred");
                        }
                    }
                });
            });
        });
    </script>
@endpush