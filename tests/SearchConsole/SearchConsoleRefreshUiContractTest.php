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
    "'goosialize-google-refresh-button'",
    "'goosialize-google-refresh-icon'",
    "'aria-label'",
    "'Refresh data'",
    "'Refreshing data'",
    "'Refreshing data…'",
    "refreshButton.addEventListener(",
    "'click'",
    'this.loadDashboard();',
    "refreshButton.classList.add(",
    "'is-loading'",
    'goosialize-google-refresh-rotate',
    'goosialize-google-refresh-pulse',
];

foreach ($required as $needle) {
    if (!str_contains(
        $page,
        $needle
    )) {
        throw new RuntimeException(
            'Refresh UI contract missing: '
            . $needle
        );
    }
}

$forbidden = [
    "element(\n          'button',\n          this.loading\n            ? 'Refreshing…'\n            : 'Refresh'",
    'grav:plugin-page-action',
];

foreach ($forbidden as $needle) {
    if (
        str_contains(
            $page,
            $needle
        )
    ) {
        throw new RuntimeException(
            'Legacy refresh UI remains: '
            . $needle
        );
    }
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
