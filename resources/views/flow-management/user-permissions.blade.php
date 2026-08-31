@foreach($permissions as $permission)
	<input type="checkbox" name="flow[{{$randomId}}][permission_id][]" id ="permission_id{{$randomId}}" class="checkbox" value="{{$permission->id}}" {{ in_array($permission->id, $permissionId ?? []) ? 'checked' : '' }}>&nbsp;{{$permission->name}}<br>
@endforeach
<div class="text-danger form-error" id="Permission_{{$randomId}}_error"></div>