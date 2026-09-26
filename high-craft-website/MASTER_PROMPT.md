# Master Prompt — High-Craft Bootstrap 5 Website Build
HTML + Bootstrap 5 only · Swiper.js + Vanilla JS · No GSAP · No React · SEO-Ready

## Role
Act as a Senior Principal Product Designer and Lead Frontend Architect. Design and build the UI for **[PROJECT NAME / TYPE]**, converting the attached design/reference into a fully responsive, production-ready website.

Primary objective: a bespoke, high-craft, human-designed interface that strictly eliminates AI-generated design clichés and predictable SaaS/template tropes — built as clean static HTML, not a framework app.

---

## 1. Tech Stack (locked — do not substitute)

| Purpose | Library | Notes |
|---|---|---|
| Output format | **Plain HTML** | No React, no Vue, no JSX, no build step required |
| Layout & components | **Bootstrap 5** (latest) | CSS + JS bundle via CDN |
| Icons | **Font Awesome** (latest, free) | CDN `<link>` in `<head>` |
| Sliders / Carousels | **Swiper.js** (latest) | Use instead of Bootstrap Carousel for hero, testimonials, logos, portfolio |
| Scroll animations | **Vanilla JS only** — custom `IntersectionObserver`-based reveal system | **No GSAP, no AOS library dependency** |
| Typography | **Google Fonts** | Preconnect + `<link>`, defined as CSS custom properties |
| Custom styling | `style.css` | Only for what Bootstrap utilities can't handle |
| Custom interactivity | `script.js` | Vanilla JS only — no jQuery, no GSAP |

---

## 2. Banned UI Tropes & Components

- **No rounded eyebrow/subtitle pills.** No pill-shaped badges above headings, no sparkle-prefixed labels ("✨ New:", "✦ Introducing"). If an eyebrow tag is functionally required: uppercase, letter-spaced text (`text-uppercase`, letter-spacing utility, 11–12px, semibold), no pill container, no background chip.
- **No icons trapped in rounded background squares.** No generic Font Awesome icon in a squircle with 10% opacity fill. Icons appear inline, serve real navigation, or are replaced by typographic hierarchy and real content.
- **No glowing borders or pseudo-gradients.** No purple/violet/cyan gradient borders, illuminated edges, or outer glow effects.
- **No decorative AI sparkles/stars.** Zero ✨ ✦ 🪄 or ornamental floating shapes.
- **No generic hover clichés.** No `hover:scale-105` card lift, no uniform fade-up-on-scroll reveal slapped on every single element. Motion is purposeful — see Section 6.

## 3. Geometry & Surface Styling

- **Border radius limits:**
  - Cards/containers: 8–12px max
  - Buttons/inputs: 4–8px max
  - Badges/status tags: 2–4px max
  - No full-pill radii (`border-radius: 9999px` / `rounded-pill`) anywhere
- **Borders:** no default low-contrast 1px stroke wrapped around every card. Separate content with background tone shifts, whitespace, or solid dividing rules (`border-bottom`, `border-end`).
- **Backgrounds:** no mesh blobs, no dark violet/navy mesh, no glassmorphism/`backdrop-filter: blur()` cards. Solid, high-contrast, confident surface colors.
- **Shadows:** tight natural elevation only (`0 1px 2px rgba(0,0,0,.05)`) or crisp 1px borders with zero shadow. No diffuse, colored, or spread-out drop shadows.

## 4. Color System

- Specify before build starts: **[a] Monochrome + single saturated accent for CTAs/key data]** or **[b] Named brand palette with hex values]**
- Base palette (fill in): background / surface / text-primary / text-secondary / border / accent
- Accent color reserved for primary actions and key data points — never decoration.
- No default-blue-on-white palette unless explicitly the brand color.
- Define as CSS custom properties in `:root` and override Bootstrap's Sass variables or CSS variables (`--bs-primary`, `--bs-body-bg`, etc.) — never hardcode hex values inline in markup.

## 5. Typography & Information Architecture

