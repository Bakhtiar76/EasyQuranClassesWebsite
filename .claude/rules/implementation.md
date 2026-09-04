# Implementation Rules

These rules apply to every implementation task.

## Mandatory task TODO

Before editing, create a TODO list that includes:

- scan the repo/relevant project state;
- identify reusable code/features/libraries;
- explain briefly when new implementation is necessary;
- define the smallest correct implementation;
- identify correct file ownership/location;
- define relevant edge cases/tests;
- implement minimal changes;
- test changed code and edge cases;
- remove dead/unused code made obsolete by the task;
- update docs/checklists and durable project knowledge;
- review Git diff/secrets;
- verify the result.

## Reuse first

Before implementation, inspect whether an existing code block, WordPress/Elementor capability, helper, hook, template, style, plugin, library, or utility can be reused. Prefer reuse over duplication. If nothing suitable exists, state why a new implementation is needed.

## Minimal implementation

Use the smallest maintainable solution that satisfies the requirement. Prefer native WordPress, Elementor Free, browser APIs, or existing dependencies. Do not add speculative abstractions, parallel systems, or unnecessary libraries.

## Correct ownership

Every logic block must live in the appropriate existing file/location. Presentation belongs in the child theme; durable business functionality belongs in a site-specific plugin; Elementor owns page content/composition; Claude configuration belongs under project Claude files. Do not create empty, placeholder, duplicate, or unnecessary files/folders.

## Testing

Test every new/modified code path against relevant normal and edge cases. Do not claim tests passed when they were not run.

## Dead code

Remove temporary/debug code and code made obsolete by the implementation. If interfering dead code is encountered within scope, safely reuse/fix/remove it now rather than leaving a pending duplicate. Do not perform unrelated repository-wide cleanup.

## Documentation

After each meaningful iteration, update affected docs and explicit checklists. Keep `CLAUDE.md` current with durable project knowledge; do not turn it into a task log. `CLAUDE.md` must never exceed 1000 lines. Use rules/skills/dedicated docs for detailed procedures.

## Output quality

Do not add filler or restating comments, emoji in code or commit messages, placeholder/scaffold code not requested, or documentation nobody asked for. Comments explain why, not what the code already says.
