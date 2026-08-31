@php
// Extract the previous path segment
$previousPath = parse_url(url()->previous(), PHP_URL_PATH);
$previousSegment = collect(explode('/', trim($previousPath, '/')))->last();

// Define the route segment you want to match
$allowedBackRoutes = [
'snp-msme',
'onboarded-msme',
'msme-chossen-me',
'open-msme',
'validated-mse',
'mse-to-be-validated',
];


// Determine the final back route based on previous URL or fallback
$backUrl = in_array($previousSegment, $allowedBackRoutes)
? url()->previous()
: url($module_url);

@endphp

<!-- <button
    type="button"
    class="btn btn-danger"
    autocomplete="off"
    onclick="window.location = '{{ $backUrl }}'">
    <i class="fa fa-angle-double-left"></i>
    Back
</button> -->


<button
    type="button"
    class="btn btn-secondary"
    onclick="window.location.href='{{ url()->previous() }}'">
    <i class="fa fa-angle-double-left"></i>
    Back
</button>