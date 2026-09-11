<?php

declare(strict_types=1);

$root =
    dirname(__DIR__, 2);

$entry =
    file_get_contents(
        $root
        . '/goosialize-google.php'
    );

if ($entry === false) {
    throw new RuntimeException(
        'Unable to read plugin entrypoint.'
    );
}

if (
    !str_contains(
        $entry,
        '(string) $this->grav->output'
    )
) {
    throw new RuntimeException(
        'Grav object output read authority missing.'
    );
}

if (
    !str_contains(
        $entry,
        '$this->grav->output ='
    )
) {
    throw new RuntimeException(
        'Grav object output write authority missing.'
    );
}

if (
    str_contains(
        $entry,
        "\$this->grav['output']"
    )
) {
    throw new RuntimeException(
        'Legacy container-style output authority remains.'
    );
}

echo "ANALYTICS_OUTPUT_AUTHORITY_CONTRACT=PASS\n";
