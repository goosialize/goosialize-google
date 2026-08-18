# Changelog

## [Unreleased]

## [0.2.1] - 2026-08-18

### Changed

- Updated the product identity to **Goosialize Google — Tools & Integrations**.
- Kept the compact **Goosialize Google** label in Admin2 navigation.
- Updated English and Greek plugin identity labels.
- Updated README and installation documentation to reflect the current Google Analytics and Search Console modules.
- Clarified potential future integrations including Business Profile, Maps / Places, PageSpeed, Google Ads and YouTube Analytics.


## [0.2.0] - 2026-08-18

### Added

- Google Search Console integration alongside Google Analytics 4.
- Search Console property discovery for URL-prefix and `sc-domain:` properties.
- Search performance reporting for clicks, impressions, CTR and average position.
- Top queries, top pages, devices and countries reporting.
- Search Console Admin2 routes, configuration and dedicated read permission.
- Google Analytics / Search Console product switching.
- Shared 7, 30 and 90 day reporting periods.
- Component-owned Refresh behavior.
- Indeterminate loading progress indicator.
- English and Greek Search Console configuration labels.

### Changed

- Renamed the Admin2 page to Goosialize Google.
- Aligned dashboard UI with the supported Admin2 custom-page integration model.
- Removed inline visual styling in favor of Admin2 utility classes and tokens.
- Improved toolbar composition, typography, spacing and control alignment.
- Stabilized the Google HTTP dependency stack on Guzzle 7 and PSR-7 2.
- Extended service-account authentication to support product-specific OAuth scopes.

### Fixed

- Fixed dashboard Refresh behavior.
- Fixed Refresh alignment.
- Fixed Google HTTP runtime compatibility issues.
- Replaced the legacy loading panel with a compact progress indicator.

### Notes

- Search Console API must be enabled in the Google Cloud project.
- The configured service account must have access to the required Search Console properties.
- Loading indicator visual polish is deferred to a future version.

## [0.1.0]

### Added

- Initial Grav plugin foundation.
- Google Analytics 4 reporting foundation.
- Shared Google authentication infrastructure.
- Analytics property discovery and reporting dashboard.
- SQLite persistence foundation.
- Admin2 integration.
- English and Greek translation foundations.
