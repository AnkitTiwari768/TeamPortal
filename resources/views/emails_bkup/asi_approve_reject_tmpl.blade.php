@php 
    if($status === \App\Enums\ReviewStatus::Approve->value)
        $msg = 'has been approved.';
    if($status === \App\Enums\ReviewStatus::Reject->value)
        $msg = 'has been rejected.';
    if($status === \App\Enums\ReviewStatus::Pending->value)
        $msg = 'has been forwarded.';
    
@endphp

<p>Dear {{ $applicantName }},</p>
<p>ASI Application no. {{$applicationNumber}} for ({{$scriptTitle}}), {{$msg}}. 
    Please visit the portal for further action<br /><br />
    Kind regards, <br />
    <strong>India Cine Hub</strong>
</p>