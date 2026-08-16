# Goosialize Google — Installation

Requirements:

- Grav 2
- Admin2
- API plugin
- PHP 8.1+
- PDO SQLite
- cURL
- OpenSSL
- JSON

Install the release under:

user/plugins/goosialize-google/

Official release ZIPs include production Composer dependencies.

Persistent storage is created automatically at:

user/data/goosialize-google/google.sqlite

Storage is initialized by the web/API runtime, not CLI, to avoid SQLite
ownership conflicts.

Google service-account credentials must stay outside Git, SQLite and the
release ZIP.

Configure only the absolute credential path:

authentication:
  service_account_file: '/absolute/path/service-account.json'

Required Google APIs:

- Google Analytics Data API
- Google Analytics Admin API

Admin2 permission:

api.goosialize_google.analytics.read

The plugin does not require api.gpm.read.

Developers working from source must run:

composer install --no-dev --prefer-dist --classmap-authoritative

If PHP classes are added later, rebuild the authoritative autoloader:

composer dump-autoload --no-dev --classmap-authoritative
