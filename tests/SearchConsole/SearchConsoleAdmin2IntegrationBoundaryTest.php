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
    'mt-1 inline-flex h-10 items-center justify-center rounded-md border border-border',
    'mt-1 inline-flex h-10 items-center rounded-md border border-border bg-background p-1 shadow-sm',
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
    '.style.',
    'Goosialize Ltd',
    'grav:plugin-page-action',
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

echo "ADMIN2_INTEGRATION_BOUNDARY=PASS\n";
