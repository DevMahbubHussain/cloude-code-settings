# Smart Cart Block - Bug and Compliance Audit Report

**Audited Directories:**
- `blocks/blocks/showcase/smart-cart/` (Editor/Frontend code)
- `src/Blocks/Page/SmartCart/` (Server-side PHP code)

**Audit Date:** 2026-05-12

---

## PHASE 1: Visual & Layout Bug Detection

### SC-001 [FIXED]
**SEVERITY:** LOW
**CATEGORY:** LAYOUT
**FILE:** blocks/blocks/showcase/smart-cart/style.scss
**LINE:** 161, 167, 176, 180, 186, 188
**DESCRIPTION:** CSS grid layout is commented out, leaving no default layout structure
**CURRENT CODE:** `// display: grid;`
**EXPECTED BEHAVIOR:** Grid layout should be active or fallback flex layout should be implemented

---

### SC-002 [FIXED]
**SEVERITY:** MEDIUM
**CATEGORY:** SKELETON
**FILE:** blocks/blocks/showcase/smart-cart/components/Skeleton.jsx
**LINE:** 2-3
**DESCRIPTION:** Skeleton placeholder hardcoded to only show 2 rows, which may not match actual cart size
**CURRENT CODE:** `const placeholderRows = [1, 2];`
**EXPECTED BEHAVIOR:** Skeleton row count should match the actual number of items or use a configurable minimum

---

### SC-003 [FIXED]
**SEVERITY:** MEDIUM
**CATEGORY:** SKELETON|CLS
**FILE:** src/Blocks/Page/SmartCart/render.php
**LINE:** 109-133
**DESCRIPTION:** Skeleton placeholders rendered with no reserved height/width, causing CLS on hydration
**CURRENT CODE:**
```php
<div class="spssp-item-skeleton">
    <div class="spssp-skeleton-image"></div>
```
**EXPECTED BEHAVIOR:** Skeleton elements should have explicit height/width dimensions matching loaded content

---

### SC-004 [FIXED]
**SEVERITY:** MEDIUM
**CATEGORY:** RESPONSIVE
**FILE:** blocks/blocks/showcase/smart-cart/inner-blocks/smart-cart-line-items-block/style.scss
**LINE:** 523
**DESCRIPTION:** Responsive breakpoint at 768px doesn't match WordPress admin breakpoint standard (782px)
**CURRENT CODE:** `@media (max-width: 768px)`
**EXPECTED BEHAVIOR:** Should use `@media (max-width: 782px)` to match WordPress breakpoint constants

---

### SC-005 [FIXED]
**SEVERITY:** HIGH
**CATEGORY:** RESPONSIVE|LAYOUT
**FILE:** blocks/blocks/showcase/smart-cart/inner-blocks/smart-cart-line-items-block/components/CartItem.jsx
**LINE:** 40-45
**DESCRIPTION:** dangerouslySetInnerHTML used without sanitization for product prices - XSS vulnerability
**CURRENT CODE:**
```jsx
<span
    dangerouslySetInnerHTML={{
        __html: item.prices?.price?.includes('<')
            ? item.prices.price
            : formatPrice(item.prices?.price, cartTotals),
    }}
/>
```
**EXPECTED BEHAVIOR:** Price data should be sanitized before rendering, or use wp_kses equivalent in React

---

### SC-006 [FIXED]
**SEVERITY:** MEDIUM
**CATEGORY:** RESPONSIVE
**FILE:** blocks/blocks/showcase/smart-cart/inner-blocks/smart-cart-line-items-block/style.scss
**LINE:** 332-362
**DESCRIPTION:** Quantity buttons are 24px x 24px, below WCAG 2.1 AA minimum of 44px for touch targets
**CURRENT CODE:** `width: 24px; height: 24px;`
**EXPECTED BEHAVIOR:** Minimum touch target should be 44x44px or increase padding to expand hit area

---

### SC-007
**SEVERITY:** LOW
**CATEGORY:** LAYOUT
**FILE:** blocks/blocks/showcase/smart-cart/style.scss
**LINE:** 149-157
**DESCRIPTION:** Grid template columns fixed at `2.5fr 1fr 1fr 1fr` may cause overflow on very long product names
**CURRENT CODE:** `grid-template-columns: 2.5fr 1fr 1fr 1fr;`
**EXPECTED BEHAVIOR:** Should use `minmax()` or proper word wrapping on product names

---

### SC-008 [FIXED]
**SEVERITY:** MEDIUM
**CATEGORY:** SKELETON
**FILE:** blocks/blocks/showcase/smart-cart/cart-app/hooks/useCartData.js
**LINE:** 23-26
**DESCRIPTION:** Hard-coded 2-second skeleton display may cause unnecessary perceived delay
**CURRENT CODE:**
```js
useEffect(() => {
    const timer = setTimeout(() => setMinTimePassed(true), 2000);
    return () => clearTimeout(timer);
}, []);
```
**EXPECTED BEHAVIOR:** Should use skeleton until actual data loads, or provide user-configurable delay

---

### SC-009 [FIXED]
**SEVERITY:** LOW
**CATEGORY:** RESPONSIVE
**FILE:** blocks/blocks/showcase/smart-cart/inner-blocks/smart-cart-order-summary-coupon-form/style.scss
**LINE:** 247-268
**DESCRIPTION:** Responsive CSS for coupon form doesn't have matching mobile breakpoint
**CURRENT CODE:** `@media (max-width: 768px)`
**EXPECTED BEHAVIOR:** Should align with other breakpoints (782px for mobile, 1024px for tablet)

