# MasjidOS Implementation Plan

Last updated: 2026-07-12

Source notes:
- `_future/MasjidOS-Free-Enrichment-Plan.docx`
- `_future/MasjidOS-Pro-Research-Report.docx`
- Current free plugin release state and WordPress.org approval feedback

## 1. Product Direction

MasjidOS is a WordPress plugin for mosques and Islamic centers.

The product strategy is:

- **Free / WordPress.org:** generous, useful, trust-building core features that make small and medium mosques say "this is enough to run our public website."
- **Pro / Separate plugin:** financial, CRM, operations, payments, automation, reporting, and advanced management features.

The free plugin should feel rich, fast, and complete for public-facing mosque information. The Pro plugin should feel like the mosque committee's operational command center.

## 2. Current Free Plugin Baseline

Already built in the approved free plugin:

- Local prayer time calculation.
- Coordinates, timezone, calculation method, Asr method, offsets.
- Iqamah times.
- Qibla direction.
- Jumuah schedule with Khatib profile, topic, language, notice, and multiple sessions.
- Monthly prayer timetable with navigation and print support.
- Announcements with list and ticker designs.
- Basic events.
- Admin Settings, Docs, shortcode generators.
- Bangla translation pack.
- WordPress.org review fixes:
  - Admin menu position moved to `80`.
  - Shortcode output wrapped with `wp_kses_post()`.
  - Public announcements REST endpoint respects module-enabled state.
- Public shortcodes:
  - `[masjidos_prayer_times]`
  - `[masjidos_jumuah]`
  - `[masjidos_monthly_prayer_times]`
  - `[masjidos_announcements]`
  - `[masjidos_events]`

## 3. Free Plugin Enrichment Strategy

The free version should follow a "Generous Free" strategy.

Goal:

MasjidOS Free should deliver more practical value than many paid single-feature mosque plugins, while keeping Pro reserved for committee operations, payments, reports, and advanced automation.

Expected outcomes:

- Better WordPress.org ratings.
- More installs and word-of-mouth.
- More trust before a future Pro upgrade.
- Strong Bangladesh and global mosque community adoption.

Important boundary:

- Free can be rich.
- Free should not include Pro business logic.
- Free should not include payment processing, donor CRM, member management, accounting, or private committee workflows.
- Any external API use in Free must be opt-in, cached, privacy-conscious, and clearly documented. The current local calculation mode must remain the default.

## 4. Free Modules Roadmap

### Module 1: Prayer Times

Current state:

- Local prayer time calculation exists.
- Qibla, Iqamah, offsets, monthly timetable, countdown, public widgets exist.
- Phase 1 enrichment done: calculation method registry expanded and method-specific Maghrib angle support added.
- Phase 2 enrichment done: local Hijri date helper, Hijri date adjustment setting, public prayer date display, dashboard Hijri source, and monthly Hijri range added.

Planned enrichment:

- Add more calculation methods: **done in phase 1**
  - MWL
  - ISNA
  - Egypt
  - Makkah
  - Karachi
  - Tehran
  - Jafari
  - Dubai
  - Qatar
  - Kuwait
  - Singapore
- Improve Hijri date support: **done in phase 2**
- Add optional print/PDF monthly calendar output.
- Add Gutenberg block for prayer times.
- Improve Qibla compass interaction.
- Add better onboarding for latitude/longitude and timezone.
- Consider optional API-assisted prayer time source later:
  - Aladhan.com may be supported as an optional source.
  - Local calculation remains default.
  - API must be disabled by default or clearly opt-in.
  - Cache results and document privacy.

Implementation priority:

1. Expand calculation method registry.
2. Add Hijri/date polish.
3. Add Gutenberg block.
4. Add monthly PDF/print export polish.
5. Optional API source after privacy and review check.

### Module 2: Events and Jumuah

Current state:

- Basic events exist.
- Jumuah profile and public shortcode exist.

Planned enrichment:

- Unlimited public events.
- Featured image for event cards.
- Event reminder badge, for example "3 days left".
- iCal export for Google Calendar and Apple Calendar.
- Islamic auto-events from Hijri calendar:
  - Eid al-Fitr
  - Eid al-Adha
  - Ashura
  - Muharram
  - Laylatul Qadr
  - Other configurable important dates
- Recurring Jumuah automation.
- Jumuah Khutbah archive:
  - title
  - topic
  - khatib
  - date
  - searchable list

