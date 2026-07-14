=== MasjidOS ===
Contributors: itsmanzur
Tags: prayer times, mosque, islamic calendar, quran, hijri
Requires at least: 6.0
Tested up to: 7.0
Stable tag: 1.1.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Complete mosque management plugin: prayer times, Jumuah, Islamic calendar, Duas & Azkar, Quran, Hadith, events, announcements, TV display, and more.

== Description ==

**MasjidOS** is an all-in-one mosque management plugin for WordPress. It gives your mosque a dedicated admin dashboard and a full set of public shortcodes — covering everything from daily prayer times to a fullscreen TV display, Duas & Azkar, Quran verses, Hadith of the Day, Islamic calendar, community events, and scheduled announcements.

Designed for mosques of all sizes, MasjidOS performs prayer time calculations **locally on your server** — no external prayer API required by default. Everything runs offline, with no telemetry or visitor tracking.

---

**🕌 Prayer Times**

* Local, offline prayer-time calculation using saved coordinates, timezone, and calculation method
* Optional Aladhan.com API integration for cloud-sourced monthly timetables
* Supports all major calculation methods (Karachi, ISNA, MWL, Egypt, Makkah, Tehran, Kuwait, Qatar, Singapore, and more)
* Hanafi and standard Asr methods
* Per-prayer minute adjustments to match your local mosque timetable
* Iqamah (Jamaat) times display
* Qibla direction with live browser compass API
* Hijri date display with adjustable offset for local moon-sighting calendars
* Responsive widgets in English, Bangla, and Arabic

**📅 Monthly Timetable**

* Full month prayer timetable with Hijri date column
* Month navigation, current-month return button, and print support
* Compact and classic layout designs

**🗓️ Islamic Calendar**

* Gregorian calendar with Hijri dates overlaid on every day
* Highlights important Islamic dates (Ramadan, Eid, Laylat al-Qadr, and more)
* Shows mosque community events as calendar markers
* Supports Bangla and English languages
* Available as a shortcode and as a Gutenberg block

**📺 TV Display (Fullscreen Mosque Screen)**

* Dedicated fullscreen display at `/masjidos-display/`
* Shows live prayer times, upcoming prayer countdown, Hijri date, announcements ticker, and mosque name
* Multiple themes (Dark, Light, Green)
* Configurable font size and announcement scroll speed
* Custom logo support

**🤲 Duas & Azkar**

* Built-in collection of authentic Duas and Azkar with Arabic text, transliteration, and Bangla/English meanings
* Source labels (Quran reference or Hadith source) for every dua
* Filter by category: Morning, Evening, Food, Sleep, Home, Masjid, Travel, Rain, Forgiveness, Quran, Protection
* Local recitation counter stored in the visitor's browser
* Share button for each dua
* Custom Duas Library: add your own Duas from the admin and assign categories

**📖 Quran & Hadith**

* Daily Quran verse shortcode with Arabic, English, and Bangla translation
* Daily Hadith widget with source reference
* Audio Quran shortcode — play any Surah directly on the page (streamed from QuranicAudio.com)
* 99 Names of Allah shortcode with Arabic, transliteration, and meaning

**🕋 Jumuah Management**

* Multiple Jumuah sessions per Friday (first Jumuah, second Jumuah)
* Khatib profile with name, photo, and bio
* Khutbah topic, language, and special notice fields
* Recurring auto-creation of Friday Jumuah entries
* Jumuah Khutbah Archive with audio player and date filtering

**📢 Mosque Announcements**

* Scheduled notices with start and end dates/times
* Display styles: Ticker, Banner, List, and Popup Modal
* Priority ordering for notices
* Ramadan gold ticker theme auto-activates during Ramadan

**📆 Community Events**

* Upcoming events list with title, date, location, description, and featured image
* "Remaining days" badge for each event
* iCal download so visitors can add events to their personal calendars
* Islamic recurring events (Eid, Ramadan, Milad, Muharram) generated automatically with Hijri date conversion

**⚙️ Administration**

