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

foreach (
    [
        "'operating_systems'",
        "'busy_days'",
        "'busy_hours'",
        "'gender'",
        "'ages'",
        "'operatingSystem'",
        "'dayOfWeekName'",
        "'hour'",
        "'userGender'",
        "'userAgeBracket'",
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
            'Missing admin report: '
            . $needle
        );
    }
}

foreach (
    [
        "'operating_systems' =>",
        "'busy_days' =>",
        "'busy_hours' =>",
        "'gender' =>",
        "'ages' =>",
        'optionalRows(',
        'catch (Throwable)',
    ]
    as $needle
) {
    if (
        !str_contains(
            $dashboard,
            $needle
        )
    ) {
        throw new RuntimeException(
            'Missing optional dashboard contract: '
            . $needle
        );
    }
}

foreach (
    [
        'Visitors over time',
        'appendAdminInsight',
        'Operating systems',
        'Busiest days',
        'Busiest hours',
        'Audience demographics',
        'repeat(',
        '8,',
        'Top pages',
        "'Events'",
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
            'Missing admin-first UI contract: '
            . $needle
        );
    }
}

if (
    str_contains(
        $ui,
        "appendDonutBreakdown("
    )
    || str_contains(
        $ui,
        "appendCountryMap("
    )
) {
    throw new RuntimeException(
        'Legacy donut/map UI still active.'
    );
}

echo "ANALYTICS_ADMIN_FIRST_CONTRACT=PASS\n";
