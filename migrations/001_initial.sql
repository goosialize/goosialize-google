PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS schema_migrations (
    version TEXT PRIMARY KEY NOT NULL,
    applied_at TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS google_connections (
    id TEXT PRIMARY KEY NOT NULL,
    label TEXT NOT NULL,

    credential_type TEXT NOT NULL
        CHECK (
            credential_type IN (
                'service_account_file',
                'oauth'
            )
        ),

    credential_reference TEXT NOT NULL,

    enabled INTEGER NOT NULL DEFAULT 1
        CHECK (enabled IN (0, 1)),

    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS analytics_properties (
    property_id TEXT PRIMARY KEY NOT NULL,

    connection_id TEXT NOT NULL,

    display_name TEXT NOT NULL,

    enabled INTEGER NOT NULL DEFAULT 1
        CHECK (enabled IN (0, 1)),

    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL,

    FOREIGN KEY (connection_id)
        REFERENCES google_connections(id)
        ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS
    idx_analytics_properties_connection
ON analytics_properties(connection_id);

CREATE TABLE IF NOT EXISTS report_cache (
    cache_key TEXT PRIMARY KEY NOT NULL,

    property_id TEXT NOT NULL,

    report_type TEXT NOT NULL,
    request_hash TEXT NOT NULL,

    payload_json TEXT NOT NULL,

    fetched_at TEXT NOT NULL,
    expires_at TEXT NOT NULL,

    FOREIGN KEY (property_id)
        REFERENCES analytics_properties(property_id)
        ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS
    idx_report_cache_property_expiry
ON report_cache(
    property_id,
    expires_at
);

CREATE TABLE IF NOT EXISTS sync_runs (
    id TEXT PRIMARY KEY NOT NULL,

    property_id TEXT NOT NULL,

    operation TEXT NOT NULL,

    status TEXT NOT NULL
        CHECK (
            status IN (
                'running',
                'success',
                'failed'
            )
        ),

    started_at TEXT NOT NULL,
    finished_at TEXT,

    error_code TEXT,
    error_summary TEXT,

    FOREIGN KEY (property_id)
        REFERENCES analytics_properties(property_id)
        ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS
    idx_sync_runs_property_started
ON sync_runs(
    property_id,
    started_at
);
