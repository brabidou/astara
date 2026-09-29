# CLAUDE.md — Astara WordPress Theme

Custom WordPress **block theme** for a client, built from Figma designs. This repo contains **only the theme**, living at `theme/astara/`. WordPress core, plugins, uploads and the database never live here.

## Stack decisions (settled, don't relitigate)

- **Block theme (FSE)**: `theme.json`, HTML templates, template parts, patterns. No classic PHP template hierarchy.
- **Styling**: `theme.json` is the single source of truth for design tokens (color, typography, spacing, layout). Use plain modern CSS for everything else (nesting, `clamp()`, container queries, custom properties). **No Tailwind. No CSS framework.**
- **Per-block CSS**: load via `wp_enqueue_block_style()` so styles only ship where the block is used. Keep one small global stylesheet for what `theme.json` can't express.
- **Interactivity**: use the WordPress **Interactivity API** for menus, tabs, accordions, filters, etc. **No Vue** unless an app-like feature is explicitly approved. If that happens, scope Vue to that one template and build it with Vite.
- **Build tooling**: none by default. Add `@wordpress/scripts` only when custom blocks or Interactivity API stores need it.
- **Business logic** (custom post types, taxonomies): registered in the theme (`inc/`), e.g. `inc/team.php`. This is a deliberate deviation from the usual "theme vs. plugin" split, chosen for simplicity on this project — it does mean content types disappear if the theme is ever swapped out, which is an accepted tradeoff here, not an oversight.

## Repo layout

```
/                          ← repo root: tooling + config, not theme code
├── theme/
│   └── astara/            ← the theme itself, mounted into wp-env
│       ├── style.css      ← theme header only
│       ├── theme.json
│       ├── functions.php
│       ├── templates/
│       ├── parts/
│       ├── patterns/
│       ├── styles/        ← style variations (if any)
│       ├── assets/
│       │   ├── css/
│       │   │   └── blocks/    ← per-block stylesheets
│       │   ├── js/
│       │   └── fonts/
│       └── inc/           ← PHP includes, one concern per file
├── tests/
│   └── e2e/               ← Playwright specs
├── .wp-env.json
├── .env.example           ← committed, no real values
├── .env                   ← NOT committed (see Secrets)
├── CLAUDE.md
└── README.md
```

Only files under `theme/astara/` ship to WordPress as the theme. Everything else at the repo root (config, tests, tooling manifests) is project scaffolding.

## Local environment

- `@wordpress/env` (Docker). `.wp-env.json` mounts the theme subfolder: `"themes": ["./theme/astara"]`.
- Dev plugins are listed under `"plugins"` in `.wp-env.json`. Never commit plugin code or paid plugin zips.
- Start: `npx wp-env start`. Site: http://localhost:8888, admin: http://localhost:8888/wp-admin.
- WP-CLI: `npx wp-env run cli wp <command>`.
- wp-env manages its own database and credentials. The theme itself never needs DB credentials.

## Deployment

- Target: **Pressable**, deployed via `.github/workflows/deploy.yml` (`rsync` over SSH via `sshpass`, same mechanism as `scripts/*.sh`) on push to `main` when `theme/astara/**` changes, or manually via workflow_dispatch.
- Auth is username/password for now (deliberate, temporary choice — upgrade to SSH key auth later; see README's Deployment section for how).
- No build step — the theme ships as-is (per "Build tooling: none by default" above). If `@wordpress/scripts` is ever added for a custom block, add a build step to the workflow before the deploy step.
- Credentials are GitHub Actions secrets (`PRESSABLE_SFTP_HOST`, `PRESSABLE_SFTP_USER`, `PRESSABLE_SFTP_PORT`, `PRESSABLE_SFTP_PASSWORD`, `PRESSABLE_DEPLOY_PATH`), not `.env`. See README's Deployment section for exact setup steps.
- This deploy only ships theme code, never content. Database/media sync between local and Pressable is `scripts/pull-content.sh` / `push-db.sh` / `push-media.sh` (WP-CLI over SSH, no plugin) — see README's "Content sync" section. `.env`'s `PRESSABLE_*` vars (host/user/port/WP root/site URL) drive these; the SSH password is never stored, only prompted for. Push scripts require typing `PUSH` to confirm — never make a push non-interactive or silent.

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
- **Canonical page/canvas: "Web design_Rd. 3".** The file has multiple design rounds (Rd. 1, Rd. 2, Rd. 3, …) as separate pages/canvases — always confirm you're pulling frames from **Rd. 3**, not an earlier round.
- Use the Figma MCP server:
  - `get_variable_defs` to pull variables into `theme.json` presets. **Do this first, before building any template.**
  - `get_design_context` to get exact values (spacing, sizes, weights) per frame.
  - `get_screenshot` to get the reference image for visual comparison.
  - `download_assets` to pull real logo/icon SVGs and other image assets — don't substitute a generic placeholder (e.g. a default block's built-in icon set) when the real asset is available.
- Map Figma variables to `theme.json` presets 1:1 and keep the naming aligned. Never hardcode a hex value or pixel size that exists as a token.
- If Figma MCP calls return a tool-call-limit error (this happens on accounts with only View access to a file — the limit is tied to your role on that specific file, not the plan or the connection method), stop and tell the user rather than guessing at values or falling back to a different page. Getting Editor/Dev access to the file, or your own copy of it, removes the cap.

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
- Template parts (`.html` files) are **not** PHP — `<?php ... ?>` inside them is printed as literal text, not executed. For anything that needs `esc_url()`/`home_url()`/dynamic PHP, either use a native block that already handles it (e.g. logos via CSS `background-image` on a static anchor, not inline PHP) or move the logic into a real PHP-rendered block.
- `core/navigation` block CSS ships a `color: inherit` rule at `.wp-block-navigation .wp-block-navigation-item__content.wp-block-navigation-item__content` (specificity 0,3,0, via a duplicated class). A typical 2-class override (e.g. `.astara-nav .wp-block-navigation-item__content`) silently loses to it. Match or beat that specificity (e.g. `.astara-header .astara-nav .wp-block-navigation-item__content`) whenever styling nav link color, and verify with `getComputedStyle` — don't trust a screenshot alone, since two dark colors can look identical at a glance.

## Open questions (ask, don't assume)

- Final theme slug and name. `astara` is assumed.
- The list of required plugins.
- Browser support floor.
