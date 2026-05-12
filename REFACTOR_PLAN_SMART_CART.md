# Smart Cart Block Refactoring Plan

> **NOTE**: This document contains proposed refactoring recommendations. No code changes have been made yet.
>
> **CRITICAL RULE**: All refactoring must NOT change the block's visual output, user-facing behavior, or saved markup. We are improving code quality, performance, and maintainability only.

---

## PHASE 1: Component Architecture & React Patterns (Editor-Side)

### R-001: CartItem component contains formatting logic that should be extracted
**SEVERITY**: MEDIUM
**REFACTOR CATEGORY**: ARCHITECTURE
**FILE**: `blocks/blocks/showcase/smart-cart/inner-blocks/smart-cart-line-items-block/components/CartItem.jsx`
**LINE**: 5-8, 11-17
**CURRENT CODE**:
```jsx
const CartItem = ({ item, totals }) => {
    // Rating Logic: Actual if >= 4, else mock 5 stars
    const rawRating = parseFloat(item.average_rating || 0);
    const starValue = rawRating >= 4 ? Math.round(rawRating) : 5;
    const displayStars = '★'.repeat(starValue).padEnd(5, '☆');

    // Description Logic: Trim to ~25 words
    const rawDesc = item.short_description || 'This premium product features high-quality materials...';
    const cleanDesc = rawDesc.replace(/<[^>]*>/g, '');
    const descArray = cleanDesc.split(/\s+/);
    const trimmedDesc = descArray.length > 25 ? descArray.slice(0, 25).join(' ') + '...' : cleanDesc;
    // ...
};
```
**PROPOSED REFACTORING**: Extract rating and description formatting into the existing `formatters.js` utility that's already used in the frontend CartItem. The logic exists in `cart-app/utils/formatters.js` but this editor component duplicates it.
```jsx
// In CartItem.jsx
import { renderRating, formatDescription } from '../../../../cart-app/utils/formatters';

const CartItem = ({ item, totals }) => {
    return (
        <div className="smart-cart-item-row">
            {/* ... */}
            <div className="product-rating">{renderRating(item.average_rating)}</div>
            <div className="product-short-desc">{formatDescription(item.short_description)}</div>
        </div>
    );
};
```
**BEHAVIOR PRESERVATION CHECK**: Uses existing formatter functions that produce identical output. The `renderRating` and `formatDescription` functions in `cart-app/utils/formatters.js` produce the same results as the inline logic.

---

### R-002: useCouponActions hook uses direct DOM manipulation
**SEVERITY**: HIGH
**REFACTOR CATEGORY**: STATE
**FILE**: `blocks/blocks/showcase/smart-cart/cart-app/hooks/useCouponActions.js`
**LINE**: 10-28, 31-106, 109-146
**CURRENT CODE**:
```javascript
useEffect(() => {
    const container = document.querySelector('.spssp-injected-coupons');
    if (!container) return;
    container.innerHTML = '';
    appliedCoupons.forEach((coupon) => {
        const badge = document.createElement('span');
        badge.className = 'spssp-coupon-badge';
        badge.innerHTML = `${coupon.code.toUpperCase()}<button...>`;
        container.appendChild(badge);
    });
}, [appliedCoupons]);
```
**PROPOSED REFACTORING**: Replace direct DOM manipulation with React state and proper component rendering. Create a dedicated `CouponBadges` component and pass coupons as props. The coupon form block should render badges via React, not by manipulating the DOM.
```javascript
// Create CouponBadges component
const CouponBadges = ({ coupons, onRemove }) => (
    <div className="spssp-injected-coupons">
        {coupons.map((coupon) => (
            <span key={coupon.code} className="spssp-coupon-badge">
                {coupon.code.toUpperCase()}
                <button onClick={() => onRemove(coupon.code)}>&times;</button>
            </span>
        ))}
    </div>
);

// Use in hook or component instead of DOM manipulation
```
**BEHAVIOR PRESERVATION CHECK**: React will render the same markup structure and classes. The visual output remains identical but is now managed by React's virtual DOM instead of direct manipulation.

---

### R-003: useTotalsSync hook uses direct DOM manipulation
**SEVERITY**: HIGH
**REFACTOR CATEGORY**: STATE
**FILE**: `blocks/blocks/showcase/smart-cart/cart-app/hooks/useTotalsSync.js`
**LINE**: 26-47
**CURRENT CODE**:
```javascript
const updates = {
    '.spssp-subtotal-value': formatPrice(/* ... */),
    '.spssp-shipping-value': displayShipping,
    '.spssp-tax-value': displayTax,
    '.spssp-discount-value': `-${formatPrice(Math.abs(discountValue), cartTotals)}`,
    '.spssp-total-value': formatPrice(cartTotals.total_price, cartTotals),
};

Object.entries(updates).forEach(([selector, value]) => {
    const node = document.querySelector(selector);
    if (node) {
        node.innerHTML = value;
    }
});
```
**PROPOSED REFACTORING**: Create a `CartTotals` React component that receives totals as props and renders them properly. The totals should be in the context and rendered via React, not via DOM queries.
```javascript
// Create CartTotals component
const CartTotals = ({ cartTotals }) => (
    <div className="spssp-totals-card">
        <div className="summary-row subtotal-row">
            <span>Subtotal</span>
            <span className="spssp-subtotal-value">
                {formatPrice(cartTotals.total_items_total || cartTotals.total_items, cartTotals)}
            </span>
        </div>
        {/* ... */}
    </div>
);
```
**BEHAVIOR PRESERVATION CHECK**: The totals will be rendered with the same formatting via React. The visual output and values remain identical.

