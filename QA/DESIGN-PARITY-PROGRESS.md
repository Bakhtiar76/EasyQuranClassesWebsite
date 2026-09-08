# Design parity execution

Local target: `http://localhost`; branch: `feature/setup`. Supervisor: Codex; bounded harness assistant: Claude Code `claude-opus-5`. Existing user reference-image replacements and `local_SS/` are preserved.

## TODO / reuse / verification

- [x] Read task, project rules, design system, graphics pipeline and setup sections completely.
- [x] Inspect branch, working tree and installed local tooling.
- [x] Inspect local Docker and read-only WordPress state.
- [x] Extend existing screenshot harness; create one-command page rebuild/flush/sweep loop (PHP/JS syntax verified; first actual rebuild verification pending).
- [x] Capture all routes at 1920/1440/1024/768/390 before presentation changes, including Lighthouse baseline.
- [x] Global chrome: measured review, reuse plan, implementation, iterations, responsive/a11y/performance evidence (checkpoint being recorded).
- [ ] Home: measured review, reuse plan, implementation, iterations, evidence, commit.
- [ ] Courses: review/build/verify/commit.
- [ ] Pricing: review/build/verify/commit.
- [ ] Teachers: review/build/verify/commit.
- [ ] About: review/build/verify/commit.
- [ ] Contact: review/build/verify/commit.
- [ ] Free Trial: review/build/verify/commit.
- [ ] FAQ: review/build/verify/commit.
- [ ] Kids: review/build/verify/commit.
- [ ] Blog archive and single: review/build/verify/commit.
- [ ] Finish placeholder register, image brief, asset sources and accumulated lessons.
- [ ] Prove clean-bootstrap reproduction in isolated local volumes.
- [ ] Review final diff, remove newly obsolete code/assets and verify `main` untouched.

Reuse `tools/pages`, `elementor-helpers.php`, child-theme tokens/shell/components, SVG generators and `tests/visual/sweep.mjs`; each page review records exact ownership and any justified additions. Edge cases: 360–1920 intermediate widths, wrapping, image masks/loading, keyboard navigation, menu, forms/errors, carousel states, contrast and reduced motion. No extra framework or plugin is needed.

## Initial state

Docker was already running despite the task's dated startup note. Read-only audit: WordPress 7.1, PHP 8.3.33, child theme over Hello Elementor, Elementor/Fluent Forms/Novamira/Rank Math active, pretty permalinks enabled. WP-CLI reports environment `production` even though URL is localhost: the CLI service lacks the web service's `WORDPRESS_CONFIG_EXTRA`. Investigate and correct local runner context before page writes; do not interpret this as a remote site. Novamira emits three existing missing-ability registration errors in CLI; retain this baseline evidence and inspect browser impact.

Resolved CLI environment mismatch by matching the web service configuration. Saved `local/backups/pre-design-parity-20260909.sql`. Baseline55 full-page screenshots: zero capture/overflow/console/asset failures. Lighthouse baselines saved per route; desktop accessibility94–100, best practices100. Local noindex depresses SEO scores intentionally. `main` not checked out or modified.
