# Installation

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

## Install

Extract the plugin to:

    user/plugins/goosialize-google/

The blueprint must exist at:

    user/plugins/goosialize-google/blueprints.yaml

Enable the plugin through Admin2.

## Persistent storage

Persistent data is created under:

    user/data/goosialize-google/

Default database:

    user/data/goosialize-google/google.sqlite

Do not store persistent data inside the plugin directory.

## Credential

Store the Google service-account JSON outside Git and preferably outside the webroot.

Example:

    /home/example/var/goosialize-google/service-account.json

Recommended permissions:

- credential directory: 700
- credential JSON: 600

Configure only the absolute path:

    authentication:
      service_account_file: '/home/example/var/goosialize-google/service-account.json'

Never paste the private key into Grav configuration.

## Required Google APIs

Enable:

- Google Analytics Data API
- Google Analytics Admin API
- Google Search Console API

## GA4 property

Give the service-account email Viewer access to the required GA4 property.

Configure:

    analytics:
      enabled: true
      default_property: '123456789'

The default property becomes the strict GA4 site scope.

## Search Console property

Add the service-account email to the required Search Console property.

Domain example:

    search_console:
      enabled: true
      default_property: 'sc-domain:example.com'

URL-prefix example:

    search_console:
      enabled: true
      default_property: 'https://www.example.com/'

The default property becomes the strict Search Console site scope.

## Tracking

Example:

    tracking:
      enabled: true
      measurement_id: 'G-XXXXXXXXXX'
      send_page_view: true
      allow_google_signals: false
      allow_ad_personalization_signals: false
      debug_mode: false

## Consent

For consent-gated tracking, install Goosialize Cookies and keep:

    consent:
      consumer_enabled: true

See tracking-consent.md.

## Permissions

Analytics:

    api.goosialize_google.analytics.read

Search Console:

    api.goosialize_google.search_console.read
