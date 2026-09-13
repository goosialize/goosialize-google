# Google Analytics 4

## Property discovery

Goosialize Google uses the Google Analytics Admin API to discover properties available to the configured service account.

## Strict site scope

Configure:

    analytics:
      default_property: '123456789'

When present, this property becomes the strict GA4 scope for the installation.

The property selector exposes only that property.

Requests for another GA4 property are rejected.

If no default property is configured, multi-property discovery remains available.

## Reporting

The Admin2 dashboard supports:

- overview
- trend
- top pages
- traffic channels
- devices
- countries
- operating systems
- cities
- busy days
- busy hours
- events
- demographics where available

## Periods

Supported periods include:

- 7 days
- 30 days
- 90 days

## New properties

A newly created GA4 property can legitimately show "No data".

Verify the property ID, Measurement ID, tracking and consent before treating an empty dashboard as an error.
