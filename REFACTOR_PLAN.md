The Master Prompt for Generating REFACTOR_PLAN.md


Use this in Cursor Agent or Claude Code CLI (pointing to your specific block folders just like before):

You are a senior WordPress core contributor and Gutenberg block developer. I want you to perform a comprehensive refactoring audit of the "smart-cart" block.

Strictly limit your analysis to these two directory paths:
1. Editor/Frontend Block code: blocks/blocks/smart-cart/
2. Server-Side PHP code: src/Pages/smartCart/

Read all PHP, JS, JSX, CSS, and block.json files within these directories. Create a new file called REFACTOR_PLAN_SMART_CART.md. Do NOT refactor any code yet. Only document your findings.

CRITICAL RULE: Refactoring must NOT change the block's visual output, user-facing behavior, or saved markup. We are improving code quality, performance, and maintainability only.

Populate REFACTOR_PLAN_SMART_CART.md using the following phases. Be specific: reference exact file paths, line numbers, and code snippets.

## PHASE 1: Component Architecture & React Patterns (Editor-Side)
- Are components doing too much? (Identify components > 150 lines that need splitting)
- Should logic be extracted into custom hooks (e.g., useCartItem, useSmartCartState)?
- Is prop drilling happening? (Should we use React Context or WordPress Data stores?)
- Are block attributes defined correctly, or are we storing derived/volatile data as attributes?
- Are we using the native `useBlockProps` and `useInnerBlockProps` correctly?
- Is there any direct DOM manipulation that should be handled via React state?

## PHASE 2: State Management & Data Fetching (Editor-Side)
- Is `useSelect` properly memoized with correct dependency arrays?
- Are there unnecessary re-renders? (Identify missing `useMemo` or `useCallback`)
- Are we fetching data in components instead of using @wordpress/data resolvers?
- Is the block relying on `wp.apiFetch` directly when a core data store exists?

## PHASE 3: PHP Architecture & Backend Logic (Server-Side)
- Are render callbacks doing too much? (Separation of Concerns: Data fetching vs HTML rendering)
- Is there repeated code that should be extracted into trait/utility classes?
- Are we using modern PHP practices? (Type declarations, return types, strict typing)
- Are hooks (actions/filters) added inside classes correctly (e.g., using [$this, 'method'])?
- Can dynamic blocks be optimized by caching or lazy loading?

## PHASE 4: Performance Optimization (Both Sides)
- Are block assets conditionally loaded? (Only load JS/CSS when the block is present on the page)
- Are we using `wp_enqueue_script` with `in_footer` where appropriate?
- Can large CSS files be split? (Editor styles vs Frontend styles)
- Are images/assets using proper lazy loading or async loading?
- Is the block markup optimized for Core Web Vitals (CLS, LCP)?

## PHASE 5: Modernization & DRY (Don't Repeat Yourself) (Both Sides)
- Are there deprecated WordPress/Gutenberg APIs that need updating?
- Are we using native `block.json` `supports` (like spacing, colors, borders) instead of custom attributes? (This reduces JS/CSS drastically)
- Is there duplicate CSS that can be consolidated using CSS custom properties?
- Are there hardcoded text strings that should use `wp.i18n` or `__()`?

## OUTPUT FORMAT for every finding:
FINDING ID: [e.g., R-001] (R for Refactor)
SEVERITY: HIGH|MEDIUM|LOW (High = major performance/architecture issue; Low = code cleanliness)
REFACTOR CATEGORY: [ARCHITECTURE|STATE|PHP-LOGIC|PERFORMANCE|MODERNIZE|DRY]
FILE: [exact file path]
LINE: [line number]
CURRENT CODE: [snippet showing the problem]
PROPOSED REFACTORING: [detailed explanation of how to rewrite it, or a code snippet of the proposed new structure]
BEHAVIOR PRESERVATION CHECK: [Explain briefly why this refactoring will NOT change the visual output or user experience]

Go ahead and analyze the targeted directories now. Write the full REFACTOR_PLAN_SMART_CART.md.



How to Execute the Refactoring (Using the ID Method)
Refactoring is riskier than bug fixing. When you tell an AI to "refactor component X," it will often completely rewrite it, changing class names, HTML structure, and breaking your block's backward compatibility.

You must use the ID method, and you must constrain the AI heavily.


Example 1: Refactoring a specific architectural issue
Read REFACTOR_PLAN_SMART_CART.md. 

I want you to implement the refactoring for ONLY finding ID: R-003 (Extracting logic to a custom hook).

Rules:
1. Create the new custom hook file exactly as proposed.
2. Update the component to import and use the new hook.
3. Do NOT change the JSX return statement or the CSS classes in the component.
4. Do NOT change the block.json attributes.
5. Ensure the custom hook returns the exact same data structure the component was previously generating internally.
6. Update REFACTOR_PLAN_SMART_CART.md and mark R-003 as [REFACTORED].


Example 2: Modernizing block.json supports (High impact, careful execution)


Read REFACTOR_PLAN_SMART_CART.md. 

Implement the refactoring for ONLY finding ID: R-012 (Using native block.json supports for spacing instead of custom attributes).

Rules:
1. Update block.json to add the "spacing" support.
2. Remove the custom margin/padding attributes from block.json.
3. IMPORTANT: Add a block DEPRECATION for the old attributes so existing blocks don't break or show "Block Recovery" errors.
4. Remove the custom margin/padding UI from the InspectorControls in the edit.js file (WordPress will handle this automatically now).
5. Update the frontend CSS/PHP to use the `style` attribute injected by WordPress rather than custom classes.
6. Update REFACTOR_PLAN_SMART_CART.md and mark R-012 as [REFACTORED].




Pro-Tip: The "Behavior Preservation Check"
Before telling the AI to implement a refactor ID, ask it to explain the risk:

Look at R-005 in REFACTOR_PLAN_SMART_CART.md. 
Before you write any code, explain to me: 
1. Will this change the HTML output on the frontend? 
2. Will this cause a "Block Recovery" error for users who have already inserted this block?
3. Do we need a block deprecation? 
Do not code yet, just answer these 3 questions.