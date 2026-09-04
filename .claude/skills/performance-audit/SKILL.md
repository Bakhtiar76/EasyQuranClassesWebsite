---
description: Audits WordPress/Elementor performance and Core Web Vitals risk using browser and Lighthouse tooling when available, then recommends the smallest environment-aware fixes. Use after homepage build and before launch.
---

# Performance Audit

Do not install or configure a cache plugin before identifying the hosting/cache stack.

Inspect:

- server/cache/CDN clues available to the project
- LCP candidate and hero image behavior
- image dimensions/formats/compression
- lazy-loading placement
- font families/weights/loading
- render-blocking CSS/JS
- Elementor DOM complexity
- third-party scripts
- plugin bloat
- console/network failures
- caching headers where visible

Use the chrome-devtools MCP's `lighthouse_audit` and `performance_start_trace`/`performance_stop_trace`/`performance_analyze_insight` tools — no separate Lighthouse install needed for this project.

Separate recommendations into:

1. content/image fixes
2. Elementor/layout fixes
3. child-theme code fixes
4. plugin fixes
5. server/cPanel fixes

Do not recommend generic minification/caching changes that conflict with an existing host-level cache.
