<div class="btn-group drop-btn">
    <button type="button" class="btn btn-danger dropdown-toggle-split" data-bs-toggle="dropdown" 
        aria-expanded="false"> <img src="{{ asset('assets/ffo-admin/img/'. $icon .'.svg')}}"> {{ $label }}</button>
    <button 
        type="button"
        class="btn btn-danger dropdown-toggle dropdown-toggle-split"
        data-bs-toggle="dropdown" 
        aria-expanded="false">
            <span class="visually-hidden">Toggle Dropdown</span>
    </button>
    
    @isset($links)
        
        @if ($links)

            <ul class="dropdown-menu">
            
                @foreach ($links as $link)

                    <li>
                        <a class="dropdown-item" href="{{ url($link['second_url']) }}">
                            {{ $link['name'] }}
                        </a>
                    </li>

                @endforeach

            </ul>

        @endif

    @endisset

</div>