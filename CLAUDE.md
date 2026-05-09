# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code Smart Store Pro Plugin.

## Plugin Overview

**Smart Store Pro** — a block-only WordPress plugin that ships ~50 Gutenberg blocks under the `sp-smart-store/*` namespace for composing WooCommerce storefronts: product grids, carousels, smart carts, advanced product filters, and full template-builder layouts. WooCommerce must be active; the plugin shows an admin notice and short-circuits otherwise.

The codebase has two halves that mirror each other:
- `blocks/` — JS/JSX block source (editor UI, save markup, dynamic CSS, frontend view scripts).
- `src/` — PHP block classes (server-side render, attribute schemas, frontend CSS generation), autoloaded via PSR-4.

## Common Commands

Build & dev (Node + WP-Scripts toolchain):

- `npm run start` — dev mode for the block editor bundle + Gulp SCSS watcher (concurrent).
- `npm run build` — full production build: regenerates `src/Blocks/blocks.php`, lints, then builds blocks + admin + frontend bundles.
- `npm run build:blocks` — editor bundle only (`assets/editor`) + Gulp styles.
- `npm run build:admin` — dashboard SPA bundle (`assets/dashboard`).
- `npm run build:frontend` — frontend interactivity bundle (`assets/js`).
- `npm run build:blocks-php` — regenerates `src/Blocks/blocks.php` from every `block.json`. **Run this whenever a `block.json` changes**; PHP block registration reads the generated file, not the JSON directly.
- `npm run build:css` / `watch:css` — Gulp-only SCSS pipeline for the SCSS files that aren't imported by JS (see `gulpfile.js` for the explicit list).
- `npm run lint` / `npm run lint:fix` — ESLint over `blocks/` (the JS side; PHP is not linted here).
- `npm run build:pot` — regenerate the `.pot` translation template (requires `wp-cli`).

There is no test suite.

## Architecture

### Two-tier build pipeline
- `wp-scripts` (webpack) handles `blocks/` JS/JSX and the SCSS that's `import`ed from JS. `webpack.config.js` extends the default WP config to swap `style-loader` for `MiniCssExtractPlugin` (so styles emit as files), and registers the `@woo-product-slider-pro/*` aliases.
- `gulpfile.js` compiles a small allowlist of standalone SCSS entry points (admin dashboard styles, frontend `style.scss`) that aren't reachable from any JS entry. New SCSS files only get processed if they're imported from JS *or* added to `allScssFiles` in `gulpfile.js`.
- `BUILD_TARGET=blocks|admin` env var is checked inside the build; not all webpack rules fire for both targets.

### `block.json` → `blocks.php` codegen
`scripts/generate-blocks-php.js` walks the directories listed in its `BLOCK_MAPPING` and serializes each `block.json` into `src/Blocks/blocks.php` as a PHP array literal. **Adding a new block requires editing `BLOCK_MAPPING` in this script** — otherwise PHP registration silently skips it. Do not hand-edit `src/Blocks/blocks.php`; the header says "auto-generated" and a build will overwrite it.

### PHP block architecture
- PSR-4 autoload from `src/` (see `composer.json`). Composer is used only for autoloading; there are no runtime PHP deps.
- `src/Includes/WooProductSliderPro.php` is the bootstrap. `init_advanced_blocks()` wires the asset manager, block manager, REST API (`Includes/REST_API.php`), advanced-filter REST handler (`Includes/AdvancedFilterHandler.php`), template builder, and SmartCart endpoint.
- `src/Includes/BlockManager::register_all_blocks()` is hooked at `init` priority 20 and explicitly `register_block()`s each block class. Blocks extend `src/Blocks/AbstractBlock.php` (or `AbstractAdvancedBlock.php` for builder-tier blocks). Each block class must define `$block_name` and a `render()` method; `AbstractBlock::register_block_type()` reads metadata from `blocks.php` and either registers fresh or attaches a `render_callback` to a JS-pre-registered block.
- All blocks use the same bundled editor script handle: `spssp-blocks-editor` (output is `assets/editor/index.js`).
- Each block lives in `src/Blocks/<Tier>/<BlockName>/` with a `Block.php`, `*Attributes.php` (centralized attribute definitions, often used to seed defaults on the PHP side), and `*CssGenerator.php` for server-rendered dynamic CSS.

