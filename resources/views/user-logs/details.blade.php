@extends('components.admin.layout')
@section('page-content')

@php
    $module_url = 'user-logs';
    $targetUser = $log->user;
    $actorUser = $log->performedByUser;
@endphp

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('User Log Details') }}</h6>
                    @include('components.admin.buttons.back-button')
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="form-group col-md-4">
                            <label><strong>{{ __('Action') }} :</strong></label>
                            <div>{{ \App\Domain\UserLog\UserLogType::tryFrom($log->action)?->label() ?? ucfirst($log->action) }}</div>
                        </div>
                        <div class="form-group col-md-4">
                            <label><strong>{{ __('Date & Time') }} :</strong></label>
                            <div>{{ optional($log->created_at)->format('d-m-Y H:i:s') }}</div>
                        </div>
                        <div class="form-group col-md-4">
                            <label><strong>{{ __('Affected User') }} :</strong></label>
                            <div>
                                {{ $targetUser ? trim($targetUser->first_name.' '.$targetUser->last_name) : '-' }}
                                @if($targetUser?->email)
                                    <br><small class="text-muted">{{ $targetUser->email }}</small>
                                @endif
                            </div>
                        </div>
                        <div class="form-group col-md-4">
                            <label><strong>{{ __('Performed By') }} :</strong></label>
                            <div>
                                {{ $actorUser ? trim($actorUser->first_name.' '.$actorUser->last_name) : '-' }}
                                @if($actorUser?->email)
                                    <br><small class="text-muted">{{ $actorUser->email }}</small>
                                @endif
                            </div>
                        </div>
                        <div class="form-group col-md-12">
                            <label><strong>{{ __('Description') }} :</strong></label>
                            <div>{{ $log->description }}</div>
                        </div>
                    </div>
                </div>
            </div>

            @if(!empty($log->old_values) || !empty($log->new_values))
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('Changed Values') }}</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ __('Field') }}</th>
                                    <th>{{ __('Old Value') }}</th>
                                    <th>{{ __('New Value') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $old = $log->old_values ?? [];
                                    $new = $log->new_values ?? [];
                                    $fields = array_unique(array_merge(array_keys($old), array_keys($new)));

                                    $format = function ($value) {
                                        if (is_null($value)) return '-';
                                        if (is_array($value)) return implode(', ', $value);
                                        if (is_bool($value)) return $value ? 'Yes' : 'No';
                                        return (string) $value;
                                    };
                                @endphp
                                @forelse($fields as $field)
                                    <tr>
                                        <td>{{ ucwords(str_replace('_', ' ', $field)) }}</td>
                                        <td>{{ $format($old[$field] ?? null) }}</td>
                                        <td>{{ $format($new[$field] ?? null) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3">{{ __('No field-level changes recorded.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
