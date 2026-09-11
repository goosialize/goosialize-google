<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$ui = file_get_contents(
    $root
    . '/admin-next/pages/goosialize-google.js'
);

foreach (
    [
        '.goosialize-google-period-row',
        'align-items: center',
        '.goosialize-google-period-refresh',
        'width: 2.35rem',
        'height: 2.35rem',
        'min-height: 2.35rem',
        'align-self: center',
    ]
    as $needle
) {
    if (!str_contains(
        $ui,
        $needle
    )) {
        throw new RuntimeException(
            'Missing refresh visual alignment contract: '
            . $needle
        );
    }
}

echo "ANALYTICS_REFRESH_VISUAL_ALIGNMENT_CONTRACT=PASS\n";
