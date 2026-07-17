# MasjidOS Product Website (Elementor Kit)

English product landing page as **section HTML + one shared CSS file**. Reorder or delete sections freely — styles are not duplicated per section.

This kit lives under `_future/website/` and is **not** part of the installable plugin ZIP.

## Folder layout

```text
_future/website/
  README.md                 ← this guide
  css/mos-landing.css       ← paste once
  sections/
    01-hero.html
    02-trust-strip.html
    03-promise.html
    04-how-it-works.html
    05-prayer.html
    06-tv-display.html
    07-friday-minbar.html
    08-content-learning.html
    09-languages.html
    10-screens-gallery.html
    11-faq.html
    12-final-cta.html
    13-footer.html
```

## Placeholders (replace before publish)

| Placeholder | Use |
|-------------|-----|
| `YOUR_WPORG_URL` | WordPress.org plugin page (Install free CTA) |
| `YOUR_DEMO_URL` | Live demo site |
| `YOUR_DOCS_URL` | Docs / support page (footer) |
| `YOUR_MEDIA_URL_SCREENSHOT_1` … `_6` | Media Library URLs for gallery images |

**Demo mosque name in mockups:** Madani Masjid.

**Suggested WordPress.org URL format:**

`https://wordpress.org/plugins/masjidos/`

(Update when the slug is final.)

## Elementor install (once)

### 1. Font (Outfit)

Prefer **Elementor → Site Settings → Global Fonts / Typography** and set primary font to **Outfit** (or “Outfit” via Google Fonts in Elementor).

The CSS file also includes an `@import` for Outfit as a fallback. If Elementor already loads Outfit, you can delete the `@import` line at the top of `mos-landing.css` to avoid double-loading.

### 2. Paste CSS once

1. Open `css/mos-landing.css`.
2. Copy the full file.
3. Paste into **Elementor → Site Settings → Custom CSS**  
   (or your child theme / Customizer Additional CSS).

Do **not** paste the CSS into every HTML widget.

### 3. Build the page (one section = one widget)

1. Create a new page (e.g. “Home” or “MasjidOS”).
2. Edit with Elementor.
3. For each kit section you want:
   - Add an **Elementor Section** (full width recommended for Hero).
   - Inside it, add an **HTML** widget.
   - Paste the contents of the matching `sections/0N-….html` file.
4. Save / publish.

**Important:** Keep the outer `<div class="mos">…</div>` wrapper. All styles are scoped under `.mos`.

## Reorder / remove sections

- Drag Elementor Sections up/down to reorder.
- Delete any Section you do not need (e.g. Gallery or FAQ).
- No CSS edits required when removing or reordering — shared primitives live in `mos-landing.css`.

Suggested default order matches the filenames (`01` → `13`).

## Gallery screenshots

Source files (upload to Media Library, then paste URLs into section 10):

| Placeholder | Suggested source |
|-------------|------------------|
| `YOUR_MEDIA_URL_SCREENSHOT_1` | `_future/wporg-assets/screenshot-1.png` |
| `YOUR_MEDIA_URL_SCREENSHOT_2` | `_future/wporg-assets/screenshot-2.png` |
| `YOUR_MEDIA_URL_SCREENSHOT_3` | `_future/wporg-assets/screenshot-3.png` |
| `YOUR_MEDIA_URL_SCREENSHOT_4` | `_future/wporg-assets/screenshot-4.png` |
| `YOUR_MEDIA_URL_SCREENSHOT_5` | `_future/wporg-assets/screenshot-5.png` |
| `YOUR_MEDIA_URL_SCREENSHOT_6` | `_future/wporg-assets/screenshot-6.png` |

You can use screenshots 7–10 later or swap captions to match.

## Section map

| # | File | Job |
|---|------|-----|
| 01 | `01-hero.html` | Brand + promise + Install / Demo |
| 02 | `02-trust-strip.html` | Offline-first / languages / free core |
| 03 | `03-promise.html` | Problem → outcome |
| 04 | `04-how-it-works.html` | Welcome → Settings → Shortcode |
| 05 | `05-prayer.html` | Local prayer times story |
| 06 | `06-tv-display.html` | Fullscreen lobby board |
| 07 | `07-friday-minbar.html` | Jumuah + Minbar |
| 08 | `08-content-learning.html` | Articles, Duas, Quran/Hadith |
| 09 | `09-languages.html` | EN · BN · AR |
| 10 | `10-screens-gallery.html` | Promo screens |
| 11 | `11-faq.html` | Accordion FAQ |
| 12 | `12-final-cta.html` | Install again |
| 13 | `13-footer.html` | Links + license note |

## Design notes

- Brand colors: teal `#1a6b5a`, gold `#c9a84c` (MasjidOS admin brand).
- Hero is one composition: brand-first, one headline, one sentence, one CTA group, full-bleed visual plane.
- Namespace: everything under `.mos` to limit theme/Elementor collisions.

## Optional later

- Enqueue `mos-landing.css` from a child theme or MU-plugin instead of Site Settings.
- Bangla clone of every section.
- Elementor Kit `.json` export (HTML + CSS stays more portable for v1).

## Out of scope (this pass)

- Live demo hosting
- PHP enqueue inside the MasjidOS plugin
- Full Bangla marketing page
