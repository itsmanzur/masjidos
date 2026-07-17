# MasjidOS Deep Architecture Analysis

**Plugin:** MasjidOS v1.1.0  
**Path:** `wp-content/plugins/masjidos`  
**WP.org:** https://wordpress.org/plugins/masjidos/  
**Internal prefix:** `ITMMS`  
**Approx. size:** ~16.5k LOC (PHP ~7.2k · JS ~3.7k · CSS ~5.7k)  
**Analysis date:** July 2026  

---

## Verdict

MasjidOS already has the **broadest feature surface** in the WordPress prayer-time niche — it is a mosque OS, not just a timetable plugin.

The gap vs [Daily Prayer Time](https://wordpress.org/plugins/daily-prayer-time-for-mosques/) (1,000+ installs, 4.9★) is **not breadth**. It is:

1. Prayer-ops depth (CSV / Iqamah workflows)
2. Digital-screen polish
3. Trust UX (accuracy transparency)
4. Market proof (reviews / installs)

**Masterpiece formula:** Be as board-accurate as Daily Prayer Time + as block/API-modern as Muslim Prayer Times + remain the only true mosque OS (notices, events, roles, duas, TV) that stays local-first and privacy-clean.

---

## 1. Architecture Breakdown

### Folder structure

| Path | Purpose |
|------|---------|
| `masjidos.php` | Bootstrap, constants, activation hooks |
| `uninstall.php` | Multisite-aware cleanup |
| `admin/` | Fullscreen SPA shell + assets |
| `admin/class-itmms-admin.php` | Menu, enqueue, fullscreen UI |
| `admin/assets/js/app.js` | SPA entry |
| `admin/assets/js/modules/` | dashboard, settings, announcements, events, features, docs, shared |
| `includes/` | Domain logic (settings, prayer, REST, repos, roles) |
| `public/` | Shortcodes, templates, frontend/TV assets |
| `public/templates/` | PHP view partials per widget |
| `languages/` | `.pot` + `bn_BD` |
| `_future/` | Plans, research, translation tools (not runtime) |
| `_release/` | Packaged ZIP artifact |

### Boot sequence

```
masjidos.php
  ├─ Constants (ITMMS_VERSION, ITMMS_DB_VERSION=1.3, paths)
  ├─ Manual require_once of all classes (no Composer/PSR-4)
  ├─ register_activation_hook → ITMMS_Installer::activate
  ├─ init → ITMMS_Education::register_post_type
  └─ plugins_loaded → ITMMS_Core::get_instance()
        ├─ ITMMS_Installer::maybe_upgrade()
        ├─ ITMMS_Admin (admin only)
        ├─ ITMMS_Duas_Library
        ├─ ITMMS_Public
        └─ ITMMS_REST
```

### Core classes

| Class | File | Role |
|-------|------|------|
| `ITMMS_Core` | `includes/class-itmms-core.php` | Singleton orchestrator |
| `ITMMS_Installer` | `includes/class-itmms-installer.php` | Activate/upgrade, dbDelta, rewrites |
| `ITMMS_Roles` | `includes/class-itmms-roles.php` | Imam / Muazzin + custom caps |
| `ITMMS_Settings` | `includes/class-itmms-settings.php` | Single-option settings + sanitizers |
| `ITMMS_Prayer_Times` | `includes/class-itmms-prayer-times.php` | Local solar calc, Aladhan, cache, Qibla |
| `ITMMS_Hijri` | `includes/class-itmms-hijri.php` | Gregorian→Hijri + labels |
| `ITMMS_Announcements` | `includes/class-itmms-announcements.php` | Custom-table CRUD |
| `ITMMS_Events` | `includes/class-itmms-events.php` | Events CRUD + Islamic/Jumuah synthetic |
| `ITMMS_Duas_Azkar` | `includes/class-itmms-duas-azkar.php` | Built-in duas catalog |
| `ITMMS_Duas_Library` | `includes/class-itmms-duas-library.php` | CPT `itmms_dua` |
| `ITMMS_Education` | `includes/class-itmms-education.php` | Articles CPT + verse/hadith/names |
| `ITMMS_REST` | `includes/class-itmms-rest.php` | `masjidos/v1` API |
| `ITMMS_Admin` | `admin/class-itmms-admin.php` | Admin menu + SPA |
| `ITMMS_Public` | `public/class-itmms-public.php` | Shortcodes, blocks, TV, iCal |

### Design patterns

- **OOP + `final` classes** for most domain objects
- **Singleton** (`get_instance`): Core, Admin, Public, Duas_Library
- **Static service/repository classes:** Settings, Prayer_Times, Announcements, Events, Hijri, Education, Roles, Installer
- **Hooks-based WordPress integration**
- **REST as admin API** (no classic `admin-ajax.php`)
- **SPA-in-admin** (vanilla JS string templates — not React/Vue)
- **Free/Pro design registry** via `apply_filters` on design maps
- **Capability-based RBAC** (custom caps, not only `manage_options`)
- **No DI container / Composer autoload**

### How components interact

```
Settings (wp_options: itmms_settings)
    ↓
Prayer engine (transient cache 12h)
    ↓
Public templates / TV display / Gutenberg blocks

Admin SPA (vanilla JS)
    ↓ X-WP-Nonce: wp_rest
REST masjidos/v1
    ↓ capability checks
Settings / Announcements table / Events table
```

---

## 2. Core Logic & Systems

### Prayer engine (main engine)

**File:** `includes/class-itmms-prayer-times.php`

1. Read settings (lat/lng/timezone/method/Asr/offsets/iqamah/source)
2. Build cache key from date + settings hash
3. If `prayer_source=aladhan` → fetch monthly Aladhan API (30-day transient), map day timings
4. Else **local solar calculation**: equation of time, declination, method fajr/isha angles, Hanafi Asr factor
5. Apply per-prayer minute offsets + iqamah display strings
6. Cache result 12 hours; refresh dynamic “next prayer” state without full recalc

### Data flow

| Stage | What happens |
|-------|----------------|
| Input | Admin settings, announcement/event forms, shortcode atts |
| Processing | Sanitizers → domain services → SQL/options/transients |
| Output | Escaped PHP templates, REST JSON, TV HTML, iCal `.ics` |

### Hooks / REST / AJAX

- **AJAX:** None (`wp_ajax_*` not used)
- **REST namespace:** `masjidos/v1`

| Route | Auth |
|-------|------|
| `/dashboard` | ITMMS cap / admin |
| `/settings` GET/POST | read / `itmms_manage_settings` |
| `/prayer-times/today` | can_read |
| `/prayer-times/monthly` | **public** |
| `/*-widget` preview routes | **public** (`__return_true`) |
| `/announcements`, `/events` CRUD | manage_* caps |
| `/announcements/public`, `/events/public` | public |

Important WP hooks: activation, `plugins_loaded`, `init`, `rest_api_init`, `admin_menu`, `template_redirect` (TV + iCal), rewrite for `/masjidos-display/`, block editor assets.

Pro extension filters:

- `masjidos_*_widget_designs`
- `masjidos_render_*_widget_design`

---

## 3. Feature Analysis

| Feature | How it works | Maturity |
|---------|--------------|----------|
| **Prayer times + Qibla** | Local/Aladhan calc; shortcode + block; DeviceOrientation compass | Strong |
| **Monthly timetable** | `for_month()` + REST month nav + print | Strong |
| **Jumuah** | Settings blob (sessions, khatib, topic); synthetic Friday events | Good |
| **Islamic calendar** | Hijri overlay + event markers + REST | Good |
| **TV display** | Rewrite `/masjidos-display/` + dedicated JS/CSS + Google Fonts | Good |
| **Announcements** | Custom table + start/end schedule; free designs: list, ticker | Partial |
| **Events** | Custom table + Islamic holidays + iCal download | Good |
| **Duas & Azkar** | Built-in catalog + CPT library + localStorage counters | Strong |
| **Quran / Hadith / Names / Audio** | Day-of-year hardcode + QuranicAudio CDN stream | Basic |
| **Khutbah archive** | Table + public shortcode only — **no admin CRUD** | Broken gap |
| **Islamic Articles** | CPT + taxonomy; no shortcode widget | Partial |
| **Imam / Muazzin roles** | Custom caps on activation | Good |
| **Docs / Features** | Live shortcode previews + generators in admin SPA | Good |

### Hidden / advanced

- Module toggles via `modules.*` in settings
- Ramadan CSS class on announcements when Hijri month = 9
- Recurring Jumuah event IDs ≥ 90000 (synthetic)
- Free/Pro design lock path (filter-injectable for Pro plugin)
- Settings stubs: `currency`, `public_transparency` (likely Pro foreshadowing)
- Alias shortcode `[itmms_calendar]`

### Shipped vs advertised mismatch (important)

- **Banner / popup** announcements: template supports them; Features/readme advertise them; design registry only frees `list` / `ticker` → requesting `design="banner|popup"` hits **Pro lock notice**
- **Khutbah archive:** DB + public UI exist; **zero admin/REST write path**

---

## 4. Database & Data Handling

### Custom tables (`dbDelta`, schema `ITMMS_DB_VERSION = 1.3`)

| Table | Purpose |
|-------|---------|
| `{prefix}itmms_announcements` | Scheduled notices |
| `{prefix}itmms_events` | Community events |
| `{prefix}itmms_khutbah_archive` | Khutbah records (read-only from admin POV today) |

### Options / cache

| Key | Use |
|-----|-----|
| `itmms_settings` | All settings (single blob) |
| `itmms_db_version` | Schema marker |
| Transient `itmms_prayers_{md5}` | Per-day prayer cache (12h) |
| Transient duas library | Custom duas list (1h) |
| Aladhan month transient | 30 days |
| Object-cache groups | Announcements/events (1 min + last_changed bust) |

### Post types

- CPT `itmms_dua` + tax `itmms_dua_category`
- CPT `itmms_article` + tax `itmms_article_category`

### Performance implications

- Single settings blob = simple reads, but large write races / harder migrations later
- Transients good for prayer; object-cache optional (falls back gracefully)
- Custom tables correct for high-churn notices/events (better than post meta spam)

---

## 5. UI/UX System

### Admin

- Top menu **MasjidOS** → single mount `#itmms-app`
- Fullscreen body class `itmms-fullscreen`
- SPA tabs: Dashboard · Notices · Events · Features · Modules · Settings · Docs
- Settings sub-tabs: profile, calculation, adjustments, iqamah, jumuah, tv, public
- WP submenu also exposes Duas Library + Articles CPT
- Live shortcode previews via public widget REST endpoints

### Public

- Shortcodes (primary distribution)
- 2 Gutenberg blocks: Prayer Times, Islamic Calendar
- TV fullscreen page
- Templates under `public/templates/`

### UX flow

1. Activate → Settings (coords, method, timezone)
2. Optional: Notices / Events / Jumuah / Duas Library
3. Place shortcodes or open `/masjidos-display/`
4. Use Docs/Features generators for copy-paste shortcodes

### UX problems

- Admin SPA is string-template `innerHTML` → hard to scale polish
- Only 2 blocks with limited Inspector controls
- Docs advertise designs that free tier locks
- Khutbah UI exists publicly with no way to fill data from admin

---

## 6. Performance & Optimization

| Area | Strategy |
|------|----------|
| Assets | Enqueue only when shortcode/block renders; admin assets only on MasjidOS pages |
| Prayer | 12h transient; Aladhan month 30d |
| Notices/Events | Object cache 1 min + last_changed |
| TV | `nocache_headers()` (correct for live countdown) |
| Build | No webpack — zero build tax, but no tree-shaking/min pipeline for admin |

### Bottlenecks / risks

- Google Fonts on TV = third-party latency + privacy disclosure
- Public HTML widget REST endpoints can be hit freely (preview/nav) — cache carefully under load
- Monthly timetable loops day calc (mitigated by cache)

---

## 7. Security & Best Practices

### Good

- `ABSPATH` / uninstall guards
- REST permission callbacks with custom caps
- Settings sanitization (allowlists, lat/lng ranges, timezone validation)
- Dua meta: nonce + `current_user_can` + typed sanitizers
- Public output: `esc_html` / `esc_attr` / `esc_url` + `safe_kses()`
- Prepared SQL via `$wpdb->prepare`
- Admin SPA uses `X-WP-Nonce: wp_rest`

### Watch / weaker

- Many public widget routes return **rendered HTML** with `__return_true` — intentional for previews; XSS blast radius if admin settings compromised
- Read-only `$_GET` filters (TV theme/lang, khutbah search, iCal) sanitized but no nonce (acceptable for display)
- JSON body validation mostly in repositories, not strict REST schema
- Uninstall `DROP TABLE` uses fixed internal table names (phpcs ignored)

### Caps

```
itmms_manage_prayers
itmms_manage_events
itmms_manage_announcements
itmms_view_reports
itmms_manage_settings
```

- **Imam:** prayers, events, announcements, reports  
- **Muazzin:** prayers, reports  
- Administrator gets all caps on activate

---

## 8. Problems & Weaknesses

| Severity | Issue | Why it matters |
|----------|-------|----------------|
| **P0** | Banner/popup advertised but locked by design registry | Trust break; WP.org inconsistency |
| **P0** | Khutbah archive has no admin write path | Dead feature |
| **P1** | No CSV timetable import/export | Daily Prayer Time moat |
| **P1** | Iqamah is static strings, not rules | Real mosque ops need minutes-after-athan |
| **P1** | Only 2 Gutenberg blocks; little style controls | Block-first sites skip shortcodes |
| **P2** | Education content is day-of-year hardcode | Feels thin |
| **P2** | Single settings blob + no PSR-4 | Scale pain for Pro/tests |
| **P2** | Vanilla SPA string templates | UI velocity ceiling |
| **P3** | `currency` / `public_transparency` stubs | Dead settings confuse admins |
| **Market** | <10 installs, 0 reviews | Distribution / trust gap |

### Scalability

- Fine for single-mosque sites
- Multi-mosque / network sync not designed
- Pro modules will fight the flat `includes/` + manual requires structure

---

## 9. Improvement Suggestions (Masterpiece Path)

### Phase A — Trust & fix (2–3 weeks)

1. Register `banner` / `popup` as free **or** stop advertising them
2. Ship Khutbah CRUD in admin SPA + REST
3. Prayer trust panel: source, method, offsets, Hijri, next-7-days preview
4. Self-host TV fonts; remove Google Fonts dependency
5. Collect first 10 mosque testimonials / screenshots

### Phase B — Beat Daily Prayer Time on prayer ops (4–6 weeks)

6. CSV year import + export (Azan + Iqamah columns)
7. Iqama rules engine (fixed / +N after athan / before sunrise)
8. TV templates: multi-layout, slides, overnight dim
9. Ishraq / Zawal / next-Iqamah highlight parity

### Phase C — Platform edge (6–10 weeks)

10. SalahAPI 1.0 JSON + headless prayer endpoints
11. Full block suite with Inspector style controls
12. Articles shortcode/block; richer education packs
13. Bangla number/date formatting parity

### Phase D — SaaS Pro

14. Separate Pro plugin: finance, members, school, push/WhatsApp alerts
15. Design packs via existing filter hooks (keep free generous)
16. Optional cloud sync for multi-branch masajid — **never require account for core prayer**

### Competitive steal list

From **Daily Prayer Time** (https://wordpress.org/plugins/daily-prayer-time-for-mosques/):

- CSV year upload = real mosque board match
- Digital screen template depth
- Ramadan timetable workflows
- Ishraq / Zawal / next Iqamah UX

From **Muslim Prayer Times** (https://wordpress.org/plugins/muslim-prayer-times/):

- Iqama rules engine
- SalahAPI export
- Dense Gutenberg block customization

From **Masjidal** (https://wordpress.org/plugins/masjidal/):

- Cloud sync expectation — differentiate hard on **local-first + privacy**

Niche search: https://wordpress.org/plugins/search/prayer+time/

---

## 10. Rebuild Blueprint

### Recommended folder structure

```
masjidos/
  masjidos.php
  src/
    Core/
      Plugin.php
      Bootstrap.php
    Domain/
      Prayer/
        Calculator.php
        AladhanClient.php
        IqamaRules.php
        CsvImport.php
      Hijri/
        Converter.php
      Notices/
        Repository.php
        Scheduler.php
      Events/
        Repository.php
        IcalExporter.php
      Content/
        Duas.php
        Education.php
        Khutbah.php
      Display/
        TvController.php
        Themes/
    Application/
      Settings.php
      Modules.php
      Capabilities.php
    Infrastructure/
      Database/
        Migrations/
      Cache/
      Http/
    Interfaces/
      Rest/
        Controllers/
      Admin/          # React (wp-scripts) when UI complexity demands
      Public/
        Shortcodes/
        Blocks/
        Templates/
  assets/
  tests/
  languages/
```

### Core classes to aim for

- `Plugin` / light `Container`
- `SettingsStore`
- `ModuleRegistry`
- Domain services: `PrayerCalculator`, `IqamaRules`, `CsvImport`, `NoticeRepository`, `TvController`
- REST controllers as thin adapters

### Tech stack

| Layer | Recommendation |
|-------|----------------|
| PHP | 8.1+, Composer PSR-4, PHPUnit |
| Admin UI | Keep vanilla while simple; migrate hot modules to React via `wp-scripts` when needed |
| Public JS | Stay vanilla + lightweight CSS (speed wins) |
| Blocks | `@wordpress/scripts` block build |
| Pro | Sibling plugin using design/render filters — no Pro business logic in free |

### System design principles

1. **Local-first prayer** remains default and offline-capable
2. **REST is the contract** between admin UI and domain
3. **Free stays generous**; Pro adds packs/modules, not basic mosque necessities
4. **Trust metadata** on every public timetable (source, method, offsets, Hijri)
5. **One render path** for live preview, docs, and frontend widgets

---

## Competitive Snapshot

| Plugin | Installs | Focus | MasjidOS vs them |
|--------|----------|-------|------------------|
| Daily Prayer Time | 1,000+ · 4.9★ | CSV timetable, digital screen | They win prayer-ops trust; you win OS breadth |
| Muslim Prayer Times | 80+ | Blocks, Iqama rules, SalahAPI | They win standards/API; you win mosque tooling |
| Masjidal | 400+ | Cloud-synced widgets | SaaS lock-in; you win local-first privacy |
| Salat Times / AZAN / JetPrayer | 10–500 | Location calc widgets | Feature-thin; easy to outclass |
| **MasjidOS** | **<10 · 0★** | Full mosque OS | Broadest free surface; traction gap is trust + depth |

---

## Where You Already Win

- Offline-first prayer calculation (privacy)
- Fullscreen admin SPA + capability-based REST
- Announcements / events / Jumuah / Duas / education bundle
- Built-in Bangla + Imam/Muazzin roles
- TV route, iCal, Features/Docs generators
- External Services privacy disclosure (WP.org-friendly)

## Where You Must Catch Up

- CSV board match workflows
- Iqama rule engines
- Digital screen template depth
- Gutenberg customization density
- Reviews / installs / support reputation
- Closing P0 product honesty gaps (banner/popup, khutbah CRUD)

---

## Suggested Next Action

Start with **Phase A** (fix advertised features + trust panel). That raises product integrity before adding more modules — which matches your own `_future/implementation_plan.md` polish-first strategy.

---

*Generated from local codebase analysis + WordPress.org niche research. For implementation work, begin with P0 fixes in `public/class-itmms-public.php` (announcement designs) and new Khutbah admin/REST paths.*
