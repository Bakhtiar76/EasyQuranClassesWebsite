---
description: Diagnoses WordPress/Elementor/PHP/frontend issues using a smallest-cause-first workflow without exposing secrets or enabling unsafe production debugging. Use when something is broken or behaving unexpectedly.
---

# WordPress Debugging

1. Confirm local/staging/production and reproduce the issue.
2. Inspect the smallest relevant surface first: browser console/network for frontend, WordPress/PHP logs for backend, WP-CLI read checks for state.
3. Do not expose credentials or enable `WP_DEBUG_DISPLAY` on production.
4. Check evidence for plugin/theme conflicts before disabling anything.
5. Identify the smallest root cause; avoid speculative bulk changes.
6. Reuse/fix existing implementation when possible.
7. Implement the smallest fix in the correct ownership layer.
8. Test normal behavior and relevant edge cases.
9. Remove temporary logging/debug code.
10. Review diff and report verification/manual checks.
