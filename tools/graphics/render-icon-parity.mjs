/** Render the maintained icon/ornament parity sheet after SVG changes. */
import { createRequire } from 'node:module';
import path from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';

const repoRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..', '..');
const require = createRequire(path.join(repoRoot, 'tests', 'visual', 'noop.js'));
const { chromium } = require('playwright');

const browser = await chromium.launch();
const page = await browser.newPage({ viewport: { width: 1600, height: 1000 }, deviceScaleFactor: 1 });
await page.goto(pathToFileURL(path.join(repoRoot, 'QA', 'icon-parity', 'collage.html')).href, { waitUntil: 'load' });
await page.waitForFunction(() => [...document.images].every((image) => image.complete));
const sections = page.locator('section');
await sections.nth(await sections.count() - 1).screenshot({ path: path.join(repoRoot, 'QA', 'icon-parity', 'collage-14.png') });
await page.screenshot({ path: path.join(repoRoot, 'QA', 'icon-parity', 'collage-full.png'), fullPage: true });
await browser.close();
