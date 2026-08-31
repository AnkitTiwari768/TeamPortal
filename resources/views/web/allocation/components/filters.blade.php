{{--
    Component: components/filters.blade.php
    Usage: @include('web.allocation.components.filters', ['financialYears' => [...], 'components' => [...], 'subComponents' => [...]])

    Emits DOM IDs:
        #alloc-filter-fy       — Financial Year
        #alloc-filter-comp     — Component
        #alloc-filter-sub      — Sub Component
        #alloc-filter-reset    — Reset button
--}}
<div class="alloc-filter-bar" id="alloc-filter-bar">
    {{-- Financial Year --}}
    <div class="alloc-form-group" style="min-width:180px;">
        <label class="alloc-form-label" for="alloc-filter-fy">Financial Year</label>
        <select id="alloc-filter-fy" class="alloc-form-select">
            <option value="">All Years</option>
            @foreach($financialYears ?? [] as $fy)
                <option value="{{ $fy }}">{{ $fy }}</option>
            @endforeach
        </select>
    </div>

    {{-- Component --}}
    <div class="alloc-form-group" style="min-width:200px;">
        <label class="alloc-form-label" for="alloc-filter-comp">Component</label>
        <select id="alloc-filter-comp" class="alloc-form-select">
            <option value="">All Components</option>
            @foreach($components ?? [] as $comp)
                <option value="{{ $comp->id }}">{{ $comp->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- Sub Component --}}
    <div class="alloc-form-group" style="min-width:200px;">
        <label class="alloc-form-label" for="alloc-filter-sub">Sub Component</label>
        <select id="alloc-filter-sub" class="alloc-form-select">
            <option value="">All Sub-Components</option>
            @foreach($subComponents ?? [] as $sub)
                <option value="{{ $sub->id }}">{{ $sub->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- Reset --}}
    <div class="alloc-form-group" style="min-width:auto; align-self:flex-end;">
        <button type="button" id="alloc-filter-reset" class="alloc-btn alloc-btn-outline" style="display:none;">
            <i data-feather="x-circle"></i> Clear Filters
        </button>
    </div>
</div>
