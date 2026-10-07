# Go-live checklist

Everything that has to be checked, changed or finished before the Astara site goes live. This is a **living document**: add an item the moment you introduce or discover something that must be handled before launch, and tick items off (with the date) when they're done. See "Maintaining this file" in `CLAUDE.md`.

- `- [ ]` open · `- [x]` done (add the date and, if useful, how it was verified)
- Staging: https://astara.mystagingwebsite.com/ (auto-deploys from `main`)
- Last updated: 2026-10-07

---

## 1. Blockers — the site is not launchable until these are done

- [ ] **Replace every placeholder `#` link.** Home page content and the `astara/*` patterns have buttons that go nowhere:
  hero "View Case Studies" and "Contact"; How We Help "View Case Studies", "Meet our Team" and "Apply to Operations Associate Program"; Investment Criteria "Download Criteria" (needs the PDF uploaded and linked). Fix on the live pages **and** in `theme/astara/patterns/` so new insertions are right.
- [ ] **X (Twitter) profile URL** under Site Settings (admin menu → Site Settings). The footer's X icon is hidden until a URL is entered; the client hasn't supplied one. LinkedIn (`linkedin.com/company/astara-capital-partners`) is already the built-in default. While there, check the phone, email and the two addresses. These settings are stored in the database (option `astara_site_settings`), so anything changed in the admin has to be entered on each environment, and a content-only database push doesn't carry it.
- [ ] **Publish a Privacy Policy.** The page exists but is a draft on staging. Write it, publish it, link it from the footer. See section 6.
- [ ] **Newsletter signup: real reCAPTCHA keys and notify address** (section 5). Without keys the form is protected by the honeypot only.
- [ ] **Change every password** (section 3) before launch.

## 2. Search engines and indexing

Staging is hidden from search by Pressable's staging robots.txt (`Disallow: /`). The pages themselves still say `index, follow`, and "Discourage search engines" is **unchecked**, so staging is only protected by that robots.txt.

- [ ] On the live site, confirm **Settings → Reading → "Discourage search engines" is UNCHECKED**. (This is a database setting: `content:push-db` overwrites it with the local value.)
- [ ] Load `https://<live domain>/robots.txt` and confirm it does **not** contain `Disallow: /`.
- [ ] View source on the live home page and confirm there is no `noindex` robots meta tag.
- [ ] Yoast SEO configured: site name/logo, default social image, organization schema, per-page titles and descriptions.
- [ ] `/sitemap_index.xml` loads on live; submit it in Google Search Console.
- [ ] Add the live domain to Search Console and Bing Webmaster Tools.

## 3. Credentials and access

- [ ] Change **all** passwords once development is finished: WordPress admin accounts, the Pressable SSH/SFTP password, GitHub Actions secrets (`PRESSABLE_SFTP_*`), and the `WP_ADMIN_*` values in `.env`. The password that's been in `.env` during development counts as exposed to every tool and session that touched this repo.
- [ ] Review **Users** on staging and live: delete or rename any default/dev accounts. A `content:push-db` copies the *local* users table over the remote one, so local credentials may now exist on staging.
- [ ] Replace password auth with **SSH key auth** for the deploy workflow and the content scripts (README → Deployment describes how).
- [ ] Confirm `.env` is not committed and no secret is in the repo history (`git log -p | grep -i password` style check).
- [ ] Optional hardening: 2FA for admins, login-attempt limiting, disable file editing in `wp-config.php`.

## 3b. Dev-only things that must NOT reach production

- [ ] `ASTARA_RECAPTCHA_SITE_KEY` / `ASTARA_RECAPTCHA_SECRET_KEY` in `.wp-env.json` are **Google's public test keys** (always pass). They only apply to local wp-env. Do not copy them to production; set real keys there (section 5).
- [ ] `.claude/` (launch config, session hooks) is tooling, not site code.
- [ ] Plugins active locally differ from staging (local: onepress-login, ShortPixel, Site Kit, UpdraftPlus, WP Security Audit Log; staging also runs Yoast SEO). Decide the final production plugin list and record it in README → "Required plugins" (currently "TBD").

## 4. Content and copy

