# Coding Standards and Conventions

This document outlines coding standards, naming conventions, and file structure patterns used in this project.

## File Naming Conventions

### JS/JSX Files

```
blocks/
├─ blocks/
│  ├─ {tier}/
│  │  └─ {block-name}/
│  │     ├─ index.jsx           (Always: block registration entry)
│  │     ├─ edit.jsx           (Editor component)
│  │     ├─ save.jsx           (Save component or return null)
│  │     ├─ block.json         (Block metadata)
│  │     ├─ dynamicCss.js     (Preview CSS generator)
│  │     ├─ style.scss        (Frontend styles)
│  │     ├─ editor.scss       (Editor styles)
│  │     ├─ icon.jsx          (Block icon SVG)
│  │     ├─ tabs.jsx          (Inspector tab definitions)
│  │     ├─ inspectorControls.jsx (Additional controls)
│  │     └─ view.js          (Frontend interactivity)
│  │     └─ inner-blocks/    (For parent blocks)
│  │        └─ {inner-block}/
│  │           └─ (same structure as parent)
└─ components/
   └─ PascalCase for component folders

PHP:
├─ CamelCase for file names within class directories
├─ PascalCase for class names
└─ snake_case for methods

Examples:
├─ ProductImage.php → class ProductImage
├─ product-grid.php → class ProductGrid
└─ get_products() (method)
```

### SCSS Files

```
├─ style.scss           (Frontend styles)
├─ editor.scss          (Editor styles)
└─ {feature}.scss     (Feature-specific styles)

Naming: kebab-case
```

## PHP Coding Standards

### Class Structure

```php
<?php
namespace ShapedPlugin\WooProductSliderPro\Blocks\Element\Example;

use ShapedPlugin\WooProductSliderPro\Blocks\AbstractBlock;
use ShapedPlugin\WooProductSliderPro\Blocks\CssHelper;

defined('ABSPATH') || exit;

/**
 * Short description.
 *
 * Longer description if needed.
 *
 * @package ShapedPlugin\WooProductSliderPro\Blocks\Element
 */
class ExampleBlock extends AbstractBlock {

    /**
     * Property description.
     *
     * @var string
     */
    protected $block_name = 'example-block';

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Method description.
     *
     * @param array $attributes Block attributes.
     * @param string $content Block content.
     * @param WP_Block $block Block instance.
     * @return string Rendered HTML.
     */
    public function render($attributes, $content, $block) {
        // Implementation
    }
}
```

### Method Naming

- `public render()` - Main render method (required)
- `protected get_block_content()` - Content generation helper
- `protected get_block_specific_attributes()` - Attribute definitions
- `public initialize()` - Setup hooks (optional)

### Variable Naming

```php
// Regular variables: snake_case
$product_id = get_the_ID();
$block_attributes = $attributes;

// Class properties: snake_case or camelCase
protected $block_name = 'example';
private $cache_key = '';

// Static constants: UPPER_SNAKE_CASE
const POST_TYPE = 'example_post';
const DEFAULT_LIMIT = 10;
```

## JSX/JavaScript Coding Standards

### Component Structure

```jsx
/**
 * Component description.
 *
 * @param {Object} props Component props.
 * @return {JSX.Element} Rendered component.
 */
const Edit = (props) => {
    const { attributes, setAttributes, clientId } = props;

    return (
        <div {...useBlockProps()}>
            {/* Content */}
        </div>
    );
};

export default Edit;
```

### Hook Usage

```jsx
// WordPress hooks
import { useSelect, useDispatch } from '@wordpress/data';
import { useBlockProps } from '@wordpress/block-editor';

const { blockProps } = useBlockProps();
const dispatch = useDispatch('core/block-editor');
const { updateBlockAttributes } = dispatch('core/block-editor');

// Custom hooks
import { useDefaultValue } from '@woo-product-slider-pro/hooks';

const defaultValue = useDefaultValue('defaultValueKey');
```

### Event Handlers

