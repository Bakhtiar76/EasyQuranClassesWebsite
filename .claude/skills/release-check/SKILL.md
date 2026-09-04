---
description: Runs a pre-release checklist for Easy Quran Classes without deploying, checking Git state, backups, staging QA, SEO, forms, responsive behavior and rollback readiness. Use immediately before a production release.
---

# Release Readiness Check

This skill NEVER performs the production deployment.

Check:

- target release scope
- clean/reviewed Git state
- relevant diff
- staging implementation verified
- no secrets in tracked files
- no staging placeholders or test content intended for production
- responsive QA complete
- forms/email path tested
- key links tested
- SEO/indexability settings appropriate for production
- cache/CDN implications understood
- filesystem and DB backups confirmed
- rollback procedure documented

Return:

- GO / NO-GO
- blockers
- warnings
- exact proposed deployment steps
- exact rollback steps

Wait for separate explicit production approval.
