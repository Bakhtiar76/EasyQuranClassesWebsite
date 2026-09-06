---
name: plugin-evaluation
description: Evaluate a WordPress plugin before installing it locally or recommending it for production.
---
# Plugin Evaluation

Before installing, check:
- can WordPress/Elementor/current code already do it?;
- maintenance/update status and compatibility;
- permissions/data access;
- frontend/admin performance cost;
- overlap with existing plugins;
- free vs paid requirement;
- lock-in/exportability;
- security implications;
- whether it is needed on production or only local tooling.

Return one: `INSTALL`, `DO NOT INSTALL`, or `NEEDS APPROVAL`, with a brief reason.

Never install nulled/unknown plugins or add a plugin merely because it exists.
