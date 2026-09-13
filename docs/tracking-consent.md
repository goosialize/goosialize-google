# GA4 tracking and consent

## Enable tracking

Configure:

    tracking:
      enabled: true
      measurement_id: 'G-XXXXXXXXXX'

Recommended baseline:

    tracking:
      enabled: true
      measurement_id: 'G-XXXXXXXXXX'
      send_page_view: true
      allow_google_signals: false
      allow_ad_personalization_signals: false
      debug_mode: false

## Advanced controls

Supported options include:

- automatic page views
- Google Signals
- ad personalization signals
- debug mode
- cookie domain
- cookie prefix
- cookie expiry
- linker domains
- user properties
- event parameters

## Avoid duplicate tracking

Before enabling injection, verify that the same Measurement ID is not already loaded through:

- the theme
- another plugin
- hard-coded gtag.js
- Google Tag Manager
- another analytics integration

## Goosialize Cookies

For privacy-aware consent gating, install and enable Goosialize Cookies.

Keep:

    consent:
      consumer_enabled: true

The Google tag should load only when analytics consent permits it.

## Production acceptance

Verify:

1. public site loads normally;
2. before analytics consent, GA4 analytics traffic is not sent;
3. after analytics consent, the configured tag loads;
4. Tag Assistant sees the correct Measurement ID;
5. the tag is not loaded twice.
