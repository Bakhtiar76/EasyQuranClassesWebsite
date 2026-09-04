# Deployment Rules

- Git represents custom code, not the complete mutable WordPress site.
- Do not production-deploy without a verified rollback and current DB/filesystem backup.
- Prefer staging verification before production.
- Review `git status` and the relevant diff before a checkpoint/release.
- Never force-push unless the user explicitly requests it and risk is explained.
- Do not use `rsync --delete`, mass file deletion, DB import or search/replace as an implicit deployment shortcut.
- Production release is a separate approved operation, never an automatic follow-up to staging work.
