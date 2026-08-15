<?php

declare(strict_types=1);

namespace Goosialize\Google\Analytics\Discovery;

interface AnalyticsPropertyDiscoveryInterface
{
    /**
     * @return list<AnalyticsPropertySummary>
     */
    public function discover(): array;
}
