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
    'use Grav\\Common\\Grav;',
    'private readonly Config $config;',
    'Grav $grav',
    "\$grav['config']",
    '$config instanceof Config',
    '$this->config = $config;',
];

foreach ($required as $needle) {
    if (!str_contains($source, $needle)) {
        throw new RuntimeException(
            'Missing DI contract: ' . $needle
        );
    }
}

$old = <<<'OLD'
public function __construct(
        private readonly Config $config
OLD;

if (str_contains($source, $old)) {
    throw new RuntimeException(
        'Old Config constructor injection remains.'
    );
}

echo "P6_CONTROLLER_DI_CONTRACT=PASS\n";
