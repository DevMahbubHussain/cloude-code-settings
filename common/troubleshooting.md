# Common Issues & Fixes

## Build Issues

| Issue | Symptoms | Fix |
|--------|-----------|-----|
| Module not found | Import error after adding block | Add import to `blocks/blocks/index.js`, run `npm run build:blocks` |
| Block not registering | Block in editor, doesn't render | 1. Add to `BLOCK_MAPPING` in `generate-blocks-php.js`<br>2. Run `npm run build:blocks-php`<br>3. Register in `BlockManager.php` |
| CSS not applying | Styles work in preview, not editor | 1. Import `./editor.scss` in index.jsx<br>2. Check `uniqueId` attribute exists<br>3. Verify `dynamicCss.js` returns array |
| CSS mismatch | Editor ≠ Frontend | Ensure `dynamicCss.js` and `*CssGenerator.php` produce identical output |

## Runtime Issues

| Issue | Symptoms | Fix |
|--------|-----------|-----|
| Swiper not initializing | Carousel doesn't scroll, arrows broken | 1. Check script registered in AssetManager<br>2. Verify `data-carousel-settings` attribute |
| REST API 404 | Pagination/filters fail | 1. Check namespace is `wpspro/v1`<br>2. Flush permalinks (Settings > Permalinks) |
| Wrong products | Query returns incorrect results | Use `ProductQuery` class, pass IDs not slugs |
| Global settings not saving | Settings reset on reload | 1. Check `current_user_can('manage_options')`<br>2. Verify `update_option()` returns true |

## Editor Issues

| Issue | Symptoms | Fix |
|--------|-----------|-----|
| Block not in inserter | Block not found when searching | 1. Check `block.json` category<br>2. Hard refresh (Ctrl+Shift+R)<br>3. Run `wp transient delete --all` |
| Attributes not saving | Changes lost on save | 1. Ensure attribute in `block.json`<br>2. Use `setAttributes({ attr: value })` |
| InnerBlocks not rendering | Parent shows, no children | 1. Check `allowedBlocks` list<br>2. Adjust `templateLock`<br>3. Verify `providesContext` |

## Performance Issues

| Issue | Symptoms | Fix |
|--------|-----------|-----|
| Slow page load | Long load times | 1. Use object caching<br>2. Lazy load products<br>3. Optimize CSS |
| Large bundle | Editor loads slowly, >1MB | 1. Analyze bundle size<br>2. Remove unused imports<br>3. Use dynamic imports |

## Quick Commands

```bash
# After block.json change
npm run build:blocks-php

# After adding new block to index.js
npm run build:blocks

# Clear WordPress transients
wp transient delete --all

# Flush permalinks
# In WP Admin: Settings > Permalinks > Save Changes
```

## When to File a Bug

Collect:
1. Error message (exact text)
2. Steps to reproduce
3. Browser console output
4. WordPress version
5. PHP version