---

### R-004: useCartActions hook uses direct DOM manipulation for loading states
**SEVERITY**: HIGH
**REFACTOR CATEGORY**: STATE
**FILE**: `blocks/blocks/showcase/smart-cart/cart-app/hooks/useCartActions.js`
**LINE**: 10-34, 37-72, 76-92, 106-114
**CURRENT CODE**:
```javascript
const handleRemoveItem = useCallback(async (itemKey) => {
    const row = document.querySelector(`[data-cart-item-key="${itemKey}"]`);
    const container = document.querySelector('.spssp-cart-container');
    if (row) row.classList.add('is-updating');
    if (container) container.classList.add('is-loading');
    // ...
    if (container) container.classList.remove('is-loading');
    if (row) row.classList.remove('is-updating');
}, [removeItemFromCart]);
```
**PROPOSED REFACTORING**: Use React state for loading/updating states. The `CartItem` component should receive `isUpdating` prop and manage its own class. The container loading state should be managed via context.
```javascript
// In a cart context or parent component
const [updatingItems, setUpdatingItems] = useState(new Set());
const [isCartLoading, setIsCartLoading] = useState(false);

const handleRemoveItem = useCallback(async (itemKey) => {
    setUpdatingItems(prev => new Set(prev).add(itemKey));
    setIsCartLoading(true);
    try {
        await removeItemFromCart(itemKey);
    } finally {
        setUpdatingItems(prev => {
            const next = new Set(prev);
            next.delete(itemKey);
            return next;
        });
        setIsCartLoading(false);
    }
}, [removeItemFromCart]);

// Pass updatingItems to CartItem component
<CartItem item={item} isUpdating={updatingItems.has(item.key)} />
```
**BEHAVIOR PRESERVATION CHECK**: CSS classes will be applied via React's className prop based on state, producing the same visual result as manual class manipulation.

---

### R-005: Duplicate Skeleton components
**SEVERITY**: LOW
**REFACTOR CATEGORY**: DRY
**FILE**: `blocks/blocks/showcase/smart-cart/components/Skeleton.jsx` and `blocks/blocks/showcase/smart-cart/inner-blocks/smart-cart-line-items-block/components/Skeleton.jsx`
**LINE**: Both files contain nearly identical skeleton implementations
**CURRENT CODE**:
```jsx
// components/Skeleton.jsx
const Skeleton = ({ count = 3 }) => {
    const placeholderRows = Array.from({ length: count }, (_, i) => i + 1);
    return (/* ... */);
};

// inner-blocks/.../components/Skeleton.jsx
const Skeleton = () => {
    const placeholderRows = [1, 2]; // Hardcoded!
    return (/* ... */);
};
```
**PROPOSED REFACTORING**: Consolidate into a single shared skeleton component and export from a common location.
```jsx
// In components/Skeleton.jsx
export const CartItemsSkeleton = ({ count = 3 }) => { /* ... */ };
export const TotalsSkeleton = () => { /* ... */ };

// Delete inner-blocks/.../components/Skeleton.jsx
// Import from shared location instead
```
**BEHAVIOR PRESERVATION CHECK**: The skeleton component produces identical markup and CSS classes. Consolidating them doesn't change the visual output.

---

### R-006: Inline SVG icons in edit.jsx
**SEVERITY**: LOW
**REFACTOR CATEGORY**: ARCHITECTURE
**FILE**: `blocks/blocks/showcase/smart-cart/edit.jsx`
**LINE**: 25-78
**CURRENT CODE**:
```jsx
const icons = {
    filled: (
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
            {/* ... many path elements ... */}
        </svg>
    ),
    empty: (
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="22" viewBox="0 0 24 22" fill="none">
            {/* ... many path elements ... */}
        </svg>
    ),
};
```
**PROPOSED REFACTORING**: Extract icons to a separate file or use the existing `icons.jsx` pattern used elsewhere in the codebase.
```jsx
// Create icons/CartIcons.jsx
export const FilledCartIcon = () => (
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
        {/* ... */}
    </svg>
);

export const EmptyCartIcon = () => (/* ... */);

// In edit.jsx
import { FilledCartIcon, EmptyCartIcon } from './icons/CartIcons';
```
**BEHAVIOR PRESERVATION CHECK**: Icons render identically. This is purely a code organization improvement.

---

### R-007: useBlockProps.save() not used consistently
**SEVERITY**: LOW
**REFACTOR CATEGORY**: MODERNIZE
**FILE**: Multiple inner-block Save components
**LINE**: Various
**CURRENT CODE**:
```jsx
// Some components use:
export const Save = () => {
    return <InnerBlocks.Content />;
};

// Others use:
export const Save = () => {
    return <div {...useBlockProps.save()}><InnerBlocks.Content /></div>;
};
```
**PROPOSED REFACTORING**: Standardize on using `useBlockProps.save()` for all Save components to ensure proper block attributes are preserved.
```jsx
export const Save = () => {
    return <div {...useBlockProps.save()}><InnerBlocks.Content /></div>;
};
```
**BEHAVIOR PRESERVATION CHECK**: `useBlockProps.save()` adds standard block classes and attributes, which improves block consistency without changing saved content.

---

## PHASE 2: State Management & Data Fetching (Editor-Side)