- **Banned fonts:** Inter, Roboto, Space Grotesk, Plus Jakarta Sans, Outfit.
- **Font pairing (specify, don't leave open):** Display/heading — **[e.g. Newsreader, General Sans Semibold]**; Body/UI — **[e.g. Instrument Sans, Switzer]**
- Load via Google Fonts `<link rel="preconnect">` + import, expose as `--font-heading` / `--font-body` custom properties, apply via custom classes — never hardcode `font-family` repeatedly.
- **Section layouts:** no standard 3-column feature cards with centered icons + 2-line descriptions. Use asymmetric grids, editorial side-by-side splits, tabbed/accordion content, structured list or table views.
- **Content realism:** no Lorem Ipsum, no generic placeholder copy. Populate with plausible real content specific to the project domain.

## 6. Motion & Interaction (Vanilla JS, no GSAP)

Custom scroll-reveal via `IntersectionObserver` — not a decorative default on every element:

- Add `data-aos="fade-up"` (or `fade-in`, `zoom-in`, `slide-left`, `slide-right`) to elements that should reveal on scroll.
- In `script.js`, observe all `[data-aos]` elements and toggle an `.aos-animate` class on intersection.
- Define transitions in `style.css` (opacity + transform, `transition: .6s ease-out`) — explicit timing, not library defaults.
- Support `data-aos-delay` for staggered reveals.
- Trigger once per element (unobserve after reveal) unless repeat-on-scroll is explicitly requested.
- Apply reveal motion selectively — hero, section intros, key stats — not blanket-applied to every card and paragraph.
- Define hover/focus/active states per component individually (button, nav link, card) rather than one blanket hover rule site-wide.

## 7. Edge & System States

- Design and code empty, loading, and error states for any data-driven view (forms, listings) — not just the happy path.
- Empty states give a clear next action, not just an illustration and a sentence.
- Form validation states (success/error) styled explicitly, not left to browser defaults.

## 8. Responsive Behavior

- Mobile-first across `sm`, `md`, `lg`, `xl`, `xxl` — not a naive stack of the desktop grid.
- Specify per-section what collapses, reorders, or is deprioritized/hidden at small viewports.
- Distinct mobile composition where warranted (e.g. hero stat block, service tables) rather than shrinking desktop layout as-is.

---

## 9. Semantic HTML

- Proper semantic tags: `<header>`, `<nav>`, `<main>`, `<section>`, `<article>`, `<aside>`, `<footer>` — no `<div>` soup.
- Correct heading hierarchy, exactly **one `<h1>`** per page.
- `<button>` for actions, `<a>` only for navigation/links.
- `<ul>/<li>` for nav menus and list content — not stacked `<div>`s.

## 10. SEO-Friendly Structure

- Unique `<title>` and `<meta name="description">` per page.
- Open Graph tags (`og:title`, `og:description`, `og:image`, `og:type`, `og:url`).
- Twitter Card meta tags.
- Descriptive, non-keyword-stuffed `alt` text on every image.
- Favicon + canonical URL tag.
- `loading="lazy"` on below-the-fold images.
- JSON-LD schema.org structured data matching page type (Organization / LocalBusiness / Service / FAQPage, etc.).
- Clean, crawlable anchor links for in-page nav (`#about`, `#services`, `#contact`).
- `sitemap.xml` + `robots.txt` at project root.

## 11. Bootstrap 5 Coding Standards

- Use Bootstrap's grid (`container`, `row`, `col-*`) — no float/absolute-position hacks.
- Prefer Bootstrap utility classes (spacing, flex, display, text) over custom CSS where they achieve the same result without conflicting with Section 3's radius/shadow rules.
- Mobile-first responsiveness.
- No inline styles.
- Use Bootstrap's native JS components (modal, offcanvas, accordion, nav-tabs) where they fit; reserve Swiper strictly for slider/carousel needs.

## 12. Font Awesome Usage

- Use for social links, functional feature markers, buttons, contact info (phone/email/location), star ratings, arrows/nav controls.
- `fa-solid` / `fa-regular` / `fa-brands` used appropriately.
- No raster icon images where a Font Awesome icon serves the same purpose.
- Icons stay inline with text or serve real navigation — never dropped into a decorative squircle per Section 2.

## 13. Swiper.js Usage

- Use for: hero slider (if needed), testimonials, client/logo strip, portfolio/gallery.
- Include navigation arrows + pagination dots; `loop` and `autoplay` only where it suits the content (not testimonials with long text).
- Responsive via Swiper's `breakpoints` config — different slides-per-view per device.
- Each instance initialized in `script.js`, never inline `<script>` in HTML.

## 14. Google Fonts

- Match the design's typography per Section 5's pairing.
- `<link rel="preconnect">` + import for performance.
- Exposed as CSS custom properties, applied via custom classes.

## 15. Human-Readable, Custom Class Naming

- Pair Bootstrap utilities with meaningful custom classes: `class="hero-section d-flex align-items-center"`.
- BEM-style naming: `.block-name`, `.block-name__element`, `.block-name--modifier` (e.g. `.service-card`, `.service-card__title`, `.service-card--featured`).
- No meaningless names (`.div1`, `.box2`, `.wrap`).
- Kebab-case, purpose-based naming — not appearance-based (`.cta-button`, not `.gold-button`).

## 16. Clean, Maintainable Code

- Consistent indentation and formatting.
- Comment major sections: `<!-- Hero Section -->`, `<!-- Services -->`, `<!-- Footer -->`.
- Separate concerns into dedicated files per the folder structure below.
- Valid, W3C-compliant HTML.
- Accessibility: ARIA labels where needed, sufficient color contrast, full keyboard navigability, visible focus states.

---

## 17. Deliverables & Folder Structure

```
project-root/
│
├── index.html
├── [other-page].html
├── assets/
│   ├── css/
│   │   └── style.css        # custom styles only (fonts, tokens, reveal transitions, BEM components)
│   ├── js/
│   │   └── script.js        # Swiper init, IntersectionObserver reveal system, custom interactions
│   └── images/
│       └── ...               # optimized/compressed design assets
│
└── README.md                 # setup notes: CDN links used, fonts, credits
```

### CDN Checklist
- Bootstrap 5 CSS + JS bundle
- Font Awesome CSS
- Swiper CSS + JS
- Google Fonts `<link>`
- Custom `style.css` (after Bootstrap, so overrides apply)
- Custom `script.js` (before `</body>`, `defer` attribute)
- **No GSAP, no jQuery, no AOS library, no React/JSX anywhere**

---

## 18. Priority Rule

Match the source design/reference closely for spacing, typography, and color — but if there's ever a conflict, prioritize **semantic correctness, accessibility, and SEO** over pixel-exact replication.
