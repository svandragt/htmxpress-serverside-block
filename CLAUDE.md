# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A WordPress plugin: a Gutenberg block scaffolded with `@wordpress/create-block`, adapted to render server-side and refresh via HTMX instead of client-side JS. It's an example/demo for [HTMXpress](https://github.com/svandragt/htmxpress), which HTMXpress must be installed and active for the `/htmx/` endpoint to work.

## Commands

```
npm install       # install JS deps
npm run start     # watch mode build (wp-scripts start)
npm run build     # production build to build/
npm run lint:js   # lint JS
npm run lint:css  # lint SCSS
npm run format    # format JS/SCSS
npm run plugin-zip # package plugin as zip
```

There's no PHP build step, no PHP test suite, and no PHP linter configured in this repo.

## Architecture

Two halves, connected loosely:

- **Editor side** (`src/`): a normal Gutenberg block (`index.js` registers it, `edit.js` renders it in the editor using `<ServerSideRender>` — there's no `save.js`, since output is always server-rendered). Built by `wp-scripts` into `build/`, which is what `htmx-server-block.php` registers via `register_block_type()`.
- **Server/frontend side**: `htmx-server-block.php` sets the block's `render_callback` to just `load_template()` a PHP file from `templates/`. That template (`templates/random_posts.php`) is plain procedural PHP using `WP_Query` — no build step, edit and reload.
- **HTMX wiring**: the same PHP file adds a filter, `htmx.template_paths`, that registers `templates/` with the HTMXpress plugin, exposing every template in that directory under the `/htmx/<template-name>` endpoint. The template itself contains a `<button hx-post="/htmx/random_posts" hx-target="#random-posts">` that re-requests itself and swaps in the result — so the same template file serves both the initial block render and the HTMX partial-refresh endpoint.

To add a new server-rendered/HTMX-refreshable block: add a template to `templates/`, `load_template()` it from a `render_callback`, and it's automatically reachable at `/htmx/<filename-without-extension>` for `hx-post`/`hx-get` targets.
