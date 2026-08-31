 <ul>
 
	@foreach($childrens as $child)
		@php 
			if ($child['have_children'] === 0 && $child['have_permissions'] === 0) {
				continue;
			}
		@endphp
	   <li>
			{{ $child['name'] }}
	
		   
		   @foreach($child['permissions'] as $resp)
		   <?php $explode_string= explode(" ",$resp->name);?>
			<ul><li><label>	
			<?php echo end($explode_string); ?>
			<input type="checkbox" name="permissions[]" id="permissions" <?php if (in_array($resp->id, $rolePermissions)){echo "checked";}else{echo "";} ?> value="{{$resp->id}}" class="form-check-input role-permission-checkbox"></label></li></ul>
			@endforeach
			
			@if(count($child['children']))
				@include('roles.childrens',['childrens' => $child['children']])
			@endif
	   </li>
	@endforeach
</ul>


<script>
$(document).ready(function() {
	$('.branch').each(function() {
		var slength =$(this).find('ul li').length;
		//console.log(slength);
		if(slength==0){
			$(this).hide(slength);
		}
	}); 
}); 
</script>



