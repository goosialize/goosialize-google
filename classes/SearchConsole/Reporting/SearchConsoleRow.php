<?php

declare(strict_types=1);

namespace Goosialize\Google\SearchConsole\Reporting;

final readonly class SearchConsoleRow
{
    /**
     * @param list<string> $keys
     */
    public function __construct(
        public array $keys,
        public float $clicks,
        public float $impressions,
        public float $ctr,
        public float $position,
    ) {
    }
}