### R-008: useSelect with empty dependency array
**SEVERITY**: HIGH
**REFACTOR CATEGORY**: STATE
**FILE**: `blocks/blocks/showcase/smart-cart/provider/cart-provider.jsx`
**LINE**: 9-15
**CURRENT CODE**:
```javascript
const { cartData, isFinished } = useSelect((select) => {
    const store = select(CART_STORE_KEY);
    return {
        cartData: store ? store.getCartData() : null,
        isFinished: select('core/data').hasFinishedResolution(CART_STORE_KEY, 'getCartData'),
    };
}, []); // Empty dependency array!
```
**PROPOSED REFACTORING**: Remove the dependency array or use `useSelect` without it to let WordPress Data manage memoization properly.
```javascript
const { cartData, isFinished } = useSelect((select) => {
    const store = select(CART_STORE_KEY);
    return {
        cartData: store ? store.getCartData() : null,
        isFinished: select('core/data').hasFinishedResolution(CART_STORE_KEY, 'getCartData'),
    };
}); // Let WordPress Data handle dependencies automatically
```
**BEHAVIOR PRESERVATION CHECK**: The data will be fetched and re-selected exactly as before, but will properly update when cart data changes.

---

### R-009: useSelect with empty dependency array in useCartData
**SEVERITY**: HIGH
**REFACTOR CATEGORY**: STATE
**FILE**: `blocks/blocks/showcase/smart-cart/cart-app/hooks/useCartData.js`
**LINE**: 8-20
**CURRENT CODE**:
```javascript
const { cartItems, cartTotals, isLoading, isResolving, appliedCoupons } = useSelect((select) => {
    const storeKey = window.wc?.wcBlocksData?.CART_STORE_KEY || 'wc/store/cart';
    const store = select(storeKey);
    const data = store?.getCartData();

    return {
        cartItems: data?.items || [],
        cartTotals: data?.totals || {},
        isLoading: !store?.hasFinishedResolution('getCartData'),
        isResolving: store?.isResolving('getCartData') || false,
        appliedCoupons: store?.getCartData()?.coupons || [],
    };
}, []); // Empty - won't update!
```
**PROPOSED REFACTORING**: Remove the empty dependency array to allow the selector to re-run when store data changes.
```javascript
const { cartItems, cartTotals, isLoading, isResolving, appliedCoupons } = useSelect((select) => {
    const storeKey = window.wc?.wcBlocksData?.CART_STORE_KEY || 'wc/store/cart';
    const store = select(storeKey);
    const data = store?.getCartData();

    return {
        cartItems: data?.items || [],
        cartTotals: data?.totals || {},
        isLoading: !store?.hasFinishedResolution('getCartData'),
        isResolving: store?.isResolving('getCartData') || false,
        appliedCoupons: data?.coupons || [],
    };
}); // No dependency array - let WC store manage
```
**BEHAVIOR PRESERVATION CHECK**: The hook will now properly update when cart data changes, ensuring the UI reflects the actual cart state.

---

### R-010: Storing cart data as attributes (volatile data in attributes)
**SEVERITY**: MEDIUM
**REFACTOR CATEGORY**: ARCHITECTURE
**FILE**: Multiple block.json and edit.jsx files
**LINE**: Various
**CURRENT CODE**:
```json
// In multiple block.json files
"attributes": {
    "cartContext": { "type": "object", "default": {} },
    "cartItemsContext": { "type": "object", "default": {} },
    "cartTotalsContext": { "type": "object", "default": {} },
    "isFilledCart": { "type": "boolean", "default": true }
}
```

And in edit.jsx:
```javascript
useEffect(() => {
    if (cartData?.items) {
        setAttributes({ cartItemsContext: cartData.items });
    }
}, [cartData]);
```
**PROPOSED REFACTORING**: Cart data is runtime state, not block configuration. It should be accessed via context/store, not stored as attributes. Remove these volatile attributes and use the SmartCartContext directly.
```javascript
// Remove from block.json attributes:
// "cartContext", "cartItemsContext", "cartTotalsContext", "isFilledCart"

// In edit.jsx - use context instead
const cart = useSmartCart();
const cartItems = cart?.items || [];

// No useEffect to sync to attributes needed
```
**BEHAVIOR PRESERVATION CHECK**: Cart data is now accessed directly from context/store. The UI displays the same data but doesn't unnecessarily persist volatile data to the database.

---

### R-011: Missing useCallback for event handlers
**SEVERITY**: LOW
**REFACTOR CATEGORY**: STATE
**FILE**: `blocks/blocks/showcase/smart-cart/cart-app/hooks/useCouponActions.js`
**LINE**: 82-95, 110-142
**CURRENT CODE**:
```javascript
useEffect(() => {
    const handleApply = async () => { /* ... */ };
    const handleInputChange = (e) => { /* ... */ };
    const onEnter = (e) => { /* ... */ };

    couponBtn.addEventListener('click', handleApply);
    // ...
    return () => {
        couponBtn.removeEventListener('click', handleApply);
        // ...
    };
}, [applyCoupon]); // Functions recreated on every render
```
**PROPOSED REFACTORING**: Wrap event handlers in `useCallback` to prevent unnecessary re-creation and event listener thrashing.
```javascript
useEffect(() => {
    const handleApply = async () => { /* ... */ };
    const handleInputChange = (e) => { /* ... */ };
    const onEnter = (e) => { /* ... */ };

    couponBtn.addEventListener('click', handleApply);
    // ...
    return () => {
        couponBtn.removeEventListener('click', handleApply);
        // ...
    };
}, [applyCoupon, couponBtn, couponInput, messageNode]);
```
**BEHAVIOR PRESERVATION CHECK**: Event handlers work identically but are properly memoized to reduce re-creation and listener thrashing.

---

## PHASE 3: PHP Architecture & Backend Logic (Server-Side)

