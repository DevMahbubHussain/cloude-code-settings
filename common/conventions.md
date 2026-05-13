# Coding Conventions

## Security (Non-Negotiable)

**PHP:**
- Every file: `defined('ABSPATH') || exit;`
- Sanitize inputs: `sanitize_text_field()`, `absint()`, `wp_kses_post()`
- Escape outputs: `esc_html()`, `esc_attr()`, `esc_url()`
- Database: `$wpdb->prepare()` — no raw SQL
- Auth: `current_user_can()` on all REST/AJAX
- CSRF: `wp_verify_nonce()`

**JS:**
- Never `dangerouslySetInnerHTML` with unsanitized content
- Validate attributes in save/render
- No direct DOM manipulation

## WordPress Standards

```php
// Hooks with priorities
add_action('init', array($this, 'init'), 10);
add_filter('the_content', array($this, 'modify_content'), 10, 1);

// Block rendering
$wrapper_attributes = get_block_wrapper_attributes(array('class' => 'custom-class'));
return sprintf('<div %s>%s</div>', $wrapper_attributes, $content);
```

## React/JSX Standards

```jsx
// Import order
import { registerBlockType } from '@wordpress/blocks';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import Edit from './edit';
import Save from './save';
import './style.scss';

// useBlockProps in edit, useBlockProps.save in save
const Edit = (props) => {
    const { attributes, setAttributes, clientId } = props;
    const blockProps = useBlockProps();

    return <div {...blockProps}>{/* content */}</div>;
};

// i18n
__('Text', 'woo-product-slider-pro')
_n('Single', 'Plural', count, 'woo-product-slider-pro')
```

## CSS Standards

- BEM: `spssp-block__element--modifier`
- No `!important` except WP editor conflicts
- Use `clamp()` for fluid spacing
- Breakpoints: 600px, 782px, 1080px
- Min touch target: 44×44px

```scss
// Mobile-first
.component {
    padding: 10px;

    @media (min-width: 768px) {
        padding: 20px;
    }

    @media (min-width: 1024px) {
        padding: 30px;
    }
}
```

## block.json Standards

```json
{
    "name": "sp-smart-store/block-name",
    "title": "Block Title",
    "category": "smart-store-blocks",
    "apiVersion": 3,
    "attributes": {
        "uniqueId": { "type": "string", "default": "" }
    },
    "supports": {
        "html": false,
        "align": true,
        "spacing": {},
        "color": {},
        "typography": {}
    }
}
```

## PHP Standards

```php
<?php
namespace ShapedPlugin\WooProductSliderPro\Blocks\Element\BlockName;

use ShapedPlugin\WooProductSliderPro\Blocks\AbstractBlock;

defined('ABSPATH') || exit;

class BlockName extends AbstractBlock {
    protected $block_name = 'block-name';

    public function render($attributes, $content, $block) {
        // Sanitize input
        $unique_id = sanitize_text_field($attributes['uniqueId'] ?? '');

        // Escape output
        return sprintf(
            '<div class="spssp-block %s">%s</div>',
            esc_attr($unique_id),
            wp_kses_post($content)
        );
    }
}
```

## File Structure

```
blocks/blocks/<tier>/<block>/
├── block.json          # Metadata
├── index.jsx           # Registration
├── edit.jsx            # Editor UI
├── save.jsx            # Save (null for dynamic)
├── dynamicCss.js       # Preview CSS
├── icons.jsx           # SVG icon
├── tabs.jsx            # Inspector tabs
├── inspectorControls.jsx # Additional controls
├── view.js            # Frontend interactivity
├── style.scss          # Frontend styles
└── editor.scss         # Editor styles

src/Blocks/<Tier>/<Block>/
├── Block.php           # Main class
├── Attributes.php      # Attribute definitions
└── CssGenerator.php   # Server CSS
```
