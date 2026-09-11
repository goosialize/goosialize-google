<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$ui = file_get_contents(
    $root
    . '/admin-next/pages/goosialize-google.js'
);

if ($ui === false) {
    throw new RuntimeException(
        'Unable to read Search Console UI.'
    );
}

foreach (
    [
        'goosialize-google-search-kpi-grid',
        "this.product === 'search_console'",
        'repeat(',
        '4,',
        '.goosialize-google-search-pages-table',
        'th:not(:first-child)',
        'text-align: right',
        'vertical-align: middle',
    ]
    as $needle
) {
    if (!str_contains(
        $ui,
        $needle
    )) {
        throw new RuntimeException(
            'Missing Search Console final layout contract: '
            . $needle
        );
    }
}

echo "SEARCH_CONSOLE_FINAL_LAYOUT_CONTRACT=PASS\n";
