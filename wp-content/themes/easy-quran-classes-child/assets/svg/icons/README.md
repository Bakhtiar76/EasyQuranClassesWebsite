# Generated icon SVGs — do not edit, do not add files here

Every file in this directory is written by
`tools/graphics/build-icon-sprite.mjs` from the same `ICONS` array that
produces `inc/icon-sprite.php`, so the files and the sprite can never drift.

**The generator deletes any `.svg` here that is not a current icon.** Dropping
artwork into this folder will silently lose it on the next build. Put new or
replacement artwork in `tools/graphics/source-icons/` instead and add it to
the generator's `SUPPLIED` map.

To change an icon: edit its geometry in `tools/graphics/build-icon-sprite.mjs`
(drawn icons) or `tools/graphics/source-icons/` (supplied artwork), then run
`node tools/graphics/build-icon-sprite.mjs`.
