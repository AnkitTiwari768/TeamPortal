@extends('components.admin.content-layout')

@section('action-header')
<div class="btn-group drop-btn">
    @if(empty($isProfile))
        @include('components.admin.buttons.back-button')
    @endif
</div>
@endsection

@section('card-content') 

<div class="card-body pt-1">      
    <form id="formId" method="POST" action="{{ !empty($row['id']) ? url('/ca-user/' . $row['id']) : url('/ca-user') }}">
        @csrf
        @if(!empty($row['id']))
            <input type="hidden" name="id" value="{{ $row['id'] }}">
        @endif
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between"> 
                <h6 class="m-0 box-heading heading-1">
                    @if(!empty($row['id']))
                        @if(!empty($isProfile))
                            {{ __('message.your_profile') }}
                        @else
                            {{ __('Edit CA') }}
                        @endif
                    @else
                        {{ __('message.personal_details') }}
                    @endif
                </h6>
            </div>

            <div class="card-body">
                <div class="row g-3">
                    <div class="form-group col-md-4">
                        <label class="form-label required">{{ __('message.first_name') }}</label>
                        <input type="text" class="form-control txtOnly" maxlength="{{ config('constant.MAXLENGTH2') }}"
                            name="first_name" id="first_name" placeholder="{{ __('message.first_name') }}"
                            value="{{ old('first_name', $row['first_name'] ?? '') }}">
                        <span class="text-danger form-error" id="first_name_error"></span>
                    </div>

                    @if (!isset($isCustom))
                    <div class="form-group col-md-4">
                        <label class="">{{ __('message.middle_name') }}</label>
                        <input type="text" class="form-control txtOnly" maxlength="{{ config('constant.MAXLENGTH2') }}"
                            name="middle_name" id="middle_name" placeholder="{{ __('message.middle_name') }}"
                            value="{{ old('middle_name', $row['middle_name'] ?? '') }}">
                        <span class="text-danger form-error" id="middle_name_error"></span>
                    </div>
                    @endif

                    <div class="form-group col-md-4">
                        <label class="form-label required">{{ __('message.last_name') }}</label>
                        <input type="text" class="form-control txtOnly" maxlength="{{ config('constant.MAXLENGTH2') }}"
                            name="last_name" id="last_name" placeholder="{{ __('message.last_name') }}"
                            value="{{ old('last_name', $row['last_name'] ?? '') }}">
                        <span class="text-danger form-error" id="last_name_error"></span>
                    </div>

                    <div class="form-group col-md-4 mb-3">
                        <label class="form-label required label_email">{{ __('message.email') }}</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="email" id="email"
                                placeholder="{{ __('message.email') }}"
                                maxlength="{{ config('constant.EMAIL_LENGTH') }}"
                                value="{{ old('email', $row['email'] ?? '') }}">
                        </div>
                        <span class="text-danger form-error" id="email_error"></span>
                    </div> 

                    <div class="form-group col-md-4 mb-3">
                        <label class="form-label required label_mobile">{{ __('message.mobile') }}</label>
                        <div class="input-group">
                            <input type="text" class="form-control numeric" name="mobile" id="mobile"
                                placeholder="{{ __('message.mobile') }}"
                                maxlength="{{ config('constant.MOBILE_LENGTH') }}"
                                value="{{ old('mobile', $row['mobile'] ?? '') }}">
                        </div> 
                        <span class="text-danger form-error" id="mobile_error"></span>
                    </div> 

                    @if (empty($row) || empty($row['id']))
                    <div class="form-group col-md-4">
                        <label class="form-label required">{{ __('message.password') }}</label>
                        <input type="password" class="form-control"
                            maxlength="{{ config('constant.PASSWORDLENGTH') }}"
                            name="password" id="password"
                            placeholder="{{ __('message.password') }}">
                        <span class="text-danger form-error" id="password_error"></span>
                    </div>

                    <div class="form-group col-md-4">
                        <label class="form-label required">{{ __('message.confirm_password') }}</label>
                        <input type="password" class="form-control"
                            maxlength="{{ config('constant.PASSWORDLENGTH') }}"
                            name="password_confirmation" id="password_confirmation"
                            placeholder="{{ __('message.confirm_password') }}">
                        <span class="text-danger form-error" id="password_confirmation_error"></span>
                    </div>
                    @endif
                </div>      
            </div>

            <div class="card-body"> 
                <div class="row g-3">
                    <div class="form-action mt-3 mb-3">
                        <button type="submit" id="submit-btn" class="btn btn-primary">Submit</button>
                        <a href="javascript:void(0);" class="btn btn-warning wave-effect has-ripple" onclick="history.back(-1);">Cancel</a>
                    </div>  
                </div>
            </div> 
        </div> 
    </form>
</div>

@endsection

@section('js')

<!-- CryptoJS for password encryption -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    @if(isset($isProfile))
        $("#roles").prop("disabled", true);
    @endif

    function crypto(secret) {
        if (secret && secret.length > 0) {
            const salt = CryptoJS.enc.Hex.parse("{{ $crypto_salt ?? 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6' }}");
            const iv = CryptoJS.enc.Hex.parse("{{ $crypto_iv ?? '00112233445566778899aabbccddeeff' }}");
            const key = CryptoJS.PBKDF2("{{ $crypto_key ?? 'your-secure-key' }}", salt, {
                hasher: CryptoJS.algo.SHA512,
                keySize: {{ $crypto_key_size ?? 8 }},
                iterations: {{ $crypto_iterations ?? 1000 }}
            });
            const encrypted = CryptoJS.AES.encrypt(secret, key, { iv: iv });
            return {
                ciphertext: CryptoJS.enc.Base64.stringify(encrypted.ciphertext)
            };
        }
        return null;
    }

    $("#formId").on("submit", function (event) {
        event.preventDefault();

        let formArray = $(this).serializeArray();
        let formData = new FormData();

        @if (empty($row) || empty($row['id']))
            formArray = formArray.filter(item => item.name !== 'password' && item.name !== 'password_confirmation');

            let password = $("#password").val();
            let confirmPassword = $("#password_confirmation").val();

            if (password) {
                const encryptedPass = crypto(password);
                if (encryptedPass) formData.append('password', encryptedPass.ciphertext);
            }

            if (confirmPassword) {
                const encryptedConfirm = crypto(confirmPassword);
                if (encryptedConfirm) formData.append('password_confirmation', encryptedConfirm.ciphertext);
            }
        @endif

        // Append form data
        formArray.forEach(item => {
            formData.append(item.name, item.value);
        });

        @if (!empty($row['id']))
            const url = "{{ url('/ca-user/' . $row['id']) }}";
            const method = 'POST';
            formData.append('_method', 'POST');
        @else
            const url = "{{ url('/ca-user') }}";
            const method = 'POST';
        @endif

        fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(async response => {
            if (response.ok) {
                return response.json();
            }
            const error = await response.text();
            throw new Error(error);
        })
        .then(data => {
            if (data.success) {
                window.location.href = "{{ url('ca-user') }}";
            } else {
                console.error('Validation error:', data.errors || data.message);
            }
        })
        .catch(error => {
            console.error('Request failed:', error);
        });
    });

});
</script>
@endsection
