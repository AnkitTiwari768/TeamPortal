<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Route List' }}</title>

    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #f3f4f6;
            color: #111827;
        }

        [hidden] { display: none !important; }

        .rl-page { max-width: 1280px; margin: 0 auto; padding: 24px 20px 60px; }
        .rl-page-title { font-size: 22px; font-weight: 700; margin: 0 0 18px; }

        .rl { --rl-blue:#2563eb; --rl-blue-bg:#eff6ff; --rl-green:#16a34a; --rl-green-bg:#f0fdf4;
              --rl-orange:#d97706; --rl-orange-bg:#fffbeb; --rl-red:#dc2626; --rl-red-bg:#fef2f2;
              --rl-gray:#6b7280; --rl-gray-bg:#f3f4f6; --rl-border:#e5e7eb; --rl-text:#111827;
              --rl-muted:#6b7280; --rl-surface:#ffffff; color: var(--rl-text); position: relative; }

        .rl-cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 20px; }
        .rl-card { display: flex; align-items: center; gap: 12px; background: var(--rl-surface); border: 1px solid var(--rl-border);
                   border-radius: 12px; padding: 16px; box-shadow: 0 1px 2px rgba(16,24,40,.04); cursor: pointer; text-align: left;
                   transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease; }
        .rl-card:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(16,24,40,.08); border-color: #d1d5db; }
        .rl-card.is-active { border-color: var(--rl-blue); box-shadow: 0 0 0 3px var(--rl-blue-bg); }
        .rl-card-icon { display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px;
                        border-radius: 10px; flex-shrink: 0; background: var(--rl-gray-bg); color: var(--rl-gray); }
        .rl-card-icon svg { width: 18px; height: 18px; }
        .rl-card-icon--total { background: var(--rl-blue-bg); color: var(--rl-blue); }
        .rl-card-icon--muted { background: var(--rl-gray-bg); color: var(--rl-gray); }
        .rl-card-body { display: flex; flex-direction: column; min-width: 0; }
        .rl-card-value { font-size: 22px; font-weight: 700; line-height: 1.1; }
        .rl-card-label { font-size: 12px; color: var(--rl-muted); white-space: nowrap; }

        .rl-toolbar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 12px; }
        .rl-toolbar-spacer { flex: 1 1 auto; }
        .rl-search { display: flex; align-items: center; gap: 8px; background: var(--rl-surface); border: 1px solid var(--rl-border);
                     border-radius: 10px; padding: 8px 12px; min-width: 260px; flex: 1 1 260px; max-width: 420px; }
        .rl-search svg { width: 16px; height: 16px; color: var(--rl-muted); flex-shrink: 0; }
        .rl-search input { border: none; outline: none; width: 100%; font-size: 14px; background: transparent; }
        .rl-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 10px; font-size: 13px;
                  font-weight: 500; border: 1px solid transparent; cursor: pointer; transition: background .15s ease, border-color .15s ease; white-space: nowrap; }
        .rl-btn svg { width: 14px; height: 14px; }
        .rl-btn--outline { background: var(--rl-surface); border-color: var(--rl-border); color: var(--rl-text); }
        .rl-btn--outline:hover { background: #f9fafb; }
        .rl-btn--outline.is-active { border-color: var(--rl-blue); color: var(--rl-blue); background: var(--rl-blue-bg); }
        .rl-btn--ghost { background: transparent; color: var(--rl-muted); }
        .rl-btn--ghost:hover { background: var(--rl-gray-bg); color: var(--rl-text); }
        .rl-per-page { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--rl-muted); }
        .rl-per-page select { border: 1px solid var(--rl-border); border-radius: 8px; padding: 6px 8px; font-size: 13px; background: var(--rl-surface); }

        .rl-filters { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; background: #f9fafb; border: 1px solid var(--rl-border);
                      border-radius: 10px; padding: 14px; margin-bottom: 14px; }
        .rl-filter-group { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
        .rl-filter-group label { font-size: 11px; text-transform: uppercase; letter-spacing: .03em; color: var(--rl-muted); font-weight: 600; }
        .rl-filter-group select, .rl-filter-group input { border: 1px solid var(--rl-border); border-radius: 8px; padding: 7px 8px; font-size: 13px; background: var(--rl-surface); font-family: inherit; }

        .rl-summary { display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: var(--rl-muted); margin-bottom: 10px; }

        .rl-table-wrap { border: 1px solid var(--rl-border); border-radius: 12px; overflow: auto; max-height: 640px; background: var(--rl-surface);
                         transition: opacity .12s ease; }
        .rl-table-wrap.is-loading { opacity: .45; pointer-events: none; }
        .rl-table { width: 100%; border-collapse: collapse; font-size: 13px; min-width: 900px; }
        .rl-table thead th { position: sticky; top: 0; background: #f9fafb; text-align: left; padding: 10px 12px; font-weight: 600;
                              border-bottom: 1px solid var(--rl-border); white-space: nowrap; z-index: 1; user-select: none; }
        .rl-table thead th[data-sort] { cursor: pointer; }
        .rl-table thead th[data-sort]:hover { color: var(--rl-blue); }
        .rl-table thead th .rl-sort-arrow { margin-left: 4px; font-size: 10px; opacity: .6; }
        .rl-table tbody td { padding: 10px 12px; border-bottom: 1px solid #f1f2f4; vertical-align: middle; }
        .rl-table tbody tr { transition: background .12s ease; }
        .rl-table tbody tr:hover { background: #f9fafb; }
        .rl-cell-truncate { display: inline-block; max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; vertical-align: middle; }
        .rl-code { font-family: SFMono-Regular, Consolas, "Liberation Mono", Menlo, monospace; font-size: 12.5px; }
        .rl-muted-text { color: var(--rl-muted); }

        .rl-badge { display: inline-flex; align-items: center; justify-content: center; padding: 2px 8px; border-radius: 6px;
                    font-size: 11px; font-weight: 700; letter-spacing: .02em; margin-right: 4px; }
        .rl-badge--GET { background: var(--rl-blue-bg); color: var(--rl-blue); }
        .rl-badge--POST { background: var(--rl-green-bg); color: var(--rl-green); }
        .rl-badge--PUT, .rl-badge--PATCH { background: var(--rl-orange-bg); color: var(--rl-orange); }
        .rl-badge--DELETE { background: var(--rl-red-bg); color: var(--rl-red); }
        .rl-badge--OPTIONS, .rl-badge--HEAD { background: var(--rl-gray-bg); color: var(--rl-gray); }
        .rl-mw-badge { display: inline-flex; align-items: center; padding: 1px 7px; margin: 1px; border-radius: 999px;
                       font-size: 11px; background: var(--rl-gray-bg); color: #374151; }

        .rl-details-btn { display: inline-flex; align-items: center; gap: 4px; padding: 5px 10px; border-radius: 8px; border: 1px solid var(--rl-border);
                           background: var(--rl-surface); font-size: 12px; cursor: pointer; color: var(--rl-text); }
        .rl-details-btn:hover { border-color: var(--rl-blue); color: var(--rl-blue); }
        .rl-details-btn svg { width: 13px; height: 13px; }

        .rl-empty { display: flex; flex-direction: column; align-items: center; gap: 10px; padding: 60px 20px; color: var(--rl-muted); }
        .rl-empty svg { width: 36px; height: 36px; opacity: .5; }
        .rl-empty p { font-size: 15px; font-weight: 500; margin: 0; }

        .rl-pagination { display: flex; align-items: center; justify-content: flex-end; gap: 6px; margin-top: 14px; flex-wrap: wrap; }
        .rl-page-btn { min-width: 34px; height: 34px; padding: 0 8px; border-radius: 8px; border: 1px solid var(--rl-border);
                       background: var(--rl-surface); font-size: 13px; cursor: pointer; color: var(--rl-text); }
        .rl-page-btn:hover:not(:disabled) { border-color: var(--rl-blue); color: var(--rl-blue); }
        .rl-page-btn.is-active { background: var(--rl-blue); border-color: var(--rl-blue); color: #fff; }
        .rl-page-btn:disabled { opacity: .4; cursor: not-allowed; }

        .rl-modal-backdrop { position: fixed; inset: 0; background: rgba(15, 23, 42, .45); display: flex; align-items: center;
                             justify-content: center; z-index: 1080; padding: 16px; }
        .rl-modal { background: var(--rl-surface); border-radius: 14px; width: 100%; max-width: 560px; max-height: 85vh; overflow: auto;
                    box-shadow: 0 20px 60px rgba(0,0,0,.25); animation: rlModalIn .15s ease; }
        @keyframes rlModalIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        .rl-modal-header { display: flex; align-items: center; justify-content: space-between; padding: 16px 18px; border-bottom: 1px solid var(--rl-border); }
        .rl-modal-header h5 { margin: 0; font-size: 16px; font-weight: 600; }
        .rl-modal-close { border: none; background: transparent; cursor: pointer; padding: 4px; color: var(--rl-muted); border-radius: 8px; }
        .rl-modal-close:hover { background: var(--rl-gray-bg); color: var(--rl-text); }
        .rl-modal-close svg { width: 16px; height: 16px; }
        .rl-modal-body { padding: 18px; }
        .rl-detail-row { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; padding: 10px 0; border-bottom: 1px solid #f1f2f4; }
        .rl-detail-row:last-child { border-bottom: none; }
        .rl-detail-label { font-size: 11px; text-transform: uppercase; letter-spacing: .03em; color: var(--rl-muted); font-weight: 600; flex: 0 0 120px; padding-top: 3px; }
        .rl-detail-value { font-size: 13px; word-break: break-word; text-align: right; flex: 1 1 auto; }
        .rl-copy-btn { border: 1px solid var(--rl-border); background: var(--rl-surface); border-radius: 7px; padding: 3px 8px;
                       font-size: 11px; cursor: pointer; margin-left: 8px; color: var(--rl-muted); }
        .rl-copy-btn:hover { border-color: var(--rl-blue); color: var(--rl-blue); }

        .rl-toast { position: fixed; bottom: 24px; left: 50%; transform: translate(-50%, 12px); background: #111827; color: #fff;
                    padding: 8px 16px; border-radius: 999px; font-size: 13px; opacity: 0; pointer-events: none; transition: opacity .15s ease, transform .15s ease; z-index: 1090; }
        .rl-toast.is-visible { opacity: 1; transform: translate(-50%, 0); }

        /* AJAX loader */
        .rl-loader { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
                     background: rgba(255, 255, 255, .5); border-radius: 12px; z-index: 5; pointer-events: none; }
        .rl-spinner { width: 34px; height: 34px; border: 3px solid var(--rl-blue-bg); border-top-color: var(--rl-blue);
                      border-radius: 50%; animation: rlSpin .7s linear infinite; }
        @keyframes rlSpin { to { transform: rotate(360deg); } }
        .rl-load-error { display: flex; align-items: center; justify-content: space-between; gap: 12px; background: var(--rl-red-bg);
                         color: var(--rl-red); border: 1px solid #fecaca; border-radius: 10px; padding: 10px 14px; font-size: 13px; margin-bottom: 14px; }
        .rl-load-error button { border: 1px solid #fecaca; background: #fff; color: var(--rl-red); border-radius: 8px; padding: 5px 10px; font-size: 12px; cursor: pointer; }

        @media (max-width: 900px) { .rl-cards { grid-template-columns: repeat(2, 1fr); } .rl-filters { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 560px) { .rl-cards { grid-template-columns: 1fr 1fr; } .rl-filters { grid-template-columns: 1fr; }
            .rl-search { min-width: 0; } .rl-detail-row { flex-direction: column; align-items: flex-start; }
            .rl-detail-value { text-align: left; } .rl-detail-label { flex-basis: auto; } }
    </style>
</head>

<body>

    <div class="rl-page">
        <h1 class="rl-page-title">{{ $title ?? 'Route List' }}</h1>

        <div class="rl">

            <div class="rl-loader" id="rl-loader" hidden>
                <div class="rl-spinner"></div>
            </div>

            <div class="rl-load-error" id="rl-load-error" hidden>
                <span>Something went wrong while loading routes.</span>
                <button type="button" id="rl-load-error-retry">Retry</button>
            </div>

            {{-- Dashboard cards --}}
            <div class="rl-cards" id="rl-cards">
                <button type="button" class="rl-card" data-card-filter="">
                    <span class="rl-card-icon rl-card-icon--total"><i data-feather="list"></i></span>
                    <span class="rl-card-body">
                        <span class="rl-card-value" id="rl-count-total">0</span>
                        <span class="rl-card-label">Total Routes</span>
                    </span>
                </button>
                <button type="button" class="rl-card" data-card-filter="GET">
                    <span class="rl-card-icon rl-badge--GET"><i data-feather="download"></i></span>
                    <span class="rl-card-body">
                        <span class="rl-card-value" id="rl-count-get">0</span>
                        <span class="rl-card-label">GET Routes</span>
                    </span>
                </button>
                <button type="button" class="rl-card" data-card-filter="POST">
                    <span class="rl-card-icon rl-badge--POST"><i data-feather="upload"></i></span>
                    <span class="rl-card-body">
                        <span class="rl-card-value" id="rl-count-post">0</span>
                        <span class="rl-card-label">POST Routes</span>
                    </span>
                </button>
                <button type="button" class="rl-card" data-card-filter="PUT,PATCH">
                    <span class="rl-card-icon rl-badge--PUT"><i data-feather="edit-2"></i></span>
                    <span class="rl-card-body">
                        <span class="rl-card-value" id="rl-count-putpatch">0</span>
                        <span class="rl-card-label">PUT/PATCH Routes</span>
                    </span>
                </button>
                <button type="button" class="rl-card" data-card-filter="DELETE">
                    <span class="rl-card-icon rl-badge--DELETE"><i data-feather="trash-2"></i></span>
                    <span class="rl-card-body">
                        <span class="rl-card-value" id="rl-count-delete">0</span>
                        <span class="rl-card-label">DELETE Routes</span>
                    </span>
                </button>
                <button type="button" class="rl-card" data-card-toggle="named">
                    <span class="rl-card-icon rl-card-icon--muted"><i data-feather="tag"></i></span>
                    <span class="rl-card-body">
                        <span class="rl-card-value" id="rl-count-named">0</span>
                        <span class="rl-card-label">Named Routes</span>
                    </span>
                </button>
                <button type="button" class="rl-card" data-card-toggle="middleware">
                    <span class="rl-card-icon rl-card-icon--muted"><i data-feather="shield"></i></span>
                    <span class="rl-card-body">
                        <span class="rl-card-value" id="rl-count-middleware">0</span>
                        <span class="rl-card-label">Routes With Middleware</span>
                    </span>
                </button>
                <button type="button" class="rl-card" data-card-toggle="controller">
                    <span class="rl-card-icon rl-card-icon--muted"><i data-feather="box"></i></span>
                    <span class="rl-card-body">
                        <span class="rl-card-value" id="rl-count-controller">0</span>
                        <span class="rl-card-label">Controller Routes</span>
                    </span>
                </button>
            </div>

            {{-- Toolbar --}}
            <div class="rl-toolbar">
                <div class="rl-search">
                    <i data-feather="search"></i>
                    <input type="text" id="rl-search-input" placeholder="Search routes..." aria-label="Search routes" autocomplete="off">
                </div>
                <button type="button" class="rl-btn rl-btn--outline" id="rl-filters-toggle" aria-expanded="false" aria-controls="rl-filters-panel">
                    <i data-feather="filter"></i> Filters
                </button>
                <button type="button" class="rl-btn rl-btn--ghost" id="rl-clear-filters">
                    <i data-feather="x-circle"></i> Clear Filters
                </button>
                <div class="rl-toolbar-spacer"></div>
                <label class="rl-per-page">
                    Rows per page
                    <select id="rl-per-page">
                        <option value="10">10</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </label>
            </div>

            {{-- Filters panel --}}
            <div class="rl-filters" id="rl-filters-panel" hidden>
                <div class="rl-filter-group">
                    <label for="rl-filter-method">Method</label>
                    <select id="rl-filter-method">
                        <option value="">All Methods</option>
                        <option value="GET">GET</option>
                        <option value="POST">POST</option>
                        <option value="PUT">PUT</option>
                        <option value="PATCH">PATCH</option>
                        <option value="DELETE">DELETE</option>
                        <option value="OPTIONS">OPTIONS</option>
                        <option value="HEAD">HEAD</option>
                    </select>
                </div>
                <div class="rl-filter-group">
                    <label for="rl-filter-uri">URI</label>
                    <input type="text" id="rl-filter-uri" placeholder="Filter by URI..." autocomplete="off">
                </div>
                <div class="rl-filter-group">
                    <label for="rl-filter-assignment">Assignment</label>
                    <select id="rl-filter-assignment">
                        <option value="">All Assignments</option>
                        @foreach ($filterOptions['assignments'] as $assignment)
                            <option value="{{ $assignment }}">{{ $assignment }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="rl-filter-group">
                    <label for="rl-filter-middleware">Middleware</label>
                    <select id="rl-filter-middleware">
                        <option value="">All Middleware</option>
                        @foreach ($filterOptions['middlewares'] as $middlewareOption)
                            <option value="{{ $middlewareOption }}">{{ $middlewareOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="rl-filter-group">
                    <label for="rl-filter-named">Named / Unnamed</label>
                    <select id="rl-filter-named">
                        <option value="">All Routes</option>
                        <option value="named">Named Only</option>
                        <option value="unnamed">Unnamed Only</option>
                    </select>
                </div>
                <div class="rl-filter-group">
                    <label for="rl-filter-type">Controller / Closure</label>
                    <select id="rl-filter-type">
                        <option value="">All Types</option>
                        <option value="controller">Controller Only</option>
                        <option value="closure">Closure Only</option>
                    </select>
                </div>
            </div>

            {{-- Result summary --}}
            <div class="rl-summary">
                <span><strong id="rl-summary-showing">0</strong> of <strong id="rl-summary-total">0</strong> routes</span>
                <span class="rl-summary-range" id="rl-summary-range"></span>
            </div>

            {{-- Table --}}
            <div class="rl-table-wrap" id="rl-table-wrap">
                <table class="rl-table" id="rl-table">
                    <thead>
                        <tr>
                            <th data-sort="method">Method</th>
                            <th data-sort="uri">URI</th>
                            <th data-sort="name">Name</th>
                            <th data-sort="action">Action</th>
                            <th>Middleware</th>
                            <th data-sort="assignment">Assignment</th>
                            <th class="rl-th-actions">Details</th>
                        </tr>
                    </thead>
                    <tbody id="rl-table-body"></tbody>
                </table>
            </div>

            {{-- Empty state --}}
            <div class="rl-empty" id="rl-empty" hidden>
                <i data-feather="inbox"></i>
                <p>No routes found</p>
                <button type="button" class="rl-btn rl-btn--outline" id="rl-empty-clear">Clear Filters</button>
            </div>

            {{-- Pagination --}}
            <div class="rl-pagination" id="rl-pagination"></div>

            {{-- Detail modal --}}
            <div class="rl-modal-backdrop" id="rl-modal-backdrop" hidden>
                <div class="rl-modal" role="dialog" aria-modal="true" aria-labelledby="rl-modal-title">
                    <div class="rl-modal-header">
                        <h5 id="rl-modal-title">Route Details</h5>
                        <button type="button" class="rl-modal-close" id="rl-modal-close" aria-label="Close">
                            <i data-feather="x"></i>
                        </button>
                    </div>
                    <div class="rl-modal-body" id="rl-modal-body"></div>
                </div>
            </div>

            {{-- Copy toast --}}
            <div class="rl-toast" id="rl-toast">Copied</div>
        </div>
    </div>

    <script src="{{ asset('assets/js/feather.min.js') }}"></script>
    <script>
    (function () {
        'use strict';

        var DATA_URL = @json(route('route-list.data'));

        var state = {
            search: '',
            method: '',
            uri: '',
            assignment: '',
            middleware: '',
            named: '',
            type: '',
            cardToggle: '',
            page: 1,
            perPage: 25,
            sortKey: 'uri',
            sortDir: 'asc',
        };

        var els = {
            cards: document.getElementById('rl-cards'),
            searchInput: document.getElementById('rl-search-input'),
            filtersToggle: document.getElementById('rl-filters-toggle'),
            filtersPanel: document.getElementById('rl-filters-panel'),
            clearFilters: document.getElementById('rl-clear-filters'),
            emptyClear: document.getElementById('rl-empty-clear'),
            perPage: document.getElementById('rl-per-page'),
            filterMethod: document.getElementById('rl-filter-method'),
            filterUri: document.getElementById('rl-filter-uri'),
            filterAssignment: document.getElementById('rl-filter-assignment'),
            filterMiddleware: document.getElementById('rl-filter-middleware'),
            filterNamed: document.getElementById('rl-filter-named'),
            filterType: document.getElementById('rl-filter-type'),
            summaryShowing: document.getElementById('rl-summary-showing'),
            summaryTotal: document.getElementById('rl-summary-total'),
            summaryRange: document.getElementById('rl-summary-range'),
            tableWrap: document.getElementById('rl-table-wrap'),
            tableBody: document.getElementById('rl-table-body'),
            table: document.getElementById('rl-table'),
            empty: document.getElementById('rl-empty'),
            pagination: document.getElementById('rl-pagination'),
            modalBackdrop: document.getElementById('rl-modal-backdrop'),
            modalBody: document.getElementById('rl-modal-body'),
            modalClose: document.getElementById('rl-modal-close'),
            toast: document.getElementById('rl-toast'),
            loader: document.getElementById('rl-loader'),
            loadError: document.getElementById('rl-load-error'),
            loadErrorRetry: document.getElementById('rl-load-error-retry'),
        };

        function esc(value) {
            var div = document.createElement('div');
            div.textContent = value === null || value === undefined ? '' : String(value);
            return div.innerHTML;
        }

        function methodBadges(methods) {
            return methods.map(function (m) {
                return '<span class="rl-badge rl-badge--' + esc(m) + '">' + esc(m) + '</span>';
            }).join('');
        }

        function middlewareBadges(list, limit) {
            if (!list.length) {
                return '<span class="rl-muted-text">&mdash;</span>';
            }
            var shown = limit ? list.slice(0, limit) : list;
            var html = shown.map(function (m) {
                return '<span class="rl-mw-badge">' + esc(m) + '</span>';
            }).join('');
            if (limit && list.length > limit) {
                html += '<span class="rl-mw-badge">+' + (list.length - limit) + '</span>';
            }
            return html;
        }

        function renderCounts(counts) {
            document.getElementById('rl-count-total').textContent = counts.total;
            document.getElementById('rl-count-get').textContent = counts.GET;
            document.getElementById('rl-count-post').textContent = counts.POST;
            document.getElementById('rl-count-putpatch').textContent = counts.PUTPATCH;
            document.getElementById('rl-count-delete').textContent = counts.DELETE;
            document.getElementById('rl-count-named').textContent = counts.named;
            document.getElementById('rl-count-middleware').textContent = counts.middleware;
            document.getElementById('rl-count-controller').textContent = counts.controller;
        }

        function renderCardActiveState() {
            Array.prototype.forEach.call(els.cards.querySelectorAll('.rl-card'), function (card) {
                var cardFilter = card.getAttribute('data-card-filter');
                var cardToggleAttr = card.getAttribute('data-card-toggle');
                var isActive = (cardFilter !== null && cardFilter !== '' && cardFilter === state.cardToggle) ||
                               (cardToggleAttr && cardToggleAttr === state.cardToggle);
                card.classList.toggle('is-active', Boolean(isActive));
            });
        }

        function renderSortIndicators() {
            Array.prototype.forEach.call(els.table.querySelectorAll('th[data-sort]'), function (th) {
                var key = th.getAttribute('data-sort');
                var arrow = th.querySelector('.rl-sort-arrow');
                if (arrow) arrow.remove();
                if (key === state.sortKey) {
                    var span = document.createElement('span');
                    span.className = 'rl-sort-arrow';
                    span.textContent = state.sortDir === 'asc' ? '▲' : '▼';
                    th.appendChild(span);
                }
            });
        }

        function renderTable(pageItems) {
            if (pageItems.length === 0) {
                els.tableWrap.hidden = true;
                els.empty.hidden = false;
                els.tableBody.innerHTML = '';
                return;
            }
            els.tableWrap.hidden = false;
            els.empty.hidden = true;

            var rows = pageItems.map(function (r, idx) {
                return '<tr data-index="' + idx + '">' +
                    '<td>' + methodBadges(r.methods) + '</td>' +
                    '<td><span class="rl-cell-truncate rl-code" title="' + esc(r.uri) + '">' + esc(r.uri) + '</span></td>' +
                    '<td><span class="rl-cell-truncate" title="' + esc(r.name || '') + '">' + (r.name ? esc(r.name) : '<span class="rl-muted-text">&mdash;</span>') + '</span></td>' +
                    '<td><span class="rl-cell-truncate rl-code" title="' + esc(r.action) + '">' + esc(r.action) + '</span></td>' +
                    '<td>' + middlewareBadges(r.middleware, 2) + '</td>' +
                    '<td>' + esc(r.assignment) + '</td>' +
                    '<td><button type="button" class="rl-details-btn" data-detail-index="' + idx + '"><i data-feather="eye"></i> View</button></td>' +
                    '</tr>';
            }).join('');

            els.tableBody.innerHTML = rows;
            if (window.feather) {
                window.feather.replace();
            }
        }

        function renderPagination(meta) {
            var totalPages = meta.totalPages;
            var page = meta.page;
            var perPage = meta.perPage;
            var totalItems = meta.filteredTotal;

            if (totalItems === 0) {
                els.pagination.innerHTML = '';
                els.summaryRange.textContent = '';
                return;
            }

            var start = (page - 1) * perPage + 1;
            var end = Math.min(page * perPage, totalItems);
            els.summaryRange.textContent = 'Showing ' + start + '–' + end + ' of ' + totalItems + ' routes';

            var buttons = [];
            buttons.push('<button type="button" class="rl-page-btn" data-page="' + (page - 1) + '" ' + (page === 1 ? 'disabled' : '') + '>Prev</button>');

            var windowStart = Math.max(1, page - 2);
            var windowEnd = Math.min(totalPages, windowStart + 4);
            windowStart = Math.max(1, windowEnd - 4);

            if (windowStart > 1) {
                buttons.push('<button type="button" class="rl-page-btn" data-page="1">1</button>');
                if (windowStart > 2) buttons.push('<span class="rl-muted-text">&hellip;</span>');
            }
            for (var p = windowStart; p <= windowEnd; p++) {
                buttons.push('<button type="button" class="rl-page-btn' + (p === page ? ' is-active' : '') + '" data-page="' + p + '">' + p + '</button>');
            }
            if (windowEnd < totalPages) {
                if (windowEnd < totalPages - 1) buttons.push('<span class="rl-muted-text">&hellip;</span>');
                buttons.push('<button type="button" class="rl-page-btn" data-page="' + totalPages + '">' + totalPages + '</button>');
            }

            buttons.push('<button type="button" class="rl-page-btn" data-page="' + (page + 1) + '" ' + (page === totalPages ? 'disabled' : '') + '>Next</button>');
            els.pagination.innerHTML = buttons.join('');
        }

        var currentPageItems = [];
        var pendingController = null;
        var requestSeq = 0;

        function buildQuery() {
            var params = new URLSearchParams();
            if (state.search) params.set('search', state.search);
            if (state.method) params.set('method', state.method);
            if (state.uri) params.set('uri', state.uri);
            if (state.assignment) params.set('assignment', state.assignment);
            if (state.middleware) params.set('middleware', state.middleware);
            if (state.named) params.set('named', state.named);
            if (state.type) params.set('type', state.type);
            if (state.cardToggle) params.set('cardToggle', state.cardToggle);
            params.set('sortKey', state.sortKey);
            params.set('sortDir', state.sortDir);
            params.set('page', String(state.page));
            params.set('perPage', String(state.perPage));
            return params.toString();
        }

        function setLoading(isLoading) {
            els.loader.hidden = !isLoading;
            els.tableWrap.classList.toggle('is-loading', isLoading);
        }

        function fetchAndRender() {
            if (pendingController) {
                pendingController.abort();
            }
            var controller = (typeof AbortController !== 'undefined') ? new AbortController() : null;
            pendingController = controller;
            var seq = ++requestSeq;

            setLoading(true);
            els.loadError.hidden = true;

            fetch(DATA_URL + '?' + buildQuery(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                signal: controller ? controller.signal : undefined,
            })
                .then(function (res) {
                    if (!res.ok) {
                        throw new Error('Request failed with status ' + res.status);
                    }
                    return res.json();
                })
                .then(function (payload) {
                    if (seq !== requestSeq) return;
                    pendingController = null;
                    setLoading(false);
                    applyResponse(payload.data);
                })
                .catch(function (err) {
                    if (err && err.name === 'AbortError') return;
                    if (seq !== requestSeq) return;
                    pendingController = null;
                    setLoading(false);
                    els.loadError.hidden = false;
                });
        }

        function applyResponse(payload) {
            currentPageItems = payload.data;
            state.page = payload.meta.page;

            renderCounts(payload.meta.counts);
            renderCardActiveState();
            renderSortIndicators();

            els.summaryShowing.textContent = currentPageItems.length;
            els.summaryTotal.textContent = payload.meta.total;

            renderTable(currentPageItems);
            renderPagination(payload.meta);
        }

        // -- Modal -------------------------------------------------------------

        function copyToClipboard(text, triggerEl) {
            function done() { showToast(triggerEl); }

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(done).catch(function () {
                    fallbackCopy(text, done);
                });
            } else {
                fallbackCopy(text, done);
            }
        }

        function fallbackCopy(text, done) {
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            try { document.execCommand('copy'); } catch (e) { /* no-op */ }
            document.body.removeChild(ta);
            done();
        }

        var toastTimer = null;
        function showToast() {
            els.toast.classList.add('is-visible');
            if (toastTimer) clearTimeout(toastTimer);
            toastTimer = setTimeout(function () { els.toast.classList.remove('is-visible'); }, 1400);
        }

        function detailRow(label, value, copyValue) {
            var copyBtn = copyValue ? '<button type="button" class="rl-copy-btn" data-copy="' + esc(copyValue) + '">Copy</button>' : '';
            return '<div class="rl-detail-row">' +
                '<div class="rl-detail-label">' + esc(label) + '</div>' +
                '<div class="rl-detail-value">' + esc(value) + copyBtn + '</div>' +
                '</div>';
        }

        function openModal(route) {
            document.getElementById('rl-modal-title').textContent = route.uri;
            els.modalBody.innerHTML =
                detailRow('URI', route.uri, route.uri) +
                detailRow('HTTP Methods', route.methods.join(', ')) +
                detailRow('Route Name', route.name || '—', route.name || '') +
                detailRow('Action', route.action, route.action) +
                detailRow('Controller', route.controller || '—') +
                detailRow('Controller Method', route.controllerMethod || '—') +
                detailRow('Middleware', route.middleware.length ? route.middleware.join(', ') : '—') +
                detailRow('Domain', route.domain || '—') +
                detailRow('Assignment', route.assignment);

            els.modalBackdrop.hidden = false;
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            els.modalBackdrop.hidden = true;
            document.body.style.overflow = '';
        }

        // -- Event wiring --------------------------------------------------------

        var searchDebounce = null;
        els.searchInput.addEventListener('input', function () {
            clearTimeout(searchDebounce);
            var value = els.searchInput.value;
            searchDebounce = setTimeout(function () {
                state.search = value;
                state.page = 1;
                fetchAndRender();
            }, 250);
        });

        var uriDebounce = null;
        els.filterUri.addEventListener('input', function () {
            clearTimeout(uriDebounce);
            var value = els.filterUri.value;
            uriDebounce = setTimeout(function () {
                state.uri = value;
                state.page = 1;
                fetchAndRender();
            }, 250);
        });

        els.filtersToggle.addEventListener('click', function () {
            var expanded = els.filtersToggle.getAttribute('aria-expanded') === 'true';
            els.filtersToggle.setAttribute('aria-expanded', String(!expanded));
            els.filtersToggle.classList.toggle('is-active', !expanded);
            els.filtersPanel.hidden = expanded;
        });

        function bindFilterSelect(el, key) {
            el.addEventListener('change', function () {
                state[key] = el.value;
                state.page = 1;
                fetchAndRender();
            });
        }
        bindFilterSelect(els.filterMethod, 'method');
        bindFilterSelect(els.filterAssignment, 'assignment');
        bindFilterSelect(els.filterMiddleware, 'middleware');
        bindFilterSelect(els.filterNamed, 'named');
        bindFilterSelect(els.filterType, 'type');

        els.perPage.addEventListener('change', function () {
            state.perPage = parseInt(els.perPage.value, 10) || 25;
            state.page = 1;
            fetchAndRender();
        });

        function resetFilters() {
            state.search = '';
            state.method = '';
            state.uri = '';
            state.assignment = '';
            state.middleware = '';
            state.named = '';
            state.type = '';
            state.cardToggle = '';
            state.page = 1;

            els.searchInput.value = '';
            els.filterMethod.value = '';
            els.filterUri.value = '';
            els.filterAssignment.value = '';
            els.filterMiddleware.value = '';
            els.filterNamed.value = '';
            els.filterType.value = '';

            fetchAndRender();
        }
        els.clearFilters.addEventListener('click', resetFilters);
        els.emptyClear.addEventListener('click', resetFilters);
        els.loadErrorRetry.addEventListener('click', fetchAndRender);

        els.cards.addEventListener('click', function (e) {
            var card = e.target.closest('.rl-card');
            if (!card) return;
            var filterValue = card.getAttribute('data-card-filter');
            var toggleValue = card.getAttribute('data-card-toggle');
            var key = filterValue !== null ? filterValue : toggleValue;

            state.cardToggle = state.cardToggle === key ? '' : (key || '');
            state.page = 1;
            fetchAndRender();
        });

        Array.prototype.forEach.call(els.table.querySelectorAll('th[data-sort]'), function (th) {
            th.addEventListener('click', function () {
                var key = th.getAttribute('data-sort');
                if (state.sortKey === key) {
                    state.sortDir = state.sortDir === 'asc' ? 'desc' : 'asc';
                } else {
                    state.sortKey = key;
                    state.sortDir = 'asc';
                }
                fetchAndRender();
            });
        });

        els.pagination.addEventListener('click', function (e) {
            var btn = e.target.closest('.rl-page-btn');
            if (!btn || btn.disabled) return;
            var page = parseInt(btn.getAttribute('data-page'), 10);
            if (!page) return;
            state.page = page;
            fetchAndRender();
        });

        els.tableBody.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-detail-index]');
            if (!btn) return;
            var index = parseInt(btn.getAttribute('data-detail-index'), 10);
            var route = currentPageItems[index];
            if (route) openModal(route);
        });

        els.modalClose.addEventListener('click', closeModal);
        els.modalBackdrop.addEventListener('click', function (e) {
            if (e.target === els.modalBackdrop) closeModal();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !els.modalBackdrop.hidden) closeModal();
        });
        els.modalBody.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-copy]');
            if (!btn) return;
            copyToClipboard(btn.getAttribute('data-copy'), btn);
        });

        // -- Init ------------------------------------------------------------

        if (window.feather) {
            window.feather.replace();
        }
        fetchAndRender();
    })();
    </script>
</body>

</html>
