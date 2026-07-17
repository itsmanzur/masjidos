=== MasjidOS — Mosque Management Plugin for WordPress ===
Contributors: itsmanzur
Tags: prayer times, mosque, islamic calendar, duas, quran
Requires at least: 6.2
Tested up to: 7.0
Stable tag: 1.2.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Mosque management plugin: prayer times, Jumuah & Minbar, Duas, Quran, Islamic calendar, TV display, events, and notices for mosques.

== Description ==

**MasjidOS** is the all-in-one mosque management plugin for WordPress.

It helps mosque committees, imams, and volunteers publish accurate prayer times, Friday (Jumuah) information, notices, events, and Islamic learning content — without coding and without depending on an external prayer API by default.

Activate MasjidOS, follow the **Welcome** guide, set your location once, then paste a shortcode (or use a Gutenberg block). Visitors see live prayer times; your lobby TV can open a fullscreen board; your website stays yours.

**Who it is for**

* Local mosques and Islamic centers that need a reliable public prayer timetable
* Committees that publish Friday khatib, topic, and notices every week
* Volunteers who want Bangla, English, or Arabic admin screens without hiring a developer
* Mosques that want a simple TV / lobby display URL for a dedicated screen

**Why mosques choose MasjidOS**

* **Local-first prayer times** — calculated on your server from coordinates, timezone, and method (optional Aladhan API if you prefer)
* **Welcome + Docs for ordinary users** — first-run Welcome screen, Features previews, and plain-language Docs so non-technical admins can succeed
* **Admin UI language** — switch MasjidOS menus between English, Bangla, and Arabic (visitor content is never auto-translated)
* **Public widgets in en / bn / ar** — shortcode `language` attribute, or follow your admin language default
* **No telemetry** — no MasjidOS analytics, ads, or forced cloud lock-in

---

= Multilingual (built-in) =

MasjidOS ships with **three** built-in languages for the admin UI and public widgets:

* **English** (`en`) — default source language
* **Bangla / Bengali** (`bn`, Bangladesh pack `bn_BD`) — full translation pack
* **Arabic** (`ar`) — built-in pack with RTL support

Switch the MasjidOS admin language from the top-bar dropdown (or Settings → Profile). For public pages, use the shortcode `language="en|bn|ar"` attribute, or omit it to follow the admin default. Your own content (article titles, notices, khutbah topics, khatib names) is never auto-translated.

The plugin is also translation-ready (`.pot` included) for community contributions of additional locales.

---

= 🕌 Prayer Times & Monthly Timetable =

* Offline calculation with major methods (Karachi, ISNA, MWL, Egypt, Makkah, Tehran, Kuwait, Qatar, Singapore, and more)
* Hanafi or standard Asr; per-prayer minute adjustments to match your printed board
* Iqamah (Jamaat) times, Qibla direction, Hijri date with moon-sighting offset
* Monthly timetable with navigation, print-friendly view, and compact designs
* Shortcodes and Gutenberg **Prayer Times** block

**Use case:** Publish `[masjidos_prayer_times]` on the homepage and `[masjidos_monthly_prayer_times]` on a dedicated timetable page. Adjust +2 minutes on Maghrib if your committee board differs.

= 🕋 Jumuah, Minbar & Friday Tools =

* Jumuah sessions (first / second), khatib profile (name, photo, bio), topic, language tag, and notice pill
* **Minbar** admin: Overview, Schedule, Planner, Archive, Sermon Builder, and References
* Public widgets: this week's khatib, upcoming khutbahs, searchable archive, and compact search
* Audio / PDF links when you add them to archive entries

**Use case:** Update Friday's khatib in Minbar → Schedule, then show `[masjidos_khatib_this_week]` under your Jumuah widget.

= 📺 TV Display (Mosque Screen) =

* Fullscreen board at `/masjidos-display/`
* Live times, next-prayer countdown, Hijri date, notices, and mosque branding
* Themes, layouts, font size, and logo via Settings → TV Display
* Self-hosted fonts (no Google Fonts request at runtime)

**Use case:** Bookmark the TV URL on a lobby Chromebox or smart TV browser and leave it on all day.

= 📢 Notices, Events & Community =

* Scheduled announcements: list, ticker, banner, and popup designs
* Events with dates, location, images, remaining-days badges, and iCal download
* Islamic calendar with Hijri overlay, highlighted dates, and event markers

= 🤲 Duas, Quran, Hadith & Articles =

* Duas & Azkar with categories, counters, share buttons, and optional custom Duas Library
* Quran verse of the day, Hadith of the day, 99 Names of Allah, and Audio Quran player
* **Islamic Articles** — title, content, featured image, categories, plus language, author, translator, source, takeaway, external URL, and audio fields
* List articles on any page with `[masjidos_articles]`

