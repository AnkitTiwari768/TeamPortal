@extends('components.admin.content-layout')

@section('card-content')
    @php
        $current_user = auth()->user();
        $userid = $current_user->id;
    @endphp

    {{-- Conditionally show "Add CA" button only if no mappings exist --}}
    @if($caUsers->isEmpty())
        <div><a href="{{ url('/ca-user/create') }}"><button class="btn btn-primary" style="margin-left:15px;">Add CA</button></a></div>
    @endif

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>{{ __('message.sn') }}</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Username') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Contact No') }}</th>
                        <th class="actions">{{ __('message.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    
                    @foreach($caUsers as $user)
                        <tr>
                            <td>{{ $loop->index + 1 }}</td>
                            <td>
                                {{ $user->first_name }}
                                @if($user->middle_name)
                                    {{ $user->middle_name }}
                                @endif
                                {{ $user->last_name }}
                            </td>
                            <td>{{ $user->username }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->mobile }}</td>
                            <td class="actions">
                                
                                <a href="{{ url('/ca-user/edit/' . $user->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                {{-- Add Delete : <button onclick="deleteCA({{ $user->id }})" class="btn btn-sm btn-danger">Delete</button> --}}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection