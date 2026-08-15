#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
    goosialize-google.php
    blueprints.yaml
    goosialize-google.yaml
    composer.json
    languages/en.yaml
    languages/el.yaml
    classes/Core/GoogleProductModuleInterface.php
    classes/Analytics/AnalyticsModule.php
)

for file in "${required[@]}"; do
    test -f "$file"
done

grep -q 'class GoosializeGooglePlugin' goosialize-google.php
grep -q 'GoogleProductModuleInterface' classes/Analytics/AnalyticsModule.php
grep -q '"Goosialize\\\\Google\\\\": "classes/"' composer.json

echo "P1_STATIC_CONTRACT=PASS"
