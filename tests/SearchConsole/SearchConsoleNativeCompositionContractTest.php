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
    'border-b-2 border-primary px-3 py-2 text-sm font-medium text-primary',
    'border-b-2 border-transparent',
    'text-xs font-medium text-muted-foreground',
    'h-10 w-full appearance-none rounded-lg border border-input bg-muted/50',
    'sm:flex-row sm:items-end',
    'min-w-0 flex-1',
    'mt-1 inline-flex h-10 items-center',
    'h-10 shrink-0 rounded-md border border-border',
    'text-xl font-semibold tracking-tight text-foreground',
    'rounded-lg border border-border bg-card py-12 text-center',
];

foreach ($required as $needle) {
    if (
        str_contains(
            $page,
            $needle
        ) === false
    ) {
        throw new RuntimeException(
            'P9-F2 native composition missing: '
            . $needle
        );
    }
}

$forbidden = [
    'grid gap-4 rounded-lg border border-border bg-card p-5 md:grid-cols-[minmax(0,1fr)_auto_auto]',
    'inline-flex gap-1 rounded-md border border-border bg-card p-1',
    'inline-flex gap-1 rounded-md border border-border bg-background p-1',
    'Goosialize Ltd',
    '.style.',
];

foreach ($forbidden as $needle) {
    if (
        str_contains(
            $page,
            $needle
        )
    ) {
        throw new RuntimeException(
            'P9-F2 forbidden legacy composition remains: '
            . $needle
        );
    }
}

echo "SEARCH_CONSOLE_NATIVE_COMPOSITION_CONTRACT=PASS\n";
