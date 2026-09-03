# MasjidOS Project Memory

Last updated: 2026-08-18 (Free + Pro live source)

## Snapshot

| Field | Free (`masjidos`) | Pro (`masjidos-pro`) |
|---|---|---|
| Path | `wp-content/plugins/masjidos` (this repo) | sibling `wp-content/plugins/masjidos-pro` |
| Local version | **1.3.0** | **0.20.0** |
| Schema option | `itmms_db_version` **1.6** | `masjidos_pro_db_version` **1.13** |
| Requires | WP **6.2+**, PHP **7.4+** | WP **6.5+**, PHP **7.4+**, Free **≥ 1.2.0** (`Requires Plugins: masjidos`) |
| Prefix | `ITMMS` / `itmms_*` | Classes `MasjidOS_Pro_*`; tables `itmms_pro_*`; textdomain `masjidos-pro` |
| Admin | Fullscreen vanilla-JS SPA | Classic WP submenu pages under MasjidOS + `admin_post_*` forms; injects Free SPA nav |
| REST | `masjidos/v1` | `masjidos-pro/v1` (donations / accounts / collections only) |
| WP.org | Free **1.2.0** live | Not on w.org; local sibling |

Brand: teal `#1A6B5A` / `#176654`, gold `#C9A84C`. License GPL-2.0-or-later.

## Active focus

- Local Free is **1.3.0**. Do not treat it as 1.0 / 1.1.
- Local Pro is **0.20.0** (old tracker said 0.8.0 — stale). Confirm with the user before shipping either plugin.
- Pro **requires Free**. Boot: `plugins_loaded` priority 20 → `MasjidOS_Pro_Core::boot()`. Sets `MASJIDOS_PRO_ACTIVE` only after Free class + version check.
- Free must not contain Pro business logic. Pro extends Free via `masjidos_*` filters. Do not edit Free files to add Pro features.

## Boot sequence

```
masjidos.php
  ├─ Constants, manual require_once (no Composer / PSR-4)
  ├─ register_activation_hook → ITMMS_Installer::activate
  ├─ init → ITMMS_Education::register_post_type
  ├─ plugin_locale + early textdomain load from Settings UI locale
  └─ plugins_loaded → ITMMS_Core::get_instance()
        ├─ ITMMS_Installer::maybe_upgrade()
        ├─ ITMMS_Admin (admin only)
        ├─ ITMMS_Duas_Library
        ├─ ITMMS_Ask_Imam
        ├─ ITMMS_Education::init()
        ├─ ITMMS_Public
        └─ ITMMS_REST
```

Deactivate preserves data. Delete runs `uninstall.php` (multisite-aware).

## Folder map

| Path | Role |
|---|---|
| `masjidos.php` | Bootstrap |
| `uninstall.php` | Delete-time cleanup |
| `includes/` | Domain classes |
| `includes/rest/` | REST traits composed into `ITMMS_REST` |
| `admin/` | Fullscreen SPA + article editor assets |
| `admin/assets/js/modules/` | dashboard, welcome, settings, features, docs, announcements, events, khutbah, minbar, shared |
| `public/` | Shortcodes, blocks, TV, iCal |
| `public/templates/` | Widget PHP views |
| `languages/` | `.pot` + BN/AR JSON/MO packs |
| `_future/` | Plans, memory, translation/build tools — **not shipped** |

## Classes

