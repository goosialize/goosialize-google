<?php

declare(strict_types=1);

namespace Goosialize\Google\Analytics\Connection;

use RuntimeException;
use Throwable;

final class AnalyticsApiException extends RuntimeException
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
