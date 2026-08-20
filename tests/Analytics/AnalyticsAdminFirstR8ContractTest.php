<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$catalog = file_get_contents(
    $root
    . '/classes/Analytics/Reporting/ReportCatalog.php'
);

$dashboard = file_get_contents(
    $root
    . '/classes/Analytics/Dashboard/AnalyticsDashboardService.php'
);

$ui = file_get_contents(
    $root
    . '/admin-next/pages/goosialize-google.js'
);

$map =
    $root
    . '/admin-next/assets/world.svg';

foreach (
    [
        "public function cities()",
        "'cities'",
        "'country'",
        "'city'",
    ]
    as $needle
) {
    if (!str_contains(
        $catalog,
        $needle
    )) {
        throw new RuntimeException(
            'Missing city contract: '
            . $needle
        );
    }
}

if (!str_contains(
    $dashboard,
    "'cities' =>"
)) {
    throw new RuntimeException(
        'Cities dashboard payload missing.'
    );
}

foreach (
    [
        "fill',\n                  item.color",
        "'xMidYMid meet'",
        "'Top pages'",
        "'Events'",
        'googleInfoIcon(',
        'goosialize-google-kpi-info',
        'goosialize-google-refresh-button',
        'goosialize-google-refresh-rotate',
        'goosialize-google-refresh-pulse',
        'appendBusiestDaysBars(',
        'appendBusiestHoursDonut(',
        'appendGeoMap(',
        'world.svg',
        'goosialize-google-map-dot',
        "'Top cities'",
    ]
    as $needle
) {
    if (!str_contains(
        $ui,
        $needle
    )) {
        throw new RuntimeException(
            'Missing R8 UI contract: '
            . $needle
        );
    }
}

if (!is_file($map)) {
    throw new RuntimeException(
        'World SVG missing.'
    );
}

echo "ANALYTICS_ADMIN_FIRST_R8_CONTRACT=PASS\n";
