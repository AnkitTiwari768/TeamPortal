@extends('components.front.auth-layout-v2')

@section('auth-form')
    <style>
        .show-password {
            right: 10px !important;
            top: 10px !important;
            cursor: pointer;
        }
    </style>
    <div id="reset-password-box">
        <div class="d-flex align-items-center mb-4">
            <img src="{{ asset('assets/img/msme-logo.png') }}" loading="lazy" class="img-fluid" style="max-width: 250px;">
        </div>

        <h3 class="fw-bold fs-4 text-dark mb-3">Reset Password</h3>
        <p class="text-muted small mb-4">Please enter a new password for your account.</p>

        <form id="resetPasswordForm">
            @csrf
            
            <div class="mb-3">
                <label class="form-label">Enter New Password <span class="text-danger">*</span></label>
                <div class="position-relative">
                    <input type="password" class="form-control" name="password" id="password" placeholder="Enter new password">
                    <span class="position-absolute show-password" id="togglePassword">
                        <i class="fa fa-eye"></i>
                    </span>
                </div>
                <small class="text-muted" style="font-size: 11px;">Password Policy: Minimum 8 characters, 1 uppercase, 1 lowercase, 1 number, 1 special character.</small>
            </div>

            <div class="mb-4">
                <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                <div class="position-relative">
                    <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" placeholder="Confirm new password">
                    <span class="position-absolute show-password" id="toggleConfirmPassword">
                        <i class="fa fa-eye"></i>
                    </span>
                </div>
            </div>

            <button type="button" class="btn btn-primary-custom w-100 text-white" id="confirmResetButton">Confirm</button>
        </form>
    </div>
@endsection

@push('js')
<script>
    // Crypto logic equivalent from the other flow
    function cryptoJS(secret) {
        if (secret.length > 0) {
            var salt = CryptoJS.enc.Hex.parse("{{ $crypto_salt ?? '' }}");
            var iv = CryptoJS.enc.Hex.parse("{{ $crypto_iv ?? '' }}");
            var key = CryptoJS.PBKDF2(
                "{{ $crypto_key ?? '' }}",
                salt, {
                    hasher: CryptoJS.algo.SHA512,
                    keySize: {{ $crypto_key_size ?? 256 }},
                    iterations: {{ $crypto_iterations ?? 1000 }}
                }
            );
            var encrypted = CryptoJS.AES.encrypt(secret, key, {
                iv: iv
            });
            var encryptedData = {
                ciphertext: CryptoJS.enc.Base64.stringify(encrypted.ciphertext),
                salt: CryptoJS.enc.Hex.stringify(salt),
                iv: CryptoJS.enc.Hex.stringify(iv)
            };
            return encryptedData;
        }
    }

    $(document).ready(function() {
        $('#togglePassword').on('click', function() {
            var passwordField = $('#password');
            var icon = $(this).find('i');
            if (passwordField.attr('type') === 'password') {
                passwordField.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                passwordField.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        $('#toggleConfirmPassword').on('click', function() {
            var passwordField = $('#password_confirmation');
            var icon = $(this).find('i');
            if (passwordField.attr('type') === 'password') {
                passwordField.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                passwordField.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        $("#confirmResetButton").on("click", function(e) {
            e.preventDefault();
            var password = $("#password").val();
            var password_confirmation = $("#password_confirmation").val();

            if(!password || !password_confirmation) {
                toastr.error("Both password fields are required");
                return;
            }

            if (password.length < 8) {
                toastr.error("Password does not meet required criteria.");
                return;
            }

            if (password !== password_confirmation) {
                toastr.error("Passwords do not match.");
                return;
            }

            var encryptedPassword = password;
            var encryptedConfirm = password_confirmation;

            // Optional front-end crypto setup based on existing token approach
            @if(isset($crypto_salt) && !empty($crypto_salt))
                try {
                    var crypto = cryptoJS(password);
                    var cryptoCP = cryptoJS(password_confirmation);
                    if (crypto && cryptoCP) {
                        encryptedPassword = crypto.ciphertext;
                        encryptedConfirm = cryptoCP.ciphertext;
                    }
                } catch(e) {}
            @endif

            var btn = $(this);
            btn.attr("disabled", true).html("Processing...");

            $.ajax({
                url: "{{ route('forgot-password-otp-reset-post') }}",
                type: 'POST',
                data: {
                    "_token": $('meta[name="csrf-token"]').attr('content'),
                    "password": encryptedPassword,
                    "password_confirmation": encryptedConfirm,
                },
                success: function(data) {
                    btn.attr("disabled", false).html("Confirm");
                    if (data.status) {
                        toastr.success(data.message);
                        setTimeout(function() {
                            window.location.href = data.url;
                        }, 1000);
                    } else {
                        if(data.errors && typeof data.errors === 'object') {
                            $.each(data.errors, function(key, val) {
                                toastr.error(val[0]);
                            });
                        } else if (data.message) {
                            toastr.error(data.message);
                        }
                    }
                },
                error: function(xhr) {
                    btn.attr("disabled", false).html("Confirm");
                    toastr.error(xhr.responseJSON?.message || "An error occurred");
                }
            });
        });
    });
</script>
@endpush
