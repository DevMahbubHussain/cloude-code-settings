# Reference Blocks

Working block examples for the Smart Store Pro plugin. Use these as templates when creating new blocks.

## Featured Image Block

A builder-tier block that wraps product-image with its own styling options.

### Structure

```
featured-image/          # JS side
├── block.json          # Block metadata
├── index.jsx           # Registration
├── edit.jsx            # Editor UI with InnerBlocks
├── save.jsx            # Returns null (dynamic)
├── dynamicCss.js       # Preview CSS
├── icons.jsx           # Icon SVG
├── inspectorControls.jsx # Inspector controls
└── tabs/
    └── generalTab.jsx   # Settings tab

FeaturedImage/          # PHP side
├── FeaturedImage.php    # Main block class
├── FeaturedImageAttributes.php # Attribute definitions
├── FeaturedImageCssGenerator.php # CSS generation
└── render.php          # Render template
```

### Key Patterns

- **InnerBlocks**: Restricts to only `sp-smart-store/product-image`
- **Template Lock**: `templateLock="all"` for fixed structure
- **Context**: Provides `uniqueId` to child blocks
- **Wrapper Styles**: Adds padding, margin, border to parent container

### Usage in New Blocks

When creating a new builder-tier block:

1. Copy the folder structure from this example
2. Update namespace in `block.json`: `sp-smart-store/your-block`
3. Update PHP class name and `$block_name`
4. Adjust `allowedBlocks` in InnerBlocks for your use case
5. Update context (`usesContext`/`providesContext`) as needed

## Other Reference Blocks

For more examples, explore the existing codebase:

- `blocks/blocks/element/product-image/` — Atomic field pattern
- `blocks/blocks/showcase/product-grid/` — Query/iteration pattern
- `blocks/blocks/builder/breadcrumb/` — Builder tier pattern
- `blocks/blocks/showcase/smart-cart/` — Complex InnerBlocks
