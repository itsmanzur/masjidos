=== MasjidOS ===
Contributors: itsmanzur
Tags: prayer times, mosque, islamic, jumuah, qibla
Requires at least: 6.0
Tested up to: 7.0
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Prayer times, Jumuah schedules, monthly timetables, Qibla direction, mosque notices, and community events without an external prayer API.

== Description ==

MasjidOS gives a mosque a focused WordPress dashboard and public shortcodes for its most frequently needed information.

Current features include:

* Local prayer-time calculation with configurable coordinates and timezone.
* Multiple calculation methods, Hanafi or standard Asr, and per-prayer adjustments.
* Iqamah times and Qibla direction.
* Responsive prayer-time widgets in English, Bangla, or Arabic.
* Jumuah sessions, Khatib profile, topic, language, and notice.
* Monthly prayer timetables with month navigation and print support.
* Scheduled mosque announcements with list and ticker designs.
* Upcoming community events calendar with time, location, and description details.
* Built-in shortcode documentation and generators.

MasjidOS performs prayer calculations locally. It does not require an external prayer-time API and does not send telemetry or visitor data to MasjidOS.

== Installation ==

1. Upload the `masjidos` folder to `/wp-content/plugins/`, or install the plugin ZIP from Plugins > Add New.
2. Activate MasjidOS.
3. Open MasjidOS > Settings and configure the mosque timezone, latitude, longitude, calculation method, and Asr method.
4. Add a shortcode to a WordPress page.

Common shortcodes:

* `[masjidos_prayer_times]`
* `[masjidos_prayer_times design="compact"]`
* `[masjidos_jumuah]`
* `[masjidos_monthly_prayer_times]`
* `[masjidos_announcements]`
* `[masjidos_announcements design="ticker"]`
* `[masjidos_events]`

The complete shortcode reference and generators are available under MasjidOS > Docs.

== Frequently Asked Questions ==

= Does MasjidOS use an external prayer-time API? =

No. Prayer times and Qibla direction are calculated locally from the saved coordinates, timezone, and calculation settings.

= How do I find latitude and longitude? =

Open a map service, select the mosque location, and copy its latitude and longitude. MasjidOS includes this guidance on the Settings screen.

= Why can local mosque times differ by a few minutes? =

Calculation conventions and official local timetables can differ. Use Prayer Time Adjustments to match the mosque's published timetable.

= Why is an announcement not visible? =

The announcement must be published, its start time must have arrived, and its end time must be blank or in the future. Scheduling uses the MasjidOS timezone.

= What happens when I delete the plugin? =

Deleting MasjidOS through WordPress removes its settings, custom roles, cached prayer calculations, and custom database tables. Export or back up information you need before deletion.

== Privacy ==

MasjidOS does not include analytics, telemetry, advertising, or automatic requests to a MasjidOS service. The Google Maps link in Settings opens only when an administrator chooses it.

Public widgets can display mosque location details, prayer settings, Khatib information, and announcements that an administrator has configured for public display.

== Changelog ==

= 1.0.0 =

* Initial public release on WordPress.org.
* Local prayer-time calculation widget, monthly timetable widget, and Qibla compass.
* Jumuah sessions scheduler and Khatib profiles display.
* Scheduled notices list and notice ticker modules.
* Upcoming community events list module.
* Translation-ready with built-in Bangla (Bangladesh) translation pack.
* Role capabilities aligned for Imam and Muazzin admin access.

== Upgrade Notice ==

= 1.0.0 =

Initial public release of MasjidOS.
