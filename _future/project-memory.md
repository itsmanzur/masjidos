# MasjidOS Project Memory

Last updated: 2026-06-28

## Product Direction

MasjidOS is a WordPress plugin for mosque and Islamic center management.

Primary positioning:
- Free plugin on WordPress.org for public-facing mosque information.
- Separate MasjidOS Pro plugin later for financial, member, facility, school, notification, and advanced display features.

Tagline direction:
- Complete Management for Mosques & Islamic Centers.

Design direction:
- Custom UI inspired by clean Shadcn-style admin patterns.
- Primary colors: deep teal `#1A6B5A` and gold `#C9A84C`.
- Admin runs as a focused fullscreen WordPress admin app with an exit link back to WordPress.
- Frontend widgets use vanilla JS/CSS and should stay light, fast, and theme-friendly.

## Current Free Plugin Scope

The first WordPress.org release should focus on a polished, dependable free core:

- Prayer Times
- Qibla
- Iqamah display
- Jumuah
- Monthly prayer timetable
- Announcements / notice board
- Basic Events
- Docs and shortcode generators
- Bangla translation pack
- Pro-safe design registries and extension hooks where already present

Avoid shipping Pro implementation code inside the free plugin. The free plugin may show locked/informational Pro design names, but actual Pro behavior belongs in a separate future plugin.

## What Is Already Built

Admin:
- Fullscreen MasjidOS admin app.
- Dashboard, Modules, Settings, Docs, Announcements, Events navigation.
- Settings page uses section tabs to reduce clutter.
- Docs page includes shortcode usage cards and shortcode generators.
- Admin route persistence: reload keeps the current MasjidOS page/tab.
- WordPress media picker is used for Khatib photo.
- Admin UI is translation-ready and bundled with Bangla language files.

Prayer Times:
- Local calculation logic, no external prayer API.
- Latitude, longitude, timezone, calculation method, Asr method.
- Per-prayer minute adjustments.
- Iqamah times.
- Qibla direction.
- Frontend shortcode: `[masjidos_prayer_times]`.
- Free designs: `classic`, `compact`.
- Language attribute supports `en`, `bn`, and `ar` labels.
- Pro-safe design registry and render filter exist.

Jumuah:
- Jumuah settings with first and optional second Jumuah.
- Khutbah time, Jamaat time, topic, language, Khatib name, photo, bio, and notice.
- Frontend shortcode: `[masjidos_jumuah]`.
- Free designs: `classic`, `compact`.
- Pro-safe design registry and render filter exist.

Monthly Prayer Timetable:
- Frontend shortcode: `[masjidos_monthly_prayer_times]`.
- Free designs: `table`, `compact`.
- Month/year navigation controls.
- Current month quick-return.
- Print button.
- Highlights current day.
- Pro-safe design registry and render filter exist.

Announcements:
- Admin notice CRUD exists.
- Frontend shortcode: `[masjidos_announcements]`.
- Free designs: `list`, `ticker`.
- Supports active date window and notice type filtering.
- Repository uses object cache and invalidation.
- Pro-safe design registry and render filter exist.

Events:
- Basic events repository, REST routes, admin UI, and frontend shortcode exist.
- Frontend shortcode: `[masjidos_events]`.
- Intended free scope is basic community event listing only.

Release / Compliance:
- Requires PHP lowered to `7.4`.
- Requires WordPress `6.0`.
- Tested up to local WordPress `7.0`.
- Custom role access mismatch fixed: Imam and Muazzin roles now receive `itmms_view_reports`, including existing-role repair during plugin upgrade.
- Plugin Check ruleset passed after the latest scanner fixes.
- Plugin Review ruleset passed after the latest scanner fixes.
- PHP and JS syntax checks passed.
- Public plugin version is consistently `1.0.0` across plugin header, `ITMMS_VERSION`, readme stable tag, changelog, and upgrade notice.
- Internal `ITMMS_DB_VERSION` is `1.1` for schema/capability repair and intentionally does not need to match the public plugin version.
- WordPress.org asset drafts are kept in `_future/wporg-assets` for later and should not be deleted.

