# Code Patterns — Common

## Block Registration (JS)

```jsx
import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import Save from './save';
import metadata from './block.json';

registerBlockType('sp-smart-store/block-name', {
    title: __('Block Title', 'woo-product-slider-pro'),
    category: 'smart-store-blocks',
    icon,
    attributes: metadata.attributes,
    usesContext: ['postId', 'query'],
    providesContext: { productId: 'productId' },
    edit: Edit,
    save: Save,
    supports: { html: false, align: true },
});
```

## Block Registration (PHP)

```php
<?php
namespace ShapedPlugin\WooProductSliderPro\Blocks\Element\BlockName;

use ShapedPlugin\WooProductSliderPro\Blocks\AbstractBlock;

defined('ABSPATH') || exit;

class BlockName extends AbstractBlock {
    protected $block_name = 'block-name';

    public function render($attributes, $content, $block) {
        $wrapper_attributes = get_block_wrapper_attributes();
        $output = '<div ' . $wrapper_attributes . '>';
        $output .= $this->get_block_content($attributes, $block);
        $output .= '</div>';
        return $output;
    }

    protected function get_block_content($attributes, $block) {
        return $content;
    }
}
```

## InnerBlocks Pattern

```jsx
import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';

<InnerBlocks
    allowedBlocks={['sp-smart-store/child-block']}
    template={[['sp-smart-store/child-block', {}]]}
    templateLock="all"
/>
```

## Dynamic CSS (JS)

```jsx
export default (props) => {
    const { attributes } = props;
    const { uniqueId, textColor, fontSize } = attributes;

    const styles = [];

    if (textColor?.color) {
        styles.push({
            selector: `.spssp-unique-id-${uniqueId}`,
            css: `color: ${textColor.color};`,
        });
    }

    if (fontSize?.value) {
        styles.push({
            selector: `.spssp-unique-id-${uniqueId}`,
            css: `font-size: ${fontSize.value}${fontSize.unit || 'px'};`,
        });
    }

    return styles;
};
```

## Dynamic CSS (PHP)

```php
public function generate_css($attributes) {
    $unique_id = $attributes['uniqueId'] ?? '';
    $css = '';

    if (!empty($attributes['textColor']['color'])) {
        $css .= ".spssp-unique-id-{$unique_id} { color: {$attributes['textColor']['color']}; }";
    }

    if (!empty($attributes['fontSize']['value'])) {
        $value = $attributes['fontSize']['value'];
        $unit = $attributes['fontSize']['unit'] ?? 'px';
        $css .= ".spssp-unique-id-{$unique_id} { font-size: {$value}{$unit}; }";
    }

    return $css;
}
```

## Data Fetching (useSelect)

```jsx
import { useSelect } from '@wordpress/data';

const { products, isLoading } = useSelect((select) => {
    const store = select('wpspro/global-settings');
    return {
        products: store.getProducts({ perPage: 10 }),
        isLoading: store.getIsLoading(),
    };
}, [attributes.perPage]);
```

## Context Provider/Consumer

```jsx
// Parent
registerBlockType('sp-smart-store/parent', {
    providesContext: { productId: 'productId', setting: 'settingValue' },
});

// Child
registerBlockType('sp-smart-store/child', {
    usesContext: ['productId', 'settingValue'],
});
```

## Responsive Control

```jsx
import ResponsiveControl from '@woo-product-slider-pro/controls/responsive';

<ResponsiveControl
    label={__('Padding', 'woo-product-slider-pro')}
    value={attributes.padding}
    onChange={(value) => setAttributes({ padding: value })}
    units={['px', '%', 'em']}
/>
```

## File Naming

| Type | Pattern | Example |
|------|----------|----------|
| Block name | `sp-smart-store/tier-block` | `sp-smart-store/product-image` |
| JS class | PascalCase | `ProductImage` |
| PHP class | PascalCase | `ProductImage` |
| PHP method | snake_case | `get_block_content()` |
| Attribute | camelCase | `productTitleColor` |
| CSS class | kebab-case | `spssp-product-image` |
