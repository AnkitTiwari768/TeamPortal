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

        <h3 class="fw-bold fs-4 text-dark mb-3">NP Application Status</h3>
        <!-- <p class="text-primary fw-bold small mb-3">Track your Provider registration</p> -->

        <div class="tracking-content mb-3">
            <!-- <p class="text-muted small mb-4">
                        Please enter your registered Email ID or Mobile Number to check your status.
                    </p> -->

            <form id="tracking-form">
                @csrf
                <div class="mb-3">
                    <label class="form-label text-dark fw-semibold small mb-1">
                        Email ID or Mobile Number <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="email_or_mobile" class="form-control"
                        placeholder="Enter Email / Mobile Number" required>
                </div>

                <button type="submit" class="btn btn-primary-custom w-100 text-white submit-btn shadow-sm mt-2">
                    CHECK STATUS
                </button>

                <button type="button" id="clear-btn" class="btn btn-success w-100 mt-2" style="display:none;">
            CLEAR
        </button>
            </form>
           
        </div>
        
            <!-- <div class="alert alert-success" role="alert">
                You are already registered on TEAM Portal.
                </div>
                <div class="alert alert-danger" role="alert">
               You are already registered on TEAM Portal.
                </div>
                <div class="alert alert-warning" role="alert">
                You are already registered on TEAM Portal.
            </div> -->

            <div id="status-result-box" class="mt-3"></div>
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
            
            $('#tracking-form').on('submit', function (e) {

        e.preventDefault();

        let $btn = $('.submit-btn');
        let data = $(this).serialize();
        let url  = "{{ url('np-application-status-check') }}";

        clearTimeout(window.hideTimer);

        $('#status-result-box').hide().html('');

        $btn.prop('disabled', true).html('Checking...');

        $.post(url, data, function (res) {

            let alertClass = 'alert-info';
            let message = '';

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

                message = res.message;
                alertClass = 'alert-danger';
            }

            $('#status-result-box').html(`
                <div class="alert ${alertClass}" role="alert">
                    ${message}
                </div>
            `).fadeIn();

            $('#clear-btn').fadeIn();

            window.hideTimer = setTimeout(function () {

                $('#status-result-box').fadeOut(800);
                $('#clear-btn').fadeOut(800);

                $('#tracking-form')[0].reset();

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


    // Clear Button Click
    $('#clear-btn').on('click', function () {

        clearTimeout(window.hideTimer);

        $('#tracking-form')[0].reset();

        $('#status-result-box').fadeOut(300, function () {
            $(this).html('');
        });

        $('#clear-btn').fadeOut(300);

    });



            /*$('#trackingModal').on('hidden.bs.modal', function () {
                location.reload();
            });

            $('#tracking-form').on('submit', function (e) {
                e.preventDefault();
                var $btn = $(this).find('.submit-btn');
                var data = $(this).serialize();

                $btn.prop('disabled', true).html('Fetching details...');

                var checkUrl = "{{ url('np-application-status-check') }}";

                $.post(checkUrl, data, function (res) {
                    if (res.status) {
                        renderResult(res);

                        // Backdrop Fix
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
            });*/

            /*
            function renderResult(response) {
                var data = response.data;
                var bannerClass = data.badge_class || 'bg-success';
                var bannerIcon = data.badge_icon ? '<i class="fa ' + data.badge_icon + ' me-2"></i>' : '<i class="fa fa-check-circle me-2"></i>';

                var html = '<div class="alert ' + bannerClass + ' text-white status-banner"><b>' + bannerIcon + data.message + '</b></div>';

                /*if (data.tab && data.tab.is_timeline_enabled && data.timeline && data.timeline.length > 0) {
                    html += '<div class="mb-4"><h6 class="fw-bold mb-3 text-muted small">PROCESSING PROGRESS</h6>';
                    html += '<div class="d-flex justify-content-between position-relative mb-4" style="padding: 0 10px;">';

                    data.timeline.forEach(function (stage, index) {
                        var isActive = stage.current;
                        var isDone = stage.completed;

                        var circleColor = isActive ? (stage.color || 'success') : 'secondary';
                        if (circleColor.startsWith('bg-')) circleColor = circleColor.replace('bg-', '');

                        var opacity = (isActive || isDone) ? '1' : '0.3';
                        var textColor = isActive ? 'text-primary fw-bold' : 'text-muted';

                        html += '<div class="text-center" style="flex: 1; z-index: 2; opacity: ' + opacity + '">';
                        html += '<div class="rounded-circle bg-' + circleColor + ' text-white d-flex align-items-center justify-content-center mx-auto mb-2" style="width:30px; height:30px; font-size: 12px; font-weight: bold; ' + (isActive ? 'box-shadow: 0 0 0 3px rgba(0,0,0,0.1);' : '') + '">' + (index + 1) + '</div>';
                        html += '<div class="small ' + textColor + '" style="font-size: 10px; line-height:1.2;">' + stage.label + '</div>';
                        html += '</div>';
                    });

                    html += '<div class="position-absolute" style="height:2px; background:#eee; top:15px; left:10%; right:10%; z-index:1;"></div>';
                    html += '</div></div>';
                }*/
                /*
                if (data.tab && data.tab.is_timeline_enabled && data.timeline && data.timeline.length > 0) {

                    html += '<div class="mb-4">';
                    html += '<h6 class="fw-bold mb-3 text-muted small">PROCESSING PROGRESS</h6>';

                    html += '<div class="d-flex justify-content-between align-items-start">';

                    data.timeline.forEach(function(stage, index){

                        var active = stage.current || stage.completed;
                        var color = active ? (stage.color || 'success') : 'secondary';

                        if(color.startsWith('bg-')){
                            color = color.replace('bg-','');
                        }

                        html += '<div class="text-center flex-fill">';

                        html += '<div class="rounded-circle bg-'+color+' text-white d-flex align-items-center justify-content-center mx-auto mb-2" style="width:34px;height:34px;font-weight:bold;">'+(index+1)+'</div>';

                        html += '<div style="font-size:11px;" class="'+(active ? 'text-primary fw-bold':'text-muted')+'">'+stage.label+'</div>';

                        if(index < data.timeline.length - 1){

                            var lineColor = stage.completed ? '#198754' : '#dddddd';

                            html += '<div style="height:4px;background:'+lineColor+';margin:10px -50% 0 50%;position:relative;top:-48px;z-index:0;"></div>';
                        }

                        html += '</div>';

                    });

                    html += '</div>';
                    html += '</div>';
                }

                var hideData = data.hide_data_section || false;
                var displayData = data.display_data || [];

                if (!hideData && displayData.length > 0) {
                    html += '<div class="registration-info-section">';
                    html += '<h6 class="fw-bold mb-2 text-muted small">REGISTRATION INFO</h6>';
                    displayData.forEach(function (item) {
                        var displayVal = (item.value !== null && item.value !== undefined && item.value !== '') ? item.value : '—';
                        html += '<div class="result-row"><div class="result-label">' + item.label + '</div>';
                        html += '<div class="result-value">' + displayVal + '</div></div>';
                    });
                    html += '</div>';
                }

                $('#modal-result-content').html(html);

                if (hideData) {
                    $('.registration-info-section').hide();
                }
            }*/
        });
    </script>
@endsection