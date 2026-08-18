<?php

declare(strict_types=1);

namespace Goosialize\Google\SearchConsole\Connection;

use Google\Auth\FetchAuthTokenInterface;
use Goosialize\Google\Core\Auth\CredentialProviderInterface;
use Goosialize\Google\Core\Auth\CredentialReference;
use GuzzleHttp\Client;
use RuntimeException;

final class GoogleSearchConsoleClientFactory
{
    public function __construct(
        private CredentialProviderInterface $credentialProvider
    ) {
    }

    public function create(
        CredentialReference $reference
    ): GoogleSearchConsoleClient {
        if (
            !$this->credentialProvider
                ->supports(
                    $reference->type()
                )
        ) {
            throw new RuntimeException(
                'Credential provider does not support this credential type.'
            );
        }

        $resolved =
            $this->credentialProvider
                ->resolve($reference);

        $credentials =
            $resolved['credentials']
            ?? null;

        if (
            !$credentials
            instanceof FetchAuthTokenInterface
        ) {
            throw new RuntimeException(
                'Credential provider did not return Google auth credentials.'
            );
        }

        return new GoogleSearchConsoleClient(
            $credentials,
            new Client([
                'timeout' => 30.0,
                'http_errors' => false,
            ])
        );
    }
}
