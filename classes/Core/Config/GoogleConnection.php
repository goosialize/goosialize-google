<?php

declare(strict_types=1);

namespace Goosialize\Google\Core\Config;

use Goosialize\Google\Core\Auth\CredentialReference;
use InvalidArgumentException;

final readonly class GoogleConnection
{
    public function __construct(
        private string $id,
        private string $label,
        private CredentialReference $credential,
        private bool $enabled = true
    ) {
        if (trim($this->id) === '') {
            throw new InvalidArgumentException(
                'Connection ID cannot be empty.'
            );
        }

        if (trim($this->label) === '') {
            throw new InvalidArgumentException(
                'Connection label cannot be empty.'
            );
        }
    }

    public function id(): string
    {
        return trim($this->id);
    }

    public function label(): string
    {
        return trim($this->label);
    }

    public function credential(): CredentialReference
    {
        return $this->credential;
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }
}
