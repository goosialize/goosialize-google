<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$ui = file_get_contents(
    $root
    . '/admin-next/pages/goosialize-google.js'
);

foreach (
    [
        "'Top cities'",
        "'City data unavailable'",
        "'Google Analytics did not report city-level data for this period.'",
        'validCities',
        "'(not set)'",
        'goosialize-google-city-note',
        'goosialize-google-refresh-rotate',
        'goosialize-google-refresh-pulse',
    ]
    as $needle
) {
    if (!str_contains(
        $ui,
        $needle
    )) {
        throw new RuntimeException(
            'Missing R8D polish contract: '
            . $needle
        );
    }
}

echo "ANALYTICS_R8D_POLISH_CONTRACT=PASS\n";
