<nav aria-label="breadcrumb">
    
    <ol class="breadcrumb">
        
        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ __('app.home') }}</a></li>

        @isset($breadcrumbs)

            @if ($breadcrumbs)

                @foreach ($breadcrumbs as [$url, $title])

                    <li class="breadcrumb-item">
                        <a href="{{ $url }}">{{ $title }}</a>
                    </li>
                    
                @endforeach

            @endif

        @endisset
        
    </ol>

</nav>