# Troubleshooting Guide

This document covers known issues, common errors, and their fixes for the Smart Store Pro plugin.

## Build Issues

### Issue: "Cannot find module" after adding new block

**Symptoms:**
- Module not found errors in webpack
- Import paths not resolving

**Cause:**
- Block not added to `blocks/blocks/index.js`
- Alias not configured in webpack config

**Fix:**
```bash
# 1. Add import to blocks/blocks/index.js
import './{tier}/{block-name}/index.jsx';

# 2. Rebuild
npm run build:blocks
```

### Issue: PHP block not registering

**Symptoms:**
- Block appears in editor but doesn't render
- Server-side PHP errors

**Cause:**
- Block not in `BLOCK_MAPPING` in `scripts/generate-blocks-php.js`
- `blocks.php` not regenerated after `block.json` change

**Fix:**
```bash
# 1. Add to BLOCK_MAPPING
# Edit scripts/generate-blocks-php.js
'element/new-block': 'new-block',

# 2. Regenerate blocks.php
npm run build:blocks-php

# 3. Register in BlockManager
# Edit src/Includes/BlockManager.php
$this->register_block('ShapedPlugin\\WooProductSliderPro\\Blocks\\Element\\NewBlock');
```

### Issue: CSS not applying in editor

**Symptoms:**
- Styles work in preview but not in editor
- Dynamic CSS not updating

**Cause:**
- `dynamicCss.js` not returning correct format
- Missing `uniqueId` attribute
- `style.scss` not imported

**Fix:**
```jsx
// 1. Ensure dynamicCss.js returns array
export default (props) => {
    return [
        {
            class: `.spssp-unique-id-${props.attributes.uniqueId}`,
            styles: {
                color: props.attributes.color,
            },
        },
    ];
};

// 2. Import in index.jsx
import './editor.scss';
import dynamicCss from './dynamicCss';

// 3. Pass to edit component
<Edit dynamicCss={dynamicCss} {...otherProps} />
```

### Issue: CSS mismatch between editor and frontend

**Symptoms:**
- Editor preview looks different from live site
- Spacing, colors, fonts don't match

**Cause:**
- PHP `*CssGenerator.php` not matching JS `dynamicCss.js`

**Fix:**
```php
// Ensure identical logic in both files

// JS (dynamicCss.js)
const fontSize = `${value}${unit}`;

// PHP (*CssGenerator.php)
$font_size = $value . $unit;
```

## Runtime Issues

### Issue: Swiper carousel not initializing

**Symptoms:**
- Carousel appears but doesn't scroll
- Navigation arrows don't work
- Console error: "Swiper is not defined"

**Cause:**
- Swiper script not enqueued
- Script load order issue
- Block missing `data-carousel-settings` attribute

**Fix:**
```bash
# 1. Check script is registered
# AssetManager.php should have:
$this->register_script('spssp-swiper', 'path/to/swiper.min.js');

# 2. Ensure block outputs data attribute
# Save.jsx should include:
<div data-carousel-settings={JSON.stringify(settings)}>
```

### Issue: REST API returns 404

**Symptoms:**
- Pagination fails to load
- Filters don't update
- Console 404 errors

**Cause:**
- REST route not registered
- Wrong namespace/version
- Permalink settings

**Fix:**
```php
// 1. Check route is registered
register_rest_route('wpspro/v1', '/endpoint', array(...));

// 2. Verify URL format
// Correct: /wp-json/wpspro/v1/endpoint
// Wrong: /wp-json/wpspro/endpoint (missing v1)

// 3. Flush rewrite rules
# In WP admin: Settings > Permalinks > Save Changes
```

### Issue: Product query returns wrong results

**Symptoms:**
- Wrong products showing
- Products not filtered correctly
- Pagination counts off

**Cause:**
- Query args malformed
- Taxonomy not properly formatted
- Category IDs incorrect

**Fix:**
```php
// Use ProductQuery class properly
$query = new ProductQuery($per_page);

$products = $query->get_products(array(
    'product_type' => 'latest',
    'categories' => array(1, 2, 3),  // IDs, not slugs
    'exclude' => array(4, 5),
));

// Debug query args
error_log('Query args: ' . print_r($args, true));
```

