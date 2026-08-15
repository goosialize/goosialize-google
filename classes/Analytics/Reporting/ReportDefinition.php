<?php

declare(strict_types=1);

namespace Goosialize\Google\Analytics\Reporting;

use InvalidArgumentException;

final readonly class ReportDefinition
{
    /**
     * @param list<string> $dimensions
     * @param list<string> $metrics
     */
    public function __construct(
        private string $id,
        private array $dimensions,
        private array $metrics,
        private int $limit = 100
    ) {
        if (trim($this->id) === '') {
            throw new InvalidArgumentException(
                'Report ID cannot be empty.'
            );
        }

        if ($this->metrics === []) {
            throw new InvalidArgumentException(
                'A report requires at least one metric.'
            );
        }

        if ($this->limit < 1) {
            throw new InvalidArgumentException(
                'Report limit must be positive.'
            );
        }

        foreach (
            array_merge(
                $this->dimensions,
                $this->metrics
            ) as $name
        ) {
            if (
                trim($name) === ''
                || !preg_match(
                    '/^[A-Za-z][A-Za-z0-9_]*$/',
                    $name
                )
            ) {
                throw new InvalidArgumentException(
                    'Invalid GA4 field name: '
                    . $name
                );
            }
        }
    }

    public function id(): string
    {
        return trim($this->id);
    }

    /**
     * @return list<string>
     */
    public function dimensions(): array
    {
        return $this->dimensions;
    }

    /**
     * @return list<string>
     */
    public function metrics(): array
    {
        return $this->metrics;
    }

    public function limit(): int
    {
        return $this->limit;
    }
}
