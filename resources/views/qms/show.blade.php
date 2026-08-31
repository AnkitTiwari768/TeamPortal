@extends('components.admin.content-layout')

@section('page-content')
<style>
    .chat-container {
        max-width: 900px;
        margin: 0 auto;
    }
    .message-bubble {
        max-width: 80%;
        padding: 1rem;
        border-radius: 1.5rem;
        position: relative;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .message-receiver {
        background-color: #f0f2f5;
        border-bottom-left-radius: 0.25rem;
        margin-right: auto;
    }
    .message-sender {
        background-color: #007bff;
        color: white;
        border-bottom-right-radius: 0.25rem;
        margin-left: auto;
    }
    .message-info {
        font-size: 0.75rem;
        margin-bottom: 0.25rem;
    }
    .message-sender .text-muted {
        color: rgba(255,255,255,0.7) !important;
    }
    .attachment-card {
        background: rgba(0,0,0,0.05);
        border-radius: 0.5rem;
        padding: 0.5rem 0.75rem;
        display: inline-block;
        margin-top: 0.5rem;
        text-decoration: none;
        color: inherit;
        font-size: 0.85rem;
        transition: background 0.2s;
    }
    .message-sender .attachment-card {
        background: rgba(255,255,255,0.2);
        color: white;
    }
    .attachment-card:hover {
        background: rgba(0,0,0,0.1);
        color: inherit;
    }
    .message-sender .attachment-card:hover {
        background: rgba(255,255,255,0.3);
        color: white;
    }
    .reply-card {
        border-radius: 1rem;
        border: none;
        box-shadow: 0 -5px 15px rgba(0,0,0,0.05);
    }
    .badge-status {
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-weight: 600;
    }
</style>

<div class="container-fluid p-4">
    <div class="card chat-card">
    <div class="card-body">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-start mb-2 border-bottom pb-3">
            <div>
                <h6 class="d-flex gap-2 align-items-center"> 
                    <div>
                @php $claimSlug = $query->claim_slug ?? null; @endphp
                @if($claimSlug === 'claim-for-catalogue-creation')
                <a href="{{ url('claims') }}" class="btn">
                    <i class="fa fa-arrow-left me-1"></i> Back
                </a>
                @elseif ($claimSlug === 'claim-for-accounts-management')
                <a href="{{ url('accounts-claims') }}" class="btn">
                    <i class="fa fa-arrow-left me-1"></i> Back
                </a>
                @elseif ($claimSlug === 'claim-for-transportation-and-logistic')
                <a href="{{ url('logistics-transportation-claim') }}" class="btn">
                    <i class="fa fa-arrow-left me-1"></i> Back
                </a>
                @elseif ($claimSlug === 'claim-for-demand-generation')
                <a href="{{ url('demand-generation-claim') }}" class="btn">
                    <i class="fa fa-arrow-left me-1"></i> Back
                </a>
                @elseif ($claimSlug === 'claim-for-packaging')
                <a href="{{ url('packaging-support-claim') }}" class="btn">
                    <i class="fa fa-arrow-left me-1"></i> Back
                </a>
                @else
                <a href="{{ route('qms.index') }}" class="btn back-btn">
                    <i class="fa fa-arrow-left me-1"></i>
                </a> 
                @endif
                </div>  

                <span>Conversation between <strong>{{ $query->sender?->first_name ?? 'System' }}</strong> and <strong>{{ $query->receiver?->first_name ?? $query->receiverRole->name ?? 'Role' }}</strong></span>
                 
                </h6>

                <div class="d-flex gap-3 align-items-center" style="margin-left: 42px;">
                    
                <div class="d-flex gap-3 align-items-center">
                    <p class="text-muted mb-0">{{ $query->subject }}</p>
                    <span class="badge {{ $query->status === 'open' ? 'bg-success text-white' : 'bg-secondary text-white' }}">   {{ ucfirst($query->status) }}</span>
                </div>
                
                <div class="mb-2">
                    @if($query->batch_number)
                        <span class="badge bg-info text-dark me-2">Batch: {{ $query->batch_number }}</span>
                    @endif
                    @if($query->claim_number)
                        <span class="badge bg-primary me-2">Claim: {{ $query->claim_number }}</span>
                    @endif
                   
                </div>
                
                </div>
                
            </div>
            <div class="text-end">
                

                @if($query->status !== 'closed' && $query->canUserClose(Auth::user()))
                    <form action="{{ route('qms.close', $query->id) }}" method="POST" class="d-inline ms-2">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-2 px-3" onclick="return confirm('Close this conversation?')">
                            Close Query
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
                <i class="fa fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Conversation Thread -->
        <div class="conversation-thread py-2">
            @foreach($query->messages as $message)
                @php $isMe = ($message->sender_id === AuthId()); @endphp
                <div class="d-flex flex-column {{ $isMe ? 'align-items-end' : 'align-items-start' }} mb-2">
                    <div class="message-info text-muted px-2">
                        <strong>{{ $isMe ? 'You' : $message->sender->first_name . ' ' . $message->sender->last_name }}</strong> 
                        &bull; {{ $message->created_at->format('d M, h:i A') }}
                    </div>
                    <div class="message-bubble {{ $isMe ? 'message-sender' : 'message-receiver' }}">
                        <div class="message-content" style="white-space: pre-wrap;">{{ $message->message }}</div>
                        
                        @if($message->attachments->count() > 0)
                            <div class="mt-2 pt-2 {{ $isMe ? 'border-top border-white-50' : 'border-top border-dark-10' }}">
                                @foreach($message->attachments as $attachment)
                                    <a href="{{ url('files/' . $attachment->file_upload_id . '/download') }}" class="attachment-card" target="_blank">
                                        <i class="fa fa-file-text-o me-1"></i> {{ Str::limit($attachment->fileUpload->file_name, 30) }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Reply Section -->
        <div class="mt-2">
            @if($query->status !== 'closed')
                @if($query->canUserReply(Auth::user()))
                    <div class="card reply-card border-0">
                        <div class="card-body p-3">
                            <h6 class="fw-medium mb-3">Send a Reply</h6>


                            <form action="{{ route('qms.reply', $query->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row align-items-start">

                                      <div class="col-lg-12">
                                <div class="mb-0">
                                    <textarea name="message" id="message" rows="2" class="form-control rounded-3 border-light shadow-sm @error('message') is-invalid @enderror" placeholder="Write your message here..." required></textarea>
                                    @error('message')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
</div>
                                
                                </div>
                                

                                <div class="row mt-3">
                                    <div class="col-lg-6">
                                        <div class="input-group input-group">
                                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa fa-paperclip"></i></span>
                                            <input type="file" name="attachments[]" id="attachments" class="form-control border-start-0" multiple>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 text-end">
                                        <button type="submit" class="btn h-100 btn-primary rounded-2">
                                            <i class="fa fa-paper-plane me-1"></i> Send Reply
                                        </button>
                                </div>
                                   
                                </div>
                            </form>


                        </div>
                    </div>
                @else
                    <div class="alert alert-light border text-center rounded-2 py-4">
                        <div class="display-6 text-muted mb-2"><i class="fa fa-hourglass-half"></i></div>
                        <h6 class="fw-bold">Awaiting Response</h6>
                        <p class="text-muted small mb-0">
                            @if($query->receiver_role_id && Auth::user()->role_id === $query->receiver_role_id)
                                A colleague from your role has already replied or it's not your turn.
                            @else
                                It's currently the other party's turn to respond to this query.
                            @endif
                        </p>
                    </div>
                @endif
            @else
                <div class="alert bg-light border-0 text-center rounded-2 py-3 shadow-sm">
                    <div class="display-6 text-success mb-2"><i class="fa fa-check-circle"></i></div>
                    <h6 class="fw-bold">This Query is Closed</h6>
                    <p class="text-muted mb-0 small">No further messages can be sent in this conversation.</p>
                </div>
            @endif
        </div>
        
    </div>
    </div>
</div>
@endsection
