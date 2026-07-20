# MasjidOS Release Tracker

Last updated: 2026-07-19

এই ফাইল = ভার্সন-ভিত্তিক **কী করেছি / কী করিনি** এর মাস্টার নোট।  
WordPress.org changelog লেখার আগে এবং পরের রিলিজ প্ল্যান করতে এখান থেকে তোলা যাবে।

---

## Current focus (2026-07-19)

- **Free 1.3.0 ship = PAUSED.** Free এই অবস্থায় থাকবে; পরে করা হবে।
- **Active now = MasjidOS Pro modules.**
- Free-এ ইতিমধ্যে করা polish/refactor/Plugin Check কাজ `_future/release-tracker.md` সেকশন B-তে টিকে আছে — 1.3.0-এর সময় তোলা যাবে।

---

## Version map

| Channel | Version | Status |
|---|---|---|
| **WordPress.org (Free)** | **1.2.0** | Live |
| Local Free (dev) | 1.2.1 label + post-1.2.0 work | Frozen for now — next public bump later = **1.3.0** |
| **Next Free public release** | **1.3.0** | Deferred (do later) |
| MasjidOS Pro (sibling) | **0.8.0** | **Active development** |

> নোট: লোকালে একবার `1.2.1` বাম্প হয়েছিল (polish + Plugin Check)। w.org এখনও **1.2.0**।  
> Free পাবলিক আপলোড পরে **1.3.0** হবে — তখন 1.2.1-এর সব কাজ + যেকোনো নতুন Free কাজ changelog-এ যাবে।

---

## Roadmap order (updated)

1. ~~Ship Free 1.3.0~~ → **later**
2. **Pro modules** ← **now**
3. Shortcode render আরও trait-এ ভাগ (Free maintainability — after Pro push or when Free ship resumes)
4. Ship Free **1.3.0** when ready

---

# FREE — MasjidOS (`masjidos`)

## A. Already on WordPress.org — 1.2.0

### Features (shipped)

- Welcome experience — first-run, live prayer preview, paths to Settings / Features / TV
- Admin UI language switcher — EN / BN / AR (+ clearer Docs on admin vs public language)
- Islamic Articles richer meta + public `[masjidos_articles]`
- Minbar docs + public Friday widgets documented (`khatib_this_week`, `upcoming_khutbah`, archive, search)
- Docs polish — First 5 minutes, checklist, Articles / Minbar tabs
- Self-hosted fonts (Outfit, Cairo, Noto Sans Bengali) — no Google Fonts at runtime
- Plugin Check / coding-standards cleanups for w.org compliance

### Earlier baseline (still Free core)

- Prayer times (local calc, optional Aladhan), Iqamah, Qibla, Hijri adjust
- Monthly timetable, Jumuah, Duas & Azkar, Announcements, Events
- TV `/masjidos-display/`, Islamic Calendar, Education widgets (verse / hadith / names / audio Quran)
- Roles (Imam / Muazzin), REST `masjidos/v1`, Features previews, BN + AR packs

---

## B. Done since 1.2.0 — fold into **1.3.0** changelog

### Bug fixes / compliance

- Plugin Check errors fixed:
  - Missing `translators:` comments (`khutbah-archive`, `khutbah-search`)
  - TV extra stylesheet escaping + `phpcs:ignore` placement
  - `date()` → `DateTimeImmutable` + `wp_timezone()` (weekday labels)
  - readme plugin name mismatch + trademarked term “plugin” in title → `=== MasjidOS ===`
  - Non-prefixed template variables (`allah-names`, `audio-quran`, `prayer-times`)
- BN pack rebuild: missing strings filled (1499/1499)
- AR pack rebuild (partial AR + EN fallback)
- Clean release ZIP excludes `_future`, `_release`, `.git`

### New / improved features (Free UX polish)

- **Prayer Times + Monthly Timetable** visual/UX polish (brand teal `#176654`, BN digits, trust/empty states)
- **TV Display** polish — localized digits/labels, brand teal, lang fonts, quiet/countdown/jumuah slide localization
- **Islamic Calendar** polish — hero header, legend, Friday highlight, mobile drawer/container fix, localized dates
- **Khutbah surfaces** polish — upcoming, this week’s khatib, archive, search (eyebrow, empty states, badges, forms)
- Broader public widget polish pass earlier in cycle (Jumuah, Announcements, Events, Duas, Education) — keep in 1.3.0 notes if not already credited under 1.2.0

### Architecture / maintainability (Free)

- Split large Public class → traits: helpers, designs, blocks, display
- Split large REST class → traits: permissions, dashboard, prayer, widgets, content, minbar
- Shared REST helper: `ITMMS_REST_Response::public_cached_response()` (Cache-Control for public GETs)
- Settings: Free finance stubs removed → “Coming in Pro” panel only
- Settings: Next 7 days prayer preview
- Minbar REST gated with `itmms_manage_khutbah` (Muazzin cannot mutate Minbar)
- Installer / README aligned to WordPress **6.2+**
- Docs generators stay Free-only where appropriate; Pro docs via filter