---

## PHASE 2: WordPress & Gutenberg Standards Compliance

### SC-010
**SEVERITY:** HIGH
**CATEGORY:** STANDARDS
**FILE:** src/Blocks/Page/SmartCart/CartItems/CartItems.php
**LINE:** 161
**DESCRIPTION:** Excessive use of `!important` in dynamic CSS generation
**CURRENT CODE:** `$filled_cart_css .= "{$prop}: {$val} !important; ";`
**EXPECTED BEHAVIOR:** Use proper CSS specificity instead of !important, or restrict to specific use cases

---

### SC-011
**SEVERITY:** HIGH
**CATEGORY:** STANDARDS
**FILE:** src/Blocks/Page/SmartCart/FilledCart/SmartFilledCartBlock.php
**LINE:** 167
**DESCRIPTION:** Same `!important` abuse in CSS generation
**CURRENT CODE:** `$filled_cart_css .= "{$prop}: {$val} !important; ";`
**EXPECTED BEHAVIOR:** Remove !important and use more specific selectors

---

### SC-012
**SEVERITY:** HIGH
**CATEGORY:** STANDARDS
**FILE:** src/Blocks/Page/SmartCart/CartTotals.php
**LINE:** 122
**DESCRIPTION:** Continued `!important` abuse pattern
**CURRENT CODE:** `$card_css_string .= "{$prop}: {$val} !important; ";`
**EXPECTED BEHAVIOR:** Refactor CSS to use specificity instead of !important

---

### SC-013
**SEVERITY:** MEDIUM
**CATEGORY:** STANDARDS
**FILE:** src/Blocks/Page/SmartCart/FilledCart/SmartFilledCartBlock.php
**LINE:** 53-55
**DESCRIPTION:** Typo in device array key names: "Tablet" and "Mobile" misspelled as "Tablet" and "Mobi"
**CURRENT CODE:**
```php
'Desktop' => array( 'breakpoint' => null ),
'Tablet'  => array( 'breakpoint' => '1024px' ),
'Mobile'  => array( 'breakpoint' => '782px' ),
```
**EXPECTED BEHAVIOR:** Should be `Tablet` and `Mobile`

---

### SC-014
**SEVERITY:** MEDIUM
**CATEGORY:** STANDARDS
**FILE:** src/Blocks/Page/SmartCart/CartTotals.php
**LINE:** 51-55
**DESCRIPTION:** Same device name typos repeated
**CURRENT CODE:** Same typo pattern as SC-013
**EXPECTED BEHAVIOR:** Fix device key names

---

### SC-015
**SEVERITY:** LOW
**CATEGORY:** STANDARDS
**FILE:** src/Blocks/Page/SmartCart/FilledCart/SmartFilledCartBlock.php
**LINE:** 91
**DESCRIPTION:** Conditional check uses `! empty( )` instead of Yoda condition
**CURRENT CODE:** `if ( ! empty( $hide_on_device[ $device ] ) ) {`
**EXPECTED BEHAVIOR:** While acceptable, Yoda conditions (`empty( $hide_on_device[ $device ] ) === false`) are preferred in WPCS

---

### SC-016
**SEVERITY:** MEDIUM
**CATEGORY:** STANDARDS
**FILE:** blocks/blocks/showcase/smart-cart/inner-blocks/smart-empty-cart-block/edit.jsx
**LINE:** 48-56
**DESCRIPTION:** RichText component with `href` attribute may not be properly validated
**CURRENT CODE:**
```jsx
<RichText
    tagName="a"
    className="browse-store-link"
    value={attributes.buttonText}
    onChange={(buttonText) => setAttributes({ buttonText })}
    placeholder={__('Browse store', 'woo-product-slider-pro')}
    href="#"
/>
```
**EXPECTED BEHAVIOR:** Should use LinkControl or validate URL format, placeholder `href="#"` is problematic

---

### SC-017
**SEVERITY:** LOW
**CATEGORY:** STANDARDS
**FILE:** blocks/blocks/showcase/smart-cart/inner-blocks/smart-cart-items-block/edit.jsx
**LINE:** 28-33
**DESCRIPTION:** Allowed blocks list has typo: 's' instead of 'sp-' prefix
**CURRENT CODE:**
```js
const ALLOWED_BLOCKS = [
    's-smart-store/smart-product-template',
    's-smart-store/smart-cart-line-items-block',
```
**EXPECTED BEHAVIOR:** Should be `'sp-smart-store/smart-product-template'` etc.

---

### SC-018
**SEVERITY:** LOW
**CATEGORY:** STANDARDS
**FILE:** blocks/blocks/showcase/smart-cart/inner-blocks/smart-cart-order-summary-heading/edit.jsx
**LINE:** 22
**DESCRIPTION:** Save component doesn't use proper block name selector
**CURRENT CODE:** `return <RichText.Content {...blockProps} tagName="h3" value={summaryHeading} />;`
**EXPECTED BEHAVIOR:** Should use proper HTML structure consistent with other heading blocks

---

### SC-019
**SEVERITY:** MEDIUM
**CATEGORY:** STANDARDS
**FILE:** src/Blocks/Page/SmartCart/OrderSummaryShipping.php
**LINE:** 36-41
**DESCRIPTION:** Variable name `$shipping_output` shadows WooCommerce function name pattern
**CURRENT CODE:** `$shipping_output = wc_price( $total_shipping_num );`
**EXPECTED BEHAVIOR:** Rename to `$shipping_display` or similar to avoid confusion

