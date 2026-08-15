<?php

declare(strict_types=1);

namespace Goosialize\Google\Analytics;

use Goosialize\Google\Core\GoogleProductModuleInterface;

final class AnalyticsModule implements GoogleProductModuleInterface
{
    public function getId(): string
    {
        return 'analytics';
    }

    public function isEnabled(): bool
    {
        return true;
    }
}
