#!/usr/bin/env node
// Playwright viewport sweep for local WordPress QA.
//
// Screenshots each route at every DESIGN.md §21 target viewport (plus the
// reference design's own 1920 width), flags horizontal overflow, and writes
// per-route JSON evidence (headings, missing image alt, console/page errors,
// asset HTTP errors) for the design-parity review trail.
//
// Usage: node sweep.mjs [url] [path...] [options]
//   node sweep.mjs                                  -> sweeps http://localhost/
//   node sweep.mjs http://localhost / /about        -> sweeps one or more paths
//   node sweep.mjs http://localhost / --out ../../QA/baseline
//   node sweep.mjs http://localhost /courses/ --widths 1920,1440,390 --scan
//   node sweep.mjs --help
//
// Targets must resolve to localhost/127.0.0.1/::1 — the base URL, every
// request the page makes and every redirect it follows are checked, so this
// can never reach production (CLAUDE.md → cPanel / Production Boundary).
//
// From Git Bash on Windows, a leading "/" argument gets silently rewritten
// to a Windows path (MSYS path conversion) — prefix the command with
// MSYS_NO_PATHCONV=1 when passing route arguments, e.g.:
//   MSYS_NO_PATHCONV=1 node sweep.mjs http://localhost / /about

import { chromium } from 'playwright';
import { mkdirSync, writeFileSync } from 'node:fs';
import path from 'node:path';

// DESIGN.md §21 — Responsive Behavior target QA viewports, plus 1920 (the
// width the client's reference composite was captured at).
const VIEWPORTS = [
  { name: 'desktop-1920x1080', width: 1920, height: 1080 },
  { name: 'desktop-1440x900', width: 1440, height: 900 },
  { name: 'laptop-1280x800', width: 1280, height: 800 },
  { name: 'laptop-sm-1024x768', width: 1024, height: 768 },
  { name: 'tablet-768x1024', width: 768, height: 1024 },
  { name: 'mobile-lg-430x932', width: 430, height: 932 },
  { name: 'mobile-390x844', width: 390, height: 844 },
  { name: 'mobile-sm-360x800', width: 360, height: 800 },
];

const LOCAL_HOSTNAMES = new Set(['localhost', '127.0.0.1', '::1', '[::1]', '0.0.0.0']);
const DEFAULT_SCAN = '380-1900:40';
const SCAN_MIN = 320;
const SCAN_MAX = 2560;
const SCAN_MAX_STEPS = 100; // keeps the optional scan bounded
const MAX_RECORDS = 200; // per evidence list, guards against console-error floods
const NAV_TIMEOUT = 20000;

const HELP = `
Playwright viewport sweep — local WordPress visual QA.

Usage:
  node sweep.mjs [url] [route...] [options]

Arguments:
  url        Base URL. Must be local (localhost/127.0.0.1/::1). Default http://localhost
  route      One or more paths. Default /

Options:
  --out <dir>        Output directory (relative to the current working directory).
                     Default tests/visual/test-results/<timestamp>/.
                     Screenshots and report.json are written per route:
                     <dir>/<route-slug>/<viewport>.png + <dir>/<route-slug>/report.json
  --widths <list>    Comma-separated subset of the viewport widths to capture,
                     e.g. --widths 1920,1440,1024,768,390. Default: all.
  --allow-fonts     Baseline only: allow fonts.googleapis.com/fonts.gstatic.com
                    subresources so the existing remote fonts render accurately.
  --scan [a-b[:s]]   Also scan intermediate widths for horizontal overflow
                     (no screenshots). Default range ${DEFAULT_SCAN};
                     bounded to ${SCAN_MIN}-${SCAN_MAX}px and ${SCAN_MAX_STEPS} steps.
  --help, -h         Show this help.

Available widths: ${VIEWPORTS.map((v) => v.width).join(', ')}

Failures (exit 1): HTTP 400+, horizontal overflow, uncaught page errors,
non-document asset requests returning 400+, any non-local target/redirect.
Warnings (exit 0): console errors, images with no alt attribute, H1 count != 1.

From Git Bash, prefix with MSYS_NO_PATHCONV=1 when passing "/" routes.
`.trimStart();

function die(message) {
  console.error(`sweep: ${message}`);
  process.exit(2);
}

