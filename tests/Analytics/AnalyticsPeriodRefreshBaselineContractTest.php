<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$ui = file_get_contents(
    $root
    . '/admin-next/pages/goosialize-google.js'
);

foreach (
    [
        "'goosialize-google-period-row mt-1'",
        "'inline-flex h-10 items-center rounded-md border border-border bg-background p-1 shadow-sm'",
        'goosialize-google-period-refresh',
        'width: 2.35rem',
        'height: 2.35rem',
        'align-items: center',
        'align-self: center',
    ]
    as $needle
) {
    if (!str_contains(
        $ui,
        $needle
    )) {
        throw new RuntimeException(
            'Missing period/refresh baseline contract: '
            . $needle
        );
    }
}

if (str_contains(
    $ui,
    "'mt-1 inline-flex h-10 items-center rounded-md border border-border bg-background p-1 shadow-sm'"
)) {
    throw new RuntimeException(
        'Period control still owns the vertical margin.'
    );
}

echo "ANALYTICS_PERIOD_REFRESH_BASELINE_CONTRACT=PASS\n";
