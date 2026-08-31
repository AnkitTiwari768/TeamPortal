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
		   <?php $explode_string = explode(" ", $resp->name); ?>
			<ul><li><label>
			<?php echo end($explode_string); ?>
			<input type="checkbox" name="permissions[]" <?php if (in_array($resp->id, $assignedPermissions)){echo "checked";}else{echo "";} ?> value="{{$resp->id}}" class="form-check-input role-type-permission"></label></li></ul>
			@endforeach

			@if(count($child['children']))
				@include('role-types.childrens',['childrens' => $child['children']])
			@endif
	   </li>
	@endforeach
</ul>
