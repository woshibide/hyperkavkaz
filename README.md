# hyperkavkaz

Kirby 5 site, scaffolded from the official Plainkit.
Website for the music label HYPERKAVKAZ.
No design decisions have been made yet.

## Requirements

- PHP 8.3 to 8.5 (with intl and pdo_sqlite)
- Composer 2

## Run locally

```sh
composer install
composer mcp               # local Kirby MCP runtime (dev only)
composer start             # http://localhost:8000
```

Open http://localhost:8000/panel to create the first Panel account.
Kirby only allows this on localhost.

## Pages

Home, About, Artists (and one page per artist), Shop (and one page per product), Blog (and one page per post), Press, Privacy policy, and Newsletter.
The site map, code layout and conventions are in `AGENTS.md`; plugins are listed in `PLUGINS.md`.

`kirby/` and `vendor/` are installed by Composer and not tracked.

## Docs

- Guide: https://getkirby.com/docs/guide
- Reference: https://getkirby.com/docs/reference
- License: Kirby needs a license before going live, see https://getkirby.com/buy