* Dedicated MasjidOS dashboard in the WordPress admin
* Role-based access: Imam and Muazzin roles with tailored capabilities
* REST API under `masjidos/v1` with capability-based permission checks
* Built-in Features page with live shortcode previews and a shortcode generator
* Full shortcode documentation under MasjidOS > Docs
* Multisite compatible (uninstall cleans all sites)

**🌐 Multilingual**

* Translation-ready (.pot file included)
* Built-in Bangla (Bangladesh) translation
* Shortcode `language` attribute supports `en`, `bn`, and `ar`

---

== Installation ==

1. Upload the `masjidos` folder to `/wp-content/plugins/`, or install the plugin ZIP via **Plugins > Add New > Upload Plugin**.
2. Activate **MasjidOS**.
3. Go to **MasjidOS > Settings** and enter your mosque's timezone, latitude, longitude, calculation method, and Asr method.
4. Add any shortcode to a WordPress page or post.

**Common shortcodes:**

`[masjidos_prayer_times]`
`[masjidos_prayer_times design="compact"]`
`[masjidos_jumuah]`
`[masjidos_monthly_prayer_times]`
`[masjidos_islamic_calendar]`
`[masjidos_islamic_calendar language="bn"]`
`[masjidos_duas_azkar]`
`[masjidos_duas_azkar category="morning" counter="yes" share="yes"]`
`[masjidos_announcements design="ticker"]`
`[masjidos_announcements design="popup"]`
`[masjidos_events]`
`[masjidos_khutbah_archive]`
`[masjidos_quran_verse]`
`[masjidos_hadith]`
`[masjidos_allah_names]`
`[masjidos_audio_quran]`

**Fullscreen TV display:**
Open `/masjidos-display/` in your browser.

The complete shortcode reference, live previews, and a shortcode generator are available under **MasjidOS > Docs** and **MasjidOS > Features** in your WordPress admin.

== External Services ==

MasjidOS connects to the following third-party services in specific situations:

= Aladhan Prayer Times API =
When the prayer source is set to "Aladhan" in Settings, the plugin fetches
monthly prayer timetables from api.aladhan.com. The city, country, calculation
method, and Asr school are sent as query parameters. This setting defaults to
"Local" (offline calculation); the API is only contacted when you explicitly
choose "Aladhan" as the source.
Service: https://aladhan.com | Privacy: https://aladhan.com/privacy

= Google Fonts (TV Display) =
The TV Display page loads Cairo, Outfit, and Noto Sans Bengali fonts from
Google Fonts CDN (fonts.googleapis.com). This request is made in the visitor's
browser when the TV Display page is opened.
Service: https://fonts.google.com | Privacy: https://policies.google.com/privacy

= Quranic Audio (Audio Quran widget) =
The Audio Quran widget generates playback URLs pointing to
download.quranicaudio.com. The audio file is streamed directly in the
visitor's browser; no data is sent from your server.
Service: https://quranicaudio.com

== Frequently Asked Questions ==

= Does MasjidOS use an external prayer-time API? =

By default, no. Prayer times are calculated locally from saved coordinates,
timezone, and calculation settings. Optionally, you can switch the prayer
source to "Aladhan" in Settings, which fetches timetables from api.aladhan.com.
See the External Services section above for details.

= How do I find latitude and longitude for my mosque? =

Open Google Maps or any map service, right-click on the mosque location, and
copy the coordinates. MasjidOS includes this guidance directly on the Settings
screen.

= Why do local mosque times differ by a few minutes from MasjidOS? =

Calculation conventions and officially published timetables can differ slightly.
Use Prayer Time Adjustments under Settings to add or subtract minutes per prayer
to match your mosque's published timetable exactly.

= Can I adjust the Hijri date? =

Yes. Go to Settings > Calculation > Hijri Date Adjustment. Enter +1 or -1 days
if your local moon-sighting calendar differs from the calculated Hijri date.

= How do I display the Islamic calendar? =

Add `[masjidos_islamic_calendar]` to any page. It shows Gregorian days with
Hijri dates alongside, highlights important Islamic dates, and marks community
events on the calendar. A Gutenberg block version is also available.

= How do I open the TV display? =

