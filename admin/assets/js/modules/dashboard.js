/**
 * MasjidOS Admin - Dashboard and Modules Module.
 */
( function () {
	'use strict';

	window.itmms = window.itmms || {};
	var __ = window.wp.i18n.__;
	var _n = window.wp.i18n._n;
	var sprintf = window.wp.i18n.sprintf;

	// Import shared helpers
	var esc = window.itmms.esc;
	var icon = window.itmms.icon;

	// Expose dashboard module
	window.itmms.dashboard = {
		dashboardHtml: dashboardHtml,
		modulesHtml: modulesHtml,
		prayerNameLabel: prayerNameLabel
	};

	function statCard( className, iconName, value, label, note ) {
		return '<div class="itmms-stat-card ' + className + '">' +
			'<div class="itmms-stat-icon">' + icon( iconName ) + '</div>' +
			'<div class="itmms-stat-value">' + esc( value ) + '</div>' +
			'<div class="itmms-stat-label">' + esc( label ) + '</div>' +
			'<div class="itmms-stat-note">' + esc( note ) + '</div>' +
		'</div>';
	}

	function prayerRows() {
		var state = window.itmms.state;
		var prayers = state.prayers && state.prayers.length ? state.prayers : [
			{ name: __( 'Fajr', 'masjidos' ), arabic: 'Fajr', time: '4:18 AM', iqamah: '', current: false },
			{ name: __( 'Sunrise', 'masjidos' ), arabic: 'Sunrise', time: '5:42 AM', iqamah: '', current: false },
			{ name: __( 'Dhuhr', 'masjidos' ), arabic: 'Dhuhr', time: '12:01 PM', iqamah: '', current: false },
			{ name: __( 'Asr', 'masjidos' ), arabic: 'Asr', time: '4:12 PM', iqamah: '', current: true },
			{ name: __( 'Maghrib', 'masjidos' ), arabic: 'Maghrib', time: '6:48 PM', iqamah: '', current: false },
			{ name: __( 'Isha', 'masjidos' ), arabic: 'Isha', time: '8:15 PM', iqamah: '', current: false }
		];

		return prayers.map( function ( prayer ) {
			var offset = Number( prayer.offset || 0 );
			var offsetBadge = offset
				? '<span class="itmms-offset-badge" title="' + esc( sprintf( __( 'Base time: %s', 'masjidos' ), prayer.base_time || prayer.time ) ) + '">' + ( offset > 0 ? '+' : '' ) + esc( offset ) + 'm</span>'
				: '';

			return '<div class="itmms-prayer-row' + ( prayer.current ? ' current' : '' ) + '">' +
				'<span class="itmms-prayer-name">' + esc( prayerNameLabel( prayer ) ) + '</span>' +
				'<span class="itmms-prayer-iqamah">' + ( prayer.iqamah ? '<small>' + esc( __( 'Iqamah', 'masjidos' ) ) + '</small><b>' + esc( prayer.iqamah ) + '</b>' : '<small>' + esc( __( 'Iqamah', 'masjidos' ) ) + '</small><em>' + esc( __( 'Not set', 'masjidos' ) ) + '</em>' ) + '</span>' +
				'<span class="itmms-prayer-time">' + esc( prayer.time ) + offsetBadge + ( prayer.current ? '<b>' + esc( __( 'Now', 'masjidos' ) ) + '</b>' : '' ) + '</span>' +
			'</div>';
		} ).join( '' );
	}

	function prayerNameLabel( prayer ) {
		return {
			fajr: __( 'Fajr', 'masjidos' ),
			sunrise: __( 'Sunrise', 'masjidos' ),
			dhuhr: __( 'Dhuhr', 'masjidos' ),
			asr: __( 'Asr', 'masjidos' ),
			maghrib: __( 'Maghrib', 'masjidos' ),
			isha: __( 'Isha', 'masjidos' )
		}[ prayer.key ] || prayer.name;
	}

	function moduleCards() {
		var state = window.itmms.state;
		var active = state.settings.modules || {};
		return state.modules.map( function ( module ) {
			return '<article class="itmms-module-card">' +
				'<div class="itmms-module-card-top">' +
					'<div class="itmms-module-icon ' + esc( module.color ) + '">' + icon( module.icon ) + '</div>' +
					'<button class="itmms-toggle' + ( active[ module.key ] ? ' on' : '' ) + '" data-module="' + esc( module.key ) + '" aria-label="' + esc( sprintf( __( 'Toggle %s', 'masjidos' ), module.name ) ) + '"></button>' +
				'</div>' +
				'<h3>' + esc( module.name ) + '</h3>' +
				'<p>' + esc( module.description ) + '</p>' +
			'</article>';
		} ).join( '' );
	}

	function dashboardHtml() {
		var state = window.itmms.state;
		var nextName = state.nextPrayer ? prayerNameLabel( state.nextPrayer ) + ' - ' + state.nextPrayer.time : __( 'Calculating...', 'masjidos' );
		var liveNotice = state.dashboardAnnouncements[0] || state.announcements.filter( function ( notice ) { return 'active' === notice.status; } )[0];
		
		var noticeType = liveNotice ? window.itmms.announcements.announcementTypeLabel( liveNotice.announcement_type ) : __( 'Notice', 'masjidos' );
		var noticeText = liveNotice ? liveNotice.title + ( liveNotice.content ? ' - ' + liveNotice.content : '' ) : __( 'No active notices. Add one from the Notices screen.', 'masjidos' );

		return '<div class="itmms-dashboard-head"><div><h2>' + esc( __( 'Assalamu Alaikum', 'masjidos' ) ) + '</h2><p>' + esc( __( 'Live overview for your masjid operations.', 'masjidos' ) ) + '</p></div><div class="itmms-arabic">&#1576;&#1616;&#1587;&#1618;&#1605;&#1616; &#1575;&#1604;&#1604;&#1617;&#1614;&#1607;&#1616;</div></div>' +
			'<div class="itmms-ticker"><strong>' + esc( noticeType ) + '</strong><span>' + esc( noticeText ) + '</span></div>' +
			'<div class="itmms-countdown"><span><small>' + esc( __( 'Next Prayer', 'masjidos' ) ) + '</small><strong id="itmms-next-prayer">' + esc( nextName ) + '</strong></span><b id="itmms-countdown">00:00:00</b></div>' +
			'<div class="itmms-stats-grid">' +
				statCard( 'teal', 'megaphone', state.stats.announcements, __( 'Active Notices', 'masjidos' ), __( 'Published and in schedule', 'masjidos' ) ) +
				( state.settings.modules.events ? statCard( 'blue', 'calendar', state.stats.events || 0, __( 'Active Events', 'masjidos' ), __( 'Mosque calendar events', 'masjidos' ) ) : '' ) +
				statCard( 'blue', 'clock', ( state.prayerMeta || {} ).calculation_method || 'Karachi', __( 'Prayer Method', 'masjidos' ), __( 'Local calculation', 'masjidos' ) ) +
				statCard( 'gold', 'clock', ( state.prayerMeta || {} ).asr_method || 'Hanafi', __( 'Asr Method', 'masjidos' ), __( 'Saved preference', 'masjidos' ) ) +
				statCard( 'green', 'settings', ( state.prayerMeta || {} ).timezone || state.settings.timezone || 'Asia/Dhaka', __( 'Timezone', 'masjidos' ), __( 'Used across schedules', 'masjidos' ) ) +
			'</div>' +
			'<div class="itmms-content-grid"><article class="itmms-card"><header><h3>' + esc( __( 'Today\'s Prayer Times', 'masjidos' ) ) + '</h3><button class="itmms-link-btn" data-open-settings>' + esc( __( 'Edit', 'masjidos' ) ) + '</button></header><div>' + prayerRows() + '</div></article><div class="itmms-side-stack">' + jumuahCard() + prayerMetaCard() + '<article class="itmms-card"><header><h3>' + esc( __( 'System Health', 'masjidos' ) ) + '</h3></header><div class="itmms-health-list"><span><b>' + esc( state.stats.announcements ) + '</b> ' + esc( _n( 'active announcement', 'active announcements', Number( state.stats.announcements ), 'masjidos' ) ) + '</span>' + ( state.settings.modules.events ? '<span><b>' + esc( state.stats.events || 0 ) + '</b> ' + esc( _n( 'active event', 'active events', Number( state.stats.events || 0 ), 'masjidos' ) ) + '</span>' : '' ) + '<span><b>' + window.itmms.activeModuleCount() + '</b> ' + esc( _n( 'module enabled', 'modules enabled', window.itmms.activeModuleCount(), 'masjidos' ) ) + '</span><span><b id="itmms-hijri-date">...</b> ' + esc( __( 'Hijri date', 'masjidos' ) ) + '</span></div></article></div></div>';
	}

	function jumuahCard() {
		var state = window.itmms.state;
		var jumuah = state.settings.jumuah || {};
		
		var khatib = window.itmms.settings.normalizeKhatib( jumuah.khatib );
		var sessions = window.itmms.settings.normalizeJumuahSessions( jumuah );
		
		var first = sessions[0] || {};
		var enabled = jumuah.enabled !== false;
		return '<article class="itmms-card itmms-jumuah-card">' +
			'<header><h3>' + esc( __( 'Jumuah', 'masjidos' ) ) + '</h3><button class="itmms-link-btn" data-open-settings>' + esc( __( 'Configure', 'masjidos' ) ) + '</button></header>' +
			'<div class="itmms-jumuah-summary">' +
				'<span class="' + ( enabled ? 'is-enabled' : '' ) + '">' + esc( enabled ? __( 'Enabled', 'masjidos' ) : __( 'Disabled', 'masjidos' ) ) + '</span>' +
				'<div><small>' + esc( __( 'Khutbah', 'masjidos' ) ) + '</small><strong>' + esc( first.khutbah_time || '13:00' ) + '</strong></div>' +
				'<div><small>' + esc( __( 'Jamaat', 'masjidos' ) ) + '</small><strong>' + esc( first.jamaat_time || '13:30' ) + '</strong></div>' +
				( jumuah.topic ? '<p><b>' + esc( __( 'Topic', 'masjidos' ) ) + '</b> ' + esc( jumuah.topic ) + '</p>' : '' ) +
				( khatib.name ? '<p><b>' + esc( __( 'Khatib', 'masjidos' ) ) + '</b> ' + esc( khatib.name ) + '</p>' : '' ) +
			'</div>' +
		'</article>';
	}

	function prayerMetaCard() {
		var state = window.itmms.state;
		var meta = state.prayerMeta || {};
		var qibla = meta.qibla_direction == null ? '...' : meta.qibla_direction + '°';
		var location = meta.location || __( 'Not set', 'masjidos' );
		var method = meta.calculation_method || 'Karachi';
		var timezone = meta.timezone || state.settings.timezone || 'Asia/Dhaka';
		var coords = meta.latitude && meta.longitude ? Number( meta.latitude ).toFixed( 4 ) + ', ' + Number( meta.longitude ).toFixed( 4 ) : __( 'Not set', 'masjidos' );

		return '<article class="itmms-card itmms-prayer-meta-card">' +
			'<header><h3>' + esc( __( 'Prayer Setup', 'masjidos' ) ) + '</h3><button class="itmms-link-btn" data-open-settings>' + esc( __( 'Configure', 'masjidos' ) ) + '</button></header>' +
			'<div class="itmms-qibla-box">' +
				'<div class="itmms-qibla-compass"><span style="transform:rotate(' + esc( meta.qibla_direction || 0 ) + 'deg)"></span></div>' +
				'<div><small>' + esc( __( 'Qibla Direction', 'masjidos' ) ) + '</small><strong>' + esc( qibla ) + '</strong><p>' + esc( __( 'Clockwise from true north', 'masjidos' ) ) + '</p></div>' +
			'</div>' +
			'<div class="itmms-meta-list">' +
				'<span><b>' + esc( __( 'Location', 'masjidos' ) ) + '</b><em>' + esc( location ) + '</em></span>' +
				'<span><b>' + esc( __( 'Coordinates', 'masjidos' ) ) + '</b><em>' + esc( coords ) + '</em></span>' +
				'<span><b>' + esc( __( 'Method', 'masjidos' ) ) + '</b><em>' + esc( method ) + '</em></span>' +
				'<span><b>' + esc( __( 'Asr', 'masjidos' ) ) + '</b><em>' + esc( meta.asr_method || 'Hanafi' ) + '</em></span>' +
				'<span><b>' + esc( __( 'Timezone', 'masjidos' ) ) + '</b><em>' + esc( timezone ) + '</em></span>' +
			'</div>' +
		'</article>';
	}

	function modulesHtml() {
		return '<div class="itmms-section-heading"><h2>' + esc( __( 'Module Manager', 'masjidos' ) ) + '</h2><p>' + esc( __( 'Turn modules on only when your masjid needs them. Disabled modules stay light.', 'masjidos' ) ) + '</p></div><div class="itmms-modules-grid">' + moduleCards() + '</div>';
	}
} )();
