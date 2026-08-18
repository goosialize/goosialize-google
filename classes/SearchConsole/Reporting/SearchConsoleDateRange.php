<?php

declare(strict_types=1);

namespace Goosialize\Google\SearchConsole\Reporting;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class SearchConsoleDateRange
{
    public function __construct(
        public DateTimeImmutable $startDate,
        public DateTimeImmutable $endDate,
    ) {
        if ($this->endDate < $this->startDate) {
            throw new InvalidArgumentException(
                'Search Console end date must not precede start date.'
            );
        }
    }

    /**
     * @return array{
     *     startDate:string,
     *     endDate:string
     * }
     */
    public function toApiPayload(): array
    {
        return [
            'startDate' => $this->startDate->format('Y-m-d'),
            'endDate' => $this->endDate->format('Y-m-d'),
        ];
    }
}
