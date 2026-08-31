@if(empty($permissions))
	<div class="alert alert-info mb-0">{{ __('message.no_permission_available') }}</div>
@else
	<ul id="tree1">
		@foreach($permissions as $child)

			@php
				if ($child['have_children'] == 0 && $child['have_permissions'] == 0) {
					continue;
				}
			@endphp

			<li>
				{{ $child['name'] }}

				@foreach($child['permissions'] as $resp)
				<ul>
					<li><label>{{ $resp->name }} <input type="checkbox" name="permissions[]" <?php if (in_array($resp->id, $assignedPermissions)){echo "checked";}else{echo "";} ?> value="{{$resp->id}}" class="form-check-input role-type-permission"></label></li>
				</ul>
				@endforeach

				@if(count($child['children']))
					@include('role-types.childrens',['childrens' => $child['children']])
				@endif
			</li>
		@endforeach
	</ul>
@endif
