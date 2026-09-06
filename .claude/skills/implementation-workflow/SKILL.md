---
name: implementation-workflow
description: Enforce reuse-first, minimal-code, TODO, testing, cleanup and documentation workflow on every implementation task.
---
# Implementation Workflow

Start with the mandatory TODO from `.claude/rules/implementation.md`.

Order:
1. inspect;
2. find reusable implementation;
3. state why new code is needed if applicable;
4. choose smallest maintainable approach;
5. identify correct file/location;
6. define edge cases;
7. implement focused change;
8. test normal + relevant edge cases;
9. remove temporary/dead/duplicate code within scope;
10. update docs/checklists/CLAUDE durable state;
11. review Git diff;
12. report verification/manual checks.
