# System Architecture

This document describes the system architecture, data flow, and block hierarchy of the Smart Store Pro plugin.

## High-Level Architecture

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         WordPress Environment                          │
└─────────────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                   Smart Store Pro Plugin                           │
├─────────────────────────────────────────────────────────────────────────┤
│  ┌────────────────────────────────────────────────────────────────┐  │
│  │              PHP Layer (src/)                           │  │
│  │  ┌──────────────┐  ┌──────────────┐  ┌─────────────┐│  │
│  │  │ WooProduct   │  │ BlockManager│  │ REST_API    ││  │
│  │  │ SliderPro    │  │             │  │             ││  │
│  │  │ (Bootstrap)  │  │ AbstractBlock│  │AdvancedFilter││  │
│  │  │              │  │             │  │ Handler     ││  │
│  │  │ AssetManager│  └──────────────┘  └─────────────┘│  │
│  │  │ TemplateBuilder                                     │  │
│  │  └──────────────┐                                     │  │
│  │                 ▼                                     │  │
│  │  Block Classes  │                                     │  │
│  │  (renderers)   │                                     │  │
│  └──────────────────────────────────────────────────────────────┘  │
├─────────────────────────────────────────────────────────────────────────┤
│  ┌────────────────────────────────────────────────────────────────┐  │
│  │              JS/JSX Layer (blocks/)                  │  │
│  │  ┌──────────────┐  ┌──────────────┐  ┌─────────────┐│  │
│  │  │ blocks/      │  │ global-      │  │ admin/       ││  │
│  │  │ index.js    │  │ settings/    │  │ index.js    ││  │
│  │  │ (Editor)    │  │ (Store)      │  │ (Dashboard)  ││  │
│  │  │              │  │              │  │              ││  │
│  │  │ blocks/      │  │ components/  │  │ hooks/       ││  │
│  │  │ frontend.js │  │ (Controls)   │  │             ││  │
│  │  │ (Interact)  │  │              │  │             ││  │
│  │  └──────────────┘  └──────────────┘  └─────────────┘│  │
│  └──────────────────────────────────────────────────────────────┘  │
├─────────────────────────────────────────────────────────────────────────┤
│  ┌────────────────────────────────────────────────────────────────┐  │
│  │              Build System                                │  │
│  │  ┌──────────────┐  ┌──────────────┐                │  │
│  │  │ webpack      │  │ gulp         │                │  │
│  │  │ (wp-scripts)│  │ (SCSS)       │                │  │
│  │  └──────────────┘  └──────────────┘                │  │
│  └──────────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────────┘
```

## Data Flow

### Block Registration Flow

```
1. WordPress loads plugin (woo-product-slider-pro.php)
   ↓
2. WooProductSliderPro constructor runs
   ↓
3. init_advanced_blocks() called
   ├─► AssetManager::register_block_editor_assets() (priority 10)
   ├─► AssetManager::register_frontend_assets() (priority 10)
   ├─► BlockManager::register_all_blocks() (priority 20)
   │   └─► Each block class calls register_block_type()
   │       ├─► Reads metadata from blocks.php
   │       ├─► Registers block in PHP
   │       └─► Attaches render_callback
   ├─► REST_API::__construct() - registers REST routes
   ├─► AdvancedFilterHandler registers filter routes
   └─► TemplateBuilder::__construct()
```

### Editor Render Flow

```
1. User opens block editor
   ↓
2. WordPress enqueues spssp-blocks-editor
   ↓
3. blocks/blocks/index.js runs
   └─► Imports all block index.jsx files
       └─► Each calls registerBlockType()
           ├─► Registers block metadata
           ├─► Sets edit component
           └─► Sets save component
```

### Frontend Render Flow

```
1. Page loads with block markup
   ↓
2. WordPress calls render_callback
   ↓
3. PHP Block::render() executes
   ├─► Parse attributes with defaults
   ├─► Generate dynamic CSS via *CssGenerator.php
   ├─► Enqueue block assets
   └─► Return HTML
   ↓
