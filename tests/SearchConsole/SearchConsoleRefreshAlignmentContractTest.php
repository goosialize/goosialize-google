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
    "const refreshGroup",
    "const refreshSpacer",
    "'invisible text-xs font-medium text-muted-foreground'",
    "'mt-1 inline-flex h-10 items-center justify-center rounded-md border border-border",
    "refreshGroup.append(",
    "propertyGroup,\n        periodGroup,\n        refreshGroup",
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

echo "SEARCH_CONSOLE_REFRESH_ALIGNMENT_CONTRACT=PASS\n";
