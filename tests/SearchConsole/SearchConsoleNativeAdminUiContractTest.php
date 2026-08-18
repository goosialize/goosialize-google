<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$page = file_get_contents(
    $root
    . '/admin-next/pages/goosialize-google.js'
);

$plugin = file_get_contents(
    $root
    . '/goosialize-google.php'
);

if (
    $page === false
    || $plugin === false
) {
    throw new RuntimeException(
        'Unable to read native Admin2 contract files.'
    );
}

if (
    str_contains(
        $page,
        '.style.'
    )
) {
    throw new RuntimeException(
        'Inline visual styling remains in Google Admin2 page.'
    );
}

$requiredClasses = [
    'text-sm',
    'text-2xl',
    'font-medium',
    'font-semibold',
    'text-muted-foreground',
    'text-foreground',
    'rounded-md',
    'rounded-lg',
    'border-border',
    'border-input',
    'bg-background',
    'bg-card',
    'bg-primary',
    'text-primary-foreground',
    'hover:bg-accent',
    'p-5',
    'px-4',
    'py-2',
    'py-3',
    'gap-4',
];

foreach (
    $requiredClasses
    as $class
) {
    if (
        str_contains(
            $page,
            $class
        ) === false
    ) {
        throw new RuntimeException(
            'Native Admin2 utility missing: '
            . $class
        );
    }
}

foreach (
    [
        "'label' =>\n                'Goosialize Google'",
        "'title' =>\n                'Goosialize Google'",
    ] as $needle
) {
    if (
        str_contains(
            $plugin,
            $needle
        ) === false
    ) {
        throw new RuntimeException(
            'Goosialize Google page identity contract missing.'
        );
    }
}

echo "SEARCH_CONSOLE_NATIVE_ADMIN_UI_CONTRACT=PASS\n";
