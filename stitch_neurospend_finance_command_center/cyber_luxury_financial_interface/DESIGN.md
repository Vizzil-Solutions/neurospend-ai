---
name: Cyber-Luxury Financial Interface
colors:
  surface: '#111318'
  surface-dim: '#111318'
  surface-bright: '#37393e'
  surface-container-lowest: '#0c0e12'
  surface-container-low: '#1a1c20'
  surface-container: '#1e2024'
  surface-container-high: '#282a2e'
  surface-container-highest: '#333539'
  on-surface: '#e2e2e8'
  on-surface-variant: '#b9cbbd'
  inverse-surface: '#e2e2e8'
  inverse-on-surface: '#2f3035'
  outline: '#849588'
  outline-variant: '#3a4a3f'
  surface-tint: '#00e290'
  primary: '#f5fff5'
  on-primary: '#003920'
  primary-container: '#00ffa3'
  on-primary-container: '#007146'
  inverse-primary: '#006d43'
  secondary: '#b9f1ff'
  on-secondary: '#00363f'
  secondary-container: '#00e0ff'
  on-secondary-container: '#005f6d'
  tertiary: '#fffbff'
  on-tertiary: '#510074'
  tertiary-container: '#f5d5ff'
  on-tertiary-container: '#952cc8'
  error: '#ffb4ab'
  on-error: '#690005'
  error-container: '#93000a'
  on-error-container: '#ffdad6'
  primary-fixed: '#52ffac'
  primary-fixed-dim: '#00e290'
  on-primary-fixed: '#002111'
  on-primary-fixed-variant: '#005231'
  secondary-fixed: '#a5eeff'
  secondary-fixed-dim: '#00daf8'
  on-secondary-fixed: '#001f25'
  on-secondary-fixed-variant: '#004e5a'
  tertiary-fixed: '#f6d9ff'
  tertiary-fixed-dim: '#e9b3ff'
  on-tertiary-fixed: '#310048'
  on-tertiary-fixed-variant: '#7200a3'
  background: '#111318'
  on-background: '#e2e2e8'
  surface-variant: '#333539'
typography:
  display-lg:
    fontFamily: Bodoni Moda
    fontSize: 48px
    fontWeight: '700'
    lineHeight: '1.1'
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Bodoni Moda
    fontSize: 32px
    fontWeight: '600'
    lineHeight: '1.2'
  headline-lg-mobile:
    fontFamily: Bodoni Moda
    fontSize: 28px
    fontWeight: '600'
    lineHeight: '1.2'
  title-md:
    fontFamily: Geist
    fontSize: 20px
    fontWeight: '600'
    lineHeight: '1.4'
    letterSpacing: 0.01em
  body-md:
    fontFamily: Geist
    fontSize: 16px
    fontWeight: '400'
    lineHeight: '1.6'
  data-mono:
    fontFamily: JetBrains Mono
    fontSize: 14px
    fontWeight: '500'
    lineHeight: '1.4'
    letterSpacing: 0.05em
  label-caps:
    fontFamily: JetBrains Mono
    fontSize: 11px
    fontWeight: '700'
    lineHeight: '1'
    letterSpacing: 0.1em
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  unit: 4px
  container-padding: 24px
  gutter: 16px
  section-gap: 48px
  stack-sm: 8px
  stack-md: 16px
---

## Brand & Style
The brand personality is **deterministic, authoritative, and sophisticated**. It positions personal finance not as a chore, but as a high-stakes command center. The target audience consists of "financial engineers"—users who demand deep data density paired with a premium, executive aesthetic.

The design style is **Cyber-Luxury**, a hybrid of **Glassmorphism** and **Corporate Modernism**. It utilizes deep, multi-layered dark surfaces to create a sense of infinite depth, reminiscent of high-end aerospace or fintech trading terminals. Visual interest is generated through high-contrast accents and glowing glass layers rather than decorative imagery. The emotional response should be one of total control and absolute security.

