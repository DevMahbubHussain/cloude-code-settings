# Claude Code Workflow Instructions

## Task Classification

**Implement Directly:**
- Single file changes (bug fixes, small tweaks)
- Adding a single line/function
- Configuration updates
- File renames/moves

**Plan First:**
- New block creation (3+ files)
- Architecture changes
- Multi-file refactoring
- New features requiring decisions

## Planning Protocol

When planning is needed:

1. **Read context first**: `../common/patterns.md`, `../common/conventions.md`
2. **Study 2 reference blocks** matching the new block's tier
3. **Output plan with**:
   - All files to create/modify (full paths)
   - Block namespace and tier decision
   - block.json attribute schema
   - Context needs (usesContext/providesContext)
   - Data fetching approach (useSelect vs static)
   - Build steps required

4. **Wait for approval** before writing code

## File Creation Checklist

**For new blocks:**
- [ ] block.json (correct namespace, category, attributes)
- [ ] index.jsx (registerBlockType)
- [ ] edit.jsx (editor UI with useBlockProps)
- [ ] save.jsx (null for dynamic, markup for static)
- [ ] dynamicCss.js (CSS scoped by uniqueId)
- [ ] Import added to blocks/blocks/index.js
- [ ] Block.php (extends AbstractBlock, render method)
- [ ] *Attributes.php (attribute defaults)
- [ ] *CssGenerator.php (matches dynamicCss.js)
- [ ] Registered in BlockManager::register_all_blocks()
- [ ] Added to BLOCK_MAPPING in generate-blocks-php.js
- [ ] Ran npm run build:blocks-php

## Common Patterns

**Element Block (atomic field):**
```jsx
usesContext: ['postId']  // Uses postId, renders single product field
// Example: product-image, product-title, product-price
```

**Showcase Block (with query):**
```jsx
const { products } = useSelect(select => {
    return select('wpspro/global-settings').getProducts(attrs);
}, [attrs]);
// Example: product-grid, product-carousel
```

**Builder Block (page component):**
```jsx
providesContext: { productId: 'productId' }  // Provides context
// Example: breadcrumb, archive-title
```

## Edge Cases to Handle

1. **Missing product**: Return empty on frontend, show notice in admin
2. **No featured image**: Use `wc_placeholder_img()`
3. **WooCommerce inactive**: Block renders nothing
4. **Empty inner blocks**: Show placeholder in editor
5. **Product not found**: Graceful degradation
6. **CSS parity**: Ensure JS and PHP generators match exactly

## Debugging

Add debug logs for product ID retrieval:
```jsx
useEffect(() => {
    console.log('Block Debug:', {
        contextPostId: context?.postId,
        attributeProductId: attributes?.productId,
        finalPostId: postId,
        productFound: !!product,
    });
}, [postId, product]);
```

## Testing Checklist

- [ ] Block appears in inserter
- [ ] Renders correctly in editor
- [ ] Attributes save and persist
- [ ] Frontend renders correctly
- [ ] Dynamic CSS matches preview
- [ ] Console has no errors
- [ ] Works in intended contexts (loop, template, single)

## Golden Rule

For anything that touches 3+ files or involves new architecture → **ALWAYS PLAN FIRST**.

The 2 minutes you spend reviewing a plan saves 20 minutes of undoing bad code.
