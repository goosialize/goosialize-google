#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
    composer.json
    composer.lock
    classes/Analytics/Connection/AnalyticsApiException.php
    classes/Analytics/Connection/GoogleAnalyticsDataClient.php
    classes/Analytics/Connection/GoogleAnalyticsDataClientFactory.php
    classes/Core/Auth/ServiceAccountCredentialProvider.php
    tests/p4/adapter-static-contract.php
)

for file in "${required[@]}"; do
    test -f "$file"
done

grep -Fq \
    '"google/analytics-data": "^0.24"' \
    composer.json

grep -Fq \
    '"name": "google/analytics-data"' \
    composer.lock

grep -Fq \
    "'transport' => 'rest'" \
    classes/Analytics/Connection/GoogleAnalyticsDataClientFactory.php

grep -Fq \
    'analytics.readonly' \
    classes/Core/Auth/ServiceAccountCredentialProvider.php

grep -Fq \
    'ServiceAccountCredentials' \
    classes/Core/Auth/ServiceAccountCredentialProvider.php

grep -Fq \
    'RunReportRequest' \
    classes/Analytics/Connection/GoogleAnalyticsDataClient.php

grep -Fq \
    'GetMetadataRequest' \
    classes/Analytics/Connection/GoogleAnalyticsDataClient.php

grep -Fq \
    '/metadata' \
    classes/Analytics/Connection/GoogleAnalyticsDataClient.php

if grep -Fxq 'composer.lock' .gitignore; then
    echo "P4_COMPOSER_LOCK_POLICY=FAIL"
    exit 1
fi

echo "P4_STATIC_CONTRACT=PASS"
