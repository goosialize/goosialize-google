<?php

declare(strict_types=1);

namespace Goosialize\Google\Admin;

use DateTimeImmutable;
use Grav\Common\Config\Config;
use Grav\Common\Grav;
use Grav\Framework\Psr7\Response;
use Goosialize\Google\Core\Auth\CredentialReference;
use Goosialize\Google\Core\Auth\CredentialType;
use Goosialize\Google\SearchConsole\Connection\GoogleSearchConsoleClientFactory;
use Goosialize\Google\SearchConsole\Connection\SearchConsoleCredentialProviderFactory;
use Goosialize\Google\SearchConsole\Dashboard\SearchConsoleDashboardPeriod;
use Goosialize\Google\SearchConsole\Dashboard\SearchConsoleDashboardService;
use Goosialize\Google\SearchConsole\Dashboard\SearchConsolePropertyDirectory;
use Goosialize\Google\SearchConsole\Reporting\SearchConsoleReportingService;
use Goosialize\Google\SearchConsole\Reporting\SearchConsoleResponseNormalizer;
use Goosialize\Google\SearchConsole\Sitemap\CanonicalSitemapStatusService;
use GuzzleHttp\Client;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Throwable;

final class SearchConsoleDashboardController
{
    private const PERMISSION =
        'api.goosialize_google.search_console.read';

    private readonly Config $config;

    public function __construct(
        Grav $grav
    ) {
        $config =
            $grav['config']
            ?? null;

        if (!$config instanceof Config) {
            throw new RuntimeException(
                'Grav configuration service is unavailable.'
            );
        }

        $this->config =
            $config;
    }

    public function properties(
        ServerRequestInterface $request
    ): ResponseInterface {
        $user =
            $request->getAttribute(
                'api_user'
            );

        if (!is_object($user)) {
            return $this->failure(
                401,
                'authentication_required',
                'Authentication required.'
            );
        }

        if (!$this->authorized($request, $user)) {
            return $this->failure(
                403,
                'forbidden',
                'Google Search Console access is forbidden.'
            );
        }

        if (!$this->enabled()) {
            return $this->failure(
                404,
                'search_console_disabled',
                'Google Search Console is disabled.'
            );
        }

        try {
            $payload =
                (
                    new SearchConsolePropertyDirectory(
                        $this->reporting()
                    )
                )->payload();

            $scopedProperty =
                $this->configuredPropertyScope();

            if ($scopedProperty !== null) {
                $payload['data'] =
                    array_values(
                        array_filter(
                            $payload['data'],
                            static fn (array $property): bool =>
                                ($property['site_url'] ?? null)
                                === $scopedProperty
                        )
                    );
            }

            return $this->response(
                200,
                $payload
            );
        } catch (Throwable $e) {
            return $this->mappedFailure(
                $e,
                'properties_unavailable'
            );
        }
    }

    public function sitemap(
        ServerRequestInterface $request
    ): ResponseInterface {
        $user =
            $request->getAttribute(
                'api_user'
            );

        if (!is_object($user)) {
            return $this->failure(
                401,
                'authentication_required',
                'Authentication required.'
            );
        }

        if (!$this->authorized($request, $user)) {
            return $this->failure(
                403,
                'forbidden',
                'Google Search Console access is forbidden.'
            );
        }

        if (!$this->enabled()) {
            return $this->failure(
                404,
                'search_console_disabled',
                'Google Search Console is disabled.'
            );
        }

        $query =
            $request->getQueryParams();

        $siteUrl =
            $query['site_url']
            ?? $this->config->get(
                'plugins.goosialize-google.search_console.default_property'
            );

        if (
            !is_string($siteUrl)
            || trim($siteUrl) === ''
        ) {
            return $this->failure(
                400,
                'property_required',
                'A Search Console property is required.'
            );
        }

        $siteUrl =
            trim($siteUrl);

        $scopedProperty =
            $this->configuredPropertyScope();

        if (
            $scopedProperty !== null
            && $siteUrl !== $scopedProperty
        ) {
            return $this->failure(
                403,
                'property_scope_forbidden',
                'Requested Search Console property is outside this site scope.'
            );
        }

        try {
            $client =
                (
                    new GoogleSearchConsoleClientFactory(
                        (
                            new SearchConsoleCredentialProviderFactory()
                        )->create()
                    )
                )->create(
                    $this->credentialReference()
                );

            $service =
                new CanonicalSitemapStatusService(
                    $client,
                    new Client([
                        'timeout' => 10.0,
                        'http_errors' => false,
                    ])
                );

            return $this->response(
                200,
                $service->status(
                    $siteUrl,
                    $this->canonicalSitemapUrl(
                        $request
                    )
                )
            );
        } catch (InvalidArgumentException $e) {
            return $this->failure(
                400,
                'request_invalid',
                'Search Console sitemap request is invalid.'
            );
        } catch (Throwable $e) {
            return $this->mappedFailure(
                $e,
                'sitemap_unavailable'
            );
        }
    }

