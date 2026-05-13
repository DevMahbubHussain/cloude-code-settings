You are a senior WordPress core contributor and Gutenberg block developer acting as an autonomous Agent. I want you to perform a comprehensive audit of the "smart-cart" block.

Strictly limit your analysis to the two folders I have attached above:
1. Editor/Frontend Block code: @Folders (blocks/blocks/smart-cart)
2. Server-Side PHP code: @Folders (src/Pages/smartCart)

Use your codebase search and file reading tools to read ALL PHP, JS, JSX, CSS, and block.json files within these specific directories. Once read, create a new file called BUGFIX_PLAN_SMART_CART.md. Do NOT fix any code yet. Only document your findings.

Populate BUGFIX_PLAN_SMART_CART.md using the following 5 phases. Be specific: reference exact file paths, line numbers, and code snippets for every finding.

## PHASE 1: Visual & Layout Bug Detection (Focus on block folder)
- Layout issues (CSS box model, flex/grid misconfigurations, overflow, spacing)
- Responsive issues (breakpoint gaps, touch targets, fluid typography, block width alignments)
- Skeleton/Loading state issues (CLS on hydration, async loading states, missing space reservation)
- Editor-specific layout (distraction-free mode, zoom-out view, widget editor, site editor compatibility)

## PHASE 2: WordPress & Gutenberg Standards Compliance (Focus on both folders)
- PHP Coding Standards (WPCS) in PHP files: naming, prefixing, docblocks, Yoda conditions
- JS/React Standards in JS/JSX files: proper @wordpress/* packages, useBlockProps, memoization, wp.i18n
- block.json specification: required fields, attribute types, script/style handle registration
- CSS Standards: BEM naming, no !important abuse, proper scoping

## PHASE 3: Dual-Sided Security Audit (Focus on both folders)
- Server-Side (Focus on PHP folder): Input validation, sanitization, $wpdb->prepare, nonces, capability checks (current_user_can), output escaping (esc_html, esc_attr, wp_kses_post), direct access prevention
- Editor-Side (Focus on block folder): block attribute validation in save/render, XSS via attributes, dangerouslySetInnerHTML usage
- Tag each finding as [SECURITY-SERVER] or [SECURITY-EDITOR]

## PHASE 4: Edge Cases & Corner Cases (Focus on both folders)
- Content edge cases (empty cart, very long product names, special characters, zero/negative quantities)
- State edge cases (missing defaults, old attributes, duplicated blocks, undo/redo consistency)
- Environment edge cases (JS disabled, API failures, slow networks, concurrent saves)

## PHASE 5: Backward & Version Compatibility (Focus on both folders)
- WordPress version: Are APIs used that don't exist in the minimum supported WP version?
- PHP version: Are PHP 8+ features used in @src if supporting PHP 7.4?
- Block deprecations: Are deprecations registered for attribute/markup changes?
- Classic theme compatibility: Does it render without theme.json? Widget areas? add_theme_support?
- Block theme compatibility: Does it respect global styles and theme.json?

## OUTPUT FORMAT for every finding:
FINDING ID: [e.g., SC-001]
SEVERITY: CRITICAL|HIGH|MEDIUM|LOW
CATEGORY: [LAYOUT|RESPONSIVE|SKELETON|SECURITY-SERVER|SECURITY-EDITOR|STANDARDS|EDGE-CASE|COMPAT]
FILE: [exact file path]
LINE: [line number]
DESCRIPTION: [what is wrong]
CURRENT CODE: [snippet]
EXPECTED BEHAVIOR: [how it should be]

Go ahead and analyze the targeted directories now. Write the full BUGFIX_PLAN_SMART_CART.md.




unsanitized input, missing nonces on data saving, direct DB queries without prepare.
2. Customer Facing Bugs: Broken layouts on mobile, missing responsive breakpoints, blocks that crash the editor, UI that doesn't load.
3. Functional Breakages: Missing block deprecations causing "Block Recovery" errors, PHP fatal errors/warnings on the frontend.
4. Accessibility (Severe): Missing ARIA labels on interactive elements (buttons/modals), keyboard navigation completely broken.


---

### Option 2: If you already generated `BUGFIX_PLAN_SMART_CART.md` and it's too long
If the file is already full of low-impact noise, you can use a new prompt to make the AI clean it up for you.

Open your CLI or Cursor and say:

```text
Read BUGFIX_PLAN_SMART_CART.md. 

The current file is too pedantic and contains findings that won't impact WordPress.org approval or customer experience. I want you to minimize the file.

Please delete any finding from the file that falls into these "Low Impact" categories:
- Missing docblocks or minor PHPDoc formatting.
- Yoda conditions or strict WPCS spacing/alignment.
- CSS class naming strictness (unless it's causing an actual CSS collision).
- Theoretical or extreme edge cases that don't practically happen.
- Minor editor styling quirks that don't break the block functionality.

KEEP only findings that are:
- Actual Security holes (unescaped output, unsanitized inputs, missing nonces).
- Visual/Functional bugs (broken mobile layout, block crashes, missing responsive styles).
- Compatibility issues (Block recovery errors, PHP fatals, broken classic theme rendering).
- Severe Accessibility issues (keyboard trap, missing ARIA on modals).

Rewrite BUGFIX_PLAN_SMART_CART.md with only the impactful findings remaining, re-numbering the Finding IDs sequentially (e.g., SC-001, SC-002).











WordPress Gutenberg Block Development Standards
===============================================

This document serves as the absolute source of truth and coding constitution for developing Gutenberg blocks in this plugin. All code generation, refactoring, and new block creation MUST comply with these standards.

1\. Project Context & Compatibility
-----------------------------------

*   **Minimum WordPress Version:** 6.0
    
*   **Minimum PHP Version:** 7.4
    
*   **Minimum Node/npm:** As defined in package.json engines
    
*   **Theme Compatibility:** MUST support both Block Themes (theme.json) and Classic Themes.
    
*   **Browser Support:** Modern evergreen browsers (last 2 versions).
    

2\. Security (Non-Negotiable)
-----------------------------

### Server-Side (PHP)

*   **Direct Access Prevention:** Every PHP file MUST start with defined('ABSPATH') || exit;.
    
*   **Input Sanitization:** ALL inputs must be sanitized.
    
    *   Text: sanitize\_text\_field()
        
    *   HTML: wp\_kses\_post()
        
    *   Integers: absint()
        
    *   Colors: sanitize\_hex\_color()
        
*   **Output Escaping:** ALL outputs must be escaped late.
    
    *   HTML text: esc\_html()
        
    *   HTML attributes: esc\_attr()
        
    *   URLs: esc\_url()
        
*   **Database:** ALL database queries MUST use $wpdb->prepare(). No raw SQL concatenation.
    
*   **Authorization:**
    
    *   ALL REST routes MUST implement a permission\_callback using current\_user\_can().
        
    *   ALL AJAX handlers MUST check current\_user\_can().
        
*   **CSRF Protection:** ALL form submissions and state-changing AJAX/REST calls MUST verify nonces using wp\_verify\_nonce().
    

### Editor-Side (JS/React)

*   **Attribute Validation:** The save component and PHP render callbacks MUST validate attribute types before rendering.
    
*   **No Raw HTML Injection:** NEVER use dangerouslySetInnerHTML unless the content has been strictly sanitized via wp\_kses\_post on the server or is inherently safe.
    
*   **XSS Prevention:** Never inject user-controlled strings directly into href, src, or event handler attributes without proper sanitization/escaping.
    

3\. Architecture & React Standards
----------------------------------

### Component Design

*   **Separation of Concerns:** Extract complex logic (data fetching, state calculations) from UI components into Custom Hooks (e.g., useCartItemData).
    
*   **Component Size:** No single component file should exceed 150 lines. If it does, split it into smaller, composable components.
    
*   **State Management:** Use @wordpress/data (useSelect, useDispatch) for global state. Do not prop-drill more than 2 levels deep; use React Context or WP Data stores instead.
    
*   **Memoization:** useSelect MUST have correct dependency arrays. Wrap expensive computations in useMemo and callback functions in useCallback to prevent unnecessary re-renders.
    

### WordPress APIs

*   **Gutenberg Packages:** ALWAYS use @wordpress/\* packages (e.g., @wordpress/element, @wordpress/i18n, @wordpress/data). NEVER use jQuery or direct DOM manipulation (document.querySelector, etc.).
    
*   **Block Props:** The edit component MUST spread useBlockProps(). The save component MUST spread useBlockProps.save(). Inner blocks MUST use useInnerBlockProps().
    
*   **Translations:** ALL user-facing strings MUST use wp.i18n.\_\_('String', 'plugin-text-domain').
    

4\. block.json Specification
----------------------------

*   **Native Supports:** ALWAYS use native supports in block.json for spacing, colors, typography, and borders. DO NOT create custom attributes for things WordPress handles natively (e.g., do not create a backgroundColor attribute; use supports.color.background).
    
*   **Attribute Types:** Define strict types (string, number, boolean, object, array) and default values for ALL attributes.
    
*   **Script/Style Handles:** Ensure editorScript, script, editorStyle, and style handles perfectly match the handles registered in PHP via wp\_register\_script/wp\_register\_style.
    
*   **Dynamic Blocks:** If a block requires server-side data on the frontend, make it a dynamic block using render callback path in block.json instead of fetching data on the client.
    

5\. Styling, Layout & Responsiveness
------------------------------------

### CSS/SCSS Standards

*   **Naming Convention:** BEM methodology scoped to the block. Classes MUST be prefixed with wp-block-pluginname-blockname\_\_element--modifier.
    
*   **No !important:** Do NOT use !important except to override WordPress core editor-specific conflicts.
    
*   **Editor Scoping:** Editor styles (editor.scss) must properly scope styles so they don't leak into the admin sidebar or other blocks.
    

### Responsive & Visual Rules

*   **Fluid Typography & Spacing:** MUST use CSS clamp() for font sizes and padding/margins. No hard-coded px for responsive scaling.
    
*   **Breakpoints:** Mobile-first approach. Use min-width media queries. Standard breakpoints: 600px, 782px, 1080px.
    
*   **Touch Targets:** Interactive elements (buttons, links) MUST have a minimum size of 44x44px.
    
*   **Layout Shifts (CLS):** Reserve space for async content. If an image or async block loads, it MUST NOT push other content down. Use aspect-ratio or explicit dimensions.
    
*   **Editor Canvas Compatibility:** Blocks MUST render correctly in Default, Distraction-Free, and Zoom-Out editor modes. Use useBlockProps correctly to ensure width constraints are respected.
    

6\. PHP Coding Standards (WPCS)
-------------------------------

*   **Naming:** snake\_case for functions and variables. PascalCase for classes.
    
*   **Prefixing:** ALL functions, classes, hooks, and variables MUST be prefixed with the plugin slug (e.g., myplugin\_get\_cart\_data()).
    
*   **Docblocks:** EVERY function, class, method, and hook MUST have a WordPress-standard docblock.
    
*   **Yoda Conditions:** Comparisons MUST use Yoda conditions (if ( true === $is\_valid )).
    
*   **Strict Typing:** Use type declarations and return types where possible (compatible with PHP 7.4 minimum). declare(strict\_types=1); is encouraged.
    
*   **Late Escaping:** Escape as close to the output point as possible, not at the start of the function.
    

7\. Edge Cases & Error Handling
-------------------------------

*   **Empty States:** Blocks MUST render gracefully when empty (no data, no posts, empty cart). Provide a placeholder/empty state in the editor.
    
*   **Long Content:** Text MUST not break layout. Use overflow-wrap: break-word; or text-overflow: ellipsis; where appropriate.
    
*   **API Failures:** JS components MUST handle REST API failures (network errors, 403s, 500s) gracefully with try/catch or useSelect error states. Do not crash the editor.
    
*   **Zero/Negative Values:** Numeric attributes MUST be validated to prevent negative values where logically invalid (e.g., item count cannot be -1).
    
*   **JS Disabled:** Dynamic blocks MUST still render on the frontend if JavaScript fails or is disabled.
    

8\. Backward Compatibility
--------------------------

*   **Deprecations:** If the markup structure or attribute definitions in block.json change, a deprecated array MUST be provided in block.json so existing blocks do not show "Block Recovery" errors.
    
*   **Version Checks:** Do NOT use PHP 8+ features (named arguments, match expressions, union types, enums) if the minimum PHP version is 7.4.
    
*   **Classic Theme Support:** Blocks MUST provide their own base styles when theme.json is absent (e.g., default spacing, default colors). Blocks MUST work in classic theme widget areas.
    

How to use this file automatically

**1\. For Cursor:**Create a **.cursorrules** file in your project root and add this line:

textAlways read and strictly follow the rules in @STANDARDS.md before writing, editing, or refactoring any code.

**2\. For Claude Code CLI:**Create a **CLAUDE.md** file in your project root and add:

textRead STANDARDS.md. This is your coding constitution. All PHP, JS, and CSS code you generate MUST adhere to it strictly.

Now, whenever you ask any of these tools to "Create a new block called X," they will automatically read this file and apply these standards!

To configure **OpenAI Codex** (specifically the Codex CLI agent) to automatically follow your **STANDARDS.md**, you use a file called **codex.md**.

The Codex CLI is designed to look for this file in your project root and inject it as the "system prompt" before it starts working.

Here is exactly how to set it up:

### Step 1: Create the **codex.md** file

Create a file named **codex.md** in the root of your plugin directory (right next to **STANDARDS.md**, **package.json**, etc.).

Paste the following code into it:

OpenAI Codex Configuration - WordPress Gutenberg Plugin
=======================================================

You are an autonomous coding agent acting as a senior WordPress core contributor.

PRIMARY DIRECTIVE
-----------------

Before writing, editing, or refactoring ANY code, you MUST read the file STANDARDS.md located in the project root. Every line of code you generate, modify, or refactor MUST strictly comply with STANDARDS.md.

Execution Rules
---------------

1.  **Read First:** Always use your file reading tools to check STANDARDS.md if you are unsure about naming conventions, security protocols, or architectural patterns.
    
2.  **No Refactoring Unless Asked:** If tasked with fixing a bug, make the MINIMAL change required. Do not refactor surrounding code.
    
3.  **WordPress Security:** Never output unescaped data. Always use $wpdb->prepare(). Always verify nonces. Always use current\_user\_can().
    
4.  **Gutenberg Standards:** Always use @wordpress/\* packages. Never use jQuery. Always use useBlockProps().
    
5.  **Block.json:** Always use native supports in block.json for spacing, colors, and typography instead of creating custom attributes.
    
6.  **CSS:** Use BEM naming (wp-block-plugin-block\_\_element--mod). Use clamp() for fluid spacing. No !important unless overriding core editor conflicts.
    
7.  **Backward Compatibility:** If modifying an existing block's markup or attributes, you MUST add a deprecated array to block.json.
    

Constraint
----------

If a user prompt contradicts a rule in STANDARDS.md, you MUST follow STANDARDS.md and warn the user.

Step 3: How to Run Codex with the Rules

Because you created **codex.md** in the root, the Codex CLI will **automatically** read it when you start a session in that folder. You don't need any special flags.

1.  Open your terminal and navigate to your plugin:bashcd /path/to/your-plugin
    
2.  Start Codex:bashcodex
    
3.  _"Create a new block called 'product-badge'. It takes a text attribute for the badge label and a color attribute."_
    

### ⚠️ Important Difference with Codex

While Cursor and Claude Code usually ask for permission before writing to files, **Codex is highly autonomous**. If you use **codex --auto-approve** or **codex --full-auto**, it will write files directly to your disk without asking.

**Because of this:**

1.  **Never run Codex in full-auto mode for the first time** until you trust it has read **STANDARDS.md** correctly.
    
2.  Run it interactively first. When it proposes a file change, review the diff to ensure it actually followed the security and block.json rules from your standards.
    
3.  If it fails to follow a rule (e.g., it uses jQuery instead of **@wordpress/element**), reject the change and say: _"This violates STANDARDS.md section 3. Rewrite using @wordpress packages."_
    

Once you verify it is obeying the **codex.md** instructions, you can trust it with more autonomy!