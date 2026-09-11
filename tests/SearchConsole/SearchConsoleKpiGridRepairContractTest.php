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

$required = [
    "this.product === 'search_console'",
    'goosialize-google-kpi-grid goosialize-google-search-kpi-grid',
    '.goosialize-google-kpi-grid.goosialize-google-search-kpi-grid',
    '.goosialize-google-map-stage',
    '.goosialize-google-geo-map',
];

foreach ($required as $needle) {
    if (!str_contains(
        $ui,
        $needle
    )) {
        throw new RuntimeException(
            'Missing Search Console KPI grid repair contract: '
            . $needle
        );
    }
}

$broken = <<<'CSS'
      .goosialize-google-map-stage
      @media (min-width: 1280px) {
CSS;

if (str_contains(
    $ui,
    $broken
)) {
    throw new RuntimeException(
        'Malformed geo-map/media CSS remains.'
    );
}

$searchRule = <<<'CSS'
      @media (min-width: 1280px) {
        .goosialize-google-kpi-grid.goosialize-google-search-kpi-grid {
          grid-template-columns:
            repeat(
              4,
              minmax(0, 1fr)
            );
        }
      }
CSS;

if (!str_contains(
    $ui,
    $searchRule
)) {
    throw new RuntimeException(
        'Search Console four-column KPI override missing.'
    );
}

echo "SEARCH_CONSOLE_KPI_GRID_REPAIR_CONTRACT=PASS\n";
