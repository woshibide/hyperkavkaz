# Plugins

The single list of Kirby plugins for this site.
Every plugin must support Kirby 5.
Install with Composer only; `site/plugins/` is gitignored and rebuilt on every deploy.

| Plugin | Package | Version | Status | What it does here |
| --- | --- | --- | --- | --- |
| [Admin Bar](https://plugins.getkirby.com/pechente/admin-bar) | `pechente/kirby-admin-bar` | 2.3.1 | Installed | Edit bar on the frontend for logged-in Panel users. |
| [Media Manager](https://plugins.getkirby.com/kirby-vault/media-manager) | private Composer (Kirby Vault) | 1.0.0 | **Blocked** | Nested media folders with UUID references. Free, but needs a claim at https://kirbyvault.com/claim/media-manager, which emails the private Composer install instructions. |
| [Uniform](https://plugins.getkirby.com/mzur/uniform) | `mzur/kirby-uniform` | 5.7.0 | Installed | Form handling for future forms (contact etc.). Not used by the newsletter form, see below. |
| [Matomo](https://plugins.getkirby.com/sylvainjule/matomo) | `sylvainjule/matomo` | 2.1.1 | Installed, inactive | Cookieless tracking snippet and Panel stats. Turns on when the `MATOMO_*` environment variables are set. |
| [Staticache](https://plugins.getkirby.com/getkirby/staticache) | `getkirby/staticache` | 2.1.0 | Installed | Writes rendered pages to static HTML files that Apache serves without PHP. |
| [Tiptap](https://plugins.getkirby.com/medienbaecker/tiptap) | `medienbaecker/kirby-tiptap` | 1.3.1 | Installed | Rich text editor for all `text` fields. Render with `->tiptapText()`. |
| [Loop](https://plugins.getkirby.com/moinframe/loop) | `moinframe/kirby-loop` | 1.2.0 | Installed, off | Click-to-comment client feedback. Switched on and off in the Panel under Site > Settings. |
| [HTML Head](https://github.com/bnomei/kirby3-htmlhead) | `bnomei/kirby3-htmlhead` | 5.0.4 | Installed | Builds `<head>` in `site/snippets/layout.php` with the `htmlhead()` helper. |
| [Kirby MCP](https://github.com/bnomei/kirby-mcp) | `bnomei/kirby-mcp` (dev) | 1.15.1 | Installed, dev only | Agent tooling. Never deployed, see `AGENTS.md`. |

Uniform pulls in `mzur/kirby-form` and `mzur/kirby-flash`; Matomo pulls in `guzzlehttp/guzzle`.

## How the plugins interact

### Staticache and logged-in users

Apache serves the static files before PHP runs, so Kirby cannot tell visitors and editors apart on a cached page.
The `.htaccess` rules therefore skip the static files when the request carries a `kirby_session` cookie, and for anything other than GET or HEAD.
Logged-in editors always get a live page with the Admin Bar and Loop; Kirby never writes their pages to the cache.
Anonymous visitors get the static file, which never contains editor UI.

The cache is cleared by Kirby on every Panel change, and by the fortrabbit post-deploy command.
Pages with URL params (`/blog/page:2`) or query strings are not cached.

### Forms and Staticache

Uniform always requires a CSRF token, and a CSRF token starts a session, which makes the page uncacheable.
The newsletter form sits in the footer of every page, so it stays on its own session-free controller (`site/controllers/newsletter.php`).
Use Uniform for forms that live on a single page, and accept that this page will always be rendered by PHP.

### HTML Head and Matomo

Both plugins register a snippet called `matomo`.
Matomo's wins because it loads later (folders load alphabetically), and `snippet('matomo')` in the layout renders Matomo's tracking code.
Check the rendered output after updating either plugin.

### Loop and the cache

Loop is only shown to logged-in users (`moinframe.loop.public` is off), so it never ends up in a static file.
Its comments are stored in `site/logs/loop/comments.sqlite`.

## Adding a plugin

1. Check the plugin directory page lists Kirby 5 support, and the package's `getkirby/cms` constraint if it has one.
2. `composer require vendor/package:^x.y`.
3. Read its README for options, snippets and blueprint sections; check for name clashes with existing snippets.
4. Check whether it changes output for logged-in users or uses sessions, cookies or CSRF: that interacts with Staticache.
5. Add a row to the table above.
