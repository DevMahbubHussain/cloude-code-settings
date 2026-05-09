# Gutenberg Block Skills

This document describes block patterns, dynamic select (DS) usage, and nested block conventions used in this codebase.

## Block Registration Pattern

All blocks follow a consistent registration pattern:

### JS Side (blocks/blocks/<tier>/<block>/)

```jsx
// index.jsx - Main entry point
import { registerBlockType } from '@wordpress/blocks';
import Edit from './Edit';
import Save from './Save';
import metadata from './block.json';
import './style.scss';
import './editor.scss';

registerBlockType('sp-smart-store/block-name', {
    title: __('Block Title', 'woo-product-slider-pro'),
    description: __('Block Description', 'woo-product-slider-pro'),
    category: 'smart-store-blocks',
    icon: icon,
    attributes: metadata.attributes,
    usesContext: ['postId', 'query'],
    providesContext: { productId: 'productId' },
    edit: Edit,
    save: Save,
    supports: {
        html: false, // Dynamic block
        align: true,
    },
});
```

### PHP Side (src/Blocks/<Tier>/<Block>/Block.php)

```php
<?php
namespace ShapedPlugin\WooProductSliderPro\Blocks\Element\ExampleBlock;

use ShapedPlugin\WooProductSliderPro\Blocks\AbstractBlock;

defined('ABSPATH') || exit;

class ExampleBlock extends AbstractBlock {
    protected $block_name = 'example-block';

    public function render($attributes, $content, $block) {
        $wrapper_attributes = get_block_wrapper_attributes();
        $output = '<div ' . $wrapper_attributes . '>';
        $output .= $this->get_block_content($attributes, $block);
        $output .= '</div>';
        return $output;
    }

    protected function get_block_content($attributes, $block) {
        // Block-specific rendering logic
        return '';
    }

    protected function get_block_specific_attributes() {
        return array(
            'customAttribute' => array(
                'type' => 'string',
                'default' => '',
            ),
        );
    }
}
```

## Dynamic Select (DS) Usage

Dynamic selects (like taxonomy, product selection) are handled via REST API endpoints:

### DS Control Pattern (JS)

```jsx
import { SelectControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';

const ExampleControl = ({ value, onChange }) => {
    const options = useSelect(select => {
        const store = select('wpspro/global-settings');
        return [
            { label: __('Option 1', 'woo-product-slider-pro'), value: 'opt1' },
            { label: __('Option 2', 'woo-product-slider-pro'), value: 'opt2' },
        ];
    }, []);

    return (
        <SelectControl
            label={__('Select Option', 'woo-product-slider-pro')}
            options={options}
            value={value}
            onChange={onChange}
        />
    );
};
```

### Taxonomy Filter Pattern

Taxonomy filters fetch terms via REST:

```jsx
// In filter block Edit.jsx
const { taxonomy, selectedTerms, onChange } = props;

const [terms, setTerms] = useState([]);
const [loading, setLoading] = useState(false);

useEffect(() => {
    if (!taxonomy) return;
    setLoading(true);
    fetch(`/wp-json/wpspro/v1/taxonomy-terms/${taxonomy}`)
        .then(res => res.json())
        .then(data => {
            setTerms(data);
            setLoading(false);
        });
}, [taxonomy]);
```

### Product Query Pattern

Product queries use centralized `ProductQuery` class:

```php
// src/Blocks/ProductQuery.php
$query = new ProductQuery($per_page);
$products = $query->get_products([
    'product_type' => 'latest',
    'categories' => [1, 2, 3],
    'exclude' => [4, 5],
]);
```

## Nested Block Patterns

### Parent-Child Relationship

Parent blocks provide context to child blocks:

```jsx
// Parent block index.jsx
registerBlockType('sp-smart-store/parent-block', {
    providesContext: {
        parentContext: 'parentId',
        parentSetting: 'settingValue',
    },
    edit: Edit,
    save: Save,
});
```

```jsx
// Child block index.jsx
registerBlockType('sp-smart-store/child-block', {
    usesContext: ['parentContext', 'parentSetting'],
    edit: Edit,
    save: Save,
});
```

### InnerBlocks Template Pattern

```jsx
import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';

const ParentEdit = ({ attributes, setAttributes }) => {
    const blockProps = useBlockProps();

    return (
        <div {...blockProps}>
            <InnerBlocks
                allowedBlocks={['sp-smart-store/child-1', 'sp-smart-store/child-2']}
                template={[
                    ['sp-smart-store/child-1', {}],
                    ['sp-smart-store/child-2', {}],
                ]}
                templateLock="all"
            />
        </div>
    );
};
```

### Smart Cart Nested Structure

Smart Cart uses deeply nested inner blocks for cart components:

```jsx
// Parent: sp-smart-store/smart-cart
<InnerBlocks
    allowedBlocks={[
        'sp-smart-store/smart-filled-cart-block',
        'sp-smart-store/smart-empty-cart-block',
    ]}
    template={[
        ['sp-smart-store/smart-filled-cart-block', {}, [
            ['sp-smart-store/smart-cart-items-block', {}, [
                ['sp-smart-store/smart-product-template', {}]
            ]],
            ['sp-smart-store/smart-cart-totals-block', {}, [
                ['sp-smart-store/smart-cart-order-summary-block', {}, [
                    ['sp-smart-store/smart-cart-order-summary-subtotal', {}],
                    ['sp-smart-store/smart-cart-order-summary-total', {}],
                ]],
            ]],
            ['sp-smart-store/smart-process-to-checkout-block', {}],
        ]],
    ]}
    templateLock="all"
/>
```

