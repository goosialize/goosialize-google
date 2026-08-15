<?php

declare(strict_types=1);

namespace Goosialize\Google\Analytics\Testing;

use Goosialize\Google\Analytics\Discovery\AnalyticsPropertyDiscoveryInterface;
use Goosialize\Google\Analytics\Discovery\AnalyticsPropertySummary;

final class FakeAnalyticsPropertyDiscovery
    implements AnalyticsPropertyDiscoveryInterface
{
    /**
     * @param list<AnalyticsPropertySummary> $properties
     */
    public function __construct(
        private array $properties
    ) {
    }

    public function discover(): array
    {
        return $this->properties;
    }
}
