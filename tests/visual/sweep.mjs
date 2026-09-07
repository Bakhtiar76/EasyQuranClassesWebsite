#!/usr/bin/env node
// Playwright viewport sweep for local WordPress QA.
//
// Screenshots a URL at every DESIGN.md §21 target viewport and flags
// horizontal overflow (DESIGN.md §21: "no layout overflow at target
// widths"). Output goes to tests/visual/test-results/<timestamp>/ —
// matched by the repo's existing `test-results/` .gitignore pattern.
//
// Usage: node sweep.mjs [url] [path...]
//   node sweep.mjs                              -> sweeps http://localhost:8080/
//   node sweep.mjs http://localhost:8080 /about  -> sweeps one or more paths
//
// From Git Bash on Windows, a leading "/" argument gets silently rewritten
// to a Windows path (MSYS path conversion) — prefix the command with
// MSYS_NO_PATHCONV=1 when passing route arguments, e.g.:
//   MSYS_NO_PATHCONV=1 node sweep.mjs http://localhost:8080 / /about

import { chromium } from 'playwright';
import { mkdirSync } from 'node:fs';
import path from 'node:path';

// DESIGN.md §21 — Responsive Behavior target QA viewports.
const VIEWPORTS = [
  { name: 'desktop-1440x900', width: 1440, height: 900 },
  { name: 'laptop-1280x800', width: 1280, height: 800 },
  { name: 'laptop-sm-1024x768', width: 1024, height: 768 },
  { name: 'tablet-768x1024', width: 768, height: 1024 },
  { name: 'mobile-lg-430x932', width: 430, height: 932 },
  { name: 'mobile-390x844', width: 390, height: 844 },
  { name: 'mobile-sm-360x800', width: 360, height: 800 },
];

const baseUrl = process.argv[2] || 'http://localhost:8080';
const routes = process.argv.slice(3).length ? process.argv.slice(3) : ['/'];

const stamp = new Date().toISOString().replace(/[:.]/g, '-');
const outDir = path.join(import.meta.dirname, 'test-results', stamp);
mkdirSync(outDir, { recursive: true });

const browser = await chromium.launch();
let failures = 0;

try {
  for (const route of routes) {
    const url = new URL(route, baseUrl).toString();
    for (const vp of VIEWPORTS) {
      const page = await browser.newPage({ viewport: { width: vp.width, height: vp.height } });
      const routeSlug = route.replace(/[^a-z0-9]+/gi, '-').replace(/^-|-$/g, '') || 'home';
      const file = path.join(outDir, `${routeSlug}--${vp.name}.png`);
      try {
        const response = await page.goto(url, { waitUntil: 'load', timeout: 15000 });
        const status = response ? response.status() : null;
        const scrollWidth = await page.evaluate(() => document.documentElement.scrollWidth);
        const overflow = scrollWidth > vp.width + 1; // 1px tolerance for scrollbar rounding
        await page.screenshot({ path: file, fullPage: true });

        if (status && status >= 400) {
          console.error(`FAIL  ${route} @ ${vp.name}: HTTP ${status}`);
          failures++;
        } else if (overflow) {
          console.error(`FAIL  ${route} @ ${vp.name}: horizontal overflow (scrollWidth=${scrollWidth}px > ${vp.width}px)`);
          failures++;
        } else {
          console.log(`OK    ${route} @ ${vp.name} (HTTP ${status}) -> ${path.relative(process.cwd(), file)}`);
        }
      } catch (err) {
        console.error(`FAIL  ${route} @ ${vp.name}: ${err.message}`);
        failures++;
      } finally {
        await page.close();
      }
    }
  }
} finally {
  await browser.close();
}

if (failures > 0) {
  console.error(`\n${failures} check(s) failed.`);
  process.exit(1);
}
console.log('\nAll viewport checks passed.');
