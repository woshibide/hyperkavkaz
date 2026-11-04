# AGENTS.md

Website for the music label HYPERKAVKAZ.
The audience is Japanese; the site is written entirely in English.
Kirby 5 (flat-file CMS, PHP), started from the official Plainkit.
No build step: plain PHP templates, one CSS file, plain JS modules (Three.js is vendored, see below).

## Status

Design has started, step by step.
`assets/css/main.css` holds the base reset, the design tokens decided so far, and the shared page frame (header and footer).
Add colours and layout only once they are decided.
Pages are designed one at a time and approved by the user before the next one starts.

Decided so far:

- Fonts: self-hosted Pinyon Script for display text through `--font-display` in `main.css`.
  Running text and UI use the system sans, `--font-text`.
- Composition: centred. The wordmark, navigation and every footer row sit on the vertical axis of the page.
- Colours: the `--color-*` custom properties in `main.css` (snow paper, night ink, pomegranate accent for the current page and errors).
- Header: the wordmark SVG, inlined, then the main navigation in one row (no menu button, also on phones).
- Page content: a centred column (`.column`, 48rem), headed by a giant page title in the script: `site/snippets/page-head.php`, used by every template unless a page gets a design of its own.
  The title is one line, exactly as wide as the column, and never taller than half the screen (`assets/js/page-title.js`).
  A title too long to stay 64px or larger on one line (post titles) wraps at 64px instead.
- Every section has its own arrangement; do not copy one page's layout to another.
  Shared parts are components only: `.section-title` (script headings), `.prose` (running text in a 36rem measure, left-aligned), `.facts` (label and value table), `.lead`, `.button`, `.tag`, `.back-link`.
- Blog: category tabs (`.tabs`), then one row per post: date, category tag, title, and the cover as a 4:3 thumbnail when set.
- Post: date and category under the title, the cover across the column, the text, then older and newer post cards and "All posts".
- Artists: an index by first letter (A to Z row, letters without artists dimmed), then the names large in the sans, grouped under script letters, with where each is based and how many releases.
- Artist: where they are based, a 4:5 portrait, biography, discography (their shop products), their links as pills.
- Shop: a grid of square covers, three a row (two on phones), artist above title, format, price, and a tag when pre-order or sold out.
- Product: the artist under the title, the cover, a framed purchase box (price, availability, "Buy" or "Pre-order" with the site it opens), description, details table, numbered tracklist with the total running time.
- About: a lead statement, the story, an overview table, and the history as a timeline.
- Press: introduction, a framed press contact, coverage as a dated table (stacked on phones), the press kit as download cards.
- Links: one large pill button per link, with the site it opens under the name.
- Privacy (legal template): effective and revision dates under the title, a framed contents list, articles numbered from the text's level 2 headings.
- Newsletter page: the form in a framed panel, under the page's own introduction.
- Not found: a short explanation, then every section as a tile.
- Dates: `2026.10.23` (year first, the order Japanese readers expect), everywhere through `site/snippets/date.php`.
- Pagination: previous, up to 5 page numbers, next (`site/snippets/pagination.php`).
- Footer: newsletter sign-up, socials (Bandcamp, Instagram, YouTube), then Links, Privacy policy and "Hyperkavkaz" in the script.

## Commands

```sh
composer install          # Kirby and dev tools into kirby/ and vendor/
composer mcp              # (re)generate the local Kirby MCP runtime, after install or update
composer start            # dev server on http://localhost:8000
```

The Panel is at `/panel`.
Local admin account: `test@test.test` / `1q2w3e4r`.
It exists only in this checkout (`site/accounts/` is gitignored); never create it on a public server.

The dev server (`kirby/router.php`) serves any file under the project root, including `content/`, `site/` and `.kirby-mcp/`.
That is acceptable locally only; on Apache, `.htaccess` blocks those paths.

## Site map

