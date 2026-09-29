# Astara WordPress Theme

Custom WordPress block theme built from the client's Figma designs. This repository contains **only the theme**, at `theme/astara/`. WordPress core, plugins, media and the database are managed separately.

## Summary

| Area | Decision |
|---|---|
| Theme type | Block theme (Full Site Editing) |
| Design tokens | `theme.json`, mapped 1:1 from Figma variables |
| Styling | Modern plain CSS, per-block stylesheets, no framework |
| Interactivity | WordPress Interactivity API (Vue only if an app-like feature justifies it) |
| Local dev | `@wordpress/env` (Docker) |
| Design source | Figma, accessed through the Figma MCP server |
| Testing | Playwright (visual + computed-style checks), axe-core, PHPCS |
| AI tooling | Claude Code with Figma MCP and Playwright MCP |
| Business logic | Companion plugin, never in the theme |

## Requirements

- Docker
- Node.js LTS
- Composer (for PHPCS)
- GitHub CLI (`gh`), authenticated
- Claude Code, with:
  - **Figma MCP** (Figma's remote or Dev Mode server)
  - **Playwright MCP** (`@playwright/mcp`)

## Getting started

These steps work in a plain terminal — no Claude Code or any AI tooling required.

### 1. Install prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) — must be **running** before you start the site (`docker info` should succeed, not error).
- [Node.js LTS](https://nodejs.org/) (v20+) and npm.
- [Composer](https://getcomposer.org/) — only needed if you're running PHPCS.

### 2. Clone and configure

```bash
git clone <repo-url> astara
cd astara
cp .env.example .env      # fill in real values; never commit .env
npm install                # installs wp-env, Playwright, and other dev tooling
```

### 3. Start the site

```bash
npx wp-env start
```

First run pulls WordPress + MySQL Docker images and can take a few minutes; later runs are fast. When it finishes you'll see:

```
WordPress development site started at http://localhost:8888
```

- **Site**: http://localhost:8888
- **Admin**: http://localhost:8888/wp-admin — wp-env default login is `admin` / `password` (override via `WP_ADMIN_USER` / `WP_ADMIN_PASSWORD` in `.env`, which Playwright tests read)
- **Site Editor**: http://localhost:8888/wp-admin/site-editor.php

The `astara` theme (from `theme/astara/`) is mounted into the container automatically per `.wp-env.json`. If it isn't active yet on a fresh environment:

```bash
npx wp-env run cli wp theme activate astara
```

### 4. Stop / reset the environment

```bash
npx wp-env stop        # stop containers, keep all data
npx wp-env start        # resume where you left off
npx wp-env destroy      # wipe the environment completely (fresh DB next start)
```

### 5. Useful commands

```bash
npx wp-env run cli wp <command>              # run any WP-CLI command inside the container
npx wp-env run cli wp theme list             # confirm the theme is active
npx wp-env logs                              # container logs
```

### Troubleshooting

- **"Cannot connect to the Docker daemon"** — Docker Desktop isn't running. Launch it and wait until it's ready, then retry `npx wp-env start`.
- **"Please run the command 'npx @wordpress/env <command>' instead"** — you ran `npx wp-env` before `@wordpress/env` was installed as a local dependency; run `npm install` first, or use `npx @wordpress/env start` directly.
- **PHP errors** — check the debug log: `npx wp-env run cli bash -c "cat wp-content/debug.log"` (empty/missing means no errors).

## Secrets

All credentials live in `.env`, which is gitignored. `.env.example` documents every variable without real values. That includes database passwords for deploy and sync scripts, WP admin credentials for tests, Figma tokens and deploy targets. Local development uses wp-env's own database, so the theme itself never needs DB credentials.

## Required plugins

_TBD._ Plugins are installed at the site level, not bundled in the theme. For local dev, list them in `.wp-env.json`. The theme feature-detects them and shows an admin notice if a critical one is missing.

## Plan

### Phase 1 — Setup
- [ ] Create GitHub repo; repo root = theme folder
- [ ] Add `.gitignore`, `.env.example`, `.wp-env.json`
- [ ] Connect Figma MCP and Playwright MCP to Claude Code
- [ ] Add `CLAUDE.md` (project instructions for Claude Code)
- [ ] Set up PHPCS with WordPress Coding Standards
- [ ] Confirm the required plugins list and whether a companion plugin is needed

### Phase 2 — Foundation
- [ ] Pull Figma variables into `theme.json` (colors, typography, spacing, layout)
- [ ] Load fonts locally from `assets/fonts/`
- [ ] Build header and footer template parts
- [ ] Set up the global stylesheet and the per-block style loading pattern

### Phase 3 — Templates and patterns
- [ ] Build one template or pattern per Figma frame
- [ ] Each piece passes the Definition of done (below) before moving on

### Phase 4 — Interactivity
- [ ] Navigation, accordions, tabs, etc. via the Interactivity API
- [ ] Re-evaluate Vue only if an app-like feature appears

### Phase 5 — Hardening
- [ ] Turn stable visual and style checks into Playwright specs in `tests/e2e/`
- [ ] Accessibility pass (axe-core plus manual keyboard check)
- [ ] Performance pass (no unused CSS or JS shipped globally)
- [ ] GitHub Actions: PHPCS and Playwright on every PR

### Phase 6 — Handoff
- [ ] Document the deploy process
- [ ] Client editor walkthrough (Site Editor, patterns, what's safe to change)

## Definition of done (per template or pattern)

1. No PHP errors or warnings in the debug log
2. Screenshots at 375, 768 and 1440 widths match the Figma layout
3. Computed styles match the Figma values (sizes, weights, spacing, colors)
4. Renders and edits correctly in the Site Editor
5. axe-core reports no serious or critical issues
6. PHPCS passes

## Repo layout

```
theme/astara/
├── style.css  theme.json  functions.php
├── templates/  parts/  patterns/  styles/
├── assets/{css,css/blocks,js,fonts}/
└── inc/
tests/e2e/
.wp-env.json  .env.example  CLAUDE.md  README.md
```