| Class | File | Role |
|---|---|---|
| `ITMMS_Core` | `includes/class-itmms-core.php` | Singleton orchestrator |
| `ITMMS_Installer` | `includes/class-itmms-installer.php` | Activate / upgrade / dbDelta |
| `ITMMS_Roles` | `includes/class-itmms-roles.php` | Imam / Muazzin + caps |
| `ITMMS_Settings` | `includes/class-itmms-settings.php` | Single option `itmms_settings` |
| `ITMMS_Pro_Bridge` | `includes/class-itmms-pro-bridge.php` | `masjidos_pro_is_active()` + marketing URLs |
| `ITMMS_Prayer_Times` | `includes/class-itmms-prayer-times.php` | Local solar calc, optional Aladhan, Qibla, cache |
| `ITMMS_Prayer_Timetable` | `includes/class-itmms-prayer-timetable.php` | CSV override store (`itmms_prayer_timetable`) |
| `ITMMS_Iqamah_Rules` | `includes/class-itmms-iqamah-rules.php` | Rule-based iqamah offsets |
| `ITMMS_SalahAPI` | `includes/class-itmms-salahapi.php` | SalahAPI document export |
| `ITMMS_Hijri` | `includes/class-itmms-hijri.php` | Gregorian→Hijri, ±3 day adjust |
| `ITMMS_Announcements` | `includes/class-itmms-announcements.php` | Custom-table CRUD |
| `ITMMS_Events` | `includes/class-itmms-events.php` | Custom-table CRUD |
| `ITMMS_Khutbah` | `includes/class-itmms-khutbah.php` | Khutbah archive repo |
| `ITMMS_Minbar` | `includes/class-itmms-minbar.php` | Khatib profiles, schedule, plans, bookmarks |
| `ITMMS_Ask_Imam` | `includes/class-itmms-ask-imam.php` | CPT Q&A + public submit |
| `ITMMS_Duas_Azkar` | `includes/class-itmms-duas-azkar.php` | Built-in duas catalog |
| `ITMMS_Duas_Library` | `includes/class-itmms-duas-library.php` | CPT `itmms_dua` |
| `ITMMS_Education` | `includes/class-itmms-education.php` | CPT `itmms_article` + verse/hadith/names |
| `ITMMS_REST` | `includes/class-itmms-rest.php` | Composes REST traits |
| `ITMMS_Admin` | `admin/class-itmms-admin.php` | Menu, fullscreen, enqueue |
| `ITMMS_Public` | `public/class-itmms-public.php` | Shortcodes; traits: helpers, designs, blocks, display |

Patterns: `final` classes, singleton where needed, static repositories, capability RBAC, Pro-safe `apply_filters` on designs/defaults/nav.

## Prayer engine

Priority for a given date:

1. CSV timetable row in `itmms_prayer_timetable` if present
2. Else `prayer_source=aladhan` → monthly Aladhan fetch + transient
3. Else **local solar calculation** (default)

Then apply offsets, iqamah times/rules, Ishraq/Zawal extras, Hijri adjust. Transient cache key `itmms_prayers_*`. Default coords Dhaka, method `karachi`, Asr `hanafi`.

Methods: karachi, mwl, isna, egypt, makkah, dubai, qatar, kuwait, singapore, tehran, jafari.

## Data stores

**Options:** `itmms_settings`, `itmms_prayer_timetable`, `itmms_khutbah_plans`, `itmms_minbar_bookmarks`, `itmms_db_version`, `itmms_show_welcome`

**Tables:** `{prefix}itmms_announcements`, `itmms_events`, `itmms_khutbah_archive`, `itmms_khatib_profiles`, `itmms_khatib_schedule`

**CPTs:** `itmms_article` (+ tax `itmms_article_category`), `itmms_dua` (+ `itmms_dua_category`), `itmms_imam_question` (+ `itmms_qa_category`)

**Roles:** `itmms_imam` (prayers, events, announcements, khutbah, reports), `itmms_muazzin` (prayers + reports only). Caps also granted to administrator. Minbar mutations require `itmms_manage_khutbah`.

**Modules in settings:** `prayer_times`, `announcements`, `events` (off by default), `duas_azkar`. Filter: `masjidos_module_definitions`.

**UI language:** `en` | `bn` | `ar` via `itmms_settings.ui_language` → locales `en_US` / `bn_BD` / `ar`.

## Public surface

### Shortcodes

