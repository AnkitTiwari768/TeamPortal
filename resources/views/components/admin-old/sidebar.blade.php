	<?php  
   $segment = Request::segment(1);   
   $mainmenus=app(\App\Http\Api\V1\MainMenu\MainMenuController::class)->index(); 
  // dd($mainmenus);
   //echo "<pre>"; print_r($mainmenus->original['data']); die();

    ?> 
   <div class="main-sidebar">
    <ul class="nav nav-pills flex-column mb-auto"> 
    @foreach($mainmenus->original['data'] as $child)
      <?php //echo "<pre>"; print_r($child['permissions'][0]->slug); die();  ?>
       <li class="mb-1">
        @if(empty($child['children']))
          @if (isset($child['permissions'][0]) && acl($child['permissions'][0]->slug))
            <a href="{{url($child['url'])}}" class="nav-link text-white  @if(request()->is(trim($child['url'], '/')) || request()->is(trim($child['url'], '/') . '/*')) active @endif"> <i data-feather="{{ $child['icon'] }}"></i>{{ $child['name'] }}</a>
          @endif
        @else
         @include('components.admin.recursive_children', [
         'children' => 
                  $child['children'],
                  'menu'=>$child['name'], 
                  'slug'=>$child['slug'], 
                  'urls'=>$child['url'],
				          'icon'=>$child['icon'],	
                  'permissions'=>$child['permissions']	  
                ])
      @endif
     
    </li>
      
      @endforeach
    </ul>
  </div>  