**Use case:** Publish a Bangla article in Articles → Add New, then show `[masjidos_articles language="bn"]` on an Education page.

= ⚙️ Administration That Feels Friendly =

* Dedicated MasjidOS dashboard (not buried in Settings alone)
* **Welcome** first-run experience with live prayer preview and three clear paths
* **Features** — browse widgets, tweak options, copy shortcodes, open previews
* **Docs** — beginner checklist, paste guides, generators, and attribute reference
* Roles: Imam and Muazzin with capability-based access
* REST API under `masjidos/v1`
* Multisite-aware uninstall

---

**Quick start (about 5 minutes)**

1. Activate MasjidOS and open the Welcome screen (or MasjidOS → Welcome).
2. Choose admin language from the top bar if you prefer Bangla or Arabic menus.
3. Open Prayer Settings: timezone, coordinates, calculation method → Save.
4. Open Features → Prayer Times → Copy shortcode → paste on a page.
5. Optional: open `/masjidos-display/` for the mosque TV.

Need deeper help later? MasjidOS → Docs. Prefer clicking options over memorizing attributes? Use Features or the Docs Generators tab.

== Installation ==

1. Upload the `masjidos` folder to `/wp-content/plugins/`, or install via **Plugins → Add New → Upload Plugin**.
2. Activate **MasjidOS**.
3. Follow the **Welcome** guide, or go to **MasjidOS → Settings** and set timezone, latitude, longitude, calculation method, and Asr method.
4. Paste a shortcode on any page, or add a MasjidOS block in the block editor.

**Most-used shortcodes**

`[masjidos_prayer_times]`
`[masjidos_prayer_times design="compact" language="bn"]`
`[masjidos_monthly_prayer_times]`
`[masjidos_jumuah]`
`[masjidos_khatib_this_week]`
`[masjidos_upcoming_khutbah]`
`[masjidos_khutbah_archive]`
`[masjidos_khutbah_search]`
`[masjidos_islamic_calendar]`
`[masjidos_duas_azkar]`
`[masjidos_announcements design="ticker"]`
`[masjidos_events]`
`[masjidos_articles]`
`[masjidos_quran_verse]`
`[masjidos_hadith]`
`[masjidos_allah_names]`
`[masjidos_audio_quran]`

**Mosque TV URL:** `/masjidos-display/`

Full attribute lists, paste locations (Page, Elementor, widgets), and generators: **MasjidOS → Docs** and **MasjidOS → Features**.

== External Services ==

MasjidOS connects to third-party services only in specific, optional situations:

= Aladhan Prayer Times API =

When prayer source is set to Aladhan / Auto API in Settings, monthly timetables are fetched from api.aladhan.com (city, country, method, and Asr school sent as query parameters). Default remains local offline calculation.
Service: https://aladhan.com | Privacy: https://aladhan.com/privacy

= Quranic Audio (Audio Quran Widget) =

The Audio Quran widget streams Surah recitation files from download.quranicaudio.com directly in the visitor's browser. No visitor data is uploaded from your server to that service.
Service: https://quranicaudio.com

TV Display and admin UI use self-hosted Outfit, Cairo, and Noto Sans Bengali fonts (SIL Open Font License). They do not call Google Fonts at runtime.

== Frequently Asked Questions ==

= I am not technical. Can I still set this up? =

Yes. After activation, the Welcome screen shows a live preview and three clear paths: set prayer times, put a widget on the website, or open the TV board. Features lets you copy shortcodes without memorizing attributes. Docs includes a beginner checklist in plain language.

= Does MasjidOS require an external prayer API? =

No, by default. Prayer times are calculated locally on your server from your coordinates and settings. You may optionally enable Aladhan / Auto API in Settings if you prefer cloud-sourced timetables.

= How do I switch the admin language to Bangla or Arabic? =

Use the language dropdown at the top of any MasjidOS screen, or set it under Settings → Profile. This changes MasjidOS menus and Docs labels only. Article titles, notices, and khutbah topics stay exactly as you typed them.

= What is the difference between admin language and widget language? =

Admin language controls the MasjidOS dashboard. Public widget language controls visitor-facing labels via `language="bn"` (or `en` / `ar`), or follows the admin default when you omit the attribute. Jumuah "khutbah language" is a public label for the sermon (for example "Bangla"), not the UI language.

= How do I match our printed mosque timetable? =

Use Settings → Adjustments to add or subtract minutes per prayer. Wrong timezone is the most common cause — use your real city timezone (for Bangladesh, typically Asia/Dhaka), not UTC.

= How do I show this Friday's khatib on the website? =

Add the khatib and date under Minbar → Schedule, then place `[masjidos_khatib_this_week]` on your Jumuah or homepage. For past sermons, use `[masjidos_khutbah_archive]` and optionally `[masjidos_khutbah_search]`.

= How do I publish Islamic articles? =