| URL | Template | Blueprint | Notes |
| --- | --- | --- | --- |
| `/` | `home` | `home` | Home. Three.js shield emblem with a reference image fallback. No editable fields. |
| `/artists` | `artists` | `artists` | Listed artists, sorted by title. |
| `/artists/{slug}` | `artist` | `artist` | Based in, portrait (cover), biography, links. Discography comes from shop products that name the artist. |
| `/shop` | `shop` | `shop` | Listed products, in Panel order. |
| `/shop/{slug}` | `product` | `product` | Price, availability, shop link, artist, format, catalogue number, release date, cover, description, tracklist. No cart or checkout: "Buy" opens the shop link (Bandcamp). |
| `/blog` | `blog` | `blog` | Listed posts, newest first, 10 per page (`/blog/page:2`). Filter by category with `/blog/category:events`; an unknown category is a 404. |
| `/blog/{slug}` | `post` | `post` | Category, date, cover, text. Folder number is the date (`num: date`). |
| `/about` | `about` | `about` | Lead, text, overview table, history. |
| `/press` | `press` | `press` | Media and PR: intro, press contact, coverage list, press kit downloads (the page's files). |
| `/links` | `links` | `links` | Unlisted, linked from the footer. Socials and other project links, in Panel order. |
| `/privacy` | `legal` | `legal` | Unlisted, linked from the footer. In effect since, last revised, articles from level 2 headings. |
| `/newsletter` | `newsletter` | `newsletter` | Unlisted. Handles the sign-up form posted from every page footer. |
| error | `error` | `default` | Kirby's 404 page, titled "Page not found". |

Main navigation is the listed top-level pages, in folder order: Artists, Shop, Blog, About, Press.

## Code layout

- `site/snippets/layout.php`: the HTML document, header navigation and footer. Templates fill it with `snippet('layout', slots: true)`.
  The `<head>` is built with the `htmlhead()` helper (HTML Head plugin); the Matomo snippet sits before `</body>`.
- `site/blueprints/site.yml`: Panel dashboard, with a Settings tab: the footer's social URLs (Bandcamp, Instagram, YouTube; empty ones are left out) and Loop on/off.
- `site/controllers/`: collection logic (filter, sort, paginate). Templates stay thin.
- `site/models/product.php`: `ProductPage::formattedPrice()`, using `NumberFormatter`.
- `site/models/product.php` also has `ProductPage::AVAILABILITY`, `availabilityLabel()`, `runningTime()` (tracklist total) and `linkHost()`.
- `site/models/post.php`: `PostPage::CATEGORIES` and `PostPage::categoryLabel()`.
- `site/models/artist.php`: `ArtistPage::releases()`, the listed products whose artist is this page.
- `site/models/legal.php`: `LegalPage::articles()`, the level 2 headings of the text for the contents list.
- `site/snippets/tiptap/heading.php`: overrides the Tiptap plugin's heading so level 2 headings get an id (slug of their text), matching `LegalPage::articles()`.
- `site/snippets/cover.php`: the page's `cover` image, resized to at most 1600px, with `width`, `height` and `alt`.
- `site/snippets/newsletter.php`: the sign-up form.
- `site/snippets/page-head.php`: the page's `<h1>`, giant and in the script; extra content (like the blog's tabs) goes in its default slot.
- `site/snippets/date.php`: a `<time>` element for any date field, `snippet('date', ['date' => $page->date()])`; add `'month' => true` for 2026.10.
- `site/snippets/pagination.php`: numbered pagination for any paginated collection.
- `site/blueprints/files/image.yml`: all uploaded images use this; it adds the `alt` field.
- `assets/svg/hyperkavkaz.svg`: the wordmark as outlined paths (exported from Illustrator, so no font file is shipped).
  The header inlines it with `svg()` and colours it with `currentColor`, so keep the file clean after a re-export: no XML declaration, no `id` or `data-name` attributes, no fills, and `aria-hidden="true" focusable="false"` on the root.
- `assets/js/main.js`: loaded on every page; imports `page-title.js`.
- `assets/js/page-title.js`: fits each `.page-title` to its column by the ink of the glyphs (measured on a canvas, because script swashes overflow the text box), capped at half the viewport height, refitted on resize.
  Without JavaScript the CSS fallback size applies.
- `assets/js/emblem.js`: loads the home shield with named panel hit targets and honours reduced motion.
  Rebuild the model from the supplied Blender and JPEG sources with `scripts/export-emblem.py`.
- `assets/js/wordmark.js`: extrudes the wordmark SVG with Three.js and spins it around the vertical axis.
  It stops under `prefers-reduced-motion`; the material colour is the canvas's CSS `color`.

## Three.js

Vendored, no npm or build step: `assets/js/vendor/three/` holds `three.module.js`, `three.core.js`, SVGLoader, GLTFLoader and its utilities, and `LICENSE` from the `three` npm package, version 0.186.1.
The import map in `site/snippets/layout.php` maps `three` and `three/addons/` to that folder; it must stay before the first module script.
To update, run `npm pack three@<version>` in a temporary folder and copy the same files over (`addons/` comes from `examples/jsm/`).
Add any further addon at the same path it has under `examples/jsm/`.

## Conventions

- Language: all site text is English, `lang="en"`.
  Never use Japanese words or characters anywhere: content, templates, Panel labels, code, comments, alt text, or file names.
  Design guidance aimed at Japanese audiences (such as the `japanese-webdesign` skill) applies to layout, density and trust, never to wording; replace its Japanese labels with English ones.
- Escape all output: `->esc()` on fields, `esc()` on strings.
- Rich text fields use the Tiptap editor (`type: tiptap`, `uploads: image`) and render with `->tiptapText()`, never `->kirbytext()`.
- Every listing renders an empty state.
- Navigation marks the current page with `aria-current="page"` and its section with `aria-current="true"`.
- Images always carry `width`, `height` and `alt`.
- Indentation is 2 spaces (`.editorconfig`).
- Prices are in EUR.
  The currency is set in two places that must match: `ProductPage::CURRENCY` and the `after` label of the price field in `blueprints/pages/product.yml`.
