# Goosialize Google

Google product integrations for Grav CMS 2.

## Current scope

Version 1 focuses exclusively on Google Analytics 4 reporting.

Planned future modules may include:

- Google Search Console
- Google Business Profile
- Google Maps / Places
- PageSpeed
- Google Ads

These future modules are not implemented in the initial release.

## Architecture

The plugin separates reusable Google infrastructure from product-specific modules.

Initial module:

- Analytics

Google APIs remain authoritative for Google-owned data.

Plugin-local persistence will use SQLite under:

`user/data/goosialize-google/`

## Status

Early development.
