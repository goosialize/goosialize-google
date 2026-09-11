<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$service = file_get_contents(
    $root
    . '/classes/Analytics/Dashboard/AnalyticsDashboardService.php'
);

$ui = file_get_contents(
    $root
    . '/admin-next/pages/goosialize-google.js'
);

foreach (
    [
        "'empty' => \$empty",
        "\$metrics['activeUsers']",
        "\$metrics['sessions']",
        "\$metrics['screenPageViews']",
        "\$metrics['eventCount']",
    ]
    as $needle
) {
    if (!str_contains(
        $service,
        $needle
    )) {
        throw new RuntimeException(
            'Missing empty fast-path backend contract: '
            . $needle
        );
    }
}

foreach (
    [
        'this.loadingPreviousProgress',
        "item.id === 'overview'",
        'packageData.empty === true',
        "'No analytics data available'",
        'duration: 520',
        "'cubic-bezier(0.22, 1, 0.36, 1)'",
        "% complete",
    ]
    as $needle
) {
    if (!str_contains(
        $ui,
        $needle
    )) {
        throw new RuntimeException(
            'Missing loading polish UI contract: '
            . $needle
        );
    }
}

echo "ANALYTICS_LOADING_POLISH_CONTRACT=PASS\n";
