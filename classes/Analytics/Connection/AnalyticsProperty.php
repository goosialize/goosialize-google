<?php

declare(strict_types=1);

namespace Goosialize\Google\Analytics\Connection;

use InvalidArgumentException;

final readonly class AnalyticsProperty
{
    public function __construct(
        private string $connectionId,
        private AnalyticsPropertyId $propertyId,
        private string $displayName,
        private bool $enabled = true
    ) {
        if (trim($this->connectionId) === '') {
            throw new InvalidArgumentException(
                'Connection ID cannot be empty.'
            );
        }

        if (trim($this->displayName) === '') {
            throw new InvalidArgumentException(
                'Property display name cannot be empty.'
            );
        }
    }

    public function connectionId(): string
    {
        return trim($this->connectionId);
    }

    public function propertyId(): AnalyticsPropertyId
    {
        return $this->propertyId;
    }

    public function displayName(): string
    {
        return trim($this->displayName);
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }
}
