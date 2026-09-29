# CLAUDE.md — Astara WordPress Theme

Custom WordPress **block theme** for a client, built from Figma designs. This repo contains **only the theme**. WordPress core, plugins, uploads and the database never live here.

## Stack decisions (settled, don't relitigate)

- **Block theme (FSE)**: `theme.json`, HTML templates, template parts, patterns. No classic PHP template hierarchy.
- **Styling**: `theme.json` is the single source of truth for design tokens (color, typography, spacing, layout). Use plain modern CSS for everything else (nesting, `clamp()`, container queries, custom properties). **No Tailwind. No CSS framework.**
- **Per-block CSS**: load via `wp_enqueue_block_style()` so styles only ship where the block is used. Keep one small global stylesheet for what `theme.json` can't express.
- **Interactivity**: use the WordPress **Interactivity API** for menus, tabs, accordions, filters, etc. **No Vue** unless an app-like feature is explicitly approved. If that happens, scope Vue to that one template and build it with Vite.
- **Build tooling**: none by default. Add `@wordpress/scripts` only when custom blocks or Interactivity API stores need it.
- **Business logic** (custom post types, taxonomies, custom blocks that hold content): these do **not** go in the theme. They belong in a separate companion plugin. If you're tempted to `register_post_type()` in `functions.php`, stop and flag it.

## Repo layout

```
/                      ← repo root IS the theme folder (astara/)
├── style.css          ← theme header only
├── theme.json
├── functions.php
├── templates/
├── parts/
├── patterns/
├── styles/            ← style variations (if any)
├── assets/
│   ├── css/
│   │   └── blocks/    ← per-block stylesheets
│   ├── js/
│   └── fonts/
├── inc/               ← PHP includes, one concern per file
├── tests/
│   └── e2e/           ← Playwright specs
├── .wp-env.json
├── .env.example       ← committed, no real values
├── .env               ← NOT committed (see Secrets)
├── CLAUDE.md
└── README.md
```

## Local environment

- `@wordpress/env` (Docker). `.wp-env.json` mounts this repo as the theme: `"themes": ["."]`.
- Dev plugins are listed under `"plugins"` in `.wp-env.json`. Never commit plugin code or paid plugin zips.
- Start: `npx wp-env start`. Site: http://localhost:8888, admin: http://localhost:8888/wp-admin.
- WP-CLI: `npx wp-env run cli wp <command>`.
- wp-env manages its own database and credentials. The theme itself never needs DB credentials.

## Secrets

**Set this up first**, before anything that needs a credential.

- Create `.env.example` (committed) listing every variable with an empty or placeholder value and a one-line comment explaining each.
- Real values go in `.env` (gitignored). Add `.env` and `.env.*` to `.gitignore`, but keep `!.env.example` so the example stays tracked.
- Expected variables (extend as needed):
  - `FIGMA_ACCESS_TOKEN` — Figma personal access token, if any tooling needs it outside the MCP connection
  - `FIGMA_FILE_URL` — link to the design file
  - `WP_ADMIN_USER` / `WP_ADMIN_PASSWORD` — for Playwright tests that log into wp-admin
  - `DB_NAME` / `DB_USER` / `DB_PASSWORD` / `DB_HOST` — for staging/production deploy or sync scripts only. Local dev doesn't use these.
  - `DEPLOY_HOST` / `DEPLOY_USER` / `DEPLOY_PATH` — deployment target
- Node scripts load `.env` via `dotenv`. Playwright config loads it the same way.
- **Never** hardcode a credential in any tracked file, never echo secrets in logs or commit messages, and never paste `.env` contents into chat. Before every commit, check `git diff --cached` for anything that looks like a key or password.

## Design source

- Figma file: see `FIGMA_FILE_URL` in `.env`.
- Use the Figma MCP server:
  - `get_variable_defs` to pull variables into `theme.json` presets. **Do this first, before building any template.**
  - `get_design_context` to get exact values (spacing, sizes, weights) per frame.
  - `get_screenshot` to get the reference image for visual comparison.
- Map Figma variables to `theme.json` presets 1:1 and keep the naming aligned. Never hardcode a hex value or pixel size that exists as a token.

## Workflow

Work **one template, part, or pattern per Figma frame**. Never "build the whole site" in one pass.

For each piece:
1. Pull the frame's design context and screenshot from Figma.
2. Build it using `theme.json` tokens.
3. Verify it (see Definition of done).
4. Commit with a message naming the frame.

## Testing: Definition of done

A template or pattern is **not done** until all of these pass:

1. **No PHP errors.** Activate the theme, load the page, check the debug log (`WP_DEBUG` and `WP_DEBUG_LOG` on in wp-env).
2. **Visual check.** Use Playwright MCP (`@playwright/mcp`, Microsoft's package; don't install lookalikes) to screenshot the page at 375, 768 and 1440 widths. Compare against the Figma frame screenshot and list the differences.
3. **Numeric check.** Read `getComputedStyle` on key elements (headings, body text, buttons, containers) and compare against the Figma values: font size, weight, line height, padding and gap, colors. This check matters more than the screenshot.
4. **Editor check.** Open the template in `/wp-admin/site-editor.php` and confirm it renders and is editable. A front end that works with a broken editor doesn't pass.
5. **Accessibility.** Run axe-core through Playwright and fix anything serious or critical.
6. **Lint.** PHPCS with WordPress Coding Standards passes.

Pixel-perfect screenshot matching is not the goal, because font rendering and real content will differ. Layout, spacing, tokens and behavior must match. Once a template stabilizes, turn its checks into a spec in `tests/e2e/`.

## Conventions

- Text domain: `astara`. Prefix all PHP functions, hooks, handles and pattern slugs with `astara_` or `astara/`.
- Escape all output (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`) and translate all strings.
- Required plugins: feature-detect with `function_exists` / `class_exists` and degrade gracefully, and show an admin notice when something critical is missing. **No TGM Plugin Activation.**
- Keep the list of required plugins in README.md.
- Don't commit `node_modules/`, `vendor/`, `build/` or `.env`.

## Open questions (ask, don't assume)

- Final theme slug and name. `astara` is assumed.
- The list of required plugins.
- Whether a companion plugin is needed (only if there are custom post types, custom blocks or other business logic).
- Deploy target and method (host, and whether deploys run a build or upload artifacts).
- Browser support floor.