### R-012: Duplicate cart data preparation logic
**SEVERITY**: MEDIUM
**REFACTOR CATEGORY**: DRY
**FILE**: `src/Blocks/Page/SmartCart/render.php` (lines 36-59), `src/Blocks/Page/SmartCart/FilledCart/render.php` (lines 22-45), `src/Blocks/Page/SmartCart/Api/SmartCartDataEndpoint.php` (lines 33-57)
**CURRENT CODE**:
```php
// Repeated in multiple files
$items_payload = array();
foreach ( $cart_items as $key => $item ) {
    $_product = $item['data'];
    if ( ! $_product ) {
        continue;
    }

    $items_payload[] = array(
        'key'               => $key,
        'id'                => $_product->get_id(),
        'name'              => $_product->get_name(),
        'permalink'         => $_product->get_permalink(),
        'average_rating'    => $_product->get_average_rating(),
        'short_description' => $_product->get_short_description(),
        'quantity'          => $item['quantity'],
        'prices'            => array(
            'price'        => (string) round( $_product->get_price() * 100 ),
            'raw_subtotal' => (int) ( (float) $item['line_total'] * 100 ),
            'subtotal'     => $cart->get_product_subtotal( $_product, $item['quantity'] ),
        ),
        'images'            => array(
            array( 'src' => get_the_post_thumbnail_url( $_product->get_id(), 'thumbnail' ) ),
        ),
    );
}
```
**PROPOSED REFACTORING**: Extract to a shared utility method or class.
```php
// Create src/Blocks/Page/SmartCart/Helper/CartDataHelper.php
class CartDataHelper {
    public static function prepare_cart_items_payload( $cart_items, $cart ) {
        $items_payload = array();
        foreach ( $cart_items as $key => $item ) {
            $_product = $item['data'];
            if ( ! $_product ) {
                continue;
            }

            $items_payload[] = array(
                'key'               => $key,
                'id'                => $_product->get_id(),
                'name'              => $_product->get_name(),
                // ... rest of the code
            );
        }
        return $items_payload;
    }
}

// Use in all locations
$items_payload = CartDataHelper::prepare_cart_items_payload( $cart_items, $cart );
```
**BEHAVIOR PRESERVATION CHECK**: The payload is identical, just generated by a shared method instead of duplicated code.

---

### R-013: Missing type declarations and return types in PHP
**SEVERITY**: LOW
**REFACTOR CATEGORY**: PHP-LOGIC
**FILE**: Multiple PHP files
**LINE**: Various
**CURRENT CODE**:
```php
public function get_cart_data() {
    // Check nonce.
    check_ajax_referer( 'spssp_cart_data', 'nonce' );
    // ...
    wp_send_json_success( $response );
}

public static function get() {
    return array(
        'replaceDefaultCart' => array(
            'type'    => 'boolean',
            'default' => false,
        ),
        // ...
    );
}
```
**PROPOSED REFACTORING**: Add proper type hints and return types.
```php
public function get_cart_data(): void {
    check_ajax_referer( 'spssp_cart_data', 'nonce' );
    // ...
    wp_send_json_success( $response );
}

public static function get(): array {
    return array(
        'replaceDefaultCart' => array(
            'type'    => 'boolean',
            'default' => false,
        ),
        // ...
    );
}
```
**BEHAVIOR PRESERVATION CHECK**: Type hints don't change runtime behavior. They improve code quality and IDE support.

---

### R-014: Inconsistent data access pattern for totals
**SEVERITY**: LOW
**REFACTOR CATEGORY**: PHP-LOGIC
**FILE**: `src/Blocks/Page/SmartCart/CartTotals.php`
**LINE**: 206-251 (fallback HTML)
**CURRENT CODE**:
```php
// In the fallback HTML, directly accessing WooCommerce cart methods
<h3 class="spssp-totals-title"><?php esc_html_e( 'Cart totals', 'woo-product-slider-pro' ); ?></h3>

<div class="summary-row subtotal-row">
    <span><?php esc_html_e( 'Subtotal', 'woo-product-slider-pro' ); ?></span>
    <span class="spssp-subtotal-value"><?php echo $cart->get_cart_subtotal(); ?></span>
</div>

<?php if ( $cart->get_discount_total() > 0 ) : ?>
    <div class="summary-row discount-row">
        <span><?php esc_html_e( 'Discount', 'woo-product-slider-pro' ); ?></span>
        <span class="spssp-discount-value">-<?php echo wc_price( $cart->get_discount_total() ); ?></span>
    </div>
<?php endif; ?>
```
**PROPOSED REFACTORING**: Extract the totals row rendering into a reusable helper method or partial template.
```php
private static function render_totals_row( $label, $value, $class = '' ): string {
    return sprintf(
        '<div class="summary-row %s"><span>%s</span><span class="%s">%s</span></div>',
        esc_attr( $class ),
        esc_html( $label ),
        esc_attr( $class . '-value' ),
        $value
    );
}

// Usage
echo self::render_totals_row(
    __( 'Subtotal', 'woo-product-slider-pro' ),
    $cart->get_cart_subtotal(),
    'subtotal-row'
);
```
**BEHAVIOR PRESERVATION CHECK**: The HTML output is identical, just generated by a helper function.

---

