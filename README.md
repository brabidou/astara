# Astara WordPress Theme

Custom WordPress block theme built from the client's Figma designs. This repository contains **only the theme**. WordPress core, plugins, media and the database are managed separately.

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

```bash
git clone <repo-url> astara
cd astara
cp .env.example .env      # fill in real values; never commit .env
npm install               # dev tooling (wp-env, Playwright)
npx wp-env start
```

- Site: http://localhost:8888
- Admin: http://localhost:8888/wp-admin (wp-env default: `admin` / `password`)
- WP-CLI: `npx wp-env run cli wp <command>`

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
style.css  theme.json  functions.php
templates/  parts/  patterns/  styles/
assets/{css,css/blocks,js,fonts}/
inc/  tests/e2e/
.wp-env.json  .env.example  CLAUDE.md  README.md
```
