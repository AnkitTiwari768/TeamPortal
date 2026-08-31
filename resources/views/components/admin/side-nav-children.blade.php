@php
    $hasChildren = !empty($menu['children']);
    $menuAccessible = false;
    if (!empty($menu['permissions'])) {
        foreach ($menu['permissions'] as $perm) {
            if (acl($perm->slug)) {
                $menuAccessible = true;
                break;
            }
        }
    }
    // Use wildcard true for keeping dropdown parent menus expanded on subpages
    $isExpanded = isActiveMenu($menu, true);
    // Use wildcard false for specific leaf links to prevent highlighting sibling menus
    $isActiveLink = isActiveMenu($menu, false);
@endphp

@if ($hasChildren && childAccessible($menu['children']))
    @php $collapseId = Str::slug($menu['slug'] . '-' . $menu['id']); @endphp
    <a class="nav-link {{ $isExpanded ? '' : 'collapsed' }}" href="#" data-bs-toggle="collapse"
        data-bs-target="#{{ $collapseId }}" aria-expanded="{{ $isExpanded ? 'true' : 'false' }}"
        aria-controls="{{ $collapseId }}">
        <!--<div class="nav-link-icon"><i class="fa fa-list"></i></div>-->
        {{ $menu['name'] }}
        <div class="sb-sidenav-collapse-arrow"><i class="fa fa-angle-down"></i></div>
    </a>
    <div class="collapse {{ $isExpanded ? 'show' : '' }}" id="{{ $collapseId }}">
        <nav class="sb-sidenav-menu-nested nav">
            @foreach ($menu['children'] as $child)
                @include('components.admin.side-nav-children', ['menu' => $child, 'segment' => $segment])
            @endforeach
        </nav>
    </div>
@elseif(!$hasChildren && ($menuAccessible || $menu['url'] === 'dashboard'))
    @if (hasRole('msme') && $menu['url'] === 'event')
        <a class="nav-link {{ $isActiveLink ? 'active' : '' }}" href="{{ url($menu['url']) }}">
            <!--<div class="nav-link-icon"><i class="fa fa-file"></i></div>-->
            Explore Workshops
        </a>
    @elseif ((hasRole('snp') || hasRole('bnp') || hasRole('lsp'))  && $menu['url'] === 'event')
        <a class="nav-link {{ $isActiveLink ? 'active' : '' }}" href="{{ url($menu['url']) }}">
            Workshops
        </a>
    @else
        <a class="nav-link {{ $isActiveLink ? 'active' : '' }}" href="{{ url($menu['url']) }}">
            <!--<div class="nav-link-icon"><i class="fa fa-file"></i></div>-->
            {{ $menu['name'] }}
        </a>
    @endif
@endif


<?php /*
@php 

    $links = array_column($children, 'url');
    $hasActiveLink = in_array($segment, $links);

@endphp

<a class="nav-link {{ $hasActiveLink ? '' : 'collapsed' }}" href="#" data-bs-toggle="collapse" data-bs-target="#{{ $slug }}-collapse" aria-expanded="{{ $hasActiveLink ? 'true' : 'false' }}" aria-controls="collapseLayouts">
    <div class="nav-link-icon"> 
      <i class="fa fa-file-text-o" aria-hidden="true"></i>
    </div>
        {{ $menu }}
    <div class="sb-sidenav-collapse-arrow"><i class="fa fa-angle-down"></i></div>
</a>
<div class="collapse {{ $hasActiveLink ? 'show' : '' }}" id="{{ $slug }}-collapse" aria-labelledby="{{ $slug }}" data-bs-parent="#sidenavAccordion">
    <nav class="sb-sidenav-menu-nested nav">
        
        @foreach($children as $child)
            
            @if($child['slug'] !='modules' && $child['slug'] !='permissions')
            
                @if(empty($child['children']))
                 
                    @if (!empty($child['permissions']) && acl($child['permissions'][0]->slug))
                        
                        <a class="nav-link @if($segment == $child['url']) active @endif" href="{{ url($child['url']) }}">{{ $child['name'] }}</a>

                    @endif 

                @else 
                    
                    @include('components.admin.side-nav-children', [
                        'menu' => $child['name'],
                        'slug' => $child['slug'], 
                        'urls' => $child['url'],
                        'icon' => $child['icon'],
                        'children' => $child['children'],
                        'permissions' => $child['permissions'] 
                    ])
               
                @endif 

            @endif

        @endforeach
    </nav>
</div>


@section('js')

<script>	
	$(document).ready(function() {
		$('.mb-.sb-sidenav-menu nav').each(function() {
			var slength =$(this).find('a').length;
			//console.log(slength);
			if(slength==0){
				$(this).hide(slength);
			}
		}); 
	});
</script>

@endsection

*/
?>
