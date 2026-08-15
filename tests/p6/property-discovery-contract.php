<?php

declare(strict_types=1);

require __DIR__
    . '/../../classes/Analytics/Connection/AnalyticsPropertyId.php';

require __DIR__
    . '/../../classes/Analytics/Discovery/AnalyticsPropertySummary.php';

require __DIR__
    . '/../../classes/Analytics/Discovery/AnalyticsPropertyDiscoveryInterface.php';

require __DIR__
    . '/../../classes/Analytics/Testing/FakeAnalyticsPropertyDiscovery.php';

use Goosialize\Google\Analytics\Connection\AnalyticsPropertyId;
use Goosialize\Google\Analytics\Discovery\AnalyticsPropertySummary;
use Goosialize\Google\Analytics\Testing\FakeAnalyticsPropertyDiscovery;

$property = new AnalyticsPropertySummary(
    'accounts/345449079',
    'Goosialize Ltd',
    new AnalyticsPropertyId(
        '477785730'
    ),
    'Agency'
);

$discovery =
    new FakeAnalyticsPropertyDiscovery([
        $property,
    ]);

$results = $discovery->discover();

if (count($results) !== 1) {
    throw new RuntimeException(
        'Property discovery count failed.'
    );
}

$result = $results[0];

if (
    $result->accountResource()
    !== 'accounts/345449079'
) {
    throw new RuntimeException(
        'Account resource contract failed.'
    );
}

if (
    $result->propertyId()->value()
    !== '477785730'
) {
    throw new RuntimeException(
        'Property ID contract failed.'
    );
}

if (
    $result->propertyName()
    !== 'Agency'
) {
    throw new RuntimeException(
        'Property name contract failed.'
    );
}

echo "P6_PROPERTY_DISCOVERY_CONTRACT=PASS\n";
