### How to Trigger Planning in Claude Code

Method 1: Explicit Plan Request (Best)
claude "plan how to build a WooCommerce featured image block. Do NOT write code yet. I only want the plan."

Method 2: Step-by-Step Instruction
claude "I need to build a featured-image block for WooCommerce. Follow these steps:
1. First, analyze the existing block patterns in src/blocks/
2. Create a detailed implementation plan
3. List all files that need to be created or modified
4. Wait for my approval before writing any code"


Method 3: Use the /plan Conversation Command
If you're already inside a Claude Code session, you can just type:
> /plan Build a WooCommerce featured image block

(This tells Claude to output only the plan, not the code)



Complete Example: WooCommerce Featured Image Block
Here's exactly how I'd structure this command for your complex Gutenberg plugin:
Step 1: The Planning Command
claude

> I need to build a new block: myplugin/featured-product-image

REQUIREMENTS:
- Displays the featured image of a WooCommerce product
- Works in two modes:
  1. Auto mode: detects current product context (single product page)
  2. Manual mode: product selector to pick a specific product
- Has image overlay controls (gradient, color, opacity)
- Has aspect ratio controls (16:9, 4:3, 1:1, original)
- Has hover effect options (zoom, darken, none)
- Click behavior: link to product, link to cart, or no link
- Must use our DS components for all inspector controls
- Must follow the block patterns from CLAUDE.md and skills.md

BEFORE WRITING ANY CODE:
1. Read CLAUDE.md, skills.md, architecture.md, conventions.md
2. Read 2 existing blocks to understand our patterns (e.g., hero, card-grid)
3. Read the DS components to know what's available
4. Create a detailed plan including:
   - All files to create (with paths)
   - block.json attribute schema
   - Context needed (usesContext / providesContext)
   - Data fetching strategy (useSelect for WC product data)
   - PHP rendering considerations (SSR vs static)
   - Which DS components will be used
   - Hook/store dependencies

OUTPUT THE PLAN ONLY. Wait for my approval before implementing.


Step 2: What Claude Should Output (The Plan)
Implementation Plan: myplugin/featured-product-image
1. Architecture Decision

Rendering: Static save + dynamic editor (useSelect for product data in editor)
Context: Uses postType and postId from core/block-editor context for auto-detect
Data Fetching: useSelect from core data store (WC products are in core entity)
No SSR needed — image URL is resolved in editor and saved statically

2. Files to Create

src/blocks/featured-product-image/
├── block.json
├── index.ts
├── edit.tsx
├── save.tsx
├── inspector.tsx
├── components/
│ ├── product-selector.tsx ← Search/select WC product
│ ├── image-overlay-controls.tsx
│ └── hover-preview.tsx
├── hooks/
│ └── use-product-image.ts ← Fetch WC product + featured image
├── types.ts
├── editor.scss
└── style.scss




### Pro Tips for Planning Commands

1. Always Reference Your Memory Files

> Read CLAUDE.md, skills.md, and architecture.md first.
> All implementations must follow those conventions.


2. Break Complex Features into Phases
> Plan this feature in 3 phases:
> Phase 1: Basic block with static product ID and image rendering
> Phase 2: Auto-detect + product selector
> Phase 3: Overlay, hover effects, and link behavior
>
> Only plan Phase 1 for now.


3. Force Claude to Study Existing Code

> Before planning, read these files and use them as the template:
> - src/blocks/hero/block.json
> - src/blocks/hero/edit.tsx
> - src/blocks/hero/save.tsx
> - src/blocks/hero/inspector.tsx
> 
> The new block should follow the exact same patterns.

4. Ask for Edge Cases
> In your plan, include handling for:
> - Product with no featured image
> - Product not found / deleted
> - WC not installed
> - Image loading state
> - Editor preview vs frontend rendering differences


5. Ask for Testing Plan Too
> Include in the plan:
> - What tests to write
> - Manual testing checklist
> - Which WC scenarios to test (simple product, variable, out of stock)


The Golden Rule

For anything that touches 3+ files or involves new architecture → ALWAYS PLAN FIRST.

The 2 minutes you spend reviewing a plan saves 20 minutes of undoing bad code.