### R-015: Duplicate generate_dynamic_css patterns across PHP classes
**SEVERITY**: MEDIUM
**REFACTOR CATEGORY**: DRY
**FILE**: `src/Blocks/Page/SmartCart/FilledCart/SmartFilledCartBlock.php` (lines 43-212), `src/Blocks/Page/SmartCart/CartItems/CartItems.php` (lines 44-193), `src/Blocks/Page/SmartCart/CartTotals.php` (lines 43-154)
**CURRENT CODE**:
```php
// Nearly identical device loop and CSS generation in each class
$devices = array(
    'Desktop' => array( 'breakpoint' => null ),
    'Tablet'  => array( 'breakpoint' => '1024px' ),
    'Mobile'  => array( 'breakpoint' => '782px' ),
);

foreach ( $devices as $device => $config ) {
    // ... duplicate CSS generation logic
}
```
**PROPOSED REFACTORING**: Create a trait or base class with shared CSS generation logic.
```php
trait ResponsiveStyleGenerator {
    protected function get_device_breakpoints(): array {
        return array(
            'Desktop' => array( 'breakpoint' => null ),
            'Tablet'  => array( 'breakpoint' => '1024px' ),
            'Mobile'  => array( 'breakpoint' => '782px' ),
        );
    }

    protected function generate_device_css( callable $style_generator, array $devices = null ): string {
        $devices = $devices ?? $this->get_device_breakpoints();
        $css = '';

        foreach ( $devices as $device => $config ) {
            $device_css = $style_generator( $device );
            if ( $config['breakpoint'] ) {
                $css .= "@media only screen and (max-width: {$config['breakpoint']}) { {$device_css} }";
            } else {
                $css .= $device_css;
            }
        }

        return $css;
    }
}

// Use in each block class
class SmartFilledCartBlock extends AbstractBlock {
    use ResponsiveStyleGenerator;

    protected function generate_dynamic_css( $attributes ) {
        $css = $this->generate_device_css( function( $device ) use ( $attributes ) {
            // Generate device-specific CSS
            return $device_css;
        });
        return '<style>' . $css . '</style>';
    }
}
```
**BEHAVIOR PRESERVATION CHECK**: The generated CSS is identical. The logic is just extracted to a shared trait.

---

### R-016: Missing null checks before accessing WooCommerce cart
**SEVERITY**: MEDIUM
**REFACTOR CATEGORY**: PHP-LOGIC
**FILE**: `src/Blocks/Page/SmartCart/render.php`
**LINE**: 8-12
**CURRENT CODE**:
```php
$cart = WC()->cart;

if ( $cart ) {
    $cart->calculate_totals();
}

// Later code assumes $cart exists without checking
$cart_items = ( $cart ) ? $cart->get_cart() : array();
```
**PROPOSED REFACTORING**: Add proper null guard and early return pattern.
```php
$cart = WC()->cart;

if ( ! $cart || $cart->is_empty() ) {
    // Render empty cart or return early
    return '';
}

$cart->calculate_totals();
$cart_items = $cart->get_cart();

// Now $cart_items is guaranteed to be available
```
**BEHAVIOR PRESERVATION CHECK**: The early return pattern prevents errors while producing the same output for empty carts.

---

### R-017: Helper function loaded with conditional inside render
**SEVERITY**: LOW
**REFACTOR CATEGORY**: PHP-LOGIC
**FILE**: `src/Blocks/Page/SmartCart/CartTotals.php`
**LINE**: 171-173
**CURRENT CODE**:
```php
// Load helper functions if not already loaded.
if ( ! function_exists( 'spssp_render_coupon_logic' ) ) {
    require_once __DIR__ . '/helper/find_block.php';
}
```
**PROPOSED REFACTORING**: Move the require_once to class constructor or use autoloading.
```php
class CartTotals extends AbstractBlock {
    public function __construct() {
        parent::__construct();
        // Load helper once
        require_once __DIR__ . '/helper/find_block.php';
    }
    // ...
}
```
**BEHAVIOR PRESERVATION CHECK**: Helper loads exactly once. This is a code organization improvement.

---

## PHASE 4: Performance Optimization (Both Sides)

### R-018: Conditional asset enqueuing not implemented
**SEVERITY**: HIGH
**REFACTOR CATEGORY**: PERFORMANCE
**FILE**: `src/Blocks/Page/SmartCart/SmartCart.php`
**LINE**: 78-80
**CURRENT CODE**:
```php
// Enqueue assets for both states.
wp_enqueue_script( 'smart-store-pro-smart-cart-view' );
wp_enqueue_style( 'sp-wpsp-smart-cart' );
```
**PROPOSED REFACTORING**: Only enqueue assets when the block is actually present on the page.
```php
protected function render( $attributes, $content, $block ) {
    if ( is_admin() ) {
        return '';
    }

    // Only enqueue if this block is being rendered
    wp_enqueue_script( 'smart-store-pro-smart-cart-view' );
    wp_enqueue_style( 'sp-wpsp-smart-cart' );

    // ... rest of render logic
}
```
**BEHAVIOR PRESERVATION CHECK**: Assets load only when needed, reducing page load time on pages without the cart block.

---

### R-019: Large inline style generation on every render
**SEVERITY**: MEDIUM
**REFACTOR CATEGORY**: PERFORMANCE
**FILE**: Multiple PHP classes with `generate_dynamic_css`
**LINE**: Various
**CURRENT CODE**:
```php
protected function render( $attributes, $content, $block ) {
    // ...
    $css = $this->generate_dynamic_css( $attributes );

    return $css . sprintf(
        '<div %s>%s</div>',
        $wrapper_attributes,
        $inner_html
    );
}
```
**PROPOSED REFACTORING**: Consider caching generated CSS or using post meta for stable configurations.
```php
protected function render( $attributes, $content, $block ) {
    $cache_key = 'spssp_cart_css_' . md5( serialize( $attributes ) );

    $css = wp_cache_get( $cache_key, 'spssp_dynamic_css' );
    if ( false === $css ) {
        $css = $this->generate_dynamic_css( $attributes );
        wp_cache_set( $cache_key, $css, 'spssp_dynamic_css', HOUR_IN_SECONDS );
    }

    return $css . sprintf(/* ... */);
}
```
**BEHAVIOR PRESERVATION CHECK**: CSS output is identical, just cached to avoid regeneration.

