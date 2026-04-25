# WP Starter Plugin

A clean, opinionated boilerplate for building custom WordPress plugins.

## Requirements

| Dependency | Minimum |
|------------|---------|
| PHP        | 8.1     |
| WordPress  | 6.0     |

## Directory structure

```
wp-starter-plugin/
├── wp-starter-plugin.php   # Entry point, constants, autoloader, bootstrap
├── uninstall.php           # Full cleanup on plugin deletion
├── includes/
│   ├── class-plugin.php    # Singleton core class, wires all hooks
│   ├── class-loader.php    # Queues & registers actions/filters
│   ├── class-activator.php # DB table creation, default options, cron
│   ├── class-deactivator.php
│   └── class-i18n.php
├── admin/
│   ├── class-admin.php     # Admin menus, enqueues, settings save
│   ├── css/admin.css
│   ├── js/admin.js
│   └── partials/
│       ├── dashboard.php
│       └── settings.php
├── public/
│   ├── class-frontend.php  # Front-end enqueues, wp_localize_script
│   ├── css/public.css
│   ├── js/public.js        # wpspFetch() REST helper
│   └── partials/
└── api/
    └── class-rest-api.php  # Full CRUD REST endpoints at wpsp/v1/items
```

## Autoloader

PSR-4 — maps `WPStarterPlugin\` to the plugin root.  
Namespace → folder → `class-{kebab-case}.php`

| Namespace segment | Folder      |
|-------------------|-------------|
| `Includes`        | `includes/` |
| `Admin`           | `admin/`    |
| `PublicFacing`    | `public/`   |
| `Api`             | `api/`      |

## REST API

Base URL: `{home_url}/wp-json/wpsp/v1/`

| Method | Route        | Auth              | Description  |
|--------|--------------|-------------------|--------------|
| GET    | /items       | logged-in user    | List items   |
| POST   | /items       | `edit_posts` cap  | Create item  |
| GET    | /items/{id}  | logged-in user    | Get item     |
| PUT    | /items/{id}  | `edit_posts` cap  | Update item  |
| DELETE | /items/{id}  | `edit_posts` cap  | Delete item  |

Front-end usage (the bundled `wpspFetch` helper):

```js
const res  = await wpspFetch( 'items', 'POST', { title: 'Hello', content: 'World' } );
const item = await res.json();
```

## Renaming the plugin

1. Rename the root folder and main `.php` file.
2. Replace every occurrence of:
   - `wp-starter-plugin` (text-domain / slug)
   - `WPSP_` / `wpsp` (constants / function/option prefixes)
   - `WPStarterPlugin` (PHP namespace)
3. Update the plugin header in the main file.