## Free Version Tasks Before WordPress.org Submission

Priority 1 - release blockers or near-blockers:
- Run a fresh Plugin Check and Plugin Review scan before packaging.
- Run PHP syntax check on all PHP files and JS syntax check on admin/public scripts.
- Create and inspect a clean release ZIP that excludes `_future`, `_release`, `.git`, and local tooling.
- Do a clean install test on a fresh WordPress site.
- Do a deactivate/reactivate test and confirm settings/data are preserved.
- Do a delete/uninstall test and confirm plugin data is removed only on delete.
- Confirm final release ZIP filename uses the same public version as `masjidos.php`, `ITMMS_VERSION`, and `Stable tag`.

Priority 2 - strong release quality:
- Add a small prayer-time regression test/checklist for Dhaka coordinates and saved offsets.
- Manually verify all public shortcodes in a clean theme:
  - `[masjidos_prayer_times]`
  - `[masjidos_prayer_times design="compact" language="bn"]`
  - `[masjidos_jumuah]`
  - `[masjidos_monthly_prayer_times]`
  - `[masjidos_monthly_prayer_times design="compact"]`
  - `[masjidos_announcements]`
  - `[masjidos_announcements design="ticker"]`
  - `[masjidos_events]`
- Verify mobile layout for prayer, Jumuah, monthly, announcements, and events widgets.
- Verify Bangla admin and frontend labels after changing site language to Bangla.
- Verify no frontend assets are loaded on pages without MasjidOS shortcodes.
- Confirm announcement/event date windows use the saved MasjidOS timezone.
- Review readme FAQ and privacy text one final time.

Priority 3 - optional for first release:
- Network activation support for multisite.
- WordPress.org screenshots and banners after the plugin is approved or when ready for SVN assets.
- More guided onboarding after activation.
- More robust event design variants.

## Free vs Pro Boundary

Free:
- Prayer Times
- Qibla
- Iqamah
- Jumuah basic scheduler and Khatib profile
- Monthly timetables
- Announcements list/ticker
- Basic events
- Docs and shortcode generators
- Bangla translation
- Pro-safe extension filters

Pro later:
- Donations and campaigns
- bKash/Nagad/Stripe/PayPal integrations
- Accounts and ledger
- Public transparency reports
- Members and attendance
- Sadaqah Jariyah/project funds
- Hall/facility booking
- Madrasa/school management
- Committee manager
- Volunteer management
- WhatsApp notifications
- Advanced TV display/digital notice board modes
- Ramadan special modes
- White-label and branding controls
- PDF reports

## Architecture Notes To Preserve

- Free plugin should not contain Pro business logic.
- Pro designs are referenced as locked/informational presets only.
- Pro plugin should extend free plugin through filters/hooks instead of editing free plugin files.
- Existing public design registries should remain stable:
  - `masjidos_prayer_widget_designs`
  - `masjidos_render_prayer_widget_design`
  - `masjidos_jumuah_widget_designs`
  - `masjidos_render_jumuah_widget_design`
  - `masjidos_monthly_prayer_widget_designs`
  - `masjidos_render_monthly_prayer_widget_design`
  - `masjidos_announcement_widget_designs`
  - `masjidos_render_announcement_widget_design`
- Keep database tables minimal in free core. Pro tables should be created by MasjidOS Pro later.
- Do not delete `_future/wporg-assets`; these drafts may be useful after WordPress.org approval.

## Strategic Notes

The strongest initial free-plugin value is not "many modules"; it is trust:
- Accurate local prayer calculation with adjustments.
- Beautiful public widgets.
- Clear Jumuah and announcement publishing.
- Fast, clean admin experience.
- Bangla support.
- No external API dependency and no telemetry.

The strongest future Pro value is the mosque committee pain point:
- Donations
- Accounts
- Transparency
- Local payment support such as bKash/Nagad
