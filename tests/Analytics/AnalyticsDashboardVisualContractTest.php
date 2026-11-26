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
        'Visitors over time',
        'goosialize-google-links-chart',
        'goosialize-google-links-chart-shell',
        'goosialize-google-chart-toggles',
        "row?.dimensions?.date",
        "row?.metrics?.screenPageViews",
        "row?.metrics?.sessions",
        "row?.metrics?.eventCount",
        "row?.metrics?.engagementRate",
        "row?.metrics?.averageSessionDuration",
        "label: 'Views'",
        "label: 'Sessions'",
        "label: 'Events'",
        "label: 'Engagement'",
        "label: 'Avg. session'",
        '#8b5cf6',
        '#3b82f6',
        '#ec4899',
        '#22c55e',
        '#f59e0b',
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
