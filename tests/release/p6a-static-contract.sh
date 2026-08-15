#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
    classes/Analytics/Discovery/AnalyticsPropertySummary.php
    classes/Analytics/Discovery/AnalyticsPropertyDiscoveryInterface.php
    classes/Analytics/Discovery/GoogleAnalyticsPropertyDiscovery.php
    classes/Analytics/Discovery/GoogleAnalyticsPropertyDiscoveryFactory.php
    classes/Analytics/Testing/FakeAnalyticsPropertyDiscovery.php
    tests/p6/property-discovery-contract.php
)

for file in "${required[@]}"; do
    test -f "$file"
done

grep -Fq \
    '"google/analytics-admin": "^0.32"' \
    composer.json

grep -Fq \
    '"name": "google/analytics-admin"' \
    composer.lock

grep -Fq \
    "new ListAccountSummariesRequest()" \
    classes/Analytics/Discovery/GoogleAnalyticsPropertyDiscovery.php

grep -Fq \
    "'transport' => 'rest'" \
    classes/Analytics/Discovery/GoogleAnalyticsPropertyDiscoveryFactory.php

grep -Fq \
    'AnalyticsPropertyDiscoveryInterface' \
    classes/Analytics/Testing/FakeAnalyticsPropertyDiscovery.php

echo "P6A_STATIC_CONTRACT=PASS"
