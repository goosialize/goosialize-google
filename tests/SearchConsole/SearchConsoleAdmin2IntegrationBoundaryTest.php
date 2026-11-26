<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$page = file_get_contents(
    $root
    . '/admin-next/pages/goosialize-google.js'
);

if ($page === false) {
    throw new RuntimeException(
        'Unable to read Google Admin2 page.'
    );
}

$required = [
    'space-y-4 px-6 pb-6',
    'border-b-2 border-primary',
    'border-b-2 border-transparent',
    'h-10 w-full rounded-lg border border-input bg-muted/50',
    'text-xs',
    'font-medium',
    'text-muted-foreground',
    'goosialize-google-refresh-button',
    'goosialize-google-period-row mt-1',
    'goosialize-google-period-refresh',
    'inline-flex h-10 items-center rounded-md border border-border bg-background p-1 shadow-sm',
    "periodRow.append(\n        periodControls,\n        refreshButton",
    "periodGroup.replaceChildren(\n        periodLabel,\n        periodRow",
    "'aria-label'",
    "'Refresh data'",
];

foreach ($required as $needle) {
    if (
        str_contains(
            $page,
            $needle
        ) === false
    ) {
        throw new RuntimeException(
            'Admin2 integration contract missing: '
            . $needle
        );
    }
}

$forbidden = [
    // Keep raw inline CSS prohibited throughout the component, in line with
    // the native Admin2 composition and loading progress contracts.
    'style="',
    "style='",
    '.style.cssText',
    "setAttribute('style'",
    'setAttribute("style"',
    'Goosialize Ltd',
    'grav:plugin-page-action',
    'const refreshGroup',
    'const refreshSpacer',
    'mt-1 inline-flex h-10 items-center justify-center rounded-md border border-border',
];

foreach ($forbidden as $needle) {
    if (
        str_contains(
            $page,
            $needle
        )
    ) {
        throw new RuntimeException(
            'Unsupported/legacy integration remains: '
            . $needle
        );
    }
}

// Native navigation and report controls must remain class-based. Scope
// the stronger DOM style restriction to this integration boundary; the
// existing chart renderer and metric cards still use property-level styles.
$startMarker = 'const productNav =';
$endMarker = 'root.append(toolbar);';
if (substr_count($page, $startMarker) !== 1 || substr_count($page, $endMarker) !== 1) {
    throw new RuntimeException('Admin2 navigation/toolbar boundaries are ambiguous.');
}
$start = strpos($page, $startMarker);
$end = strpos($page, $endMarker);
if ($start === false || $end === false || $end <= $start) {
    throw new RuntimeException('Admin2 navigation/toolbar boundary is missing.');
}
$toolbar = substr($page, $start, $end - $start + strlen($endMarker));
foreach (['.style.', '.style[', '.style ='] as $inlineStyle) {
    if (str_contains($toolbar, $inlineStyle)) {
        throw new RuntimeException('Native Admin2 navigation/toolbar contains direct inline styling.');
    }
}
foreach (['root.append(productNav);', 'periodRow.append(', 'periodGroup.replaceChildren(', 'root.append(toolbar);'] as $needle) {
    if (!str_contains($toolbar, $needle)) {
        throw new RuntimeException('Native Admin2 toolbar composition missing: ' . $needle);
    }
}

echo "ADMIN2_INTEGRATION_BOUNDARY=PASS\n";
