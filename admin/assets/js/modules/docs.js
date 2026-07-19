/**
 * MasjidOS Admin - Documentation and Generator Module.
 */
( function () {
	'use strict';

	window.itmms = window.itmms || {};
	var __ = window.wp.i18n.__;
	var sprintf = window.wp.i18n.sprintf;

	// Import shared helpers
	var esc = window.itmms.esc;

	// Expose docs module
	window.itmms.docs = {
		docsHtml: docsHtml
	};

	// Also expose activeModuleCount globally for the dashboard
	window.itmms.activeModuleCount = activeModuleCount;

	function docsHtml() {
		return '<div class="itmms-docs">' +
			docsHero() +
			'<div class="itmms-doc-tabs" role="tablist" aria-label="' + esc( __( 'Documentation sections', 'masjidos' ) ) + '">' +
				'<div class="itmms-doc-tabs__cluster">' +
					docTabButton( 'overview', __( 'Overview', 'masjidos' ), true ) +
					docTabButton( 'generators', __( 'Generators', 'masjidos' ), false ) +
				'</div>' +
				'<div class="itmms-doc-tabs__cluster" data-label="' + esc( __( 'Widgets', 'masjidos' ) ) + '">' +
					docTabButton( 'prayer', __( 'Prayer', 'masjidos' ), false ) +
					docTabButton( 'jumuah', __( 'Jumuah', 'masjidos' ), false ) +
					docTabButton( 'minbar', __( 'Minbar', 'masjidos' ), false ) +
					docTabButton( 'calendar', __( 'Calendar', 'masjidos' ), false ) +
					docTabButton( 'duas', __( 'Duas', 'masjidos' ), false ) +
					docTabButton( 'notices', __( 'Notices', 'masjidos' ), false ) +
					docTabButton( 'events', __( 'Events', 'masjidos' ), false ) +
					docTabButton( 'articles', __( 'Articles', 'masjidos' ), false ) +
					docTabButton( 'education', __( 'Quran & Hadith', 'masjidos' ), false ) +
				'</div>' +
				'<div class="itmms-doc-tabs__cluster">' +
					docTabButton( 'pro', __( 'Pro', 'masjidos' ), false ) +
					docTabButton( 'reference', __( 'Reference', 'masjidos' ), false ) +
				'</div>' +
			'</div>' +
			'<div class="itmms-doc-tab-panels">' +
				docPanel( 'overview', true, firstStepsSection() + pasteShortcodeSection() + languageGuideSection() + displayAndBlocksSection() + jumuahDataSection() + checklistSection() + beginnerTipsSection() ) +
				docPanel( 'generators', false, shortcodeBuilderHtml() ) +
				docPanel( 'prayer', false, prayerDocsSection() ) +
				docPanel( 'jumuah', false, jumuahDocsSection() ) +
				docPanel( 'minbar', false, minbarDocsSection() ) +
				docPanel( 'calendar', false, calendarDocsSection() ) +
				docPanel( 'duas', false, duasDocsSection() ) +
				docPanel( 'notices', false, announcementDocsSection() ) +
				docPanel( 'events', false, eventDocsSection() ) +
				docPanel( 'articles', false, articlesHowToSection() + articlesDocsSection() ) +
				docPanel( 'education', false, educationDocsSection() ) +
				docPanel( 'pro', false, proDocsSection() ) +
				docPanel( 'reference', false, prayerAttributesSection() + jumuahAttributesSection() + monthlyAttributesSection() + calendarAttributesSection() + duasAttributesSection() + announcementAttributesSection() + eventAttributesSection() + articlesAttributesSection() + khutbahArchiveAttributesSection() + quranVerseAttributesSection() + hadithAttributesSection() + allahNamesAttributesSection() + audioQuranAttributesSection() + tvDisplayReferenceSection() ) +
			'</div>' +
		'</div>';
	}

	function docsHero() {
		return '<div class="itmms-docs-hero">' +
			'<div>' +
				'<span>' + esc( __( 'Documentation', 'masjidos' ) ) + '</span>' +
				'<h2>' + esc( __( 'Get masjid widgets live in minutes', 'masjidos' ) ) + '</h2>' +
				'<p>' + esc( __( 'Follow Overview once, generate shortcodes, or open Features for live previews. Docs stay as your attribute reference.', 'masjidos' ) ) + '</p>' +
			'</div>' +
			'<div class="itmms-docs-hero__actions">' +
				'<button type="button" class="itmms-btn itmms-docs-hero__btn" data-open-welcome>' + esc( __( 'Welcome', 'masjidos' ) ) + '</button>' +
				'<button type="button" class="itmms-btn itmms-btn-primary itmms-docs-hero__btn itmms-docs-hero__btn--primary" data-open-settings>' + esc( __( 'Prayer Settings', 'masjidos' ) ) + '</button>' +
			'</div>' +
		'</div>';
	}

	function docTabButton( key, label, active ) {
		return '<button type="button" class="itmms-doc-tab' + ( active ? ' active' : '' ) + '" data-doc-tab="' + esc( key ) + '" role="tab" aria-selected="' + ( active ? 'true' : 'false' ) + '">' + esc( label ) + '</button>';
	}

	function docPanel( key, active, content ) {
		return '<div class="itmms-doc-tab-panel' + ( active ? ' active' : '' ) + '" data-doc-panel="' + esc( key ) + '">' + content + '</div>';
	}

	function firstStepsSection() {
		return '<section class="itmms-docs-section itmms-docs-paste">' +
			'<h3>' + esc( __( 'First 5 minutes (start here)', 'masjidos' ) ) + '</h3>' +
			'<p class="itmms-docs-note">' + esc( __( 'You do not need to read every tab. Follow these steps once, then open Features to preview widgets.', 'masjidos' ) ) + '</p>' +
			'<p class="itmms-docs-note"><button type="button" class="itmms-btn itmms-btn-primary" data-open-welcome>' + esc( __( 'Open Welcome guide', 'masjidos' ) ) + '</button></p>' +
			'<div class="itmms-paste-grid">' +
				pasteItem( '1. ' + __( 'Admin language', 'masjidos' ), __( 'Use the language dropdown at the top of MasjidOS (English / Bangla / Arabic). This only changes the admin screens.', 'masjidos' ) ) +
				pasteItem( '2. ' + __( 'Prayer Settings', 'masjidos' ), __( 'Click Prayer Settings. Set timezone, city coordinates, calculation method, then save.', 'masjidos' ) ) +
				pasteItem( '3. ' + __( 'Preview in Features', 'masjidos' ), __( 'Open Features, pick Prayer Times, copy the shortcode, and paste it on a WordPress page.', 'masjidos' ) ) +
				pasteItem( '4. ' + __( 'Optional extras', 'masjidos' ), __( 'Add Jumuah details, notices, articles, or Minbar schedule only when you need them.', 'masjidos' ) ) +
			'</div>' +
		'</section>';
	}

	function pasteShortcodeSection() {
		return '<section class="itmms-docs-section itmms-docs-paste">' +
			'<h3>' + esc( __( 'Where to paste shortcodes', 'masjidos' ) ) + '</h3>' +
			'<div class="itmms-paste-grid">' +
				pasteItem( __( 'WordPress Page', 'masjidos' ), __( 'Pages > Add New, add a Shortcode block, paste the shortcode, then publish.', 'masjidos' ) ) +
				pasteItem( __( 'Post or Article', 'masjidos' ), __( 'Posts > Add New, place the shortcode where the prayer widget should appear.', 'masjidos' ) ) +
				pasteItem( __( 'Widget Area', 'masjidos' ), __( 'Appearance > Widgets, add a Shortcode block in a sidebar or footer area.', 'masjidos' ) ) +
				pasteItem( 'Elementor', __( 'Drag the Shortcode widget into your layout and paste the shortcode there.', 'masjidos' ) ) +
				pasteItem( __( 'Block Editor', 'masjidos' ), __( 'Search for MasjidOS blocks when you prefer a block instead of manually pasting a shortcode.', 'masjidos' ) ) +
			'</div>' +
		'</section>';
	}

	function languageGuideSection() {
		return '<section class="itmms-docs-section itmms-docs-paste">' +
			'<h3>' + esc( __( 'Two kinds of language (important)', 'masjidos' ) ) + '</h3>' +
			'<div class="itmms-paste-grid">' +
				pasteItem( __( 'Admin language', 'masjidos' ), __( 'Top bar language switcher or Settings > Profile. Changes MasjidOS menus, buttons, and Docs labels only.', 'masjidos' ) ) +
				pasteItem( __( 'Public widget language', 'masjidos' ), __( 'Shortcode language="bn" (or en/ar), or leave empty to follow the admin language default. Changes visitor-facing labels on the website.', 'masjidos' ) ) +
				pasteItem( __( 'Your own content', 'masjidos' ), __( 'Article titles, notice text, khutbah topics, and khatib names are never auto-translated. Write them in the language your community reads.', 'masjidos' ) ) +
				pasteItem( __( 'Jumuah language tag', 'masjidos' ), __( 'Settings > Jumuah Settings > Khutbah Language is a public label (for example Bangla sermon), not the admin UI language.', 'masjidos' ) ) +
			'</div>' +
		'</section>';
	}

	function featureDiscoverySection() {
		return '<section class="itmms-docs-section itmms-docs-paste">' +
			'<h3>' + esc( __( 'Features vs Docs', 'masjidos' ) ) + '</h3>' +
			'<div class="itmms-paste-grid">' +
				pasteItem( __( 'Features (start here)', 'masjidos' ), __( 'Browse widgets, customize options, copy shortcodes, and open live preview. Best for day-to-day setup.', 'masjidos' ) ) +
				pasteItem( __( 'Docs (reference)', 'masjidos' ), __( 'Attribute lists, paste locations, checklists, and generators when you need deeper help.', 'masjidos' ) ) +
				pasteItem( __( 'Generators tab', 'masjidos' ), __( 'Form-based shortcode builders if you prefer typed options over the Features cards.', 'masjidos' ) ) +
				pasteItem( __( 'Pro-safe Designs', 'masjidos' ), __( 'Generators only offer free designs. Pro design keys live under the Pro tab and render only when MasjidOS Pro is active.', 'masjidos' ) ) +
			'</div>' +
		'</section>';
	}

	function displayAndBlocksSection() {
		return '<section class="itmms-docs-section itmms-docs-paste">' +
			'<h3>' + esc( __( 'Display modes and blocks', 'masjidos' ) ) + '</h3>' +
			'<div class="itmms-paste-grid">' +
				pasteItem( __( 'TV Display', 'masjidos' ), __( 'Open /masjidos-display/ on the site to show a fullscreen mosque board with prayer times, Iqamah, Hijri date, Jumuah, and notices.', 'masjidos' ) ) +
				pasteItem( __( 'TV Settings', 'masjidos' ), __( 'Go to Settings > TV Display to choose theme, logo, font size, and announcement rotation speed.', 'masjidos' ) ) +
				pasteItem( __( 'Prayer Block', 'masjidos' ), __( 'In the block editor, add the MasjidOS Prayer Times block when you want visual controls instead of shortcode text.', 'masjidos' ) ) +
				pasteItem( __( 'Calendar Block', 'masjidos' ), __( 'In the block editor, add the MasjidOS Islamic Calendar block for a Hijri + Gregorian calendar section.', 'masjidos' ) ) +
			'</div>' +
		'</section>';
	}

	function jumuahDataSection() {
		return '<section class="itmms-docs-section itmms-docs-paste">' +
			'<h3>' + esc( __( 'Where to set Jumuah data', 'masjidos' ) ) + '</h3>' +
			'<div class="itmms-paste-grid">' +
				pasteItem( __( 'Jumuah Sessions', 'masjidos' ), __( 'Go to Settings > Jumuah Settings. Add First Jumuah and optional Second Jumuah times.', 'masjidos' ) ) +
				pasteItem( __( 'Khatib Profile', 'masjidos' ), __( 'Add khatib name, short bio, and use Select Photo to choose an image from WordPress Media Library.', 'masjidos' ) ) +
				pasteItem( __( 'Topic & Language', 'masjidos' ), __( 'Use Khutbah Topic and Khutbah Language to show the Friday talk details publicly.', 'masjidos' ) ) +
				pasteItem( __( 'Notice Pill', 'masjidos' ), __( 'Use Jumuah Notice for arrival notes, parking notes, or special Friday announcements.', 'masjidos' ) ) +
			'</div>' +
		'</section>';
	}

	function prayerDocsSection() {
		return '<div class="itmms-docs-grid">' +
			docCard( __( 'Prayer Times Widget', 'masjidos' ), __( 'Best default for a public prayer times page or homepage section.', 'masjidos' ), '[masjidos_prayer_times]', [
				__( 'Shows today\'s prayer times', 'masjidos' ),
				__( 'Highlights the current prayer', 'masjidos' ),
				__( 'Shows next prayer countdown', 'masjidos' ),
				__( 'Includes Qibla direction and calculation meta', 'masjidos' )
			] ) +
			docCard( __( 'Monthly Timetable', 'masjidos' ), __( 'Show the full prayer timetable for the current month.', 'masjidos' ), '[masjidos_monthly_prayer_times]', [
				__( 'Calculates every day locally', 'masjidos' ),
				__( 'Uses your saved location, method, offsets, and timezone', 'masjidos' ),
				__( 'Good for dedicated timetable pages', 'masjidos' ),
				__( 'Visitors can switch month and year without reloading the page', 'masjidos' ),
				__( 'Includes Current Month and timetable-only Print actions', 'masjidos' )
			] ) +
			docInfoCard( __( 'Calculation Method', 'masjidos' ), __( 'Choose a local calculation preset or Auto API from Settings > Calculation.', 'masjidos' ), [
				__( 'Includes Karachi, MWL, ISNA, Egypt, Makkah, Gulf, Singapore, Tehran, and Jafari presets', 'masjidos' ),
				__( 'Supports Auto API (Aladhan.com) integration for city-based automatic timing retrieval with robust offline fallback', 'masjidos' ),
				__( 'Bangladesh often starts with Karachi + Hanafi', 'masjidos' ),
				__( 'Use minute adjustments to match your official masjid timetable', 'masjidos' )
			] ) +
			docCard( __( 'Specific Month', 'masjidos' ), __( 'Show a fixed month and year timetable.', 'masjidos' ), '[masjidos_monthly_prayer_times month="6" year="2026"]', [
				__( 'Useful for archive pages', 'masjidos' ),
				__( 'Month uses 1 to 12', 'masjidos' )
			] ) +
			docCard( __( 'Monthly Table Design', 'masjidos' ), __( 'Explicitly use the free table design.', 'masjidos' ), '[masjidos_monthly_prayer_times design="table"]', [
				__( 'Included in the free plugin', 'masjidos' ),
				__( 'Pro can add more monthly designs without changing this shortcode system', 'masjidos' )
			] ) +
			docCard( __( 'Monthly Compact Design', 'masjidos' ), __( 'Show the month as day cards instead of a wide table.', 'masjidos' ), '[masjidos_monthly_prayer_times design="compact"]', [
				__( 'Included in the free plugin', 'masjidos' ),
				__( 'Good for narrower content areas', 'masjidos' ),
				__( 'Highlights the current day', 'masjidos' )
			] ) +
			docCard( __( 'Classic Design', 'masjidos' ), __( 'Free design preset for regular content width pages.', 'masjidos' ), '[masjidos_prayer_times design="classic"]', [
				__( 'Included in the free plugin', 'masjidos' ),
				__( 'Good for pages and homepage sections', 'masjidos' )
			] ) +
			docCard( __( 'Custom Title', 'masjidos' ), __( 'Change the widget heading for a homepage or masjid page.', 'masjidos' ), '[masjidos_prayer_times title="Today at Madani Masjid"]', [
				__( 'Good for homepage sections', 'masjidos' ),
				__( 'Useful when multiple masjid pages exist', 'masjidos' )
			] ) +
			docCard( __( 'Compact Design', 'masjidos' ), __( 'Use this in a sidebar, footer, or narrow homepage section.', 'masjidos' ), '[masjidos_prayer_times design="compact"]', [
				__( 'Hides the side panel automatically', 'masjidos' ),
				__( 'Keeps countdown and prayer table', 'masjidos' ),
				__( 'Included in the free plugin', 'masjidos' )
			] ) +
			docCard( __( 'Bangla Widget', 'masjidos' ), __( 'Show prayer names and widget labels in Bangla.', 'masjidos' ), '[masjidos_prayer_times language="bn"]', [
				__( 'Shows one prayer name only', 'masjidos' ),
				__( 'Does not add Arabic beside the name', 'masjidos' ),
				__( 'Works with classic and compact designs', 'masjidos' )
			] ) +
			docCard( __( 'Hide Hijri Date', 'masjidos' ), __( 'Use this when you only want the Gregorian date in the prayer widget header.', 'masjidos' ), '[masjidos_prayer_times hijri="no"]', [
				__( 'Keeps the timezone visible', 'masjidos' ),
				__( 'Hijri date can be adjusted from Settings > Calculation', 'masjidos' )
			] ) +
			docCard( __( 'Hide Qibla', 'masjidos' ), __( 'Use this when you want a compact prayer table only.', 'masjidos' ), '[masjidos_prayer_times qibla="no"]', [
				__( 'Keeps next prayer countdown', 'masjidos' ),
				__( 'Hides only the Qibla panel', 'masjidos' )
			] ) +
			docCard( __( 'Hide Meta', 'masjidos' ), __( 'Use this when method/timezone details are not needed publicly.', 'masjidos' ), '[masjidos_prayer_times meta="no"]', [
				__( 'Still shows Qibla if qibla is enabled', 'masjidos' ),
				__( 'Makes the widget visually shorter', 'masjidos' )
			] ) +
			docCard( __( 'Hide Iqamah', 'masjidos' ), __( 'Use this when you only want calculated prayer times.', 'masjidos' ), '[masjidos_prayer_times iqamah="no"]', [
				__( 'Keeps Azan/prayer time rows clean', 'masjidos' ),
				__( 'Useful before Iqamah times are finalized', 'masjidos' )
			] ) +
		'</div>';
	}

	function calendarDocsSection() {
		return '<div class="itmms-docs-grid">' +
			docCard( __( 'Islamic Calendar', 'masjidos' ), __( 'Show a Hijri + Gregorian calendar with Islamic dates, holy days, and mosque events.', 'masjidos' ), '[masjidos_islamic_calendar]', [
				__( 'Highlights today and important Islamic dates', 'masjidos' ),
				__( 'Shows mosque events on matching days', 'masjidos' ),
				__( 'Visitors can switch month and year without reloading the page', 'masjidos' ),
				__( 'Uses the Hijri Date Adjustment from Settings > Calculation', 'masjidos' )
			] ) +
			docCard( __( 'Bangla Islamic Calendar', 'masjidos' ), __( 'Show calendar labels and Hijri month names in Bangla.', 'masjidos' ), '[masjidos_islamic_calendar language="bn"]', [
				__( 'Good for Bangla mosque websites', 'masjidos' ),
				__( 'Keeps event titles and descriptions exactly as you entered them', 'masjidos' )
			] ) +
			docCard( __( 'Specific Calendar Month', 'masjidos' ), __( 'Show a fixed Gregorian month while still displaying Hijri dates inside each day.', 'masjidos' ), '[masjidos_islamic_calendar month="7" year="2026"]', [
				__( 'Month uses 1 to 12', 'masjidos' ),
				__( 'Useful for Ramadan, Eid, or event archive pages', 'masjidos' )
			] ) +
			docCard( __( 'Calendar Without Navigation', 'masjidos' ), __( 'Use this when you want a fixed embedded month.', 'masjidos' ), '[masjidos_islamic_calendar navigation="no"]', [
				__( 'Hides month and year controls', 'masjidos' ),
				__( 'Still shows Hijri dates and event markers', 'masjidos' )
			] ) +
		'</div>';
	}

	function duasDocsSection() {
		return '<div class="itmms-docs-grid">' +
			docCard( __( 'Duas & Azkar Widget', 'masjidos' ), __( 'Show a curated set of daily duas and azkar on any public page.', 'masjidos' ), '[masjidos_duas_azkar]', [
				__( 'Includes Arabic, transliteration, meaning, and source', 'masjidos' ),
				__( 'No external API required', 'masjidos' ),
				__( 'Good for homepages, sidebars, and learning pages', 'masjidos' )
			] ) +
			docCard( __( 'Morning Azkar', 'masjidos' ), __( 'Show only duas suitable for morning remembrance.', 'masjidos' ), '[masjidos_duas_azkar category="morning"]', [
				__( 'Filters the built-in collection', 'masjidos' ),
				__( 'Useful for daily rotation sections', 'masjidos' )
			] ) +
			docCard( __( 'Compact Duas', 'masjidos' ), __( 'Use a narrow layout for sidebars and mobile-first sections.', 'masjidos' ), '[masjidos_duas_azkar design="compact" limit="3"]', [
				__( 'Hides transliteration to save space', 'masjidos' ),
				__( 'Keeps Arabic and meaning visible', 'masjidos' )
			] ) +
			docCard( __( 'Bangla Duas', 'masjidos' ), __( 'Show widget labels and meanings in Bangla.', 'masjidos' ), '[masjidos_duas_azkar language="bn"]', [
				__( 'Good for Bangla mosque websites', 'masjidos' ),
				__( 'Arabic dua text remains unchanged', 'masjidos' )
			] ) +
			docCard( __( 'Duas Library', 'masjidos' ), __( 'Add your own duas from the native WordPress library screen.', 'masjidos' ), __( 'MasjidOS > Duas Library', 'masjidos' ), [
				__( 'Publish a dua to include it in the public widget', 'masjidos' ),
				__( 'Assign native Dua Categories such as morning, food, or travel', 'masjidos' ),
				__( 'Use Select Audio to choose pronunciation audio from the Media Library', 'masjidos' )
			] ) +
		'</div>';
	}

	function announcementDocsSection() {
		return '<div class="itmms-docs-grid">' +
			docCard( __( 'Notice List', 'masjidos' ), __( 'Show active notices as a readable public notice board.', 'masjidos' ), '[masjidos_announcements]', [
				__( 'First create and publish a notice from MasjidOS > Notices', 'masjidos' ),
				__( 'Uses published notices whose schedule is currently active', 'masjidos' ),
				__( 'Sorted by priority and start date', 'masjidos' ),
				__( 'Includes notice type and publish date', 'masjidos' )
			] ) +
			docCard( __( 'Notice Ticker', 'masjidos' ), __( 'Use a compact moving strip in a homepage or header area.', 'masjidos' ), '[masjidos_announcements design="ticker"]', [
				__( 'Included in the free plugin', 'masjidos' ),
				__( 'Pauses on hover and respects reduced-motion settings', 'masjidos' ),
				__( 'Good for urgent and short updates', 'masjidos' )
			] ) +
			docCard( __( 'Urgent Notices Only', 'masjidos' ), __( 'Filter the widget to a single notice type.', 'masjidos' ), '[masjidos_announcements type="urgent"]', [
				__( 'Accepted types: all, general, urgent, jumuah', 'masjidos' ),
				__( 'Create and schedule notices from the Notices screen', 'masjidos' )
			] ) +
			docCard( __( 'Notice Banner', 'masjidos' ), __( 'Use a slim banner notice layout at page header or top.', 'masjidos' ), '[masjidos_announcements design="banner"]', [
				__( 'Displays the highest priority notice as a slim strip banner', 'masjidos' ),
				__( 'Golden style auto-applied during Ramadan', 'masjidos' )
			] ) +
			docCard( __( 'Popup Notice', 'masjidos' ), __( 'Show urgent announcements inside a dismissible popup window.', 'masjidos' ), '[masjidos_announcements design="popup"]', [
				__( 'Presents notice inside a modal popup overlay', 'masjidos' ),
				__( 'Saves dismissal status in session storage to avoid repeating', 'masjidos' )
			] ) +
			docCard( __( 'Hide Dates', 'masjidos' ), __( 'Show a cleaner list without start dates.', 'masjidos' ), '[masjidos_announcements show_date="no"]', [
				__( 'Useful for evergreen notices', 'masjidos' ),
				__( 'Expiry rules still apply in the background', 'masjidos' )
			] ) +
		'</div>';
	}

	function jumuahDocsSection() {
		return '<div class="itmms-docs-grid">' +
			docCard( __( 'Jumuah Widget', 'masjidos' ), __( 'Show Friday khutbah and jamaat time on any public page.', 'masjidos' ), '[masjidos_jumuah]', [
				__( 'Uses the Jumuah Settings section', 'masjidos' ),
				__( 'Shows one or two Jumuah sessions', 'masjidos' ),
				__( 'Can show khatib photo, name, bio, topic, and language', 'masjidos' ),
				__( 'Good for homepage and Friday notice pages', 'masjidos' )
			] ) +
			docCard( __( 'Compact Jumuah', 'masjidos' ), __( 'A narrow Jumuah card for sidebars, footers, or mobile-first layouts.', 'masjidos' ), '[masjidos_jumuah design="compact"]', [
				__( 'Included in the free plugin', 'masjidos' ),
				__( 'Designed for small spaces', 'masjidos' ),
				__( 'Supports language="bn"', 'masjidos' )
			] ) +
			docCard( __( 'Bangla Jumuah', 'masjidos' ), __( 'Show the Jumuah widget labels in Bangla.', 'masjidos' ), '[masjidos_jumuah language="bn"]', [
				__( 'Translates widget title and labels', 'masjidos' ),
				__( 'Keeps your configured topic and khatib text unchanged', 'masjidos' )
			] ) +
			docCard( __( 'Khutbah Archive', 'masjidos' ), __( 'Public Minbar archive with search, category, date filters, audio, and PDF.', 'masjidos' ), '[masjidos_khutbah_archive]', [
				__( 'Only public archive entries are shown', 'masjidos' ),
				__( 'Filter by topic, khatib, category, or date', 'masjidos' ),
				__( 'Optional audio player and PDF download links', 'masjidos' )
			] ) +
			docCard( __( 'This Week\'s Khatib', 'masjidos' ), __( 'Show this Friday\'s scheduled khatib and topic from Minbar Schedule.', 'masjidos' ), '[masjidos_khatib_this_week]', [
				__( 'Reads the Minbar roster for the next Friday', 'masjidos' ),
				__( 'Useful on homepage or Jumuah pages', 'masjidos' )
			] ) +
			docCard( __( 'Upcoming Khutbahs', 'masjidos' ), __( 'List upcoming scheduled or planned Friday topics.', 'masjidos' ), '[masjidos_upcoming_khutbah]', [
				__( 'Combines schedule roster and planner topics', 'masjidos' ),
				__( 'limit attribute controls how many rows show', 'masjidos' )
			] ) +
			docCard( __( 'Khutbah Search', 'masjidos' ), __( 'Compact search box for visitors to find past Friday sermons.', 'masjidos' ), '[masjidos_khutbah_search]', [
				__( 'Searches your public Minbar archive', 'masjidos' ),
				__( 'Only published archive entries appear in results', 'masjidos' )
			] ) +
			docCard( __( 'Jumuah Without Meta', 'masjidos' ), __( 'Show only the public Jumuah session times and header.', 'masjidos' ), '[masjidos_jumuah meta="no"]', [
				__( 'Hides khatib profile, topic, language, and location', 'masjidos' ),
				__( 'Useful for very simple pages', 'masjidos' )
			] ) +
		'</div>';
	}

	function proDocsSection() {
		var data = window.itmms.data || {};
		var pro = data.pro || {};
		var url = data.proUrl || pro.url || '';
		var cta = url
			? '<p class="itmms-docs-pro-cta"><a class="itmms-btn itmms-btn-primary" href="' + esc( url ) + '" target="_blank" rel="noopener noreferrer">' +
				esc( pro.active ? ( pro.cta || __( 'Open Pro', 'masjidos' ) ) : ( pro.cta || __( 'Learn about MasjidOS Pro', 'masjidos' ) ) ) +
			'</a></p>'
			: '';

		var sections = [];
		if ( pro.active && Array.isArray( pro.docs ) && pro.docs.length ) {
			sections = pro.docs.slice();
		} else if ( ! pro.active ) {
			sections.push( {
				id: 'locked',
				nav: __( 'Pro tools', 'masjidos' ),
				heading: __( 'Pro tools (when installed)', 'masjidos' ),
				note: __( 'Donations, Accounts ledger, and Special day collections docs appear here after MasjidOS Pro is active. Shortcode details are maintained by the Pro plugin.', 'masjidos' )
			} );
		}

		sections.push( {
			id: 'designs',
			nav: __( 'Designs', 'masjidos' ),
			heading: __( 'Pro design presets', 'masjidos' ),
			note: __( 'The free plugin only shows these as available in MasjidOS Pro. The actual Pro design code ships from the Pro plugin, not from this free plugin.', 'masjidos' ),
			_designs: true,
			_cta: cta
		} );

		var toc = '<nav class="itmms-docs-toc" aria-label="' + esc( __( 'Pro docs topics', 'masjidos' ) ) + '">' +
			'<span class="itmms-docs-toc__label">' + esc( __( 'Jump to', 'masjidos' ) ) + '</span>' +
			sections.map( function ( section, index ) {
				var id = proDocSectionId( section, index );
				var label = String( section.nav || section.heading || id );
				return '<button type="button" class="itmms-docs-toc__btn" data-docs-accordion-open="' + esc( id ) + '">' + esc( label ) + '</button>';
			} ).join( '' ) +
		'</nav>';

		var accordions = sections.map( function ( section, index ) {
			return renderProDocAccordion( section, index, index === 0 );
		} ).join( '' );

		return '<div class="itmms-docs-pro">' +
			'<p class="itmms-docs-note">' + esc( __( 'Pick a topic below — sections stay collapsed so the page stays short as Pro grows.', 'masjidos' ) ) + '</p>' +
			toc +
			'<div class="itmms-docs-accordions">' + accordions + '</div>' +
		'</div>';
	}

	function proDocSectionId( section, index ) {
		var raw = String( ( section && section.id ) || ( section && section.heading ) || ( 'section-' + index ) );
		return raw.toLowerCase().replace( /[^a-z0-9\-]+/g, '-' ).replace( /^-+|-+$/g, '' ) || ( 'section-' + index );
	}

	function renderProDocAccordion( section, index, open ) {
		if ( ! section || typeof section !== 'object' ) {
			return '';
		}
		var id = proDocSectionId( section, index );
		var heading = String( section.heading || section.nav || id );
		var body = '';

		if ( section.note ) {
			body += '<p class="itmms-docs-note">' + esc( String( section.note ) ) + '</p>';
		}
		if ( section._cta ) {
			body += section._cta;
		}
		if ( section.admin_url ) {
			body += '<p class="itmms-docs-pro-cta"><a class="itmms-btn itmms-btn-primary" href="' + esc( String( section.admin_url ) ) + '">' +
				esc( String( section.admin_label || __( 'Open in Pro', 'masjidos' ) ) ) +
			'</a></p>';
		}
		if ( Array.isArray( section.steps ) && section.steps.length ) {
			body += '<div class="itmms-paste-grid">' + section.steps.map( function ( step ) {
				return pasteItem( String( step.title || '' ), String( step.text || '' ) );
			} ).join( '' ) + '</div>';
		}
		if ( Array.isArray( section.cards ) && section.cards.length ) {
			body += '<div class="itmms-docs-grid">' + section.cards.map( function ( card ) {
				var sc = String( card.shortcode || '' );
				var bullets = Array.isArray( card.bullets ) ? card.bullets.map( String ) : [];
				if ( ! sc ) {
					return docInfoCard( String( card.title || '' ), String( card.description || '' ), bullets );
				}
				return docCard(
					String( card.title || '' ),
					String( card.description || '' ),
					sc,
					bullets
				);
			} ).join( '' ) + '</div>';
		}
		if ( Array.isArray( section.rows ) && section.rows.length ) {
			body += '<div class="itmms-docs-table">' + section.rows.map( function ( row ) {
				return docRow(
					String( row.attr || '' ),
					String( row.values || '' ),
					String( row.fallback || '' ),
					String( row.note || '' )
				);
			} ).join( '' ) + '</div>';
		}
		if ( section._designs ) {
			body += '<div class="itmms-pro-design-grid">' +
				proDesignCard( __( 'Premium Card', 'masjidos' ), '[masjidos_prayer_times design="premium-card"]' ) +
				proDesignCard( __( 'Mosque Display', 'masjidos' ), '[masjidos_prayer_times design="mosque-display"]' ) +
				proDesignCard( __( 'Ramadan Special', 'masjidos' ), '[masjidos_prayer_times design="ramadan-special"]' ) +
				proDesignCard( __( 'Premium Sermon', 'masjidos' ), '[masjidos_jumuah design="premium-sermon"]' ) +
				proDesignCard( __( 'Mosque Notice', 'masjidos' ), '[masjidos_jumuah design="mosque-notice"]' ) +
				proDesignCard( __( 'Premium Print', 'masjidos' ), '[masjidos_monthly_prayer_times design="premium-print"]' ) +
				proDesignCard( __( 'Mosque Board', 'masjidos' ), '[masjidos_monthly_prayer_times design="mosque-board"]' ) +
				proDesignCard( __( 'Ramadan Monthly', 'masjidos' ), '[masjidos_monthly_prayer_times design="ramadan-monthly"]' ) +
				proDesignCard( __( 'Digital Board', 'masjidos' ), '[masjidos_announcements design="digital-board"]' ) +
				proDesignCard( __( 'Ramadan Banner', 'masjidos' ), '[masjidos_announcements design="ramadan-banner"]' ) +
			'</div>';
		}

		return '<details class="itmms-docs-accordion" id="docs-pro-' + esc( id ) + '" data-docs-accordion="' + esc( id ) + '"' + ( open ? ' open' : '' ) + '>' +
			'<summary class="itmms-docs-accordion__summary">' +
				'<span>' + esc( heading ) + '</span>' +
				'<em aria-hidden="true"></em>' +
			'</summary>' +
			'<div class="itmms-docs-accordion__body">' + body + '</div>' +
		'</details>';
	}

	function renderProDocSection( section ) {
		return renderProDocAccordion( section, 0, true );
	}

	function checklistSection() {
		var status = docsChecklistStatus();
		var done = Object.keys( status ).filter( function ( key ) { return status[ key ]; } ).length;
		var total = 10;
		return '<section class="itmms-docs-section">' +
			'<h3>' + esc( __( 'Recommended setup checklist', 'masjidos' ) ) + '</h3>' +
			'<p class="itmms-docs-note">' + esc( sprintf( __( '%1$d of %2$d complete — green items are detected from your saved settings.', 'masjidos' ), done, total ) ) + '</p>' +
			'<div class="itmms-docs-checklist">' +
				checkItem( 'language', __( 'Choose Admin Language', 'masjidos' ), __( 'Pick English, Bangla, or Arabic from the top bar so menus and Docs feel familiar.', 'masjidos' ), status.language, true ) +
				checkItem( 'timezone', __( 'Set Timezone', 'masjidos' ), __( 'For Bangladesh use Asia/Dhaka. Wrong timezone is the most common cause of wrong prayer times.', 'masjidos' ), status.timezone, true ) +
				checkItem( 'coords', __( 'Set Coordinates', 'masjidos' ), __( 'Copy latitude and longitude from Google Maps for the exact masjid location.', 'masjidos' ), status.coords, true ) +
				checkItem( 'method', __( 'Choose Method', 'masjidos' ), __( 'Pick the method used by your local Islamic authority. For Bangladesh, Karachi + Hanafi is a sensible starting point.', 'masjidos' ), status.method, true ) +
				checkItem( 'csv', __( 'Import Official CSV Year', 'masjidos' ), __( 'In Prayer Setup → Timetable: Export Calculated Year, edit official times, Validate, then Import. Widgets and TV use imported days.', 'masjidos' ), status.csv, true ) +
				checkItem( 'iqamah', __( 'Set Iqamah Times', 'masjidos' ), __( 'Use Iqamah rules (fixed or minutes after Azan) in Prayer Setup → Iqamah.', 'masjidos' ), status.iqamah, true ) +
				checkItem( 'jumuah', __( 'Set Jumuah Details', 'masjidos' ), __( 'Add sessions, topic, language, khatib profile, and notice from Settings > Jumuah Settings, then publish [masjidos_jumuah].', 'masjidos' ), status.jumuah, true ) +
				checkItem( 'features', __( 'Try Features Preview', 'masjidos' ), __( 'Open MasjidOS > Features to preview widgets before adding them to a public page.', 'masjidos' ), status.features, false ) +
				checkItem( 'publish', __( 'Publish Prayer Shortcode', 'masjidos' ), __( 'Add [masjidos_prayer_times] to the masjid homepage or a dedicated prayer times page.', 'masjidos' ), status.publish, false ) +
				checkItem( 'tv', __( 'Optional: TV Display', 'masjidos' ), __( 'Visit /masjidos-display/ on your site if you need a fullscreen board for mosque TVs.', 'masjidos' ), status.tv, false ) +
			'</div>' +
		'</section>';
	}

	function docsChecklistStatus() {
		var state = window.itmms.state || {};
		var s = state.settings || {};
		var lat = Number( s.latitude );
		var lng = Number( s.longitude );
		var coordsOk = ! isNaN( lat ) && ! isNaN( lng ) && ( Math.abs( lat ) > 0.0001 || Math.abs( lng ) > 0.0001 ) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180;
		var timezone = String( s.timezone || '' );
		var timezoneOk = !! timezone && [ 'UTC', '+00:00', '-00:00', '' ].indexOf( timezone ) === -1;
		var timetable = state.timetable || {};
		var iqamah = s.iqamah_times || {};
		var iqamahRules = s.iqamah_rules || {};
		var hasIqamah = [ 'fajr', 'dhuhr', 'asr', 'maghrib', 'isha' ].some( function ( key ) {
			var rule = iqamahRules[ key ] || {};
			var mode = rule.mode || ( iqamah[ key ] ? 'fixed' : '' );
			if ( mode === 'after_azan' || mode === 'before_sunrise' ) {
				return Number( rule.minutes || 0 ) > 0;
			}
			return mode === 'fixed' && !! iqamah[ key ];
		} );
		var jumuah = s.jumuah || {};
		var sessions = ( window.itmms.settings && window.itmms.settings.normalizeJumuahSessions )
			? window.itmms.settings.normalizeJumuahSessions( jumuah )
			: ( Array.isArray( jumuah.sessions ) ? jumuah.sessions : [] );
		var khatib = jumuah.khatib || {};
		var hasJumuah = !!( jumuah.topic || jumuah.language || khatib.name || sessions.some( function ( row ) { return row && ( row.khutbah_time || row.jamaat_time ); } ) );

		return {
			language: !!( s.ui_language ),
			timezone: timezoneOk,
			coords: coordsOk,
			method: !!( s.calculation_method && s.asr_method ),
			csv: !!( timetable.active || Number( timetable.count || 0 ) > 0 ),
			iqamah: hasIqamah,
			jumuah: hasJumuah,
			features: docsCheckSaved( 'features' ),
			publish: docsCheckSaved( 'publish' ),
			tv: docsCheckSaved( 'tv' )
		};
	}

	function docsCheckSaved( id ) {
		try {
			return window.localStorage.getItem( 'itmms_docs_check_' + id ) === '1';
		} catch ( e ) {
			return false;
		}
	}

	function prayerAttributesSection() {
		return '<section class="itmms-docs-section">' +
			'<h3>' + esc( __( 'Prayer shortcode attributes', 'masjidos' ) ) + '</h3>' +
			'<div class="itmms-docs-table">' +
				docRow( 'title', __( 'Text', 'masjidos' ), __( 'Prayer Times', 'masjidos' ), __( 'Changes the widget heading.', 'masjidos' ) ) +
				docRow( 'design', 'classic/compact', 'classic', __( 'Selects the free design preset. Pro adds more designs from the Pro plugin.', 'masjidos' ) ) +
				docRow( 'language', 'en/bn/ar', 'en', __( 'Changes public widget labels and prayer names.', 'masjidos' ) ) +
				docRow( 'qibla', 'yes/no', 'yes', __( 'Shows or hides the Qibla card.', 'masjidos' ) ) +
				docRow( 'meta', 'yes/no', 'yes', __( 'Shows or hides method, Asr, and timezone details.', 'masjidos' ) ) +
				docRow( 'compact', 'yes/no', 'no', __( 'Legacy shortcut. Use design="compact" for new pages.', 'masjidos' ) ) +
				docRow( 'iqamah', 'yes/no', 'yes', __( 'Shows or hides jamaat/Iqamah times.', 'masjidos' ) ) +
				docRow( 'hijri', 'yes/no', 'yes', __( 'Shows or hides the Hijri date in the widget header.', 'masjidos' ) ) +
			'</div>' +
		'</section>';
	}

	function jumuahAttributesSection() {
		return '<section class="itmms-docs-section">' +
			'<h3>' + esc( __( 'Jumuah shortcode attributes', 'masjidos' ) ) + '</h3>' +
			'<p class="itmms-docs-note">' + esc( __( 'The shortcode controls presentation only. Actual Friday data comes from Settings > Jumuah Settings.', 'masjidos' ) ) + '</p>' +
			'<div class="itmms-docs-table">' +
				docRow( 'design', 'classic/compact', 'classic', __( 'Selects the free Jumuah design preset. Pro can add more designs from the Pro plugin.', 'masjidos' ) ) +
				docRow( 'language', 'en/bn/ar', 'en', __( 'Changes public Jumuah labels.', 'masjidos' ) ) +
				docRow( 'title', __( 'Text', 'masjidos' ), __( 'Jumuah Prayer', 'masjidos' ), __( 'Changes the widget heading.', 'masjidos' ) ) +
				docRow( 'meta', 'yes/no', 'yes', __( 'Shows or hides khatib and location details.', 'masjidos' ) ) +
			'</div>' +
		'</section>';
	}

	function monthlyAttributesSection() {
		return '<section class="itmms-docs-section">' +
			'<h3>' + esc( __( 'Monthly timetable attributes', 'masjidos' ) ) + '</h3>' +
			'<div class="itmms-docs-table">' +
				docRow( 'month', '1-12', __( 'current', 'masjidos' ), __( 'Shows a specific Gregorian month.', 'masjidos' ) ) +
				docRow( 'year', 'YYYY', __( 'current', 'masjidos' ), __( 'Shows a specific Gregorian year.', 'masjidos' ) ) +
				docRow( 'design', 'table/pro key', 'table', __( 'Selects the monthly timetable design. Pro designs are rendered by the Pro plugin.', 'masjidos' ) ) +
				docRow( 'language', 'en/bn/ar', 'en', __( 'Changes public labels and prayer names.', 'masjidos' ) ) +
				docRow( 'title', __( 'Text', 'masjidos' ), __( 'Monthly Prayer Timetable', 'masjidos' ), __( 'Changes the table heading.', 'masjidos' ) ) +
				docRow( 'iqamah', 'yes/no', 'no', __( 'Adds Iqamah columns beside prayer times.', 'masjidos' ) ) +
				docRow( 'navigation', 'yes/no', 'yes', __( 'Shows or hides reload-free month and year controls.', 'masjidos' ) ) +
			'</div>' +
		'</section>';
	}

	function calendarAttributesSection() {
		return '<section class="itmms-docs-section">' +
			'<h3>' + esc( __( 'Islamic calendar attributes', 'masjidos' ) ) + '</h3>' +
			'<div class="itmms-docs-table">' +
				docRow( 'month', '1-12', __( 'current', 'masjidos' ), __( 'Shows a specific Gregorian month with Hijri dates inside the grid.', 'masjidos' ) ) +
				docRow( 'year', 'YYYY', __( 'current', 'masjidos' ), __( 'Shows a specific Gregorian year.', 'masjidos' ) ) +
				docRow( 'language', 'en/bn/ar', 'en', __( 'Changes calendar labels, month names, and number formatting.', 'masjidos' ) ) +
				docRow( 'title', __( 'Text', 'masjidos' ), __( 'Islamic Calendar', 'masjidos' ), __( 'Changes the calendar heading.', 'masjidos' ) ) +
				docRow( 'navigation', 'yes/no', 'yes', __( 'Shows or hides reload-free calendar controls.', 'masjidos' ) ) +
			'</div>' +
		'</section>';
	}

	function duasAttributesSection() {
		return '<section class="itmms-docs-section">' +
			'<h3>' + esc( __( 'Duas & Azkar attributes', 'masjidos' ) ) + '</h3>' +
			'<div class="itmms-docs-table">' +
				docRow( 'title', __( 'Text', 'masjidos' ), __( 'Duas & Azkar', 'masjidos' ), __( 'Changes the widget heading.', 'masjidos' ) ) +
				docRow( 'language', 'en/bn/ar', 'en', __( 'Changes widget labels and meanings.', 'masjidos' ) ) +
				docRow( 'category', 'all/daily/morning/evening/food/sleep/home/masjid/travel/rain/forgiveness/quran/protection', 'all', __( 'Filters the built-in dua collection.', 'masjidos' ) ) +
				docRow( 'design', 'cards/compact', 'cards', __( 'Selects the free design preset.', 'masjidos' ) ) +
				docRow( 'limit', '1-12', '4', __( 'Controls how many duas appear.', 'masjidos' ) ) +
				docRow( 'source', 'yes/no', 'yes', __( 'Shows or hides source labels.', 'masjidos' ) ) +
				docRow( 'counter', 'yes/no', 'yes', __( 'Shows or hides local recitation counters.', 'masjidos' ) ) +
				docRow( 'share', 'yes/no', 'yes', __( 'Shows or hides share buttons.', 'masjidos' ) ) +
				docRow( 'audio', 'yes/no', 'yes', __( 'Shows audio buttons when an item has an audio URL.', 'masjidos' ) ) +
			'</div>' +
		'</section>';
	}

	function announcementAttributesSection() {
		return '<section class="itmms-docs-section">' +
			'<h3>' + esc( __( 'Announcement shortcode attributes', 'masjidos' ) ) + '</h3>' +
			'<div class="itmms-docs-table">' +
				docRow( 'design', 'list/ticker/banner/popup', 'list', __( 'Selects a free notice design. Pro designs are supplied by the Pro plugin.', 'masjidos' ) ) +
				docRow( 'language', 'en/bn/ar', 'en', __( 'Changes widget labels.', 'masjidos' ) ) +
				docRow( 'type', 'all/general/urgent/jumuah', 'all', __( 'Filters public notices by type.', 'masjidos' ) ) +
				docRow( 'limit', '1-20', '5', __( 'Limits the number of active notices shown.', 'masjidos' ) ) +
				docRow( 'title', __( 'Text', 'masjidos' ), __( 'Masjid Notices', 'masjidos' ), __( 'Changes the list design heading.', 'masjidos' ) ) +
				docRow( 'show_date', 'yes/no', 'yes', __( 'Shows or hides notice start dates.', 'masjidos' ) ) +
			'</div>' +
		'</section>';
	}

	function beginnerTipsSection() {
		return '<section class="itmms-docs-section">' +
			'<h3>' + esc( __( 'If you feel stuck', 'masjidos' ) ) + '</h3>' +
			'<div class="itmms-docs-roadmap">' +
				'<span>' + esc( __( 'Start in Features, not Reference', 'masjidos' ) ) + '</span>' +
				'<span>' + esc( __( 'Use Generators to build shortcodes', 'masjidos' ) ) + '</span>' +
				'<span>' + esc( __( 'Wrong times? Recheck timezone first', 'masjidos' ) ) + '</span>' +
				'<span>' + esc( __( 'After updates, hard refresh (Ctrl+F5)', 'masjidos' ) ) + '</span>' +
			'</div>' +
			'<p class="itmms-docs-note">' + esc( __( 'Planned polish later: more Gutenberg blocks, more TV layouts, and printable PDF monthly timetables.', 'masjidos' ) ) + '</p>' +
		'</section>';
	}

	function tvDisplayReferenceSection() {
		return '<section class="itmms-docs-section">' +
			'<h3>' + esc( __( 'TV display URL options', 'masjidos' ) ) + '</h3>' +
			'<p class="itmms-docs-note">' + esc( __( 'The TV Display is a URL, not a shortcode. Open it in a browser on your mosque screen.', 'masjidos' ) ) + '</p>' +
			'<div class="itmms-docs-table">' +
				docRow( '/masjidos-display/', __( 'URL', 'masjidos' ), __( 'default', 'masjidos' ), __( 'Fullscreen board using saved TV Display settings.', 'masjidos' ) ) +
				docRow( 'theme', 'dark/light/green', 'dark', __( 'Optional URL override, for example /masjidos-display/?theme=light.', 'masjidos' ) ) +
				docRow( 'layout', 'classic/split/focus', 'classic', __( 'Optional layout override, for example /masjidos-display/?layout=focus.', 'masjidos' ) ) +
				docRow( 'lang', 'en/bn/ar', 'en', __( 'Optional URL override for display labels.', 'masjidos' ) ) +
				docRow( 'font_size', 'small/normal/large/xlarge', 'normal', __( 'Optional URL override for large screens.', 'masjidos' ) ) +
			'</div>' +
		'</section>';
	}

	function docCard( title, description, shortcode, bullets ) {
		return '<article class="itmms-doc-card">' +
			'<div class="itmms-doc-card-head"><h3>' + esc( title ) + '</h3><button type="button" class="itmms-copy-btn" data-copy-shortcode="' + esc( shortcode ) + '">' + esc( __( 'Copy', 'masjidos' ) ) + '</button></div>' +
			'<p>' + esc( description ) + '</p>' +
			'<code>' + esc( shortcode ) + '</code>' +
			'<ul>' + bullets.map( function ( bullet ) {
				return '<li>' + esc( bullet ) + '</li>';
			} ).join( '' ) + '</ul>' +
		'</article>';
	}

	function docInfoCard( title, description, bullets ) {
		return '<article class="itmms-doc-card">' +
			'<div class="itmms-doc-card-head"><h3>' + esc( title ) + '</h3></div>' +
			'<p>' + esc( description ) + '</p>' +
			'<ul>' + bullets.map( function ( bullet ) {
				return '<li>' + esc( bullet ) + '</li>';
			} ).join( '' ) + '</ul>' +
		'</article>';
	}

	function shortcodeBuilderHtml() {
		return '<section class="itmms-docs-section itmms-shortcode-builder">' +
			'<div class="itmms-builder-head"><div><h3>' + esc( __( 'Prayer Times Generator', 'masjidos' ) ) + '</h3><p>' + esc( __( 'Create a copy-ready prayer widget shortcode without memorizing attributes.', 'masjidos' ) ) + '</p></div><button type="button" class="itmms-btn itmms-btn-primary" data-copy-generated-shortcode>' + esc( __( 'Copy Shortcode', 'masjidos' ) ) + '</button></div>' +
			'<div class="itmms-builder-grid">' +
				'<label><span>' + esc( __( 'Design', 'masjidos' ) ) + '</span><select data-builder-design><option value="classic">' + esc( __( 'Classic', 'masjidos' ) ) + '</option><option value="compact">' + esc( __( 'Compact', 'masjidos' ) ) + '</option></select></label>' +
				'<label><span>' + esc( __( 'Language', 'masjidos' ) ) + '</span><select data-builder-language><option value="en">' + esc( __( 'English', 'masjidos' ) ) + '</option><option value="bn">' + esc( __( 'Bangla', 'masjidos' ) ) + '</option><option value="ar">' + esc( __( 'Arabic', 'masjidos' ) ) + '</option></select></label>' +
				'<label><span>' + esc( __( 'Title', 'masjidos' ) ) + '</span><input type="text" data-builder-title placeholder="' + esc( __( 'Optional custom title', 'masjidos' ) ) + '"></label>' +
				'<label class="itmms-builder-check"><input type="checkbox" data-builder-qibla checked><span>' + esc( __( 'Show Qibla', 'masjidos' ) ) + '</span></label>' +
				'<label class="itmms-builder-check"><input type="checkbox" data-builder-meta checked><span>' + esc( __( 'Show Meta', 'masjidos' ) ) + '</span></label>' +
				'<label class="itmms-builder-check"><input type="checkbox" data-builder-iqamah checked><span>' + esc( __( 'Show Iqamah', 'masjidos' ) ) + '</span></label>' +
				'<label class="itmms-builder-check"><input type="checkbox" data-builder-hijri checked><span>' + esc( __( 'Show Hijri Date', 'masjidos' ) ) + '</span></label>' +
			'</div>' +
			'<div class="itmms-builder-output"><code data-generated-shortcode>[masjidos_prayer_times]</code></div>' +
			'</section>' +
			'<section class="itmms-docs-section itmms-shortcode-builder">' +
			'<div class="itmms-builder-head"><div><h3>' + esc( __( 'Jumuah Generator', 'masjidos' ) ) + '</h3><p>' + esc( __( 'Create a Jumuah widget shortcode for Friday pages, sidebars, or homepage sections.', 'masjidos' ) ) + '</p></div><button type="button" class="itmms-btn itmms-btn-primary" data-copy-generated-jumuah-shortcode>' + esc( __( 'Copy Shortcode', 'masjidos' ) ) + '</button></div>' +
			'<div class="itmms-builder-grid">' +
				'<label><span>' + esc( __( 'Design', 'masjidos' ) ) + '</span><select data-jumuah-builder-design><option value="classic">' + esc( __( 'Classic', 'masjidos' ) ) + '</option><option value="compact">' + esc( __( 'Compact', 'masjidos' ) ) + '</option></select></label>' +
				'<label><span>' + esc( __( 'Language', 'masjidos' ) ) + '</span><select data-jumuah-builder-language><option value="en">' + esc( __( 'English', 'masjidos' ) ) + '</option><option value="bn">' + esc( __( 'Bangla', 'masjidos' ) ) + '</option><option value="ar">' + esc( __( 'Arabic', 'masjidos' ) ) + '</option></select></label>' +
				'<label><span>' + esc( __( 'Title', 'masjidos' ) ) + '</span><input type="text" data-jumuah-builder-title placeholder="' + esc( __( 'Optional custom title', 'masjidos' ) ) + '"></label>' +
				'<label class="itmms-builder-check"><input type="checkbox" data-jumuah-builder-meta checked><span>' + esc( __( 'Show Khatib & Meta', 'masjidos' ) ) + '</span></label>' +
			'</div>' +
			'<div class="itmms-builder-output"><code data-generated-jumuah-shortcode>[masjidos_jumuah]</code></div>' +
			'</section>' +
			'<section class="itmms-docs-section itmms-shortcode-builder">' +
			'<div class="itmms-builder-head"><div><h3>' + esc( __( 'Monthly Timetable Generator', 'masjidos' ) ) + '</h3><p>' + esc( __( 'Create a monthly timetable shortcode for public pages or printable notice-board views.', 'masjidos' ) ) + '</p></div><button type="button" class="itmms-btn itmms-btn-primary" data-copy-generated-monthly-shortcode>' + esc( __( 'Copy Shortcode', 'masjidos' ) ) + '</button></div>' +
			'<div class="itmms-builder-grid">' +
				'<label><span>' + esc( __( 'Design', 'masjidos' ) ) + '</span><select data-monthly-builder-design><option value="table">' + esc( __( 'Table', 'masjidos' ) ) + '</option><option value="compact">' + esc( __( 'Compact', 'masjidos' ) ) + '</option></select></label>' +
				'<label><span>' + esc( __( 'Month', 'masjidos' ) ) + '</span><select data-monthly-builder-month><option value="">' + esc( __( 'Current month', 'masjidos' ) ) + '</option>' + monthOptionsHtml() + '</select></label>' +
				'<label><span>' + esc( __( 'Year', 'masjidos' ) ) + '</span><input type="number" min="1900" max="2100" data-monthly-builder-year placeholder="' + esc( __( 'Current year', 'masjidos' ) ) + '"></label>' +
				'<label><span>' + esc( __( 'Language', 'masjidos' ) ) + '</span><select data-monthly-builder-language><option value="en">' + esc( __( 'English', 'masjidos' ) ) + '</option><option value="bn">' + esc( __( 'Bangla', 'masjidos' ) ) + '</option><option value="ar">' + esc( __( 'Arabic', 'masjidos' ) ) + '</option></select></label>' +
				'<label><span>' + esc( __( 'Title', 'masjidos' ) ) + '</span><input type="text" data-monthly-builder-title placeholder="' + esc( __( 'Optional custom title', 'masjidos' ) ) + '"></label>' +
				'<label class="itmms-builder-check"><input type="checkbox" data-monthly-builder-iqamah><span>' + esc( __( 'Show Iqamah', 'masjidos' ) ) + '</span></label>' +
				'<label class="itmms-builder-check"><input type="checkbox" data-monthly-builder-navigation checked><span>' + esc( __( 'Month navigation', 'masjidos' ) ) + '</span></label>' +
			'</div>' +
			'<div class="itmms-builder-output"><code data-generated-monthly-shortcode>[masjidos_monthly_prayer_times]</code></div>' +
		'</section>' +
		'<section class="itmms-docs-section itmms-shortcode-builder">' +
			'<div class="itmms-builder-head"><div><h3>' + esc( __( 'Islamic Calendar Generator', 'masjidos' ) ) + '</h3><p>' + esc( __( 'Create a Hijri + Gregorian calendar shortcode with optional month controls.', 'masjidos' ) ) + '</p></div><button type="button" class="itmms-btn itmms-btn-primary" data-copy-generated-calendar-shortcode>' + esc( __( 'Copy Shortcode', 'masjidos' ) ) + '</button></div>' +
			'<div class="itmms-builder-grid">' +
				'<label><span>' + esc( __( 'Month', 'masjidos' ) ) + '</span><select data-calendar-builder-month><option value="">' + esc( __( 'Current month', 'masjidos' ) ) + '</option>' + monthOptionsHtml() + '</select></label>' +
				'<label><span>' + esc( __( 'Year', 'masjidos' ) ) + '</span><input type="number" min="1900" max="2100" data-calendar-builder-year placeholder="' + esc( __( 'Current year', 'masjidos' ) ) + '"></label>' +
				'<label><span>' + esc( __( 'Language', 'masjidos' ) ) + '</span><select data-calendar-builder-language><option value="en">' + esc( __( 'English', 'masjidos' ) ) + '</option><option value="bn">' + esc( __( 'Bangla', 'masjidos' ) ) + '</option><option value="ar">' + esc( __( 'Arabic', 'masjidos' ) ) + '</option></select></label>' +
				'<label><span>' + esc( __( 'Title', 'masjidos' ) ) + '</span><input type="text" data-calendar-builder-title placeholder="' + esc( __( 'Optional custom title', 'masjidos' ) ) + '"></label>' +
				'<label class="itmms-builder-check"><input type="checkbox" data-calendar-builder-navigation checked><span>' + esc( __( 'Month navigation', 'masjidos' ) ) + '</span></label>' +
			'</div>' +
			'<div class="itmms-builder-output"><code data-generated-calendar-shortcode>[masjidos_islamic_calendar]</code></div>' +
		'</section>' +
		'<section class="itmms-docs-section itmms-shortcode-builder">' +
			'<div class="itmms-builder-head"><div><h3>' + esc( __( 'TV Display URL', 'masjidos' ) ) + '</h3><p>' + esc( __( 'Copy this URL for a fullscreen mosque TV or lobby display.', 'masjidos' ) ) + '</p></div><button type="button" class="itmms-btn itmms-btn-primary" data-copy-shortcode="/masjidos-display/">' + esc( __( 'Copy URL', 'masjidos' ) ) + '</button></div>' +
			'<div class="itmms-builder-output"><code>/masjidos-display/</code></div>' +
		'</section>' +
		'<section class="itmms-docs-section itmms-shortcode-builder">' +
			'<div class="itmms-builder-head"><div><h3>' + esc( __( 'Duas & Azkar Generator', 'masjidos' ) ) + '</h3><p>' + esc( __( 'Create a public duas and azkar widget shortcode.', 'masjidos' ) ) + '</p></div><button type="button" class="itmms-btn itmms-btn-primary" data-copy-generated-duas-shortcode>' + esc( __( 'Copy Shortcode', 'masjidos' ) ) + '</button></div>' +
			'<div class="itmms-builder-grid">' +
				'<label><span>' + esc( __( 'Design', 'masjidos' ) ) + '</span><select data-duas-builder-design><option value="cards">' + esc( __( 'Cards', 'masjidos' ) ) + '</option><option value="compact">' + esc( __( 'Compact', 'masjidos' ) ) + '</option></select></label>' +
				'<label><span>' + esc( __( 'Language', 'masjidos' ) ) + '</span><select data-duas-builder-language><option value="en">' + esc( __( 'English', 'masjidos' ) ) + '</option><option value="bn">' + esc( __( 'Bangla', 'masjidos' ) ) + '</option><option value="ar">' + esc( __( 'Arabic', 'masjidos' ) ) + '</option></select></label>' +
				'<label><span>' + esc( __( 'Category', 'masjidos' ) ) + '</span><select data-duas-builder-category><option value="all">' + esc( __( 'All', 'masjidos' ) ) + '</option><option value="daily">' + esc( __( 'Daily', 'masjidos' ) ) + '</option><option value="morning">' + esc( __( 'Morning', 'masjidos' ) ) + '</option><option value="evening">' + esc( __( 'Evening', 'masjidos' ) ) + '</option><option value="food">' + esc( __( 'Food', 'masjidos' ) ) + '</option><option value="sleep">' + esc( __( 'Sleep', 'masjidos' ) ) + '</option><option value="home">' + esc( __( 'Home', 'masjidos' ) ) + '</option><option value="masjid">' + esc( __( 'Masjid', 'masjidos' ) ) + '</option><option value="travel">' + esc( __( 'Travel', 'masjidos' ) ) + '</option><option value="rain">' + esc( __( 'Rain', 'masjidos' ) ) + '</option><option value="forgiveness">' + esc( __( 'Forgiveness', 'masjidos' ) ) + '</option><option value="quran">' + esc( __( 'Quranic', 'masjidos' ) ) + '</option><option value="protection">' + esc( __( 'Protection', 'masjidos' ) ) + '</option></select></label>' +
				'<label><span>' + esc( __( 'Limit', 'masjidos' ) ) + '</span><input type="number" min="1" max="12" data-duas-builder-limit value="4"></label>' +
				'<label><span>' + esc( __( 'Title', 'masjidos' ) ) + '</span><input type="text" data-duas-builder-title placeholder="' + esc( __( 'Optional custom title', 'masjidos' ) ) + '"></label>' +
				'<label class="itmms-builder-check"><input type="checkbox" data-duas-builder-source checked><span>' + esc( __( 'Show Source', 'masjidos' ) ) + '</span></label>' +
				'<label class="itmms-builder-check"><input type="checkbox" data-duas-builder-counter checked><span>' + esc( __( 'Show Counter', 'masjidos' ) ) + '</span></label>' +
				'<label class="itmms-builder-check"><input type="checkbox" data-duas-builder-share checked><span>' + esc( __( 'Show Share Button', 'masjidos' ) ) + '</span></label>' +
				'<label class="itmms-builder-check"><input type="checkbox" data-duas-builder-audio checked><span>' + esc( __( 'Show Audio Button', 'masjidos' ) ) + '</span></label>' +
			'</div>' +
			'<div class="itmms-builder-output"><code data-generated-duas-shortcode>[masjidos_duas_azkar]</code></div>' +
		'</section>' +
		'<section class="itmms-docs-section itmms-shortcode-builder">' +
			'<div class="itmms-builder-head"><div><h3>' + esc( __( 'Notice Widget Generator', 'masjidos' ) ) + '</h3><p>' + esc( __( 'Create a public notice list or compact ticker from your active announcements.', 'masjidos' ) ) + '</p></div><button type="button" class="itmms-btn itmms-btn-primary" data-copy-generated-announcement-shortcode>' + esc( __( 'Copy Shortcode', 'masjidos' ) ) + '</button></div>' +
			'<p class="itmms-docs-note">' + esc( __( 'Before testing this shortcode, publish at least one notice from the Notices screen and make sure its start time has arrived.', 'masjidos' ) ) + '</p>' +
			'<div class="itmms-builder-grid">' +
				'<label><span>' + esc( __( 'Design', 'masjidos' ) ) + '</span><select data-announcement-builder-design><option value="list">' + esc( __( 'List', 'masjidos' ) ) + '</option><option value="ticker">' + esc( __( 'Ticker', 'masjidos' ) ) + '</option><option value="banner">' + esc( __( 'Banner', 'masjidos' ) ) + '</option><option value="popup">' + esc( __( 'Popup Modal', 'masjidos' ) ) + '</option></select></label>' +
				'<label><span>' + esc( __( 'Language', 'masjidos' ) ) + '</span><select data-announcement-builder-language><option value="en">' + esc( __( 'English', 'masjidos' ) ) + '</option><option value="bn">' + esc( __( 'Bangla', 'masjidos' ) ) + '</option><option value="ar">' + esc( __( 'Arabic', 'masjidos' ) ) + '</option></select></label>' +
				'<label><span>' + esc( __( 'Type', 'masjidos' ) ) + '</span><select data-announcement-builder-type><option value="all">' + esc( __( 'All notices', 'masjidos' ) ) + '</option><option value="general">' + esc( __( 'General', 'masjidos' ) ) + '</option><option value="urgent">' + esc( __( 'Urgent', 'masjidos' ) ) + '</option><option value="jumuah">' + esc( __( 'Jumuah', 'masjidos' ) ) + '</option></select></label>' +
				'<label><span>' + esc( __( 'Limit', 'masjidos' ) ) + '</span><input type="number" min="1" max="20" value="5" data-announcement-builder-limit></label>' +
				'<label><span>' + esc( __( 'Title', 'masjidos' ) ) + '</span><input type="text" data-announcement-builder-title placeholder="' + esc( __( 'Optional custom title', 'masjidos' ) ) + '"></label>' +
				'<label class="itmms-builder-check"><input type="checkbox" data-announcement-builder-date checked><span>' + esc( __( 'Show Date', 'masjidos' ) ) + '</span></label>' +
			'</div>' +
			'<div class="itmms-builder-output"><code data-generated-announcement-shortcode>[masjidos_announcements]</code></div>' +
		'</section>' +
		'<section class="itmms-docs-section itmms-shortcode-builder">' +
			'<div class="itmms-builder-head"><div><h3>' + esc( __( 'Events Generator', 'masjidos' ) ) + '</h3><p>' + esc( __( 'Create a public events feed shortcode for community pages or mosque notice boards.', 'masjidos' ) ) + '</p></div><button type="button" class="itmms-btn itmms-btn-primary" data-copy-generated-events-shortcode>' + esc( __( 'Copy Shortcode', 'masjidos' ) ) + '</button></div>' +
			'<div class="itmms-builder-grid">' +
				'<label><span>' + esc( __( 'Design', 'masjidos' ) ) + '</span><select data-events-builder-design><option value="list">' + esc( __( 'List', 'masjidos' ) ) + '</option></select></label>' +
				'<label><span>' + esc( __( 'Language', 'masjidos' ) ) + '</span><select data-events-builder-language><option value="en">' + esc( __( 'English', 'masjidos' ) ) + '</option><option value="bn">' + esc( __( 'Bangla', 'masjidos' ) ) + '</option><option value="ar">' + esc( __( 'Arabic', 'masjidos' ) ) + '</option></select></label>' +
				'<label><span>' + esc( __( 'Limit', 'masjidos' ) ) + '</span><input type="number" min="1" max="20" value="5" data-events-builder-limit></label>' +
				'<label><span>' + esc( __( 'Title', 'masjidos' ) ) + '</span><input type="text" data-events-builder-title placeholder="' + esc( __( 'Optional custom title', 'masjidos' ) ) + '"></label>' +
			'</div>' +
			'<div class="itmms-builder-output"><code data-generated-events-shortcode>[masjidos_events]</code></div>' +
		'</section>';
	}

	function monthOptionsHtml() {
		return [
			[ '1', __( 'January', 'masjidos' ) ],
			[ '2', __( 'February', 'masjidos' ) ],
			[ '3', __( 'March', 'masjidos' ) ],
			[ '4', __( 'April', 'masjidos' ) ],
			[ '5', __( 'May', 'masjidos' ) ],
			[ '6', __( 'June', 'masjidos' ) ],
			[ '7', __( 'July', 'masjidos' ) ],
			[ '8', __( 'August', 'masjidos' ) ],
			[ '9', __( 'September', 'masjidos' ) ],
			[ '10', __( 'October', 'masjidos' ) ],
			[ '11', __( 'November', 'masjidos' ) ],
			[ '12', __( 'December', 'masjidos' ) ]
		].map( function ( month ) {
			return '<option value="' + month[0] + '">' + month[1] + '</option>';
		} ).join( '' );
	}

	function pasteItem( title, text ) {
		return '<div><b>' + esc( title ) + '</b><span>' + esc( text ) + '</span></div>';
	}

	function proDesignCard( title, shortcode ) {
		return '<article class="itmms-pro-design-card">' +
			'<span>Pro</span>' +
			'<h4>' + esc( title ) + '</h4>' +
			'<code>' + esc( shortcode ) + '</code>' +
			'<p>' + esc( __( 'Available when MasjidOS Pro is installed and active.', 'masjidos' ) ) + '</p>' +
		'</article>';
	}

	function checkItem( id, title, text, done, auto ) {
		return '<label class="itmms-docs-check' + ( done ? ' is-done' : '' ) + ( auto ? ' is-auto' : '' ) + '">' +
			'<input type="checkbox" data-docs-check="' + esc( id ) + '"' + ( done ? ' checked' : '' ) + ( auto ? ' disabled' : '' ) + '>' +
			'<span class="itmms-docs-check__body">' +
				'<b>' + esc( title ) + '</b>' +
				'<span>' + esc( text ) + '</span>' +
				( auto ? '<em>' + esc( __( 'Auto from settings', 'masjidos' ) ) + '</em>' : '' ) +
			'</span>' +
		'</label>';
	}

	function docRow( attr, type, fallback, note ) {
		return '<div><code>' + esc( attr ) + '</code><span>' + esc( type ) + '</span><span>' + esc( fallback ) + '</span><p>' + esc( note ) + '</p></div>';
	}

	function activeModuleCount() {
		var state = window.itmms.state;
		var active = state.settings.modules || {};
		return Object.keys( active ).filter( function ( key ) { return active[ key ]; } ).length;
	}

	function eventDocsSection() {
		return '<div class="itmms-docs-grid">' +
			docCard( __( 'Events List', 'masjidos' ), __( 'Show upcoming community gatherings, lectures, and special prayers.', 'masjidos' ), '[masjidos_events]', [
				__( 'First create and schedule an event from MasjidOS > Events', 'masjidos' ),
				__( 'Islamic holidays automatically calculated and merged using local Hijri engine', 'masjidos' ),
				__( 'Recurring Jumuah automatically calculated and added dynamically', 'masjidos' ),
				__( 'Includes Add to Calendar action (.ics files) supporting Google & Apple Calendar', 'masjidos' ),
				__( 'Shows dynamic X days remaining reminder badges', 'masjidos' ),
				__( 'Displays event featured image at card header side', 'masjidos' ),
				__( 'Automatically hides past events based on their end time', 'masjidos' )
			] ) +
			docCard( __( 'Limit Events', 'masjidos' ), __( 'Limit the number of events displayed on the page.', 'masjidos' ), '[masjidos_events limit="3"]', [
				__( 'Defaults to 5 events', 'masjidos' ),
				__( 'Maximum limit is 20', 'masjidos' )
			] ) +
			docCard( __( 'Bangla Events', 'masjidos' ), __( 'Translate event labels and titles into Bangla.', 'masjidos' ), '[masjidos_events language="bn"]', [
				__( 'Changes headers, dates, and location prefixes', 'masjidos' ),
				__( 'Keeps your custom event title and description unchanged', 'masjidos' )
			] ) +
		'</div>';
	}

	function eventAttributesSection() {
		return '<section class="itmms-docs-section">' +
			'<h3>' + esc( __( 'Event shortcode attributes', 'masjidos' ) ) + '</h3>' +
			'<div class="itmms-docs-table">' +
				docRow( 'title', __( 'Text', 'masjidos' ), __( 'Upcoming Events', 'masjidos' ), __( 'Changes the events list heading.', 'masjidos' ) ) +
				docRow( 'design', 'list', 'list', __( 'Selects the layout style.', 'masjidos' ) ) +
				docRow( 'language', 'en/bn/ar', 'en', __( 'Changes public widget labels.', 'masjidos' ) ) +
				docRow( 'limit', '1-20', '5', __( 'Limits the number of events shown.', 'masjidos' ) ) +
			'</div>' +
		'</section>';
	}

	function khutbahArchiveAttributesSection() {
		return '<section class="itmms-docs-section">' +
			'<h3>' + esc( __( 'Minbar shortcode attributes', 'masjidos' ) ) + '</h3>' +
			'<div class="itmms-docs-table">' +
				docRow( 'title', __( 'Text', 'masjidos' ), __( 'Jumuah Khutbah Archive', 'masjidos' ), __( 'Changes the archive heading.', 'masjidos' ) ) +
				docRow( 'language', 'en/bn/ar', 'en', __( 'Changes public widget labels.', 'masjidos' ) ) +
				docRow( 'limit', '1-100', '12', __( 'Limits the number of khutbahs shown.', 'masjidos' ) ) +
				docRow( 'category', 'aqeedah/akhlaq/fiqh/…', '', __( 'Optional category filter for the archive.', 'masjidos' ) ) +
			'</div>' +
			'<p class="itmms-docs-note">' + esc( __( 'Also available: [masjidos_khatib_this_week], [masjidos_upcoming_khutbah], [masjidos_khutbah_search]. Manage content in Admin → Minbar.', 'masjidos' ) ) + '</p>' +
		'</section>';
	}

	function minbarDocsSection() {
		return '<section class="itmms-docs-section itmms-docs-paste">' +
			'<h3>' + esc( __( 'How to use Minbar (Friday tools)', 'masjidos' ) ) + '</h3>' +
			'<p class="itmms-docs-note">' + esc( __( 'Minbar is for khatibs and Friday planning. Start simple: one schedule entry, then later add archive or sermon notes.', 'masjidos' ) ) + '</p>' +
			'<div class="itmms-paste-grid">' +
				pasteItem( __( 'Overview', 'masjidos' ), __( 'Open Minbar > Overview for a quick picture of this week\'s khatib and upcoming topics.', 'masjidos' ) ) +
				pasteItem( __( 'Schedule', 'masjidos' ), __( 'Add who will give khutbah on which Friday. This powers [masjidos_khatib_this_week] and upcoming lists.', 'masjidos' ) ) +
				pasteItem( __( 'Planner', 'masjidos' ), __( 'Park future topic ideas before they are assigned to a Friday.', 'masjidos' ) ) +
				pasteItem( __( 'Archive', 'masjidos' ), __( 'Save past sermons with audio/PDF so visitors can search them later.', 'masjidos' ) ) +
				pasteItem( __( 'Sermon Builder', 'masjidos' ), __( 'Draft outline notes for a khutbah. Optional — skip if you only need public schedule widgets.', 'masjidos' ) ) +
				pasteItem( __( 'References', 'masjidos' ), __( 'Keep ayah, hadith, or notes linked to a sermon for your own preparation.', 'masjidos' ) ) +
			'</div>' +
		'</section>' +
		'<div class="itmms-docs-grid">' +
			docCard( __( 'This Week\'s Khatib', 'masjidos' ), __( 'Show this Friday\'s scheduled khatib and topic.', 'masjidos' ), '[masjidos_khatib_this_week]', [
				__( 'Reads Minbar Schedule for the next Friday', 'masjidos' ),
				__( 'Good for homepage or Jumuah pages', 'masjidos' )
			] ) +
			docCard( __( 'Upcoming Khutbahs', 'masjidos' ), __( 'List upcoming Friday topics from schedule and planner.', 'masjidos' ), '[masjidos_upcoming_khutbah]', [
				__( 'Useful under the Jumuah widget', 'masjidos' ),
				__( 'limit controls how many rows show', 'masjidos' )
			] ) +
			docCard( __( 'Khutbah Archive', 'masjidos' ), __( 'Public searchable list of past sermons.', 'masjidos' ), '[masjidos_khutbah_archive]', [
				__( 'Only public archive entries are shown', 'masjidos' ),
				__( 'Supports filters, audio, and PDF links when you add them', 'masjidos' )
			] ) +
			docCard( __( 'Khutbah Search', 'masjidos' ), __( 'Compact search box for the archive.', 'masjidos' ), '[masjidos_khutbah_search]', [
				__( 'Place beside the archive on a dedicated page', 'masjidos' ),
				__( 'Only published entries are searchable', 'masjidos' )
			] ) +
		'</div>';
	}

	function articlesHowToSection() {
		return '<section class="itmms-docs-section itmms-docs-paste">' +
			'<h3>' + esc( __( 'How to publish an Islamic Article', 'masjidos' ) ) + '</h3>' +
			'<p class="itmms-docs-note">' + esc( __( 'Articles work like a normal WordPress post, with extra Islamic fields on the side.', 'masjidos' ) ) + '</p>' +
			'<div class="itmms-paste-grid">' +
				pasteItem( '1. ' + __( 'Open Articles', 'masjidos' ), __( 'In the WordPress admin sidebar under MasjidOS, open Articles > Add New.', 'masjidos' ) ) +
				pasteItem( '2. ' + __( 'Write the basics', 'masjidos' ), __( 'Add Title, write Content, set a Featured Image, and choose a Category (for example Fiqh or Akhlaq).', 'masjidos' ) ) +
				pasteItem( '3. ' + __( 'Fill Article Details', 'masjidos' ), __( 'In the MasjidOS panel set Language, Author, optional Translator, Source, Key takeaway, External URL, and Audio URL.', 'masjidos' ) ) +
				pasteItem( '4. ' + __( 'Publish & show', 'masjidos' ), __( 'Click Publish. Then paste [masjidos_articles] on any page, or open Features > Islamic Articles to copy options.', 'masjidos' ) ) +
			'</div>' +
		'</section>';
	}

	function articlesDocsSection() {
		return '<div class="itmms-docs-grid">' +
			docCard( __( 'Articles List', 'masjidos' ), __( 'Show published Islamic articles on a public page.', 'masjidos' ), '[masjidos_articles]', [
				__( 'Shows title, image, category, and Read button', 'masjidos' ),
				__( 'Reading time is estimated automatically', 'masjidos' ),
				__( 'Optional author, source, and language badges when set', 'masjidos' )
			] ) +
			docCard( __( 'Bangla Labels', 'masjidos' ), __( 'Use Bangla labels on the list widget.', 'masjidos' ), '[masjidos_articles language="bn"]', [
				__( 'Changes buttons and empty-state text', 'masjidos' ),
				__( 'Does not translate your article title or body', 'masjidos' )
			] ) +
			docCard( __( 'Category Filter', 'masjidos' ), __( 'Show only one article category by slug.', 'masjidos' ), '[masjidos_articles category="fiqh"]', [
				__( 'Use the category slug from Articles > Categories', 'masjidos' ),
				__( 'Leave empty to show all categories', 'masjidos' )
			] ) +
			docCard( __( 'Hide Excerpt', 'masjidos' ), __( 'Show a tighter card without the short summary.', 'masjidos' ), '[masjidos_articles excerpt="no"]', [
				__( 'Useful in sidebars or dense layouts', 'masjidos' )
			] ) +
		'</div>';
	}

	function articlesAttributesSection() {
		return '<section class="itmms-docs-section">' +
			'<h3>' + esc( __( 'Articles shortcode attributes', 'masjidos' ) ) + '</h3>' +
			'<div class="itmms-docs-table">' +
				docRow( 'title', __( 'Text', 'masjidos' ), __( 'Islamic Articles', 'masjidos' ), __( 'Changes the list heading.', 'masjidos' ) ) +
				docRow( 'language', 'en/bn/ar', 'en', __( 'Changes widget labels. Article content stays as you wrote it.', 'masjidos' ) ) +
				docRow( 'category', __( 'slug', 'masjidos' ), '', __( 'Optional category slug filter.', 'masjidos' ) ) +
				docRow( 'limit', '1-24', '6', __( 'How many articles to list.', 'masjidos' ) ) +
				docRow( 'excerpt', 'yes/no', 'yes', __( 'Shows or hides the short summary under each title.', 'masjidos' ) ) +
			'</div>' +
		'</section>';
	}

	function educationDocsSection() {
		return '<div class="itmms-docs-grid">' +
			docCard( __( 'Quran Verse', 'masjidos' ), __( 'Daily random verse from the Quran in Arabic, English, and Bangla.', 'masjidos' ), '[masjidos_quran_verse]', [
				__( 'Refreshes every day automatically', 'masjidos' ),
				__( 'Includes share button and Tafsir reference link', 'masjidos' )
			] ) +
			docCard( __( 'Hadith of Day', 'masjidos' ), __( 'Daily random Hadith from authentic collections.', 'masjidos' ), '[masjidos_hadith]', [
				__( 'Refreshes every day automatically', 'masjidos' ),
				__( 'Includes share button and source references', 'masjidos' )
			] ) +
			docCard( __( '99 Names of Allah', 'masjidos' ), __( 'Show 99 Names of Allah (Asmaul Husna) in a responsive grid.', 'masjidos' ), '[masjidos_allah_names]', [
				__( 'Displays Arabic, transliteration, and meanings', 'masjidos' ),
				__( 'Includes hover highlights and responsive structure', 'masjidos' )
			] ) +
			docCard( __( 'Audio Quran Player', 'masjidos' ), __( 'Audio player to stream beautiful recitations of Surahs.', 'masjidos' ), '[masjidos_audio_quran]', [
				__( 'Stream high quality Surah recitations directly', 'masjidos' ),
				__( 'Choose from a selector dropdown in public widgets', 'masjidos' )
			] ) +
		'</div>';
	}

	function quranVerseAttributesSection() {
		return '<section class="itmms-docs-section">' +
			'<h3>' + esc( __( 'Quran Verse shortcode attributes', 'masjidos' ) ) + '</h3>' +
			'<div class="itmms-docs-table">' +
				docRow( 'title', __( 'Text', 'masjidos' ), __( 'Quran Verse of the Day', 'masjidos' ), __( 'Changes the widget heading.', 'masjidos' ) ) +
				docRow( 'language', 'en/bn', 'en', __( 'Changes public widget translations.', 'masjidos' ) ) +
			'</div>' +
		'</section>';
	}

	function hadithAttributesSection() {
		return '<section class="itmms-docs-section">' +
			'<h3>' + esc( __( 'Hadith shortcode attributes', 'masjidos' ) ) + '</h3>' +
			'<div class="itmms-docs-table">' +
				docRow( 'title', __( 'Text', 'masjidos' ), __( 'Hadith of the Day', 'masjidos' ), __( 'Changes the widget heading.', 'masjidos' ) ) +
				docRow( 'language', 'en/bn', 'en', __( 'Changes public widget translations.', 'masjidos' ) ) +
			'</div>' +
		'</section>';
	}

	function allahNamesAttributesSection() {
		return '<section class="itmms-docs-section">' +
			'<h3>' + esc( __( 'Names of Allah shortcode attributes', 'masjidos' ) ) + '</h3>' +
			'<div class="itmms-docs-table">' +
				docRow( 'title', __( 'Text', 'masjidos' ), __( '99 Names of Allah', 'masjidos' ), __( 'Changes the widget heading.', 'masjidos' ) ) +
				docRow( 'language', 'en/bn', 'en', __( 'Changes translation meaning labels.', 'masjidos' ) ) +
			'</div>' +
		'</section>';
	}

	function audioQuranAttributesSection() {
		return '<section class="itmms-docs-section">' +
			'<h3>' + esc( __( 'Audio Quran shortcode attributes', 'masjidos' ) ) + '</h3>' +
			'<div class="itmms-docs-table">' +
				docRow( 'title', __( 'Text', 'masjidos' ), __( 'Audio Quran Player', 'masjidos' ), __( 'Changes the player heading.', 'masjidos' ) ) +
				docRow( 'language', 'en/bn/ar', 'en', __( 'Changes player option labels.', 'masjidos' ) ) +
			'</div>' +
		'</section>';
	}
} )();
