@extends('components.admin.content-layout')

@section('page-content')
<div class="container-fluid p-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="page-title">{{ $title }}</h1>
        </div>
    </div>

    <div class="card common-card">
        <div class="card-body">
            <form action="{{ route('qms.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="receiver_id" class="form-label">To User</label>
                        <select name="receiver_id" id="receiver_id" class="form-select @error('receiver_id') is-invalid @enderror">
                            <option value="">Select User (Optional)</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->first_name }} {{ $user->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="receiver_role_id" class="form-label">To Role</label>
                        <select name="receiver_role_id" id="receiver_role_id" class="form-select @error('receiver_role_id') is-invalid @enderror">
                            <option value="">Select Role (Optional)</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" name="subject" id="subject" class="form-control @error('subject') is-invalid @enderror" value="{{ old('subject') }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="message" class="form-label">Message</label>
                    <textarea name="message" id="message" rows="5" class="form-control @error('message') is-invalid @enderror" required>{{ old('message') }}</textarea>
                    @error('message')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="attachments" class="form-label">Attachments</label>
                    <input type="file" name="attachments[]" id="attachments" class="form-control" multiple>
                    <small class="text-muted">You can select multiple files.</small>
                </div>

                <div class="text-end">
                    <a href="{{ route('qms.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Send Query</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
