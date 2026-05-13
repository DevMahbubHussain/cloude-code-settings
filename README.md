# Smart Store Pro — AI Documentation Setup

> Unified documentation for Claude Code, Cursor, and GitHub Copilot/Codex.

## Quick Start

1. **Copy the appropriate folder to your AI tool's config directory:**
   - `.claude/` → `~/.claude/` (for Claude Code)
   - `.cursor/` → `~/.cursorrules` or Cursor's rules location
   - `.codex/` → Include in your project root for Copilot

2. **Reference files:**
   - `reference-blocks/` — Working block examples (JS and PHP)
   - `BUGFIX_PLAN_SMART_CART.md` — Active bug fixes
   - `REFACTOR_PLAN_SMART_CART.md` — Active refactors

## File Structure

```
cloude-code-settings/
├── README.md                 # This file
├── common/                   # Shared patterns & reference
│   ├── patterns.md          # Code patterns (block registration, CSS, etc.)
│   ├── conventions.md       # Coding standards & naming
│   └── troubleshooting.md   # Common issues & fixes
├── claude/                   # Claude Code specific
│   ├── instructions.md      # Workflow & planning
│   ├── context.md          # Project context (CLAUDE.md equivalent)
│   └── skills.md          # Block patterns & DS usage
├── cursor/                   # Cursor specific
│   ├── .cursorrules        # Cursor rules file
│   └── setup.md           # Cursor setup instructions
├── codex/                    # Codex/Copilot specific
│   ├── .copilot-instructions.md  # Copilot instructions
│   └── setup.md           # Codex setup instructions
└── reference-blocks/          # Working examples
    ├── featured-image/      # JS block example
    └── FeaturedImage/      # PHP block example
```

## Plugin Overview

**Smart Store Pro** — Gutenberg block plugin for WooCommerce (~50 blocks).

- `blocks/` — JS/JSX (editor, save, dynamic CSS, view scripts)
- `src/` — PHP PSR-4 classes (server render, attributes, CSS)

**Block Tiers:**
- `element/*` — Atomic fields (image, title, price, rating, add-to-cart)
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

## Critical Rules

1. **Security**: All inputs sanitized, all outputs escaped
2. **i18n**: Always use `'woo-product-slider-pro'` text domain
3. **CSS Parity**: `dynamicCss.js` and `*CssGenerator.php` must match
4. **Block Registration**: Add to `BLOCK_MAPPING`, run `build:blocks-php`, register in `BlockManager`
5. **Planning First**: 3+ files or new architecture → plan before coding

## Common Issues

| Issue | Fix |
|-------|------|
| Block not registering | Add to BLOCK_MAPPING, run `npm run build:blocks-php` |
| Module not found | Add import to `blocks/blocks/index.js` |
| CSS not applying | Check uniqueId scoping, verify dynamicCss.js matches CssGenerator.php |
| REST API 404 | Check namespace is `wpspro/v1`, flush permalinks |
