@foreach($childrens as $children)
    <option value="{{ $children['id'] }}" {{ $children['id'] == @$row['parent_id'] ? 'selected' : '' }}>{{ $prefix }} {{ $children['name'] }}</option>
    @include('modules.childrens', ['childrens' => $children['children'], 'prefix' => $prefix . '-'])
@endforeach