<p>Dear {{ $applicant?->applicant_name }},</p>
<p>With reference to your Application No. <strong>{{$appDetails->application_number}}</strong> for  <strong>{{$appDetails->script_title}}</strong>, this is to inform you that below documents are required by <strong>{{$zoneUserDetails->zonename}}</strong> to proceed further. </p>

@isset($document_requisitions)
<ul>
    @foreach ($document_requisitions as $item)
        <li>{{ ucwords($item['document_name']) }} (In favour of - {{ ucwords($item['document_favour']) }})</li>
    @endforeach
</ul>
@endisset

<!--<p>Kindly send these documents to [mail ID] at your earliest convenience.</p>-->
<p>You are requested to furnish above mentioned documents.</p>
<br/>
Kind regards, <br />
<strong>India Cine Hub</strong>