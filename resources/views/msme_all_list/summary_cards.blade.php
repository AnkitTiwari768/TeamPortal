{{--
    MSME All List - summary/count cards.
    Rendered once on page load with server-side defaults, then refreshed via
    AJAX (see index.blade.php) whenever a filter changes, so the counts always
    reflect the currently applied filters.
--}}
<div class="row msme-all-list-cards mt-3 mb-4" id="msme_all_list_cards_container">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="msme-all-list-stat-card msme-all-list-stat-card--total">
            <div class="msme-all-list-stat-card__icon">
                <i class="fas fa-store"></i>
            </div>
            <div class="msme-all-list-stat-card__body">
                <p class="msme-all-list-stat-card__label">Total MSEs</p>
                <h3 class="msme-all-list-stat-card__value"><span id="msme_all_list_total_count">{{ $summary['total_msme'] ?? 0 }}</span></h3>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="msme-all-list-stat-card msme-all-list-stat-card--open">
            <div class="msme-all-list-stat-card__icon">
                <i class="fas fa-store"></i>
            </div>
            <div class="msme-all-list-stat-card__body">
                <p class="msme-all-list-stat-card__label">Open MSME Count</p>
                <h3 class="msme-all-list-stat-card__value"><span id="msme_all_list_open_msme_count">{{ $summary['open_msme'] ?? 0 }}</span></h3>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="msme-all-list-stat-card msme-all-list-stat-card--direct">
            <div class="msme-all-list-stat-card__icon">
                <i class="fas fa-hand-pointer"></i>
            </div>
            <div class="msme-all-list-stat-card__body">
                <p class="msme-all-list-stat-card__label">Direct Selection MSME</p>
                <h3 class="msme-all-list-stat-card__value"><span id="msme_all_list_direct_selection_msme_count">{{ $summary['direct_selection_msme'] ?? 0 }}</span></h3>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="msme-all-list-stat-card msme-all-list-stat-card--onboard">
            <div class="msme-all-list-stat-card__icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="msme-all-list-stat-card__body">
                <p class="msme-all-list-stat-card__label">Onboard MSME</p>
                <h3 class="msme-all-list-stat-card__value"><span id="msme_all_list_onboarded_msme_count">{{ $summary['onboarded_msme'] ?? 0 }}</span></h3>
            </div>
        </div>
    </div>
</div>
