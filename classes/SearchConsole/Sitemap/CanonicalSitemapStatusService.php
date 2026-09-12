<?php

declare(strict_types=1);

namespace Goosialize\Google\SearchConsole\Sitemap;

use Goosialize\Google\SearchConsole\Connection\SearchConsoleClientInterface;
use InvalidArgumentException;
use Psr\Http\Client\ClientInterface;
use Throwable;

final readonly class CanonicalSitemapStatusService
{
    public function __construct(
        private SearchConsoleClientInterface $searchConsole,
        private ClientInterface $httpClient
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function status(
        string $siteUrl,
        string $sitemapUrl
    ): array {
        $siteUrl =
            trim($siteUrl);

        $sitemapUrl =
            trim($sitemapUrl);

        if ($siteUrl === '') {
            throw new InvalidArgumentException(
                'Search Console property is required.'
            );
        }

        if (!$this->validUrl($sitemapUrl)) {
            throw new InvalidArgumentException(
                'Canonical sitemap URL is invalid.'
            );
        }

        $local =
            $this->localStatus(
                $sitemapUrl
            );

        $remote =
            $this->searchConsole
                ->listSitemaps(
                    $siteUrl
                );

        $submitted =
            $this->submittedStatus(
                $remote,
                $sitemapUrl
            );

        return [
            'ok' => true,

            'sitemap' => [
                'url' =>
                    $sitemapUrl,

                'reachable' =>
                    $local['reachable'],

                'http_status' =>
                    $local['http_status'],

                'content_type' =>
                    $local['content_type'],

                'consistent' =>
                    $local['consistent'],

                'url_count' =>
                    $local['url_count'],

                'hash_sections' =>
                    $local['hash_sections'],
            ],

            'search_console' => [
                'property' =>
                    $siteUrl,

                'submitted' =>
                    $submitted !== null,

                'submission' =>
                    $submitted,
            ],
        ];
    }

    /**
     * @return array{
     *   reachable:bool,
     *   http_status:int,
     *   content_type:string,
     *   consistent:bool,
     *   url_count:int,
     *   hash_sections:int
     * }
     */
    private function localStatus(
        string $sitemapUrl
    ): array {
        try {
            $response =
                $this->httpClient
                    ->request(
                        'GET',
                        $sitemapUrl,
                        [
                            'http_errors' =>
                                false,

                            'timeout' =>
                                10.0,

                            'headers' => [
                                'Accept' =>
                                    'application/xml',
                            ],
                        ]
                    );
        } catch (Throwable $e) {
            throw new \RuntimeException(
                'Canonical sitemap is unavailable.',
                0,
                $e
            );
        }

        $status =
            $response->getStatusCode();

        $type =
            trim(
                $response
                    ->getHeaderLine(
                        'Content-Type'
                    )
            );

        $consistent =
            strtoupper(
                trim(
                    $response
                        ->getHeaderLine(
                            'X-Goosialize-SEO-Sitemap-Consistent'
                        )
                )
            ) === 'PASS';

        $urlCount =
            $this->nonNegativeInt(
                $response
                    ->getHeaderLine(
                        'X-Goosialize-SEO-Sitemap-URLs'
                    )
            );

        $hashSections =
            $this->nonNegativeInt(
                $response
                    ->getHeaderLine(
                        'X-Goosialize-SEO-Hash-Sections'
                    )
            );

        $reachable =
            $status >= 200
            && $status < 300
            && str_starts_with(
                strtolower($type),
                'application/xml'
            );

        return [
            'reachable' =>
                $reachable,

            'http_status' =>
                $status,

            'content_type' =>
                $type,

            'consistent' =>
                $reachable
                && $consistent,

            'url_count' =>
                $urlCount,

            'hash_sections' =>
                $hashSections,
        ];
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>|null
     */
    private function submittedStatus(
        array $payload,
        string $sitemapUrl
    ): ?array {
        $items =
            $payload['sitemap']
            ?? [];

        if (!is_array($items)) {
            return null;
        }

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $path =
                $item['path']
                ?? null;

            if (
                !is_string($path)
                || trim($path)
                    !== $sitemapUrl
            ) {
                continue;
            }

            return [
                'path' =>
                    trim($path),

                'last_submitted' =>
                    $this->nullableString(
                        $item['lastSubmitted']
                        ?? null
                    ),

                'last_downloaded' =>
                    $this->nullableString(
                        $item['lastDownloaded']
                        ?? null
                    ),

                'pending' =>
                    (bool) (
                        $item['isPending']
                        ?? false
                    ),

                'sitemaps_index' =>
                    (bool) (
                        $item['isSitemapsIndex']
                        ?? false
                    ),

                'type' =>
                    $this->nullableString(
                        $item['type']
                        ?? null
                    ),

                'warnings' =>
                    $this->nonNegativeInt(
                        $item['warnings']
                        ?? 0
                    ),

                'errors' =>
                    $this->nonNegativeInt(
                        $item['errors']
                        ?? 0
                    ),
            ];
        }

        return null;
    }

    private function validUrl(
        string $url
    ): bool {
        if (
            filter_var(
                $url,
                FILTER_VALIDATE_URL
            ) === false
        ) {
            return false;
        }

        $scheme =
            strtolower(
                (string) parse_url(
                    $url,
                    PHP_URL_SCHEME
                )
            );

        return in_array(
            $scheme,
            ['http', 'https'],
            true
        );
    }

    private function nonNegativeInt(
        mixed $value
    ): int {
        if (
            is_int($value)
            && $value >= 0
        ) {
            return $value;
        }

        if (
            is_string($value)
            && ctype_digit(
                trim($value)
            )
        ) {
            return (int) trim($value);
        }

        if (
            is_float($value)
            && $value >= 0
        ) {
            return (int) $value;
        }

        return 0;
    }

    private function nullableString(
        mixed $value
    ): ?string {
        if (!is_scalar($value)) {
            return null;
        }

        $value =
            trim(
                (string) $value
            );

        return $value === ''
            ? null
            : $value;
    }
}
