# Goosialize Google — Tools & Integrations

Google Analytics 4 and Google Search Console tools and integrations for Grav CMS 2.

## Current release

Version 0.3.1 provides production-ready:

- Google Analytics 4 reporting
- Google Search Console reporting
- Search Console sitemap status
- GA4 tracking injection
- Goosialize Cookies consent integration
- strict per-site GA4 and Search Console property isolation

## Google Analytics 4

Supported reporting includes:

- overview metrics
- trends
- top pages
- traffic channels
- devices
- countries
- operating systems
- cities
- busy days and hours
- events
- demographics where available
- 7, 30 and 90 day periods

## Google Search Console

Supported reporting includes:

- clicks
- impressions
- CTR
- average position
- top queries
- top pages
- devices
- countries
- sitemap status

Both URL-prefix and Domain properties are supported.

Example Domain property:

    sc-domain:example.com

## Strict site property scope

For production installations, configure one GA4 property and one Search Console property.

    analytics:
      default_property: '123456789'

    search_console:
      default_property: 'sc-domain:example.com'

When configured, these values become security/isolation boundaries for that Grav installation.

The Admin2 property selectors expose only the configured properties and reporting requests for other properties are rejected.

If no default property is configured, multi-property discovery remains available.

## Requirements

- Grav CMS 2
- Admin2
- Grav API plugin
- PHP 8.1+
- BCMath
- PDO SQLite
- cURL
- OpenSSL
- JSON

Official release ZIPs include production Composer dependencies.

## Installation

Install under:

    user/plugins/goosialize-google/

Persistent data is stored under:

    user/data/goosialize-google/

See [Installation](docs/installation.md).

## Google credentials

Use a Google Cloud service account.

Store its JSON credential outside Git and preferably outside the public webroot.

Example:

    /home/example/var/goosialize-google/service-account.json

Configure only the absolute path:

    authentication:
      service_account_file: '/home/example/var/goosialize-google/service-account.json'

See [Google Cloud setup](docs/google-cloud.md).

## GA4 tracking

Example:

    tracking:
      enabled: true
      measurement_id: 'G-XXXXXXXXXX'
      send_page_view: true
      allow_google_signals: false
      allow_ad_personalization_signals: false
      debug_mode: false

Before enabling tracking, verify that the same Measurement ID is not already loaded by the theme, another plugin or Google Tag Manager.

## Consent

For privacy-aware GA4 loading, install Goosialize Cookies and keep:

    consent:
      consumer_enabled: true

See [Tracking and consent](docs/tracking-consent.md).

## Documentation

- [Installation](docs/installation.md)
- [Google Cloud setup](docs/google-cloud.md)
- [Google Analytics 4](docs/analytics.md)
- [Google Search Console](docs/search-console.md)
- [Tracking and consent](docs/tracking-consent.md)
- [Security and privacy](docs/security-privacy.md)
- [Upgrade guide](docs/upgrade.md)
- [Troubleshooting](docs/troubleshooting.md)

## Security

- Service-account credentials are never bundled in release packages.
- Google reporting uses read-only access.
- API routes require Grav authorization.
- Configured properties act as site isolation boundaries.
- Tracking can be consent-gated.

## License

MIT. See [LICENSE](LICENSE).
