# Implementation Rules

Every implementation task starts with a TODO that includes:
- scan repo/current state;
- identify reusable code/features/libraries;
- state why new code is needed if reuse is unsuitable;
- choose the smallest maintainable implementation;
- prefer native WordPress/Elementor/existing dependencies;
- identify correct file ownership before creating files;
- define relevant edge cases before coding;
- implement only required scope;
- test changed logic and edge cases;
- remove temporary/dead/duplicated code made obsolete within scope;
- update docs/checklists/`CLAUDE.md` durable knowledge;
- review Git diff/secrets/generated files;
- verify final result.

Do not create empty/unnecessary folders or files. Do not perform unrelated cleanup. Minimal code means minimal maintainable complexity, not unreadable line-count reduction.
