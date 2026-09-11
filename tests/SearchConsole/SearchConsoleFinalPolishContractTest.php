<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$ui = file_get_contents(
    $root
    . '/admin-next/pages/goosialize-google.js'
);

foreach (
    [
        'function formatSearchPage(',
        'url.pathname',
        'decodeURIComponent(',
        'pathname =',
        'goosialize-google-search-highlight-value',
        'valueNode.title =',
        'page_full:',
        "title === 'Search pages'",
        "key === 'page_display'",
        'td.title =',
        'row.page_full',
    ]
    as $needle
) {
    if (!str_contains(
        $ui,
        $needle
    )) {
        throw new RuntimeException(
            'Missing Search Console final polish contract: '
            . $needle
        );
    }
}

echo "SEARCH_CONSOLE_FINAL_POLISH_CONTRACT=PASS\n";
