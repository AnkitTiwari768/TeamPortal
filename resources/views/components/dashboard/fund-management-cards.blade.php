@php
    $allocatedCurrent = $fundMetrics['allocated']['current'] ?? 0.00;
    $allocatedTrendVal = $fundMetrics['allocated']['trend'] ?? 0;
    $allocatedDirection = $fundMetrics['allocated']['direction'] ?? 'neutral';

    $allocatedClass = 'trend-neutral';
    $allocatedArrow = '';
    if ($allocatedDirection === 'up') {
        $allocatedClass = 'trend-up';
        $allocatedArrow = '▲ ';
    } elseif ($allocatedDirection === 'down') {
        $allocatedClass = 'trend-down';
        $allocatedArrow = '▼ ';
    }
    
    $distributedCurrent = $fundMetrics['distributed']['current'] ?? 0.00;
    $distributedTrendVal = $fundMetrics['distributed']['trend'] ?? 0;
    $distributedDirection = $fundMetrics['distributed']['direction'] ?? 'neutral';

    $distributedClass = 'trend-neutral';
    $distributedArrow = '';
    if ($distributedDirection === 'up') {
        $distributedClass = 'trend-up';
        $distributedArrow = '▲ ';
    } elseif ($distributedDirection === 'down') {
        $distributedClass = 'trend-down';
        $distributedArrow = '▼ ';
    }

    $remainingCurrent = $fundMetrics['remaining']['current'] ?? 0.00;
    $utilizationPercent = $fundMetrics['utilization_percent'] ?? 0;
    $prevPeriodString = $fundMetrics['prev_period_string'] ?? 'vs previous period';
@endphp

<style>
    /* Icon badges */
    .fund-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
    .icon-allocated   { background-color: #ecfdf5; color: #10b981; }
    .icon-distributed { background-color: #eff6ff; color: #3b82f6; }
    .icon-remaining   { background-color: #fef3c7; color: #f59e0b; }

    /* Card text sizing */
    .doc-cards .card-allocated p,
    .doc-cards .card-distributed p,
    .doc-cards .card-remaining p { font-size: 12px; margin-bottom: 2px; }

    .doc-cards .card-allocated h3,
    .doc-cards .card-distributed h3,
    .doc-cards .card-remaining h3 { font-size: 1.1rem; }

    /* Trend & utilization badges */
    .trend-indicator {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 2px 7px;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        gap: 3px;
    }
    .trend-up      { background-color: #d1fae5; color: #065f46; }
    .trend-down    { background-color: #fee2e2; color: #991b1b; }
    .trend-neutral { background-color: #f3f4f6; color: #374151; }

    .utilization-badge {
        font-size: 0.75rem;
        background: rgba(245, 158, 11, 0.1);
        color: #d97706;
        padding: 3px 8px;
        border-radius: 8px;
        font-weight: 700;
    }
</style>

<div class="row g-4 mb-4 doc-cards">
    {{-- 1. Total Funds Allocated --}}
    <div class="col-lg-4 col-md-6 col-12">
        <div class="card h-100 card-allocated">
            <div class="card-body p-3 text-start">
                <div class="d-flex align-items-center justify-content-between mb-2 w-100">
                    <div class="fund-icon icon-allocated">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <!-- <span class="trend-indicator {{ $allocatedClass }} fund_allocated_trend" id="allocated_trend">
                        <span>{{ $allocatedArrow }}{{ $allocatedTrendVal }}%</span>
                    </span> -->
                </div>
                <p class="text-muted mb-1 fw-semibold text-uppercase tracking-wider fs-7">Total Funds Allocated</p>
                <h3 class="fw-bold text-dark mb-0">₹<span class="fund_allocated_total" id="allocated_total">{{ number_format($allocatedCurrent, 2, '.', ',') }}</span></h3>
              
            </div>
        </div>
    </div>

    {{-- 2. Total Funds Distributed --}}
    <div class="col-lg-4 col-md-6 col-12">
        <div class="card h-100 card-distributed">
            <div class="card-body p-3 text-start">
                <div class="d-flex align-items-center justify-content-between mb-2 w-100">
                    <div class="fund-icon icon-distributed">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <!-- <span class="trend-indicator {{ $distributedClass }} fund_distributed_trend" id="distributed_trend">
                        <span>{{ $distributedArrow }}{{ $distributedTrendVal }}%</span>
                    </span> -->
                </div>
                <p class="text-muted mb-1 fw-semibold text-uppercase tracking-wider fs-7">Total Funds Distributed</p>
                <h3 class="fw-bold text-dark mb-0">₹<span class="fund_distributed_total" id="distributed_total">{{ number_format($distributedCurrent, 2, '.', ',') }}</span></h3>
              
            </div>
        </div>
    </div>

    {{-- 3. Remaining Funds --}}
    <div class="col-lg-4 col-md-6 col-12">
        <div class="card h-100 card-remaining">
            <div class="card-body p-3 text-start">
                <div class="d-flex align-items-center justify-content-between mb-2 w-100">
                    <div class="fund-icon icon-remaining">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <!-- <span class="utilization-badge fund_utilization_percent" id="utilization_percent">{{ $utilizationPercent }}% Utilized</span> -->
                </div>
                <p class="text-muted mb-1 fw-semibold text-uppercase tracking-wider fs-7">Remaining Funds</p>
                <h3 class="fw-bold text-dark mb-0">₹<span class="fund_remaining_total" id="remaining_total">{{ number_format($remainingCurrent, 2, '.', ',') }}</span></h3>
              
            </div>
        </div>
    </div>
</div>

