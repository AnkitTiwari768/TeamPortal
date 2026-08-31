@extends('components.front.auth-layout-v2')

@push('css')
    <style>
        .card.login-card.login-form-page {
            max-width: 950px !important;
            min-width: auto !important;
            width: 95% !important;
            transition: all 0.3s ease;
        }

        @media (max-width: 768px) {
            .card.login-card.login-form-page {
                width: 100% !important;
                border-radius: 0 !important;
            }

            .left-side {
                display: none !important;
            }

            .right-side {
                padding: 20px !important;
            }
        }

        /* ── Modal Result Styling ── */
        .result-row {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .result-row:last-child {
            border-bottom: none;
        }

        .result-label {
            font-size: 11px;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .result-value {
            font-size: 14px;
            color: #1e2a78;
            font-weight: 700;
        }

        .status-banner {
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }

        .modal-content {
            border-radius: 12px;
            overflow: hidden;
        }

        .login_img_bg_2 {
            background: url("{{ asset('assets/img/image-login.png') }}") repeat !important;
            background-size: cover !important;
        }

        body {
            background-image: url("{{ asset('assets/img/login-background.png') }}") !important;
            background-repeat: no-repeat !important;
            background-size: cover !important;
        }
    </style>
@endpush

@section('auth-form')
    <div id="tracking-search-container">
        <div class="d-flex align-items-center mb-4">
            <img src="{{ asset('assets/img/msme-logo.png') }}" loading="lazy" class="img-fluid" style="max-width: 220px;">
        </div>

        <h3 class="fw-bold fs-4 text-dark mb-3">MSE Application Status</h3>
        {{-- <p class="text-primary fw-bold small mb-3">Track your MSME registration</p> --}}

        <div class="tracking-content">
            {{-- <p class="text-muted small mb-4">
                Please enter your Udyam Registration Number or Registered Mobile Number to check your status.
            </p> --}}

            <form id="tracking-form">
                @csrf
                <div class="mb-3">
                    <label class="form-label text-dark fw-semibold small mb-1">
                        Please enter the MSE details <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="udyam_no_or_mobile" class="form-control"
                        placeholder="Enter Udyam Number / Mobile Number" required>
                </div>

                <button type="submit" class="btn btn-primary-custom w-100 text-white submit-btn shadow-sm mt-2">
                    CHECK STATUS
                </button>

                <button type="button" id="clear-btn" class="btn btn-success w-100 mt-2" style="display:none;"> CLEAR </button>
            </form>
             <div id="status-result-box" class="mt-3"></div>
        </div>
    </div>

    <!-- <div class="modal fade" id="trackingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" style="color: #1e2a78;">Application Detail</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="modal-result-content"></div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div> -->
@endsection

@section('js')
    <script>
        $(document).ready(function () {

            /*$('#trackingModal').on('hidden.bs.modal', function () {
                location.reload();
            });

            $('#tracking-form').on('submit', function (e) {
                e.preventDefault();
                var $btn = $(this).find('.submit-btn');
                var data = $(this).serialize();

                $btn.prop('disabled', true).html('Fetching details...');

                var checkUrl = "{{ url('msme-application-status-check') }}";

                $.post(checkUrl, data, function (res) {
                    if (res.status) {
                        renderResult(res);

                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open');

                        var modalEl = document.getElementById('trackingModal');
                        if (window.bootstrap && window.bootstrap.Modal) {
                            var myModal = new bootstrap.Modal(modalEl, {
                                backdrop: false,
                                keyboard: true
                            });
                            myModal.show();
                        } else {
                            $('#trackingModal').modal({
                                backdrop: false,
                                keyboard: true
                            });
                            $('#trackingModal').modal('show');
                        }
                    } else {
                        toastr.error(res.message);
                    }
                }).fail(function (xhr) {
                    var msg = 'Unable to fetch status. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    toastr.error(msg);
                }).always(function () {
                    $btn.prop('disabled', false).html('CHECK STATUS');
                });
            });

            function renderResult(data) {
                var bannerClass = data.badge_class || 'bg-success';
                var bannerIcon = data.badge_icon ? '<i class="fa ' + data.badge_icon + ' me-2"></i>' : '<i class="fa fa-check-circle me-2"></i>';

                var html = '<div class="alert ' + bannerClass + ' text-white status-banner mb-0"><b>' + bannerIcon + data
                    .message + '</b></div>';

                $('#modal-result-content').html(html);
            }*/

        $('#tracking-form').on('submit', function (e) {

            e.preventDefault();

            let $btn = $('.submit-btn');
            let data = $(this).serialize();
            let url  = "{{ url('msme-application-status-check') }}";

            clearTimeout(window.hideTimer);

            $('#status-result-box').stop(true,true).hide().html('');

            $btn.prop('disabled', true).html('Checking...');

            $.post(url, data, function (res) {

                let alertClass = 'alert-info';
                let message    = '';

                if (res.status) {

                    message = res.data.message;

                    if (res.data.badge_class.includes('success')) {
                        alertClass = 'alert-success';
                    }
                    else if (res.data.badge_class.includes('danger')) {
                        alertClass = 'alert-danger';
                    }
                    else if (res.data.badge_class.includes('warning')) {
                        alertClass = 'alert-warning';
                    }

                } else {

                    message    = res.message;
                    alertClass = 'alert-danger';
                }

                $('#status-result-box').html(`
                    <div class="alert ${alertClass}" role="alert">
                        ${message}
                    </div>
                `).fadeIn();

                $('#clear-btn').fadeIn();

                // 1 minute baad hide + refresh
                window.hideTimer = setTimeout(function () {

                    $('#status-result-box').fadeOut(800);
                    $('#clear-btn').fadeOut(800);

                    $('#tracking-form')[0].reset();

                    location.reload();

                }, 60000);

            }).fail(function () {

                $('#status-result-box').html(`
                    <div class="alert alert-danger" role="alert">
                        Something went wrong.
                    </div>
                `).fadeIn();

                $('#clear-btn').fadeIn();

            }).always(function () {

                $btn.prop('disabled', false).html('CHECK STATUS');

            });

    });


    $('#clear-btn').on('click', function () {

        clearTimeout(window.hideTimer);

        $('#tracking-form')[0].reset();

        $('#status-result-box').fadeOut(300, function () {
            $(this).html('');
        });

        $('#clear-btn').fadeOut(300);

    });


});
    </script>
@endsection