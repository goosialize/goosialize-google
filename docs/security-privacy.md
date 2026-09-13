# Security and privacy

## Credentials

The plugin stores only a filesystem reference to the service-account JSON.

Credentials should:

- remain outside Git;
- remain outside release ZIPs;
- preferably remain outside the webroot;
- use restrictive filesystem permissions.

## Google API access

Reporting integrations use read-only access.

Search Console sitemap integration reads status only and does not submit or delete sitemaps.

## Site isolation

A service account may have access to multiple client properties.

For production sites configure:

    analytics:
      default_property: '123456789'

    search_console:
      default_property: 'sc-domain:example.com'

These values become isolation boundaries for the installation.

Out-of-scope property requests are rejected.

## API authorization

Protected routes require Grav authentication and explicit API permissions.

## Release packages

Official release artifacts contain:

- production Composer dependencies;
- no service-account JSON;
- no private key;
- no development test suite.
