# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

`local_elby_dashboard` is a Moodle 5.0+ local plugin that provides an analytics dashboard for the Rwanda TVET Board e-learning platform. It uses **Preact** with **TypeScript** for the frontend and **Vite** to compile AMD-compatible JavaScript modules for Moodle's RequireJS system.

## Technical Design Specification (TDS)

The TDS lives in `docs/tds/` (14 files). **Always check the TDS before implementing a feature, and update the relevant TDS sections if any implementation changes the design.** Keep the TDS in sync with the code at all times.

## Development Commands

```bash
# Install dependencies
pnpm install

# Build AMD modules (compiles TypeScript/TSX to amd/build/)
pnpm run build

# Watch mode for development
pnpm run dev
```

After building, clear Moodle's caches:
```bash
docker compose exec php php /var/www/html/moodle_app/admin/cli/purge_caches.php
```

## Architecture

### Frontend Build Pipeline

The plugin uses a non-standard approach for Moodle: **Vite + TypeScript + Preact** instead of Moodle's traditional Grunt + ES6 + AMD workflow.

```
amd/src/*.ts, *.tsx  →  Vite build  →  amd/build/*.js (AMD format)
```

Key configuration in `vite.config.ts`:
- Entry points are TypeScript files in `amd/src/` (excluding subdirectories and helper files)
- Output format is AMD for Moodle's RequireJS loader
- Moodle core modules like `core/ajax` are marked as external
- CSS is processed with Tailwind CSS 4 via PostCSS

### AMD Module Structure

| File | Purpose |
|------|---------|
| `amd/src/dashboard.ts` | Main entry point, exports `init()` function called by Moodle |
| `amd/src/app.tsx` | Root Preact component (not an entry point) |
| `amd/src/types.ts` | TypeScript interfaces (not an entry point) |
| `amd/src/components/` | Preact components (bundled into entry points) |

The `excludeFromEntries` array in `vite.config.ts` controls which files are entry points vs. internal modules.

### PHP/Moodle Structure

| File | Purpose |
|------|---------|
| `version.php` | Plugin metadata (component name: `local_elby_dashboard`) |
| `db/access.php` | Capability definitions (`view`, `viewreports`, `manage`) |
| `db/services.php` | Web service function definitions |
| `db/install.xml` | Database schema (12 SDMS cache tables) |
| `lib.php` | Navigation hooks (`extend_navigation`, `extend_settings_navigation`) |
| `settings.php` | Admin settings page registration |
| `lang/en/local_elby_dashboard.php` | Language strings |
| `templates/root.mustache` | Mustache template with skeleton loading UI |
| `index.php` | Main dashboard page |
| `admin/index.php` | Admin-only dashboard with extended stats |
| `classes/sdms_client.php` | SDMS API HTTP client (no auth, IP whitelist) |
| `classes/sync_service.php` | Cache-first sync orchestrator |
| `classes/external/sdms.php` | SDMS web service endpoints |
| `classes/external/completion.php` | Completion stats web service |
| `classes/external/course_report.php` | Course report web service |

### Data Flow

1. PHP page (`index.php`) prepares user/stats data from Moodle DB
2. Data is JSON-encoded into HTML data attributes on the root element
3. Mustache template renders skeleton UI with data attributes
4. JavaScript module (`dashboard.ts`) reads data attributes and renders Preact app
5. Preact components use Tailwind CSS classes for styling

## Capabilities

- `local/elby_dashboard:view` - View dashboard (students, teachers, managers)
- `local/elby_dashboard:viewreports` - View detailed reports (teachers, managers)
- `local/elby_dashboard:manage` - Manage settings (managers only)

## Adding New Components

1. Create `.tsx` file in `amd/src/components/`
2. Import in entry point or other components
3. Run `pnpm run build`
4. Purge Moodle caches

## Adding New AMD Entry Points

1. Create `.ts` file in `amd/src/` (root level)
2. Ensure file is NOT in `excludeFromEntries` array in `vite.config.ts`
3. Export named functions (Moodle calls them via `js_call_amd`)
4. Run `pnpm run build`

## CSS/Styling

- **Tailwind CSS 4** for component styling (imported in `amd/src/styles.css`)
- **Base styles** in `styles.css` (root level) for skeleton loading animations
- Styles are bundled into `amd/build/dashboard.css` during Vite build
