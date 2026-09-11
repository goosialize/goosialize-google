<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$ui = file_get_contents(
    $root
    . '/admin-next/pages/goosialize-google.js'
);

foreach (
    [
        'goosialize-google-period-row',
        'goosialize-google-period-refresh',
        "periodRow.append(\n        periodControls,\n        refreshButton",
        "periodGroup.replaceChildren(\n        periodLabel,\n        periodRow",
        "toolbar.append(\n        propertyGroup,\n        periodGroup",
        'align-items: stretch',
        'align-self: stretch',
    ]
    as $needle
) {
    if (!str_contains(
        $ui,
        $needle
    )) {
        throw new RuntimeException(
            'Missing refresh structural alignment contract: '
            . $needle
        );
    }
}

foreach (
    [
        'refreshSpacer',
        'refreshGroup.append(',
    ]
    as $obsolete
) {
    if (str_contains(
        $ui,
        $obsolete
    )) {
        throw new RuntimeException(
            'Obsolete refresh layout remains: '
            . $obsolete
        );
    }
}

echo "ANALYTICS_REFRESH_STRUCTURAL_ALIGNMENT_CONTRACT=PASS\n";