- `[masjidos_prayer_times]` designs `classic`, `compact`
- `[masjidos_jumuah]` designs `classic`, `compact`
- `[masjidos_monthly_prayer_times]` designs `table`, `compact`
- `[masjidos_announcements]` designs `list`, `ticker`
- `[masjidos_events]`
- `[masjidos_islamic_calendar]` alias `[itmms_calendar]`
- `[masjidos_duas_azkar]`
- `[masjidos_khutbah_archive]`, `[masjidos_khatib_this_week]`, `[masjidos_upcoming_khutbah]`, `[masjidos_khutbah_search]`
- `[masjidos_quran_verse]`, `[masjidos_hadith]`, `[masjidos_allah_names]`, `[masjidos_audio_quran]`, `[masjidos_articles]`
- `[masjidos_ask_imam]`, `[masjidos_imam_answers]` (registered in Ask Imam class)

### Blocks (`masjidos/*`)

prayer-times, islamic-calendar, monthly-prayer-times, jumuah, announcements, events, duas-azkar, khutbah-archive, khatib-this-week, upcoming-khutbah, khutbah-search, quran-verse, hadith, allah-names, audio-quran, articles

### Other public routes

- TV: `/masjidos-display/`
- Event iCal: `?masjidos_ical={id}`
- Assets enqueue only when a shortcode/block/TV path needs them

## REST `masjidos/v1`

Admin (cap-gated): `/dashboard`, `/welcome/dismiss`, `/settings`, `/salahapi`, `/salahapi/csv`, timetable import/export/sample, announcements CRUD, events CRUD, khutbah CRUD, `/minbar/*` (dashboard, profiles, schedule, plans, references, bookmarks)

Public / `__return_true` (cached via `ITMMS_REST_Response::public_cached_response()` where used): `/prayer-times/today|date|month|monthly`, widget preview routes, `/calendar`, `/announcements/public`, `/events/public`

Traits: permissions, response, dashboard, prayer, widgets, content, minbar.

Exception: Ask the Imam public submit still uses `wp_ajax(_nopriv)_itmms_ask_imam_submit`.

## Pro extension filters (keep stable)

Design registries + renderers:

- `masjidos_prayer_widget_designs` / `masjidos_render_prayer_widget_design`
- `masjidos_jumuah_widget_designs` / `masjidos_render_jumuah_widget_design`
- `masjidos_monthly_prayer_widget_designs` / `masjidos_render_monthly_prayer_widget_design`
- `masjidos_announcement_widget_designs` / `masjidos_render_announcement_widget_design`

Bridge / admin / TV:

- `masjidos_defaults`, `masjidos_module_definitions`
- `masjidos_pro_url`, `masjidos_pro_docs_url`, `masjidos_pro_docs`
- `masjidos_admin_nav`, `masjidos_admin_dependencies`, `masjidos_dashboard_data`
- `masjidos_tv_extra_styles`, `masjidos_tv_extra_slides`
- `masjidos_minbar_ai_available`, `masjidos_salahapi_document`

Locked/informational Pro design names in Free are OK. Pro implementation code is not.

## Coding conventions for this plugin

- Prefix PHP/JS/CSS/options/caps/tables with `itmms` / `ITMMS`; public names with `masjidos`.
- Escape output; sanitize REST/settings input; no `admin-ajax` for new admin APIs.
- Public widgets: vanilla JS/CSS, theme-friendly, self-hosted fonts only (Outfit, Cairo, Noto Sans Bengali).
- Keep `_future/` and `_release/` out of WordPress.org ZIPs.
- Do not delete `_future/wporg-assets`.
- When strings change, rebuild BN (and AR) packs with `_future/tools`.
- PHP 7.4 typed properties/returns are already in use; do not raise PHP requirement without a release decision.

## Free vs Pro boundary