Free boundary:

- Basic event listing and public calendar stay Free.
- Advanced recurring events with audio archive can be Pro later if needed.

Implementation priority:

1. Event featured image.
2. iCal export.
3. Islamic auto-events.
4. Jumuah archive.
5. Recurring Jumuah.

### Module 3: Announcements

Current state:

- Scheduled announcements exist.
- List and ticker designs exist.

Planned enrichment:

- Multiple announcement queue polish.
- Priority ordering.
- Expiry handling polish.
- Popup announcement design.
- Ramadan special ticker style.
- Better public design variations:
  - banner
  - ticker
  - popup
  - compact list

Free boundary:

- Public notices, ticker, basic priority, and expiry remain Free.
- Advanced digital signage rotations can live in TV Display or Pro design packs later.

Implementation priority:

1. Priority ordering UI.
2. Popup design.
3. Ramadan ticker mode.
4. Design picker in shortcode generator.

### Module 4: Content and Education

Current state:

- Not yet fully implemented as a module.

Planned free features:

- Random Quran verse widget.
- Random Hadith widget.
- 99 Names of Allah widget.
- Audio Quran embed shortcode.
- Islamic article category helper.
- Quran verse block:
  - Arabic
  - Bangla/English translation
  - share button
  - optional tafsir link

API/privacy note:

- If using third-party Quran/Hadith APIs, make them opt-in or cache content locally.
- Prefer bundled starter content or admin-provided content for WordPress.org safety.

Implementation priority:

1. 99 Names widget because it is self-contained.
2. Random Quran verse widget with local seed data.
3. Hadith widget with local seed data or opt-in source.
4. Audio Quran embed.
5. Gutenberg blocks.

### Module 5: TV Display Mode

New Free module.

Purpose:

A fullscreen URL for mosque screens/TVs. The user opens one URL on a display and it shows prayer times and announcements without WordPress UI.

Public URL idea:

- `/masjidos-display/`

Display content:

- Masjid name and logo.
- Current clock.
- Today's prayer times.
- Next prayer countdown.
- Scrolling announcements.
- Hijri and Gregorian dates.
- Optional Jumuah notice.
- Islamic geometric background.

Technical requirements:

- No theme header/footer.
- No WordPress admin UI.
- Responsive for TV, tablet, and large display.
- Dark mode optimized.
- Auto-refresh data.
- Font size/display density settings.

Implementation priority:

1. Rewrite endpoint or virtual page route.
2. Public display template.
3. Display settings section.
4. Announcement ticker integration.
5. Prayer countdown integration.

### Module 6: Ramadan Mode

New Free seasonal module.

Purpose:

A seasonal feature set that can create strong adoption during Ramadan.

Features:

- Manual Ramadan mode toggle.
- Optional auto-detection later.
- Suhoor countdown.
- Iftar countdown.
- Ramadan prayer schedule.
- Taraweeh time.
- Daily Ramadan dua.
- Ramadan banner.
- Eid countdown after Ramadan.

Implementation priority:

1. Ramadan settings schema.
2. Suhoor/Iftar display using prayer times.
3. Ramadan shortcode/widget.
4. Ramadan banner.
5. Taraweeh time.
6. Daily dua content.

### Module 7: Islamic Calendar

New Free module.

Features:

- Hijri + Gregorian dual calendar.
- Islamic event highlighting.
- Upcoming event overlay from MasjidOS Events.
- Month navigation.
- Shortcode:
  - `[masjidos_islamic_calendar]`
- Gutenberg block later.

Implementation priority:

1. Hijri date utility.
2. Calendar public shortcode.
3. Admin settings for important dates.
4. Event module integration.

### Module 8: Duas and Azkar

New Free module.

Features:

- Morning azkar.
- Evening azkar.
- Daily duas by situation:
  - before/after food
  - sleeping/waking
  - entering/leaving home
  - entering/leaving masjid
  - travel
  - rain
- Counter with `localStorage`.
- Share button.
- Optional audio pronunciation.

Implementation priority:

1. Local dua content schema.
2. Public shortcode:
  - `[masjidos_duas]`
3. Counter UI.
4. Share button.
5. Admin content override later.

## 5. Pro Plugin Strategy

MasjidOS Pro should be a separate plugin:

- Directory: `masjidos-pro`
- Depends on free MasjidOS.
- Does not modify free plugin files directly.
- Registers modules through hooks and filters.

