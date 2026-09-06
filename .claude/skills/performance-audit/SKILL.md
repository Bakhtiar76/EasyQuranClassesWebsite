---
name: performance-audit
description: Audit local WordPress/Elementor performance and prepare production checks.
---
# Performance Audit

1. Measure before adding optimization plugins.
2. Inspect LCP image, font count/weights, image sizes/formats, Elementor DOM, third-party JS and plugin weight.
3. Use Lighthouse/Playwright locally when available.
4. Do not assume local cache behavior matches cPanel hosting.
5. After deployment, re-test production and account for server/LiteSpeed/CDN cache.
6. Add no cache plugin until cPanel hosting cache is known.
