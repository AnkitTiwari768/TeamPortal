{{--
    Reusable NP Registration Card Component
    --------------------------------------------------
    Usage: @include('dashboard.components.np-registration-card', [...])

    Variables:
      $title      – Card heading (e.g. "Seller Network Participants (SNP)")
      $cardClass  – Color variant class: snp-card | bnp-card | claim-card
      $total      – Total registrations (shown in <h5>)
      $totalClass – JS-targeting class on the total <h5>
      $stats      – Array of stat rows:
                    [
                      ['label' => 'Verification Pending', 'value' => 5, 'class' => 'js_class'],
                      ['label' => 'Verified SNP',         'value' => 3, 'class' => 'js_class'],
                      ...
                    ]
                    Stats are rendered in pairs (2 per Bootstrap row).
--}}
<div class="col-lg-3 col-md-6 col-sm-12">
    <div class="card custom-card-nsic {{ $cardClass ?? 'snp-card' }}">
        <div class="custom-card-nsic-header"></div>
        <div class="card-body">
            <h5 class="fw-bold">{{ $title }}</h5>
            <div class="stats-box-nsic">
                <div class="stats-icon"><i class="bi bi-clipboard-check"></i></div>
                <div>
                    <small class="text-primary d-block">Total Registrations</small>
                    <h5 class="fw-bold mb-0 {{ $totalClass ?? '' }}">{{ $total ?? 0 }}</h5>
                </div>
            </div>
            @foreach (array_chunk($stats ?? [], 2) as $row)
                <div class="row">
                    @foreach ($row as $stat)
                        <div class="col-6">
                            <p class="info-label">{{ $stat['label'] }}</p>
                            <span class="info-value {{ $stat['class'] ?? '' }}">{{ $stat['value'] ?? 0 }}</span>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</div>
