<?php

// Maps controller class name patterns (Str::is glob syntax, e.g. "App\\Web\\Foo\\*")
// to a human-readable "assignment" label shown on the /route-list admin page.
// Checked top-to-bottom, first match wins. Anything not matched here falls back
// to the feature-folder segment after App\Web\ / App\Domain\ (see
// RouteListService::resolveAssignment()), so most controllers need no entry at all.
// Add a line here only when you want to override that automatic grouping.

return [
    'assignment_map' => [
        'App\\Http\\Controllers\\Auth\\*' => 'Authentication',
        'App\\Web\\RouteList\\*' => 'Developer Tools',
    ],
];
