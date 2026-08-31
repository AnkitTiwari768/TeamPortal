{{--
    Reusable MSE Stat Card Component
    --------------------------------------------------
    Usage: @include('dashboard.components.mse-stat-card', [...])

    Variables:
      $title       – Card heading text
      $total       – Primary numeric value (shown in <h5>)
      $link        – Anchor href (default '#')
      $items       – Associative array/object used for the sub-list
      $itemKey     – Key to read from each $items entry (null = scalar value)
      $iconType    – CSS suffix class: type-1 … type-4
      $cssClass    – Extra class on the outer col div
      $totalClass  – JS-targeting class on the total <span>
      $listId      – id attr on the <ul>
      $listClass   – CSS class on the <ul>
      $colWidth    – Bootstrap column width (default: col-lg-3)
--}}

<div class="{{ $colWidth ?? 'col-lg-3' }} {{ $cssClass ?? '' }}">
    <a href="{{ $link ?? '#' }}">
        <div class="card w-100">
            <div class="card-body d-flex gap-3">
                <div class="right text-start w-100">

                    <div class="top-header-card">
                        <div class="left align-items-center icon {{ $iconType ?? 'type-1' }}">
                            <img src="{{ asset('assets/ffo-admin/img/document-ico.svg') }}" alt="">
                        </div>

                        <span>
                            <p @isset($titleClass) class="{{ $titleClass }}" @endisset>
                                {{ $title }}
                            </p>

                            <h5>
                                <span class="{{ $totalClass ?? '' }}">
                                    {{ $total ?? 0 }}
                                </span>
                            </h5>
                        </span>
                    </div>

                    <div class="card-detail">
                        <ul
                            @isset($listId)
                                id="{{ $listId }}"
                            @endisset
                            class="{{ $listClass ?? '' }}"
                        >

                            @forelse($items ?? [] as $activity => $counts)

                                @php
                                    $value = 0;

                                    if (is_null($itemKey ?? null)) {

                                        if (is_array($counts) || is_object($counts)) {
                                            $value = '';
                                        } else {
                                            $value = $counts;
                                        }

                                    } else {

                                        if (is_array($counts)) {

                                            $value = $counts[$itemKey] ?? 0;

                                        } elseif (is_object($counts)) {

                                            $value = $counts->{$itemKey} ?? 0;

                                        }

                                    }
                                @endphp

                                <li>
                                    <span>{{ $activity }}</span>
                                    <span>{{ $value }}</span>
                                </li>

                            @empty

                                <li>
                                    <span>No Data</span>
                                    <span>0</span>
                                </li>

                            @endforelse

                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </a>
</div>