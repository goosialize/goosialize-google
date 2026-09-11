<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$ui = file_get_contents(
    $root
    . '/admin-next/pages/goosialize-google.js'
);

if ($ui === false) {
    throw new RuntimeException(
        'Unable to read Google Admin2 page.'
    );
}

foreach (
    [
        'this.productStates = {',
        'analytics: {',
        'search_console: {',
        'loaded: false',
        'saveProductState(',
        'restoreProductState(',
        'markProductLoaded(',
        'state.loaded = true',
        'this.saveProductState();',
        'const cached =',
        'this.restoreProductState(',
        'if (cached) {',
        'this.render();',
    ]
    as $needle
) {
    if (!str_contains(
        $ui,
        $needle
    )) {
        throw new RuntimeException(
            'Missing Google product state cache contract: '
            . $needle
        );
    }
}

$obsolete = <<<'JS'
            this.product = id;
            this.properties = [];
            this.propertyId = '';
            this.data = null;
            this.error = null;

            this.load();
JS;

if (str_contains(
    $ui,
    $obsolete
)) {
    throw new RuntimeException(
        'Legacy full-reload product switch remains.'
    );
}

echo "GOOGLE_PRODUCT_STATE_CACHE_CONTRACT=PASS\n";
