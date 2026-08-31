@foreach($childrens as $children)
    <option value="{{ $children['id'] }}" {{ $children['id'] == @$row['module_id'] ? 'selected' : '' }}>{{ $prefix }} {{ $children['name'] }}</option>
    @include('permissions.childrens', ['childrens' => $children['children'], 'prefix' => $prefix . '-'])
@endforeach