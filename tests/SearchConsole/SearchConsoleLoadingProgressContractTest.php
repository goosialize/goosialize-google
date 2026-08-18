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
    "const progress =",
    "element('div')",
    "'h-0.5 w-full overflow-hidden rounded-full bg-muted'",
    "'progressbar'",
    "'aria-busy'",
    "'Loading Search Console data'",
    "'Loading Google Analytics data'",
    "const progressBar =",
    "'h-full w-1/3 rounded-full bg-primary'",
    "progressBar.animate(",
    "'translateX(-120%)'",
    "'translateX(320%)'",
    "duration: 900",
    "iterations: Infinity",
    "easing: 'ease-in-out'",
    "progress.append(",
    "progressBar",
];

foreach ($required as $needle) {
    if (
        str_contains(
            $page,
            $needle
        ) === false
    ) {
        throw new RuntimeException(
            'Loading progress contract missing: '
            . $needle
        );
    }
}

$forbidden = [
    'Loading Google Search Console…',
    'Loading Google Analytics…',
    'animate-pulse',
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
            'Legacy loading UI remains: '
            . $needle
        );
    }
}

echo "SEARCH_CONSOLE_LOADING_PROGRESS_CONTRACT=PASS\n";
