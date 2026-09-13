# Upgrade guide

## Persistent resources

Plugin code:

    user/plugins/goosialize-google/

Configuration:

    user/config/plugins/goosialize-google.yaml

Persistent data:

    user/data/goosialize-google/

Service-account credentials should also live outside the plugin directory.

## Recommended upgrade

1. Back up the current plugin directory.
2. Back up plugin configuration.
3. Record the credential path and checksum.
4. Validate the release ZIP/version.
5. Replace only the plugin code directory.
6. Clear Grav cache.
7. Verify Admin and public health.
8. Confirm config and credential are unchanged.
9. Verify GA4 and Search Console site scope.
10. Verify tracking/consent if enabled.

## Rollback

Restore the previous plugin code directory while preserving external configuration, data and credential files.
