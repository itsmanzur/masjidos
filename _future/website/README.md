# MasjidOS Elementor Landing + Module Pages

English-primary marketing kit for **Elementor**: numbered home sections, three-tier pricing (Free / Pro / Pro Network), reusable section templates, and one deep-dive page per product module.

Lives under `_future/website/` — **not** part of the installable plugin ZIP.

## Folder layout

```text
_future/website/
  README.md
  css/mos-landing.css          ← paste once (Site Settings → Custom CSS)
  js/mos-landing.js            ← optional demo tabs
  templates/
    section-shell.html
    module-card.html
    pricing-card.html
    faq-item.html
    module-page-skeleton.html
  sections/                    ← Home blocks (Elementor Navigator names)
    01-hero.html … 13-footer.html
  pages/
    home.html                  ← assembled preview (open in browser)
    compare.html
    get-started.html
    features/*.html
  bn/
    home.html                  ← Bangla Home clone (key sections)
    sections/
    features/                  ← prayer-times, tv-display, donations
```

## Placeholders (replace before publish)

| Token | Use |
|-------|-----|
| `https://wordpress.org/plugins/masjidos/` | Get Free / Download (already wired) |
| `YOUR_BUY_URL` | Pro checkout / Buy license |
| `YOUR_CONTACT_URL` | Pro Network / Contact sales |
| `YOUR_DEMO_URL` | Optional Watch demo (Hero) |
| `YOUR_DOCS_URL` | Footer docs link |
| `$XX` / `$YY` | Pro and Network prices (Elementor text) |

Search the kit for `YOUR_` and replace site-wide.

## Elementor method

1. **Font:** Site Settings → Outfit (CSS also `@import`s Outfit; remove import if Elementor already loads it).
2. **CSS once:** paste `css/mos-landing.css` into Elementor Custom CSS (or child theme). Do not paste per widget.
3. **Home:** one top-level Section per file in `sections/`. Name Navigator exactly: `01 Hero`, `02 Trust bar`, … `09 Pricing` (set CSS ID `pricing` on that section), … `12 Final CTA`, `13 Footer`.
4. **Reorder / insert:** drag in Navigator; to insert mid-page, Add Container above/below. Do not nest the whole home in one Section.
5. **Templates:** save polished blocks from `templates/` as Elementor Template → Section (`MasjidOS / Hero`, `MasjidOS / Module card`, …).
6. **Module pages:** create pages at the permalinks below; paste `pages/features/*.html` as M01–M07 sections (see skeleton).
7. **Demo tabs:** enqueue or paste `js/mos-landing.js` once on pages that include `07-live-demo`.
8. **Mobile:** pricing stacks to 1 column; CTAs go full-width under 700px (already in CSS).

## Suggested permalinks

| Page | Slug |
|------|------|
| Home | `/` |
| Compare | `/compare/` |
| Pricing deep-link | `/#pricing` |
| Get started | `/get-started/` |
| Prayer Times | `/features/prayer-times/` |
| TV Display | `/features/tv-display/` |
| Jumuah & Minbar | `/features/jumuah-minbar/` |
| Notices & Events | `/features/notices-events/` |
| Islamic Education | `/features/islamic-education/` |
| Donations (Pro) | `/features/donations/` |
| Accounts (Pro) | `/features/accounts/` |
| Members (Pro) | `/features/members/` |
| Bangla Home | `/bn/` or Polylang/WPML clone |

## Home section map

| # | File | Job |
|---|------|-----|
| 01 | `01-hero.html` | Brand + promise + Get Free / See Pro / Demo |
| 02 | `02-trust-bar.html` | Credibility strip |
| 03 | `03-problem.html` | Why it exists |
| 04 | `04-free-modules.html` | Free module cards → feature pages |
| 05 | `05-pro-modules.html` | Pro module cards → `#pricing` |
| 06 | `06-how-it-works.html` | 3 steps |
| 07 | `07-live-demo.html` | Prayer / Jumuah / TV tabs |
| 08 | `08-compare-strip.html` | Compact Free vs Pro + `/compare/` |
| 09 | `09-pricing.html` | Free / Pro / Pro Network · `#pricing` |
| 10 | `10-social-proof.html` | Quote placeholders |
| 11 | `11-faq.html` | Objections |
| 12 | `12-final-cta.html` | Get Free · Buy Pro · Contact Network |
| 13 | `13-footer.html` | Links (or Theme Builder footer) |

## CTAs (wired)

- **Get Free / Download** → WordPress.org MasjidOS plugin URL  
- **See Pro / View pricing** → `#pricing` or `/#pricing`  
- **Buy Pro / Buy license** → `YOUR_BUY_URL`  
- **Contact for Network / Contact sales** → `YOUR_CONTACT_URL`  
- Module “Learn more” → `/features/…/`  

## Brand

- Teal `#1a6b5a`, gold `#c9a84c`  
- Outfit headings  
- Soft patterned/gradient hero (not purple SaaS / cream-serif cliché)  
- Namespace: all rules under `.mos`

## Local preview

Open `pages/home.html` in a browser (file://). Module pages under `pages/features/` need the CSS path adjusted if opened alone — prefer Elementor assembly, or use relative `../css/mos-landing.css` from `pages/`.

## Bangla (`bn/`)

Clone of key Home sections + prayer-times, tv-display, donations. Expand remaining modules by copying EN feature pages and translating, or use WPML/Polylang.

## Out of scope

- New plugin PHP features  
- Final dollar/taka amounts  
- Elementor Kit `.json` export (HTML + CSS is the portable v1)
