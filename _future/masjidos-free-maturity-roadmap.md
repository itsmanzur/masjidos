# MasjidOS Free — Long-Term Maturity & Enhancement Roadmap

Last updated: October 2026

This document records the master plan and feature roadmap to elevate MasjidOS Free into an industry-leading, dependable, and developer-friendly mosque management suite for WordPress.

---

## Pillar 1: Core Engine & Prayer Timetable Maturity

### 1.1 Manual Timetable Override (CSV / Excel Import & Export)
- Support mosques that adhere to government/Islamic Foundation perpetual timetables (চিরস্থায়ী ক্যালেন্ডার).
- 365-day custom timetable storage in database (`wp_itmms_prayer_timetable`).
- One-click CSV/Excel import and export template with field mapping (Fajr, Sunrise, Dhuhr, Asr, Maghrib, Isha, Sehri, Iftar).
- Visual administrative table grid for direct inline edits with fallback to calculation engine.

### 1.2 Smart Automated Iqamah Scheduling Rules
- Rule-based dynamic Iqamah calculations:
  - Fixed time (e.g. 5:15 AM)
  - Relative offset after Azan (e.g. 15 minutes after Fajr Azan)
  - Bi-monthly / Seasonal table (changes on 1st and 15th of every month automatically)
- Jumuah multiple session automation.

### 1.3 Ramadan Special Engine
- Auto-detection of Ramadan period based on Hijri calendar setting.
- Live Sehri and Iftar countdown banner / widget.
- 30-day Ramadan Timetable shortcode and print-ready PDF/A4 view.
- Taraweeh, Khatm-ul-Quran, and I'tikaf registration notices.

---

## Pillar 2: Modern WordPress Ecosystem & Page Builders Integration

### 2.1 Native Gutenberg Blocks Upgrade
- Rich Inspector Controls with live preview inside the block editor:
  - Design selector (Classic, Compact, Table, Modern Card)
  - Color & typography controls
  - Toggle visibility for Hijri date, Iqamah, Qibla, Sunrise/Zawal
- Block themes / Full Site Editing (FSE) compatibility.

### 2.2 Dedicated Elementor Addons / Widgets
- Native Elementor integration (conditional registration when Elementor is active):
  - `MasjidOS Prayer Times Card`
  - `MasjidOS Jumuah Card`
  - `MasjidOS Monthly Timetable`
  - `MasjidOS Notice Board / Ticker`
  - `MasjidOS Islamic Calendar`
  - `MasjidOS Ask the Imam Form & Library`
- Custom style tabs in Elementor with typography, colors, borders, and responsive controls.

### 2.3 Theme Template Override Hierarchy (Developer-friendly)
- WooCommerce-style template hierarchy:
  - Check `get_stylesheet_directory() . '/masjidos/templates/'`
  - Check `get_template_directory() . '/masjidos/templates/'`
  - Fallback to plugin `masjidos/public/templates/`
- Allows theme developers and agency builders to fully customize HTML markup without modifying core plugin files.

---

## Pillar 3: Smart TV Display & Digital Signage

### 3.1 Vertical / Portrait Kiosk Display Mode
- Dedicated layout for 9:16 vertical standing screens located at mosque entrance doors.
- Large legible typography optimized for 4K / 1080p kiosk screens.

### 3.2 Pre-Salah Jamah Countdown & Phone Silence Screen
- Automated transition 5-10 minutes prior to Iqamah time:
  - Bold high-contrast countdown timer: "Iqamah in 04:30"
  - Clear multi-lingual "Please Turn Off or Silence Your Mobile Phones" warning animation.
- Blackout / Dim mode during active prayer duration to avoid visual distraction in prayer hall.

### 3.3 Custom Mosque Branding & Weather on TV
- Display custom mosque logo, title, and current weather forecast alongside prayer times.

---

## Pillar 4: Community & Mosque Daily Utilities

### 4.1 Janazah (Funeral) Notice Board
- Dedicated Janazah announcement format:
  - Marhum/Marhuma name and details
  - Janazah prayer time & mosque location
  - Burial / Graveyard location with direct Google Maps link
  - Public widget and urgent ticker banner.

### 4.2 Jumuah Khutbah & Video Archive Upgrades
- YouTube / Vimeo / Audio recording embed support in Khutbah Archive.
- Khatib biography card with upcoming schedule.

---

## Pillar 5: Technical Architecture, Performance & Dev-Ops

### 5.1 Public Trait Split & Shortcode Refactoring
- Split monolithic `class-itmms-public.php` render methods into focused traits:
  - `ITMMS_Public_Render_Prayer`
  - `ITMMS_Public_Render_Content`
  - `ITMMS_Public_Render_Minbar`
  - `ITMMS_Public_Render_Education`

### 5.2 1-Minute Smart Onboarding Wizard
- City / Location search using OpenStreetMap geocoding.
- Auto-configuration of latitude, longitude, timezone, and regional calculation method (e.g. Islamic Foundation Bangladesh, Karachi, MWL, ISNA).

### 5.3 System Health & Diagnostics Screen
- Diagnostic dashboard verifying WP Cron health, server time vs timezone sync, REST endpoints availability, and font cache status.
