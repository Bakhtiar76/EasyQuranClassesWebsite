# Visual QA — Playwright viewport sweep

Project-local Playwright install (not global) for repeatable screenshot/overflow sweeps of the local WordPress site across the `DESIGN.md` §21 target viewports plus the task's 1920px comparison viewport (the source composite itself is 992px wide). `chrome-devtools` MCP remains the tool for interactive inspection and Lighthouse audits — this script is for scripted, repeatable regression sweeps and for the `TASK-DESIGN-PARITY.md` §6/§9 evidence sets.

`sweep.mjs` is the only screenshot harness in this repo. Extend it; do not add a second script beside it.

## Setup (one-time)

```bash
cd tests/visual
npm install
npx.cmd playwright install chromium
```

## Run

```bash
cd tests/visual
node sweep.mjs                              # sweeps http://localhost/ at every viewport
node sweep.mjs http://localhost / /about    # sweeps specific routes
node sweep.mjs --help                       # full option reference
```

From Git Bash on Windows, prefix with `MSYS_NO_PATHCONV=1` when passing a route starting with `/` — otherwise Git Bash rewrites it into a Windows path before Node sees it:

```bash
MSYS_NO_PATHCONV=1 node sweep.mjs http://localhost /
```

## Options

| Option | Effect |
|---|---|
| `--out <dir>` | Output directory, resolved **relative to the current working directory**. Default `tests/visual/test-results/<timestamp>/`. |
| `--widths <list>` | Comma-separated subset of the capture widths, e.g. `--widths 1920,1440,1024,768,390`. Default: all eight. |
| `--scan [min-max[:step]]` | Additionally scan intermediate widths for horizontal overflow, no screenshots. Bare `--scan` uses `380-1900:40`. Bounded to 320–2560px and 100 steps. |
| `--help`, `-h` | Usage and the available widths. |

Capture widths: **1920**, 1440, 1280, 1024, 768, 430, 390, 360.

## Output layout

One directory per route, so a page's evidence stays together:

```
<out>/
  summary.json                         # base URL, widths, per-route failure/warning counts
  home/
    desktop-1920x1080.png              # full-page screenshot per viewport
    desktop-1440x900.png
    ...
    report.json
  courses/
    ...
```

`report.json` per route contains:

- `viewports[]` — status, final URL, `scrollWidth`, `overflow`, screenshot filename (and `error` if the load failed)
- `headings[]` + `h1Count` — every `h1`–`h6` in document order, for heading-hierarchy review
- `imagesMissingAlt[]` — `<img>` elements with **no** `alt` attribute (`alt=""` is a valid decorative image and is not flagged)
- `consoleErrors[]`, `pageErrors[]` — console `error` messages and uncaught exceptions, tagged with the viewport
- `assetErrors[]` — any response with HTTP 400+ (URL, status, resource type)
- `blockedRequests[]` — non-local requests the harness refused (see below)
- `scan` — the intermediate-width result when `--scan` was used

Each list is capped at 200 entries so a console-error loop can't produce an unbounded file.

## Exit codes

- **Failures (exit 1):** HTTP 400+ on a route, horizontal overflow at a captured or scanned width, uncaught page error, non-document asset returning 400+, or any non-local target/redirect.
- **Warnings (exit 0):** console errors, images with no `alt`, `h1Count != 1`. These are recorded as evidence, not gates — judge them per page.

## Localhost-only

The harness refuses any non-local target, in three layers:

1. the base URL and each resolved route URL are host-checked before the browser launches (a non-local target exits 2 without opening a page);
2. every request the page issues goes through a Playwright route handler that aborts anything not on `localhost` / `127.0.0.1` / `::1` — this covers sub-resources and the redirect hops the handler observes;
3. after navigation the final `page.url()` is re-checked, so a redirect that was followed anyway fails the route instead of being screenshotted.

Redirects are fetched with `maxRedirects: 0`; each Location target is checked before its request is sent. The final URL check is an additional assertion. `--allow-fonts` narrowly permits the two Google Fonts hosts for legacy baseline captures only. Blocked requests are recorded in `report.json` and fail the run, so a stray absolute production URL baked into a page surfaces as a test failure rather than silently loading. The rule stands on its own regardless: never point any browser tool at `easyquranclasses.com` (`CLAUDE.md` → cPanel / Production Boundary).

## Page settling

Captures use reduced motion for deterministic carousel/reveal state; test normal motion separately. Before each full-page screenshot the script waits for `document.fonts.ready`, scrolls the page top-to-bottom (triggering lazy-loaded images and scroll-reveal animations), waits for any images that started loading, then returns to the top. Font and image waits are individually time-boxed at 5s so a stalled asset slows a capture rather than hanging the run.

## Design-parity usage (`TASK-DESIGN-PARITY.md`)

Baseline, before changing anything (§6) — run from the repo root so `--out` lands in `QA/`:

```bash
MSYS_NO_PATHCONV=1 node tests/visual/sweep.mjs http://localhost \
  / /courses/ /pricing/ /teachers/ /about/ /contact/ /free-trial/ /faq/ /online-quran-classes-for-kids/ /blog/ \
  --widths 1920,1440,1024,768,390 --out QA/baseline
```

Per-page evidence after a build iteration (§9) — one page, its own directory, plus the intermediate-width overflow scan:

```bash
MSYS_NO_PATHCONV=1 node tests/visual/sweep.mjs http://localhost /courses/ \
  --widths 1920,1440,1024,768,390 --scan --out QA/after
```

That writes `QA/after/courses/*.png` + `report.json`, directly comparable with `QA/baseline/courses/`. Re-running the same command overwrites that route's directory in place, so an iteration always leaves one current evidence set.

Note that `QA/` is **not** gitignored (unlike the default `test-results/` output), so review what you stage — commit the evidence you intend to keep as the audit trail, not every intermediate run.
