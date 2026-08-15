#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
    classes/Core/Auth/CredentialType.php
    classes/Core/Auth/CredentialReference.php
    classes/Core/Auth/CredentialProviderInterface.php
    classes/Core/Config/GoogleConnection.php
    classes/Core/Persistence/SqliteConnectionFactory.php
    classes/Analytics/Connection/AnalyticsPropertyId.php
    classes/Analytics/Connection/AnalyticsProperty.php
    classes/Analytics/Connection/AnalyticsDataClientInterface.php
    migrations/001_initial.sql
    tests/p2/domain-contract.php
    tests/p2/sqlite-contract.php
)

for file in "${required[@]}"; do
    test -f "$file"
done

grep -Fq "case SERVICE_ACCOUNT_FILE" \
    classes/Core/Auth/CredentialType.php

grep -Fq "case OAUTH" \
    classes/Core/Auth/CredentialType.php

grep -Fq "properties/" \
    classes/Analytics/Connection/AnalyticsPropertyId.php

grep -Fq "CREATE TABLE IF NOT EXISTS google_connections" \
    migrations/001_initial.sql

grep -Fq "CREATE TABLE IF NOT EXISTS analytics_properties" \
    migrations/001_initial.sql

grep -Fq "CREATE TABLE IF NOT EXISTS report_cache" \
    migrations/001_initial.sql

grep -Fq "CREATE TABLE IF NOT EXISTS sync_runs" \
    migrations/001_initial.sql

grep -Fq "default_property:" \
    goosialize-google.yaml

grep -Fq "PLUGIN_GOOSIALIZE_GOOGLE:" \
    languages/en.yaml

grep -Fq "PLUGIN_GOOSIALIZE_GOOGLE:" \
    languages/el.yaml

echo "P2_STATIC_CONTRACT=PASS"
