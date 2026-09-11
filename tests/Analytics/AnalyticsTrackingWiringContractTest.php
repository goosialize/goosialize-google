<?php

declare(strict_types=1);

$root =
    dirname(__DIR__, 2);

$entry =
    file_get_contents(
        $root
        . '/goosialize-google.php'
    );

$config =
    file_get_contents(
        $root
        . '/goosialize-google.yaml'
    );

$blueprint =
    file_get_contents(
        $root
        . '/blueprints.yaml'
    );

if (
    $entry === false
    || $config === false
    || $blueprint === false
) {
    throw new RuntimeException(
        'Unable to read tracking contract inputs.'
    );
}

foreach (
    [
        'AnalyticsTrackingInjector',
        "'onOutputGenerated'",
        'public function onOutputGenerated(): void',
        '$this->isAdmin()',
        'plugins.goosialize-google.tracking.enabled',
        'plugins.goosialize-google.tracking.measurement_id',
        '$this->grav->output',
    ]
    as $needle
) {
    if (!str_contains(
        $entry,
        $needle
    )) {
        throw new RuntimeException(
            'Missing tracking wiring contract: '
            . $needle
        );
    }
}

foreach (
    [
        'tracking:',
        'enabled: false',
        "measurement_id: ''",
    ]
    as $needle
) {
    if (!str_contains(
        $config,
        $needle
    )) {
        throw new RuntimeException(
            'Missing tracking config contract: '
            . $needle
        );
    }
}

foreach (
    [
        'tracking.enabled:',
        'tracking.measurement_id:',
        'G-XXXXXXXXXX',
        "'^(G-[A-Za-z0-9]+)?$'",
    ]
    as $needle
) {
    if (!str_contains(
        $blueprint,
        $needle
    )) {
        throw new RuntimeException(
            'Missing tracking blueprint contract: '
            . $needle
        );
    }
}

echo "ANALYTICS_TRACKING_WIRING_CONTRACT=PASS\n";
