<?php

declare(strict_types=1);

namespace Goosialize\Google\Analytics\Discovery;

use Goosialize\Google\Analytics\Connection\AnalyticsPropertyId;
use InvalidArgumentException;

final readonly class AnalyticsPropertySummary
{
    public function __construct(
        private string $accountResource,
        private string $accountName,
        private AnalyticsPropertyId $propertyId,
        private string $propertyName
    ) {
        if (trim($this->accountResource) === '') {
            throw new InvalidArgumentException(
                'Analytics account resource cannot be empty.'
            );
        }

        if (trim($this->accountName) === '') {
            throw new InvalidArgumentException(
                'Analytics account name cannot be empty.'
            );
        }

        if (trim($this->propertyName) === '') {
            throw new InvalidArgumentException(
                'Analytics property name cannot be empty.'
            );
        }
    }

    public function accountResource(): string
    {
        return trim($this->accountResource);
    }

    public function accountName(): string
    {
        return trim($this->accountName);
    }

    public function propertyId(): AnalyticsPropertyId
    {
        return $this->propertyId;
    }

    public function propertyName(): string
    {
        return trim($this->propertyName);
    }
}
