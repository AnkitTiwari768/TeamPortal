{{--
    Component: components/header.blade.php
    Usage: @include('web.allocation.components.header', ['title' => '...', 'breadcrumbs' => [...], 'actions' => '...'])
--}}
<div class="alloc-page-header">
    <div>
        <h1>{{ $icon ?? '📋' }} {{ $title ?? 'Allocation' }}</h1>
        @if(!empty($breadcrumbs))
        <ol class="alloc-breadcrumb">
            @foreach($breadcrumbs as $crumb)
                <li>
                    @if(!empty($crumb['url']))
                        <a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a>
                    @else
                        {{ $crumb['label'] }}
                    @endif
                </li>
            @endforeach
        </ol>
        @endif
    </div>

    <div style="display:flex; gap:.6rem; align-items:center; flex-wrap:wrap;">
        @if(!empty($backUrl))
            <a href="{{ $backUrl }}" class="alloc-btn alloc-btn-outline" style="background:rgba(255,255,255,.15); color:#fff; border-color:rgba(255,255,255,.5);">
                <i data-feather="arrow-left"></i> Back
            </a>
        @endif
        @isset($actions)
            {!! $actions !!}
        @endisset
    </div>
</div>
