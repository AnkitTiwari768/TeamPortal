@extends('components.front.auth-layout-v2')

@push('css')
    <style>
        /* ── Responsive Fix ── */
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

        /* ── Tab Styling ── */
        #trackingTab.nav-tabs {
            border-bottom: 2px solid #eee !important;
            display: flex !important;
            flex-wrap: nowrap !important;
        }

        #trackingTab.nav-tabs .nav-link {
            font-size: 14px !important;
            padding: 10px 20px !important;
            font-weight: 600 !important;
            color: #666 !important;
            border: none !important;
            background: none !important;
            margin-right: 5px !important;
        }

        #trackingTab.nav-tabs .nav-link.active {
            color: #1e2a78 !important;
            border-bottom: 3px solid #1e2a78 !important;
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
    </style>
@endpush

@section('auth-form')
    <div id="tracking-search-container">
        <div class="d-flex align-items-center mb-4">
            <img src="{{ asset('assets/img/msme-logo.png') }}" loading="lazy" class="img-fluid" style="max-width: 220px;">
        </div>

        <h3 class="fw-bold fs-4 text-dark mb-3">Know Your Application Status</h3>

        {{-- Tabs --}}
        <ul class="nav nav-tabs mb-2" id="trackingTab" role="tablist">
            @foreach ($tabs as $index => $tab)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $index === 0 ? 'active' : '' }}" id="tab-{{ $tab->tab_key }}"
                        data-bs-toggle="tab" data-bs-target="#content-{{ $tab->tab_key }}" type="button" role="tab">
                        <i class="fa {{ $tab->icon ?: '' }} me-1"></i> {{ $tab->label }}
                    </button>
                </li>
            @endforeach
        </ul>

        {{-- Dynamic Form Content --}}
        <div class="tab-content" id="trackingTabContent">
            @foreach ($tabs as $index => $tab)
                <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="content-{{ $tab->tab_key }}"
                    role="tabpanel">
                    <p class="text-muted small mb-4">
                        {{ $tab->hint_text ?: 'Please enter details below to track status.' }}
                    </p>

                    @if (!empty($tab->searchableFields))
                        <form class="tracking-form" data-tab="{{ $tab->tab_key }}">
                            @foreach ($tab->searchableFields as $field)
                                <div class="mb-3">
                                    <label class="form-label text-dark fw-semibold small mb-1">
                                        {{ $field->field_label }} @if ($field->is_required)
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>
                                    <input type="{{ $field->field_type ?: 'text' }}" name="{{ $field->field_key }}"
                                        class="form-control"
                                        placeholder="{{ $field->placeholder ?: 'Enter ' . $field->field_label }}"
                                        @if ($field->is_required) required @endif>
                                </div>
                            @endforeach

                            <button type="submit"
                                class="btn btn-primary-custom w-100 text-white submit-btn shadow-sm mt-2">
                                CHECK STATUS
                            </button>
                        </form>
                    @else
                        <div class="alert alert-light border small text-muted text-center py-4">
                            No search fields configured for this tab.
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- Result Modal --}}
    <div class="modal fade" id="trackingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
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
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            // Modal Close = Page Refresh
            $('#trackingModal').on('hidden.bs.modal', function() {
                location.reload();
            });

            $('.tracking-form').on('submit', function(e) {
                e.preventDefault();
                var $btn = $(this).find('.submit-btn');
                var data = $(this).serialize() + '&tab=' + $(this).data('tab') +
                    '&_token={{ csrf_token() }}';

                $btn.prop('disabled', true).html('Fetching details...');

                $.post("{{ route('application-status-check') }}", data, function(res) {
                    if (res.status) {
                        renderResult(res.data);

                        // ── Permanent Backdrop Fix ──
                        $('.modal-backdrop').remove(); // Clear any leftovers
                        $('body').removeClass('modal-open'); // Prevent scroll lock

                        var modalEl = document.getElementById('trackingModal');
                        if (window.bootstrap && window.bootstrap.Modal) {
                            // Bootstrap 5
                            var myModal = new bootstrap.Modal(modalEl, {
                                backdrop: false,
                                keyboard: true
                            });
                            myModal.show();
                        } else {
                            // Bootstrap 3/4 (jQuery Bridge)
                            $('#trackingModal').modal({
                                backdrop: false,
                                keyboard: true
                            });
                            $('#trackingModal').modal('show');
                        }
                    } else {
                        toastr.error(res.message);
                    }
                }).fail(function(xhr) {
                    toastr.error('Unable to fetch status. Please try again.');
                }).always(function() {
                    $btn.prop('disabled', false).html('CHECK STATUS');
                });
            });

            function renderResult(data) {
                var bannerClass = data.badge_class || 'bg-success';
                var bannerIcon = data.badge_icon ? '<i class="fa ' + data.badge_icon + ' me-2"></i>' : '';

                var html = '<div class="alert ' + bannerClass + ' text-white status-banner"><b>' + bannerIcon + data
                    .message + '</b></div>';

                // ── Dynamic Timeline ──
                if (data.tab.is_timeline_enabled && data.timeline && data.timeline.length > 0) {
                    html += '<div class="mb-4"><h6 class="fw-bold mb-3 text-muted small">PROCESSING PROGRESS</h6>';
                    html +=
                        '<div class="d-flex justify-content-between position-relative mb-4" style="padding: 0 10px;">';

                    data.timeline.forEach(function(stage, index) {
                        var color = stage.completed ? (stage.color || 'success') : 'secondary';
                        var opacity = stage.completed ? '1' : '0.4';

                        html += '<div class="text-center" style="flex: 1; z-index: 2; opacity: ' + opacity +
                            '">';
                        html += '<div class="rounded-circle bg-' + color +
                            ' text-white d-flex align-items-center justify-content-center mx-auto mb-2" style="width:30px; height:30px; font-size: 12px; font-weight: bold;">' +
                            (index + 1) + '</div>';
                        html += '<div class="small fw-bold ' + (stage.current ? 'text-primary' :
                                'text-dark') + '" style="font-size: 10px; line-height:1.2;">' + stage
                            .label + '</div>';
                        html += '</div>';
                    });

                    html +=
                        '<div class="position-absolute" style="height:2px; background:#eee; top:15px; left:10%; right:10%; z-index:1;"></div>';
                    html += '</div></div>';
                }

                // ── Dynamic Result Fields ──
                if (data.display_data && data.display_data.length > 0) {
                    html += '<h6 class="fw-bold mb-2 text-muted small">REGISTRATION INFO</h6>';
                    data.display_data.forEach(function(item) {
                        var displayVal = (item.value !== null && item.value !== undefined && item.value !==
                            '') ? item.value : '—';
                        html += '<div class="result-row"><div class="result-label">' + item.label +
                        '</div>';
                        html += '<div class="result-value">' + displayVal + '</div></div>';
                    });
                }

                $('#modal-result-content').html(html);
            }
        });
    </script>
@endsection