```jsx
// Inline handlers for simple operations
<button onClick={() => setAttributes({ count: count + 1 })}>

// Named handlers for complex logic
const handleQuantityChange = (value) => {
    const sanitized = Math.max(1, parseInt(value, 10));
    setAttributes({ quantity: sanitized });
};

<input onChange={handleQuantityChange} />
```

### Conditional Rendering

```jsx
// Short-circuit for simple conditions
{showElement && <Element />}

// Ternary for alternatives
{isActive ? <ActiveComponent /> : <InactiveComponent />}

// Null return for hide logic
if (!shouldRender) {
    return null;
}
```

## Naming Conventions

### Block Names

```
Namespace: sp-smart-store
Block format: sp-smart-store/{tier}-{block}
Examples:
├─ sp-smart-store/product-image
├─ sp-smart-store/product-grid
├─ sp-smart-store/smart-cart
└─ sp-smart-store/advanced-product-filters

PHP Class format: {Tier}\{Block}\{Block}
Examples:
├─ Element\ProductImage\ProductImage
├─ Showcase\ProductGrid\ProductGrid
├─ Page\SmartCart\SmartCart
└─ Builder\AdvancedProductFilter\AdvancedProductFilter
```

### Attribute Names

```javascript
// camelCase for JS
{
    productTitle: '',
    fontSize: { device: {...}, unit: 'px' },
    enableFeature: false
}

// camelCase for PHP (mapped from block.json)
'productTitle' => '',
'fontSize' => [],
'enableFeature' => false
```

### CSS Class Names

```
Prefix: spssp- (from old plugin name)
Pattern: spssp-{block-name}--{element}
Examples:
├─ spssp-product-image
├─ spssp-product-image__wrapper
├─ spssp-product-grid
└─ spssp-unique-id-{uniqueId}
```

### CSS Custom Properties

```
Prefix: --wpspro-
Pattern: --wpspro-{category}-{variant}
Examples:
├─ --wpspro-primary
├─ --wpspro-secondary
├─ --wpspro-text-color
└─ --wpspro-breakpoint-tablet: 1023px
```

## Code Organization

### Import Order

```jsx
// 1. WordPress imports
import { registerBlockType } from '@wordpress/blocks';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

// 2. Relative imports (use aliases when possible)
import Edit from './Edit';
import Save from './Save';
import icon from './icon';

// 3. Third-party imports
import { useState, useEffect } from '@wordpress/element';

// 4. SCSS imports
import './style.scss';
import './editor.scss';
```

### Export Order

```jsx
// 1. Main component
export default Edit;

// 2. Named exports (if any)
export { helperFunction };
```

## File Structure Rules

### Block Directory

Each block must contain at minimum:
- `block.json` - Block metadata and attributes
- `index.jsx` - Block registration
- `edit.jsx` - Editor interface
- `save.jsx` - Save logic

Optional files:
- `dynamicCss.js` - Preview CSS
- `style.scss` - Frontend styles
- `editor.scss` - Editor styles
- `icon.jsx` - Block icon
- `tabs.jsx` - Inspector tabs
- `inspectorControls.jsx` - Additional controls
- `view.js` - Frontend interactivity

### PHP Block Directory

```
{Block}/
├─ Block.php              (Main class)
├─ Attributes.php          (Attribute definitions)
└─ CssGenerator.php       (CSS generation)
```

## Comment Standards

### JS/JSX

```jsx
/**
 * Component or function description.
 * Multi-line if needed.
 *
 * @param {Type} paramName Description
 * @return {Type} Description
 */
const Component = (props) => {
    // Inline comment for non-obvious logic
    const value = complexCalculation();

    return <div />;
};
```

### PHP

```php
/**
 * Class or method description.
 *
 * Longer description spanning multiple lines if needed.
 *
 * @package ShapedPlugin\WooProductSliderPro\Blocks\Element
 */

class Example {

    /**
     * Method description.
     *
     * @param string $param Description
     * @return string Description
     */
    public function method($param) {
        // Comment explaining non-obvious logic
        return $result;
    }
}
```