function isLocalHttpUrl(value) {
  let url;
  try {
    url = new URL(value);
  } catch {
    return false;
  }
  if (url.protocol !== 'http:' && url.protocol !== 'https:') return false;
  return LOCAL_HOSTNAMES.has(url.hostname);
}

// Non-HTTP schemes (data:, blob:, about:) never leave the page — only
// http(s) requests need the localhost check.
function isAllowedRequestUrl(value) {
  if (opts.allowFonts && /^https:\/\/(fonts\.googleapis\.com|fonts\.gstatic\.com)\//.test(value)) return true;
  return /^https?:/i.test(value) ? isLocalHttpUrl(value) : true;
}

function parseArgs(argv) {
  const opts = { base: null, routes: [], widths: null, out: null, scan: null, help: false };
  for (let i = 0; i < argv.length; i++) {
    const arg = argv[i];
    if (arg === '--help' || arg === '-h') {
      opts.help = true;
    } else if (arg === '--out') {
      opts.out = argv[++i] || die('--out needs a directory');
    } else if (arg === '--widths') {
      opts.widths = argv[++i] || die('--widths needs a comma-separated list');
    } else if (arg === '--allow-fonts') {
      opts.allowFonts = true;
    } else if (arg === '--scan') {
      const next = argv[i + 1];
      opts.scan = next && /^\d+-\d+(:\d+)?$/.test(next) ? argv[++i] : DEFAULT_SCAN;
    } else if (arg.startsWith('-')) {
      die(`unknown option "${arg}" (try --help)`);
    } else if (opts.base === null && arg.includes('://')) {
      opts.base = arg;
    } else {
      opts.routes.push(arg);
    }
  }
  return opts;
}

function selectViewports(widths) {
  if (!widths) return VIEWPORTS;
  const wanted = widths.split(',').map((w) => Number(w.trim()));
  const selected = [];
  for (const width of wanted) {
    const vp = VIEWPORTS.find((v) => v.width === width);
    if (!vp) die(`unknown width "${width}" — available: ${VIEWPORTS.map((v) => v.width).join(', ')}`);
    if (!selected.includes(vp)) selected.push(vp);
  }
  return selected;
}

function parseScan(spec) {
  const [range, step = '40'] = spec.split(':');
  const [min, max] = range.split('-').map(Number);
  const stepPx = Number(step);
  if (!(min >= SCAN_MIN && max <= SCAN_MAX && min < max && stepPx > 0)) {
    die(`--scan range must be within ${SCAN_MIN}-${SCAN_MAX} with a positive step (got "${spec}")`);
  }
  const steps = Math.floor((max - min) / stepPx) + 1;
  if (steps > SCAN_MAX_STEPS) die(`--scan "${spec}" is ${steps} widths; keep it under ${SCAN_MAX_STEPS}`);
  const widths = [];
  for (let w = min; w <= max; w += stepPx) widths.push(w);
  return { min, max, step: stepPx, widths };
}

function routeSlug(route) {
  return route.replace(/[^a-z0-9]+/gi, '-').replace(/^-|-$/g, '').toLowerCase() || 'home';
}

function record(list, item) {
  if (list.length < MAX_RECORDS) list.push(item);
}

// Waits for webfonts, scrolls the whole page to trigger lazy images and
// scroll-reveal animations, waits for those images, then returns to the top
// so the full-page screenshot is of a settled page.
async function settlePage(page) {
  await page.evaluate(async () => {
    const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
    const withTimeout = (p, ms) => Promise.race([p, sleep(ms)]);
    const fontsReady = () => (document.fonts ? withTimeout(document.fonts.ready, 5000) : Promise.resolve());

    await fontsReady();
    const step = Math.max(320, Math.round(window.innerHeight * 0.9));
    for (let i = 0, y = 0; i < 60 && y < document.documentElement.scrollHeight; i++, y += step) {
      window.scrollTo(0, y);
      await sleep(100);
    }
    window.scrollTo(0, document.documentElement.scrollHeight);
    await sleep(200);
    window.scrollTo(0, 0);
    await sleep(200);

    const pending = [...document.images]
      .filter((img) => !img.complete)
      .map((img) => new Promise((done) => {
        img.addEventListener('load', done, { once: true });
        img.addEventListener('error', done, { once: true });
      }));
    await withTimeout(Promise.all(pending), 5000);
    await fontsReady();
  });
}

async function collectEvidence(page) {
  return page.evaluate(() => {
    const clean = (s) => s.replace(/\s+/g, ' ').trim().slice(0, 160);
    return {
      headings: [...document.querySelectorAll('h1,h2,h3,h4,h5,h6')].map((el) => ({
        level: Number(el.tagName[1]),
        text: clean(el.textContent || ''),
      })),
      // Missing alt attribute only — alt="" is a valid decorative image.
      imagesMissingAlt: [...document.querySelectorAll('img')]
        .filter((img) => !img.hasAttribute('alt'))
        .map((img) => ({ src: img.currentSrc || img.getAttribute('src') || '', class: img.className || '' })),
    };
  });
}

const opts = parseArgs(process.argv.slice(2));
if (opts.help) {
  console.log(HELP);
  process.exit(0);
}

const baseUrl = opts.base || 'http://localhost';
if (!isLocalHttpUrl(baseUrl)) {
  die(`refusing to sweep non-local target "${baseUrl}" — this harness is localhost-only`);
}

const viewports = selectViewports(opts.widths);
const routes = opts.routes.length ? opts.routes : ['/'];
const scan = opts.scan ? parseScan(opts.scan) : null;

const stamp = new Date().toISOString().replace(/[:.]/g, '-');
const outDir = opts.out
  ? path.resolve(process.cwd(), opts.out)
  : path.join(import.meta.dirname, 'test-results', stamp);
mkdirSync(outDir, { recursive: true });

const browser = await chromium.launch();
let failures = 0;
let warnings = 0;
const summary = { generatedAt: new Date().toISOString(), baseUrl, widths: viewports.map((v) => v.width), scan: scan && opts.scan, routes: [] };

try {
  for (const route of routes) {
    const url = new URL(route, baseUrl).toString();
    if (!isLocalHttpUrl(url)) die(`route "${route}" resolves to non-local URL "${url}"`);

    const slug = routeSlug(route);
    const routeDir = path.join(outDir, slug);
    mkdirSync(routeDir, { recursive: true });

    const report = {
      route,
      url,
      generatedAt: new Date().toISOString(),
      viewports: [],
      headings: [],
      h1Count: 0,
      imagesMissingAlt: [],
      consoleErrors: [],
      pageErrors: [],
      assetErrors: [],
      blockedRequests: [],
      scan: null,
      failures: 0,
      warnings: 0,
    };
    const fail = (msg) => { console.error(`FAIL  ${msg}`); failures++; report.failures++; };
    const warn = (msg) => { console.warn(`WARN  ${msg}`); warnings++; report.warnings++; };

    for (const vp of viewports) {
      const page = await browser.newPage({ viewport: { width: vp.width, height: vp.height }, reducedMotion: 'reduce', serviceWorkers: 'block' });
      page.setDefaultNavigationTimeout(NAV_TIMEOUT);

      // Localhost-only guard for sub-resources and observed redirect hops.
      // The post-navigation page.url() check below is the catch-all, since
      // request-level blocking is not reliable across redirects.
      await page.route('**/*', async (r) => {
        const target = r.request().url();
        if (!isAllowedRequestUrl(target)) {
          record(report.blockedRequests, { url: target, resourceType: r.request().resourceType(), viewport: vp.name });
          return r.abort('blockedbyclient');
        }
        // Resolve redirects ourselves before the browser follows them. A
        // post-navigation URL check is too late to protect production.
        let next = target;
        for (let hop = 0; hop < 10; hop++) {
          const response = await r.fetch({ url: next, maxRedirects: 0 });
          const location = response.headers().location;
          if (response.status() < 300 || response.status() >= 400 || !location) {
            return r.fulfill({ response });
          }
          next = new URL(location, next).href;
          if (!isAllowedRequestUrl(next)) {
            record(report.blockedRequests, { url: next, resourceType: r.request().resourceType(), viewport: vp.name });
            return r.abort('blockedbyclient');
          }
        }
        return r.abort('failed');
      });
      page.on('console', (msg) => {
        if (msg.type() === 'error') record(report.consoleErrors, { viewport: vp.name, text: msg.text() });
      });
      page.on('pageerror', (err) => record(report.pageErrors, { viewport: vp.name, message: err.message }));
      page.on('response', (res) => {
        if (res.status() >= 400) {
          record(report.assetErrors, {
            viewport: vp.name,
            url: res.url(),
            status: res.status(),
            resourceType: res.request().resourceType(),
          });
        }
      });

      const file = path.join(routeDir, `${vp.name}.png`);
      const result = { name: vp.name, width: vp.width, height: vp.height, screenshot: path.basename(file) };
      try {
        const response = await page.goto(url, { waitUntil: 'load' });
        result.status = response ? response.status() : null;
        if (!isLocalHttpUrl(page.url())) throw new Error(`navigated off localhost to ${page.url()}`);
        result.finalUrl = page.url();

        await settlePage(page);
        result.scrollWidth = await page.evaluate(() => document.documentElement.scrollWidth);
        result.overflow = result.scrollWidth > vp.width + 1; // 1px tolerance for scrollbar rounding
        await page.screenshot({ path: file, fullPage: true });

        if (result.status && result.status >= 400) {
          fail(`${route} @ ${vp.name}: HTTP ${result.status}`);
        } else if (result.overflow) {
          fail(`${route} @ ${vp.name}: horizontal overflow (scrollWidth=${result.scrollWidth}px > ${vp.width}px)`);
        } else {
          console.log(`OK    ${route} @ ${vp.name} (HTTP ${result.status}) -> ${path.relative(process.cwd(), file)}`);
        }

        // Structural evidence is viewport-independent: collect once, widest first.
        if (!report.headings.length) {
          const evidence = await collectEvidence(page);
          report.headings = evidence.headings;
          report.h1Count = evidence.headings.filter((h) => h.level === 1).length;
          report.imagesMissingAlt = evidence.imagesMissingAlt;
        }

        if (scan && vp === viewports[0]) {
          const overflows = [];
          for (const width of scan.widths) {
            await page.setViewportSize({ width, height: vp.height });
            await page.waitForTimeout(120);
            const scrollWidth = await page.evaluate(() => document.documentElement.scrollWidth);
            if (scrollWidth > width + 1) overflows.push({ width, scrollWidth });
          }
          report.scan = { min: scan.min, max: scan.max, step: scan.step, checked: scan.widths.length, overflows };
          if (overflows.length) {
            fail(`${route}: horizontal overflow at ${overflows.length}/${scan.widths.length} scanned width(s), e.g. ${overflows[0].width}px (scrollWidth=${overflows[0].scrollWidth}px)`);
          } else {
            console.log(`OK    ${route} @ scan ${scan.min}-${scan.max}:${scan.step} (${scan.widths.length} widths)`);
          }
        }
      } catch (err) {
        result.error = err.message;
        fail(`${route} @ ${vp.name}: ${err.message}`);
      } finally {
        report.viewports.push(result);
        await page.close();
      }
    }

    for (const err of report.pageErrors) fail(`${route} @ ${err.viewport}: page error: ${err.message}`);
    for (const err of report.assetErrors.filter((e) => e.resourceType !== 'document')) {
      fail(`${route} @ ${err.viewport}: asset HTTP ${err.status} ${err.url}`);
    }
    for (const blocked of report.blockedRequests) {
      fail(`${route} @ ${blocked.viewport}: blocked non-local request ${blocked.url}`);
    }
    if (report.consoleErrors.length) warn(`${route}: ${report.consoleErrors.length} console error(s), see report.json`);
    if (report.imagesMissingAlt.length) warn(`${route}: ${report.imagesMissingAlt.length} image(s) with no alt attribute`);
    if (report.h1Count !== 1) warn(`${route}: ${report.h1Count} H1 element(s) (expected 1)`);

    writeFileSync(path.join(routeDir, 'report.json'), `${JSON.stringify(report, null, 2)}\n`);
    summary.routes.push({ route, dir: slug, failures: report.failures, warnings: report.warnings });
  }
} finally {
  await browser.close();
}

summary.failures = failures;
summary.warnings = warnings;
writeFileSync(path.join(outDir, 'summary.json'), `${JSON.stringify(summary, null, 2)}\n`);
console.log(`\nEvidence: ${path.relative(process.cwd(), outDir)}`);

if (failures > 0) {
  console.error(`\n${failures} check(s) failed, ${warnings} warning(s).`);
  process.exit(1);
}
console.log(`\nAll viewport checks passed${warnings ? `, ${warnings} warning(s)` : ''}.`);
