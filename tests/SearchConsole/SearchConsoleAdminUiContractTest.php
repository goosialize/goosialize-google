<?php

declare(strict_types=1);

$root =
    dirname(__DIR__, 2);

$pagePath =
    $root
    . '/admin-next/pages/'
    . 'goosialize-google.js';

if (is_file($pagePath) === false) {
    throw new RuntimeException(
        'Goosialize Google Admin2 page is missing.'
    );
}

$page =
    file_get_contents(
        $pagePath
    );

if ($page === false) {
    throw new RuntimeException(
        'Unable to read Goosialize Google Admin2 page.'
    );
}

$required = [
    "this.product = 'analytics'",
    "'Google Analytics'",
    "'Search Console'",
    "'search_console'",
    '/goosialize-google/properties',
    '/goosialize-google/analytics?',
    '/goosialize-google/search-console/properties',
    '/goosialize-google/search-console/performance?',
    "'site_url'",
    "'property_id'",
    "'Clicks'",
    "'Impressions'",
    "'CTR'",
    "'Average position'",
    "'Top queries'",
    "'Search pages'",
    "'Search devices'",
    "'Search countries'",
    'appendSearchConsoleTable(',
    'formatPercent(',
    'formatDecimal(',
    '[7, 30, 90]',
];

foreach (
    $required
    as $needle
) {
    if (
        str_contains(
            $page,
            $needle
        ) === false
    ) {
        throw new RuntimeException(
            'P9-D Admin2 UI contract missing: '
            . $needle
        );
    }
}

if (
    str_contains(
        $page,
        'api.gpm.read'
    )
) {
    throw new RuntimeException(
        'P9-D UI must not depend on api.gpm.read.'
    );
}

echo "SEARCH_CONSOLE_ADMIN_UI_CONTRACT=PASS\n";