Primary market position:

- Bangladesh-first mosque operations suite.
- bKash + Nagad support.
- Bangla + English + Arabic.
- WordPress-native alternative to expensive SaaS platforms.

Competitor gap:

- MOHID, ConnectMazjid, Masjidbox, MadinaApps are SaaS-oriented.
- Existing WordPress mosque plugins are often single-feature.
- No strong WordPress plugin currently combines Bangla, local payments, accounting, and mosque management.

## 6. Pro Modules Roadmap

### Module 7: Donations and Campaigns

Core Pro killer feature.

Features:

- One-time donations.
- Recurring donations.
- bKash, Nagad, Rocket for Bangladesh.
- Stripe and PayPal for global use.
- Donor receipt email.
- Anonymous donation option.
- Goal-based campaigns.
- Campaign progress bar.
- Deadline and countdown.
- Campaign share link.
- Live donation ticker.
- Zakat suite:
  - Nisab calculator.
  - Gold/silver rate source.
  - Zakat amount calculator.
  - Zakat fund isolation.
  - Beneficiary/disbursement tracking.

### Module 8: Accounts and Ledger

Trust-building Pro feature.

Features:

- Income and expense entry.
- Cash and online payment tracking.
- Multi-fund tracking:
  - General
  - Zakat
  - Building
  - Madrasa
  - Custom funds
- Bank reconciliation.
- Recurring expenses.
- Monthly/yearly P&L statement.
- Fund-wise breakdown.
- PDF and Excel export.
- Audit trail.
- Public transparency mode:
  - summary dashboard
  - details hidden
  - monthly infographic style output

### Module 9: Member and Community CRM

Features:

- Family-based registration.
- Member photo, profession, area.
- Donation history timeline.
- Attendance history.
- Membership tiers:
  - General Member
  - Life Member
  - Patron
  - Honorary
- Renewal reminders.
- Membership card PDF.
- Searchable directory.
- Public/private toggle.
- Map view by area.

### Module 10: Sadaqah Jariyah Fund

Features:

- Project-specific funds.
- Donor wall.
- Progress bar.
- Remaining amount.
- Public campaign page.

### Module 11: Hall and Facility Booking

Features:

- Hall/room booking calendar.
- Online booking request form.
- Admin approval workflow.
- Booking conflict detection.
- Wedding/event management.
- Janazah support workflow.
- Itikaf registration.
- Classroom schedule.
- Invoice generation.

### Module 12: Madrasa and Islamic School

Features:

- Student admission form.
- Student profile.
- Class/section assignment.
- Guardian contact.
- Attendance tracking.
- Exam result entry.
- Result card PDF.
- Monthly fee collection.
- Due fee reminders.
- Scholarship/waiver tracking.
- Fee receipt generation.

### Module 13: Committee and Governance

Features:

- Committee member list.
- Role and term tracking.
- Meeting schedule.
- Agenda.
- Meeting minutes.
- Decision archive.
- Document vault.
- Election module later.

### Module 14: Volunteer Management

Features:

- Volunteer profile.
- Skill database.
- Event-based signup.
- Role assignment:
  - security
  - hospitality
  - parking
  - cleaning
- Attendance and hours.
- Volunteer certificates.
- Recognition leaderboard.

### Module 15: Communication Hub

Bangladesh-focused Pro feature.

Features:

- WhatsApp notifications:
  - prayer reminder
  - event alert
  - donation receipt
  - bulk announcement
  - birthday/anniversary greeting
- Email newsletter.
- Subscriber list.
- Template-based messages.
- SMS:
  - emergency notice
  - fee reminder
  - event reminder

## 7. Pro Technical Architecture

### Free plugin extension hooks needed

Add or preserve these hooks in the free plugin:

- `masjidos_defaults`
- `masjidos_module_definitions`
- `masjidos_dashboard_data`
- `masjidos_admin_dependencies`
- Public widget design registries:
  - `masjidos_prayer_widget_designs`
  - `masjidos_render_prayer_widget_design`
  - `masjidos_jumuah_widget_designs`
  - `masjidos_render_jumuah_widget_design`
  - `masjidos_monthly_prayer_widget_designs`
  - `masjidos_render_monthly_prayer_widget_design`
  - `masjidos_announcement_widget_designs`
  - `masjidos_render_announcement_widget_design`