### JS block architecture
- Entry points: `blocks/blocks/index.js` (editor — registers every block via `registerBlockType`), `blocks/blocks/frontend.js` (interactivity / view scripts), `blocks/admin/index.js` (dashboard SPA).
- Block source layout under `blocks/blocks/{element,smart,showcase,builder}/<block>/`: `index.jsx` registers, `edit.jsx` is the editor view, `save.jsx` outputs static markup (or returns null for dynamic blocks), `block.json` is metadata, `dynamicCss.js` mirrors the PHP CSS generator for live preview, plus optional `tabs.jsx`, `inspectorControls.jsx`, `view.js`.
- Block tiers (mirror the PHP namespace structure):
  - `element/*` — atomic fields rendered inside a product template (image, title, price, rating, add-to-cart, etc.).
  - `smart/*` — generic layout primitives (container, heading, paragraph, button).
  - `showcase/*` — composite blocks (product-grid, product-carousel, smart-pagination, smart-buttons, smart-cart with many inner blocks, product-template).
  - `builder/*` — full-page builder blocks (advanced-product-filters with per-filter inner blocks, breadcrumb, archive-title/description, product-description, product-review).
- Webpack aliases (use these instead of relative paths when reaching across blocks):
  - `@woo-product-slider-pro/components` → `blocks/components`
  - `@woo-product-slider-pro/common` → `blocks/common`
  - `@woo-product-slider-pro/icons` → `blocks/icons`
  - `@woo-product-slider-pro/controls` → `blocks/controls`
  - `@woo-product-slider-pro/utils` → `blocks/blocks/utils`
  - `@woo-product-slider-pro/hooks` → `blocks/hooks`

### Dynamic CSS pattern
For block visual settings: the JS `dynamicCss.js` and the PHP `*CssGenerator.php` must produce equivalent output (the JS one feeds the editor live preview; the PHP one renders frontend CSS). Selectors are `uniqueId`-scoped (`.spssp-unique-id-{uniqueId}`). Helpers live in `blocks/blocks/shared/helpFn.js` (JS) and `src/Blocks/CssHelper.php` (PHP).

### Global settings
`blocks/global-settings/` is a separate JS module bundled into the editor (registered via `import '../global-settings/index.js'` from `blocks/blocks/index.js`). It owns plugin-wide typography, color, button, shadow, and breakpoint presets that individual blocks consume through `store.js`/`hooks.js`.

### Frontend interactivity
Frontend bundle (`blocks/blocks/frontend.js` → `assets/js`) uses `@wordpress/interactivity` plus Swiper for sliders. Source maps are stripped post-build.

## Code Style

- ESLint extends `@wordpress/eslint-plugin/recommended` with the local overrides in `.eslintrc.js`. Notable: tabs for indentation, single quotes (`avoidEscape: true`), `quote-props: as-needed`, `space-before-function-paren: never` for named functions, i18n text-domain locked to the plugin slug. `src/`, `assets/`, `vendor/`, `node_modules/`, `*.min.js` are ignored.
- jsconfig is configured for the same path aliases — keep `webpack.config.js`, `.eslintrc.js` `import/resolver`, and `jsconfig.json` in sync when adding aliases.

## Adding a New Block (checklist)

1. Setup & Registration

1. Create blocks/blocks/<tier>/<name>/block.json (namespace: sp-smart-store/<name>).
2. Add directory → key entry to BLOCK_MAPPING in scripts/generate-blocks-php.js.
3. Run npm run build:blocks-php to generate the PHP registration array.

### Editor-Side Component (React / JavaScript)
1. Create blocks/blocks/<tier>/<name>/index.jsx (register block).
2.  Add the import to blocks/blocks/index.js.
3.  Create edit.jsx (editor view). Must use components from @woo-product-slider-pro/components and @woo-product-slider-pro/controls.
4. Create save.jsx (static markup, or return null for dynamic blocks).
5. Create dynamicCss.js (live editor CSS preview, scoped by uniqueId).
6. Create inspectorControls.jsx and/or tabs.jsx if settings are complex.
7. Create view.js if block has frontend interactivity (using @wordpress/interactivity).
8. Example reference: See how Breadcrumb,ProductGrid,AdvancedTab block uses components from /components.

###  Server Side (PHP)
1. Create src/Blocks/<Tier>/<Name>/Block.php extending AbstractBlock or AbstractAdvancedBlock. Define $block_name and render() method.
2. Register the class in BlockManager::register_all_blocks().
3. Create {BlockName}Attributes.php. Reuse common attributes (spacing, colors, borders) from CommonAttributes.php. Only use 4. AttributeGenerators.php if custom logic is needed.
5. Create {BlockName}CssGenerator.php. Must output valid CSS string identical to dynamicCss.js logic.
6. Rule: Must accept attributes and output valid CSS string. Use existing CSS generator classes as a reference.