Open `/masjidos-display/` on your WordPress site. Configure the theme, logo,
font size, and announcement speed under MasjidOS > Settings > TV Display.

= How do I show Duas and Azkar? =

Add `[masjidos_duas_azkar]` to any page. Filter the built-in collection with
`category="morning"`, `category="evening"`, `category="food"`, `category="sleep"`,
`category="home"`, `category="masjid"`, `category="travel"`, `category="rain"`,
`category="forgiveness"`, `category="quran"`, or `category="protection"`.
Recitation counters are stored in the visitor's browser.

= Can I add my own custom Duas? =

Yes. Open MasjidOS > Duas Library in the admin, add a new dua, assign one or
more Dua Categories, and publish it. Your custom duas appear in
`[masjidos_duas_azkar]` alongside the built-in collection.

= How does the Audio Quran widget work? =

Add `[masjidos_audio_quran]` to a page. A Surah selector and an audio player
appear. Selecting a Surah streams the recitation by Sheikh Mishary Alafasy
directly from QuranicAudio.com in the visitor's browser.

= How do community event iCal downloads work? =

Each event in `[masjidos_events]` includes a download button. Clicking it
downloads a standard .ics file that visitors can add to Google Calendar,
Apple Calendar, Outlook, or any calendar app.

= Why is an announcement not showing? =

The announcement must be published, its start date/time must have passed, and
its end date/time must be blank or in the future. Scheduling uses the timezone
configured in MasjidOS > Settings.

= Does MasjidOS include Gutenberg blocks? =

Yes. In the WordPress block editor, search for "MasjidOS" to find the Prayer
Times block and the Islamic Calendar block.

= What is removed when I delete the plugin? =

Deleting MasjidOS through WordPress removes its settings, custom roles (Imam,
Muazzin), cached prayer calculations, and custom database tables
(announcements, events, khutbah archive). Content stored as WordPress posts,
such as custom Duas and Islamic Articles, follows WordPress content behavior.
Back up any data you need before deleting.

= Is MasjidOS compatible with multisite? =

Yes. The uninstall routine runs per-site and cleans data from every site in the
network.

== Privacy ==

MasjidOS does not include analytics, telemetry, advertising, or any automatic
requests to a MasjidOS-owned service. See the External Services section above
for third-party services that may be contacted under specific conditions.

Public widgets display only information that an administrator has explicitly
configured: prayer settings, Khatib profiles, announcements, and events.

== Changelog ==

= 1.1.0 =
* Content & Education module: Islamic Articles post type, Quran Verse, Hadith of the Day, 99 Names of Allah, and Audio Quran widgets.
* Jumuah and Events upgrades: Khutbah Archive with audio, recurring Friday Jumuah generation, featured event images, remaining-days badges, and iCal exports.
* Announcement styles: Banner and Popup Modal layouts, plus Ramadan ticker styling.
* TV Display improvements: geometric background patterns and faster reconnect/refresh behavior.
* Shortcodes and docs: added [itmms_calendar] as an Islamic Calendar alias and expanded admin documentation.
* Privacy documentation: added External Services disclosures for optional third-party services.

= 1.0.0 =
* Initial public release on WordPress.org.
* Prayer Times with local offline calculation, adjustments, Iqamah times, Hijri date, and Qibla compass.
* Monthly Timetable with month navigation and print support.
* Jumuah schedule widget with Khatib profile and public shortcode.
* Announcements and Events modules for mosque communication.
* Duas & Azkar widget with counters and sharing.
* Gutenberg blocks for Prayer Times and Islamic Calendar.
* Imam and Muazzin roles with capability-based admin access.
* Built-in Bangla (Bangladesh) translation and translation-ready POT file.
* REST API under masjidos/v1 with capability-based permission checks.
* Features page with live shortcode previews and shortcode generator.
* Full shortcode documentation under MasjidOS > Docs.

== Upgrade Notice ==

= 1.1.0 =
This update adds the Content & Education module, Jumuah sermon archive, announcements styles, TV geometric templates, and major bug fixes. Highly recommended for all mosques.

= 1.0.0 =
Initial public release of MasjidOS.
