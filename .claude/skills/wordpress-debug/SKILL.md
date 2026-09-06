---
name: wordpress-debug
description: Debug local WordPress/Elementor issues safely and minimally.
---
# WordPress Debug

1. Reproduce on local site.
2. Check browser console/network for frontend issues.
3. Check local WordPress/PHP logs without exposing secrets.
4. Inspect plugin/theme conflict evidence; do not randomly disable production plugins.
5. Isolate smallest root cause.
6. Implement smallest fix in correct file.
7. Test relevant edge cases.
8. Remove temporary logs/debug code.
9. Re-run visual/functional check.

Never enable production error display as a debugging shortcut.
