<?php

declare(strict_types=1);

$root =
    dirname(__DIR__, 2);

$blueprint =
    file_get_contents(
        $root . '/blueprints.yaml'
    );

$en =
    file_get_contents(
        $root . '/languages/en.yaml'
    );

$el =
    file_get_contents(
        $root . '/languages/el.yaml'
    );

if (
    $blueprint === false
    || $en === false
    || $el === false
) {
    throw new RuntimeException(
        'Unable to read Admin2 configuration contract files.'
    );
}

$groups = [
    'authentication_group',
    'analytics_group',
    'consent_group',
    'tracking_group',
    'search_console_group',
];

foreach ($groups as $group) {
    if (
        !str_contains(
            $blueprint,
            "    {$group}:"
        )
    ) {
        throw new RuntimeException(
            "Missing group: {$group}"
        );
    }
}

if (
    substr_count(
        $blueprint,
        'collapsible: true'
    ) < 6
) {
    throw new RuntimeException(
        'Primary groups plus Advanced Tracking must be collapsible.'
    );
}

if (
    substr_count(
        $blueprint,
        'collapsed: true'
    ) < 6
) {
    throw new RuntimeException(
        'All collapsible groups must default closed.'
    );
}

$sectionHelpKeys = [
    'AUTHENTICATION_HELP',
    'ANALYTICS_HELP',
    'CONSENT_HELP',
    'TRACKING_HELP',
    'TRACKING_ADVANCED_HELP',
    'SEARCH_CONSOLE_HELP',
];

foreach ($sectionHelpKeys as $key) {
    foreach (
        [
            'EN' => $en,
            'EL' => $el,
        ]
        as $locale => $content
    ) {
        if (
            !str_contains(
                $content,
                "  {$key}:"
            )
        ) {
            throw new RuntimeException(
                "Missing {$locale} section help: {$key}"
            );
        }
    }
}

$fieldHelpKeys = [
    'DEFAULT_CONNECTION_HELP',
    'SERVICE_ACCOUNT_FILE_HELP',
    'ANALYTICS_ENABLED_HELP',
    'DEFAULT_PROPERTY_HELP',
    'CONSENT_CONSUMER_HELP',
    'TRACKING_ENABLED_HELP',
    'TRACKING_MEASUREMENT_ID_HELP',
    'TRACKING_SEND_PAGE_VIEW_HELP',
    'TRACKING_GOOGLE_SIGNALS_HELP',
    'TRACKING_AD_PERSONALIZATION_HELP',
    'TRACKING_DEBUG_MODE_HELP',
    'TRACKING_COOKIE_DOMAIN_HELP',
    'TRACKING_COOKIE_PREFIX_HELP',
    'TRACKING_COOKIE_EXPIRES_HELP',
    'TRACKING_LINKER_DOMAINS_HELP',
    'TRACKING_USER_PROPERTIES_HELP',
    'TRACKING_EVENT_PARAMETERS_HELP',
    'SEARCH_CONSOLE_ENABLED_HELP',
    'SEARCH_CONSOLE_DEFAULT_PROPERTY_HELP',
];

foreach ($fieldHelpKeys as $key) {
    foreach (
        [
            'EN' => $en,
            'EL' => $el,
        ]
        as $locale => $content
    ) {
        if (
            !str_contains(
                $content,
                "  {$key}:"
            )
        ) {
            throw new RuntimeException(
                "Missing {$locale} field help: {$key}"
            );
        }
    }
}

echo "GOOGLE_PLUGIN_COLLAPSIBLE_CONFIG_CONTRACT=PASS\n";
