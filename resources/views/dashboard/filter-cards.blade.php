<div class="row doc-cards ondc-mse-cards mt-3 mb-4" id="cards-container">
    {{-- ✅ Card 1: Total Registered MSEs --}}
    <div class="col-lg-3 register-mse-card">
        <a href="{{ url('mis-reports-msme')}}" class="">
            <div class="card w-100">
                <div class="card-body d-flex gap-3">
                    <div class="right text-start w-100">
                        <div class="top-header-card">
                            <div class="left align-items-center icon type-1">
                                <img src="{{ asset('assets/ffo-admin/img/document-ico.svg') }}">
                            </div>
                            <span>
                                <p>Total Registered MSEs</p>
                                <h5>
                                    <span id="total_application">{{ $msmeForMeCounts['total_msme'] ?? 0 }}</span>
                                </h5>
                            </span>
                        </div>
                        <div class="card-detail">
                            <ul>
                                @foreach(($msmeForMeCounts['major_activities'] ?? []) as $activity => $count)
                                    <li>
                                        <span>{{ $activity }}</span>
                                        <span>{{ $count }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    {{-- ✅ Card 2: SNP Initiated Mapping --}}
    <div class="col-lg-3 choosen-option-card-1">
        <a href="{{ url('mis-reports-msme')}}" class="">
            <div class="card w-100">
                <div class="card-body d-flex gap-3">
                    <div class="right text-start w-100">
                        <div class="top-header-card">
                            <div class="left align-items-center icon type-2">
                                <img src="{{ asset('assets/ffo-admin/img/document-ico.svg') }}">
                            </div>
                            <span>
                                <p>SNP Initiated Mapping</p>
                                <h5>
                                    <span id="snp_initiated">{{ $msmeCounts['total']['option1'] ?? 0 }}</span>
                                </h5>
                            </span>
                        </div>
                        <div class="card-detail">
                            <ul>
                                @foreach(($msmeCounts['major_activities'] ?? []) as $activity => $counts)
                                    <li>
                                        <span>{{ $activity }}</span>
                                        <span>{{ $counts['option1'] ?? 0 }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    {{-- ✅ Card 3: MSME Initiated Mapping --}}
    <div class="col-lg-3 choosen-option-card-2">
        <a href="{{ url('mis-reports-msme')}}" class="">
            <div class="card w-100">
                <div class="card-body d-flex gap-3">
                    <div class="right text-start w-100">
                        <div class="top-header-card">
                            <div class="left align-items-center icon type-3">
                                <img src="{{ asset('assets/ffo-admin/img/document-ico.svg') }}">
                            </div>
                            <span>
                                <p class="text-nowrap">MSME Initiated Mapping</p>
                                <h5>
                                    <span id="msme_initiated">{{ $msmeCounts['total']['option2'] ?? 0 }}</span>
                                </h5>
                            </span>
                        </div>
                        <div class="card-detail">
                            <ul>
                                @foreach(($msmeCounts['major_activities'] ?? []) as $activity => $counts)
                                    <li>
                                        <span>{{ $activity }}</span>
                                        <span>{{ $counts['option2'] ?? 0 }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    {{-- ✅ Card 4: Onboarded MSEs --}}
    <div class="col-lg-3 onboarded-card">
        <a href="{{ url('msme-snp-mapping-report') }}" class="">
            <div class="card w-100">
                <div class="card-body d-flex gap-3">
                    <div class="right text-start w-100">
                        <div class="top-header-card">
                            <div class="left align-items-center icon type-4">
                                <img src="{{ asset('assets/ffo-admin/img/document-ico.svg') }}">
                            </div>
                            <span>
                                <p>Onboarded MSEs</p>
                                <h5>
                                    <span id="onboarded">{{ $msmeCounts['total']['onboarded'] ?? 0 }}</span>
                                </h5>
                            </span>
                        </div>
                        <div class="card-detail">
                            <ul>
                                @foreach(($msmeCounts['major_activities'] ?? []) as $activity => $counts)
                                    <li>
                                        <span>{{ $activity }}</span>
                                        <span>{{ $counts['onboarded'] ?? 0 }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>