### Issue: Global settings not saving

**Symptoms:**
- Settings reset on page reload
- "Save Settings" button appears to work but no change

**Cause:**
- Permissions error
- Store not dirty flag issue
- REST endpoint failure

**Fix:**
```php
// 1. Check user capability
if (!current_user_can('manage_options')) {
    return new WP_Error('permission_denied', ...);
}

// 2. Verify option update
$updated = update_option('sp_wpspro_global_settings', $settings);
if (!$updated) {
    error_log('Failed to save global settings');
}

// 3. Check for conflicts
# Deactivate other plugins that might interfere
```

## Editor Issues

### Issue: Block not appearing in inserter

**Symptoms:**
- Block not found when searching
- Block in different category than expected

**Cause:**
- `block.json` category mismatch
- Block registration error
- Editor cache

**Fix:**
```json
// 1. Check block.json
{
    "name": "sp-smart-store/block-name",
    "category": "smart-store-blocks",
    "apiVersion": 3
}

// 2. Clear browser cache
// Hard refresh: Ctrl+Shift+R (Cmd+Shift+R on Mac)

// 3. Clear transients
# Run: wp transient delete --all
```

### Issue: Attributes not saving

**Symptoms:**
- Changes lost on save
- Default values persist
- Inspector controls don't update

**Cause:**
- Attribute type mismatch
- Missing in `block.json`
- Not calling `setAttributes`

**Fix:**
```jsx
// 1. Ensure attribute in block.json
{
    "attributes": {
        "myAttribute": {
            "type": "string",
            "default": ""
        }
    }
}

// 2. Use setAttributes correctly
const onChange = (value) => {
    setAttributes({ myAttribute: value });
};

// 3. Check for conflicting extensions
// Disable other block plugins to test
```

### Issue: InnerBlocks not rendering

**Symptoms:**
- Parent block shows but no child blocks
- Cannot add inner blocks
- Template not loading

**Cause:**
- `allowedBlocks` too restrictive
- `templateLock` preventing insertion
- Context not provided

**Fix:**
```jsx
// 1. Check allowedBlocks
<InnerBlocks
    allowedBlocks={['sp-smart-store/child-1', 'sp-smart-store/child-2']}
/>

// 2. Adjust templateLock
// "all" - no changes
// "insert" - can insert but not move/delete
// false - full control
<InnerBlocks templateLock={false} />

// 3. Verify providesContext
registerBlockType('sp-smart-store/parent', {
    providesContext: {
        contextKey: 'value',
    },
});
```

## Performance Issues

### Issue: Slow page load

**Symptoms:**
- Long load times
- High CPU usage
- Console timing warnings

**Cause:**
- Too many queries per page
- Unoptimized images
- Excessive dynamic CSS

**Fix:**
```php
// 1. Use object caching
$cached = wp_cache_get($key);
if ($cached === false) {
    $cached = expensive_operation();
    wp_cache_set($key, $cached, '', 3600);
}

// 2. Lazy load products
// Implement pagination instead of loading all at once

// 3. Optimize CSS
// Combine media queries
// Remove unused selectors
```

### Issue: Large bundle size

**Symptoms:**
- Editor loads slowly
- Bundle > 1MB warning
- Memory issues

**Cause:**
- Unoptimized dependencies
- Dead code
- No tree shaking

**Fix:**
```bash
# 1. Analyze bundle
npm run build:blocks
# Check assets/editor/index.js size

# 2. Remove unused imports
# Only import what's needed

# 3. Use dynamic imports
const Component = lazy(() => import('./HeavyComponent'));
```

## Integration Issues

### Issue: WooCommerce not detected

**Symptoms:**
- Admin notice: "WooCommerce not active"
- Blocks don't render products

**Cause:**
- WooCommerce plugin inactive
- Version compatibility issue
- Wrong plugin directory

