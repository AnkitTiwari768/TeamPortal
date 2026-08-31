@extends('components.front.auth-layout-v2')

@section('auth-form')
    <div id="verify-otp-box">
        <div class="otp-screen">
            <div class="d-flex align-items-center mb-4">
                <img src="{{ asset('assets/img/msme-logo.png') }}" loading="lazy" class="img-fluid"
                    style="max-width: 250px;">
            </div>

            <h3 class="fw-bold fs-4 text-dark mb-3">Forgot Password</h3>

            <h4>Verify OTP</h4>
            <p>Enter the 6-digit OTP received on the registered email
                <strong id="otp-email">{{ $email }}</strong>
            </p>

            <div class="otp-inputs">
                <input type="text" maxlength="1" class="otp-box" inputmode="numeric">
                <input type="text" maxlength="1" class="otp-box" inputmode="numeric">
                <input type="text" maxlength="1" class="otp-box" inputmode="numeric">
                <input type="text" maxlength="1" class="otp-box" inputmode="numeric">
                <input type="text" maxlength="1" class="otp-box" inputmode="numeric">
                <input type="text" maxlength="1" class="otp-box" inputmode="numeric">
            </div>

            <div class="resend-text mt-3">
                <p class="resend-message mb-1">
                    You can resend OTP in
                    <span id="otp_timer" class="otp-timer text-primary text-bold">00:30</span>
                </p>
                <a class="resend-otp" id="resend-otp" style="cursor:pointer; display:none;">Resend OTP</a>
            </div>

            <button type="button" class="verify-btn mt-4" id="verifyOtpButton">Verify OTP</button>

            <div class="text-center mt-4">
                <a href="{{ route('forgot-password-otp') }}" class="text-decoration-none"
                    style="color:#1e2a78;font-size:14px;"><i class="fa fa-arrow-left"></i> Back</a>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        var interval;
        var lockInterval;
        var resendDuration = 30; // 30 seconds

        // Function to show loader on button
        function showLoader(button, text) {
            var originalText = button.html();
            button.data('original-text', originalText);
            button.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' + text);
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

        function checkLockStatus() {
            $.ajax({
                url: "{{ route('forgot-password-otp-check-lock') }}",
                type: 'GET',
                success: function (data) {
                    if (data.locked) {
                        startLockTimer(data.remaining_seconds);
                    }
                }
            });
        }

        function formatTimeRemaining(totalSeconds) {
            var hours = Math.floor(totalSeconds / 3600);
            var minutes = Math.floor((totalSeconds % 3600) / 60);
            var seconds = totalSeconds % 60;

            var formatted = "";
            if (hours > 0) {
                formatted += (hours < 10 ? "0" + hours : hours) + ":";
            }
            formatted += (minutes < 10 ? "0" + minutes : minutes) + ":";
            formatted += (seconds < 10 ? "0" + seconds : seconds);
            return formatted;
        }

        function startLockTimer(remainingSeconds) {
            $('.otp-box').val('').attr('disabled', true);
            $('#verifyOtpButton').attr('disabled', true).html("Verify OTP");
            $(".resend-message").hide();
            $("#resend-otp").hide();

            if ($("#lock-message-box").length === 0) {
                $(".resend-text").after('<div id="lock-message-box" class="alert alert-danger mt-3 text-center" style="font-weight:bold;"><span id="lock-text">Account locked. Try again in </span><span id="lock-timer"></span></div>');
            } else {
                $("#lock-message-box").show();
            }

            $("#lock-timer").text(formatTimeRemaining(remainingSeconds));

            clearInterval(lockInterval);
            clearInterval(interval);

            lockInterval = setInterval(function () {
                remainingSeconds--;
                if (remainingSeconds <= 0) {
                    clearInterval(lockInterval);
                    $("#lock-message-box").hide();

                    $('.otp-box').attr('disabled', false);
                    $('#verifyOtpButton').attr('disabled', false);
                    $("#resend-otp").show();
                } else {
                    $("#lock-timer").text(formatTimeRemaining(remainingSeconds));
                }
            }, 1000);
        }

        function startTimer() {
            $(".resend-message").show();
            $("#resend-otp").hide();
            var counter = 0;
            $("#otp_timer").text("00:30");

            clearInterval(interval);
            interval = setInterval(function () {
                var timeLeft = resendDuration - counter;
                if (timeLeft <= 0) {
                    stopTimer();
                    $("#otp_timer").hide();
                    $(".resend-message").hide();
                    $("#resend-otp").show();
                } else {
                    var seconds = timeLeft < 10 ? "0" + timeLeft : timeLeft;
                    $("#otp_timer").text("00:" + seconds).show();
                }
                counter++;
            }, 1000);
        }

        function stopTimer() {
            clearInterval(interval);
        }

        $(document).ready(function () {
            startTimer();
            checkLockStatus();

            $('.otp-box').on('input', function () {
                var val = $(this).val();
                if (isNaN(val)) {
                    $(this).val('');
                    return false;
                }
                if ($(this).val().length === 1) {
                    $(this).next('.otp-box').focus();
                }
            });

            $('.otp-box').on('keydown', function (e) {
                // Backspace deletes and focuses previous
                if (e.key === 'Backspace' && $(this).val() === '') {
                    $(this).prev('.otp-box').focus();
                }
            });

            $("#verifyOtpButton").on("click", function (e) {
                e.preventDefault();
                const inputs = document.querySelectorAll('.otp-box');
                const otp = Array.from(inputs).map(input => input.value).join('');

                if (otp.length < 6) {
                    toastr.error("Please enter a 6-digit OTP");
                    return;
                }

                var btn = $(this);
                showLoader(btn, "Verifying...");

                $.ajax({
                    url: "{{ route('forgot-password-otp-verify-post') }}",
                    type: 'POST',
                    data: {
                        "_token": $('meta[name="csrf-token"]').attr('content'),
                        "otp": otp,
                    },
                    success: function (data) {
                        restoreButton(btn);
                        if (data.status) {
                            toastr.success(data.message);
                            setTimeout(function () {
                                window.location.href = data.url;
                            }, 1000);
                        } else {
                            if (data.locked && data.remaining_seconds) {
                                startLockTimer(data.remaining_seconds);
                            }
                            if (data.message) {
                                toastr.error(data.message);
                            }
                        }
                    },
                    error: function (xhr) {
                        restoreButton(btn);
                        const resp = xhr.responseJSON;
                        if (resp && resp.locked && resp.remaining_seconds) {
                            startLockTimer(resp.remaining_seconds);
                        }
                        toastr.error(resp?.message || "An error occurred");
                    }
                });
            });

            $("#resend-otp").on("click", function () {
                var btn = $(this);
                showLoader(btn, "Resending...");
                btn.css("pointer-events", "none");

                $.ajax({
                    url: "{{ route('forgot-password-otp-send') }}",
                    type: 'POST',
                    data: {
                        "_token": $('meta[name="csrf-token"]').attr('content'),
                        "email": "{{ $email }}",
                        "resend": true
                    },
                    success: function (data) {
                        restoreButton(btn);
                        btn.css("pointer-events", "auto");
                        if (data.status) {
                            toastr.success(data.message);
                            startTimer();
                        } else {
                            if (data.locked && data.remaining_seconds) {
                                startLockTimer(data.remaining_seconds);
                            }
                            if (data.message) {
                                toastr.error(data.message);
                            }
                        }
                    },
                    error: function (xhr) {
                        restoreButton(btn);
                        btn.css("pointer-events", "auto");
                        const resp = xhr.responseJSON;
                        if (resp && resp.locked && resp.remaining_seconds) {
                            startLockTimer(resp.remaining_seconds);
                        }
                        toastr.error(resp?.message || "An error occurred");
                    }
                });
            });
        });
    </script>
@endpush