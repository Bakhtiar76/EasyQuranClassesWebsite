---
description: Enforces the Easy Quran Classes implementation workflow: TODO first, reuse scan, minimal code, correct file ownership, edge-case testing, dead-code cleanup, docs updates and diff review. Use for every feature, fix, refactor or setup implementation.
---

# Implementation Workflow

Before editing, create the task TODO defined in `.claude/rules/implementation.md`.

1. Scan the relevant repository/project state.
2. Identify reusable code, styles, helpers, libraries, WordPress/Elementor features or configuration.
3. Reuse suitable existing implementation. If none fits, state why new code/config is necessary.
4. Define the smallest maintainable solution and correct file ownership.
5. Define relevant normal and edge-case checks before implementation.
6. Implement only the focused change.
7. Run targeted verification as you work; do not wait until the end to discover basic failures.
8. Remove temporary/debug/duplicate/dead code made obsolete by the task.
9. Update affected docs/checklists and durable `CLAUDE.md` knowledge.
10. Review Git status/diff, secrets/generated files and final verification.

Do not create empty/unnecessary files or speculative abstractions.
