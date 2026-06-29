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
			'<section class="itmms-docs-hero">' +
				'<div><span>' + esc( __( 'MasjidOS Documentation', 'masjidos' ) ) + '</span><h2>' + esc( __( 'Shortcodes, setup notes, and usage recipes', 'masjidos' ) ) + '</h2><p>' + esc( __( 'Copy a shortcode, paste it into a WordPress page, and keep this page as your built-in memory.', 'masjidos' ) ) + '</p></div>' +
				'<button class="itmms-btn itmms-btn-primary" data-open-settings>' + esc( __( 'Prayer Settings', 'masjidos' ) ) + '</button>' +
			'</section>' +
			'<div class="itmms-doc-tabs" role="tablist">' +
				docTabButton( 'overview', __( 'Overview', 'masjidos' ), true ) +
				docTabButton( 'generators', __( 'Generators', 'masjidos' ), false ) +
				docTabButton( 'prayer', __( 'Prayer', 'masjidos' ), false ) +
				docTabButton( 'jumuah', __( 'Jumuah', 'masjidos' ), false ) +
				docTabButton( 'notices', __( 'Notices', 'masjidos' ), false ) +
				docTabButton( 'events', __( 'Events', 'masjidos' ), false ) +
				docTabButton( 'pro', 'Pro', false ) +
				docTabButton( 'reference', __( 'Reference', 'masjidos' ), false ) +
			'</div>' +
			'<div class="itmms-doc-tab-panels">' +
				docPanel( 'overview', true, pasteShortcodeSection() + jumuahDataSection() + checklistSection() + roadmapSection() ) +
				docPanel( 'generators', false, shortcodeBuilderHtml() ) +
				docPanel( 'prayer', false, prayerDocsSection() ) +
				docPanel( 'jumuah', false, jumuahDocsSection() ) +
				docPanel( 'notices', false, announcementDocsSection() ) +
				docPanel( 'events', false, eventDocsSection() ) +
				docPanel( 'pro', false, proDocsSection() ) +
				docPanel( 'reference', false, prayerAttributesSection() + jumuahAttributesSection() + monthlyAttributesSection() + announcementAttributesSection() + eventAttributesSection() ) +
			'</div>' +
		'</div>';
	}

	function docTabButton( key, label, active ) {
		return '<button type="button" class="itmms-doc-tab' + ( active ? ' active' : '' ) + '" data-doc-tab="' + esc( key ) + '">' + esc( label ) + '</button>';
	}

	function docPanel( key, active, content ) {
		return '<div class="itmms-doc-tab-panel' + ( active ? ' active' : '' ) + '" data-doc-panel="' + esc( key ) + '">' + content + '</div>';
	}

	function pasteShortcodeSection() {
		return '<section class="itmms-docs-section itmms-docs-paste">' +
			'<h3>' + esc( __( 'Where to paste shortcodes', 'masjidos' ) ) + '</h3>' +
			'<div class="itmms-paste-grid">' +
				pasteItem( __( 'WordPress Page', 'masjidos' ), __( 'Pages > Add New, add a Shortcode block, paste the shortcode, then publish.', 'masjidos' ) ) +
				pasteItem( __( 'Post or Article', 'masjidos' ), __( 'Posts > Add New, place the shortcode where the prayer widget should appear.', 'masjidos' ) ) +
				pasteItem( __( 'Widget Area', 'masjidos' ), __( 'Appearance > Widgets, add a Shortcode block in a sidebar or footer area.', 'masjidos' ) ) +
				pasteItem( 'Elementor', __( 'Drag the Shortcode widget into your layout and paste the shortcode there.', 'masjidos' ) ) +
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
			docCard( __( 'Custom Title', 'masjidos' ), __( 'Change the widget heading for a homepage or masjid page.', 'masjidos' ), '[masjidos_prayer_times title="Today at Powerup Masjid"]', [
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
			docCard( __( 'Jumuah Without Meta', 'masjidos' ), __( 'Show only the public Jumuah session times and header.', 'masjidos' ), '[masjidos_jumuah meta="no"]', [
				__( 'Hides khatib profile, topic, language, and location', 'masjidos' ),
				__( 'Useful for very simple pages', 'masjidos' )
			] ) +
		'</div>';
	}

	function proDocsSection() {
		return '<section class="itmms-docs-section">' +
			'<h3>' + esc( __( 'Pro design presets', 'masjidos' ) ) + '</h3>' +
			'<p class="itmms-docs-note">' + esc( __( 'The free plugin only shows these as available in MasjidOS Pro. The actual Pro design code ships from the Pro plugin, not from this free plugin.', 'masjidos' ) ) + '</p>' +
			'<div class="itmms-pro-design-grid">' +
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
			'</div>' +
		'</section>';
	}

	function checklistSection() {
		return '<section class="itmms-docs-section">' +
			'<h3>' + esc( __( 'Recommended setup checklist', 'masjidos' ) ) + '</h3>' +
			'<div class="itmms-docs-checklist">' +
				checkItem( __( 'Set Timezone', 'masjidos' ), __( 'For Bangladesh use Asia/Dhaka. Wrong timezone is the most common cause of wrong prayer times.', 'masjidos' ) ) +
				checkItem( __( 'Set Coordinates', 'masjidos' ), __( 'Copy latitude and longitude from Google Maps for the exact masjid location.', 'masjidos' ) ) +
				checkItem( __( 'Choose Method', 'masjidos' ), __( 'For Bangladesh, Karachi + Hanafi is a sensible default.', 'masjidos' ) ) +
				checkItem( __( 'Adjust Official Times', 'masjidos' ), __( 'Use Prayer Time Adjustments if the local committee timetable differs by a few minutes.', 'masjidos' ) ) +
				checkItem( __( 'Set Iqamah Times', 'masjidos' ), __( 'Add jamaat start times for Fajr, Dhuhr, Asr, Maghrib, and Isha.', 'masjidos' ) ) +
				checkItem( __( 'Set Jumuah Details', 'masjidos' ), __( 'Add sessions, topic, language, khatib profile, and notice from Settings > Jumuah Settings, then publish [masjidos_jumuah].', 'masjidos' ) ) +
				checkItem( __( 'Publish Shortcode', 'masjidos' ), __( 'Add [masjidos_prayer_times] to the masjid homepage or a dedicated prayer times page.', 'masjidos' ) ) +
			'</div>' +
		'</section>';
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

	function announcementAttributesSection() {
		return '<section class="itmms-docs-section">' +
			'<h3>' + esc( __( 'Announcement shortcode attributes', 'masjidos' ) ) + '</h3>' +
			'<div class="itmms-docs-table">' +
				docRow( 'design', 'list/ticker', 'list', __( 'Selects a free notice design. Pro designs are supplied by the Pro plugin.', 'masjidos' ) ) +
				docRow( 'language', 'en/bn/ar', 'en', __( 'Changes widget labels.', 'masjidos' ) ) +
				docRow( 'type', 'all/general/urgent/jumuah', 'all', __( 'Filters public notices by type.', 'masjidos' ) ) +
				docRow( 'limit', '1-20', '5', __( 'Limits the number of active notices shown.', 'masjidos' ) ) +
				docRow( 'title', __( 'Text', 'masjidos' ), __( 'Masjid Notices', 'masjidos' ), __( 'Changes the list design heading.', 'masjidos' ) ) +
				docRow( 'show_date', 'yes/no', 'yes', __( 'Shows or hides notice start dates.', 'masjidos' ) ) +
			'</div>' +
		'</section>';
	}

	function roadmapSection() {
		return '<section class="itmms-docs-section">' +
			'<h3>' + esc( __( 'What is coming next', 'masjidos' ) ) + '</h3>' +
			'<div class="itmms-docs-roadmap">' +
				'<span>' + esc( __( 'Prayer times block/widget presets', 'masjidos' ) ) + '</span>' +
				'<span>' + esc( __( 'Jumuah design presets', 'masjidos' ) ) + '</span>' +
				'<span>' + esc( __( 'Monthly prayer timetable', 'masjidos' ) ) + '</span>' +
				'<span>' + esc( __( 'PDF export for notice boards', 'masjidos' ) ) + '</span>' +
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
				'<label><span>' + esc( __( 'Design', 'masjidos' ) ) + '</span><select data-monthly-builder-design><option value="table">' + esc( __( 'Table', 'masjidos' ) ) + '</option><option value="compact">' + esc( __( 'Compact', 'masjidos' ) ) + '</option><option value="premium-print">' + esc( __( 'Premium Print (Pro)', 'masjidos' ) ) + '</option><option value="mosque-board">' + esc( __( 'Mosque Board (Pro)', 'masjidos' ) ) + '</option><option value="ramadan-monthly">' + esc( __( 'Ramadan Monthly (Pro)', 'masjidos' ) ) + '</option></select></label>' +
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
			'<div class="itmms-builder-head"><div><h3>' + esc( __( 'Notice Widget Generator', 'masjidos' ) ) + '</h3><p>' + esc( __( 'Create a public notice list or compact ticker from your active announcements.', 'masjidos' ) ) + '</p></div><button type="button" class="itmms-btn itmms-btn-primary" data-copy-generated-announcement-shortcode>' + esc( __( 'Copy Shortcode', 'masjidos' ) ) + '</button></div>' +
			'<p class="itmms-docs-note">' + esc( __( 'Before testing this shortcode, publish at least one notice from the Notices screen and make sure its start time has arrived.', 'masjidos' ) ) + '</p>' +
			'<div class="itmms-builder-grid">' +
				'<label><span>' + esc( __( 'Design', 'masjidos' ) ) + '</span><select data-announcement-builder-design><option value="list">' + esc( __( 'List', 'masjidos' ) ) + '</option><option value="ticker">' + esc( __( 'Ticker', 'masjidos' ) ) + '</option><option value="digital-board">' + esc( __( 'Digital Board (Pro)', 'masjidos' ) ) + '</option><option value="ramadan-banner">' + esc( __( 'Ramadan Banner (Pro)', 'masjidos' ) ) + '</option></select></label>' +
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

	function checkItem( title, text ) {
		return '<div><b>' + esc( title ) + '</b><span>' + esc( text ) + '</span></div>';
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
				__( 'Automatically hides past events based on their end time', 'masjidos' ),
				__( 'Sorted chronologically by event start date', 'masjidos' ),
				__( 'Displays location, date range, and descriptions', 'masjidos' )
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
} )();