## Colors
This design system utilizes a **"Deep-Space" dark mode** palette. The foundation is built on ultra-dark charcoals and navies to ensure that accent colors appear luminous, almost self-lit.

- **Primary (Emerald Green):** Dedicated to growth, positive balances, and "Go" signals.
- **Secondary (Neon Blue):** Reserved exclusively for Nova AI interactions and system intelligence.
- **Tertiary (Amethyst):** Used for debt management, liabilities, and long-term planning.
- **Neutral:** A range of desaturated cool grays that provide structure without competing with data visualizations.

Backgrounds should use subtle radial gradients (Deep Navy to Charcoal) to prevent "flatness" and enhance the perception of depth.

## Typography
The typography strategy creates a tension between **Traditional Luxury** and **Technical Precision**.

- **Headings:** Use *Bodoni Moda*. The high-contrast serifs evoke the feeling of a premium masthead or a private bank. Use this for high-level summaries and section titles.
- **Body & Interface:** Use *Geist*. Its geometric, clean-room aesthetic ensures readability in complex dashboards.
- **Financial Data:** Use *JetBrains Mono*. All currency figures, percentages, and tickers must use this monospaced font to ensure numbers align perfectly in columns, reinforcing the "deterministic" nature of the system.

## Layout & Spacing
The layout follows a **Strict Modular Grid**. Elements are locked to a 4px baseline to maintain a rigorous, engineered feel.

- **Desktop:** 12-column grid with 24px margins. Use "Dashboard Tiles" that span 3, 4, or 6 columns.
- **Tablet:** 8-column grid. Sidebars collapse into icons to prioritize data visualization.
- **Mobile:** Single column fluid layout. Large charts should be optimized for horizontal scrolling or simplified "sparkline" views.

Spacing should be generous between major sections to emphasize the "Luxury" aspect of the brand, while internal component spacing remains tight and efficient for high data density.

## Elevation & Depth
Depth is conveyed through **Optical Transparency and Light Leaks** rather than traditional drop shadows.

- **The Base:** The lowest layer is the `#050608` canvas.
- **Glass Containers:** Use a `1px` semi-transparent border (the "Edge Light") and a `backdrop-filter: blur(20px)`. This creates a frosted pane effect that suggests layers of data stacked in a 3D space.
- **Active State Glow:** When an element is selected or active, it emits a soft outer glow in its respective accent color (Primary, Secondary, or Tertiary), as if the UI is powered by a light source from within.

## Shapes
Shapes are **sharp and architectural**. While a small corner radius is used to prevent the UI from feeling "hostile," the overall vibe is rectangular and precise.

- **Buttons/Inputs:** 4px radius (Soft).
- **Cards/Panels:** 8px radius (Soft).
- **Interactive Elements:** Avoid fully rounded "pill" shapes unless used for status indicators (chips).
- **Dividers:** Use 1px ultra-thin lines with a 0.1 opacity.

## Components

### Financial Charts
Charts should never use solid fills. Use linear gradients that fade into the background. Grid lines should be faint (`opacity: 0.05`). Use *JetBrains Mono* for all axis labels.

### AI Diagnostic Cards (Nova AI)
These cards feature a **Neon Blue** "breathing" border. Use a subtle grain texture in the background of the glass to distinguish AI-generated insights from standard user data.

### Debt Simulators
Interactives use Amethyst sliders and toggles. Simulation results should "count up" quickly using monospaced numerals to emphasize the real-time processing power of the engine.

### Buttons & Inputs
- **Primary Action:** Solid background with high-contrast black text. No border.
- **Secondary Action:** Ghost style with the 1px "Edge Light" border.
- **Inputs:** Darker than the surface layer, inset appearance, with a neon focus ring when active.

### Chips & Status
Small, uppercase labels using *JetBrains Mono*. Use high-saturation background tints at 10% opacity with solid text of the same color for status indicators (e.g., "STABLE", "RISK", "OPTIMIZED").