/**
 * MasjidOS Admin - Dashboard and Modules Module.
 */
( function () {
	'use strict';

	window.itmms = window.itmms || {};
	var __ = window.wp.i18n.__;
	var _n = window.wp.i18n._n;
	var sprintf = window.wp.i18n.sprintf;

	var esc = window.itmms.esc;
	var icon = window.itmms.icon;

	window.itmms.dashboard = {
		dashboardHtml: dashboardHtml,
		modulesHtml: modulesHtml,
		prayerNameLabel: prayerNameLabel,
		bindDashboardEvents: bindDashboardEvents
	};

	function navAttrs( tab, opts ) {
		opts = opts || {};
		var attrs = ' data-tab="' + esc( tab ) + '"';
		if ( opts.minbarTab ) {
			attrs += ' data-minbar-tab="' + esc( opts.minbarTab ) + '"';
		}
		if ( opts.settingsTab ) {
			attrs += ' data-settings-tab="' + esc( opts.settingsTab ) + '"';
		}
		return attrs;
	}

	function statCard( className, iconName, value, label, note, nav ) {
		return '<button type="button" class="itmms-stat-card itmms-stat-card--action ' + className + '"' + navAttrs( nav.tab, nav ) + '>' +
			'<div class="itmms-stat-icon">' + icon( iconName ) + '</div>' +
			'<div class="itmms-stat-value">' + esc( value ) + '</div>' +
			'<div class="itmms-stat-label">' + esc( label ) + '</div>' +
			'<div class="itmms-stat-note">' + esc( note ) + '</div>' +
		'</button>';
	}

	function quickAction( label, iconName, nav ) {
		return '<button type="button" class="itmms-quick-action"' + navAttrs( nav.tab, nav ) + '>' +
			icon( iconName ) + '<span>' + esc( label ) + '</span>' +
		'</button>';
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
			ishraq: __( 'Ishraq', 'masjidos' ),
			zawal: __( 'Zawal', 'masjidos' ),
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
		var mods = state.settings.modules || {};
		var nextLabel = state.nextPrayer ? prayerNameLabel( state.nextPrayer ) : __( 'Calculating...', 'masjidos' );
		var nextTime = state.nextPrayer && state.nextPrayer.time ? state.nextPrayer.time : '';
		var liveNotice = state.dashboardAnnouncements[0] || state.announcements.filter( function ( notice ) { return 'active' === notice.status; } )[0];
		var noticeType = liveNotice ? window.itmms.announcements.announcementTypeLabel( liveNotice.announcement_type ) : __( 'Notice', 'masjidos' );
		var noticeText = liveNotice
			? liveNotice.title + ( liveNotice.content ? ' — ' + liveNotice.content : '' )
			: __( 'No active notices. Click to add one.', 'masjidos' );
		var hijriLabel = state.hijriDate && state.hijriDate.label ? state.hijriDate.label : '';
		var minbarStats = ( state.minbarDash && state.minbarDash.stats ) || {};
		var method = ( state.prayerMeta || {} ).calculation_method || 'Karachi';

		return '<div class="itmms-dashboard">' +
			'<div class="itmms-dashboard-head">' +
				'<div>' +
					'<h2>' + esc( __( 'Assalamu Alaikum', 'masjidos' ) ) + '</h2>' +
					( hijriLabel ? '<p class="itmms-dashboard-hijri" id="itmms-hijri-date">' + esc( hijriLabel ) + '</p>' : '<p class="itmms-dashboard-hijri" id="itmms-hijri-date">…</p>' ) +
				'</div>' +
				'<div class="itmms-arabic">&#1576;&#1616;&#1587;&#1618;&#1605;&#1616; &#1575;&#1604;&#1604;&#1617;&#1614;&#1607;&#1616;</div>' +
			'</div>' +

			'<button type="button" class="itmms-ticker itmms-ticker--action"' + navAttrs( 'announcements' ) + '>' +
				'<strong>' + esc( noticeType ) + '</strong>' +
				'<span>' + esc( noticeText ) + '</span>' +
				'<em class="itmms-ticker-cta">' + esc( liveNotice ? __( 'Manage', 'masjidos' ) : __( 'Add notice', 'masjidos' ) ) + '</em>' +
			'</button>' +

			'<div class="itmms-countdown itmms-countdown--hero">' +
				'<div class="itmms-countdown-copy">' +
					'<small>' + esc( __( 'Next Prayer', 'masjidos' ) ) + '</small>' +
					'<strong id="itmms-next-prayer">' + esc( nextLabel ) + ( nextTime ? ' · ' + esc( nextTime ) : '' ) + '</strong>' +
				'</div>' +
				'<b id="itmms-countdown" class="itmms-countdown-timer">00:00:00</b>' +
			'</div>' +

			'<div class="itmms-quick-actions">' +
				quickAction( __( 'Add Notice', 'masjidos' ), 'megaphone', { tab: 'announcements' } ) +
				( mods.events ? quickAction( __( 'Events', 'masjidos' ), 'calendar', { tab: 'events' } ) : '' ) +
				quickAction( __( 'Minbar', 'masjidos' ), 'mic', { tab: 'minbar', minbarTab: 'dashboard' } ) +
				quickAction( __( 'Features', 'masjidos' ), 'star', { tab: 'features' } ) +
				quickAction( __( 'Prayer Setup', 'masjidos' ), 'clock', { tab: 'settings', settingsTab: 'timetable' } ) +
			'</div>' +

			'<div class="itmms-stats-grid itmms-stats-grid--dash">' +
				statCard( 'teal', 'megaphone', state.stats.announcements || 0, __( 'Active Notices', 'masjidos' ), __( 'Open announcements', 'masjidos' ), { tab: 'announcements' } ) +
				( mods.events ? statCard( 'blue', 'calendar', state.stats.events || 0, __( 'Active Events', 'masjidos' ), __( 'Open events', 'masjidos' ), { tab: 'events' } ) : '' ) +
				statCard( 'gold', 'books', minbarStats.archive || state.khutbahs.length || 0, __( 'Khutbah Archive', 'masjidos' ), __( 'Open Minbar archive', 'masjidos' ), { tab: 'minbar', minbarTab: 'archive' } ) +
				statCard( 'purple', 'pen', minbarStats.planned || 0, __( 'Planned Topics', 'masjidos' ), __( 'Open Minbar planner', 'masjidos' ), { tab: 'minbar', minbarTab: 'planner' } ) +
				statCard( 'green', 'settings', window.itmms.activeModuleCount(), __( 'Modules On', 'masjidos' ), __( 'Manage modules', 'masjidos' ), { tab: 'modules' } ) +
				statCard( 'blue', 'compass', method, __( 'Prayer Setup', 'masjidos' ), __( 'Method & timetable', 'masjidos' ), { tab: 'settings', settingsTab: 'timetable' } ) +
			'</div>' +

			'<div class="itmms-content-grid">' +
				'<article class="itmms-card">' +
					'<header><h3>' + esc( __( 'Today\'s Prayer Times', 'masjidos' ) ) + '</h3>' +
					'<button type="button" class="itmms-link-btn"' + navAttrs( 'settings', { settingsTab: 'timetable' } ) + '>' + esc( __( 'Edit', 'masjidos' ) ) + '</button></header>' +
					'<div>' + prayerRows() + '</div>' +
				'</article>' +
				'<div class="itmms-side-stack">' +
					jumuahCard() +
					minbarSnippetCard() +
					prayerMetaCard() +
				'</div>' +
			'</div>' +

			upcomingDaysCard() +
		'</div>';
	}

	function jumuahCard() {
		var state = window.itmms.state;
		var jumuah = state.settings.jumuah || {};
		var khatib = window.itmms.settings.normalizeKhatib( jumuah.khatib );
		var sessions = window.itmms.settings.normalizeJumuahSessions( jumuah );
		var first = sessions[0] || {};
		var enabled = jumuah.enabled !== false;

		return '<article class="itmms-card itmms-jumuah-card">' +
			'<header><h3>' + esc( __( 'Jumuah', 'masjidos' ) ) + '</h3>' +
			'<button type="button" class="itmms-link-btn"' + navAttrs( 'settings', { settingsTab: 'jumuah' } ) + '>' + esc( __( 'Configure', 'masjidos' ) ) + '</button></header>' +
			'<div class="itmms-jumuah-summary">' +
				'<span class="' + ( enabled ? 'is-enabled' : '' ) + '">' + esc( enabled ? __( 'Enabled', 'masjidos' ) : __( 'Disabled', 'masjidos' ) ) + '</span>' +
				'<div><small>' + esc( __( 'Khutbah', 'masjidos' ) ) + '</small><strong>' + esc( first.khutbah_time || '13:00' ) + '</strong></div>' +
				'<div><small>' + esc( __( 'Jamaat', 'masjidos' ) ) + '</small><strong>' + esc( first.jamaat_time || '13:30' ) + '</strong></div>' +
				( khatib.name ? '<p class="itmms-jumuah-khatib"><b>' + esc( __( 'Khatib', 'masjidos' ) ) + '</b> ' + esc( khatib.name ) + '</p>' : '' ) +
				( jumuah.topic ? '<p class="itmms-jumuah-topic"><b>' + esc( __( 'Topic', 'masjidos' ) ) + '</b> ' + esc( jumuah.topic ) + '</p>' : '' ) +
			'</div>' +
		'</article>';
	}

	function minbarSnippetCard() {
		var dash = window.itmms.state.minbarDash || {};
		var schedule = dash.schedule || [];
		var next = schedule[0] || null;

		if ( next ) {
			return '<article class="itmms-card itmms-minbar-snippet">' +
				'<header><h3>' + esc( __( 'This Week\'s Minbar', 'masjidos' ) ) + '</h3>' +
				'<button type="button" class="itmms-link-btn"' + navAttrs( 'minbar', { minbarTab: 'schedule' } ) + '>' + esc( __( 'Open', 'masjidos' ) ) + '</button></header>' +
				'<div class="itmms-minbar-snippet-body">' +
					( next.scheduled_date ? '<span class="itmms-minbar-snippet-date">' + esc( next.scheduled_date ) + '</span>' : '' ) +
					'<strong>' + esc( next.khatib_name || next.name || __( 'Khatib TBD', 'masjidos' ) ) + '</strong>' +
					'<p>' + esc( next.topic || __( 'Topic not set yet', 'masjidos' ) ) + '</p>' +
				'</div>' +
			'</article>';
		}

		return '<article class="itmms-card itmms-minbar-snippet itmms-minbar-snippet--empty">' +
			'<header><h3>' + esc( __( 'This Week\'s Minbar', 'masjidos' ) ) + '</h3></header>' +
			'<p class="itmms-minbar-snippet-empty">' + esc( __( 'No Friday roster yet. Plan a khatib and topic.', 'masjidos' ) ) + '</p>' +
			'<button type="button" class="itmms-btn itmms-btn-primary itmms-btn-sm"' + navAttrs( 'minbar', { minbarTab: 'schedule' } ) + '>' + esc( __( 'Open Minbar', 'masjidos' ) ) + '</button>' +
		'</article>';
	}

	function prayerMetaCard() {
		var state = window.itmms.state;
		var meta = state.prayerMeta || {};
		var trust = state.trust || {};
		var settings = state.settings || {};
		var qibla = meta.qibla_direction == null ? '...' : meta.qibla_direction + '°';
		var location = meta.location || __( 'Not set', 'masjidos' );
		var method = meta.calculation_method || 'Karachi';
		var timezone = meta.timezone || settings.timezone || 'Asia/Dhaka';
		var coords = meta.latitude && meta.longitude ? Number( meta.latitude ).toFixed( 4 ) + ', ' + Number( meta.longitude ).toFixed( 4 ) : __( 'Not set', 'masjidos' );
		var source = 'aladhan' === ( trust.source || settings.prayer_source ) ? __( 'Auto API (Aladhan)', 'masjidos' ) : __( 'Local calculation', 'masjidos' );
		var hijriAdj = Number( trust.hijri_adjustment != null ? trust.hijri_adjustment : settings.hijri_adjustment || 0 );
		var hijriLabel = 0 === hijriAdj ? '0' : ( ( hijriAdj > 0 ? '+' : '' ) + hijriAdj );
		var offsets = trust.offsets || settings.prayer_offsets || {};
		var offsetParts = [ 'fajr', 'dhuhr', 'asr', 'maghrib', 'isha' ].map( function ( key ) {
			var value = Number( offsets[ key ] || 0 );
			return value ? ( key.charAt( 0 ).toUpperCase() + key.slice( 1 ) + ' ' + ( value > 0 ? '+' : '' ) + value + 'm' ) : '';
		} ).filter( Boolean );
		var checks = [
			[ true === trust.coordinates_ok, __( 'Coordinates look valid', 'masjidos' ), __( 'Set latitude and longitude', 'masjidos' ) ],
			[ true === trust.timezone_ok, __( 'Timezone is set', 'masjidos' ), __( 'Avoid UTC / +00:00 for local masjid times', 'masjidos' ) ],
			[ trust.source ? ! trust.timezone_mismatch : false, __( 'Matches WordPress timezone', 'masjidos' ), sprintf( __( 'WP site timezone is %s', 'masjidos' ), trust.site_timezone || '—' ) ]
		];

		return '<article class="itmms-card itmms-prayer-meta-card itmms-trust-card">' +
			'<header><h3>' + esc( __( 'Accuracy & Trust', 'masjidos' ) ) + '</h3>' +
			'<button type="button" class="itmms-link-btn"' + navAttrs( 'settings', { settingsTab: 'calculation' } ) + '>' + esc( __( 'Configure', 'masjidos' ) ) + '</button></header>' +
			'<div class="itmms-qibla-box">' +
				'<div class="itmms-qibla-compass"><span style="transform:rotate(' + esc( meta.qibla_direction || 0 ) + 'deg)"></span></div>' +
				'<div><small>' + esc( __( 'Qibla Direction', 'masjidos' ) ) + '</small><strong>' + esc( qibla ) + '</strong><p>' + esc( __( 'Clockwise from true north', 'masjidos' ) ) + '</p></div>' +
			'</div>' +
			'<div class="itmms-trust-checks">' + checks.map( function ( item ) {
				return '<span class="' + ( item[0] ? 'is-ok' : 'is-warn' ) + '">' + esc( item[0] ? item[1] : item[2] ) + '</span>';
			} ).join( '' ) + '</div>' +
			'<div class="itmms-trust-actions">' +
				'<button type="button" class="itmms-trust-toggle" data-trust-toggle aria-expanded="false">' +
					'<span>' + esc( __( 'Details', 'masjidos' ) ) + '</span>' +
				'</button>' +
			'</div>' +
			'<div class="itmms-trust-details" hidden>' +
				'<div class="itmms-meta-list">' +
					'<span><b>' + esc( __( 'Source', 'masjidos' ) ) + '</b><em>' + esc( source ) + '</em></span>' +
					'<span><b>' + esc( __( 'Location', 'masjidos' ) ) + '</b><em>' + esc( location ) + '</em></span>' +
					'<span><b>' + esc( __( 'Coordinates', 'masjidos' ) ) + '</b><em>' + esc( coords ) + '</em></span>' +
					'<span><b>' + esc( __( 'Method', 'masjidos' ) ) + '</b><em>' + esc( method ) + '</em></span>' +
					'<span><b>' + esc( __( 'Asr', 'masjidos' ) ) + '</b><em>' + esc( meta.asr_method || 'Hanafi' ) + '</em></span>' +
					'<span><b>' + esc( __( 'Timezone', 'masjidos' ) ) + '</b><em>' + esc( timezone ) + '</em></span>' +
					'<span><b>' + esc( __( 'Hijri adjustment', 'masjidos' ) ) + '</b><em>' + esc( hijriLabel ) + '</em></span>' +
					'<span><b>' + esc( __( 'Offsets', 'masjidos' ) ) + '</b><em>' + esc( offsetParts.length ? offsetParts.join( ', ' ) : __( 'None', 'masjidos' ) ) + '</em></span>' +
				'</div>' +
				'<p class="itmms-trust-note">' + esc( __( 'Hijri dates may differ by one day based on local moon sighting.', 'masjidos' ) ) + '</p>' +
			'</div>' +
		'</article>';
	}

	function upcomingDaysCard() {
		var state = window.itmms.state;
		var days = state.upcomingDays || [];
		if ( ! days.length ) {
			return '';
		}

		var rows = days.map( function ( day ) {
			var prayers = day.prayers || {};
			return '<tr class="' + ( day.is_today ? 'is-today' : '' ) + '">' +
				'<th scope="row"><strong>' + esc( day.label || day.date ) + '</strong>' + ( day.hijri ? '<small>' + esc( day.hijri ) + '</small>' : '' ) + '</th>' +
				'<td>' + esc( prayers.fajr || '—' ) + '</td>' +
				'<td>' + esc( prayers.dhuhr || '—' ) + '</td>' +
				'<td>' + esc( prayers.asr || '—' ) + '</td>' +
				'<td>' + esc( prayers.maghrib || '—' ) + '</td>' +
				'<td>' + esc( prayers.isha || '—' ) + '</td>' +
			'</tr>';
		} ).join( '' );

		return '<article class="itmms-card itmms-upcoming-card">' +
			'<header><h3>' + esc( __( 'Next 7 Days Preview', 'masjidos' ) ) + '</h3><span class="itmms-card-eyebrow">' + esc( __( 'Verify accuracy against your masjid board', 'masjidos' ) ) + '</span></header>' +
			'<div class="itmms-upcoming-scroll"><table class="itmms-upcoming-table"><thead><tr><th scope="col">' + esc( __( 'Date', 'masjidos' ) ) + '</th><th scope="col">' + esc( __( 'Fajr', 'masjidos' ) ) + '</th><th scope="col">' + esc( __( 'Dhuhr', 'masjidos' ) ) + '</th><th scope="col">' + esc( __( 'Asr', 'masjidos' ) ) + '</th><th scope="col">' + esc( __( 'Maghrib', 'masjidos' ) ) + '</th><th scope="col">' + esc( __( 'Isha', 'masjidos' ) ) + '</th></tr></thead><tbody>' + rows + '</tbody></table></div>' +
		'</article>';
	}

	function modulesHtml() {
		return '<div class="itmms-modules-grid">' + moduleCards() + '</div>';
	}

	function bindDashboardEvents() {
		var app = document.getElementById( 'itmms-app' );
		if ( ! app ) {
			return;
		}

		app.querySelectorAll( '[data-trust-toggle]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var card = btn.closest( '.itmms-trust-card' );
				var details = card ? card.querySelector( '.itmms-trust-details' ) : null;
				if ( ! details ) {
					return;
				}
				var open = details.hasAttribute( 'hidden' );
				if ( open ) {
					details.removeAttribute( 'hidden' );
				} else {
					details.setAttribute( 'hidden', '' );
				}
				btn.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
				btn.classList.toggle( 'is-open', open );
				var label = btn.querySelector( 'span' );
				if ( label ) {
					label.textContent = open ? __( 'Hide details', 'masjidos' ) : __( 'Details', 'masjidos' );
				}
			} );
		} );
	}
} )();