    public function performance(
        ServerRequestInterface $request
    ): ResponseInterface {
        $user =
            $request->getAttribute(
                'api_user'
            );

        if (!is_object($user)) {
            return $this->failure(
                401,
                'authentication_required',
                'Authentication required.'
            );
        }

        if (!$this->authorized($request, $user)) {
            return $this->failure(
                403,
                'forbidden',
                'Google Search Console access is forbidden.'
            );
        }

        if (!$this->enabled()) {
            return $this->failure(
                404,
                'search_console_disabled',
                'Google Search Console is disabled.'
            );
        }

        $query =
            $request->getQueryParams();

        $siteUrl =
            $query['site_url']
            ?? $this->config->get(
                'plugins.goosialize-google.search_console.default_property'
            );

        if (
            !is_string($siteUrl)
            || trim($siteUrl) === ''
        ) {
            return $this->failure(
                400,
                'property_required',
                'A Search Console property is required.'
            );
        }

        $siteUrl =
            trim($siteUrl);

        $scopedProperty =
            $this->configuredPropertyScope();

        if (
            $scopedProperty !== null
            && $siteUrl !== $scopedProperty
        ) {
            return $this->failure(
                403,
                'property_scope_forbidden',
                'Requested Search Console property is outside this site scope.'
            );
        }

        $package =
            $query['package']
            ?? null;

        if (
            $package !== null
            && !is_string($package)
        ) {
            return $this->failure(
                400,
                'package_invalid',
                'Search Console package is invalid.'
            );
        }

        $daysRaw =
            $query['days']
            ?? 30;

        if (
            is_string($daysRaw)
            && ctype_digit($daysRaw)
        ) {
            $daysRaw =
                (int) $daysRaw;
        }

        if (!is_int($daysRaw)) {
            return $this->failure(
                400,
                'period_invalid',
                'Search Console period is invalid.'
            );
        }

        try {
            $period =
                SearchConsoleDashboardPeriod::fromDays(
                    $daysRaw
                );

            $dashboard =
                new SearchConsoleDashboardService(
                    $this->reporting()
                );

            $payload =
                is_string($package)
                && trim($package) !== ''
                    ? $dashboard->package(
                        $siteUrl,
                        $period,
                        new DateTimeImmutable('today'),
                        trim($package)
                    )
                    : $dashboard->dashboard(
                        $siteUrl,
                        $period,
                        new DateTimeImmutable('today')
                    );

            return $this->response(
                200,
                $payload
            );
        } catch (InvalidArgumentException $e) {
            return $this->failure(
                400,
                'request_invalid',
                'Search Console request is invalid.'
            );
        } catch (Throwable $e) {
            return $this->mappedFailure(
                $e,
                'search_console_unavailable'
            );
        }
    }

    private function configuredPropertyScope(): ?string
    {
        $configured =
            $this->config->get(
                'plugins.goosialize-google.search_console.default_property'
            );

        if (!is_string($configured)) {
            return null;
        }

        $configured =
            trim($configured);

        return $configured !== ''
            ? $configured
            : null;
    }

    private function canonicalSitemapUrl(
        ServerRequestInterface $request
    ): string {
        $route =
            $this->config->get(
                'plugins.goosialize-seo.sitemap.route',
                '/sitemap.xml'
            );

        if (
            !is_string($route)
            || trim($route) === ''
        ) {
            $route =
                '/sitemap.xml';
        }

        $route =
            '/'
            . trim(
                $route,
                '/'
            );

        $configuredBase =
            $this->config->get(
                'plugins.goosialize-seo.canonical.base_url',
                ''
            );

        if (
            is_string($configuredBase)
            && trim($configuredBase) !== ''
        ) {
            return rtrim(
                trim($configuredBase),
                '/'
            )
            . $route;
        }

        $uri =
            $request->getUri();

        $host =
            trim(
                $request
                    ->getHeaderLine(
                        'Host'
                    )
            );

        if ($host === '') {
            $host =
                trim(
                    $uri->getAuthority()
                );
        }

        if ($host === '') {
            throw new RuntimeException(
                'Canonical sitemap host is unavailable.'
            );
        }

        $forwardedProto =
            strtolower(
                trim(
                    $request
                        ->getHeaderLine(
                            'X-Forwarded-Proto'
                        )
                )
            );

        if (
            str_contains(
                $forwardedProto,
                ','
            )
        ) {
            $forwardedProto =
                trim(
                    explode(
                        ',',
                        $forwardedProto,
                        2
                    )[0]
                );
        }

        $scheme =
            in_array(
                $forwardedProto,
                ['http', 'https'],
                true
            )
                ? $forwardedProto
                : strtolower(
                    trim(
                        $uri->getScheme()
                    )
                );

        if (
            $scheme !== 'http'
            && $scheme !== 'https'
        ) {
            $scheme =
                'https';
        }

        return $scheme
            . '://'
            . $host
            . $route;
    }

