<?php

declare(strict_types=1);

namespace Goosialize\Google\Core\Auth;

use Google\Auth\Credentials\ServiceAccountCredentials;
use RuntimeException;

final class ServiceAccountCredentialProvider
    implements CredentialProviderInterface
{
    public const ANALYTICS_READONLY_SCOPE =
        'https://www.googleapis.com/auth/analytics.readonly';

    /**
     * @param list<string> $scopes
     */
    public function __construct(
        private array $scopes = [
            self::ANALYTICS_READONLY_SCOPE,
        ]
    ) {
        if ($this->scopes === []) {
            throw new RuntimeException(
                'At least one Google OAuth scope is required.'
            );
        }

        foreach ($this->scopes as $scope) {
            if (
                !is_string($scope)
                || trim($scope) === ''
            ) {
                throw new RuntimeException(
                    'Google OAuth scopes must be non-empty strings.'
                );
            }
        }
    }

    public function supports(
        CredentialType $type
    ): bool {
        return $type
            === CredentialType::SERVICE_ACCOUNT_FILE;
    }

    public function resolve(
        CredentialReference $reference
    ): array {
        if (!$this->supports($reference->type())) {
            throw new RuntimeException(
                'Unsupported credential type.'
            );
        }

        $path = $reference->reference();

        if (!is_file($path)) {
            throw new RuntimeException(
                'Google credential file does not exist.'
            );
        }

        if (!is_readable($path)) {
            throw new RuntimeException(
                'Google credential file is not readable.'
            );
        }

        $json = file_get_contents($path);

        if ($json === false) {
            throw new RuntimeException(
                'Unable to read Google credential file.'
            );
        }

        try {
            $data = json_decode(
                $json,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (\JsonException $e) {
            throw new RuntimeException(
                'Google credential file contains invalid JSON.',
                0,
                $e
            );
        }

        if (!is_array($data)) {
            throw new RuntimeException(
                'Google credential payload is invalid.'
            );
        }

        foreach (
            [
                'client_email',
                'private_key',
                'token_uri',
            ] as $required
        ) {
            if (
                !isset($data[$required])
                || !is_string($data[$required])
                || trim($data[$required]) === ''
            ) {
                throw new RuntimeException(
                    'Google credential file is missing required fields.'
                );
            }
        }

        $credentials = new ServiceAccountCredentials(
            array_values(
                array_unique(
                    array_map(
                        'trim',
                        $this->scopes
                    )
                )
            ),
            $data
        );

        return [
            'credentials' => $credentials,
        ];
    }
}
