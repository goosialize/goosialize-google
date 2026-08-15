<?php

declare(strict_types=1);

namespace Goosialize\Google\Analytics\Reporting;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class DateRange
{
    public function __construct(
        private DateTimeImmutable $start,
        private DateTimeImmutable $end
    ) {
        if ($this->start > $this->end) {
            throw new InvalidArgumentException(
                'Date range start must not be after end.'
            );
        }
    }

    public static function fromStrings(
        string $start,
        string $end
    ): self {
        $startDate = DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $start
        );

        $endDate = DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $end
        );

        if (
            !$startDate
            || !$endDate
            || $startDate->format('Y-m-d') !== $start
            || $endDate->format('Y-m-d') !== $end
        ) {
            throw new InvalidArgumentException(
                'Dates must use YYYY-MM-DD format.'
            );
        }

        return new self(
            $startDate,
            $endDate
        );
    }

    public function start(): DateTimeImmutable
    {
        return $this->start;
    }

    public function end(): DateTimeImmutable
    {
        return $this->end;
    }

    /**
     * @return array{startDate:string,endDate:string}
     */
    public function toApiArray(): array
    {
        return [
            'startDate' => $this->start->format('Y-m-d'),
            'endDate' => $this->end->format('Y-m-d'),
        ];
    }
}
