<?php

declare(strict_types=1);

namespace Goosialize\Google\SearchConsole\Connection;

use InvalidArgumentException;

final readonly class SearchConsoleProperty
{
    public function __construct(
        public string $siteUrl,
        public ?string $permissionLevel = null,
    ) {
        if (trim($this->siteUrl) === '') {
            throw new InvalidArgumentException(
                'Search Console property must not be empty.'
            );
        }

        if (
            !str_starts_with($this->siteUrl, 'sc-domain:')
            && filter_var($this->siteUrl, FILTER_VALIDATE_URL) === false
        ) {
            throw new InvalidArgumentException(
                'Search Console property must be a valid URL-prefix or sc-domain property.'
            );
        }
    }

    public function isDomainProperty(): bool
    {
        return str_starts_with(
            $this->siteUrl,
            'sc-domain:'
        );
    }
}