### Pro plugin files

Pro root:

- `masjidos-pro.php`
- `includes/class-masjidos-pro.php`
- `includes/class-masjidos-pro-installer.php`
- `includes/class-masjidos-pro-rest.php`
- `includes/class-masjidos-pro-public.php`

Repositories:

- `class-itmms-donations.php`
- `class-itmms-accounts.php`
- `class-itmms-members.php`
- `class-itmms-funds.php`
- `class-itmms-bookings.php`
- `class-itmms-students.php`
- `class-itmms-committee.php`
- `class-itmms-volunteers.php`
- `class-itmms-notifications.php`

Admin modules:

- `members.js`
- `donations.js`
- `accounts.js`
- `bookings.js`
- `madrasa.js`
- `committee.js`
- `volunteers.js`
- `notifications.js`

Public shortcodes:

- `[masjidos_financials]`
- `[masjidos_donation_form]`
- `[masjidos_campaign]`
- `[masjidos_member_directory]`
- `[masjidos_facility_booking]`

## 8. Suggested Database Tables

Free plugin should remain minimal.

Pro tables:

- `itmms_donations`
- `itmms_campaigns`
- `itmms_funds`
- `itmms_ledger_entries`
- `itmms_members`
- `itmms_member_families`
- `itmms_facility_bookings`
- `itmms_madrasa_students`
- `itmms_madrasa_attendance`
- `itmms_madrasa_fees`
- `itmms_committees`
- `itmms_meetings`
- `itmms_volunteers`
- `itmms_notification_logs`

## 9. Pricing Strategy

Draft pricing:

- Free: 1 site, 0 BDT, six or more public-facing core modules.
- Starter Pro: 1 site, 3,999 BDT/year, Donations + Accounts + Members.
- Organization: 3 sites, 7,999 BDT/year, all Starter plus Madrasa + Hall + Committee.
- Complete: unlimited sites, 14,999 BDT/year, all modules plus WhatsApp + API + white-label.
- Lifetime: unlimited sites, 39,999 BDT one-time, all features.

This is a planning draft, not final public pricing.

## 10. Implementation Priority

### Immediate Free Roadmap After WordPress.org Launch

P1:

1. Prayer Times enrichment.
2. Ramadan Mode.

P2:

3. TV Display Mode.
4. Islamic Calendar.

P3:

5. Duas and Azkar.
6. Content and Education widgets.

P4:

7. Events enrichment.
8. Announcements enrichment.

### Pro Roadmap

P1:

1. Pro bootstrap plugin and license-safe architecture.
2. Donations and Campaigns.
3. Accounts and Ledger.
4. Members and CRM.

P2:

5. Sadaqah Jariyah Funds.
6. Hall and Facility Booking.
7. Madrasa and School.

P3:

8. Committee Manager.
9. Volunteer Management.
10. Communication Hub.

## 11. Verification Plan

For every Free release:

- Run Plugin Check.
- Run Plugin Review ruleset.
- Run PHP syntax check.
- Run JS syntax check.
- Confirm no external API calls happen without explicit user configuration.
- Test all public shortcodes.
- Test Bangla language pack.
- Build clean ZIP.
- Update SVN trunk and tag.

For Pro:

- Verify free plugin dependency.
- Verify activation/deactivation safety.
- Verify database install/upgrade.
- Verify role/capability checks.
- Verify payments in sandbox mode before live mode.
- Verify no financial data is exposed publicly unless transparency mode is enabled.
- Verify exports and reports sanitize all output.

## 12. Growth Plan

WordPress.org growth:

- Add onboarding wizard.
- Add setup checklist.
- Add Bangla and English docs.
- Add YouTube tutorial.
- Share TV Display demo in mosque/community groups.
- Publish use-case pages:
  - mosque website prayer times
  - Bangla mosque plugin
  - Ramadan mosque display
  - Jumuah schedule widget

SEO keywords:

- mosque management plugin WordPress
- masjid website plugin
- prayer times WordPress plugin Bangladesh
- namaz time WordPress
- mosque announcement plugin
- Islamic center WordPress plugin

## 13. Guiding Principle

Build Free for trust.

Build Pro for operations.

MasjidOS Free should make a mosque website beautiful and useful. MasjidOS Pro should help a mosque committee run donations, accounts, members, bookings, school, governance, volunteers, and communications with confidence.