4. blocks/blocks/frontend.js initializes
   ├─► Initialize Swiper carousels
   ├─► Initialize pagination
   ├─► Initialize advanced filters (React roots)
   └─► Initialize tabs
```

### REST API Flow

```
Client Request
   ↓
wp-json/wpspro/v1/endpoint
   ↓
REST_API::method()
   ├─► Validate permissions
   ├─► Process request
   │   └─► May call ProductQuery::get_products()
   └─► Return JSON response
   ↓
Client receives JSON
   ├─► Update UI
   └─► Re-render blocks
```

## Block Hierarchy

### Tier System

Blocks are organized into 4 tiers based on their scope and complexity:

```
Element Tier (Atomic Components)
├─ product-image          (Renders product image with gallery support)
├─ product-title          (Renders product title with link)
├─ product-price          (Renders price, sale price, currency)
├─ product-category       (Renders product category/tags)
├─ product-rating         (Renders star rating)
├─ stock-status           (Renders in-stock/out-of-stock)
├─ product-short-description (Renders excerpt)
├─ product-meta          (Renders SKU, dimensions, etc.)
├─ add-to-cart           (Add to cart button with options)
└─ product-quantity       (Quantity input with +/- buttons)

Smart Tier (Layout Primitives)
├─ smart-container       (Container with spacing/alignment)
├─ smart-column          (Column for grid layouts)
├─ smart-heading         (H1-H6 heading with typography)
└─ smart-paragraph       (Text content with typography)

Showcase Tier (Composite Blocks)
├─ product-template      (Template for product cards)
│   └─ InnerBlocks: Element tier blocks
├─ product-grid          (Grid of products with pagination)
│   └─ Queries products via ProductQuery
├─ product-carousel      (Swiper carousel of products)
│   └─ Uses Swiper.js for slides
├─ smart-pagination      (Load more, numeric, infinite)
├─ smart-buttons         (Button group with presets)
├─ smart-cart           (Full cart with many inner blocks)
│   ├─ smart-filled-cart-block
│   │   └─ smart-cart-items-block
│   │       └─ smart-product-template
│   └─ smart-cart-totals-block
│       └─ smart-cart-order-summary-block
│           ├─ smart-cart-order-summary-subtotal
│           ├─ smart-cart-order-summary-shipping
│           ├─ smart-cart-order-summary-taxes
│           └─ smart-cart-order-summary-total
├─ advanced-tab          (Tabbed content)
└─ advanced-tab-item     (Individual tab pane)

Builder Tier (Page Components)
├─ advanced-product-filters
│   ├─ price-filter (Range slider)
│   ├─ rating-filter (Star filter)
│   ├─ taxonomy-filter (Checkbox/visual filter)
│   ├─ status-filter (In stock, on sale)
│   ├─ sort-by-filter (Sort dropdown)
│   ├─ order-by-filter (Order dropdown)
│   ├─ search-filter (Search input)
│   ├─ active-filters (Display applied filters)
│   └─ clear-filters-button (Reset all)
├─ breadcrumb             (WP breadcrumbs)
├─ archive-title          (Archive page title)
├─ archive-description    (Archive description text)
├─ product-description    (Full product description)
├─ product-additional-information (Product attributes)
└─ product-review         (Reviews with summary)
    └─ reviews-summary
```

## Component Architecture

### Global Settings System

```
blocks/global-settings/
├─ index.js              (PluginSidebar component)
├─ store.js              (Redux store for settings)
├─ constants.js          (Default values)
├─ default-settings.js    (Setting structures)
├─ defaultConstant.js     (Static defaults)
├─ api.js                (REST API calls)
├─ css-manager.js        (Root CSS injection)
├─ components/
│   ├─ ColorSettings     (Color palette UI)
│   ├─ TypographySettings (Font management)
│   ├─ ButtonsSettings   (Button presets)
│   ├─ ShadowSettings    (Shadow presets)
│   ├─ Breakpoint       (Responsive breakpoints)
│   └─ CustomCss        (Custom CSS editor)
└─ utils/               (Helper functions)
```

### Control Components

```
blocks/controls/
├─ responsive/          (Device-specific controls)
├─ color/               (Color pickers)
├─ typography/          (Font settings)
├─ spacing/             (Margin/padding)
├─ border/              (Border controls)
├─ shadow/              (Box shadow)
├─ alignment/           (Text alignment)
└─ box-shadow/          (Shadow control)
```

## CSS Architecture

### CSS Generation Pipeline

```
1. Block with visual settings
   ↓
