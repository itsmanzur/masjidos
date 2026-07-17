/**
 * MasjidOS Admin - Welcome / first-run experience.
 */
( function () {
	'use strict';

	window.itmms = window.itmms || {};
	var __ = window.wp.i18n.__;
	var sprintf = window.wp.i18n.sprintf;

	var esc = window.itmms.esc;
	var icon = window.itmms.icon;
	var data = window.itmms.data || {};

	window.itmms.welcome = {
		welcomeHtml: welcomeHtml,
		bindWelcomeEvents: bindWelcomeEvents
	};

	function welcomeHtml() {
		var state = window.itmms.state || {};
		var next = state.nextPrayer || {};
		var nextName = next.name || __( 'Maghrib', 'masjidos' );
		var hijri = state.hijriDate && state.hijriDate.label ? state.hijriDate.label : '';
		var masjid = ( state.settings && state.settings.masjid_name ) || 'MasjidOS';
		var location = [ state.settings && state.settings.city, state.settings && state.settings.country ].filter( Boolean ).join( ', ' );
		var trust = state.trust || {};
		var tvUrl = ( data.siteUrl || '' ).replace( /\/$/, '' ) + '/masjidos-display/';

		return '<div class="itmms-welcome">' +
			'<section class="itmms-welcome-hero">' +
				'<div class="itmms-welcome-brand">' +
					'<div class="itmms-welcome-mark">' + icon( 'ledger' ) + '</div>' +
					'<p class="itmms-welcome-eyebrow">' + esc( __( 'Assalamu Alaikum', 'masjidos' ) ) + '</p>' +
					'<h2>MasjidOS</h2>' +
					'<p class="itmms-welcome-tagline">' + esc( __( 'Your masjid’s prayer times, Friday board, and TV — ready in minutes.', 'masjidos' ) ) + '</p>' +
					'<p class="itmms-welcome-sub">' + esc( sprintf( __( 'Welcome to %s. Set prayer once, then put widgets on your website.', 'masjidos' ), masjid ) ) + '</p>' +
					'<div class="itmms-welcome-cta-row">' +
						'<button type="button" class="itmms-btn itmms-btn-primary itmms-welcome-primary" data-welcome-setup>' + esc( __( 'Set up prayer times', 'masjidos' ) ) + '</button>' +
						'<button type="button" class="itmms-btn" data-welcome-skip>' + esc( __( 'Skip to Dashboard', 'masjidos' ) ) + '</button>' +
					'</div>' +
				'</div>' +
				'<div class="itmms-welcome-preview">' +
					'<div class="itmms-welcome-preview-head">' +
						'<span>' + esc( __( 'Live preview', 'masjidos' ) ) + '</span>' +
						( hijri ? '<small>' + esc( hijri ) + '</small>' : '' ) +
					'</div>' +
					'<div class="itmms-countdown itmms-countdown--hero itmms-welcome-countdown">' +
						'<div class="itmms-countdown-copy">' +
							'<small>' + esc( __( 'Next prayer', 'masjidos' ) ) + '</small>' +
							'<strong>' + esc( nextName ) + '</strong>' +
							( location ? '<em>' + esc( location ) + '</em>' : '' ) +
						'</div>' +
						'<b class="itmms-countdown-timer" id="itmms-countdown">00:00:00</b>' +
					'</div>' +
					'<div class="itmms-welcome-prayer-list">' + welcomePrayerRows() + '</div>' +
					'<p class="itmms-welcome-preview-note">' + esc( __( 'This is what visitors can see on your site after you paste a shortcode.', 'masjidos' ) ) + '</p>' +
				'</div>' +
			'</section>' +

			'<section class="itmms-welcome-progress">' +
				'<h3>' + esc( __( 'Setup pulse', 'masjidos' ) ) + '</h3>' +
				'<div class="itmms-welcome-chips">' +
					progressChip( __( 'Timezone', 'masjidos' ), !! trust.timezone_ok ) +
					progressChip( __( 'Location', 'masjidos' ), !! trust.coordinates_ok ) +
					progressChip( __( 'Prayer times', 'masjidos' ), !!( state.prayers && state.prayers.length ) ) +
				'</div>' +
			'</section>' +

			'<section class="itmms-welcome-paths">' +
				'<h3>' + esc( __( 'Three paths to start', 'masjidos' ) ) + '</h3>' +
				'<div class="itmms-welcome-path-grid">' +
					pathCard( 'clock', __( '1. Set up prayer', 'masjidos' ), __( 'Choose timezone, coordinates, and calculation method.', 'masjidos' ), 'data-welcome-setup' ) +
					pathCard( 'star', __( '2. Put on website', 'masjidos' ), __( 'Open Features, copy a shortcode, paste on any page.', 'masjidos' ), 'data-welcome-features' ) +
					pathCard( 'monitor', __( '3. Mosque TV', 'masjidos' ), __( 'Open the fullscreen board for lobby or prayer hall screens.', 'masjidos' ), 'data-welcome-tv', tvUrl ) +
				'</div>' +
			'</section>' +

			'<section class="itmms-welcome-steps">' +
				'<h3>' + esc( __( 'About 5 minutes', 'masjidos' ) ) + '</h3>' +
				'<ol class="itmms-welcome-step-list">' +
					'<li>' + esc( __( 'Pick your admin language from the top bar (English, Bangla, or Arabic).', 'masjidos' ) ) + '</li>' +
					'<li>' + esc( __( 'Save prayer settings so times match your masjid.', 'masjidos' ) ) + '</li>' +
					'<li>' + esc( __( 'Publish [masjidos_prayer_times] on your homepage or prayer page.', 'masjidos' ) ) + '</li>' +
				'</ol>' +
			'</section>' +

			'<footer class="itmms-welcome-foot">' +
				'<p>' + esc( __( 'Works after setup without constant internet · Free core forever', 'masjidos' ) ) + '</p>' +
				'<div class="itmms-welcome-foot-actions">' +
					'<button type="button" class="itmms-btn" data-welcome-docs>' + esc( __( 'Open Docs', 'masjidos' ) ) + '</button>' +
					'<button type="button" class="itmms-btn itmms-btn-primary" data-welcome-done>' + esc( __( 'I’m ready — go to Dashboard', 'masjidos' ) ) + '</button>' +
				'</div>' +
			'</footer>' +
		'</div>';
	}

	function welcomePrayerRows() {
		var state = window.itmms.state || {};
		var prayers = state.prayers && state.prayers.length ? state.prayers : [
			{ name: __( 'Fajr', 'masjidos' ), time: '—', current: false },
			{ name: __( 'Dhuhr', 'masjidos' ), time: '—', current: false },
			{ name: __( 'Asr', 'masjidos' ), time: '—', current: true },
			{ name: __( 'Maghrib', 'masjidos' ), time: '—', current: false },
			{ name: __( 'Isha', 'masjidos' ), time: '—', current: false }
		];

		return prayers.filter( function ( prayer ) {
			var key = ( prayer.key || prayer.name || '' ).toString().toLowerCase();
			return [ 'sunrise', 'ishraq', 'zawal' ].indexOf( key ) === -1;
		} ).slice( 0, 6 ).map( function ( prayer ) {
			var label = prayer.name || '';
			if ( window.itmms.dashboard && window.itmms.dashboard.prayerNameLabel ) {
				label = window.itmms.dashboard.prayerNameLabel( prayer );
			}
			return '<div class="itmms-welcome-prayer-row' + ( prayer.current ? ' is-current' : '' ) + '">' +
				'<span>' + esc( label ) + '</span>' +
				'<strong>' + esc( prayer.time || '—' ) + '</strong>' +
			'</div>';
		} ).join( '' );
	}

	function progressChip( label, ok ) {
		return '<span class="itmms-welcome-chip' + ( ok ? ' is-ok' : '' ) + '">' +
			'<i aria-hidden="true"></i>' + esc( label ) +
		'</span>';
	}

	function pathCard( iconName, title, desc, attr, href ) {
		if ( href ) {
			return '<a class="itmms-welcome-path" href="' + esc( href ) + '" target="_blank" rel="noopener noreferrer" ' + attr + '>' +
				'<div class="itmms-welcome-path-icon">' + icon( iconName ) + '</div>' +
				'<strong>' + esc( title ) + '</strong>' +
				'<p>' + esc( desc ) + '</p>' +
			'</a>';
		}
		return '<button type="button" class="itmms-welcome-path" ' + attr + '>' +
			'<div class="itmms-welcome-path-icon">' + icon( iconName ) + '</div>' +
			'<strong>' + esc( title ) + '</strong>' +
			'<p>' + esc( desc ) + '</p>' +
		'</button>';
	}

	function dismissWelcome( then ) {
		var api = window.itmms.api;
		if ( ! api ) {
			data.showWelcome = false;
			if ( then ) {
				then();
			}
			return;
		}
		api( 'welcome/dismiss', { method: 'POST', body: '{}' } ).then( function () {
			data.showWelcome = false;
			if ( then ) {
				then();
			}
		} ).catch( function () {
			data.showWelcome = false;
			if ( then ) {
				then();
			}
		} );
	}

	function bindWelcomeEvents() {
		var app = document.getElementById( 'itmms-app' );
		if ( ! app ) {
			return;
		}
		var switchTab = window.itmms.switchTab;

		app.querySelectorAll( '[data-welcome-setup]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function ( event ) {
				event.preventDefault();
				var state = window.itmms.state;
				if ( state ) {
					state.settingsTab = 'timetable';
				}
				dismissWelcome( function () {
					if ( switchTab ) {
						switchTab( 'settings', 'push' );
					}
					if ( window.itmms.activateSettingsTab ) {
						window.itmms.activateSettingsTab( 'timetable', 'replace' );
					}
				} );
			} );
		} );

		app.querySelectorAll( '[data-welcome-features]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				dismissWelcome( function () {
					if ( switchTab ) {
						switchTab( 'features', 'push' );
					}
				} );
			} );
		} );

		app.querySelectorAll( '[data-welcome-docs]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				if ( switchTab ) {
					switchTab( 'docs', 'push' );
				}
			} );
		} );

		app.querySelectorAll( '[data-welcome-skip], [data-welcome-done]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				dismissWelcome( function () {
					if ( switchTab ) {
						switchTab( 'dashboard', 'push' );
					}
				} );
			} );
		} );

		app.querySelectorAll( '[data-open-welcome]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				if ( switchTab ) {
					switchTab( 'welcome', 'push' );
				}
			} );
		} );
	}
} )();
