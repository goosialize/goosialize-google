<?php

declare(strict_types=1);

require __DIR__
    . '/../../classes/Core/Auth/CredentialType.php';

require __DIR__
    . '/../../classes/Core/Auth/CredentialReference.php';

require __DIR__
    . '/../../classes/Core/Config/GoogleConnection.php';

require __DIR__
    . '/../../classes/Analytics/Connection/AnalyticsPropertyId.php';

require __DIR__
    . '/../../classes/Analytics/Connection/AnalyticsProperty.php';

use Goosialize\Google\Analytics\Connection\AnalyticsProperty;
use Goosialize\Google\Analytics\Connection\AnalyticsPropertyId;
use Goosialize\Google\Core\Auth\CredentialReference;
use Goosialize\Google\Core\Auth\CredentialType;
use Goosialize\Google\Core\Config\GoogleConnection;

$credential = new CredentialReference(
    CredentialType::SERVICE_ACCOUNT_FILE,
    '/secure/google/service-account.json'
);

$connection = new GoogleConnection(
    'primary',
    'Primary Google connection',
    $credential
);

$propertyId = new AnalyticsPropertyId('123456789');

$property = new AnalyticsProperty(
    $connection->id(),
    $propertyId,
    'Example GA4 Property'
);

if (
    $property->propertyId()->resourceName()
    !== 'properties/123456789'
) {
    throw new RuntimeException(
        'GA4 resource name contract failed.'
    );
}

if ($property->connectionId() !== 'primary') {
    throw new RuntimeException(
        'GA4 connection ID contract failed.'
    );
}

$invalidValues = [
    '',
    '0',
    '-1',
    'G-ABC123',
    'properties/123',
    '123ABC',
];

foreach ($invalidValues as $value) {
    try {
        new AnalyticsPropertyId($value);

        throw new RuntimeException(
            'Invalid GA4 property ID accepted: '
            . $value
        );
    } catch (InvalidArgumentException) {
        // Expected.
    }
}

echo "P2_DOMAIN_CONTRACT=PASS\n";
