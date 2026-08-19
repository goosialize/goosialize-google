<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$catalog =
    file_get_contents(
        $root
        . '/classes/Analytics/Reporting/ReportCatalog.php'
    );

$dashboard =
    file_get_contents(
        $root
        . '/classes/Analytics/Dashboard/AnalyticsDashboardService.php'
    );

$ui =
    file_get_contents(
        $root
        . '/admin-next/pages/goosialize-google.js'
    );

foreach (
    [
        "'trend'",
        "'date'",
        "'activeUsers'",
        "'sessions'",
        "'screenPageViews'",
    ]
    as $needle
) {
    if (!str_contains($catalog, $needle)) {
        throw new RuntimeException(
            'Trend report contract missing: '
            . $needle
        );
    }
}

if (
    !str_contains(
        $dashboard,
        "'trend' =>"
    )
) {
    throw new RuntimeException(
        'Trend dashboard payload missing.'
    );
}

foreach (
    [
        'appendAnalyticsTrendChart',
        'Analytics trend',
        'xl:col-span-2',
        '#a855f7',
        '#3b82f6',
        '#06b6d4',
    ]
    as $needle
) {
    if (!str_contains($ui, $needle)) {
        throw new RuntimeException(
            'Visual dashboard contract missing: '
            . $needle
        );
    }
}

echo "ANALYTICS_DASHBOARD_VISUAL_CONTRACT=PASS\n";
