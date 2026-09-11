<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$ui = file_get_contents(
    $root
    . '/admin-next/pages/goosialize-google.js'
);

if ($ui === false) {
    throw new RuntimeException(
        'Unable to read Search Console UI.'
    );
}

foreach (
    [
        'appendSearchConsoleDimensionVisual(',
        "'Search devices'",
        "'Search countries'",
        'forceDonut = false',
        'goosialize-google-search-donut-layout',
        'goosialize-google-search-donut',
        'goosialize-google-search-donut-legend',
        'goosialize-google-search-country-single',
        'data.length === 1',
        '100% share',
        'stroke-dasharray',
        'impressions',
    ]
    as $needle
) {
    if (!str_contains(
        $ui,
        $needle
    )) {
        throw new RuntimeException(
            'Missing Search Console donut visual contract: '
            . $needle
        );
    }
}

$devicesOld = <<<'JS'
        this.appendSearchConsoleInsight(
          searchInsights,
          'Search devices',
JS;

$countriesOld = <<<'JS'
        this.appendSearchConsoleInsight(
          searchInsights,
          'Search countries',
JS;

if (
    str_contains($ui, $devicesOld)
    || str_contains($ui, $countriesOld)
) {
    throw new RuntimeException(
        'Legacy Search Console device/country bar renderer remains.'
    );
}

echo "SEARCH_CONSOLE_DONUT_VISUAL_CONTRACT=PASS\n";
