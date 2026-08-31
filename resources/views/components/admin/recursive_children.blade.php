@php
    $isOpen = false;
    // A simple internal recursive scope function to check if any deep nested descendant is active
    $checkDescendantActive = function(array $items) use (&$checkDescendantActive) {
        foreach ($items as $item) {
            if (!empty($item['url']) && (request()->is(trim($item['url'], '/')) || request()->is(trim($item['url'], '/') . '/*'))) {
                return true;
            }
            if (!empty($item['children']) && is_array($item['children']) && $checkDescendantActive($item['children'])) {
                return true;
            }
        }
        return false;
    };
    $isOpen = $checkDescendantActive($children);
@endphp

<button class="btn btn-toggle align-items-center {{ $isOpen ? '' : 'collapsed' }} nav-link text-white" data-bs-toggle="collapse" data-bs-target="#{{ $slug }}-collapse" aria-expanded="{{ $isOpen ? 'true' : 'false' }}"> <i data-feather="{{$icon}}"></i>  {{ $menu }}</button>
         <div class="collapse parent {{ $isOpen ? 'show' : '' }}" id="{{ $slug }}-collapse">
            <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">   
             @foreach($children as $child)
			 <?php //echo "<pre>"; echo 'hello' ; print_r($child['permissions'][0]->slug); die();  ?>
				@if($child['slug'] !='modules' && $child['slug'] !='permissions')
				  	@if(empty($child['children']))
				  	    @php
				  	       $hasPerm = false;
				  	       if (!empty($child['permissions'])) {
				  	           foreach ($child['permissions'] as $perm) {
				  	               if (acl($perm->slug)) {
				  	                   $hasPerm = true;
				  	                   break;
				  	               }
				  	           }
				  	       }
				  	    @endphp
				  		@if ($hasPerm)
							<a href="{{url($child['url'])}}" class="nav-link text-white child @if(request()->is(trim($child['url'], '/')) || request()->is(trim($child['url'], '/') . '/*')) active @endif"><i data-feather="minus"></i>{{ $child['name'] }}</a>
						@endif
					@else
						@include('components.admin.recursive_children', ['children' => $child['children'],'menu'=>$child['name'],'slug'=>$child['slug'], 'urls'=>$child['url'],'icon'=>$child['icon'],'permissions'=>$child['permissions'] ])
			   		@endif
		   		@endif
            @endforeach
        </div>
		
<script>	
	$(document).ready(function() {
		$('.mb-1').each(function() {
			var slength =$(this).find('a').length;
			//console.log(slength);
			if(slength==0){
				$(this).hide(slength);
			}
		}); 
	});
</script>