**Free:** prayer (local / Aladhan / CSV), iqamah, Qibla, Hijri, Jumuah, monthly timetable, announcements, events, duas, TV, calendar, education widgets, articles, Minbar, Ask the Imam, roles, Docs, EN/BN/AR, Pro-safe hooks.

**Pro (built, sibling plugin):** donations + bKash/Nagad, accounts/ledger/funds/budgets, collections, members/dues/attendance/cards, transparency PDF, facilities booking, madrasa/fees/teacher portal, volunteers, premium widget designs, TV collections slide. **Not built:** WhatsApp, white-label, Stripe/PayPal, remote license (local stub only).

Extend Free through filters — do not fork Free files. Pro docs live in Pro `includes/class-pro-docs.php` (`masjidos_pro_docs`); never hardcode Pro shortcodes in Free `docs.js`.

---

# MasjidOS Pro (sibling)

Path: `C:\Users\Manzur\Local Sites\powerup\app\public\wp-content\plugins\masjidos-pro`  
README is the human install/docs note. No `uninstall.php`. No Composer. License stub: option `masjidos_pro_license_key`; designs unlock while Pro is active (`masjidos_pro_designs_unlocked`, default true).

## Boot

```
masjidos-pro.php
  ├─ MASJIDOS_PRO_VERSION 0.20.0, MASJIDOS_PRO_MIN_FREE 1.2.0
  ├─ require domain + admin + payments + REST + designs
  ├─ activation → MasjidOS_Pro_Installer::install()
  └─ plugins_loaded:20 → MasjidOS_Pro_Core::boot()
        ├─ need ITMMS_Core + ITMMS_VERSION ≥ 1.2.0
        ├─ define MASJIDOS_PRO_ACTIVE
        ├─ maybe_upgrade schema 1.13
        ├─ License, Designs, REST, Public, Docs, TV collections
        ├─ Admin: Donations, Accounts, Members, Attendance, Transparency,
        │         Facilities, Madrasa, Volunteers
        ├─ Dues cron, Member card, School fees, Teacher portal
        └─ filters: masjidos_admin_nav, masjidos_dashboard_data
```

Admin UI is **classic PHP pages** (not inside Free SPA). Free SPA sidebar gets Pro links via `masjidos_admin_nav`. Dashboard cards via `masjidos_dashboard_data` → `pro_cards`.

## Modules

| Module | Domain class | Admin | Notes |
|---|---|---|---|
| Donations | `MasjidOS_Pro_Donations` | `class-pro-admin-donations.php` | Campaigns; public form → pending; confirm posts to ledger if fund linked |
| Payments | `MasjidOS_Pro_Payments` + bKash/Nagad gateways | Donations → Payments tab | Option `masjidos_pro_payments`; sandbox/live; REST callbacks |
| Accounts | `MasjidOS_Pro_Accounts` | `class-pro-admin-accounts.php` | Funds, ledger, transfers, budgets, month lock |
| Collections | `MasjidOS_Pro_Collections` | Accounts → Collections | Occasions: jumuah, shab_e_barat, eid_ul_fitr (3d), eid_ul_adha (4d), laylat_al_qadr, ashura, mawlid, custom |
| Members | `MasjidOS_Pro_Members` | `class-pro-admin-members.php` | Families, CRM, CSV, public directory opt-in |
| Dues | `MasjidOS_Pro_Dues` | Members → Dues | Option `masjidos_pro_dues_settings`; daily cron reminders |
| Attendance | members table + `MasjidOS_Pro_Attendance_Report` | Members mark; report page | Print/PDF + CSV |
| Member card | `MasjidOS_Pro_Member_Card` | `admin_post_masjidos_pro_member_card` | A6 print HTML |
| Transparency | `MasjidOS_Pro_Transparency` | `class-pro-admin-transparency.php` | Needs Free `public_transparency`; print query var |
| Facilities | `MasjidOS_Pro_Facilities` | `class-pro-admin-facilities.php` | Halls + bookings, conflict check, emails |
| Madrasa | `MasjidOS_Pro_Madrasa` + `MasjidOS_Pro_School_Fees` | `class-pro-admin-madrasa.php` | Classes/students/attendance/fees/receipts |
| Teacher portal | `MasjidOS_Pro_Teacher_Portal` | `/masjidos-teacher/` | Role `masjidos_teacher`, cap `itmms_teach_classes` |
| Volunteers | `MasjidOS_Pro_Volunteers` | `class-pro-admin-volunteers.php` | People + shifts + public signup |
| Designs | `MasjidOS_Pro_Designs` | License page lists keys | Markup only in Pro CSS/PHP |
| TV | `MasjidOS_Pro_TV_Collections` | — | Extra slide on `/masjidos-display/` |
| Docs | `MasjidOS_Pro_Docs` | Free Docs → Pro tab | Filter payload only |
| Print | `MasjidOS_Pro_Print` | shared A4 print layout | Transparency + attendance |

