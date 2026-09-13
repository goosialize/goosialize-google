# Google Cloud setup

## Service account

Create or use a Google Cloud service account for the integration.

Download its JSON key and store it securely outside the public webroot.

Goosialize Google stores only the absolute filesystem path to that JSON file.

## Required APIs

Enable:

- Google Analytics Data API
- Google Analytics Admin API
- Google Search Console API

## Google Analytics access

The service account must be explicitly added to Google Analytics.

In Google Analytics:

1. Open Admin.
2. Open account or property access management.
3. Add the service-account email.
4. Viewer access is sufficient for reporting and discovery.

## Search Console access

In Search Console:

1. Open the required property.
2. Open Settings.
3. Open Users and permissions.
4. Add the service-account email.

Supported properties include:

    sc-domain:example.com

and:

    https://www.example.com/

## Least privilege

Use the smallest access level required.

Goosialize Google reporting is designed around read-only Google API access.
