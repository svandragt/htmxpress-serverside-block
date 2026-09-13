# AGENTS.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A WordPress plugin: a Gutenberg block scaffolded with `@wordpress/create-block`, adapted to render
server-side and refresh via HTMX instead of client-side JS. It's an example/demo for
[HTMXpress](https://github.com/svandragt/htmxpress), which must be installed and active for the
`/htmx/` endpoint to work.

## Toolchain

Two traps that cost time if you don't know them:

- **`npm`, `node`, `php` and `composer` are not on the bare PATH.** They come from devbox, which
  pins the versions in `devbox.json` (Node 22, PHP 8.4, pcov). Prefix every command with
  `devbox run --`. A bare `npm run build` fails with `command not found`.
- **Use `viv`, not `composer`.** `viv` is a Rust drop-in for Composer on the host PATH
  (`~/.cargo/bin/viv`) and reads the same `composer.json`. CI still uses `composer` — both run the
  same `scripts` entries, so they don't diverge.

## Commands

```
devbox run -- npm install          # install JS deps
devbox run -- npm run start        # watch-mode build (wp-scripts start)
devbox run -- npm run build        # production build to build/
devbox run -- npm run lint:js      # lint JS
devbox run -- npm run lint:css     # lint SCSS
devbox run -- npm run test:unit    # run the JS tests (Jest, via wp-scripts)
devbox run -- npm run plugin-zip   # package the plugin as a zip

devbox run -- viv install          # install PHP deps
devbox run -- viv run test         # run the PHP test suite
devbox run -- viv run test:coverage  # run tests and enforce the 50% coverage gate
devbox run -- viv audit            # check PHP deps for advisories
```

Don't substitute `composer` for `viv` on the coverage script. Composer's script runner resolves a
different `php` than the devbox shell does, loses the pcov extension, and fails with
`No code coverage driver available`. CI sidesteps this by invoking `vendor/bin/phpunit` directly
rather than through any script runner.

Run a single PHP test with PHPUnit's own filters:

```
devbox run -- viv exec phpunit --filter testRenderCallbackReturnsTemplateOutput
devbox run -- viv exec phpunit tests/Unit/RenderTest.php
```

Run a single JS test by file or name, passing Jest's own flags through:

```
devbox run -- npm run test:unit -- src/edit.test.js
devbox run -- npm run test:unit -- -t 'renders'
```

`devbox run test` runs the whole CI sequence locally (see `scripts.test` in `devbox.json`); it
mirrors `.github/workflows/ci.yml` step for step, so a green run there is a good predictor of CI.

There is no `@testing-library/react` in this project and adding it isn't necessary — the JS tests
render with React 18's own `createRoot` and `act`. Two non-obvious requirements: set
`globalThis.IS_REACT_ACT_ENVIRONMENT = true` before rendering (the wp-scripts jsdom preset predates
React 18 and doesn't set it), and import `act` from `react`, not `react-dom/test-utils` — the
deprecated one emits a warning that `@wordpress/jest-console` escalates into a test failure.

## Coverage: the pcov trap

PCOV's `pcov.directory` ini defaults to `<project>/src` — but in this repo `src/` holds the
**JavaScript**, and every PHP file lives in `includes/`, `templates/`, and the root. Left alone,
coverage reports a plausible-looking `0.00%` while the tests genuinely pass.

The fix is baked into the `composer.json` scripts as `php -d pcov.directory=. vendor/bin/phpunit`.
If you invoke PHPUnit directly and see 0%, that flag is what's missing. `devbox.d/` is gitignored,
so the ini cannot be fixed there.

PHPUnit 13 has no built-in fail-under option, so the coverage threshold is enforced by
`bin/coverage-check.php`, which reads `coverage/clover.xml` and exits non-zero below the
percentage passed as its argument.

## Architecture

Two halves, connected loosely:

- **Editor side** (`src/`): a normal Gutenberg block. `index.js` registers it with `save: null`;
  `edit.js` renders it in the editor using `<ServerSideRender>`. There's no `save.js` — output is
  always server-rendered. `wp-scripts` builds this into `build/`, which is what
  `htmx-server-block.php` hands to `register_block_type()`.
- **Server/frontend side**: `htmx-server-block.php` is hook registration only. The callbacks it
  registers live in `includes/render.php`, deliberately split out so they can be unit tested with
  Brain Monkey without bootstrapping WordPress. `htmx_server_block_render_callback()` just
  output-buffers a `load_template()` of a file in `templates/`.
- **HTMX wiring**: `includes/render.php` also filters `htmx.template_paths` to register
  `templates/` with the HTMXpress plugin, exposing every template in that directory under
  `/htmx/<template-name>`. `templates/random_posts.php` contains a
  `<button hx-post="/htmx/random_posts" hx-target="#random-posts">` that re-requests itself and
  swaps in the result — so one template file serves both the initial block render and the HTMX
  partial-refresh endpoint.

To add a new server-rendered, HTMX-refreshable block: add a template to `templates/`,
`load_template()` it from a `render_callback`, and it's automatically reachable at
`/htmx/<filename-without-extension>`.

### Testing the server side

`templates/` is procedural PHP that calls into WordPress globals, so tests need doubles, not just
Brain Monkey function stubs — Brain Monkey stubs functions, not classes, and the templates
instantiate `WP_Query`. Class stubs live in `tests/` and are required from `tests/bootstrap.php`.

## CI

`.github/workflows/ci.yml` runs PHP tests then the JS lint and build, on PHP 8.4 and Node 22.
Branch protection on `main` requires the `test` context.

`.github/workflows/dependabot-auto-merge.yml` enables auto-merge for Dependabot PRs, but
deliberately **skips majors** (`update-type != 'version-update:semver-major'`), so major bumps
always need a human. Note that a Dependabot PR with a `package-lock.json` conflict never gets a
`test` check at all: GitHub can't compute a merge ref for a conflicting PR, so `pull_request`
workflows don't fire and the required check stays absent. `@dependabot rebase`, one PR at a time.