## Premium designs (unlock Free registry keys)

- Prayer: `premium-card`, `mosque-display`, `ramadan-special`
- Jumuah: `premium-sermon`, `mosque-notice`
- Monthly: `premium-print`, `mosque-board`, `ramadan-monthly`
- Announcements: `digital-board`, `ramadan-banner`

## Shortcodes (Pro)

`[masjidos_campaign]`, `[masjidos_donation_form]`, `[masjidos_financials]`, `[masjidos_transparency_report]`, `[masjidos_collections]`, `[masjidos_members]`, `[masjidos_facility_booking]`, `[masjidos_classes]`, `[masjidos_volunteers]`

Public forms also use `admin_post(_nopriv)_masjidos_pro_request_booking|request_enroll|signup_shift`.

## REST `masjidos-pro/v1`

Campaigns/donations CRUD, public `/donate`, `/campaigns/{slug}/public`, payment callbacks `/payments/{bkash|nagad}/callback`, funds, ledger, transfer, accounts summary/public, transparency report, budgets, shura, collections board/CRUD.

Members / facilities / madrasa / volunteers are **admin_post PHP**, not REST.

## Tables (`{prefix}itmms_pro_*`)

campaigns, donations, funds, ledger_entries, fund_budgets, collections, families, members, attendance, dues, facilities, bookings, classes, students, class_attendance, student_fees, fee_payments, volunteers, volunteer_shifts

Seeded funds: General, Zakat (restricted), Building, Madrasa, plus Sadaqah / Lillah / Fidya (restricted).

## Options

`masjidos_pro_db_version`, `masjidos_pro_license_key`, `masjidos_pro_payments`, `masjidos_pro_dues_settings`, `masjidos_pro_dues_reminder_log`, `masjidos_pro_locked_months`, `masjidos_pro_flush_rewrite`

## Roles / caps

Caps: `itmms_manage_donations`, `itmms_manage_accounts`, `itmms_view_accounts`, `itmms_manage_members`, `itmms_teach_classes`. Facilities/school/volunteers reuse `itmms_manage_accounts`.

Roles: `masjidos_treasurer`, `masjidos_accounts_viewer`, `masjidos_teacher`. Administrator gets all Pro caps.

## Pro coding rules

- Keep all Pro markup/CSS/JS in the Pro plugin. Hook Free; do not patch Free.
- New Pro shortcode → update `MasjidOS_Pro_Docs`, not Free docs.
- Prefix tables `itmms_pro_*`, options `masjidos_pro_*`, admin pages `masjidos-pro-*`.
- Currency comes from Free settings (default BDT).
- Financial public shortcodes require Free public transparency.
- No uninstall.php yet — deleting Pro leaves tables/options.

## Related notes (may be stale)

- `_future/release-tracker.md` — version changelog / ship checklist (last 2026-07-19)
- `_future/MasjidOS-Architecture-Analysis.md` — July 2026 deep dive (version numbers there are outdated)
- `_future/implementation_plan.md` — polish strategy
