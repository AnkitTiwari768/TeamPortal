@php
use Illuminate\Support\Str;

$segment = Request::segment(1);
$controller = app(\App\Http\Api\V1\MainMenu\MainMenuController::class);
$mainMenus = $controller->index();
$accessableMenus = $controller->getAuthUserAccessableModules();
$menuList = $mainMenus->original['data'] ?? [];

/**
 * Check recursively if a menu or its children are active
 */
function isActiveMenu($menu, $allowWildcard = true) {
    $url = !empty($menu['url']) ? trim($menu['url'], '/') : '';
    if ($url !== '') {
        if ($allowWildcard) {
            if (request()->is($url) || request()->is($url . '/*')) return true;
        } else {
            if (request()->is($url)) return true;
        }
    }
    if (!empty($menu['children'])) {
        foreach ($menu['children'] as $child) {
            // Descendants always check with wildcard to bubble up expansion state
            if (isActiveMenu($child, true)) return true;
        }
    }
    return false;
}

/**
 * Check recursively if any child is accessible
 */
function childAccessible($children) {
    foreach ($children as $child) {
        $hasPerm = false;
        if (!empty($child['permissions'])) {
            foreach ($child['permissions'] as $perm) {
                if (acl($perm->slug)) {
                    $hasPerm = true;
                    break;
                }
            }
        }
        if ($hasPerm) return true;
        if (!empty($child['children']) && childAccessible($child['children'])) return true;
    }
    return false;
}
@endphp

<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">

                @if(!empty($menuList))
                    @foreach($menuList as $menu)
                        @include('components.admin.side-nav-children', ['menu' => $menu, 'segment' => $segment])
                    @endforeach
                @endif

            </div>
        </div>
    </nav>
</div>


<?php /*
@php 

    $segment = Request::segment(1);  
    $controller = app(\App\Http\Api\V1\MainMenu\MainMenuController::class); 
    $mainMenus = $controller->index(); 
    $accessableMenus = $controller->getAuthUserAccessableModules();
    $menuList = $mainMenus->original['data'] ?? []; 

@endphp

<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">

            
                @if ($menuList) 
                
                    @foreach ($menuList as $listItem)

                        @if ((bool) $listItem['have_children'])
 
                            @php 

                                $submenus = array_column($listItem['children'], 'url');
                                
                            @endphp

                            @if(empty($listItem['children']))

                                @if (isset($listItem['permissions'][0]) && acl($listItem['permissions'][0]->slug))
                                
                                    <a class="nav-link @if($segment == $listItem['url']) active @endif" href="{{ url($listItem['url']) }}">
                                        <div class="nav-link-icon"> 
                                            <!-- <img src="{{ asset('assets/ffo-admin/img/'. $listItem['icon']) }}" /> -->
                                             <i class="fa fa-home" aria-hidden="true"></i>
                                        </div>
                                        {{ $listItem['name'] }}
                                    </a>

                                @endif

                            @else 
                            
                                @if (array_intersect($submenus, $accessableMenus))

                                    @include('components.admin.side-nav-children', [
                                        'menu' => $listItem['name'], 
                                        'slug' => $listItem['slug'], 
                                        'urls' => $listItem['url'],
                                        'icon' => $listItem['icon'],	
                                        'children' => $listItem['children'],
                                        'permissions' => $listItem['permissions']	  
                                    ])

                                @endif

                            @endif
                        
                        @else
                            @if (isset($listItem['permissions'][0]) && (acl($listItem['permissions'][0]->slug) || $listItem['url'] === 'dashboard'))   
                                <a class="nav-link @if($segment == $listItem['url']) active @endif" href="{{ url($listItem['url']) }}">
                                    <div class="nav-link-icon"> 
                                        <!-- <img src="{{ asset('assets/ffo-admin/img/'. $listItem['icon']) }}" /> -->
                                         <i class="fa fa-home" aria-hidden="true"></i>
                                    </div>
                                    {{ $listItem['name'] }}
                                </a>       

                                @endif
                        @endif

                    @endforeach

                @endif
                                        
                
            </div>
        </div>
    </nav>
</div>
*/?>