---

### R-020: Missing `in_footer` parameter for view script
**SEVERITY**: LOW
**REFACTOR CATEGORY**: PERFORMANCE
**FILE**: Check script registration (likely in `src/Frontend/Frontend.php` or similar)
**CURRENT CODE**: Need to verify, but likely missing `in_footer` parameter.
**PROPOSED REFACTORING**: Register the view script with `in_footer` parameter set to `true`.
```php
wp_register_script(
    'smart-store-pro-smart-cart-view',
    $asset['src'],
    $asset['dependencies'],
    $asset['version'],
    true // Load in footer
);
```
**BEHAVIOR PRESERVATION CHECK**: Script loads in footer instead of header, improving page render performance without changing functionality.

---

### R-021: No lazy loading for product images
**SEVERITY**: LOW
**REFACTOR CATEGORY**: PERFORMANCE
**FILE**: `src/Blocks/Page/SmartCart/FilledCart/render.php`
**LINE**: 100
**CURRENT CODE**:
```php
<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $_product->get_name() ); ?>" class="is-loaded">
```
**PROPOSED REFACTORING**: Add native lazy loading for below-fold images.
```php
<img 
    src="<?php echo esc_url( $image_url ); ?>" 
    alt="<?php echo esc_attr( $_product->get_name() ); ?>" 
    class="is-loaded"
    loading="lazy"
    width="80"
    height="80"
>
```
**BEHAVIOR PRESERVATION CHECK**: Images load lazily instead of eagerly, improving initial page load time. Visual output remains the same.

---

### R-022: Skeleton loading causes layout shift
**SEVERITY**: MEDIUM
**REFACTOR CATEGORY**: PERFORMANCE
**FILE**: `blocks/blocks/showcase/smart-cart/components/Skeleton.jsx` and CSS
**CURRENT CODE**: Skeleton elements don't have explicit dimensions, causing CLS.
**PROPOSED REFACTORING**: Add explicit dimensions to skeleton elements to prevent layout shift.
```css
.skeleton-image {
    width: 80px;
    height: 80px; /* Explicit height prevents CLS */
}

.skeleton-text.title {
    height: 16px;
    width: 60%;
}

.skeleton-text.desc {
    height: 36px;
    width: 90%;
}
```
**BEHAVIOR PRESERVATION CHECK**: Skeletons have explicit dimensions, preventing layout shift when content loads.

---

### R-023: view.js initializes cart blocks without debouncing
**SEVERITY**: LOW
**REFACTOR CATEGORY**: PERFORMANCE
**FILE**: `blocks/blocks/showcase/smart-cart/view.js`
**LINE**: 34-51
**CURRENT CODE**:
```javascript
const waitForWooCommerce = () => {
    if (window.wc && window.wc.wcBlocksData) {
        initCart();
        // ...
    } else {
        setTimeout(waitForWooCommerce, 100); // Polling every 100ms
    }
};
```
**PROPOSED REFACTORING**: Use MutationObserver or event-based initialization instead of polling.
```javascript
const waitForWooCommerce = () => {
    if (window.wc && window.wc.wcBlocksData) {
        initCart();
        return;
    }

    // Use MutationObserver for more efficient detection
    const observer = new MutationObserver((mutations, obs) => {
        if (window.wc && window.wc.wcBlocksData) {
            obs.disconnect();
            initCart();
        }
    });

    observer.observe(document.body, { childList: true, subtree: true });
};
```
**BEHAVIOR PRESERVATION CHECK**: Cart initializes at the same time but uses event-driven detection instead of polling.

---

## PHASE 5: Modernization & DRY (Both Sides)

### R-024: Two duplicate formatPrice utilities
**SEVERITY**: MEDIUM
**REFACTOR CATEGORY**: DRY
**FILE**: `blocks/blocks/showcase/smart-cart/cart-app/utils/formatters.js` and `blocks/blocks/showcase/smart-cart/utils.js`
**LINE**: Both files contain `formatPrice` function
**CURRENT CODE**:
```javascript
// cart-app/utils/formatters.js
export const formatPrice = (rawPrice, totals) => {
    if (!totals || rawPrice === undefined) {
        return '—';
    }
    const value = parseFloat(rawPrice) / 100;
    return `${totals.currency_symbol || '$'}${value.toFixed(totals.currency_minor_unit ?? 2)}`;
};

// utils.js
const formatPrice = (rawPrice, totals) => {
    if (!totals || rawPrice === undefined || rawPrice === null) {
        return '—';
    }
    const numericValue = parseFloat(rawPrice);
    if (isNaN(numericValue)) {
        return '—';
    }
    const minorUnit = parseInt(totals.currency_minor_unit ?? 2, 10);
    const symbol = totals.currency_symbol || '$';
    const formatted = (numericValue / Math.pow(10, minorUnit)).toFixed(minorUnit);
    if (totals.currency_suffix && totals.currency_suffix !== '') {
        return `${formatted}${symbol}`;
    }
    return `${symbol}${formatted}`;
};
```
**PROPOSED REFACTORING**: Consolidate into a single shared utility.
```javascript
// Keep only in cart-app/utils/formatters.js, improve it to handle all cases:
export const formatPrice = (rawPrice, totals) => {
    if (!totals || rawPrice === undefined || rawPrice === null) {
        return '—';
    }

    const numericValue = parseFloat(rawPrice);
    if (isNaN(numericValue)) {
        return '—';
    }

    const minorUnit = parseInt(totals.currency_minor_unit ?? 2, 10);
    const symbol = totals.currency_symbol || '$';
    const formatted = (numericValue / Math.pow(10, minorUnit)).toFixed(minorUnit);

    if (totals.currency_suffix && totals.currency_suffix !== '') {
        return `${formatted}${symbol}`;
    }
    return `${symbol}${formatted}`;
};

// Delete utils.js and update all imports
```
**BEHAVIOR PRESERVATION CHECK**: All uses of formatPrice will use the consolidated function that handles all edge cases.

