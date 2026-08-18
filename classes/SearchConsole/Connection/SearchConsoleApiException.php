<?php

declare(strict_types=1);

namespace Goosialize\Google\SearchConsole\Connection;

use RuntimeException;
use Throwable;

final class SearchConsoleApiException extends RuntimeException
{
    public function __construct(
        string $message,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct(
            $message,
            $code,
            $previous
        );
    }
}