Go to Articles → Add New under MasjidOS. Add title, content, featured image, and category. Fill Article Details (language, author, source, takeaway, optional audio). Publish, then use `[masjidos_articles]` on any page.

= How do I open the TV display? =

Visit `/masjidos-display/` on your site. Configure theme, logo, font size, and notice rotation under Settings → TV Display.

= Why is my announcement not showing? =

It must be published; start time must have arrived; end time must be empty or still in the future. Scheduling uses the MasjidOS timezone.

= Does MasjidOS include Gutenberg blocks? =

Yes. Search for "MasjidOS" in the block editor to find the Prayer Times block, Islamic Calendar block, and other registered MasjidOS blocks.

= Is there a free / Pro difference? =

The free plugin includes the full mosque toolkit described here. Some design presets are documented as Pro placeholders and render only when a Pro add-on is active. All free designs always work from this plugin alone.

= What is removed on uninstall? =

Settings, custom roles (Imam, Muazzin), prayer caches, and MasjidOS custom database tables (announcements, events, khutbah archive, khatib profiles, schedules). WordPress post content such as custom Duas and Islamic Articles follows normal WordPress behavior. Back up first if you need to keep that data.

= Is multisite supported? =

Yes. Uninstall runs per site across the entire network.

== Privacy ==

MasjidOS does not ship analytics, telemetry, advertising, or automatic calls to a MasjidOS-owned service. See the External Services section above for optional third-party services.

Public widgets display only what an administrator has explicitly configured: prayer settings, khatib profiles, notices, events, articles, and related content.

== Screenshots ==

1. Welcome — first-run guide with live prayer preview (Madani Masjid)
2. TV Display — fullscreen mosque board with countdown and notices
3. Dashboard — next prayer countdown and mosque overview
4. Features — browse widgets and copy shortcodes
5. Docs — beginner checklist and First 5 minutes guide
6. Prayer Times — public widget with Iqamah and current prayer
7. Jumuah — Friday sessions, khatib profile, and topic
8. Islamic Articles — published article list with Read buttons
9. Duas & Learning — duas, Quran verse, and Hadith widgets
10. Notices & Events — scheduled notices and community events

== Changelog ==

= 1.2.0 =
* **Welcome experience** — first-run screen with live prayer preview, setup pulse, and clear paths to Settings, Features, and TV display.
* **Admin UI language switcher** — English, Bangla, and Arabic for MasjidOS menus with reload-friendly toggle and clearer Docs guidance on admin vs. public vs. content language.
* **Islamic Articles** — richer article details (language, author, translator, source, takeaway, URLs, audio) and public `[masjidos_articles]` list widget.
* **Minbar docs and public Friday widgets** — this week's khatib, upcoming khutbahs, archive, and search, documented for ordinary mosque admins.
* **Docs polish** — First 5 minutes guide, setup checklist updates, Articles and Minbar tabs, and beginner-friendly wording throughout.
* **Self-hosted fonts** — shared Outfit, Cairo, and Noto Sans Bengali fonts for admin and public widgets; no Google Fonts request at runtime.
* Plugin Check and coding standards cleanups for WordPress.org compliance.

= 1.1.0 =
* **Content & Education** — Islamic Articles CPT, Quran Verse of the Day, Hadith of the Day, 99 Names of Allah, and Audio Quran widgets.
* **Jumuah and Events upgrades** — Khutbah Archive with audio player, virtual recurring Friday generation, community event featured images, remaining-days badges, and iCal exports.
* **Announcement styles** — slim Banner layout, session-controlled dismissible Popup Modal, and Ramadan Gold ticker theme (auto-activates during Ramadan).
* **TV Display improvements** — Islamic Geometric SVG patterns, 15-minute auto-reload, and instant refresh on browser reconnect.
* External Services privacy disclosures added to readme.txt.

= 1.0.0 =
* Initial public release on WordPress.org.
* Local prayer-time calculation, per-prayer adjustments, Iqamah times, Hijri date, and Qibla compass.
* Monthly timetable, Jumuah sessions, Duas & Azkar, scheduled announcements, and community events.
* Gutenberg Prayer Times and Islamic Calendar blocks.
* Imam and Muazzin roles with capability-based access.
* Built-in Bangla (Bangladesh) translation pack and Arabic pack.
* REST API under `masjidos/v1`.
* Features page with live shortcode previews and Docs with shortcode generators.

== Upgrade Notice ==

= 1.2.0 =
Welcome screen, admin language switcher (English / Bangla / Arabic), Islamic Articles improvements, Minbar public widget docs, self-hosted fonts, and Docs polish for everyday mosque admins. Recommended update for all users.

= 1.1.0 =
Adds education widgets (Quran Verse, Hadith, Audio Quran, 99 Names), Khutbah Archive, new announcement styles, and TV Display improvements.

= 1.0.0 =
Initial public release of MasjidOS.