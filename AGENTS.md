# AGENTS.md

Website for the music label HYPERKAVKAZ.
Kirby 5 (flat-file CMS, PHP), started from the official Plainkit.
No build step: plain PHP templates, one CSS file, plain JS modules (Three.js is vendored, see below).

## Status

Design has started, step by step.
`assets/css/main.css` holds the base reset and the design tokens decided so far.
Add colours and layout only once they are decided.

Decided so far:

- Fonts: Adobe Fonts kit `mrh7ota` (linked in `site/snippets/layout.php`), families `sloop-script-one`, `sloop-script-two`, `sloop-script-three`, `sloop-script-pro`, each in 400 and 700.
  Use them through the `--font-sloop-*` custom properties in `main.css`.
  Kit settings (families, weights, `font-display`) are managed at https://fonts.adobe.com/my_fonts#web_projects-section.

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
| `/` | `home` | `home` | Home. Only the rotating 3D wordmark, on a full-viewport canvas. No editable fields. |
| `/artists` | `artists` | `artists` | Listed artists, sorted by title. |
| `/artists/{slug}` | `artist` | `artist` | Biography, cover. |
| `/shop` | `shop` | `shop` | Listed products, in Panel order. |
| `/shop/{slug}` | `product` | `product` | Price, artist, cover, text. No cart or checkout (out of scope for now). |
| `/blog` | `blog` | `blog` | Listed posts, newest first, 10 per page (`/blog/page:2`). Filter by category with `/blog/category:events`; an unknown category is a 404. |
| `/blog/{slug}` | `post` | `post` | Category, date, cover, text. Folder number is the date (`num: date`). |
| `/about` | `default` | `default` | |
| `/press` | `press` | `press` | Media and PR: intro, press contact, coverage list, press kit downloads (the page's files). |
| `/links` | `links` | `links` | Unlisted, linked from the footer. Socials and other project links, in Panel order. |
| `/privacy` | `default` | `default` | Unlisted, linked from the footer. |
| `/newsletter` | `newsletter` | `newsletter` | Unlisted. Handles the sign-up form posted from every page footer. |
| error | `default` | `default` | Kirby's 404 page. |

Main navigation is the listed top-level pages, in folder order: Artists, Shop, Blog, About, Press.

## Code layout

- `site/snippets/layout.php`: the HTML document, header navigation and footer. Templates fill it with `snippet('layout', slots: true)`.
  The `<head>` is built with the `htmlhead()` helper (HTML Head plugin); the Matomo snippet sits before `</body>`.
- `site/blueprints/site.yml`: Panel dashboard, with a Settings tab (Loop on/off).
- `site/controllers/`: collection logic (filter, sort, paginate). Templates stay thin.
- `site/models/product.php`: `ProductPage::formattedPrice()`, using `NumberFormatter`.
- `site/models/post.php`: `PostPage::CATEGORIES` and `PostPage::categoryLabel()`.
- `site/snippets/cover.php`: the page's `cover` image, resized to at most 1600px, with `width`, `height` and `alt`.
- `site/snippets/newsletter.php`: the sign-up form.
- `site/snippets/date.php`: a `<time>` element for any date field, `snippet('date', ['date' => $page->date()])`.
- `site/blueprints/files/image.yml`: all uploaded images use this; it adds the `alt` field.
- `assets/svg/hyperkavkaz.svg`: the wordmark as outlined paths (exported from Illustrator, so no font file is shipped).
- `assets/js/wordmark.js`: extrudes that SVG with Three.js and spins it around the vertical axis.
  It stops under `prefers-reduced-motion`; the material colour is the canvas's CSS `color`.

## Three.js

Vendored, no npm or build step: `assets/js/vendor/three/` holds `three.module.js`, `three.core.js`, `addons/loaders/SVGLoader.js` and `LICENSE` from the `three` npm package, version 0.186.1.
The import map in `site/snippets/layout.php` maps `three` and `three/addons/` to that folder; it must stay before the first module script.
To update, run `npm pack three@<version>` in a temporary folder and copy the same four files over (`addons/` comes from `examples/jsm/`).
Add any further addon at the same path it has under `examples/jsm/`.

## Conventions

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
