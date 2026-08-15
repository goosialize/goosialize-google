<?php

declare(strict_types=1);

namespace Goosialize\Google\Core\Auth;

use InvalidArgumentException;

final readonly class CredentialReference
{
    public function __construct(
        private CredentialType $type,
        private string $reference
    ) {
        $reference = trim($this->reference);

        if ($reference === '') {
            throw new InvalidArgumentException(
                'Credential reference cannot be empty.'
            );
        }

        if (
            $this->type === CredentialType::SERVICE_ACCOUNT_FILE
            && !str_starts_with($reference, '/')
        ) {
            throw new InvalidArgumentException(
                'Service account credential reference must be an absolute path.'
            );
        }
    }

    public function type(): CredentialType
    {
        return $this->type;
    }

    public function reference(): string
    {
        return trim($this->reference);
    }
}
