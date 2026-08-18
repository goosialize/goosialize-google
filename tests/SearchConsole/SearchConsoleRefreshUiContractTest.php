<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$page = file_get_contents(
    $root
    . '/admin-next/pages/goosialize-google.js'
);

$plugin = file_get_contents(
    $root
    . '/goosialize-google.php'
);

if (
    $page === false
    || $plugin === false
) {
    throw new RuntimeException(
        'Unable to read refresh contract files.'
    );
}

$required = [
    "'Refresh'",
    "'Refreshing…'",
    "'Refresh Search Console data'",
    "'Refresh Google Analytics data'",
    "refreshButton.addEventListener(",
    "'click'",
    'this.loadDashboard();',
];

foreach ($required as $needle) {
    if (!str_contains($page, $needle)) {
        throw new RuntimeException(
            'Refresh UI contract missing: '
            . $needle
        );
    }
}

if (
    str_contains(
        $page,
        'grav:plugin-page-action'
    )
) {
    throw new RuntimeException(
        'Unsupported plugin-page action event remains.'
    );
}

if (
    str_contains(
        $plugin,
        "'id' =>\n                        'refresh'"
    )
) {
    throw new RuntimeException(
        'Unsupported Admin2 refresh action remains.'
    );
}

echo "SEARCH_CONSOLE_REFRESH_UI_CONTRACT=PASS\n";
