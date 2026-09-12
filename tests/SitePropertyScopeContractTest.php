<?php

declare(strict_types=1);

$root =
    dirname(__DIR__);

$analytics =
    file_get_contents(
        $root
        . '/classes/Admin/AnalyticsDashboardController.php'
    );

$searchConsole =
    file_get_contents(
        $root
        . '/classes/Admin/SearchConsoleDashboardController.php'
    );

if (
    !is_string($analytics)
    || !is_string($searchConsole)
) {
    fwrite(
        STDERR,
        "Unable to read controller sources.\n"
    );
    exit(1);
}

$analyticsContracts = [
    "plugins.goosialize-google.analytics.default_property",
    "property_scope_forbidden",
    "Requested GA4 property is outside this site scope.",
    "configuredPropertyScope",
    "array_filter",
    "property_id",
];

foreach ($analyticsContracts as $contract) {
    if (!str_contains($analytics, $contract)) {
        fwrite(
            STDERR,
            "Missing Analytics scope contract: "
            . $contract
            . "\n"
        );
        exit(2);
    }
}

$searchConsoleContracts = [
    "plugins.goosialize-google.search_console.default_property",
    "property_scope_forbidden",
    "Requested Search Console property is outside this site scope.",
    "configuredPropertyScope",
    "array_filter",
    "site_url",
];

foreach ($searchConsoleContracts as $contract) {
    if (!str_contains($searchConsole, $contract)) {
        fwrite(
            STDERR,
            "Missing Search Console scope contract: "
            . $contract
            . "\n"
        );
        exit(3);
    }
}

if (
    substr_count(
        $searchConsole,
        "'property_scope_forbidden'"
    ) !== 2
) {
    fwrite(
        STDERR,
        "Search Console must enforce scope on performance and sitemap.\n"
    );
    exit(4);
}

if (
    substr_count(
        $analytics,
        "'property_scope_forbidden'"
    ) !== 1
) {
    fwrite(
        STDERR,
        "Analytics must enforce scope on reporting requests.\n"
    );
    exit(5);
}

echo "SITE_PROPERTY_SCOPE_CONTRACT=PASS\n";
