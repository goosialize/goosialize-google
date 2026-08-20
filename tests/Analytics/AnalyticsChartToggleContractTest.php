<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$catalog =
    file_get_contents(
        $root
        . '/classes/Analytics/Reporting/ReportCatalog.php'
    );

$ui =
    file_get_contents(
        $root
        . '/admin-next/pages/goosialize-google.js'
    );

foreach (
    [
        "'screenPageViews'",
        "'sessions'",
        "'eventCount'",
        "'engagementRate'",
        "'averageSessionDuration'",
    ]
    as $needle
) {
    if (
        !str_contains(
            $catalog,
            $needle
        )
    ) {
        throw new RuntimeException(
            'Missing trend metric: '
            . $needle
        );
    }
}

foreach (
    [
        "label: 'Views'",
        "label: 'Sessions'",
        "label: 'Events'",
        "label: 'Engagement'",
        "label: 'Avg. session'",
        'new Set(',
        "addEventListener(\n          'click'",
        "'aria-pressed'",
        "marker.setAttribute(\n                  'fill',\n                  item.color",
        "marker.setAttribute(\n                  'r',\n                  '4'",
        'active.size === 1',
        'goosialize-google-chart-toggle',
    ]
    as $needle
) {
    if (
        !str_contains(
            $ui,
            $needle
        )
    ) {
        throw new RuntimeException(
            'Missing chart toggle contract: '
            . $needle
        );
    }
}

echo "ANALYTICS_CHART_TOGGLE_CONTRACT=PASS\n";