    private function reporting():
        SearchConsoleReportingService
    {
        $provider =
            (
                new SearchConsoleCredentialProviderFactory()
            )->create();

        $client =
            (
                new GoogleSearchConsoleClientFactory(
                    $provider
                )
            )->create(
                $this->credentialReference()
            );

        return new SearchConsoleReportingService(
            $client,
            new SearchConsoleResponseNormalizer()
        );
    }

    private function credentialReference():
        CredentialReference
    {
        $path =
            $this->config->get(
                'plugins.goosialize-google.authentication.service_account_file'
            );

        if (
            !is_string($path)
            || trim($path) === ''
        ) {
            throw new RuntimeException(
                'Google credential path is not configured.'
            );
        }

        return new CredentialReference(
            CredentialType::SERVICE_ACCOUNT_FILE,
            trim($path)
        );
    }

    private function enabled(): bool
    {
        return (bool) $this->config->get(
            'plugins.goosialize-google.search_console.enabled',
            true
        );
    }

    private function authorized(
        ServerRequestInterface $request,
        object $user
    ): bool {
        try {
            $scopes =
                $request->getAttribute(
                    'api_key_scopes'
                );

            if (
                is_array($scopes)
                && $scopes !== []
                && !$this->scopesPermit(
                    $scopes,
                    self::PERMISSION
                )
            ) {
                return false;
            }

            if (
                $this->userAuthorized(
                    $user,
                    'api.super'
                )
            ) {
                return true;
            }

            if (
                !$this->userAuthorized(
                    $user,
                    'api.access'
                )
            ) {
                return false;
            }

            return $this->userAuthorized(
                $user,
                self::PERMISSION
            );
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @param array<int,mixed> $scopes
     */
    private function scopesPermit(
        array $scopes,
        string $permission
    ): bool {
        foreach ($scopes as $scope) {
            if (
                !is_string($scope)
                || $scope === ''
            ) {
                continue;
            }

            if (
                $scope === '*'
                || $scope === $permission
                || str_starts_with(
                    $permission,
                    $scope . '.'
                )
            ) {
                return true;
            }
        }

        return false;
    }

    private function userAuthorized(
        object $user,
        string $permission
    ): bool {
        if (
            method_exists($user, 'get')
            && $user->get(
                'access.' . $permission
            ) === true
        ) {
            return true;
        }

        return method_exists(
            $user,
            'authorize'
        )
            && $user->authorize(
                $permission
            ) === true;
    }

    private function mappedFailure(
        Throwable $error,
        string $fallbackCode
    ): ResponseInterface {
        $message =
            $error->getMessage();

        if (
            str_contains(
                strtolower($message),
                'credential'
            )
        ) {
            return $this->failure(
                503,
                'credentials_unavailable',
                'Google Search Console credentials are unavailable.'
            );
        }

        if (
            str_contains(
                $message,
                'not accessible'
            )
        ) {
            return $this->failure(
                403,
                'property_forbidden',
                'The selected Search Console property is not accessible.'
            );
        }

        return $this->failure(
            503,
            $fallbackCode,
            'Google Search Console data is currently unavailable.'
        );
    }

    private function failure(
        int $status,
        string $code,
        string $message
    ): ResponseInterface {
        return $this->response(
            $status,
            [
                'ok' => false,
                'code' => $code,
                'message' => $message,
            ]
        );
    }

    /**
     * @param array<string,mixed> $body
     */
    private function response(
        int $status,
        array $body
    ): ResponseInterface {
        return new Response(
            $status,
            [
                'Content-Type' =>
                    'application/json; charset=utf-8',
                'Cache-Control' =>
                    'no-store',
                'X-Content-Type-Options' =>
                    'nosniff',
            ],
            json_encode(
                $body,
                JSON_THROW_ON_ERROR
                | JSON_UNESCAPED_SLASHES
                | JSON_UNESCAPED_UNICODE
            )
        );
    }
}
