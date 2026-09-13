# Google Search Console

## Supported property types

Goosialize Google supports:

- Domain properties
- URL-prefix properties

Examples:

    sc-domain:example.com

    https://www.example.com/

## Strict site scope

Configure:

    search_console:
      enabled: true
      default_property: 'sc-domain:example.com'

The configured property becomes the strict Search Console scope.

Property discovery exposes only that property and reporting/sitemap requests for other properties are rejected.

## Reporting

Supported data includes:

- clicks
- impressions
- CTR
- average position
- queries
- pages
- devices
- countries

## Sitemap status

Goosialize Google can read sitemap status from Search Console.

The integration does not submit or delete sitemaps.

The canonical sitemap URL is resolved from site configuration when available, otherwise from the current host and `/sitemap.xml`.

## New properties

New Search Console properties can legitimately return no performance data while Google collects and processes information.
