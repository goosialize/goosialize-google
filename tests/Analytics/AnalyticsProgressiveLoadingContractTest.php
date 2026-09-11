<?php

declare(strict_types=1);

$root =
    dirname(__DIR__, 2);

$service =
    file_get_contents(
        $root
        . '/classes/Analytics/Dashboard/AnalyticsDashboardService.php'
    );

$controller =
    file_get_contents(
        $root
        . '/classes/Admin/AnalyticsDashboardController.php'
    );

$ui =
    file_get_contents(
        $root
        . '/admin-next/pages/goosialize-google.js'
    );

foreach (
    [
        'public function package(',
        "'overview'",
        "'trend'",
        "'top_pages'",
        "'audience'",
        "'geo'",
        "'timing'",
        "'events'",
        "'demographics'",
    ]
    as $needle
) {
    if (!str_contains(
        $service,
        $needle
    )) {
        throw new RuntimeException(
            'Missing package service contract: '
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
            'Missing package controller contract: '
            . $needle
        );
    }
}

foreach (
    [
        'this.loadingProgress',
        'this.loadingStatus',
        "id: 'overview'",
        "id: 'trend'",
        "id: 'top_pages'",
        "id: 'audience'",
        "id: 'geo'",
        "id: 'timing'",
        "id: 'events'",
        "id: 'demographics'",
        "packageQuery.set(\n              'package'",
        "'Dashboard ready'",
        'goosialize-google-loading-panel',
        'goosialize-google-loading-track',
        'goosialize-google-loading-value',
        'aria-valuenow',
        'goosialize-google-timing-grid',
        "city.toLowerCase()",
        "normalized !== '(not set)'",
        "this.appendTable(\n          root,\n          'Events'",
    ]
    as $needle
) {
    if (!str_contains(
        $ui,
        $needle
    )) {
        throw new RuntimeException(
            'Missing progressive UI contract: '
            . $needle
        );
    }
}

echo "ANALYTICS_PROGRESSIVE_LOADING_CONTRACT=PASS\n";