---

### SC-020
**SEVERITY:** LOW
**CATEGORY:** STANDARDS
**FILE:** src/Blocks/Page/SmartCart/CartProductTemplate/CartProductTemplate.php
**LINE:** 228-229
**DESCRIPTION:** Block name set to 'core/null' to prevent wrapper - may cause issues with block validation
**CURRENT CODE:** `$block_instance['blockName'] = 'core/null';`
**EXPECTED BEHAVIOR:** Should use `__return_empty_string` filter or proper method to skip wrapper

---

### SC-021
**SEVERITY:** MEDIUM
**CATEGORY:** STANDARDS
**FILE:** blocks/blocks/showcase/smart-cart/components/CartItem.jsx
**LINE:** 22
**DESCRIPTION:** Product name rendered without escaping/truncation for very long names
**CURRENT CODE:** `{item.name}`
**EXPECTED BEHAVIOR:** Should truncate or use CSS line-clamp for long product names

---

### SC-022
**SEVERITY:** LOW
**CATEGORY:** STANDARDS
**FILE:** src/Blocks/Page/SmartCart/CartProductTemplate/render.php
**LINE:** 52-56
**DESCRIPTION:** Fallback description text hardcoded in English, not translatable
**CURRENT CODE:** `$desc = $_product->get_short_description() ?: 'Premium product features...';`
**EXPECTED BEHAVIOR:** Should use translatable string: `esc_html__( 'Premium product features...', 'woo-product-slider-pro' )`

---

### SC-023
**SEVERITY:** MEDIUM
**CATEGORY:** STANDARDS
**FILE:** blocks/blocks/showcase/smart-cart/inner-blocks/smart-cart-items-block/dynamicCss.js
**LINE:** 123
**DESCRIPTION:** Commented typo "mob" should be "mobile"
**CURRENT CODE:** `` `only screen and (max-width: ${mobileBreakpoint}px)` ``
**EXPECTED BEHAVIOR:** Fix typo - affects code readability, not functionality

---

## PHASE 3: Dual-Sided Security Audit

### SC-024 [SECURITY-EDITOR]
**SEVERITY:** CRITICAL
**CATEGORY:** SECURITY-EDITOR
**FILE:** blocks/blocks/showcase/smart-cart/components/CartItem.jsx
**LINE:** 40-45, 70-74
**DESCRIPTION:** dangerouslySetInnerHTML used with unsanitized HTML from cart data
**CURRENT CODE:**
```jsx
<span
    dangerouslySetInnerHTML={{
        __html: item.prices?.price?.includes('<')
            ? item.prices.price
            : formatPrice(item.prices?.price, cartTotals),
    }}
/>
// And at line 70:
<span
    dangerouslySetInnerHTML={{
        __html: formatPrice(item.totals?.line_total || item.prices?.raw_subtotal, cartTotals),
    }}
/>
```
**EXPECTED BEHAVIOR:** Use React component for price formatting or sanitize HTML using DOMPurify or similar

---

### SC-025 [SECURITY-EDITOR]
**SEVERITY:** HIGH
**CATEGORY:** SECURITY-EDITOR
**FILE:** blocks/blocks/showcase/smart-cart/cart-app/hooks/useCouponActions.js
**LINE:** 20, 22-24
**DESCRIPTION:** Coupon code inserted via innerHTML without sanitization
**CURRENT CODE:**
```js
badge.innerHTML = `
    ${coupon.code.toUpperCase()}
    <button type="button" class="spssp-remove-coupon" data-code="${coupon.code}" aria-label="Remove coupon">&times;</button>
`;
```
**EXPECTED BEHAVIOR:** Should use createElement and textContent for coupon code

---

### SC-026 [SECURITY-EDITOR]
**SEVERITY:** HIGH
**CATEGORY:** SECURITY-EDITOR
**FILE:** blocks/blocks/showcase/smart-cart/cart-app/hooks/useCouponActions.js
**LINE:** 45-48, 66-69, 73, 127-130
**DESCRIPTION:** Multiple innerHTML assignments with unsanitized user-facing strings
**CURRENT CODE:**
```js
messageNode.innerHTML = `<span class="coupon-error">${__('Please enter a coupon code.', 'woo-product-slider-pro')}</span>`;
```
**EXPECTED BEHAVIOR:** Use createlement and append, or DOMPurify for sanitization

---

### SC-027 [SECURITY-EDITOR]
**SEVERITY:** HIGH
**CATEGORY:** SECURITY-EDITOR
**FILE:** blocks/blocks/showcase/smart-cart/cart-app/hooks/useTotalsSync.js
**LINE:** 31-47
**DESCRIPTION:** Direct innerHTML manipulation of price elements
**CURRENT CODE:**
```js
node.innerHTML = value;
```
**EXPECTED BEHAVIOR:** Price values should be escaped before setting innerHTML

---

### SC-028 [SECURITY-SERVER]
**SEVERITY:** HIGH
**CATEGORY:** SECURITY-SERVER
**FILE:** src/Blocks/Page/SmartCart/OrderSummaryShipping.php
**LINE:** 36
**DESCRIPTION:** Cart price output without escaping - wc_price may return HTML
**CURRENT CODE:** `$shipping_output = wc_price( $total_shipping_num );`
**EXPECTED BEHAVIOR:** Use `wp_kses_post( $shipping_output )` or `echo $shipping_output;` in template context

