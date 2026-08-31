@extends('components.admin.layout')
@section('page-content')
<div class="container-fluid">     
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex- align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"> {{ __('message.change_password') }}</h6>
                    
                    @include('components.admin.buttons.back-button')
                </div>
                <div class="card-body">
                     <form id="change-password-form"  autocomplete="off">
                        @if ($is_profile)
                        <div class="row mb-3">
                            
                            <div class="form-group col-md-6">
                                <label class="form-label required">{{ __('message.current_password') }}</label>
                                <input type="password" class="form-control" name="current_password" id="current_password" placeholder="{{ __('message.current_password') }}" autocomplete="current-password" />
                                <span class="text-danger form-error" id="current_password_error"></span>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="form-label required">{{ $is_profile ? __('message.new_password') : __('message.password') }}</label>
                                <input type="password" class="form-control" name="password" id="password" placeholder="{{ $is_profile ? __('message.new_password') : __('message.password') }}"    autocomplete="new-password" />
                                <span class="text-danger form-error" id="password_error"></span>
                            </div>
                            
                        </div>
                        @endif
                        <div class="row mb-3">
                            
                            <div class="form-group col-md-6">
                                <label class="form-label required">{{ __('message.confirm_password') }}</label>
                                <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" placeholder="{{ __('message.confirm_password') }}" autocomplete="new-password"  />
                                <span class="text-danger form-error" id="password_confirmation_error"></span>
                            </div>
                            
                        </div>
                        <button type="submit" id="change-password-btn" class="btn btn-primary" >
                            {{ __('message.change_password') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
 

@section('js')

    <script>
        function crypto(secret) {
            if (secret.length > 0) {
                var salt = CryptoJS.enc.Hex.parse("{{ $crypto_salt }}");
                var iv = CryptoJS.enc.Hex.parse("{{ $crypto_iv }}");
                var key = CryptoJS.PBKDF2(
                    "{{ $crypto_key }}", 
                    salt, { 
                        hasher: CryptoJS.algo.SHA512, 
                        keySize: {{ $crypto_key_size }}, 
                        iterations: {{ $crypto_iterations }} 
                    }
                ); 
                var encrypted = CryptoJS.AES.encrypt(secret, key, {iv: iv});
                var encryptedData = {
                    ciphertext : CryptoJS.enc.Base64.stringify(encrypted.ciphertext),
                    salt : CryptoJS.enc.Hex.stringify(salt),
                    iv : CryptoJS.enc.Hex.stringify(iv)    
                };  
                return encryptedData;
            }
        } 

    @isset($id)

        //change password form     
        $("#change-password-form").on("submit", function (event) {
            event.preventDefault();
            var password = $("#password").val();
            var passwordConfirmation = $("#password_confirmation").val();
            var csrfToken = "{{ csrf_token() }}";
            var method = 'POST';
            var url = "{{ url('update-password/') }}";
         

            if (password) {
                password = crypto(password).ciphertext;
            }

            if (passwordConfirmation) {
                passwordConfirmation = crypto(passwordConfirmation).ciphertext;
            }

            @isset($is_profile)

                var currentPassword = $("#current_password").val();
                if (currentPassword) {
                    currentPassword = crypto(currentPassword).ciphertext;
                }

            @endisset
           
            var requestData = {
                url: url,
                method: method,
                body: {
                    id: "{{$id}}",
                    password: password,
                    password_confirmation: passwordConfirmation,
                    @isset($is_profile)
                        current_password: currentPassword,
                    @endisset
                    _token: csrfToken
                }
            };

                var redirectTo = "{{ url('/logout') }}";
                sendRequest(requestData, redirectTo, true);

        });

       
    @endisset
    </script>
@endsection
@endsection
