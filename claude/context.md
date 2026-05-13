# Smart Store Pro — Claude Code Context

## Plugin Overview
Gutenberg block plugin for WooCommerce storefronts (~50 blocks). Requires WooCommerce active.

```
blocks/          → JS/JSX (editor, save, dynamic CSS, view)
src/             → PHP PSR-4 classes (server render, attributes, CSS)
```

**Block Tiers:**
- `element/*` — Atomic fields (image, price, rating, add-to-cart)
- `smart/*` — Layout primitives (container, heading, button)
- `showcase/*` — Composites (product-grid, carousel, smart-cart)
- `builder/*` — Page components (advanced-filters, breadcrumb, product-review)

## Build Commands

```bash
npm run start          # Dev mode
npm run build          # Production build
npm run build:blocks   # Editor bundle only
npm run build:blocks-php # Regenerate src/Blocks/blocks.php from block.json
```

## Path Aliases

```
@woo-product-slider-pro/components  → blocks/components
@woo-product-slider-pro/common      → blocks/common
@woo-product-slider-pro/icons       → blocks/icons
@woo-product-slider-pro/controls    → blocks/controls
@woo-product-slider-pro/utils       → blocks/blocks/utils
@woo-product-slider-pro/hooks        → blocks/hooks
```

## Adding a Block

1. Create `blocks/blocks/<tier>/<name>/block.json` (namespace: `sp-smart-store/<name>`)
2. Add to `BLOCK_MAPPING` in `scripts/generate-blocks-php.js`
3. Run `npm run build:blocks-php`
4. Create JS files: `index.jsx`, `edit.jsx`, `save.jsx`, `dynamicCss.js`
5. Import in `blocks/blocks/index.js`
6. Create PHP: `src/Blocks/<Tier>/<Name>/Block.php`, `*Attributes.php`, `*CssGenerator.php`
7. Register in `src/Includes/BlockManager.php`

## Coding Rules

**Security:**
- PHP: `defined('ABSPATH') || exit;`
- Sanitize ALL inputs, escape ALL outputs
- `$wpdb->prepare()` only
- `current_user_can()` + `wp_verify_nonce()`

**React:**
- Use `@wordpress/*` packages
- `useBlockProps()` in edit, `useBlockProps.save()` in save
- No `dangerouslySetInnerHTML` with unsanitized content

**CSS:**
- BEM: `spssp-block__element--modifier`
- No `!important` except WP conflicts
- Breakpoints: 600px, 782px, 1080px
- Min touch target: 44×44px

**i18n:** Always use `'woo-product-slider-pro'`

**CSS Parity:** `dynamicCss.js` and `*CssGenerator.php` must match

## Reference Blocks

- `blocks/blocks/element/product-image/` — atomic field pattern
- `blocks/blocks/showcase/product-grid/` — query/iteration pattern
- `blocks/blocks/builder/breadcrumb/` — builder tier pattern
- `blocks/blocks/showcase/smart-cart/` — complex nested InnerBlocks

## Bug Fix Workflow

1. Read `BUGFIX_PLAN_SMART_CART.md`
2. Fix ONE finding ID at a time
3. Minimal changes only — no refactoring unrelated code
4. Add comment referencing finding ID
5. Update plan status to `[FIXED]`

## Refactor Workflow

1. Read `REFACTOR_PLAN_SMART_CART.md`
2. Implement ONE finding ID at a time
3. Preserve visual output — only change code quality
4. Add block deprecation if markup changes
5. Test: visual regression + cart functionality
