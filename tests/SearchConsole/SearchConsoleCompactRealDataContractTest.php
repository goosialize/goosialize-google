<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$ui = file_get_contents(
    $root
    . '/admin-next/pages/goosialize-google.js'
);

foreach (
    [
        'function formatSearchDevice(',
        'function formatSearchCountry(',
        'function formatSearchPage(',
        "grc: 'Greece'",
        'decodeURIComponent(raw)',
        'new URL(decoded)',
        'goosialize-google-search-chart',
        'height: 210px',
        'goosialize-google-search-insight-layout',
        'goosialize-google-search-insight-card',
        "'is-query'",
        "'is-compact'",
        "title === 'Top queries'",
        '? 5',
        'formatSearchDevice(',
        'formatSearchCountry(',
        'formatSearchPage(',
        'page_display',
        'goosialize-google-search-pages-table',
        'table-layout: fixed',
        'text-overflow: ellipsis',
    ]
    as $needle
) {
    if (!str_contains(
        $ui,
        $needle
    )) {
        throw new RuntimeException(
            'Missing Search Console compact real-data contract: '
            . $needle
        );
    }
}

echo "SEARCH_CONSOLE_COMPACT_REAL_DATA_CONTRACT=PASS\n";
