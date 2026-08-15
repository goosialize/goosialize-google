<?php

declare(strict_types=1);

namespace Goosialize\Google\Analytics\Reporting;

use DateTimeImmutable;
use InvalidArgumentException;

final class DateRangeFactory
{
    public function trailingDays(
        DateTimeImmutable $end,
        int $days
    ): DateRange {
        if ($days < 1) {
            throw new InvalidArgumentException(
                'Days must be greater than zero.'
            );
        }

        return new DateRange(
            $end->modify(
                '-' . ($days - 1) . ' days'
            ),
            $end
        );
    }

    public function previousPeriod(
        DateRange $current
    ): DateRange {
        $days = (
            $current->start()
                ->diff($current->end())
                ->days
            ?? 0
        ) + 1;

        $previousEnd = $current
            ->start()
            ->modify('-1 day');

        $previousStart = $previousEnd
            ->modify(
                '-' . ($days - 1) . ' days'
            );

        return new DateRange(
            $previousStart,
            $previousEnd
        );
    }
}
