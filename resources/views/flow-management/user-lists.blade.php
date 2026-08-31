<select name="flow[{{$randomId}}][user_id]" id ="user_id{{$randomId}}" class="form-control form-select" <?php /* onchange="getPermissionsByRoleIdAndUserId({{$randomId}})" */?>>
    <option value="">Select User</option>
    @foreach($users as $user)
        <option value="{{ $user->id }}" @if($userId==$user->id) selected @else @endif>{{ $user->full_name }}</option>
    @endforeach
</select>
<div class="text-danger form-error" id="User_{{$randomId}}_error"></div>