2. JS: dynamicCss.js generates preview CSS
   └─► Injected via <style> tag for editor preview
   ↓
3. PHP: *CssGenerator.php generates frontend CSS
   └─► Inline CSS added to block output
   ↓
4. Global CSS from settings
   └─► :root CSS variables added by AssetManager
   └─► Custom CSS from global settings
```

### CSS Scoping Strategy

- All block-specific CSS uses `.spssp-unique-id-{uniqueId}` selector
- Global settings use CSS custom properties (`--wpspro-primary`, etc.)
- Responsive CSS wrapped in media queries from breakpoints
- Hover states use `:hover` pseudo-class

## State Management

### WordPress Data Store Usage

```
Blocks use @wordpress/data:
├─ @wordpress/data (Core WP store)
│   ├─ core/block-editor (Block operations)
│   ├─ core/editor (Editor state)
│   └─ core/notices (Notifications)
├─ wc/store (WooCommerce data)
│   └─ wc-blocks-data-store (Product data)
└─ wpspro/global-settings (Plugin store)
    ├─ Typography settings
    ├─ Color presets
    ├─ Button styles
    ├─ Shadow presets
    └─ Breakpoints
```

### Context API Usage

```
Parent provides context:
├─ postId (Current product ID)
├─ query (WP_Query args)
├─ uniqueId (Block unique identifier)
└─ spsspContext (Custom context)

Child consumes context:
├─ usesContext: ['postId']
├─ usesContext: ['query']
└─ usesContext: ['spsspContext']
```

## REST API Endpoints

### WPSPro v1

```
POST /wp-json/wpspro/v1/filter
    └─ Product grid pagination/filter
POST /wp-json/wpspro/v1/preview
    └─ Block preview in editor
GET  /wp-json/wpspro/v1/products
    └─ Fetch products by type
GET  /wp-json/wpspro/v1/categories
    └─ Get product categories
GET  /wp-json/wpspro/v1/sliders
    └─ Get slider shortcodes
```

### WPSPro v2

```
GET  /wp-json/wpspro/v2/get-global-settings
    └─ Fetch global settings
POST /wp-json/wpspro/v2/global-settings
    └─ Save global settings
POST /wp-json/wpspro/v2/icon-list
    └─ Get icon library
```

## Asset Management

### Editor Assets

```
Entry: blocks/blocks/index.js
├─ @wordpress/blocks
├─ @wordpress/block-editor
├─ @wordpress/components
├─ @wordpress/element
├─ @wordpress/i18n
└─ wc-settings (WooCommerce)

Output: assets/editor/index.js + index.css
```

### Frontend Assets

```
Entry: blocks/blocks/frontend.js
├─ @wordpress/interactivity
└─ swiper (Slider library)

Output: assets/js/frontend.js
```

### Dashboard Assets

```
Entry: blocks/admin/index.js
├─ @wordpress/components
├─ @wordpress/element
└─ react-admin interface

Output: assets/dashboard/index.js + style-dashboard.css
```

## Key Design Patterns

### 1. Dynamic Block Pattern

Blocks that render differently based on context (product vs page):
- Use `postId` context to detect product mode
- Fall back to static rendering when context unavailable
- Server-side render for SEO, client-side for interactivity

### 2. Filter Pattern

Advanced filters use:
- React context for shared filter state
- REST API for filtered product queries
- URL sync for bookmarkable filter states
- MutationObserver for dynamic content

### 3. Cart Pattern

Smart cart uses:
- LocalStorage for cart persistence
- REST endpoints for cart operations
- InnerBlocks for layout flexibility
- WooCommerce hooks for actual cart integration

### 4. Template Builder Pattern

Template builder uses:
- Custom post type for templates
- Block-based template editing
- Template hierarchy override
- Location-based template selection
