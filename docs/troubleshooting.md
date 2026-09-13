# Troubleshooting

## Credentials unavailable

Verify:

- the configured path is absolute;
- the JSON file exists;
- PHP can read it;
- it contains valid service-account credentials;
- required Google APIs are enabled.

## No accessible GA4 properties

Add the service-account email to the required Google Analytics property/account access management.

## Search Console property missing

Add the service-account email to Search Console Users and permissions.

Ensure the configured property exactly matches Google.

## Wrong client properties appear

Configure both site defaults:

    analytics:
      default_property: '123456789'

    search_console:
      default_property: 'sc-domain:example.com'

Current releases use these defaults as strict site isolation boundaries.

## No data

For new GA4 or Search Console properties this can be normal.

Check:

- selected property;
- Measurement ID;
- tracking enabled state;
- analytics consent;
- processing time.

## Tag Assistant shows no Google tag

Check:

- tracking.enabled is true;
- the Measurement ID is correct;
- Goosialize Cookies is installed when consent gating is enabled;
- analytics consent has been granted;
- CSP or another mechanism is not blocking Google scripts.

## Duplicate analytics

Check for another:

- gtag.js integration;
- Google Tag Manager container;
- theme analytics integration;
- plugin analytics integration.

Avoid multiple owners of the same Measurement ID.

## Sitemap status empty

Verify:

- Search Console access;
- the public sitemap;
- the selected Search Console property;
- whether Google knows the sitemap.

Goosialize Google reads sitemap status; it does not submit the sitemap.