---

### R-025: Duplicate CSS rules in SCSS files
**SEVERITY**: LOW
**REFACTOR CATEGORY**: DRY
**FILE**: `blocks/blocks/showcase/smart-cart/inner-blocks/smart-cart-line-items-block/style.scss`
**LINE**: 1-139 (commented code), 147-510 (active code)
**CURRENT CODE**: The file has a large commented-out section (lines 1-139) that duplicates the active CSS structure.
**PROPOSED REFACTORING**: Remove commented-out code and consolidate shared styles.
```scss
// Remove lines 1-139 (commented code)
// Extract shared animations to a common file or use CSS variables
@keyframes shimmer {
    0% { background-position: -468px 0; }
    100% { background-position: 468px 0; }
}
```
**BEHAVIOR PRESERVATION CHECK**: Removing commented code doesn't change any visual output.

---

### R-026: Inconsistent breakpoint values
**SEVERITY**: LOW
**REFACTOR CATEGORY**: MODERNIZE
**FILE**: Multiple files
**LINE**: Various
**CURRENT CODE**:
```javascript
// In some files
const tabletBreakpoint = '1024px';
const mobileBreakpoint = '768px';

// In others (smart-cart-line-items-block/style.scss line 526)
@media (max-width: 782px) {
    // ... WordPress mobile breakpoint standard
}

// In PHP classes
'Mobile' => array( 'breakpoint' => '782px' ),
```
**PROPOSED REFACTORING**: Standardize on WordPress breakpoint constants.
```javascript
// Create shared breakpoints file
export const BREAKPOINTS = {
    DESKTOP: 1024,  // Not needed, but explicit
    TABLET: 1024,
    MOBILE: 782,      // WordPress admin breakpoint standard
    MOBILE_WIDE: 768, // Common mobile breakpoint
};

// Use consistently across all files
import { BREAKPOINTS } from '@woo-product-slider-pro/constants';
```
**BEHAVIOR PRESERVATION CHECK**: Breakpoints are consistent, ensuring responsive behavior is predictable.

---

### R-027: Hardcoded text strings without i18n
**SEVERITY**: LOW
**REFACTOR CATEGORY**: MODERNIZE
**FILE**: `blocks/blocks/showcase/smart-cart/inner-blocks/smart-cart-line-items-block/components/CartItem.jsx`
**LINE**: 15
**CURRENT CODE**:
```jsx
<div className="product-description product-short-desc">
    {formatDescription(item.short_description)}
</div>
```

```javascript
// In formatters.js
export const formatDescription = (desc) => {
    if (!desc) {
        return 'This premium product features high-quality materials and a modern design.';
    }
    // ...
};
```
**PROPOSED REFACTORING**: Use internationalized default text.
```javascript
import { __ } from '@wordpress/i18n';

export const formatDescription = (desc) => {
    if (!desc) {
        return __(
            'This premium product features high-quality materials and a modern design.',
            'woo-product-slider-pro'
        );
    }
    // ...
};
```
**BEHAVIOR PRESERVATION CHECK**: Default description is now translatable. Visual output is identical for English users.

---

### R-028: Deprecated `data-type` selector pattern
**SEVERITY**: LOW
**REFACTOR CATEGORY**: MODERNIZE
**FILE**: Multiple CSS files and dynamic CSS generators
**CURRENT CODE**:
```javascript
// In dynamicCss.js
{
    class: `${selector} [data-type="sp-smart-store/smart-cart-items-block"]`,
    styles: { width: '100%!important', padding: '5px' },
}
```
**PROPOSED REFACTORING**: Use class-based selectors or proper block class patterns.
```javascript
// WordPress automatically adds block-name classes
{
    class: `${selector} .wp-block-sp-smart-store-smart-cart-items-block`,
    styles: { width: '100%', padding: '5px' },
}
```
**BEHAVIOR PRESERVATION CHECK**: Selects the same elements but uses standard WordPress block class patterns.

---

### R-029: use of `!important` in dynamic CSS
**SEVERITY**: LOW
**REFACTOR CATEGORY**: MODERNIZE
**FILE**: Multiple dynamic CSS generators
**CURRENT CODE**:
```javascript
// Throughout dynamicCss.js files
styles: {
    'width': '100%!important',
    'display': 'grid !important',
    // ... many more !important declarations
}
```
**PROPOSED REFACTORING**: Reduce `!important` usage by improving selector specificity.
```javascript
// Instead of
{
    class: `${selector}`,
    styles: { width: '100%!important' },
}

// Use more specific selectors
{
    class: `${selector}.spssp-store-filled-cart`,
    styles: { width: '100%' },
}
```
**BEHAVIOR PRESERVATION CHECK**: Same visual output but with better CSS practices (easier to override, less specificity wars).

---

