<?php

declare(strict_types=1);

$root =
    dirname(__DIR__, 2);

$dashboard =
    file_get_contents(
        $root
        . '/classes/SearchConsole/Dashboard/SearchConsoleDashboardService.php'
    );

$ui =
    file_get_contents(
        $root
        . '/admin-next/pages/goosialize-google.js'
    );

foreach (
    [
        'SearchConsoleDimension::Date',
        "'trend' =>",
        "'date'",
    ]
    as $needle
) {
    if (!str_contains(
        $dashboard,
        $needle
    )) {
        throw new RuntimeException(
            'Missing Search Console trend contract: '
            . $needle
        );
    }
}

foreach (
    [
        'appendSearchConsoleTrendChart(',
        "'Search performance over time'",
        "label: 'Clicks'",
        "label: 'Impressions'",
        "label: 'CTR'",
        "label: 'Position'",
        'appendSearchConsoleInsight(',
        "'Top queries'",
        "'Search devices'",
        "'Search countries'",
        "'Search pages'",
        "'Clicks':",
        "'Impressions':",
        "'Average position':",
    ]
    as $needle
) {
    if (!str_contains(
        $ui,
        $needle
    )) {
        throw new RuntimeException(
            'Missing Search Console admin-first UI contract: '
            . $needle
        );
    }
}

echo "SEARCH_CONSOLE_ADMIN_FIRST_VISUAL_CONTRACT=PASS\n";