### Intentionally NOT done in this Free cycle

- Network / multisite activation polish
- Full Arabic translation completeness (still mostly EN fallback)
- Server-side object-cache for REST (only `Cache-Control` headers)
- Shortcode render methods further trait-split (**next after 1.3.0 ship**)
- New Free modules beyond polish (no new finance/member modules in Free)
- WordPress.org SVN assets upload (banners/screenshots stay in `_future/wporg-assets`)

---

## C. Free — planned for **1.3.0** release checklist

Use this when writing `readme.txt` Changelog + Upgrade Notice.

### Suggested changelog buckets (draft)

**Features**

- Public widget polish across Prayer, Monthly, TV, Calendar, Minbar/Khutbah widgets
- Settings 7-day prayer preview; clearer Free vs Pro settings boundary
- Maintainability: Public/REST trait split; shared public REST cache headers

**Fixes**

- Plugin Check / PHPCS compliance fixes
- Bangla pack completeness for new strings
- Calendar mobile container / drawer reliability; TV/i18n edge cases

### Pre-upload QA (1.3.0)

- [ ] Bump Free public version → **1.3.0** everywhere (`masjidos.php`, `ITMMS_VERSION`, Stable tag, POT metadata)
- [ ] Plugin Check + Plugin Review clean
- [ ] PHP / JS syntax clean
- [ ] Clean ZIP (no `_future` / `_release` / `.git`)
- [ ] Smoke: Welcome, Settings (preview + Pro tab), key shortcodes, TV URL, Minbar admin
- [ ] Deactivate/reactivate + uninstall spot-check
- [ ] Confirm Pro still activates against Free ≥ declared min (`MASJIDOS_PRO_MIN_FREE`)

---

## D. Free — after 1.3.0 (next engineering)

1. **Shortcode render আরও trait-এ ভাগ**  
   `class-itmms-public.php`-এ থাকা render_* shortcode বডি আলাদা traits/modules (prayer / content / minbar / education).
2. তারপর Pro module work-এ ফোকাস (নিচের Pro সেকশন)।

---

# PRO — MasjidOS Pro (`masjidos-pro`)

Current local version: **0.8.0** · Requires Free ≥ **1.2.0** (bump min Free to **1.3.0** when Free ships)

## A. Already built in Pro (local)

### Features

- Premium design packs (prayer / Jumuah / monthly / announcements) via Free design filters
- Donations & campaigns — admin + `[masjidos_campaign]` / `[masjidos_donation_form]`
- Accounts / ledger / funds / committee summary
- Collections (occasion-based) + public shortcodes; TV collections slide when transparency + TV slides on
- Pro Docs payload via `masjidos_pro_docs` filter (Free Docs → Pro tab)
- Pro REST namespace `masjidos-pro/v1`
- License / activation scaffolding
- Own Pro DB tables (`itmms_pro_*`)

### Fixes / polish in this Free cycle (Pro side)

- এই রাউন্ডে Pro-তে বড় শিপ/বাগফিক্স ট্র্যাক করা হয়নি (ফোকাস ছিল Free polish + w.org readiness)
- Free Settings থেকে finance stub সরানো → Pro boundary পরিষ্কার (Pro unlock messaging)

## B. Pro — not done yet (future modules)

Priority ideas from product plan (not shipped):

- Live payment gateways (bKash / Nagad / Stripe / PayPal) beyond pending/manual confirm
- Public transparency report polish + exports (PDF)
- Members / attendance
- Sadaqah Jariyah / project funds depth
- Hall / facility booking
- Madrasa / school
- Committee manager (beyond basic accounts)
- Volunteer management
- WhatsApp / notification hub
- Advanced TV modes / white-label branding
- Ramadan special modes (beyond Free ticker theme)

## C. Pro — after Free 1.3.0 + shortcode trait split

When Free ship + maintainability pass is done:

1. Align `MASJIDOS_PRO_MIN_FREE` → `1.3.0`
2. Pick next Pro vertical (likely **Donations payments** or **Collections/TV** polish — decide at kickoff)
3. Keep Pro logic out of Free; extend via hooks/filters only

---

## Quick “what changed for users?” (1.2.0 → 1.3.0)

| Area | Free | Pro |
|---|---|---|
| Public widgets look/i18n | Major polish | Uses Free base; Pro designs unchanged this cycle |
| Admin Settings | 7-day preview; Pro teaser only | Finance UI stays in Pro |
| Code structure | Public/REST traits | No required Pro change |
| Translations | BN pack completed for new strings | — |
| Compliance | Plugin Check fixes | — |

---

## How to use this file next time

1. প্রতিটি কাজ শেষে এখানে **Done** বা **Not done** আন্ডার লাইন যোগ করো (Free/Pro আলাদা)।
2. রিলিজের আগে সেকশন **C** থেকে `readme.txt` Changelog কপি করে ইংরেজি পালিশ করো।
3. শিপের পর সেকশন **B** কে “Shipped in 1.3.0” এ মুভ করো; নতুন সেকশন খোলো **1.4.0 / next**।
