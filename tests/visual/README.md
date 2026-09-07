# Visual QA — Playwright viewport sweep

Project-local Playwright install (not global) for repeatable screenshot/overflow sweeps of the local WordPress site across the `DESIGN.md` §21 target viewports. `chrome-devtools` MCP remains the tool for interactive inspection and Lighthouse audits — this script is for scripted, repeatable regression sweeps.

## Setup (one-time)

```bash
cd tests/visual
npm install
npx playwright install chromium
```

## Run

```bash
cd tests/visual
node sweep.mjs                                  # sweeps http://localhost:8080/
node sweep.mjs http://localhost:8080 / /about   # sweeps specific routes
```

From Git Bash on Windows, prefix with `MSYS_NO_PATHCONV=1` when passing a route starting with `/` — otherwise Git Bash rewrites it into a Windows path before Node sees it:

```bash
MSYS_NO_PATHCONV=1 node sweep.mjs http://localhost:8080 /
```

Screenshots and a pass/fail summary are written to `test-results/<timestamp>/` (gitignored by the repo's existing `test-results/` pattern). The script exits non-zero if any route returns HTTP 400+ or overflows its viewport horizontally.
