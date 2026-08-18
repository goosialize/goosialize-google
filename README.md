# Goosialize Google — Tools & Integrations

Google tools and integrations for Grav CMS 2.

## Current scope

Version 0.2.1 includes:

- Google Analytics 4 reporting
- Google Search Console performance reporting

Potential future integrations may include:

- Google Business Profile
- Google Maps / Places
- PageSpeed
- Google Ads
- YouTube Analytics

## Architecture

The plugin separates reusable Google infrastructure from product-specific modules.

Current modules:

- Google Analytics
- Google Search Console

Google APIs remain authoritative for Google-owned data.

Plugin-local persistence will use SQLite under:

`user/data/goosialize-google/`

## Status

Active development.

Google Analytics and Google Search Console are implemented and available through the Admin2 dashboard.

## Installation

Official release ZIPs include production dependencies.

Install under:

`user/plugins/goosialize-google/`

SQLite storage is automatically created at:

`user/data/goosialize-google/google.sqlite`

Google credentials must remain outside the repository and release package.

See `docs/installation.md` for complete setup instructions.

## Google Search Console

Goosialize Google 0.2.0 adds Google Search Console performance reporting to the Admin2 dashboard.

Supported reporting includes clicks, impressions, click-through rate, average position, top queries, top pages, devices, countries, and 7, 30 and 90 day reporting periods.

Both URL-prefix properties and Domain properties are supported. Domain properties use the format `sc-domain:example.com`.

The Search Console API must be enabled in the Google Cloud project used by the configured service account. The service account must also have access to the required Search Console properties.

Google credentials must remain outside the repository and release package.
