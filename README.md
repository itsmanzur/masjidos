# MasjidOS

All-in-one mosque management for WordPress: prayer times, Jumuah & Minbar, Duas, Quran, Islamic calendar, TV display, events, and notices.

**Version:** 1.2.1 · **Requires:** WordPress 6.2+, PHP 7.4+ · **License:** GPL-2.0-or-later

## Features

- Local prayer-time calculation (optional Aladhan API), Iqamah, Qibla, Hijri adjust, monthly timetable
- Jumuah sessions, Minbar (schedule / planner / archive / sermon builder), public khatib widgets
- Fullscreen mosque TV at `/masjidos-display/` (self-hosted fonts)
- Notices: list, ticker, banner, popup
- Events with remaining-days badges and iCal export
- Duas & Azkar, Quran verse, Hadith, 99 Names, Audio Quran, Islamic Articles
- Welcome guide, Features previews, Docs + shortcode generators
- Admin UI in English, Bangla, and Arabic (RTL)
- Imam / Muazzin roles; REST API under `masjidos/v1`

## Shortcodes

- `[masjidos_prayer_times]`
- `[masjidos_monthly_prayer_times]`
- `[masjidos_jumuah]`
- `[masjidos_khatib_this_week]`
- `[masjidos_upcoming_khutbah]`
- `[masjidos_khutbah_archive]`
- `[masjidos_khutbah_search]`
- `[masjidos_islamic_calendar]`
- `[masjidos_duas_azkar]`
- `[masjidos_announcements]`
- `[masjidos_events]`
- `[masjidos_articles]`
- `[masjidos_quran_verse]`
- `[masjidos_hadith]`
- `[masjidos_allah_names]`
- `[masjidos_audio_quran]`

**Mosque TV URL:** `/masjidos-display/`

Most shortcodes accept `language="en|bn|ar"` and a free `design` attribute. Full attribute lists and generators: **MasjidOS → Docs** and **MasjidOS → Features**.

## Quick start

1. Activate MasjidOS and open the Welcome screen.
2. Set timezone, coordinates, and calculation method under Settings.
3. Copy a shortcode from Features (or use a Gutenberg block) and paste on a page.
4. Optional: open `/masjidos-display/` on a lobby TV.

## Requirements

- WordPress 6.2 or later
- PHP 7.4 or later

## License

GPL-2.0-or-later

WordPress.org listing details, FAQ, and changelog live in [`readme.txt`](readme.txt).
