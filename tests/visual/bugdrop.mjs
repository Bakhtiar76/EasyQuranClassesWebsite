#!/usr/bin/env node
// Read-only BugDrop smoke test for the local WordPress site.
//
// Verifies the public widget contract without submitting a GitHub Issue:
// the pinned script loads, the configured repository is exact, BugDrop emits
// its ready state, and the open Shadow DOM contains the trigger button.

import { chromium } from 'playwright';

const TARGET = new URL(process.argv[2] || 'http://localhost/');
const LOCAL_HOSTNAMES = new Set(['localhost', '127.0.0.1', '::1', '[::1]', '0.0.0.0']);
const BUGDROP_HOSTNAME = 'bugdrop.neonwatty.workers.dev';

if (!LOCAL_HOSTNAMES.has(TARGET.hostname) || !['http:', 'https:'].includes(TARGET.protocol)) {
  throw new Error(`BugDrop smoke tests are local-only; refused ${TARGET.href}`);
}

const browser = await chromium.launch({ headless: true });
const context = await browser.newContext({ viewport: { width: 390, height: 844 } });
const page = await context.newPage();
const failedRequests = [];
const pageErrors = [];
const workerResponses = [];

await page.route('**/*', async (route) => {
  const requestUrl = new URL(route.request().url());
  if (LOCAL_HOSTNAMES.has(requestUrl.hostname) || requestUrl.hostname === BUGDROP_HOSTNAME) {
    await route.continue();
    return;
  }
  await route.abort('blockedbyclient');
});

page.on('requestfailed', (request) => {
  if ('blockedbyclient' !== request.failure()?.errorText) {
    failedRequests.push(`${request.failure()?.errorText || 'unknown'} ${request.url()}`);
  }
});
page.on('pageerror', (error) => pageErrors.push(error.message));
page.on('response', (response) => {
  const responseUrl = new URL(response.url());
  if (responseUrl.hostname === BUGDROP_HOSTNAME) {
    workerResponses.push({ path: responseUrl.pathname, status: response.status() });
  }
});

try {
  const response = await page.goto(TARGET.href, { waitUntil: 'networkidle', timeout: 20000 });
  if (!response?.ok()) {
    throw new Error(`Local page returned HTTP ${response?.status() || 'unknown'}`);
  }

  await page.waitForFunction(
    () => window.BugDrop && document.querySelector('#bugdrop-host')?.shadowRoot?.querySelector('.bd-trigger'),
    null,
    { timeout: 15000 },
  );
  const trigger = page.locator('#bugdrop-host .bd-trigger');
  await trigger.waitFor({ state: 'visible', timeout: 15000 });
  const modalStarted = Date.now();
  await trigger.click();
  await page.locator('#bugdrop-host .bd-modal').waitFor({ state: 'visible', timeout: 5000 });
  await page.locator('#bugdrop-host .bd-close').waitFor({ state: 'visible', timeout: 5000 });
  const modalOpenMs = Date.now() - modalStarted;

  const result = await page.evaluate(() => {
    const script = [...document.scripts].find((candidate) =>
      candidate.src.includes('bugdrop.neonwatty.workers.dev'),
    );
    const host = document.querySelector('#bugdrop-host');
    const textFields = [...document.querySelectorAll(
      'input:not([type="hidden"]):not([type="submit"]):not([type="button"]), textarea, [contenteditable="true"]',
    )];

    return {
      widgetReady: Boolean(window.BugDrop),
      hostPresent: Boolean(host),
      triggerPresent: Boolean(host?.shadowRoot?.querySelector('.bd-trigger')),
      modalPresent: Boolean(host?.shadowRoot?.querySelector('.bd-modal')),
      closePresent: Boolean(host?.shadowRoot?.querySelector('.bd-close')),
      welcomeSkipped: !host?.shadowRoot?.querySelector('.bd-modal')?.textContent.includes('Get Started'),
      scriptSrc: script?.src || '',
      repo: script?.dataset.repo || '',
      label: script?.dataset.label || '',
      position: script?.dataset.position || '',
      async: Boolean(script?.async),
      defer: Boolean(script?.defer),
      textFields: textFields.length,
      maskedTextFields: textFields.filter((field) => field.hasAttribute('data-bugdrop-mask')).length,
    };
  });

  const expected = {
    scriptSrc: 'https://bugdrop.neonwatty.workers.dev/widget.v1.56.4.js',
    repo: 'Bakhtiar76/EasyQuranClassesWebsite',
    label: 'Report a website bug',
    position: 'bottom-left',
  };
  for (const [key, value] of Object.entries(expected)) {
    if (result[key] !== value) {
      throw new Error(`Unexpected ${key}: expected ${value}, received ${result[key] || '(empty)'}`);
    }
  }
  if (!result.widgetReady || !result.hostPresent || !result.triggerPresent || !result.modalPresent || !result.closePresent) {
    throw new Error('BugDrop did not expose its documented ready/host/trigger/modal/close contract');
  }
  if (!result.welcomeSkipped) {
    throw new Error('BugDrop still shows the extra welcome step');
  }
  if (result.async || result.defer) {
    throw new Error('BugDrop script must not use async or defer');
  }
  if (result.textFields !== result.maskedTextFields) {
    throw new Error(`Only ${result.maskedTextFields}/${result.textFields} text-entry fields are masked`);
  }
  if (failedRequests.length || pageErrors.length) {
    throw new Error(JSON.stringify({ failedRequests, pageErrors }));
  }
  if (!workerResponses.some(({ path, status }) => path.startsWith('/api/check/') && status === 200)) {
    throw new Error(`BugDrop did not confirm repository installation: ${JSON.stringify(workerResponses)}`);
  }
  const installationChecks = workerResponses.filter(({ path }) => path.startsWith('/api/check/'));
  if (installationChecks.length !== 1) {
    throw new Error(`Expected one warmed installation check, received ${installationChecks.length}`);
  }
  if (modalOpenMs > 500) {
    throw new Error(`BugDrop modal took ${modalOpenMs}ms to open after warm-up`);
  }

  console.log(JSON.stringify({ ok: true, target: TARGET.href, modalOpenMs, ...result, workerResponses }, null, 2));
} finally {
  await browser.close();
}
