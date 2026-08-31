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
    /* Premium Fund Management Card Styles */
    .fund-card {
        border: none;
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.08);
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        position: relative;
        overflow: hidden;
    }

    .fund-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 40px 0 rgba(31, 38, 135, 0.15);
    }

    .fund-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 6px;
    }

    /* Curated top-borders and gradients */
    .card-allocated::before { background: linear-gradient(90deg, #10b981, #059669); } /* Emerald */
    .card-distributed::before { background: linear-gradient(90deg, #3b82f6, #2563eb); } /* Blue */
    .card-remaining::before { background: linear-gradient(90deg, #f59e0b, #d97706); } /* Amber */

    .fund-icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .icon-allocated {
        background-color: #ecfdf5;
        color: #10b981;
    }

    .icon-distributed {
        background-color: #eff6ff;
        color: #3b82f6;
    }

    .icon-remaining {
        background-color: #fef3c7;
        color: #f59e0b;
    }

    .trend-indicator {
        font-size: 0.875rem;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .trend-up {
        background-color: #d1fae5;
        color: #065f46;
    }

    .trend-down {
        background-color: #fee2e2;
        color: #991b1b;
    }

    .trend-neutral {
        background-color: #f3f4f6;
        color: #374151;
    }

    .utilization-badge {
        font-size: 0.8rem;
        background: rgba(245, 158, 11, 0.1);
        color: #d97706;
        padding: 4px 10px;
        border-radius: 8px;
        font-weight: 700;
    }
</style>

<div class="row g-4 mb-4">
    {{-- 1. Total Funds Allocated --}}
    @if (acl(config('permissions.allocation-view')))
        <div class="col-lg-4 col-md-6 col-12">
            <div class="card h-100 fund-card card-allocated">
                <div class="card-body p-4 text-start">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="fund-icon icon-allocated">
                            <i class="bi bi-wallet2"></i>
                        </div>
                        <span class="trend-indicator {{ $allocatedClass }} fund_allocated_trend" id="allocated_trend">
                            <span>{{ $allocatedArrow }}{{ $allocatedTrendVal }}%</span>
                        </span>
                    </div>
                    <p class="text-muted mb-1 fw-semibold text-uppercase tracking-wider fs-7">Total Funds Allocated</p>
                    <h3 class="fw-bold text-dark mb-0">₹<span class="fund_allocated_total" id="allocated_total">{{ number_format($allocatedCurrent, 2, '.', ',') }}</span></h3>
                    <small class="text-muted d-block mt-2" id="allocated_prev_period">{{ $prevPeriodString }}</small>
                </div>
            </div>
        </div>
    @endif

    {{-- 2. Total Funds Distributed --}}
    @if (acl(config('permissions.fund-distribution-view')))
        <div class="col-lg-4 col-md-6 col-12">
            <div class="card h-100 fund-card card-distributed">
                <div class="card-body p-4 text-start">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="fund-icon icon-distributed">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                        <span class="trend-indicator {{ $distributedClass }} fund_distributed_trend" id="distributed_trend">
                            <span>{{ $distributedArrow }}{{ $distributedTrendVal }}%</span>
                        </span>
                    </div>
                    <p class="text-muted mb-1 fw-semibold text-uppercase tracking-wider fs-7">Total Funds Distributed</p>
                    <h3 class="fw-bold text-dark mb-0">₹<span class="fund_distributed_total" id="distributed_total">{{ number_format($distributedCurrent, 2, '.', ',') }}</span></h3>
                    <small class="text-muted d-block mt-2" id="distributed_prev_period">{{ $prevPeriodString }}</small>
                </div>
            </div>
        </div>
    @endif

    {{-- 3. Remaining Funds --}}
    @if (acl(config('permissions.allocation-view')) && acl(config('permissions.fund-distribution-view')))
        <div class="col-lg-4 col-md-6 col-12 m-auto m-md-0">
            <div class="card h-100 fund-card card-remaining">
                <div class="card-body p-4 text-start">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="fund-icon icon-remaining">
                            <i class="bi bi-piggy-bank"></i>
                        </div>
                        <span class="utilization-badge fund_utilization_percent" id="utilization_percent">{{ $utilizationPercent }}% Utilized</span>
                    </div>
                    <p class="text-muted mb-1 fw-semibold text-uppercase tracking-wider fs-7">Remaining Funds</p>
                    <h3 class="fw-bold text-dark mb-0">₹<span class="fund_remaining_total" id="remaining_total">{{ number_format($remainingCurrent, 2, '.', ',') }}</span></h3>
                    <small class="text-muted d-block mt-2 fund_remaining_trend" id="remaining_prev_period">{{ $prevPeriodString }}</small>
                </div>
            </div>
        </div>
    @endif
</div>

