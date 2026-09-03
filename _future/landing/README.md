# MasjidOS landing preview

Standalone marketing page. **Not part of the plugin runtime** and not included in WordPress.org ZIPs (`_future/` is excluded).

No files under `masjidos.php`, `includes/`, `admin/`, or `public/` were changed.

## Open it

Double-click:

`wp-content/plugins/masjidos/_future/landing/index.html`

Or from this folder:

```text
index.html
css/landing.css
js/landing.js
```

## What is interactive

- EN / বাং language toggle (saved in `localStorage`)
- Live next-prayer countdown on the hero board (demo Dhaka times vs the visitor’s clock)
- Product tour tabs: Prayer, Friday, TV (rotating slides), Donations (progress bar)
- Feature cards: All / Free / Pro filter; click a card for details
- Compare table: “differences only” checkbox
- Sticky nav + mobile menu

## Notes

- Pro price is a placeholder until checkout exists.
- An older Elementor HTML kit still lives in `_future/website/`. This folder is the self-contained interactive page.
- Do not enqueue these assets from the plugin.