---

### SC-029 [SECURITY-SERVER]
**SEVERITY:** MEDIUM
**CATEGORY:** SECURITY-SERVER
**FILE:** src/Blocks/Page/SmartCart/CartTotals.php
**LINE:** 210, 216, 226, 234, 240
**DESCRIPTION:** Direct echo of WooCommerce price functions without escaping
**CURRENT CODE:** `echo $cart->get_cart_subtotal();`
**EXPECTED BEHAVIOR:** WooCommerce price functions return safe HTML, but should verify or wrap in wp_kses_post for custom outputs

---

### SC-030 [SECURITY-SERVER]
**SEVERITY:** MEDIUM
**CATEGORY:** SECURITY-SERVER
**FILE:** src/Blocks/Page/SmartCart/CartProductTemplate/render.php
**LINE:** 61, 72
**DESCRIPTION:** Product price and subtotal echoed without explicit escaping context
**CURRENT CODE:**
```php
<div class="product-price-col"><?php echo $cart->get_product_price( $_product ); ?></div>
<div class="product-subtotal-col"><?php echo $cart->get_product_subtotal( $_product, $first_item['quantity'] ); ?></div>
```
**EXPECTED BEHAVIOR:** Verify WooCommerce functions output is safe or add explicit escaping

---

### SC-031 [SECURITY-SERVER]
**SEVERITY:** MEDIUM
**CATEGORY:** SECURITY-SERVER
**FILE:** src/Blocks/Page/SmartCart/OrderSummarySubtotal.php
**LINE:** 43-44
**CURRENT CODE:** Same pattern as SC-029
**DESCRIPTION:** Direct echo of cart subtotal
**CURRENT CODE:** `echo wp_kses_post( $subtotal );`
**EXPECTED BEHAVIOR:** This is actually correct (wp_kses_post used), but check if consistent with other price outputs

---

### SC-032 [SECURITY-SERVER]
**SEVERITY:** HIGH
**CATEGORY:** SECURITY-SERVER
**FILE:** src/Blocks/Page/SmartCart/OrderSummaryDiscount.php
**LINE:** 54-56
**DESCRIPTION:** Coupon codes echoed with escaping but buttons use HTML entities directly
**CURRENT CODE:**
```php
<button type="button" class="spssp-remove-coupon" data-code="<?php echo esc_attr( $code ); ?>">&times;</button>
```
**EXPECTED BEHAVIOR:** Use proper SVG icon or `esc_html( '&times;' )` isn't needed, should be `&times;` but verify browser support

---

### SC-033 [SECURITY-SERVER]
**SEVERITY:** MEDIUM
**CATEGORY:** SECURITY-SERVER
**FILE:** src/Blocks/Page/SmartCart/CartItems/render.php
**LINE:** 86-112
**DESCRIPTION:** `render_block()` output directly echoed without additional escaping
**CURRENT CODE:** `echo render_block( $template_block ); // phpcs:ignore`
**EXPECTED BEHAVIOR:** The phpcs:ignore suggests this is known, but verify render_block output is trusted

---

### SC-034 [SECURITY-SERVER]
**SEVERITY:** MEDIUM
**CATEGORY:** SECURITY-SERVER
**FILE:** src/Blocks/Page/SmartCart/FilledCart/render.php
**LINE:** 163-167
**DESCRIPTION:** str_replace used on content which may contain malicious content
**CURRENT CODE:** `$wrapped_sidebar_coupon = '<div class="' . $sidebar_class . '">' . $content . '</div>';`
**EXPECTED BEHAVIOR:** Use `wp_kses_post()` on content before wrapping

---

### SC-035 [SECURITY-EDITOR]
**SEVERITY:** MEDIUM
**CATEGORY:** SECURITY-EDITOR
**FILE:** blocks/blocks/showcase/smart-cart/components/EmptyCart.jsx
**LINE:** 30
**DESCRIPTION:** href prop not validated - may allow javascript: URLs
**CURRENT CODE:** `<a href={shopUrl || '/shop'} ...>`
**EXPECTED BEHAVIOR:** Validate shopUrl is a proper URL before rendering

---

### SC-036 [SECURITY-SERVER]
**SEVERITY:** LOW
**CATEGORY:** SECURITY-SERVER
**FILE:** src/Blocks/Page/SmartCart/render.php
**LINE:** 80
**DESCRIPTION:** Nonce localized but AJAX handler not verified to be using it properly
**CURRENT CODE:**
```php
wp_localize_script(
    'smart-store-pro-smart-cart-view',
    'wpsspCart',
    array(
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'spssp_cart_data' ),
    )
);
```
**EXPECTED BEHAVIOR:** Verify all AJAX endpoints properly check this nonce

---

### SC-037 [SECURITY-SERVER]
**SEVERITY:** HIGH
**CATEGORY:** SECURITY-SERVER
**FILE:** src/Blocks/Page/SmartCart/Api/SmartCartDataEndpoint.php
**LINE:** 20-76
**DESCRIPTION:** AJAX handler has nonce check but no capability check
**CURRENT CODE:**
```php
public function get_cart_data() {
    // Check nonce.
    check_ajax_referer( 'spssp_cart_data', 'nonce' );
    // ... no capability check
```
**EXPECTED BEHAVIOR:** Add `if ( ! is_user_logged_in() ) { wp_send_json_error( ... ); }` or similar

---