- Blog categories are `media` (interviews, features) and `events`.
  They are set in two places that must match: `PostPage::CATEGORIES` and the options of the category field in `blueprints/pages/post.yml`.
- Product availability is `available`, `preorder` or `soldout`, set in two places that must match: `ProductPage::AVAILABILITY` and the options of the availability field in `blueprints/pages/product.yml`.
- Never name a field after a `Page` method such as `status`, `url`, `parent`, `files` or `num` (`title` is the built-in exception): `$page->status()` returns the page status, not the field.

## Newsletter

The form validates the address and shows a confirmation, but it is not connected to a provider.
Valid addresses are discarded.
Connect the provider at the `TODO` in `site/controllers/newsletter.php`.
Spam protection is a honeypot field (`website`).
The form has no CSRF token, on purpose: a token would start a session on every page and stop Staticache from caching any page.
For the same reason it does not use Uniform (see `PLUGINS.md`).
The provider's double opt-in is the real safeguard.

## Plugins

All plugins, their status and how they interact are listed in `PLUGINS.md`.
Read it before adding a plugin or touching caching, forms, or anything that differs for logged-in users.

## Hosting (fortrabbit)

Production runs on fortrabbit: Apache with `.htaccess`, document root is the repository root.
Docs: https://docs.fortrabbit.com/guides/kirby

- Deploy with Git. fortrabbit's build runs `composer install --no-dev --prefer-dist --no-interaction --no-ansi --no-progress`, so `kirby/`, `vendor/` and `site/plugins/` are rebuilt on every deploy.
- Post-deploy command: `rm -rf site/cache/*/pages` (clears the Staticache files).
- Paths that must survive deploys go in the environment's excluded paths: `/content`, `/media` (both from fortrabbit's Kirby guide), and also `/site/accounts`, `/site/sessions`, `/site/logs` (Panel users, sessions, Loop comments).
- fortrabbit's guide keeps `content/` out of Git and syncs it with rsync. Not decided for this project yet.
- Environment variables: `APP_DEBUG=false`, `APP_MODE=production`, and `MATOMO_URL`, `MATOMO_SITE_ID`, `MATOMO_TOKEN` once Matomo runs.
- Choose PHP 8.3 or newer in the dashboard; confirm `intl` and `pdo_sqlite` with `phpinfo()` (Loop needs SQLite).
- Panel accounts are not deployed. Create the first one on the server with the `panel.install` option switched on for that moment only.
- `sendmail` is not available. Any form that sends email (Uniform's email action) needs SMTP settings in the `email` option.
- Matomo needs MySQL, which fortrabbit offers per environment, so self-hosted Matomo gets its own environment and database.

## Kirby MCP

`bnomei/kirby-mcp` is a dev dependency, pinned to `^1.15`, the last line that supports Kirby 5.
Do not upgrade it to 6.x before the site moves to Kirby 6.

- The MCP server is registered for Claude Code in `.mcp.json` (absolute paths, adjust per machine).
- `composer mcp` writes `.kirby-mcp/`, `site/commands/mcp/` and `site/plugins/kirby-mcp/`.
  All three are gitignored (all of `site/plugins/` is) and must never be deployed: the plugin needs the dev-only package and would break a `composer install --no-dev` build.
- Project skills live in `.claude/skills/`, pinned to kirby-mcp `v1.15.1` in `skills-lock.json`:
  `kirby-scaffold-page-type`, `kirby-panel-and-blueprints`, `kirby-collections-and-navigation`, `kirby-forms-and-frontend-actions`.
  More are available with `npx skills add "https://github.com/bnomei/kirby-mcp/tree/v1.15.1" --list`.

Prefer MCP tools for project facts: `kirby_init` first, then `kirby_blueprints_index`, `kirby_render_page`, and the `kirby://kb` knowledge base.

## UI rules

- Check UI code against the Vercel Web Interface Guidelines:
  https://raw.githubusercontent.com/vercel-labs/web-interface-guidelines/main/command.md
- When the design phase starts, use the `frontend-design` skill.
- The quality floor applies from day one: keyboard focus visible, `prefers-reduced-motion` respected, layouts work at phone width.

## Verify a change

1. `composer start`.
2. Load every affected URL from the site map and check the status code and output.
3. For listings, test both an empty collection and more than 10 items (pagination).
4. For the newsletter, post an invalid address, a valid one, and one with the honeypot filled.
5. Staticache is off locally (`config.localhost.php`). To test caching, switch it on there, load pages anonymously, and check `site/cache/localhost_8000/pages/`. Switch it off again afterwards.
6. Remove any test content, `media/pages/` and `site/cache/localhost_8000/` afterwards.
