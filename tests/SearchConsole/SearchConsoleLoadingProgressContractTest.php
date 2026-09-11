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
    'goosialize-google-loading-panel',
    'goosialize-google-loading-header',
    'goosialize-google-loading-track',
    'goosialize-google-loading-value',
    'goosialize-google-loading-value.is-indeterminate',
    'goosialize-google-loading-slide',
    "'progressbar'",
    "'aria-busy'",
    "'Loading Search Console data'",
    "'Loading Google Analytics data'",
    "'Loading Search Console…'",
    'translateX(-120%)',
    'translateX(320%)',
    '0.9s ease-in-out infinite',
    "progressBar.classList.add(",
    "'is-indeterminate'",
    'progress.append(',
    'progressBar',
];

foreach ($required as $needle) {
    if (
        str_contains(
            $page,
            $needle
        ) === false
    ) {
        throw new RuntimeException(
            'Current loading progress contract missing: '
            . $needle
        );
    }
}

$forbidden = [
    "'h-0.5 w-full overflow-hidden rounded-full bg-muted'",
    "'h-full w-1/3 rounded-full bg-primary'",
    'animate-pulse',
    'style="',
    "style='",
    '.style.cssText',
    "setAttribute('style'",
    'setAttribute("style"',
];

foreach ($forbidden as $needle) {
    if (
        str_contains(
            $page,
            $needle
        )
    ) {
        throw new RuntimeException(
            'Legacy/raw loading UI remains: '
            . $needle
        );
    }
}

echo "SEARCH_CONSOLE_LOADING_PROGRESS_CONTRACT=PASS\n";
