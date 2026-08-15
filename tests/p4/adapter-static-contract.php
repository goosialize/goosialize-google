<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$files = [
    'classes/Analytics/Connection/AnalyticsApiException.php',
    'classes/Analytics/Connection/GoogleAnalyticsDataClient.php',
    'classes/Analytics/Connection/GoogleAnalyticsDataClientFactory.php',
    'classes/Core/Auth/ServiceAccountCredentialProvider.php',
];

foreach ($files as $relative) {
    $path = $root . '/' . $relative;

    if (!is_file($path)) {
        throw new RuntimeException(
            'Missing P4 adapter file: '
            . $relative
        );
    }
}

$factory = file_get_contents(
    $root
    . '/classes/Analytics/Connection/'
    . 'GoogleAnalyticsDataClientFactory.php'
);

if ($factory === false) {
    throw new RuntimeException(
        'Unable to read GA4 factory.'
    );
}

if (
    !str_contains(
        $factory,
        "'transport' => 'rest'"
    )
) {
    throw new RuntimeException(
        'REST transport contract missing.'
    );
}

$provider = file_get_contents(
    $root
    . '/classes/Core/Auth/'
    . 'ServiceAccountCredentialProvider.php'
);

if ($provider === false) {
    throw new RuntimeException(
        'Unable to read credential provider.'
    );
}

if (
    !str_contains(
        $provider,
        'analytics.readonly'
    )
) {
    throw new RuntimeException(
        'Analytics readonly scope missing.'
    );
}

echo "P4_ADAPTER_STATIC_CONTRACT=PASS\n";
