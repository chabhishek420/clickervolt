# ClickerVolt (Modernized 2026+)

ClickerVolt is a WordPress plugin for affiliate click tracking, redirect routing, and campaign reporting.

## Requirements

- WordPress: `6.5+`
- PHP: `8.2+`
- Tested up to: WordPress `6.7`

## Installation

1. Place the plugin in `wp-content/plugins/clickervolt`.
2. Ensure dependencies are present:
   - `composer install`
3. Activate **ClickerVolt** from WordPress admin.

## Modernized Architecture

```text
clickervolt/
├── clickervolt.php        # Main plugin bootstrap + metadata
├── src/
│   ├── Core/
│   │   ├── Plugin.php     # Runtime bootstrap and hook registration
│   │   └── Compatibility.php
│   └── Admin/
│       └── HealthPage.php
├── admin/                 # Legacy admin module layer
├── db/                    # Data/schema layer
├── redirect/              # Redirect runtime paths
├── utils/                 # Shared utilities
├── vendor/                # Composer autoload runtime
└── readme.txt             # WordPress.org style readme
```

## Compatibility & Health

- Runtime guard checks PHP and WordPress minimum versions.
- Plugin deactivates itself with an admin notice on unsupported environments.
- A **Health Check** admin page exposes runtime info (PHP/WP version, Redis extension status, object cache status).

## Schema Lifecycle

- Schema updates run through `DB::setupTables()`.
- Table create/update SQL now routes through `dbDelta()` for CREATE TABLE statements with WordPress charset/collation handling.

## Changelog

### 1.146 (2026 modernization)
- Added Composer support and autoloading.
- Introduced modern core bootstrap in `src/Core`.
- Added compatibility guards and health-check page.
- Hardened clicklog escaping and session behavior.
- Updated schema execution path for modern WordPress/PHP expectations.
