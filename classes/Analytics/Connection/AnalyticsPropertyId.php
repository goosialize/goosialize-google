<?php

declare(strict_types=1);

namespace Goosialize\Google\Analytics\Connection;

use InvalidArgumentException;

final readonly class AnalyticsPropertyId
{
    public function __construct(
        private string $value
    ) {
        $value = trim($this->value);

        if (
            $value === ''
            || !ctype_digit($value)
            || $value === '0'
        ) {
            throw new InvalidArgumentException(
                'GA4 property ID must be a positive numeric identifier.'
            );
        }
    }

    public function value(): string
    {
        return trim($this->value);
    }

    public function resourceName(): string
    {
        return 'properties/' . $this->value();
    }
}