## Block Hierarchy Patterns

### Element Blocks (Atomic)
- Product Image
- Product Title
- Product Price
- Product Category
- Product Rating
- Stock Status
- Add to Cart
- Product Quantity
- Short Description
- Product Meta

These blocks use `postId` context and render single product fields.

### Smart Blocks (Layout Primitives)
- Smart Container
- Smart Column
- Smart Heading
- Smart Paragraph
- Smart Button

Generic layout components that can be used anywhere.

### Showcase Blocks (Composites)
- Product Template
- Product Grid
- Product Carousel
- Smart Pagination
- Smart Buttons
- Smart Cart
- Advanced Tab

These blocks combine element blocks into reusable layouts.

### Builder Blocks (Page Components)
- Advanced Product Filters (with inner filter blocks)
- Breadcrumb
- Archive Title
- Archive Description
- Product Description
- Product Additional Information
- Product Review

These blocks provide full-page template capabilities.

## Advanced Product Filters Pattern

The advanced product filters block uses a complex nested structure with context provider:

```jsx
// Parent block provides filter context
<FilterContext.Provider value={{ activeFilters, setActiveFilters }}>
    <InnerBlocks
        allowedBlocks={[
            'sp-smart-store/price-filter',
            'sp-smart-store/rating-filter',
            'sp-smart-store/taxonomy-filter',
            'sp-smart-store/status-filter',
            'sp-smart-store/sort-by-filter',
            'sp-smart-store/search-filter',
        ]}
    />
</FilterContext.Provider>
```

## Dynamic CSS Generation Pattern

Both JS and PHP sides must generate equivalent CSS:

### JS Side (dynamicCss.js)

```jsx
export default (props) => {
    const { attributes, clientId } = props;
    const { uniqueId, productTitleColor, productTitleFontSize } = attributes;

    const dynamicStyles = [
        {
            class: `.spssp-unique-id-${uniqueId}`,
            styles: {
                color: productTitleColor,
                'font-size': `${productTitleFontSize.value}${productTitleFontSize.unit}`,
            },
        },
    ];

    return objectToCssString(dynamicStyles);
};
```

### PHP Side (*CssGenerator.php)

```php
public function generate_css($attributes) {
    $unique_id = $attributes['uniqueId'] ?? '';
    $css = '';

    // Color
    if (!empty($attributes['productTitleColor']['color'])) {
        $css .= ".spssp-unique-id-{$unique_id} { ";
        $css .= "color: {$attributes['productTitleColor']['color']}; ";
        $css .= "}";
    }

    // Font Size
    $font_size = CssHelper::spacing_from_value(
        $attributes['productTitleFontSize'],
        'Desktop'
    );
    if ($font_size) {
        $css .= ".spssp-unique-id-{$unique_id} { ";
        $css .= "font-size: {$font_size}; ";
        $css .= "}";
    }

    return $css;
}
```

## Control Component Usage

Common control patterns for the block inspector:

### Responsive Control

```jsx
import ResponsiveControl from '@woo-product-slider-pro/controls/responsive';

<ResponsiveControl
    label={__('Padding', 'woo-product-slider-pro')}
    value={attributes.padding}
    onChange={padding => setAttributes({ padding })}
    units={['px', '%', 'em']}
/>
```

### Color Gradient Control

```jsx
import ColorGradientControl from '@woo-product-slider-pro/controls/color-gradient';

<ColorGradientControl
    label={__('Background', 'woo-product-slider-pro')}
    value={attributes.background}
    onChange={background => setAttributes({ background })}
/>
```

### Box Shadow Control

```jsx
import BoxShadowControl from '@woo-product-slider-pro/controls/box-shadow';

<BoxShadowControl
    label={__('Shadow', 'woo-product-slider-pro')}
    value={attributes.boxShadow}
    onChange={boxShadow => setAttributes({ boxShadow })}
/>
```

## Block Validation Patterns

### Required Attributes

```php
protected function get_block_specific_attributes() {
    return array(
        'requiredField' => array(
            'type' => 'string',
            'default' => '',
            'required' => true,
        ),
    );
}
```

### Conditional Rendering

```jsx
const Edit = ({ attributes }) => {
    if (!attributes.showOnDesktop && !wp_is_mobile()) {
        return null;
    }
    // Render block...
};
```

## Best Practices

1. **Always use text domain**: `__('Text', 'woo-product-slider-pro')`
2. **Use unique IDs**: Generate `uniqueId` on first insert, scope CSS with it
3. **Mirror JS/PHP CSS**: Ensure `dynamicCss.js` and `*CssGenerator.php` produce identical output
4. **Use context for hierarchical data**: Parent provides context, child consumes
5. **Register in BLOCK_MAPPING**: Add new blocks to `scripts/generate-blocks-php.js`
6. **Run build:blocks-php**: After editing `block.json`, regenerate PHP metadata
7. **Use allowedBlocks for InnerBlocks**: Restrict which blocks can be nested
8. **Template lock for fixed structures**: Use `templateLock="all"` or `"insert"`
