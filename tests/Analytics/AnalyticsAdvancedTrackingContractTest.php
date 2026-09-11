<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2)
    . '/classes/Analytics/Tracking/AnalyticsTrackingInjector.php';

use Goosialize\Google\Analytics\Tracking\AnalyticsTrackingInjector;

function expect(
    bool $condition,
    string $label
): void {
    if (!$condition) {
        throw new RuntimeException(
            $label
        );
    }
}

$injector =
    new AnalyticsTrackingInjector();

$html =
    '<html><head></head><body></body></html>';

$result =
    $injector->inject(
        $html,
        true,
        'G-ADVANCED1',
        [
            'send_page_view' =>
                false,

            'allow_google_signals' =>
                true,

            'allow_ad_personalization_signals' =>
                true,

            'debug_mode' =>
                true,

            'cookie_domain' =>
                'example.com',

            'cookie_prefix' =>
                'goose',

            'cookie_expires' =>
                3600,

            'linker_domains' => [
                'Example.com',
                'shop.example.com',
                'invalid domain',
            ],

            'user_properties' => [
                'customer_type' =>
                    'registered',

                '<bad>' =>
                    'drop',
            ],

            'event_parameters' => [
                'site_section' =>
                    'main',

                'send_to' =>
                    'forbidden',

                'google_test' =>
                    'forbidden',
            ],
        ]
    );

foreach (
    [
        '"send_page_view":false',
        '"allow_google_signals":true',
        '"allow_ad_personalization_signals":true',
        '"debug_mode":true',
        '"cookie_domain":"example.com"',
        '"cookie_prefix":"goose"',
        '"cookie_expires":3600',
        '"domains":["example.com","shop.example.com"]',
        '"customer_type":"registered"',
        '"site_section":"main"',
        "analytics_storage: 'denied'",
        "ad_storage: 'denied'",
        "ad_user_data: 'denied'",
        "ad_personalization: 'denied'",
    ]
    as $needle
) {
    expect(
        str_contains(
            $result,
            $needle
        ),
        'Missing: ' . $needle
    );
}

foreach (
    [
        'invalid domain',
        '<bad>',
        '"send_to"',
        '"google_test"',
    ]
    as $needle
) {
    expect(
        !str_contains(
            $result,
            $needle
        ),
        'Unsafe value leaked: '
        . $needle
    );
}

expect(
    str_contains(
        $result,
        "baseConfig.event_parameters"
    ),
    'Default event parameters wiring missing.'
);

expect(
    str_contains(
        $result,
        "'user_properties'"
    ),
    'User properties wiring missing.'
);

expect(
    str_contains(
        $result,
        "state?.marketing === true"
    ),
    'Marketing consent mapping missing.'
);

echo "ANALYTICS_ADVANCED_TRACKING_CONTRACT=PASS\n";
