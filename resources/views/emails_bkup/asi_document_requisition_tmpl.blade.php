<p>Dear {{ $applicant?->applicant_name }},</p>
<p>To proceed with the review of your application, we require the following additional documents:</p>

@isset($document_requisitions)
<ul>
    @foreach ($document_requisitions as $item)
        <li>{{ ucwords($item['document_name']) }}</li>
    @endforeach
</ul>
@endisset

<p>Kindly send these documents to [mail ID] at your earliest convenience.</p>
<br/>
Kind regards, <br />
<strong>India Cine Hub</strong>