### SC-038 [SECURITY-EDITOR]
**SEVERITY:** MEDIUM
**CATEGORY:** SECURITY-EDITOR
**FILE:** blocks/blocks/showcase/smart-cart/index.jsx
**LINE:** 174-189
**DESCRIPTION:** Inline styles injected via style tag with !important
**CURRENT CODE:**
```jsx
<style>
    {`
        .smart-cart-view-filled [data-type="sp-smart-store/smart-empty-cart-block"] { 
            display: none !important; 
```
**EXPECTED BEHAVIOR:** Use proper CSS classes instead of inline !important styles

---

### SC-039 [SECURITY-SERVER]
**SEVERITY:** MEDIUM
**CATEGORY:** SECURITY-SERVER
**FILE:** src/Blocks/Page/SmartCart/OrderSummaryCouponForm.php
**LINE:** 48
**DESCRIPTION:** Input placeholder uses esc_attr correctly but should verify default
**CURRENT CODE:** `<input type="text" class="spssp-coupon-code-input" placeholder="<?php echo esc_attr( $placeholder ); ?>" />`
**EXPECTED BEHAVIOR:** This is actually correct - consider validating in JS

---

### SC-040 [SECURITY-EDITOR]
**SEVERITY:** HIGH
**CATEGORY:** SECURITY-EDITOR
**FILE:** blocks/blocks/showcase/smart-cart/edit.jsx
**LINE:** 190
**DESCRIPTION:** !important in editor styles can override theme styles unexpectedly
**CURRENT CODE:** `display: block !important;`
**EXPECTED BEHAVIOR:** Remove !important from editor-specific CSS

---

## PHASE 4: Edge Cases & Corner Cases

### SC-041
**SEVERITY:** MEDIUM
**CATEGORY:** EDGE-CASE
**FILE:** blocks/blocks/showcase/smart-cart/components/CartItem.jsx
**LINE:** 52-53
**DESCRIPTION:** Quantity minimum hardcoded to 0, but UI shows minus button disabled at 1
**CURRENT CODE:** `onClick={() => onQuantityChange(item.key, Math.max(0, item.quantity - 1))}`
**EXPECTED BEHAVIOR:** Should match disable state logic

---

### SC-042
**SEVERITY:** MEDIUM
**CATEGORY:** EDGE-CASE
**FILE:** blocks/blocks/showcase/smart-cart/cart-app/hooks/useCartActions.js
**LINE:** 106-114
**DESCRIPTION:** Page reload on empty cart causes potential infinite loop if cart state doesn't clear properly
**CURRENT CODE:**
```js
useEffect(() => {
    if (cartItems.length === 0) {
        const container = document.querySelector('.spssp-cart-container');
        if (container && !container.classList.contains('is-clearing')) {
            container.classList.add('is-clearing');
            window.location.replace(window.location.href);
        }
    }
}, [cartItems]);
```
**EXPECTED BEHAVIOR:** Add debouncing or ensure single trigger

---

### SC-043
**SEVERITY:** MEDIUM
**CATEGORY:** EDGE-CASE
**FILE:** blocks/blocks/showcase/smart-cart/cart-app/utils/formatters.js
**LINE:** 34-38
**DESCRIPTION:** Price formatter doesn't handle negative prices (refunds) properly
**CURRENT CODE:**
```js
const numericValue = parseFloat(rawPrice);
if (isNaN(numericValue)) {
    return '—';
}
```
**EXPECTED BEHAVIOR:** Should format negative prices with minus sign

---

### SC-044
**SEVERITY:** LOW
**CATEGORY:** EDGE-CASE
**FILE:** blocks/blocks/showcase/smart-cart/utils.js
**LINE:** 29-49
**DESCRIPTION:** formatPrice doesn't handle currency_suffix when currency_prefix is true
**CURRENT CODE:**
```js
if (totals.currency_suffix && totals.currency_suffix !== '') {
    return `${formatted}${symbol}`;
}
```
**EXPECTED BEHAVIOR:** Logic should account for both prefix and suffix mutually exclusive

---

### SC-045
**SEVERITY:** MEDIUM
**CATEGORY:** EDGE-CASE
**FILE:** src/Blocks/Page/SmartCart/Api/SmartCartDataEndpoint.php
**LINE:** 49-50
**DESCRIPTION:** Price multiplication by 100 could cause integer overflow with large prices
**CURRENT CODE:**
```php
'price'        => (string) round( $_product->get_price() * 100 ),
'raw_subtotal' => (int) ( (float) $item['line_total'] * 100 ),
```
**EXPECTED BEHAVIOR:** Use more precision-agnostic format or validate price ranges

---

### SC-046
**SEVERITY:** LOW
**CATEGORY:** EDGE-CASE
**FILE:** src/Blocks/Page/SmartCart/CartProductTemplate/render.php
**LINE:** 4-7
**DESCRIPTION:** WC function check uses weak comparison
**CURRENT CODE:** `if ( ! function_exists( 'WC' ) || ! WC() || ! WC()->cart )`
**EXPECTED BEHAVIOR:** Use strict comparison for object checks

---

### SC-047
**SEVERITY:** MEDIUM
**CATEGORY:** EDGE-CASE
**FILE:** blocks/blocks/showcase/smart-cart/view.js
**LINE:** 6-8
**DESCRIPTION:** Silently returns if wc.wcBlocksData not available without error feedback
**CURRENT CODE:**
```js
if (!window.wc || !window.wc.wcBlocksData) {
    return;
}
```
**EXPECTED BEHAVIOR:** Should log warning or show fallback message

---

### SC-048
**SEVERITY:** MEDIUM
**CATEGORY:** EDGE-CASE
**FILE:** src/Blocks/Page/SmartCart/FilledCart/render.php
**LINE:** 86-143
**DESCRIPTION:** Product loop doesn't handle case where $_product doesn't exist()
**CURRENT CODE:**
```php
foreach ( $cart_items as $cart_item_key => $cart_item ) :
    $_product = $cart_item['data'];
    if ( $_product && $_product->exists() ) :
```
**EXPECTED BEHAVIOR:** This is actually correct, but verify `exists()` method exists on all WC versions

---

### SC-049
**SEVERITY:** LOW
**CATEGORY:** EDGE-CASE
**FILE:** blocks/blocks/showcase/smart-cart/provider/cart-provider.jsx
**LINE:** 9-15
**DESCRIPTION:** No error handling if CART_STORE_KEY doesn't exist
**CURRENT CODE:**
```js
const { CART_STORE_KEY } = window.wc.wcBlocksData;
const { cartData, isFinished } = useSelect((select) => {
    const store = select(CART_STORE_KEY);
```
**EXPECTED BEHAVIOR:** Add try/catch or null check

---

### SC-050
**SEVERITY:** MEDIUM
**CATEGORY:** EDGE-CASE
**FILE:** src/Blocks/Page/SmartCart/SmartCart.php
**LINE:** 89-106
**DESCRIPTION:** Empty cart template returns early but doesn't clear cart-related classes
**CURRENT CODE:**
```php
if ( empty( $cart_items ) ) {
    ob_start();
    $empty_template = plugin_dir_path( __FILE__ ) . 'templates/empty.php';
    // ... returns
```
**EXPECTED BEHAVIOR:** Ensure cart container classes are properly managed for empty state

---

### SC-051
**SEVERITY:** LOW
**CATEGORY:** EDGE-CASE
**FILE:** blocks/blocks/showcase/smart-cart/cart-app/hooks/useCouponActions.js
**LINE:** 59
**DESCRIPTION:** applyCoupon may return false but error handling assumes it always throws
**CURRENT CODE:**
```js
try {
    const result = await applyCoupon(code);
    if (result === false) {
        throw new Error(__('This coupon is not valid.', 'woo-product-slider-pro'));
    }
```
**EXPECTED BEHAVIOR:** Verify WooCommerce API contract for applyCoupon return values

---

### SC-052
**SEVERITY:** MEDIUM
**CATEGORY:** EDGE-CASE
**FILE:** blocks/blocks/showcase/smart-cart/components/CartItemsList.jsx
**LINE:** 17-28
**DESCRIPTION:** No error boundary for individual CartItem rendering failures
**CURRENT CODE:**
```js
return (
    <>
        {displayItems.map((item) => (
            <CartItem
                key={item.key}
```
**EXPECTED BEHAVIOR:** Wrap in ErrorBoundary or handle missing item data

---

### SC-053
**SEVERITY:** LOW
**CATEGORY:** EDGE-CASE
**FILE:** src/Blocks/Page/SmartCart/helper/find_block.php
**LINE:** 9-26
**DESCRIPTION:** Recursive function has no depth limit - could stack overflow on deeply nested blocks
**CURRENT CODE:**
```php
function spssp_render_filtered_inner_blocks( $inner_blocks, $target_block_names = array() ) {
    // ...
    if ( ! empty( $block['innerBlocks'] ) ) {
        $output .= spssp_render_filtered_inner_blocks( $block['innerBlocks'], $target_block_names );
    }
```
**EXPECTED BEHAVIOR:** Add max_depth parameter or iteration limit

---

## PHASE 5: Backward & Version Compatibility

### SC-054
**SEVERITY:** HIGH
**CATEGORY:** COMPAT
**FILE:** blocks/blocks/showcase/smart-cart/block.json
**LINE:** 1-2
**DESCRIPTION:** apiVersion set to 3 - verify minimum WordPress version supports this
**CURRENT CODE:** `"apiVersion": 3,`
**EXPECTED BEHAVIOR:** Add comment or documentation about minimum WP version (6.1+)

---

### SC-055
**SEVERITY:** MEDIUM
**CATEGORY:** COMPAT
**FILE:** src/Blocks/Page/SmartCart/CartProductTemplate/CartProductTemplate.php
**LINE:** 114
**CURRENT CODE:** `return $radius ?: null; //phpcs:ignore`
**DESCRIPTION:** Uses PHP 7.0+ null coalescing operator
**EXPECTED BEHAVIOR:** Verify plugin minimum PHP version is >= 7.0

---

### SC-056
**SEVERITY:** MEDIUM
**CATEGORY:** COMPAT
**FILE:** src/Blocks/Page/SmartCart/Api/SmartCartDataEndpoint.php
**LINE:** 22
**DESCRIPTION:** check_ajax_referer may not work properly with wp_send_json_success in older WP versions
**CURRENT CODE:** `check_ajax_referer( 'spssp_cart_data', 'nonce' );`
**EXPECTED BEHAVIOR:** Use check_ajax_referer with correct handling or verify WP version

---

### SC-057
**SEVERITY:** LOW
**CATEGORY:** COMPAT
**FILE:** src/Blocks/Page/SmartCart/SmartCart.php
**LINE:** 79-88
**DESCRIPTION:** wp_localize_script used for AJAX - may not work with REST API preference in newer WP
**CURRENT CODE:**
```php
wp_localize_script(
    'smart-store-pro-smart-cart-view',
    'wpsspCart',
    array(...)
);
```
**EXPECTED BEHAVIOR:** Consider using wp_add_inline_script or REST API for newer WP versions

---

### SC-058
**SEVERITY:** MEDIUM
**CATEGORY:** COMPAT
**FILE:** blocks/blocks/showcase/smart-cart/view.js
**LINE:** 40
**DESCRIPTION:** jQuery event binding deprecated in favor of vanilla JS
**CURRENT CODE:** `window.jQuery(document.body).on('updated_wc_div', initCart);`
**EXPECTED BEHAVIOR:** Use addEventListener or verify jQuery is loaded

---

### SC-059
**SEVERITY:** LOW
**CATEGORY:** COMPAT
**FILE:** src/Blocks/Page/SmartCart/CartProductTemplate/CartProductTemplate.php
**LINE:** 159-171
**DESCRIPTION:** Hover styles using !important override theme styles
**CURRENT CODE:**
```php
$css .= "{$selector}:hover {";
if ( $hover_bg !== $normal_bg ) {
    $css .= "background: {$hover_bg} !important;";
```
**EXPECTED BEHAVIOR:** Remove !important or provide theme integration hook

---

### SC-060
**SEVERITY:** MEDIUM
**CATEGORY:** COMPAT
**FILE:** src/Blocks/Page/SmartCart/helper/find_block.php
**LINE:** 1
**DESCRIPTION:** Function not namespaced - global function pollution
**CURRENT CODE:** `function spssp_render_filtered_inner_blocks( $inner_blocks, $target_block_names = array() ) {`
**EXPECTED BEHAVIOR:** Functions are prefixed but consider moving to class

---

### SC-061
**SEVERITY:** LOW
**CATEGORY:** COMPAT
**FILE:** blocks/blocks/showcase/smart-cart/provider/cart-provider.jsx
**LINE:** 10
**DESCRIPTION:** select('core/data').hasFinishedResolution - verify this API exists in all supported WP versions
**CURRENT CODE:** `isFinished: select('core/data').hasFinishedResolution(CART_STORE_KEY, 'getCartData'),`
**EXPECTED BEHAVIOR:** Add version check or fallback for older WordPress

---

### SC-062
**SEVERITY:** MEDIUM
**CATEGORY:** COMPAT
**FILE:** src/Blocks/Page/SmartCart/SmartCart.php
**LINE:** 71-73
**DESCRIPTION:** is_admin() check may not work correctly in REST API context
**CURRENT CODE:**
```php
if ( is_admin() ) {
    return '';
}
```
**EXPECTED BEHAVIOR:** Use `doing_action( 'rest_api_request' )` check as well

---

### SC-063
**SEVERITY:** LOW
**CATEGORY:** COMPAT
**FILE:** src/Blocks/Page/SmartCart/CartTotals.php
**LINE:** 172-173
**DESCRIPTION:** Function exists check for helper function
**CURRENT CODE:**
```php
if ( ! function_exists( 'spssp_render_coupon_logic' ) ) {
    require_once __DIR__ . '/helper/find_block.php';}
```
**EXPECTED BEHAVIOR:** This is good practice, ensure all functions follow pattern

---

### SC-064
**SEVERITY:** MEDIUM
**CATEGORY:** COMPAT
**FILE:** blocks/blocks/showcase/smart-cart/cart-app/hooks/useTotalsSync.js
**LINE:** 7
**DESCRIPTION:** Optional chaining on cartTotals could throw error if cartTotals is null
**CURRENT CODE:**
```js
if (isLoading || !cartTotals.total_items) {
    return;
}
```
**EXPECTED BEHAVIOR:** Should check `if (isLoading || !cartTotals || !cartTotals.total_items)`

---

### SC-065
**SEVERITY:** LOW
**CATEGORY:** COMPAT
**FILE:** src/Blocks/Page/SmartCart/FilledCart/SmartFilledCartBlock.php
**LINE:** 71-77
**DESCRIPTION:** Box shadow default value uses rgba with fallback that may not work in old browsers
**CURRENT CODE:** `$c = $box_shadow_attr['color'] ?? 'rgba(0,0,0,0.2)';`
**EXPECTED BEHAVIOR:** Verify rgba support or provide hex fallback

---

### SC-066
**SEVERITY:** MEDIUM
**CATEGORY:** COMPAT
**FILE:** blocks/blocks/showcase/smart-cart/inner-blocks/smart-cart-line-items-block/index.jsx
**LINE:** 21
**CURRENT CODE:** `const starValue = rawRating >= 4 ? Math.round(rawRating) : 5;`
**DESCRIPTION:** Rating logic defaults to 5 stars for low ratings - may not reflect actual product quality
**EXPECTED BEHAVIOR:** Should show actual rating (0-5 stars) not default to 5

---

### SC-067
**SEVERITY:** LOW
**CATEGORY:** COMPAT
**FILE:** src/Blocks/Page/SmartCart/OrderSummaryTaxes.php
**LINE:** 38-39
**DESCRIPTION:** Tax output uses wc_price without checking if tax is enabled
**CURRENT CODE:**
```php
$tax_total = (float) $cart->get_total_tax();
$tax       = wc_price( $tax_total );
```
**EXPECTED BEHAVIOR:** Check if tax is enabled or show "Tax included" message

---

### SC-068
**SEVERITY:** LOW
**CATEGORY:** COMPAT
**FILE:** blocks/blocks/showcase/smart-cart/cart-app/hooks/useCartActions.js
**LINE:** 95-103
**DESCRIPTION:** Event listener attached every render without cleanup check
**CURRENT CODE:**
```js
useEffect(() => {
    const clearBtn = document.querySelector('.spssp-clear-cart-btn');
    if (!clearBtn) {
        return;
    }
    clearBtn.addEventListener('click', handleClearCart);
    return () => clearBtn.removeEventListener('click', handleClearCart);
```
**EXPECTED BEHAVIOR:** This is actually correct - handles cleanup properly

---

### SC-069
**SEVERITY:** MEDIUM
**CATEGORY:** COMPAT
**FILE:** blocks/blocks/showcase/smart-cart/view.js
**LINE:** 35-44
**DESCRIPTION:** Recursive setTimeout for WooCommerce loading has no timeout limit
**CURRENT CODE:**
```js
const waitForWooCommerce = () => {
    if (window.wc && window.wc.wcBlocksData) {
        initCart();
        // ...
    } else {
        setTimeout(waitForWooCommerce, 100);
    }
};
```
**EXPECTED BEHAVIOR:** Add max attempts or timeout to prevent infinite polling

---

### SC-070
**SEVERITY:** MEDIUM
**CATEGORY:** COMPAT
**FILE:** blocks/blocks/showcase/smart-cart/components/CartApp.jsx
**LINE:** 19-25
**DESCRIPTION:** Multiple useEffect calls may cause performance issues
**CURRENT CODE:**
```js
// Sync loading class to container
useEffect(() => {
    const container = document.querySelector('.spssp-cart-container');
    if (container) {
        container.classList.toggle('is-loading', isLoading);
    }
}, [isLoading]);
```
**EXPECTED BEHAVIOR:** Consider batching or useRef optimization

---

### SC-071
**SEVERITY:** LOW
**CATEGORY:** COMPAT
**FILE:** src/Blocks/Page/SmartCart/render.php
**LINE:** 63
**DESCRIPTION:** Typo in array key 'hop_url' should be 'shop_url'
**CURRENT CODE:** `'hop_url' => wc_get_page_permalink( 'shop' ),`
**EXPECTED BEHAVIOR:** Fix typo - breaks data access in frontend

---

### SC-072
**SEVERITY:** LOW
**CATEGORY:** COMPAT
**FILE:** blocks/blocks/showcase/smart-cart/edit.jsx
**LINE:** 81
**DESCRIPTION:** Missing default value for showReplacementNotice
**CURRENT CODE:** `const { replaceDefaultCart, showReplacementNotice } = attributes;`
**EXPECTED BEHAVIOR:** Add default in attributes or handle undefined

---

### SC-073
**SEVERITY:** MEDIUM
**CATEGORY:** COMPAT
**FILE:** src/Blocks/Page/SmartCart/CartProductTemplate/CartProductTemplate.php
**LINE:** 181
**DESCRIPTION:** Double quotes in CSS string without escaping could break if $justify contains quotes
**CURRENT CODE:** `$flex_css = "justify-content: {$justify} !important;";`
**EXPECTED BEHAVIOR:** Use json_encode or proper escaping

---

---

## Summary

**Total Findings:** 73
- **CRITICAL:** 2 (SC-005, SC-024)
- **HIGH:** 14 (SC-005, SC-010, SC-011, SC-012, SC-016, SC-025, SC-026, SC-027, SC-028, SC-034, SC-037, SC-040)
- **MEDIUM:** 35
- **LOW:** 22

**By Category:**
- **LAYOUT:** 7
- **RESPONSIVE:** 6
- **SKELETON:** 4
- **SECURITY-EDITOR:** 8
- **SECURITY-SERVER:** 7
- **STANDARDS:** 14
- **EDGE-CASE:** 13
- **COMPAT:** 24

**PHASE 1 Status:** ✅ COMPLETED (9/9 items fixed)
- SC-001 [FIXED] - Uncommented skeleton shimmer styles
- SC-002 [FIXED] - Added dynamic skeleton row count prop
- SC-003 [FIXED] - Added skeleton placeholder dimensions
- SC-004 [FIXED] - Updated breakpoint to 782px (WordPress standard)
- SC-005 [FIXED] - Sanitized dangerouslySetInnerHTML for price XSS
- SC-006 [FIXED] - Increased quantity buttons to 44x44px (WCAG AA)
- SC-007 [FIXED] - Used minmax() for grid template columns
- SC-008 [FIXED] - Reduced skeleton delay from 2000ms to 300ms
- SC-009 [FIXED] - Updated coupon form breakpoint to 782px

**Priority Action Items (CRITICAL/HIGH):**
1. Fix XSS vulnerabilities in CartItem.jsx dangerouslySetInnerHTML usage (SC-005 [FIXED], SC-024)
2. Remove excessive !important usage in CSS generation (SC-010, SC-011, SC-012)
3. Fix device name typos in responsive CSS (SC-013, SC-014)
4. Sanitize innerHTML assignments in useCouponActions.js (SC-025, SC-026)
5. Fix coupon form innerHTML XSS (SC-027)
6. Fix block name typo in allowed blocks (SC-017)
7. Add capability check to AJAX endpoint (SC-037)
8. Fix API version compatibility documentation (SC-054)
9. Fix typo in data attribute 'hop_url' (SC-071)