**Fix:**
```bash
# 1. Activate WooCommerce
wp plugin activate woocommerce

# 2. Check version compatibility
# Minimum: WooCommerce 5.0.0

# 3. Check detection
# In WooProductSliderPro.php
if (!class_exists('WooCommerce')) {
    return;
}
```

### Issue: Theme conflicts

**Symptoms:**
- Styles not applying
- Layout broken
- Z-index conflicts

**Cause:**
- Theme CSS specificity higher
- Conflicting class names
- Missing !important declarations

**Fix:**
```css
/* 1. Increase specificity */
.spssp-wrapper .spssp-block {
    color: red;
}

/* 2. Use !important sparingly */
.spssp-block {
    color: red !important;
}

/* 3. Scope all styles */
.spssp-unique-id-{uniqueId} {
    /* Scoped styles here */
}
```

### Issue: Plugin conflicts

**Symptoms:**
- Blocks not working with other plugins
- JavaScript errors
- PHP fatal errors

**Cause:**
- Hook priority conflicts
- Same function names
- Asset loading conflicts

**Fix:**
```php
// 1. Use unique prefixes
function spssp_my_function() {
    // Prefix prevents conflicts
}

// 2. Adjust hook priority
add_action('init', array($this, 'init'), 99);

// 3. Check for existing hooks
if (has_action('some_hook')) {
    // Handle gracefully
}
```

## Development Issues

### Issue: Hot reload not working

**Symptoms:**
- Need to refresh after changes
- Live update not happening

**Cause:**
- Not using `npm start`
- Webpack HMR disabled
- Browser cache

**Fix:**
```bash
# 1. Use dev mode
npm run start

# 2. Check webpack config
# Should have HMR enabled

# 3. Clear browser cache
# Disable cache during development
```

### Issue: Source maps not loading

**Symptoms:**
- Console points to compiled code
- Cannot debug original source

**Cause:**
- Source maps stripped
- Wrong path
- Build in production mode

**Fix:**
```bash
# 1. Check build mode
# npm run build strips maps
# npm run start keeps them

# 2. Verify output
# Check for .map files in assets/

# 3. Check webpack config
devtool: isProduction ? false : 'source-map'
```

## Common Error Messages

### "Call to undefined function get_block_wrapper_attributes"

**Fix:**
- Ensure WordPress 5.6+ is active
- Check for `@wordpress/block-editor` import

### "Cannot read properties of undefined (reading 'blockName')"

**Fix:**
- Check `block.json` exists
- Verify block registration completes before render
- Add null checks

### "Fatal error: Class not found"

**Fix:**
- Check namespace matches file path
- Verify PSR-4 autoloading
- Run composer dump-autoload

### "Warning: array_merge(): Argument #2 is not an array"

**Fix:**
- Verify default attributes are arrays
- Add type checking in render method
- Use wp_parse_args instead

## Debugging Tips

### Enable WP_DEBUG

```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', true);
```

### Console Debugging

```javascript
// Log block state
console.log('Block attributes:', attributes);
console.log('Block context:', context);

// Log API responses
console.log('REST response:', data);

// Log performance
console.time('Render');
// ... render code ...
console.timeEnd('Render');
```

### Network Debugging

1. Open browser DevTools Network tab
2. Filter by "wpspro" to see plugin requests
3. Check request/response payloads
4. Look for failed requests (red status)

### Query Debugging

```php
// Add to functions.php
add_action('wp', function() {
    global $wp_query;
    echo '<pre>';
    print_r($wp_query->query_vars);
    echo '</pre>';
});
```

## Support Resources

### Internal Documentation

- Block patterns: `.claude/skills.md`
- Architecture: `.claude/architecture.md`
- Conventions: `.claude/conventions.md`

### External Resources

- Gutenberg Handbook: https://developer.wordpress.org/block-editor/
- WooCommerce REST API: https://woocommerce.github.io/woocommerce-rest-api-docs/
- WP-Scripts: https://github.com/WordPress/gutenberg/tree/trunk/packages/scripts

### Getting Help

1. Check this troubleshooting guide first
2. Search existing issues in repo
3. Enable debug logging
4. Provide detailed error messages
5. Include environment details (WP version, PHP version, browser)