- [ ] Final **copy and photography** approved by the client for every page (hero, How We Work/Help, Sectors photos, Strategy, Team portraits, Portfolio logos, Contact).
- [ ] Team (20 members) and Portfolio (7 companies): bios, roles, case studies, LinkedIn/email links reviewed. Empty case studies show a "Case study coming soon" fallback.
- [ ] Display order checked on Team and Portfolio. Members with the same "Order" now fall back to creation order, so set Order on each entry if the sequence matters.
- [ ] Remove sample/test content: "Sample Page", the test subscriber, any dummy posts.
- [ ] **News & Media: push the imported press content to staging/production.** 29 News posts now exist locally (one per item on the old site's /media page: 13 linking to PDFs in the Media Library under `uploads/press-and-news/`, 16 linking to PR Newswire/PRWeb), all tagged "Press" and dated with their original release date. Run `content:push-media` **and** push the database (the posts and attachment records live in the DB). The full list is in `docs/press-and-news.md`.
- [ ] **Delete the 6 sample News posts** (IDs 87–92: "Astara Closes Investment in Ally Building Products", "Astara Named a Top Lower Middle Market Firm", etc.) and replace them with real news. They have stock thumbnails and invented text.
- [ ] The imported press posts have **no featured images**, so they show a navy "PDF"/"LINK" tile. Add images if the client wants them, and check the News & Media grid (it shows the 4 newest) looks right once the sample posts are gone.
- [ ] Tags: all imported items use the existing "Press" tag. Decide the final tag set (Company News / Insights / Press) and re-tag as needed; the original site split items into "Press Releases" and "In The News" tabs.
- [ ] Search content for leftover staging URLs (`astara.mystagingwebsite.com`) and `localhost`.
- [ ] Contact page: confirm address, phone, email and the embedded map pin are correct. The page body is a Classic block of raw HTML stored in the database.
- [ ] Footer: contact details (now from Site Settings). The copyright year updates itself.
- [ ] After any `content:push-db`, re-verify the home page content on the live site (the pattern-based sections have to survive the push).

## 5. Forms and email — Newsletter signup (Contact page)

- [ ] Create a **Google reCAPTCHA v2 "I'm not a robot" checkbox** key pair at https://www.google.com/recaptcha/admin for the live domain (and the staging domain, if you want it working there). Enter them under **Subscribers → Settings** (or set `ASTARA_RECAPTCHA_SITE_KEY` / `ASTARA_RECAPTCHA_SECRET_KEY` in `wp-config.php`).
- [ ] Set **"Notify this email"** under Subscribers → Settings to the person who should hear about signups (it defaults to the site admin email).
- [ ] Do a **real signup on the live site**: the entry appears under Subscribers, and the notification email arrives (check spam). `wp_mail` can silently fail if the host's mail isn't set up; if it does, add an SMTP plugin or set SPF/DKIM for the sending domain. Entries record `astara_notified` = 1/0 so you can see failures.
- [ ] Decide where the list goes (the CSV export under Subscribers is the hand-off) and how people unsubscribe.
- [ ] Mention the newsletter, reCAPTCHA and Google Maps in the Privacy Policy.

## 6. Privacy and legal

- [ ] Cookie/consent approach decided. The Contact page loads **Google Maps** and **reCAPTCHA** (third-party scripts/cookies), and Google Site Kit is installed. Decide on a consent banner for the regions you serve.
- [ ] Privacy Policy covers: the newsletter email, reCAPTCHA, Google Maps, analytics.
- [ ] Terms/disclaimer pages if the client needs them (investment firm: confirm with the client/legal).

## 7. Deployment and infrastructure

- [ ] Point the GitHub Actions secrets (`PRESSABLE_SFTP_HOST/USER/PORT/PASSWORD`, `PRESSABLE_DEPLOY_PATH`) at the **production** site, or add a separate production workflow. Decide what happens to the staging deploy (right now every push to `main` deploys to staging).
- [ ] Update the deploy workflow's warnings: `actions/checkout@v4` runs on a deprecated Node 20, and `ubuntu-latest` moves to Ubuntu 26 on **2026-10-19**.
- [ ] Confirm the **PHP version** on Pressable. Local dev runs PHP 8.5.
- [ ] Confirm the production database uses the same charset/collation as local (`utf8mb4` / `utf8mb4_general_ci`).
- [ ] Domain, DNS, SSL, and www/non-www redirect set up; HSTS is already on at Pressable.
- [ ] Caching: after deploy, purge the page cache. The newsletter form deliberately uses no nonce so it keeps working on cached pages.
- [ ] Backups: confirm Pressable's backups and/or UpdraftPlus are configured, and take a manual backup right before launch.

## 8. Content and media sync scripts

- [ ] **Fix `wp --path=/htdocs` in `scripts/push-db.sh` and `scripts/pull-content.sh`.** On Pressable, WordPress core lives outside `htdocs` (`/wordpress/core/<version>`), so WP-CLI reports "This does not seem to be a WordPress installation". Use `scripts/check-remote-uploads.sh` to see which invocation works, then update both scripts. Until then `content:push-db` and `content:pull` can't be relied on.
- [ ] After any `content:push-media`, confirm the server's `wp-content/uploads` folder is `755` (not `700`) and images load. The script now normalises permissions, but verify once on production.
- [ ] Remember the push scripts have **no confirmation prompt** (removed at the owner's request) and overwrite live data: pull first, push deliberately.
- [ ] Set the production values of `PRESSABLE_*` in `.env` when switching the scripts to production.

## 9. QA

- [ ] **Design check against Figma "Web design_Rd. 3"** for every page, using computed styles (CLAUDE.md "Definition of done" step 3). Home sections have been checked; **Strategy, Team, Portfolio, News & Media and Contact still need a numeric pass**. Exports of the frames are in `designs/`.
- [ ] **Devices:** phone and tablet, portrait and landscape, on every page and on real hardware (checked in emulation on 2026-10-07 for Home, Strategy, Team, Portfolio, News & Media and Contact).
- [ ] **Editor check** for every template, part and pattern (they open without errors as of 2026-10-07).
- [ ] **Accessibility:** run axe-core through Playwright and fix serious/critical issues. Keyboard-test the menu overlay, the popups on desktop and the newsletter form.
- [ ] **PHPCS** with WordPress Coding Standards (not installed in this environment yet).
- [ ] **Playwright e2e specs** in `tests/e2e/` for the stable templates.
- [ ] **Performance:** image sizes and formats (team portraits are 100–900 KB originals), ShortPixel configured, Lighthouse run on Home and Team.
- [ ] Decide the **browser support floor** (open question in CLAUDE.md).
- [ ] 404 page tested on the live domain. Note: a missing file under `/wp-content/uploads/` gets Pressable's plain nginx 404, not the themed page.
- [ ] **Click every link on the live site** (header, footer, home buttons, Team and Portfolio pages, News cards, Contact page) using the table in section 12 to know where each is set. External links should open in a new tab; this was verified in the codebase on 2026-10-07 (38 external links, all with `target=\"_blank\"` and `rel=\"noopener noreferrer\"`). Links an editor adds by hand in the block editor are not forced to: tick "Open in new tab" on each.
- [ ] Mobile menu: consider adding a "Get in Touch" button and contact details to the overlay (currently logo + links only).

## 10. Launch day

- [ ] Final backup taken.
- [ ] Merge/deploy to production; run content and media sync if needed (section 8).
- [ ] Purge caches; re-run the section 2 indexing checks on the live domain.
- [ ] Smoke test: every nav link, the contact form/newsletter, Team and Portfolio popups (desktop) and pages (mobile), 404, mobile menu.

## 11. After launch

- [ ] Analytics/Search Console connected and verified (Site Kit is installed; confirm it's connected to the right property).
- [ ] Uptime monitoring.
- [ ] Decide what happens to staging (keep it, and keep it out of search).

---

## 12. Reference — where every link and contact detail is set

Use this when you need to change a link, or to verify them all before launch. "Admin" paths are in the WordPress admin menu on the left.

| What | Where to change it | Notes |
|---|---|---|
| Phone, email, Contact-page address, footer address | **Admin → Site Settings** | Used in the footer and on the Contact page (call and mail links are built from them). Stored in the database, so enter per environment. |
| LinkedIn profile link (footer icon) | **Admin → Site Settings** | Built-in default is `linkedin.com/company/astara-capital-partners`. Opens in a new tab. |
| X (Twitter) profile link (footer icon) | **Admin → Site Settings** | Empty today, so the icon is hidden. Fill it in to show the icon. |
| Footer copyright year | Automatic | Always the current year; nothing to edit. |
| Header and footer menu links | **Admin → Appearance → Editor → Navigation** ("Navigation" menu), or click the menu inside the Header/Footer template part | One menu feeds both. "Investment Criteria" is a custom link to `/#investment-criteria`; Strategy, Team, Portfolio, News & Media and Contact point at those pages, so they follow if a page's URL changes. |
| Logo link in the header (goes to `/`) | **Admin → Appearance → Editor → Patterns → Template parts → Header** | It's a Custom HTML block (`parts/header.html`). |
| Footer "Get in Touch" button (goes to `/contact/`) | **Admin → Appearance → Editor → Patterns → Template parts → Footer** | A normal Button block (`parts/footer.html`). |
| Home page buttons ("View Case Studies", "Contact", "Meet our Team", "Apply to Operations Associate Program", "Download Criteria") | **Admin → Pages → Home**, click each button | All still `#` placeholders (section 1). New copies come from the patterns in `theme/astara/patterns/`, which also need the real links. |
| Strategy page links | **Admin → Pages → Strategy** | Normal page content. |
| Contact page map | **Admin → Pages → Contact** (Classic block, "Text" view) | A Google Maps `<iframe>`; the address is in its `q=` parameter. |
| Newsletter notification email, reCAPTCHA keys | **Admin → Subscribers → Settings** (or `wp-config.php` constants) | See section 5. |
| Team member LinkedIn and email | **Admin → Team Members → edit a member → "Contact Links" box** | LinkedIn opens in a new tab. |
| Portfolio company website ("Visit …" button) | **Admin → Portfolio Companies → edit → "Investment Details" box** | Opens in a new tab. |
| News item that is a PDF or outside link | **Admin → Posts → edit → "Link directly to a file or website" box** | Cards open it in a new tab; the post's own page redirects there. |
| "Back to Team", "Back to Portfolio" links; the News "View More" link | In the theme code (`inc/blocks.php`, `inc/portfolio-blocks.php`, `inc/news-blocks.php`) | Built from the paths `/team/`, `/portfolio/` and `/news-archive/`. **If the slug of the Team, Portfolio or All News page ever changes, these break**, so keep those slugs or update the code. |
| 404 page buttons (Back to Home, Contact) | In the theme code (`404.php`) | Go to `/` and `/contact/`. |
| Social links inside a team bio, or any link typed into page text | The page or member's editor | Add links with the block editor's link tool and tick "Open in new tab" for outside sites. |

## Done

- [x] 2026-10-07 — Team and Portfolio bios/case studies moved from post content into rich-text fields; migration ran on staging.
- [x] 2026-10-07 — Team and Portfolio photos restored on staging (the server's `uploads` folder had been left owner-only, `drwx------`); `push-media.sh` fixed so it can't happen again.
- [x] 2026-10-07 — Staging home page rebuilt with the new full-width sections and verified at desktop, tablet and phone widths.
- [x] 2026-10-07 — Branded 404 page added (`404.php`, not editable in the Site Editor).
- [x] 2026-10-07 — Staging is blocked from search engines by Pressable's robots.txt (re-check on live: section 2).
- [x] 2026-10-07 — News posts can link straight to a PDF or website ("Link directly to a file or website" field); cards open it in a new tab and the post page redirects there.
- [x] 2026-10-07 — Press & News PDFs scraped from astaracapital.com/media and imported locally into the Media Library (`uploads/press-and-news/`), and 29 News posts created from them (locally).
- [x] 2026-10-07 — LinkedIn profile URL set as the default (footer icon live once deployed).
- [x] 2026-10-07 — Site Settings page added (LinkedIn/X links, phone, email, addresses) feeding the footer and Contact page; footer copyright year is now automatic.
- [x] 2026-10-07 — Newsletter signup built (stores subscribers, emails a notification, reCAPTCHA v2 + honeypot); tested locally with Google's test keys.
