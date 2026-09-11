<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2)
    . '/classes/Analytics/Tracking/AnalyticsTrackingInjector.php';

use Goosialize\Google\Analytics\Tracking\AnalyticsTrackingInjector;

function same(
    mixed $expected,
    mixed $actual,
    string $message
): void {
    if ($expected !== $actual) {
        throw new RuntimeException(
            $message
        );
    }
}

function yes(
    bool $condition,
    string $message
): void {
    if (!$condition) {
        throw new RuntimeException(
            $message
        );
    }
}

$injector =
    new AnalyticsTrackingInjector();

$html =
    '<html><head><title>Test</title></head>'
    . '<body>OK</body></html>';

same(
    $html,
    $injector->inject(
        $html,
        false,
        'G-TEST123'
    ),
    'Disabled tracking must not mutate output.'
);

same(
    $html,
    $injector->inject(
        $html,
        true,
        'INVALID'
    ),
    'Invalid Measurement ID must not mutate output.'
);

same(
    'G-ABC123',
    $injector->normalizeMeasurementId(
        ' g-abc123 '
    ),
    'Measurement ID normalization failed.'
);

yes(
    $injector->validMeasurementId(
        'G-ABC123'
    ),
    'Valid GA4 Measurement ID rejected.'
);

yes(
    !$injector->validMeasurementId(
        'UA-123456-1'
    ),
    'Legacy UA identifier must not be accepted.'
);

$injected =
    $injector->inject(
        $html,
        true,
        'G-ABC123'
    );

yes(
    str_contains(
        $injected,
        'googletagmanager.com/gtag/js?id=G-ABC123'
    ),
    'gtag.js loader missing.'
);

yes(
    str_contains(
        $injected,
        "gtag('config', 'G-ABC123');"
    ),
    'gtag config call missing.'
);

yes(
    strpos(
        $injected,
        'googletagmanager.com'
    ) < strpos(
        $injected,
        '</head>'
    ),
    'Tracking must be injected before </head>.'
);

same(
    2,
    substr_count(
        $injected,
        AnalyticsTrackingInjector::MARKER
    ),
    'Expected two owned tracking markers.'
);

same(
    $injected,
    $injector->inject(
        $injected,
        true,
        'G-ABC123'
    ),
    'Duplicate injection protection failed.'
);

$existing =
    '<html><head>'
    . '<script async src="https://www.googletagmanager.com/gtag/js?id=G-ABC123"></script>'
    . '</head><body></body></html>';

same(
    $existing,
    $injector->inject(
        $existing,
        true,
        'G-ABC123'
    ),
    'Existing matching gtag loader must not be duplicated.'
);

$headless =
    '<html><body>No head</body></html>';

same(
    $headless,
    $injector->inject(
        $headless,
        true,
        'G-ABC123'
    ),
    'Headless output must not be mutated.'
);

echo "ANALYTICS_TRACKING_INJECTION_CONTRACT=PASS\n";
