<?php

declare(strict_types=1);

namespace Goosialize\Google\SearchConsole\Connection;

use Google\Auth\FetchAuthTokenInterface;
use JsonException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

final class GoogleSearchConsoleClient
    implements SearchConsoleClientInterface
{
    private const API_BASE =
        'https://www.googleapis.com/webmasters/v3';

    public function __construct(
        private FetchAuthTokenInterface $credentials,
        private ClientInterface $httpClient
    ) {
    }

    public function listSites(): array
    {
        $response = $this->request(
            'GET',
            self::API_BASE . '/sites'
        );

        $entries = $response['siteEntry'] ?? [];

        if (!is_array($entries)) {
            return [];
        }

        $sites = [];

        foreach ($entries as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $siteUrl = $entry['siteUrl'] ?? null;

            if (
                !is_string($siteUrl)
                || trim($siteUrl) === ''
            ) {
                continue;
            }

            $permission =
                $entry['permissionLevel']
                ?? null;

            $sites[] = [
                'siteUrl' => trim($siteUrl),
                'permissionLevel' =>
                    is_string($permission)
                        ? trim($permission)
                        : null,
            ];
        }

        return $sites;
    }

    public function query(
        string $siteUrl,
        array $request
    ): array {
        $siteUrl = trim($siteUrl);

        if ($siteUrl === '') {
            throw new SearchConsoleApiException(
                'Search Console site URL cannot be empty.'
            );
        }

        return $this->request(
            'POST',
            self::API_BASE
                . '/sites/'
                . rawurlencode($siteUrl)
                . '/searchAnalytics/query',
            $request
        );
    }

    /**
     * @param array<string, mixed>|null $payload
     *
     * @return array<string, mixed>
     */
    private function request(
        string $method,
        string $uri,
        ?array $payload = null
    ): array {
        try {
            $token = $this->credentials
                ->fetchAuthToken();

            $accessToken =
                $token['access_token']
                ?? null;

            if (
                !is_string($accessToken)
                || trim($accessToken) === ''
            ) {
                throw new SearchConsoleApiException(
                    'Google authentication did not return an access token.'
                );
            }

            $options = [
                'headers' => [
                    'Authorization' =>
                        'Bearer ' . trim($accessToken),
                    'Accept' =>
                        'application/json',
                ],
            ];

            if ($payload !== null) {
                $options['headers']['Content-Type'] =
                    'application/json';

                $options['body'] =
                    json_encode(
                        $payload,
                        JSON_THROW_ON_ERROR
                    );
            }

            $response =
                $this->httpClient->request(
                    $method,
                    $uri,
                    $options
                );

            return $this->decodeResponse(
                $response
            );
        } catch (SearchConsoleApiException $e) {
            throw $e;
        } catch (JsonException $e) {
            throw new SearchConsoleApiException(
                'Unable to encode Search Console API request.',
                0,
                $e
            );
        } catch (Throwable $e) {
            throw new SearchConsoleApiException(
                'Google Search Console API request failed.',
                0,
                $e
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeResponse(
        ResponseInterface $response
    ): array {
        $status = $response->getStatusCode();

        $body = (string) $response->getBody();

        if ($status < 200 || $status >= 300) {
            throw new SearchConsoleApiException(
                sprintf(
                    'Google Search Console API returned HTTP %d.',
                    $status
                ),
                $status
            );
        }

        if (trim($body) === '') {
            return [];
        }

        try {
            $decoded = json_decode(
                $body,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $e) {
            throw new SearchConsoleApiException(
                'Google Search Console API returned invalid JSON.',
                0,
                $e
            );
        }

        if (!is_array($decoded)) {
            throw new SearchConsoleApiException(
                'Google Search Console API returned an invalid payload.'
            );
        }

        return $decoded;
    }
}