## i18n Standards

### Text Domain

Always use: `'woo-product-slider-pro'`

```jsx
// Correct
__('Text', 'woo-product-slider-pro')
_n('Single', 'Plural', count, 'woo-product-slider-pro')

// Incorrect
__('Text')  // Missing text domain
__('Text', 'other-domain')  // Wrong domain
```

### String Formatting

```jsx
// Simple strings
__('Save Settings', 'woo-product-slider-pro')

// With variables
sprintf(
    __('Total: %d', 'woo-product-slider-pro'),
    total
)

// HTML allowed
__('Save <strong>Settings</strong>', 'woo-product-slider-pro')
```

## Security Standards

### Input Sanitization

```php
// Always sanitize user input
$title = sanitize_text_field($_POST['title']);
$html = wp_kses_post($content);
$color = sanitize_hex_color($attributes['color']);
```

### Output Escaping

```php
// Always escape output
echo esc_html($title);
echo esc_url($link);
echo esc_attr($class);
echo wp_kses($html, $allowed_tags);
```

### Nonce Verification

```php
// Verify nonces before processing
if (!wp_verify_nonce($nonce, 'action_name')) {
    return;
}
```

## Performance Standards

### Asset Loading

```php
// Register early, enqueue conditionally
add_action('init', array($this, 'register_assets'));
add_action('enqueue_block_assets', array($this, 'enqueue_assets'));
```

### Caching

```php
// Cache expensive operations
$cache_key = 'product_data_' . $product_id;
$cached = wp_cache_get($cache_key);

if (false === $cached) {
    $cached = get_expensive_data();
    wp_cache_set($cache_key, $cached, '', 3600);
}
```

## WordPress Standards

### Hooks

```php
// Use documented hooks with correct priorities
add_action('init', array($this, 'init'), 10);
add_filter('the_content', array($this, 'modify_content'), 10, 1);
```

### Query Modifications

```php
// Pre-get posts filter for query modification
add_action('pre_get_posts', array($this, 'modify_query'));

// Join modification for complex queries
function posts_join($join, $query) {
    global $wpdb;
    $join .= " LEFT JOIN {$wpdb->postmeta} ON ...";
    return $join;
}
```

### Block Rendering

```php
// Always use get_block_wrapper_attributes()
$wrapper_attributes = get_block_wrapper_attributes(
    array('class' => 'custom-class')
);

return sprintf(
    '<div %s>%s</div>',
    $wrapper_attributes,
    $content
);
```

## Responsive Design Standards

### Breakpoint System

```
Default breakpoints:
├─ Desktop: > 1023px
├─ Tablet: 768px - 1023px
└─ Mobile: < 768px

Customizable via global settings
```

### Media Query Pattern

```scss
// Mobile-first approach
.component {
    padding: 10px;  // Mobile default

    @media (min-width: 768px) {
        padding: 20px;  // Tablet
    }

    @media (min-width: 1024px) {
        padding: 30px;  // Desktop
    }
}
```

## Testing Guidelines

### Manual Testing Checklist

- [ ] Block appears in inserter
- [ ] Block renders correctly in editor
- [ ] Attributes save and persist
- [ ] Responsive behavior works
- [ ] Frontend renders correctly
- [ ] Dynamic CSS matches preview
- [ ] i18n strings are translatable
- [ ] Accessibility check (keyboard, ARIA)

### Cross-Browser Testing

Test in:
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers

## Git Conventions

### Commit Message Format

```
type(scope): brief description

Detailed description if needed

type: feat|fix|refactor|docs|style|test|chore
scope: block-name|component|general
```

Examples:
```
feat(product-grid): add lazy loading support

fix(add-to-cart): resolve quantity input validation issue

refactor(common): extract color utility functions

docs(README): update build instructions
```

### Branch Naming

```
feature/add-{feature-name}
fix/fix-{issue-description}
refactor/{component-name}
release/v{version}
```