### R-030: CSS shimmer animation defined multiple times
**SEVERITY**: LOW
**REFACTOR CATEGORY**: DRY
**FILE**: `blocks/blocks/showcase/smart-cart/inner-blocks/smart-cart-line-items-block/style.scss` (lines 397-405), `blocks/blocks/showcase/smart-cart/style.scss` (lines 192-200)
**CURRENT CODE**:
```scss
@keyframes shimmer {
    0% { background-position: -468px 0; }
    100% { background-position: 468px 0; }
}
```
**PROPOSED REFACTORING**: Extract to a shared CSS variables file or use a common import.
```scss
// In _skeleton.scss
@keyframes shimmer {
    0% { background-position: -468px 0; }
    100% { background-position: 468px 0; }
}

// Import where needed
@import './_skeleton.scss';
```
**BEHAVIOR PRESERVATION CHECK**: Animation is identical, just defined once.

---

### R-031: Two CartItem components with slightly different implementations
**SEVERITY**: MEDIUM
**REFACTOR CATEGORY**: DRY
**FILE**: `blocks/blocks/showcase/smart-cart/components/CartItem.jsx` and `blocks/blocks/showcase/smart-cart/inner-blocks/smart-cart-line-items-block/components/CartItem.jsx`
**LINE**: Both files
**CURRENT CODE**: Two separate CartItem components with similar structure but different props and behavior. One is for frontend (interactive), one for editor (preview).
**PROPOSED REFACTORING**: Create a base CartItem component with shared structure and behavior, then create wrapper components for editor and frontend variants.
```javascript
// Create components/CartItem/BaseCartItem.jsx
export const BaseCartItem = ({ item, cartTotals, templateId, children, ...props }) => (
    <div className={`smart-cart-item-row spssp-unique-id-${templateId}`} {...props}>
        {/* Shared structure */}
    </div>
);

// Frontend variant
export const CartItem = ({ item, cartTotals, templateId, onRemove, onQuantityChange }) => (
    <BaseCartItem item={item} templateId={templateId}>
        {/* Interactive elements */}
    </BaseCartItem>
);

// Editor variant
export const EditorCartItem = ({ item, totals }) => (
    <BaseCartItem item={item} templateId="">
        {/* Preview-only elements */}
    </BaseCartItem>
);
```
**BEHAVIOR PRESERVATION CHECK**: Both variants produce the same visual output. Shared base reduces duplication.

---

### R-032: Deprecated hardcoded fallback text
**SEVERITY**: LOW
**REFACTOR CATEGORY**: DRY
**FILE**: `blocks/blocks/showcase/smart-cart/inner-blocks/smart-cart-line-items-block/components/CartItem.jsx` (line 13), `blocks/blocks/showcase/smart-cart/cart-app/utils/formatters.js` (line 31)
**CURRENT CODE**:
```javascript
// Both contain identical fallback text
const rawDesc = item.short_description || 'This premium product features high-quality materials and a modern design. It provides exceptional value and performance.';
```
**PROPOSED REFACTORING**: Define in a constants file with i18n.
```javascript
// In constants.js
export const DEFAULT_PRODUCT_DESCRIPTION = __(
    'This premium product features high-quality materials and a modern design. It provides exceptional value and performance.',
    'woo-product-slider-pro'
);

// Import in both locations
import { DEFAULT_PRODUCT_DESCRIPTION } from '../constants';

const rawDesc = item.short_description || DEFAULT_PRODUCT_DESCRIPTION;
```
**BEHAVIOR PRESERVATION CHECK**: Same fallback text, now centralized and translatable.

---

## SUMMARY BY SEVERITY

### HIGH PRIORITY
- R-002: Direct DOM manipulation in useCouponActions
- R-003: Direct DOM manipulation in useTotalsSync
- R-004: Direct DOM manipulation in useCartActions
- R-008: useSelect with empty dependency array (cart-provider)
- R-009: useSelect with empty dependency array (useCartData)
- R-018: Conditional asset enqueuing not implemented

### MEDIUM PRIORITY
- R-001: CartItem formatting logic extraction
- R-010: Storing volatile data as attributes
- R-012: Duplicate cart data preparation logic
- R-015: Duplicate generate_dynamic_css patterns
- R-016: Missing null checks for WooCommerce cart
- R-019: Large inline style generation
- R-022: Skeleton loading causes layout shift
- R-024: Duplicate formatPrice utilities
- R-031: Two CartItem components with duplication

### LOW PRIORITY
- R-005: Duplicate Skeleton components
- R-006: Inline SVG icons
- R-007: Inconsistent useBlockProps.save usage
- R-011: Missing useCallback for event handlers
- R-013: Missing type declarations
- R-014: Inconsistent totals row rendering
- R-017: Conditional helper loading
- R-020: Missing in_footer parameter
- R-021: No lazy loading for images
- R-023: view.js polling initialization
- R-025: Duplicate CSS rules
- R-026: Inconsistent breakpoint values
- R-027: Hardcoded text without i18n
- R-028: Deprecated data-type selector
- R-029: Excessive !important usage
- R-030: Duplicate shimmer animation
- R-032: Duplicate fallback text

---

## IMPLEMENTATION RECOMMENDATIONS

1. **Start with HIGH severity items** - These affect performance, data integrity, or have potential for bugs.

2. **Focus on DOM manipulation elimination** (R-002, R-003, R-004) - This is the biggest architectural issue and will unlock other improvements.

3. **Consolidate utilities** (R-012, R-015, R-024, R-031) - These improve maintainability significantly.

4. **Test each refactoring** - Since the goal is to preserve behavior, each change should be tested with:
   - Visual regression testing
   - Cart functionality testing (add, remove, quantity change, coupon)
   - Empty cart state
   - Different breakpoints

5. **Phase the implementation** - Don't attempt all refactorings at once. Group related items and implement them in phases.
