<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$service = file_get_contents(
    $root
    . '/classes/SearchConsole/Dashboard/SearchConsoleDashboardService.php'
);

$controller = file_get_contents(
    $root
    . '/classes/Admin/SearchConsoleDashboardController.php'
);

$ui = file_get_contents(
    $root
    . '/admin-next/pages/goosialize-google.js'
);

foreach (
    [
        'public function package(',
        "'overview'",
        "'trend'",
        "'content'",
        "'breakdowns'",
        "'empty' => \$empty",
    ]
    as $needle
) {
    if (!str_contains(
        $service,
        $needle
    )) {
        throw new RuntimeException(
            'Missing SC package service contract: '
            . $needle
        );
    }
}

foreach (
    [
        "\$query['package']",
        '$dashboard->package(',
        "'package_invalid'",
    ]
    as $needle
) {
    if (!str_contains(
        $controller,
        $needle
    )) {
        throw new RuntimeException(
            'Missing SC package controller contract: '
            . $needle
        );
    }
}

foreach (
    [
        "id: 'overview'",
        "progress: 25",
        "id: 'trend'",
        "progress: 50",
        "id: 'content'",
        "progress: 75",
        "id: 'breakdowns'",
        "progress: 95",
        "'No Search Console data available'",
        "packageData.empty === true",
        "packageQuery.set(\n              'package'",
        'appendSearchConsoleHighlights(',
        "'Top query'",
        "'Best page'",
        "'Top device'",
        "'Top country'",
        'goosialize-google-search-highlights',
        'goosialize-google-search-highlight',
    ]
    as $needle
) {
    if (!str_contains(
        $ui,
        $needle
    )) {
        throw new RuntimeException(
            'Missing SC progressive admin UI contract: '
            . $needle
        );
    }
}

echo "SEARCH_CONSOLE_PROGRESSIVE_ADMIN_CONTRACT=PASS\n";
