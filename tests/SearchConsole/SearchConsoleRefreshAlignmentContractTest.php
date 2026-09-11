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
    "'goosialize-google-period-row mt-1'",
    "'goosialize-google-period-refresh'",
    'goosialize-google-period-row',
    'goosialize-google-period-refresh',
    'align-items: center',
    'align-self: center',
    'width: 2.35rem',
    'height: 2.35rem',
    "periodRow.append(\n        periodControls,\n        refreshButton",
    "periodGroup.replaceChildren(\n        periodLabel,\n        periodRow",
    "toolbar.append(\n        propertyGroup,\n        periodGroup",
];

foreach ($required as $needle) {
    if (
        str_contains(
            $page,
            $needle
        ) === false
    ) {
        throw new RuntimeException(
            'Refresh alignment contract missing: '
            . $needle
        );
    }
}

$forbidden = [
    'const refreshGroup',
    'const refreshSpacer',
    'refreshGroup.append(',
    "'mt-1 inline-flex h-10 items-center justify-center rounded-md border border-border'",
];

foreach ($forbidden as $needle) {
    if (
        str_contains(
            $page,
            $needle
        )
    ) {
        throw new RuntimeException(
            'Legacy refresh alignment remains: '
            . $needle
        );
    }
}

echo "SEARCH_CONSOLE_REFRESH_ALIGNMENT_CONTRACT=PASS\n";
