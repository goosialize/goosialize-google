<?php

declare(strict_types=1);

namespace Goosialize\Google\Analytics\Dashboard;

use InvalidArgumentException;

enum DashboardPeriod: int
{
    case DAYS_7 = 7;
    case DAYS_30 = 30;
    case DAYS_90 = 90;

    public static function fromDays(
        int $days
    ): self {
        return match ($days) {
            7 => self::DAYS_7,
            30 => self::DAYS_30,
            90 => self::DAYS_90,
            default => throw new InvalidArgumentException(
                'Dashboard period must be 7, 30 or 90 days.'
            ),
        };
    }
}
