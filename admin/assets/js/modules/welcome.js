/**
 * MasjidOS Admin - Welcome & 1-Minute Interactive Setup Wizard.
 */
( function () {
	'use strict';

	window.itmms = window.itmms || {};
	var __ = window.wp.i18n.__;
	var sprintf = window.wp.i18n.sprintf;

	var esc = window.itmms.esc;
	var icon = window.itmms.icon;
	var data = window.itmms.data || {};

	var CITY_PRESETS = [
		{ label: 'Dhaka', city: 'Dhaka', country: 'Bangladesh', lat: '23.8103', lng: '90.4125', tz: 'Asia/Dhaka', method: 'karachi', asr: 'hanafi', currency: 'BDT' },
		{ label: 'Chattogram', city: 'Chattogram', country: 'Bangladesh', lat: '22.3569', lng: '91.7832', tz: 'Asia/Dhaka', method: 'karachi', asr: 'hanafi', currency: 'BDT' },
		{ label: 'Sylhet', city: 'Sylhet', country: 'Bangladesh', lat: '24.8949', lng: '91.8687', tz: 'Asia/Dhaka', method: 'karachi', asr: 'hanafi', currency: 'BDT' },
		{ label: 'Rajshahi', city: 'Rajshahi', country: 'Bangladesh', lat: '24.3745', lng: '88.6042', tz: 'Asia/Dhaka', method: 'karachi', asr: 'hanafi', currency: 'BDT' },
		{ label: 'Khulna', city: 'Khulna', country: 'Bangladesh', lat: '22.8456', lng: '89.5403', tz: 'Asia/Dhaka', method: 'karachi', asr: 'hanafi', currency: 'BDT' },
		{ label: 'London', city: 'London', country: 'United Kingdom', lat: '51.5074', lng: '-0.1278', tz: 'Europe/London', method: 'mwl', asr: 'hanafi', currency: 'GBP' },
		{ label: 'New York', city: 'New York', country: 'United States', lat: '40.7128', lng: '-74.0060', tz: 'America/New_York', method: 'isna', asr: 'standard', currency: 'USD' },
		{ label: 'Toronto', city: 'Toronto', country: 'Canada', lat: '43.6532', lng: '-79.3832', tz: 'America/Toronto', method: 'isna', asr: 'standard', currency: 'CAD' },
		{ label: 'Makkah', city: 'Makkah', country: 'Saudi Arabia', lat: '21.3891', lng: '39.8579', tz: 'Asia/Riyadh', method: 'makkah', asr: 'standard', currency: 'SAR' },
		{ label: 'Kuala Lumpur', city: 'Kuala Lumpur', country: 'Malaysia', lat: '3.1390', lng: '101.6869', tz: 'Asia/Kuala_Lumpur', method: 'singapore', asr: 'standard', currency: 'MYR' }
	];

	window.itmms.welcome = {
		welcomeHtml: welcomeHtml,
		bindWelcomeEvents: bindWelcomeEvents
	};

	function welcomeHtml() {
		var state = window.itmms.state || {};
		var step = Number( state.wizardStep || 1 );
		if ( step < 1 || step > 4 ) {
			step = 1;
		}
		var next = state.nextPrayer || {};
		var nextName = next.name || __( 'Maghrib', 'masjidos' );
		var hijri = state.hijriDate && state.hijriDate.label ? state.hijriDate.label : '';
		var masjid = ( state.settings && state.settings.masjid_name ) || 'MasjidOS';
		var location = [ state.settings && state.settings.city, state.settings && state.settings.country ].filter( Boolean ).join( ', ' );
		var trust = state.trust || {};
		var ob = state.onboarding || data.onboarding || {};

		return '<div class="itmms-welcome">' +
			'<section class="itmms-welcome-hero itmms-welcome-hero--wizard">' +
				'<div class="itmms-welcome-brand itmms-wizard-shell">' +
					'<div class="itmms-wizard-top">' +
						'<div class="itmms-welcome-mark">' + icon( 'ledger' ) + '</div>' +
						'<div>' +
							'<p class="itmms-welcome-eyebrow">' + esc( __( '1-Minute Setup Wizard', 'masjidos' ) ) + '</p>' +
							'<h2>' + esc( sprintf( __( 'Welcome to %s', 'masjidos' ), masjid ) ) + '</h2>' +
						'</div>' +
						'<button type="button" class="itmms-btn itmms-btn-sm itmms-wizard-skip" data-welcome-skip>' + esc( __( 'Skip to Dashboard', 'masjidos' ) ) + '</button>' +
					'</div>' +

					wizardStepperHtml( step ) +
					'<div class="itmms-wizard-body">' +
						wizardStepContent( step ) +
					'</div>' +
					'<p class="itmms-wizard-status" id="itmms-wizard-status" aria-live="polite"></p>' +
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
					'<div class="itmms-welcome-chips">' +
						progressChip( __( 'Timezone', 'masjidos' ), !! trust.timezone_ok ) +
						progressChip( __( 'Location', 'masjidos' ), !! trust.coordinates_ok ) +
						progressChip( __( 'Starter Page', 'masjidos' ), !!( ob && ob.starter_page_url ) ) +
					'</div>' +
					'<p class="itmms-welcome-preview-note">' + esc( __( 'Updates live as you save each step of the wizard.', 'masjidos' ) ) + '</p>' +
				'</div>' +
			'</section>' +

			'<footer class="itmms-welcome-foot">' +
				'<p>' + esc( __( 'Works offline after setup · Gutenberg & Elementor ready · Free core forever', 'masjidos' ) ) + '</p>' +
				'<div class="itmms-welcome-foot-actions">' +
					'<button type="button" class="itmms-btn" data-welcome-docs>' + esc( __( 'Open Docs', 'masjidos' ) ) + '</button>' +
					'<button type="button" class="itmms-btn" data-welcome-features>' + esc( __( 'Explore Widgets', 'masjidos' ) ) + '</button>' +
					'<button type="button" class="itmms-btn itmms-btn-primary" data-welcome-done>' + esc( __( 'Go to Dashboard', 'masjidos' ) ) + '</button>' +
				'</div>' +
			'</footer>' +
		'</div>';
	}

	function wizardStepperHtml( activeStep ) {
		var steps = [
			[ 1, __( '1. Identity', 'masjidos' ) ],
			[ 2, __( '2. Location', 'masjidos' ) ],
			[ 3, __( '3. Iqamah & Jumuah', 'masjidos' ) ],
			[ 4, __( '4. Publish & TV', 'masjidos' ) ]
		];
		return '<div class="itmms-wizard-steps" role="tablist">' +
			steps.map( function ( item ) {
				var num = item[0];
				var cls = 'itmms-wizard-step-btn';
				if ( num === activeStep ) {
					cls += ' is-active';
				} else if ( num < activeStep ) {
					cls += ' is-done';
				}
				return '<button type="button" class="' + cls + '" data-wizard-goto="' + num + '">' +
					'<span class="itmms-wizard-step-dot">' + ( num < activeStep ? '&#10003;' : num ) + '</span>' +
					'<span>' + esc( item[1] ) + '</span>' +
				'</button>';
			} ).join( '' ) +
		'</div>';
	}

	function wizardStepContent( step ) {
		var state = window.itmms.state || {};
		var s = state.settings || {};

		if ( 1 === step ) {
			var lang = s.ui_language || data.uiLanguage || 'en';
			var curr = s.currency || 'BDT';
			var currencies = [ 'BDT', 'USD', 'GBP', 'EUR', 'SAR', 'AED', 'MYR', 'INR', 'PKR', 'CAD', 'AUD' ];
			return '<div class="itmms-wizard-pane">' +
				'<h3>' + esc( __( 'Step 1: Language & Masjid Identity', 'masjidos' ) ) + '</h3>' +
				'<p class="itmms-wizard-desc">' + esc( __( 'Choose your preferred interface language and enter your masjid name.', 'masjidos' ) ) + '</p>' +
				'<div class="itmms-wizard-grid">' +
					'<label class="itmms-field"><span>' + esc( __( 'Admin & Widget Language', 'masjidos' ) ) + '</span>' +
						'<select id="itmms-wiz-lang">' +
							'<option value="en"' + ( 'en' === lang ? ' selected' : '' ) + '>English</option>' +
							'<option value="bn"' + ( 'bn' === lang ? ' selected' : '' ) + '>বাংলা (Bangla)</option>' +
							'<option value="ar"' + ( 'ar' === lang ? ' selected' : '' ) + '>العربية (Arabic)</option>' +
						'</select>' +
					'</label>' +
					'<label class="itmms-field"><span>' + esc( __( 'Currency', 'masjidos' ) ) + '</span>' +
						'<select id="itmms-wiz-currency">' +
							currencies.map( function ( c ) {
								return '<option value="' + esc( c ) + '"' + ( c === curr ? ' selected' : '' ) + '>' + esc( c ) + '</option>';
							} ).join( '' ) +
						'</select>' +
					'</label>' +
					'<label class="itmms-field itmms-field-wide"><span>' + esc( __( 'Masjid Name', 'masjidos' ) ) + '</span>' +
						'<input type="text" id="itmms-wiz-masjid-name" value="' + esc( s.masjid_name || '' ) + '" placeholder="' + esc( __( 'e.g. Baitul Aman Jame Masjid', 'masjidos' ) ) + '">' +
					'</label>' +
				'</div>' +
				'<div class="itmms-wizard-actions">' +
					'<button type="button" class="itmms-btn itmms-btn-primary" data-wizard-save="1">' + esc( __( 'Save & Next: Location →', 'masjidos' ) ) + '</button>' +
				'</div>' +
			'</div>';
		}

		if ( 2 === step ) {
			var method = s.calculation_method || 'karachi';
			var asr = s.asr_method || 'hanafi';
			var methods = [
				[ 'karachi', 'Karachi (Islamic University)' ],
				[ 'mwl', 'Muslim World League (MWL)' ],
				[ 'isna', 'ISNA (North America)' ],
				[ 'egypt', 'Egyptian General Authority' ],
				[ 'makkah', 'Umm al-Qura, Makkah' ],
				[ 'dubai', 'Dubai' ],
				[ 'qatar', 'Qatar' ],
				[ 'kuwait', 'Kuwait' ],
				[ 'singapore', 'Singapore / Malaysia' ]
			];
			return '<div class="itmms-wizard-pane">' +
				'<h3>' + esc( __( 'Step 2: City Preset, Coordinates & Calculation', 'masjidos' ) ) + '</h3>' +
				'<p class="itmms-wizard-desc">' + esc( __( 'Click a city preset or use your browser location to auto-fill coordinates, timezone, and calculation method.', 'masjidos' ) ) + '</p>' +
				'<div class="itmms-wizard-presets">' +
					CITY_PRESETS.map( function ( p, idx ) {
						var active = String( s.city || '' ).toLowerCase() === p.city.toLowerCase();
						return '<button type="button" class="itmms-wizard-preset-chip' + ( active ? ' is-active' : '' ) + '" data-wizard-city="' + idx + '">' + esc( p.label ) + '</button>';
					} ).join( '' ) +
					'<button type="button" class="itmms-wizard-preset-chip itmms-wizard-preset-chip--geo" id="itmms-wiz-geo">' + icon( 'compass' ) + ' ' + esc( __( 'Detect My Location', 'masjidos' ) ) + '</button>' +
				'</div>' +
				'<div class="itmms-wizard-grid">' +
					'<label class="itmms-field"><span>' + esc( __( 'City', 'masjidos' ) ) + '</span><input type="text" id="itmms-wiz-city" value="' + esc( s.city || 'Dhaka' ) + '"></label>' +
					'<label class="itmms-field"><span>' + esc( __( 'Country', 'masjidos' ) ) + '</span><input type="text" id="itmms-wiz-country" value="' + esc( s.country || 'Bangladesh' ) + '"></label>' +
					'<label class="itmms-field"><span>' + esc( __( 'Latitude', 'masjidos' ) ) + '</span><input type="number" step="any" id="itmms-wiz-lat" value="' + esc( s.latitude || '23.8103' ) + '"></label>' +
					'<label class="itmms-field"><span>' + esc( __( 'Longitude', 'masjidos' ) ) + '</span><input type="number" step="any" id="itmms-wiz-lng" value="' + esc( s.longitude || '90.4125' ) + '"></label>' +
					'<label class="itmms-field"><span>' + esc( __( 'Timezone', 'masjidos' ) ) + '</span><input type="text" id="itmms-wiz-tz" value="' + esc( s.timezone || 'Asia/Dhaka' ) + '" placeholder="Asia/Dhaka"></label>' +
					'<label class="itmms-field"><span>' + esc( __( 'Calculation Method', 'masjidos' ) ) + '</span>' +
						'<select id="itmms-wiz-method">' +
							methods.map( function ( m ) {
								return '<option value="' + esc( m[0] ) + '"' + ( m[0] === method ? ' selected' : '' ) + '>' + esc( m[1] ) + '</option>';
							} ).join( '' ) +
						'</select>' +
					'</label>' +
					'<label class="itmms-field"><span>' + esc( __( 'Asr Juristic Method', 'masjidos' ) ) + '</span>' +
						'<select id="itmms-wiz-asr">' +
							'<option value="hanafi"' + ( 'hanafi' === asr ? ' selected' : '' ) + '>' + esc( __( 'Hanafi', 'masjidos' ) ) + '</option>' +
							'<option value="standard"' + ( 'standard' === asr ? ' selected' : '' ) + '>' + esc( __( 'Standard (Shafi, Maliki, Hanbali)', 'masjidos' ) ) + '</option>' +
						'</select>' +
					'</label>' +
				'</div>' +
				'<div class="itmms-wizard-actions">' +
					'<button type="button" class="itmms-btn" data-wizard-goto="1">' + esc( __( '← Back', 'masjidos' ) ) + '</button>' +
					'<button type="button" class="itmms-btn itmms-btn-primary" data-wizard-save="2">' + esc( __( 'Save & Next: Iqamah & Jumuah →', 'masjidos' ) ) + '</button>' +
				'</div>' +
			'</div>';
		}

		if ( 3 === step ) {
			var rules = s.iqamah_rules || {};
			var jumuah = s.jumuah || {};
			var prayers = [
				[ 'fajr', __( 'Fajr', 'masjidos' ), 20 ],
				[ 'dhuhr', __( 'Dhuhr', 'masjidos' ), 15 ],
				[ 'asr', __( 'Asr', 'masjidos' ), 15 ],
				[ 'maghrib', __( 'Maghrib', 'masjidos' ), 5 ],
				[ 'isha', __( 'Isha', 'masjidos' ), 15 ]
			];
			return '<div class="itmms-wizard-pane">' +
				'<h3>' + esc( __( 'Step 3: Quick Iqamah & Friday Jumuah Setup', 'masjidos' ) ) + '</h3>' +
				'<p class="itmms-wizard-desc">' + esc( __( 'Set how many minutes after Azan each Jamaat starts (you can set fixed times or seasonal rules anytime in Settings).', 'masjidos' ) ) + '</p>' +
				'<div class="itmms-wizard-grid">' +
					prayers.map( function ( p ) {
						var key = p[0];
						var label = p[1];
						var defMin = p[2];
						var r = rules[ key ] || {};
						var mins = r.offset_minutes != null ? r.offset_minutes : defMin;
						return '<label class="itmms-field"><span>' + esc( sprintf( __( '%s Iqamah (minutes after Azan)', 'masjidos' ), label ) ) + '</span>' +
							'<input type="number" min="0" max="120" data-wiz-iqamah-offset="' + esc( key ) + '" value="' + esc( mins ) + '">' +
						'</label>';
					} ).join( '' ) +
					'<label class="itmms-field"><span>' + esc( __( 'Friday Jumuah Khutbah Time', 'masjidos' ) ) + '</span>' +
						'<input type="time" id="itmms-wiz-jumuah-khutbah" value="' + esc( jumuah.khutbah_time || '13:00' ) + '">' +
					'</label>' +
					'<label class="itmms-field"><span>' + esc( __( 'Friday Jumuah Jamaat Time', 'masjidos' ) ) + '</span>' +
						'<input type="time" id="itmms-wiz-jumuah-jamaat" value="' + esc( jumuah.jamaat_time || '13:30' ) + '">' +
					'</label>' +
				'</div>' +
				'<div class="itmms-wizard-actions">' +
					'<button type="button" class="itmms-btn" data-wizard-goto="2">' + esc( __( '← Back', 'masjidos' ) ) + '</button>' +
					'<button type="button" class="itmms-btn itmms-btn-primary" data-wizard-save="3">' + esc( __( 'Save & Next: Publish & TV →', 'masjidos' ) ) + '</button>' +
				'</div>' +
			'</div>';
		}

		var ob = state.onboarding || data.onboarding || {};
		var tvUrl = ob.tv_url || ( ( data.siteUrl || '' ).replace( /\/$/, '' ) + '/masjidos-display/' );
		var hasPage = !!( ob && ob.starter_page_url );

		return '<div class="itmms-wizard-pane">' +
			'<h3>' + esc( __( 'Step 4: Publish Website Page & Open TV Display', 'masjidos' ) ) + '</h3>' +
			'<p class="itmms-wizard-desc">' + esc( __( 'Your prayer calculation is ready! Publish a ready-to-use Prayer Times page in one click or open the Mosque TV screen.', 'masjidos' ) ) + '</p>' +
			'<div class="itmms-welcome-path-grid itmms-wizard-finish-grid">' +
				'<div class="itmms-welcome-path">' +
					'<div class="itmms-welcome-path-icon">' + icon( 'star' ) + '</div>' +
					'<strong>' + esc( __( '1. Starter Prayer Times Page', 'masjidos' ) ) + '</strong>' +
					'<p>' + esc( __( 'Creates a published WordPress page with daily Prayer Times, Jumuah card, Announcements, and Monthly Timetable.', 'masjidos' ) ) + '</p>' +
					( hasPage
						? '<div class="itmms-onboarding-step-links">' +
							'<a class="itmms-btn itmms-btn-primary itmms-btn-sm" href="' + esc( ob.starter_page_url ) + '" target="_blank" rel="noopener noreferrer">' + esc( __( 'View Published Page', 'masjidos' ) ) + '</a>' +
							( ob.starter_page_edit ? '<a class="itmms-btn itmms-btn-sm" href="' + esc( ob.starter_page_edit ) + '" target="_blank" rel="noopener noreferrer">' + esc( __( 'Edit Page', 'masjidos' ) ) + '</a>' : '' ) +
						'</div>'
						: '<button type="button" class="itmms-btn itmms-btn-primary itmms-btn-sm" data-onboarding-create-page>' + esc( __( 'Create & Publish Page (1-Click)', 'masjidos' ) ) + '</button>'
					) +
				'</div>' +
				'<div class="itmms-welcome-path">' +
					'<div class="itmms-welcome-path-icon">' + icon( 'monitor' ) + '</div>' +
					'<strong>' + esc( __( '2. Mosque TV Display', 'masjidos' ) ) + '</strong>' +
					'<p>' + esc( __( 'Open the fullscreen digital clock & jamaat board designed for lobby or prayer hall screens.', 'masjidos' ) ) + '</p>' +
					'<a class="itmms-btn itmms-btn-sm" href="' + esc( tvUrl ) + '" target="_blank" rel="noopener noreferrer" data-onboarding-tv>' + esc( __( 'Open /masjidos-display/', 'masjidos' ) ) + '</a>' +
				'</div>' +
				'<div class="itmms-welcome-path">' +
					'<div class="itmms-welcome-path-icon">' + icon( 'clock' ) + '</div>' +
					'<strong>' + esc( __( '3. Copy Shortcode', 'masjidos' ) ) + '</strong>' +
					'<p>' + esc( __( 'Paste [masjidos_prayer_times] anywhere, or use our 16 Gutenberg blocks & 7 Elementor widgets.', 'masjidos' ) ) + '</p>' +
					'<button type="button" class="itmms-btn itmms-btn-sm" data-wizard-copy="[masjidos_prayer_times]">' + esc( __( 'Copy [masjidos_prayer_times]', 'masjidos' ) ) + '</button>' +
				'</div>' +
			'</div>' +
			'<div class="itmms-wizard-actions">' +
				'<button type="button" class="itmms-btn" data-wizard-goto="3">' + esc( __( '← Back', 'masjidos' ) ) + '</button>' +
				'<button type="button" class="itmms-btn itmms-btn-primary" data-welcome-done>' + esc( __( 'Finish Setup — Open Dashboard ✓', 'masjidos' ) ) + '</button>' +
			'</div>' +
		'</div>';
	}

	function welcomePrayerRows() {
		var state = window.itmms.state || {};
		var prayers = state.prayers && state.prayers.length ? state.prayers : [
			{ name: __( 'Fajr', 'masjidos' ), time: '—', iqamah: '', current: false },
			{ name: __( 'Dhuhr', 'masjidos' ), time: '—', iqamah: '', current: false },
			{ name: __( 'Asr', 'masjidos' ), time: '—', iqamah: '', current: true },
			{ name: __( 'Maghrib', 'masjidos' ), time: '—', iqamah: '', current: false },
			{ name: __( 'Isha', 'masjidos' ), time: '—', iqamah: '', current: false }
		];

		return prayers.filter( function ( prayer ) {
			var key = ( prayer.key || prayer.name || '' ).toString().toLowerCase();
			return [ 'sunrise', 'ishraq', 'zawal' ].indexOf( key ) === -1;
		} ).slice( 0, 6 ).map( function ( prayer ) {
			var label = prayer.name || '';
			if ( window.itmms.dashboard && window.itmms.dashboard.prayerNameLabel ) {
				label = window.itmms.dashboard.prayerNameLabel( prayer );
			}
			var iq = prayer.iqamah ? '<small class="itmms-welcome-iqamah">' + esc( __( 'Iqamah:', 'masjidos' ) ) + ' ' + esc( prayer.iqamah ) + '</small>' : '';
			return '<div class="itmms-welcome-prayer-row' + ( prayer.current ? ' is-current' : '' ) + '">' +
				'<span>' + esc( label ) + iq + '</span>' +
				'<strong>' + esc( prayer.time || '—' ) + '</strong>' +
			'</div>';
		} ).join( '' );
	}

	function progressChip( label, ok ) {
		return '<span class="itmms-welcome-chip' + ( ok ? ' is-ok' : '' ) + '">' +
			'<i aria-hidden="true"></i>' + esc( label ) +
		'</span>';
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

	function saveWizardStep( stepNum, btn ) {
		var state = window.itmms.state || {};
		var api = window.itmms.api;
		if ( ! api ) {
			return;
		}
		var payload = Object.assign( {}, state.settings || {} );
		var prevLang = payload.ui_language || data.uiLanguage || 'en';

		if ( 1 === stepNum ) {
			var langEl = document.getElementById( 'itmms-wiz-lang' );
			var currEl = document.getElementById( 'itmms-wiz-currency' );
			var nameEl = document.getElementById( 'itmms-wiz-masjid-name' );
			if ( langEl ) {
				payload.ui_language = langEl.value;
			}
			if ( currEl ) {
				payload.currency = currEl.value;
			}
			if ( nameEl && nameEl.value.trim() ) {
				payload.masjid_name = nameEl.value.trim();
			}
		} else if ( 2 === stepNum ) {
			var cityEl = document.getElementById( 'itmms-wiz-city' );
			var countryEl = document.getElementById( 'itmms-wiz-country' );
			var latEl = document.getElementById( 'itmms-wiz-lat' );
			var lngEl = document.getElementById( 'itmms-wiz-lng' );
			var tzEl = document.getElementById( 'itmms-wiz-tz' );
			var methodEl = document.getElementById( 'itmms-wiz-method' );
			var asrEl = document.getElementById( 'itmms-wiz-asr' );

			if ( cityEl ) {
				payload.city = cityEl.value.trim();
			}
			if ( countryEl ) {
				payload.country = countryEl.value.trim();
			}
			if ( latEl ) {
				payload.latitude = latEl.value.trim();
			}
			if ( lngEl ) {
				payload.longitude = lngEl.value.trim();
			}
			if ( tzEl && tzEl.value.trim() ) {
				payload.timezone = tzEl.value.trim();
			}
			if ( methodEl ) {
				payload.calculation_method = methodEl.value;
			}
			if ( asrEl ) {
				payload.asr_method = asrEl.value;
			}
		} else if ( 3 === stepNum ) {
			var rules = Object.assign( {}, payload.iqamah_rules || {} );
			document.querySelectorAll( '[data-wiz-iqamah-offset]' ).forEach( function ( input ) {
				var key = input.getAttribute( 'data-wiz-iqamah-offset' );
				var mins = parseInt( input.value, 10 );
				if ( isNaN( mins ) || mins < 0 ) {
					mins = 15;
				}
				rules[ key ] = Object.assign( {}, rules[ key ] || {}, {
					mode: 'after_azan',
					offset_minutes: mins,
					rounding: ( rules[ key ] && rules[ key ].rounding ) || 5
				} );
			} );
			payload.iqamah_rules = rules;

			var khutbahEl = document.getElementById( 'itmms-wiz-jumuah-khutbah' );
			var jamaatEl = document.getElementById( 'itmms-wiz-jumuah-jamaat' );
			var jumuah = Object.assign( {}, payload.jumuah || {} );
			if ( khutbahEl && khutbahEl.value ) {
				jumuah.khutbah_time = khutbahEl.value;
			}
			if ( jamaatEl && jamaatEl.value ) {
				jumuah.jamaat_time = jamaatEl.value;
			}
			if ( Array.isArray( jumuah.sessions ) && jumuah.sessions[0] ) {
				jumuah.sessions[0].khutbah_time = jumuah.khutbah_time || '13:00';
				jumuah.sessions[0].jamaat_time = jumuah.jamaat_time || '13:30';
			}
			payload.jumuah = jumuah;
		}

		var statusEl = document.getElementById( 'itmms-wizard-status' );
		if ( statusEl ) {
			statusEl.textContent = __( 'Saving...', 'masjidos' );
		}
		if ( btn ) {
			btn.disabled = true;
		}

		api( 'settings', {
			method: 'POST',
			body: JSON.stringify( payload )
		} ).then( function ( res ) {
			state.settings = res.settings || payload;
			if ( res.onboarding ) {
				state.onboarding = res.onboarding;
			}
			state.wizardStep = Math.min( 4, stepNum + 1 );
			var nextLang = ( state.settings && state.settings.ui_language ) || 'en';
			if ( nextLang !== prevLang && window.itmms.applyUiLanguage ) {
				return window.itmms.applyUiLanguage( nextLang ).then( function () {
					return api( 'dashboard' );
				} );
			}
			return api( 'dashboard' );
		} ).then( function ( dash ) {
			if ( dash ) {
				state.settings = dash.settings || state.settings;
				state.prayers = dash.prayers || state.prayers;
				state.nextPrayer = dash.next_prayer || state.nextPrayer;
				state.prayerMeta = dash.prayer_meta || state.prayerMeta;
				state.hijriDate = dash.hijri_date || state.hijriDate;
				state.trust = dash.trust || state.trust;
				state.onboarding = dash.onboarding || state.onboarding;
			}
			if ( window.itmms.render ) {
				window.itmms.render();
			}
		} ).catch( function () {
			if ( statusEl ) {
				statusEl.textContent = __( 'Could not save. Please try again.', 'masjidos' );
			}
			if ( btn ) {
				btn.disabled = false;
			}
		} );
	}

	function bindWelcomeEvents() {
		var app = document.getElementById( 'itmms-app' );
		if ( ! app ) {
			return;
		}
		var switchTab = window.itmms.switchTab;

		app.querySelectorAll( '[data-wizard-goto]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var target = parseInt( btn.getAttribute( 'data-wizard-goto' ), 10 );
				if ( target >= 1 && target <= 4 && window.itmms.state ) {
					window.itmms.state.wizardStep = target;
					if ( window.itmms.render ) {
						window.itmms.render();
					}
				}
			} );
		} );

		app.querySelectorAll( '[data-wizard-save]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var stepNum = parseInt( btn.getAttribute( 'data-wizard-save' ), 10 );
				saveWizardStep( stepNum, btn );
			} );
		} );

		app.querySelectorAll( '[data-wizard-city]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var idx = parseInt( btn.getAttribute( 'data-wizard-city' ), 10 );
				var preset = CITY_PRESETS[ idx ];
				if ( ! preset ) {
					return;
				}
				var setVal = function ( id, val ) {
					var el = document.getElementById( id );
					if ( el ) {
						el.value = val;
					}
				};
				setVal( 'itmms-wiz-city', preset.city );
				setVal( 'itmms-wiz-country', preset.country );
				setVal( 'itmms-wiz-lat', preset.lat );
				setVal( 'itmms-wiz-lng', preset.lng );
				setVal( 'itmms-wiz-tz', preset.tz );
				setVal( 'itmms-wiz-method', preset.method );
				setVal( 'itmms-wiz-asr', preset.asr );

				app.querySelectorAll( '[data-wizard-city]' ).forEach( function ( chip ) {
					chip.classList.remove( 'is-active' );
				} );
				btn.classList.add( 'is-active' );
			} );
		} );

		var geoBtn = document.getElementById( 'itmms-wiz-geo' );
		if ( geoBtn ) {
			geoBtn.addEventListener( 'click', function () {
				if ( ! navigator.geolocation ) {
					return;
				}
				geoBtn.disabled = true;
				navigator.geolocation.getCurrentPosition(
					function ( pos ) {
						geoBtn.disabled = false;
						var latEl = document.getElementById( 'itmms-wiz-lat' );
						var lngEl = document.getElementById( 'itmms-wiz-lng' );
						if ( latEl ) {
							latEl.value = pos.coords.latitude.toFixed( 4 );
						}
						if ( lngEl ) {
							lngEl.value = pos.coords.longitude.toFixed( 4 );
						}
					},
					function () {
						geoBtn.disabled = false;
					}
				);
			} );
		}

		app.querySelectorAll( '[data-wizard-copy]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var code = btn.getAttribute( 'data-wizard-copy' ) || '[masjidos_prayer_times]';
				if ( navigator.clipboard && navigator.clipboard.writeText ) {
					navigator.clipboard.writeText( code );
				}
				btn.textContent = __( 'Copied!', 'masjidos' );
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
