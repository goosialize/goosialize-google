<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$file =
    $root
    . '/classes/Admin/AnalyticsDashboardController.php';

$source = file_get_contents($file);

if ($source === false) {
    throw new RuntimeException(
        'Unable to read dashboard controller.'
    );
}

$required = [
    "getAttribute(\n                    'api_key_scopes'",
    'private function scopesPermit(',
    "\$scope === '*'",
    '$scope === $permission',
    'str_starts_with(',
    '$scope . \'.\'',
    'authorized($request, $user)',
];

foreach ($required as $needle) {
    if (!str_contains($source, $needle)) {
        throw new RuntimeException(
            'Scope-cap contract missing: '
            . $needle
        );
    }
}

$scopePosition =
    strpos(
        $source,
        "'api_key_scopes'"
    );

$superPosition =
    strpos(
        $source,
        "'api.super'",
        $scopePosition
    );

if (
    $scopePosition === false
    || $superPosition === false
    || $scopePosition >= $superPosition
) {
    throw new RuntimeException(
        'API-key scope cap must execute before api.super.'
    );
}

echo "P6_API_KEY_SCOPE_CAP_CONTRACT=PASS\n";
