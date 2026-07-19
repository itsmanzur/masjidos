/**
 * MasjidOS Admin - Settings Module.
 */
( function () {
	'use strict';

	window.itmms = window.itmms || {};
	var __ = window.wp.i18n.__;
	var sprintf = window.wp.i18n.sprintf;

	// Import shared helpers
	var esc = window.itmms.esc;
	var icon = window.itmms.icon;

	// Expose settings module
	window.itmms.settings = {
		settingsHtml: settingsHtml,
		normalizeKhatib: normalizeKhatib,
		normalizeJumuahSessions: normalizeJumuahSessions,
		bindTimetableEvents: bindTimetableEvents,
		bindIqamahRuleEvents: bindIqamahRuleEvents
	};

	function settingsForm() {
		var state = window.itmms.state;
		var s = state.settings;
		var offsets = s.prayer_offsets || {};
		var iqamah = s.iqamah_times || {};
		var iqamahRules = s.iqamah_rules || {};
		var jumuah = s.jumuah || {};
		var khatib = normalizeKhatib( jumuah.khatib );
		var sessions = normalizeJumuahSessions( jumuah );

		var profilePanel = settingsPanel( __( 'Masjid Profile', 'masjidos' ), __( 'Core identity, location, and timezone details used across widgets.', 'masjidos' ), [
			settingsTrustSummary(),
			selectField( __( 'UI Language', 'masjidos' ), 'ui_language', s.ui_language || 'en', [
				[ 'en', __( 'English', 'masjidos' ) ],
				[ 'bn', __( 'Bangla', 'masjidos' ) ],
				[ 'ar', __( 'Arabic', 'masjidos' ) ]
			] ),
			'<p class="itmms-field itmms-field-wide itmms-settings-note"><small>' + esc( __( 'Changes the MasjidOS interface language for menus and labels. Titles and content you type stay in the language you entered.', 'masjidos' ) ) + '</small></p>',
			field( __( 'Masjid Name', 'masjidos' ), 'masjid_name', s.masjid_name, 'text' ),
			field( __( 'City', 'masjidos' ), 'city', s.city, 'text' ),
			field( __( 'Country', 'masjidos' ), 'country', s.country, 'text' ),
			timezoneField( s.timezone ),
			timezoneHelp(),
			field( __( 'Latitude', 'masjidos' ), 'latitude', s.latitude, 'number', 'any' ),
			field( __( 'Longitude', 'masjidos' ), 'longitude', s.longitude, 'number', 'any' ),
			'<div class="itmms-field itmms-field-wide itmms-field-geolocation" style="margin-top: 5px;">' +
				'<button type="button" class="itmms-btn itmms-btn-ghost" id="itmms-detect-location" style="display: inline-flex; align-items: center; gap: 8px; justify-content: center; width: auto; font-weight: 700; height: 40px; padding: 0 16px;">' +
					icon( 'settings' ) + ' ' + esc( __( 'Detect My Location', 'masjidos' ) ) +
				'</button>' +
			'</div>',
			coordinateHelp()
		].join( '' ) );

		var prayerPanel = settingsPanel( __( 'Prayer Calculation', 'masjidos' ), __( 'Choose the calculation rules used by local prayer time logic.', 'masjidos' ), [
			selectField( __( 'Prayer Time Source', 'masjidos' ), 'prayer_source', s.prayer_source || 'local', [
				[ 'local', __( 'Local Calculation (Offline)', 'masjidos' ) ],
				[ 'aladhan', __( 'Auto API (Aladhan.com)', 'masjidos' ) ]
			] ),
			selectField( __( 'Calculation Method', 'masjidos' ), 'calculation_method', s.calculation_method, [
				[ 'karachi', 'Karachi' ],
				[ 'mwl', __( 'Muslim World League', 'masjidos' ) ],
				[ 'isna', 'ISNA' ],
				[ 'egypt', __( 'Egyptian Authority', 'masjidos' ) ],
				[ 'makkah', __( 'Umm al-Qura, Makkah', 'masjidos' ) ],
				[ 'dubai', __( 'Dubai', 'masjidos' ) ],
				[ 'qatar', __( 'Qatar', 'masjidos' ) ],
				[ 'kuwait', __( 'Kuwait', 'masjidos' ) ],
				[ 'singapore', __( 'Singapore', 'masjidos' ) ],
				[ 'tehran', __( 'Tehran', 'masjidos' ) ],
				[ 'jafari', __( 'Jafari', 'masjidos' ) ]
			] ),
			methodHelp(),
			selectField( __( 'Asr Method', 'masjidos' ), 'asr_method', s.asr_method, [
				[ 'hanafi', 'Hanafi' ],
				[ 'standard', __( 'Standard', 'masjidos' ) ]
			] ),
			fieldWithHelp( __( 'Hijri Date Adjustment', 'masjidos' ), 'hijri_adjustment', s.hijri_adjustment || 0, 'number', '1', __( 'Days, -3 to +3. Use this if your local moon-sighting calendar is one day different.', 'masjidos' ), '-3', '3' ),
			'<label class="itmms-check itmms-field-wide"><input type="checkbox" name="show_ishraq" ' + ( s.show_ishraq !== false ? 'checked' : '' ) + '> ' + esc( __( 'Show Ishraq (sunrise + minutes)', 'masjidos' ) ) + '</label>',
			fieldWithHelp( __( 'Ishraq Minutes After Sunrise', 'masjidos' ), 'ishraq_minutes', s.ishraq_minutes || 15, 'number', '1', __( 'Common range is 15–20 minutes after sunrise.', 'masjidos' ), '5', '45' ),
			'<label class="itmms-check itmms-field-wide"><input type="checkbox" name="show_zawal" ' + ( s.show_zawal !== false ? 'checked' : '' ) + '> ' + esc( __( 'Show Zawal (solar noon / makruh start)', 'masjidos' ) ) + '</label>'
		].join( '' ) );

		var adjustmentsPanel = settingsPanel( __( 'Prayer Time Adjustments', 'masjidos' ), __( 'Fine-tune calculated times to match the official masjid timetable.', 'masjidos' ), [
			offsetField( __( 'Fajr', 'masjidos' ), 'fajr', offsets.fajr ),
			offsetField( __( 'Sunrise', 'masjidos' ), 'sunrise', offsets.sunrise ),
			offsetField( __( 'Dhuhr', 'masjidos' ), 'dhuhr', offsets.dhuhr ),
			offsetField( __( 'Asr', 'masjidos' ), 'asr', offsets.asr ),
			offsetField( __( 'Maghrib', 'masjidos' ), 'maghrib', offsets.maghrib ),
			offsetField( __( 'Isha', 'masjidos' ), 'isha', offsets.isha )
		].join( '' ) );

		var iqamahPanel = settingsPanel( __( 'Iqamah Times', 'masjidos' ), __( 'Set fixed jamaat clocks or dynamic rules such as minutes after Azan.', 'masjidos' ), [
			'<p class="itmms-field-wide itmms-settings-note">' + esc( __( 'CSV timetable Iqamah columns override these rules on imported dates. Otherwise rules apply daily from calculated Azan times.', 'masjidos' ) ) + '</p>',
			iqamahRuleField( __( 'Fajr', 'masjidos' ), 'fajr', iqamah.fajr, iqamahRules.fajr, true ),
			iqamahRuleField( __( 'Dhuhr', 'masjidos' ), 'dhuhr', iqamah.dhuhr, iqamahRules.dhuhr, false ),
			iqamahRuleField( __( 'Asr', 'masjidos' ), 'asr', iqamah.asr, iqamahRules.asr, false ),
			iqamahRuleField( __( 'Maghrib', 'masjidos' ), 'maghrib', iqamah.maghrib, iqamahRules.maghrib, false ),
			iqamahRuleField( __( 'Isha', 'masjidos' ), 'isha', iqamah.isha, iqamahRules.isha, false )
		].join( '' ), 'itmms-settings-panel--iqamah' );

		var timetablePanel = settingsPanel( __( 'CSV Timetable', 'masjidos' ), __( 'Upload your official masjid year timetable with Azan and Iqamah columns. Imported dates override calculated times.', 'masjidos' ), timetablePanelContent(), 'itmms-settings-panel--timetable' );

		var jumuahPanel = settingsPanel( __( 'Jumuah Settings', 'masjidos' ), __( 'Set Friday sessions, khatib profile, topic, language, and public notice.', 'masjidos' ), [
			'<label class="itmms-check itmms-jumuah-enabled"><input type="checkbox" data-jumuah-enabled ' + ( jumuah.enabled !== false ? 'checked' : '' ) + '> ' + esc( __( 'Enable Jumuah widget', 'masjidos' ) ) + '</label>',
			jumuahSessionField( __( 'First Jumuah', 'masjidos' ), 0, sessions[0] ),
			jumuahSessionField( __( 'Second Jumuah', 'masjidos' ), 1, sessions[1] || { label: __( 'Second Jumuah', 'masjidos' ), khutbah_time: '', jamaat_time: '' } ),
			field( __( 'Khutbah Topic', 'masjidos' ), 'jumuah_topic', jumuah.topic || '', 'text' ),
			field( __( 'Khutbah Language', 'masjidos' ), 'jumuah_language', jumuah.language || '', 'text' ),
			field( __( 'Khatib Name', 'masjidos' ), 'jumuah_khatib_name', khatib.name || '', 'text' ),
			mediaField( __( 'Khatib Photo', 'masjidos' ), 'jumuah_khatib_image_url', khatib.image_url || '' ),
			textareaField( __( 'Khatib Short Bio', 'masjidos' ), 'jumuah_khatib_bio', khatib.bio || '', __( 'Example: Imam & Khatib, Madani Masjid.', 'masjidos' ) ),
			textareaField( __( 'Jumuah Notice', 'masjidos' ), 'jumuah_notice', jumuah.notice || '', __( 'Optional public note, for example: Please arrive early.', 'masjidos' ) )
		].join( '' ), 'itmms-settings-panel--jumuah' );

		var tvDisplayUrl = ( window.itmmData.siteUrl || '' ) + '/masjidos-display/';
		var tvPanel = settingsPanel( __( 'TV Display Settings', 'masjidos' ), __( 'Configure fullscreen mosque display layout, slides, quiet mode, overnight dim, theme, and font size.', 'masjidos' ), [
			selectField( __( 'TV Layout', 'masjidos' ), 'tv_layout', s.tv_layout || 'classic', [
				[ 'classic', __( 'Classic — table + countdown', 'masjidos' ) ],
				[ 'split', __( 'Split — large countdown first', 'masjidos' ) ],
				[ 'focus', __( 'Focus — hero countdown + strip', 'masjidos' ) ]
			] ),
			selectField( __( 'TV Theme Style', 'masjidos' ), 'tv_theme', s.tv_theme || 'dark', [
				[ 'dark', __( 'Dark Mode (Gold & Charcoal)', 'masjidos' ) ],
				[ 'light', __( 'Light Mode (Teal & Slate)', 'masjidos' ) ],
				[ 'green', __( 'Green Mode (Emerald & Gold)', 'masjidos' ) ]
			] ),
			selectField( __( 'TV Font Size', 'masjidos' ), 'tv_font_size', s.tv_font_size || 'normal', [
				[ 'small', __( 'Small', 'masjidos' ) ],
				[ 'normal', __( 'Normal', 'masjidos' ) ],
				[ 'large', __( 'Large', 'masjidos' ) ],
				[ 'xlarge', __( 'Extra Large', 'masjidos' ) ]
			] ),
			selectField( __( 'Clock Format', 'masjidos' ), 'tv_clock_format', s.tv_clock_format || '24h', [
				[ '24h', __( '24-hour (14:30:05)', 'masjidos' ) ],
				[ '12h', __( '12-hour (2:30:05 PM)', 'masjidos' ) ]
			] ),
			fieldWithHelp( __( 'Pre-prayer Alert', 'masjidos' ), 'tv_alert_minutes', s.tv_alert_minutes || 10, 'number', '1', __( 'Minutes before Azan/Iqamah to pulse the countdown (1 to 30).', 'masjidos' ), '1', '30' ),
			fieldWithHelp( __( 'Announcement Scroll Speed', 'masjidos' ), 'tv_announcement_speed', s.tv_announcement_speed || 7, 'number', '1', __( 'Lower = faster continuous ticker scroll (3 to 30).', 'masjidos' ), '3', '30' ),
			'<label class="itmms-check itmms-field-wide"><input type="checkbox" name="tv_slides" ' + ( s.tv_slides !== false ? 'checked' : '' ) + '> ' + esc( __( 'Rotate slides (prayer board → notices → Jumuah)', 'masjidos' ) ) + '</label>',
			fieldWithHelp( __( 'Slide Interval', 'masjidos' ), 'tv_slide_interval', s.tv_slide_interval || 12, 'number', '1', __( 'Seconds between slides (6 to 60).', 'masjidos' ), '6', '60' ),
			'<label class="itmms-check itmms-field-wide"><input type="checkbox" name="tv_quiet_enabled" ' + ( s.tv_quiet_enabled !== false ? 'checked' : '' ) + '> ' + esc( __( 'Quiet / Salah mode (pause slides & ticker after Iqamah)', 'masjidos' ) ) + '</label>',
			fieldWithHelp( __( 'Quiet Duration', 'masjidos' ), 'tv_quiet_minutes', s.tv_quiet_minutes || 15, 'number', '1', __( 'Minutes after Iqamah to keep the calm “Prayer in progress” screen (5 to 45).', 'masjidos' ), '5', '45' ),
			'<label class="itmms-check itmms-field-wide"><input type="checkbox" name="tv_dim_enabled" ' + ( s.tv_dim_enabled ? 'checked' : '' ) + '> ' + esc( __( 'Enable overnight dim (screen softens overnight)', 'masjidos' ) ) + '</label>',
			field( __( 'Dim Start', 'masjidos' ), 'tv_dim_start', s.tv_dim_start || '23:00', 'time' ),
			field( __( 'Dim End', 'masjidos' ), 'tv_dim_end', s.tv_dim_end || '04:30', 'time' ),
			mediaField( __( 'TV Custom Logo', 'masjidos' ), 'tv_logo_url', s.tv_logo_url || '' ),
			tvDisplayHelpUrl( tvDisplayUrl )
		].join( '' ) );

		var proData = window.itmms.data || {};
		var proUrl = proData.proUrl || ( proData.pro && proData.pro.url ) || '';
		var proCta = proUrl
			? '<p class="itmms-docs-pro-cta"><a class="itmms-btn itmms-btn-primary" href="' + esc( proUrl ) + '" target="_blank" rel="noopener noreferrer">' +
				esc( __( 'Learn about MasjidOS Pro', 'masjidos' ) ) + '</a></p>'
			: '';
		var publicPanel = settingsPanel( __( 'Coming in Pro', 'masjidos' ), __( 'Finance and public transparency tools ship with MasjidOS Pro — not in the free plugin.', 'masjidos' ), [
			'<div class="itmms-settings-empty itmms-settings-empty--pro">' +
				'<strong>' + esc( __( 'Donations, ledgers & transparency', 'masjidos' ) ) + '</strong>' +
				'<p>' + esc( __( 'Currency, donation tracking, and public transparency reports unlock when MasjidOS Pro is active. Nothing to configure here in Free.', 'masjidos' ) ) + '</p>' +
				proCta +
			'</div>'
		].join( '' ) );

		return '<form class="itmms-settings-form itmms-settings-form--general" id="itmms-settings-form">' +
			'<div class="itmms-settings-tabs" role="tablist" aria-label="' + esc( __( 'Settings sections', 'masjidos' ) ) + '">' +
				settingsTabButton( 'profile', __( 'Profile', 'masjidos' ), true, 'general' ) +
				settingsTabButton( 'jumuah', __( 'Jumuah', 'masjidos' ), false, 'general' ) +
				settingsTabButton( 'tv', __( 'TV Display', 'masjidos' ), false, 'general' ) +
				settingsTabButton( 'public', __( 'Pro', 'masjidos' ), false, 'general' ) +
				settingsTabButton( 'calculation', __( 'Calculation', 'masjidos' ), false, 'prayer' ) +
				settingsTabButton( 'timetable', __( 'Timetable', 'masjidos' ), false, 'prayer' ) +
				settingsTabButton( 'adjustments', __( 'Adjustments', 'masjidos' ), false, 'prayer' ) +
				settingsTabButton( 'iqamah', __( 'Iqamah', 'masjidos' ), false, 'prayer' ) +
			'</div>' +
			'<div class="itmms-settings-tab-panels">' +
				'<div class="itmms-settings-tab-panel active" data-settings-panel="profile" role="tabpanel">' + profilePanel + '</div>' +
				'<div class="itmms-settings-tab-panel" data-settings-panel="calculation" role="tabpanel">' + prayerPanel + '</div>' +
				'<div class="itmms-settings-tab-panel" data-settings-panel="timetable" role="tabpanel">' + timetablePanel + '</div>' +
				'<div class="itmms-settings-tab-panel" data-settings-panel="adjustments" role="tabpanel">' + adjustmentsPanel + '</div>' +
				'<div class="itmms-settings-tab-panel" data-settings-panel="iqamah" role="tabpanel">' + iqamahPanel + '</div>' +
				'<div class="itmms-settings-tab-panel" data-settings-panel="jumuah" role="tabpanel">' + jumuahPanel + '</div>' +
				'<div class="itmms-settings-tab-panel" data-settings-panel="tv" role="tabpanel">' + tvPanel + '</div>' +
				'<div class="itmms-settings-tab-panel" data-settings-panel="public" role="tabpanel">' + publicPanel + '</div>' +
			'</div>' +
			'<div class="itmms-form-actions"><button class="itmms-btn itmms-btn-primary" type="submit">' + esc( __( 'Save Settings', 'masjidos' ) ) + '</button><span id="itmms-save-status"></span></div>' +
		'</form>';
	}

	function settingsTabButton( key, label, active, group ) {
		return '<button type="button" class="itmms-settings-tab' + ( active ? ' active' : '' ) + '" data-settings-tab="' + esc( key ) + '" data-settings-group="' + esc( group || 'general' ) + '" role="tab" aria-selected="' + ( active ? 'true' : 'false' ) + '">' + esc( label ) + '</button>';
	}

	function settingsPanel( title, description, content, className ) {
		return '<section class="itmms-settings-panel ' + esc( className || '' ) + '">' +
			'<header><div><h3>' + esc( title ) + '</h3><p>' + esc( description ) + '</p></div></header>' +
			'<div class="itmms-settings-panel-grid">' + content + '</div>' +
		'</section>';
	}

	function field( label, name, value, type, step ) {
		return '<label class="itmms-field"><span>' + esc( label ) + '</span><input type="' + esc( type ) + '" name="' + esc( name ) + '" value="' + esc( value ) + '"' + ( step ? ' step="' + esc( step ) + '"' : '' ) + '></label>';
	}

	function timezoneField( value ) {
		var options = [
			[ 'Asia/Dhaka', 'Asia/Dhaka (Bangladesh)' ],
			[ 'Asia/Karachi', 'Asia/Karachi' ],
			[ 'Asia/Kolkata', 'Asia/Kolkata' ],
			[ 'Asia/Dubai', 'Asia/Dubai' ],
			[ 'Asia/Riyadh', 'Asia/Riyadh' ],
			[ 'Asia/Qatar', 'Asia/Qatar' ],
			[ 'Asia/Kuwait', 'Asia/Kuwait' ],
			[ 'Asia/Singapore', 'Asia/Singapore' ],
			[ 'Europe/London', 'Europe/London' ],
			[ 'America/New_York', 'America/New_York' ],
			[ 'UTC', 'UTC (not recommended)' ]
		];
		var current = String( value || '' );
		var known = options.some( function ( row ) { return row[0] === current; } );
		var html = '<label class="itmms-field"><span>' + esc( __( 'Timezone', 'masjidos' ) ) + '</span>' +
			'<input type="text" name="timezone" list="itmms-timezone-list" value="' + esc( current ) + '" placeholder="Asia/Dhaka" autocomplete="off">' +
			'<datalist id="itmms-timezone-list">';
		options.forEach( function ( row ) {
			html += '<option value="' + esc( row[0] ) + '">' + esc( row[1] ) + '</option>';
		} );
		if ( current && ! known ) {
			html += '<option value="' + esc( current ) + '">' + esc( current ) + '</option>';
		}
		html += '</datalist><small>' + esc( __( 'Pick a common zone or type any valid IANA timezone.', 'masjidos' ) ) + '</small></label>';
		return html;
	}

	function fieldWithHelp( label, name, value, type, step, help, min, max ) {
		return '<label class="itmms-field"><span>' + esc( label ) + '</span><input type="' + esc( type ) + '" name="' + esc( name ) + '" value="' + esc( value ) + '"' + ( step ? ' step="' + esc( step ) + '"' : '' ) + ( min ? ' min="' + esc( min ) + '"' : '' ) + ( max ? ' max="' + esc( max ) + '"' : '' ) + '><small>' + esc( help ) + '</small></label>';
	}

	function textareaField( label, name, value, placeholder ) {
		return '<label class="itmms-field itmms-field-wide"><span>' + esc( label ) + '</span><textarea name="' + esc( name ) + '" placeholder="' + esc( placeholder || '' ) + '">' + esc( value ) + '</textarea></label>';
	}

	function mediaField( label, name, value ) {
		return '<div class="itmms-field itmms-media-field">' +
			'<span>' + esc( label ) + '</span>' +
			'<div class="itmms-media-picker">' +
				'<div class="itmms-media-preview" data-media-preview>' + ( value ? '<img src="' + esc( value ) + '" alt="">' : '<span>' + esc( __( 'No photo', 'masjidos' ) ) + '</span>' ) + '</div>' +
				'<div>' +
					'<input type="url" name="' + esc( name ) + '" value="' + esc( value || '' ) + '" placeholder="' + esc( __( 'Select from Media Library or paste image URL', 'masjidos' ) ) + '" data-media-url>' +
					'<div class="itmms-media-actions">' +
						'<button type="button" class="itmms-btn itmms-btn-ghost" data-select-media>' + esc( __( 'Select Photo', 'masjidos' ) ) + '</button>' +
						'<button type="button" class="itmms-link-btn" data-remove-media>' + esc( __( 'Remove', 'masjidos' ) ) + '</button>' +
					'</div>' +
					'<small>' + esc( __( 'Upload or choose an image from WordPress Media Library.', 'masjidos' ) ) + '</small>' +
				'</div>' +
			'</div>' +
		'</div>';
	}

	function settingsTrustSummary() {
		var state = window.itmms.state;
		var s = state.settings || {};
		var lat = Number( s.latitude );
		var lng = Number( s.longitude );
		var coordsOk = ! isNaN( lat ) && ! isNaN( lng ) && ( Math.abs( lat ) > 0.0001 || Math.abs( lng ) > 0.0001 ) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180;
		var timezone = String( s.timezone || '' );
		var timezoneOk = !! timezone && [ 'UTC', '+00:00', '-00:00', '' ].indexOf( timezone ) === -1;
		var siteTimezone = window.itmmData && window.itmmData.siteTimezone ? window.itmmData.siteTimezone : '';
		var mismatch = timezoneOk && siteTimezone && timezone !== siteTimezone;
		var source = 'aladhan' === s.prayer_source ? __( 'Auto API', 'masjidos' ) : __( 'Local calculation', 'masjidos' );
		var method = s.calculation_method || 'karachi';
		var asr = s.asr_method || 'hanafi';
		var hijriAdj = Number( s.hijri_adjustment || 0 );
		var hijriLabel = 0 === hijriAdj ? '0' : ( ( hijriAdj > 0 ? '+' : '' ) + hijriAdj );
		var days = state.upcomingDays || [];
		var preview = '';
		if ( days.length ) {
			var rows = days.slice( 0, 7 ).map( function ( day ) {
				var prayers = day.prayers || {};
				return '<tr class="' + ( day.is_today ? 'is-today' : '' ) + '">' +
					'<th scope="row">' + esc( day.label || day.date ) + '</th>' +
					'<td>' + esc( prayers.fajr || '—' ) + '</td>' +
					'<td>' + esc( prayers.dhuhr || '—' ) + '</td>' +
					'<td>' + esc( prayers.asr || '—' ) + '</td>' +
					'<td>' + esc( prayers.maghrib || '—' ) + '</td>' +
					'<td>' + esc( prayers.isha || '—' ) + '</td>' +
				'</tr>';
			} ).join( '' );
			preview = '<details class="itmms-settings-trust__preview">' +
				'<summary>' + esc( __( 'Next 7 days preview', 'masjidos' ) ) + ' — ' + esc( __( 'compare with your printed board', 'masjidos' ) ) + '</summary>' +
				'<div class="itmms-upcoming-scroll"><table class="itmms-upcoming-table"><thead><tr><th scope="col">' + esc( __( 'Date', 'masjidos' ) ) + '</th><th scope="col">' + esc( __( 'Fajr', 'masjidos' ) ) + '</th><th scope="col">' + esc( __( 'Dhuhr', 'masjidos' ) ) + '</th><th scope="col">' + esc( __( 'Asr', 'masjidos' ) ) + '</th><th scope="col">' + esc( __( 'Maghrib', 'masjidos' ) ) + '</th><th scope="col">' + esc( __( 'Isha', 'masjidos' ) ) + '</th></tr></thead><tbody>' + rows + '</tbody></table></div>' +
			'</details>';
		}

		return '<div class="itmms-settings-trust">' +
			'<div class="itmms-settings-trust__head"><strong>' + esc( __( 'Accuracy checklist', 'masjidos' ) ) + '</strong><span>' + esc( source ) + ' · ' + esc( method ) + ' · ' + esc( asr ) + ' · Hijri ' + esc( hijriLabel ) + '</span></div>' +
			'<div class="itmms-settings-trust__checks">' +
				'<span class="' + ( coordsOk ? 'is-ok' : 'is-warn' ) + '">' + esc( coordsOk ? __( 'Coordinates ready', 'masjidos' ) : __( 'Latitude / longitude needed', 'masjidos' ) ) + '</span>' +
				'<span class="' + ( timezoneOk ? 'is-ok' : 'is-warn' ) + '">' + esc( timezoneOk ? __( 'Timezone ready', 'masjidos' ) : __( 'Set a real timezone (not UTC)', 'masjidos' ) ) + '</span>' +
				'<span class="' + ( mismatch ? 'is-warn' : 'is-ok' ) + '">' + esc( mismatch ? sprintf( __( 'Differs from WP timezone (%s)', 'masjidos' ), siteTimezone ) : __( 'Aligned with site timezone', 'masjidos' ) ) + '</span>' +
			'</div>' +
			preview +
		'</div>';
	}

	function coordinateHelp() {
		return '<div class="itmms-coordinate-help">' +
			'<div class="itmms-coordinate-help-icon">' + icon( 'settings' ) + '</div>' +
			'<div>' +
				'<h3>' + esc( __( 'How to find latitude and longitude', 'masjidos' ) ) + '</h3>' +
				'<ol>' +
					'<li>' + esc( __( 'Open Google Maps and search your masjid name or address.', 'masjidos' ) ) + '</li>' +
					'<li>' + esc( __( 'Right-click the exact masjid location on the map.', 'masjidos' ) ) + '</li>' +
					'<li>' + sprintf( esc( __( 'Click the first number pair, for example %s, to copy it.', 'masjidos' ) ), '<code>23.8103, 90.4125</code>' ) + '</li>' +
					'<li>' + esc( __( 'Paste the first number into Latitude and the second number into Longitude.', 'masjidos' ) ) + '</li>' +
				'</ol>' +
				'<p><strong>' + esc( __( 'Tip:', 'masjidos' ) ) + '</strong> ' + esc( __( 'Coordinates are more accurate than city name. If your timetable is still 1-3 minutes different, use Prayer Time Adjustments below.', 'masjidos' ) ) + '</p>' +
				'<a href="https://www.google.com/maps" target="_blank" rel="noopener noreferrer">' + esc( __( 'Open Google Maps', 'masjidos' ) ) + '</a>' +
			'</div>' +
		'</div>';
	}

	function timezoneHelp() {
		var state = window.itmms.state;
		var s = state.settings || {};
		var mismatchWarning = '';

		if ( window.itmmData && window.itmmData.siteTimezone && s.timezone && s.timezone !== window.itmmData.siteTimezone ) {
			mismatchWarning = '<div class="itmms-settings-warning-alert" id="itmms-timezone-mismatch-warning" style="grid-column: 1 / -1; margin-top: 10px; padding: 12px 16px; border-left: 4px solid #f0b840; background: rgba(240, 184, 64, 0.08); border-radius: var(--itmms-radius, 6px); font-size: 13px; color: var(--itmms-text, #111827); line-height: 1.5; width: 100%; box-sizing: border-box;">' +
				'<strong>' + esc( __( 'Timezone Mismatch Warning:', 'masjidos' ) ) + '</strong> ' +
				sprintf( esc( __( 'MasjidOS calculation timezone (%1$s) does not match your WordPress site settings timezone (%2$s). This can cause widget countdown calculations to be incorrect. Please align them.', 'masjidos' ) ), '<code>' + esc( s.timezone ) + '</code>', '<code>' + esc( window.itmmData.siteTimezone ) + '</code>' ) +
			'</div>';
		}

		return '<div class="itmms-inline-help">' +
			'<strong>' + esc( __( 'Timezone matters:', 'masjidos' ) ) + '</strong> ' + sprintf( esc( __( 'For Bangladesh use %1$s. If this is %2$s, prayer times will be calculated as UTC and will look wrong.', 'masjidos' ) ), '<code>Asia/Dhaka</code>', '<code>+00:00</code>' ) +
		'</div>' + mismatchWarning;
	}

	function methodHelp() {
		return '<div class="itmms-inline-help itmms-field-wide">' +
			'<strong>' + esc( __( 'Method tip:', 'masjidos' ) ) + '</strong> ' +
			esc( __( 'Use the method followed by your local masjid or Islamic authority. For Bangladesh, Karachi + Hanafi is a sensible starting point, then adjust minutes if your official timetable differs.', 'masjidos' ) ) +
		'</div>';
	}

	function offsetField( label, key, value ) {
		return '<label class="itmms-field itmms-offset-field"><span>' + esc( sprintf( __( '%s offset', 'masjidos' ), label ) ) + '</span><input type="number" min="-60" max="60" step="1" data-offset="' + esc( key ) + '" value="' + esc( value || 0 ) + '"><small>' + esc( __( 'Minutes, -60 to +60', 'masjidos' ) ) + '</small></label>';
	}

	function iqamahField( label, key, value ) {
		return '<label class="itmms-field itmms-iqamah-field"><span>' + esc( sprintf( __( '%s Iqamah', 'masjidos' ), label ) ) + '</span><input type="time" data-iqamah="' + esc( key ) + '" value="' + esc( value || '' ) + '"><small>' + esc( __( 'Jamaat start time', 'masjidos' ) ) + '</small></label>';
	}

	function iqamahRuleField( label, key, fixedValue, rule, allowBeforeSunrise ) {
		rule = rule || {};
		var mode = rule.mode || 'fixed';
		var minutes = Number( rule.minutes || 0 );
		var round = Number( rule.round || 0 );
		var modeOptions = [
			[ 'fixed', __( 'Fixed time', 'masjidos' ) ],
			[ 'after_azan', __( 'Minutes after Azan', 'masjidos' ) ],
			[ 'none', __( 'Hidden', 'masjidos' ) ]
		];
		if ( allowBeforeSunrise ) {
			modeOptions.splice( 2, 0, [ 'before_sunrise', __( 'Minutes before Sunrise', 'masjidos' ) ] );
		}

		return '<div class="itmms-iqamah-rule itmms-field-wide" data-iqamah-rule="' + esc( key ) + '">' +
			'<div class="itmms-iqamah-rule__head"><strong>' + esc( label ) + '</strong></div>' +
			'<div class="itmms-iqamah-rule__grid">' +
				'<label class="itmms-field"><span>' + esc( __( 'Mode', 'masjidos' ) ) + '</span><select data-iqamah-rule-mode="' + esc( key ) + '">' +
					modeOptions.map( function ( option ) {
						return '<option value="' + esc( option[0] ) + '"' + ( option[0] === mode ? ' selected' : '' ) + '>' + esc( option[1] ) + '</option>';
					} ).join( '' ) +
				'</select></label>' +
				'<label class="itmms-field itmms-iqamah-fixed" data-iqamah-fixed-wrap="' + esc( key ) + '"><span>' + esc( __( 'Fixed Iqamah', 'masjidos' ) ) + '</span><input type="time" data-iqamah="' + esc( key ) + '" value="' + esc( fixedValue || '' ) + '"></label>' +
				'<label class="itmms-field itmms-iqamah-minutes" data-iqamah-minutes-wrap="' + esc( key ) + '"><span>' + esc( __( 'Minutes', 'masjidos' ) ) + '</span><input type="number" min="0" max="180" step="1" data-iqamah-rule-minutes="' + esc( key ) + '" value="' + esc( String( minutes ) ) + '"></label>' +
				'<label class="itmms-field itmms-iqamah-round" data-iqamah-round-wrap="' + esc( key ) + '"><span>' + esc( __( 'Round Azan', 'masjidos' ) ) + '</span><select data-iqamah-rule-round="' + esc( key ) + '">' +
					[ [ '0', __( 'No rounding', 'masjidos' ) ], [ '5', '5 min' ], [ '10', '10 min' ], [ '15', '15 min' ] ].map( function ( option ) {
						return '<option value="' + esc( option[0] ) + '"' + ( String( round ) === option[0] ? ' selected' : '' ) + '>' + esc( option[1] ) + '</option>';
					} ).join( '' ) +
				'</select></label>' +
			'</div>' +
		'</div>';
	}

	function jumuahSessionField( title, index, session ) {
		session = session || {};
		return '<div class="itmms-jumuah-session-field">' +
			'<h4>' + esc( title ) + '</h4>' +
			'<label><span>' + esc( __( 'Label', 'masjidos' ) ) + '</span><input type="text" data-jumuah-session="' + esc( index ) + '" data-jumuah-session-field="label" value="' + esc( session.label || title ) + '"></label>' +
			'<label><span>' + esc( __( 'Khutbah', 'masjidos' ) ) + '</span><input type="time" data-jumuah-session="' + esc( index ) + '" data-jumuah-session-field="khutbah_time" value="' + esc( session.khutbah_time || '' ) + '"></label>' +
			'<label><span>' + esc( __( 'Jamaat', 'masjidos' ) ) + '</span><input type="time" data-jumuah-session="' + esc( index ) + '" data-jumuah-session-field="jamaat_time" value="' + esc( session.jamaat_time || '' ) + '"></label>' +
			'<small>' + esc( index === 1 ? __( 'Leave blank if your masjid has only one Jumuah.', 'masjidos' ) : __( 'Main Friday jamaat shown in the public widget.', 'masjidos' ) ) + '</small>' +
		'</div>';
	}

	function normalizeKhatib( value ) {
		if ( typeof value === 'string' ) {
			return { name: value, image_url: '', bio: '' };
		}
		value = value || {};
		return {
			name: value.name || '',
			image_url: value.image_url || '',
			bio: value.bio || ''
		};
	}

	function normalizeJumuahSessions( jumuah ) {
		if ( Array.isArray( jumuah.sessions ) && jumuah.sessions.length ) {
			return jumuah.sessions;
		}
		return [
			{ label: __( 'First Jumuah', 'masjidos' ), khutbah_time: jumuah.khutbah_time || '13:00', jamaat_time: jumuah.jamaat_time || '13:30' },
			{ label: __( 'Second Jumuah', 'masjidos' ), khutbah_time: '', jamaat_time: '' }
		];
	}

	function tvDisplayHelpUrl( url ) {
		return '<div class="itmms-coordinate-help itmms-field-wide" style="margin-top: 15px;">' +
			'<div class="itmms-coordinate-help-icon">' + icon( 'external' ) + '</div>' +
			'<div>' +
				'<h3>' + esc( __( 'Mosque TV Display Link', 'masjidos' ) ) + '</h3>' +
				'<p style="margin-bottom: 12px; font-size: 13px; color: var(--itmms-public-muted);">' + esc( __( 'Open this URL on your mosque TV, monitor, or display screen to show a beautiful fullscreen board with real-time clock, prayer table, countdown, and announcements.', 'masjidos' ) ) + '</p>' +
				'<div style="display: flex; gap: 10px; align-items: center;">' +
					'<input type="text" readonly value="' + esc( url ) + '" style="flex: 1; font-family: monospace; font-size: 13px; background: #fff; padding: 10px; border: 1px solid #ddd; border-radius: 4px;" onclick="this.select();">' +
					'<button type="button" class="itmms-btn itmms-btn-ghost" data-copy-tv-url data-url="' + esc( url ) + '" style="height: 40px; font-weight: 700;">' + esc( __( 'Copy Link', 'masjidos' ) ) + '</button>' +
					'<a href="' + esc( url ) + '" target="_blank" class="itmms-btn itmms-btn-primary" style="display: inline-flex; align-items: center; justify-content: center; height: 40px; padding: 0 16px; font-weight: 700; text-decoration: none; border-radius: 4px;">' + esc( __( 'Open Display', 'masjidos' ) ) + '</a>' +
				'</div>' +
				'<p style="margin-top: 10px; font-size: 12px; color: #667085;"><strong>' + esc( __( 'Tip:', 'masjidos' ) ) + '</strong> ' + esc( __( 'Override layout, theme, language, and font size in the URL, for example: ', 'masjidos' ) ) + '<code>' + esc( url ) + '?layout=focus&theme=green&lang=bn&font_size=large</code>. ' + esc( __( 'Quiet mode: ', 'masjidos' ) ) + '<code>?quiet=0</code> ' + esc( __( 'or', 'masjidos' ) ) + ' <code>?quiet=1</code></p>' +
			'</div>' +
		'</div>';
	}

	function selectField( label, name, value, options ) {
		return '<label class="itmms-field"><span>' + esc( label ) + '</span><select name="' + esc( name ) + '">' +
			options.map( function ( option ) {
				return '<option value="' + esc( option[0] ) + '"' + ( option[0] === value ? ' selected' : '' ) + '>' + esc( option[1] ) + '</option>';
			} ).join( '' ) +
		'</select></label>';
	}

	function timetablePanelContent() {
		var state = window.itmms.state;
		var summary = state.timetable || {};
		var count = Number( summary.count || 0 );
		var rangeLabel = summary.start_date && summary.end_date
			? summary.start_date + ' → ' + summary.end_date
			: __( 'No imported timetable yet', 'masjidos' );
		var year = new Date().getFullYear();
		var selectedYear = String( state.timetableYear || year );
		var yearOptions = [
			[ String( year - 1 ), String( year - 1 ) ],
			[ String( year ), String( year ) ],
			[ String( year + 1 ), String( year + 1 ) ]
		];
		var yearsMap = summary.years || {};
		var yearKeys = Object.keys( yearsMap );
		var yearsNote = yearKeys.length
			? yearKeys.map( function ( y ) {
				return y + ': ' + yearsMap[ y ];
			} ).join( ' · ' )
			: '';
		var largeWarn = summary.large
			? '<p class="itmms-timetable-warn itmms-field-wide">' + esc( __( 'Large store detected. Prefer one year per import, or clear an old year to keep the site light.', 'masjidos' ) ) + '</p>'
			: '';

		return '<div class="itmms-timetable-summary itmms-field-wide" data-timetable-summary>' +
			'<div class="itmms-timetable-summary__stat"><strong data-timetable-count>' + esc( String( count ) ) + '</strong><span>' + esc( __( 'days loaded', 'masjidos' ) ) + '</span></div>' +
			'<div class="itmms-timetable-summary__range"><strong>' + esc( __( 'Coverage', 'masjidos' ) ) + '</strong><span data-timetable-range>' + esc( rangeLabel ) + '</span></div>' +
			( yearsNote ? '<div class="itmms-timetable-summary__years"><strong>' + esc( __( 'By year', 'masjidos' ) ) + '</strong><span data-timetable-years>' + esc( yearsNote ) + '</span></div>' : '' ) +
		'</div>' +
		largeWarn +
		'<div class="itmms-field itmms-field-wide">' +
			'<span>' + esc( __( 'CSV File', 'masjidos' ) ) + '</span>' +
			'<input type="file" accept=".csv,text/csv" data-timetable-file>' +
			'<small>' + esc( __( 'Columns: date, fajr, sunrise, dhuhr, asr, maghrib, isha, and optional *_iqamah columns. Max ~2 MB / one year.', 'masjidos' ) ) + '</small>' +
		'</div>' +
		selectField( __( 'Working year', 'masjidos' ), 'timetable_year', selectedYear, yearOptions ) +
		selectField( __( 'Import Mode', 'masjidos' ), 'timetable_import_mode', 'merge', [
			[ 'merge', __( 'Merge rows by date', 'masjidos' ) ],
			[ 'replace', __( 'Replace all imported days', 'masjidos' ) ]
		] ) +
		'<div class="itmms-timetable-actions itmms-field-wide">' +
			'<button type="button" class="itmms-btn itmms-btn-ghost" data-timetable-sample>' + esc( __( 'Sample CSV', 'masjidos' ) ) + '</button>' +
			'<button type="button" class="itmms-btn itmms-btn-ghost" data-timetable-validate>' + esc( __( 'Validate CSV', 'masjidos' ) ) + '</button>' +
			'<button type="button" class="itmms-btn itmms-btn-primary" data-timetable-import>' + esc( __( 'Import CSV', 'masjidos' ) ) + '</button>' +
			'<button type="button" class="itmms-btn itmms-btn-ghost" data-timetable-export>' + esc( __( 'Export Imported Year', 'masjidos' ) ) + '</button>' +
			'<button type="button" class="itmms-btn itmms-btn-ghost" data-timetable-export-calculated>' + esc( __( 'Export Calculated Year', 'masjidos' ) ) + '</button>' +
			'<button type="button" class="itmms-link-btn" data-timetable-clear-year>' + esc( __( 'Clear Year', 'masjidos' ) ) + '</button>' +
			'<button type="button" class="itmms-link-btn" data-timetable-clear>' + esc( __( 'Clear All', 'masjidos' ) ) + '</button>' +
		'</div>' +
		'<p class="itmms-timetable-status itmms-field-wide" data-timetable-status></p>' +
		'<div class="itmms-coordinate-help itmms-field-wide">' +
			'<div class="itmms-coordinate-help-icon">' + icon( 'ledger' ) + '</div>' +
			'<div>' +
				'<h3>' + esc( __( 'How CSV timetable works', 'masjidos' ) ) + '</h3>' +
				'<ol>' +
					'<li>' + esc( __( 'Export Calculated Year as a starting template, or download the Sample CSV.', 'masjidos' ) ) + '</li>' +
					'<li>' + esc( __( 'Edit times to match your official mosque committee table (YYYY-MM-DD + 24h or AM/PM).', 'masjidos' ) ) + '</li>' +
					'<li>' + esc( __( 'Validate CSV, then Import. Widgets and TV use imported days as official times.', 'masjidos' ) ) + '</li>' +
					'<li>' + esc( __( 'Dates without CSV rows still use your calculation settings and Iqamah rules.', 'masjidos' ) ) + '</li>' +
				'</ol>' +
			'</div>' +
		'</div>';
	}

	function bindIqamahRuleEvents( root ) {
		if ( ! root ) {
			return;
		}

		function syncRuleRow( key ) {
			var modeSelect = root.querySelector( '[data-iqamah-rule-mode="' + key + '"]' );
			if ( ! modeSelect ) {
				return;
			}
			var mode = modeSelect.value;
			var fixedWrap = root.querySelector( '[data-iqamah-fixed-wrap="' + key + '"]' );
			var minutesWrap = root.querySelector( '[data-iqamah-minutes-wrap="' + key + '"]' );
			var roundWrap = root.querySelector( '[data-iqamah-round-wrap="' + key + '"]' );
			if ( fixedWrap ) {
				fixedWrap.style.display = 'fixed' === mode ? '' : 'none';
			}
			if ( minutesWrap ) {
				minutesWrap.style.display = ( 'after_azan' === mode || 'before_sunrise' === mode ) ? '' : 'none';
			}
			if ( roundWrap ) {
				roundWrap.style.display = 'after_azan' === mode ? '' : 'none';
			}
		}

		root.querySelectorAll( '[data-iqamah-rule-mode]' ).forEach( function ( select ) {
			var key = select.getAttribute( 'data-iqamah-rule-mode' );
			syncRuleRow( key );
			select.addEventListener( 'change', function () {
				syncRuleRow( key );
			} );
		} );
	}

	function bindTimetableEvents( root ) {
		if ( ! root || root.getAttribute( 'data-timetable-bound' ) === '1' ) {
			return;
		}
		root.setAttribute( 'data-timetable-bound', '1' );

		var api = window.itmms.api;
		var downloadCsv = window.itmms.downloadCsv;
		var status = root.querySelector( '[data-timetable-status]' );
		var fileInput = root.querySelector( '[data-timetable-file]' );
		var importBtn = root.querySelector( '[data-timetable-import]' );
		var validateBtn = root.querySelector( '[data-timetable-validate]' );
		var yearSelect = root.querySelector( '[name="timetable_year"]' );

		function setStatus( message, isError ) {
			if ( ! status ) {
				return;
			}
			status.textContent = message || '';
			status.classList.toggle( 'is-error', !! isError );
		}

		function workingYear() {
			if ( yearSelect && yearSelect.value ) {
				window.itmms.state.timetableYear = yearSelect.value;
				return yearSelect.value;
			}
			return String( ( window.itmms.state && window.itmms.state.timetableYear ) || new Date().getFullYear() );
		}

		function applySummary( summary ) {
			if ( ! summary ) {
				return;
			}
			window.itmms.state.timetable = summary;
			var countEl = root.querySelector( '[data-timetable-count]' );
			var rangeEl = root.querySelector( '[data-timetable-range]' );
			var yearsEl = root.querySelector( '[data-timetable-years]' );
			if ( countEl ) {
				countEl.textContent = String( summary.count || 0 );
			}
			if ( rangeEl ) {
				rangeEl.textContent = summary.start_date && summary.end_date
					? summary.start_date + ' → ' + summary.end_date
					: __( 'No imported timetable yet', 'masjidos' );
			}
			if ( yearsEl && summary.years ) {
				yearsEl.textContent = Object.keys( summary.years ).map( function ( y ) {
					return y + ': ' + summary.years[ y ];
				} ).join( ' · ' );
			}
		}

		function refreshDashboard() {
			return api( 'dashboard' ).then( function ( response ) {
				var state = window.itmms.state;
				state.settings = response.settings || state.settings;
				state.prayers = response.prayers || state.prayers;
				state.nextPrayer = response.next_prayer || state.nextPrayer;
				state.prayerMeta = response.prayer_meta || state.prayerMeta;
				state.trust = response.trust || state.trust;
				state.upcomingDays = response.upcoming_days || [];
				state.timetable = response.timetable || state.timetable;
				applySummary( state.timetable );
			} );
		}

		function readCsvFile() {
			return new Promise( function ( resolve, reject ) {
				var file = fileInput && fileInput.files && fileInput.files[0];
				if ( ! file ) {
					reject( new Error( __( 'Choose a CSV file first.', 'masjidos' ) ) );
					return;
				}
				if ( file.size > 2 * 1024 * 1024 ) {
					reject( new Error( __( 'CSV file is too large. Import one year at a time (about 365 rows).', 'masjidos' ) ) );
					return;
				}
				var reader = new FileReader();
				reader.onload = function () {
					resolve( String( reader.result || '' ) );
				};
				reader.onerror = function () {
					reject( new Error( __( 'Could not read the CSV file.', 'masjidos' ) ) );
				};
				reader.readAsText( file );
			} );
		}

		if ( yearSelect ) {
			yearSelect.addEventListener( 'change', function () {
				window.itmms.state.timetableYear = yearSelect.value;
			} );
		}

		if ( validateBtn && fileInput ) {
			validateBtn.addEventListener( 'click', function () {
				setStatus( __( 'Validating CSV...', 'masjidos' ), false );
				validateBtn.disabled = true;
				readCsvFile().then( function ( csv ) {
					return api( 'prayer-times/timetable/import', {
						method: 'POST',
						body: JSON.stringify( {
							csv: csv,
							mode: 'merge',
							dry_run: true
						} )
					} );
				} ).then( function ( response ) {
					var message = sprintf(
						__( 'Valid: %1$d day(s). Errors: %2$d. Range: %3$s → %4$s.', 'masjidos' ),
						Number( response.valid || 0 ),
						Number( response.error_count || ( response.errors && response.errors.length ) || 0 ),
						response.start_date || '—',
						response.end_date || '—'
					);
					if ( response.errors && response.errors.length ) {
						message += ' ' + response.errors.slice( 0, 2 ).join( ' ' );
					}
					setStatus( message, Number( response.error_count || 0 ) > 0 && ! response.valid );
				} ).catch( function ( error ) {
					setStatus( error.message || __( 'Validation failed.', 'masjidos' ), true );
				} ).finally( function () {
					validateBtn.disabled = false;
				} );
			} );
		}

		if ( importBtn && fileInput ) {
			importBtn.addEventListener( 'click', function () {
				var modeSelect = root.querySelector( '[name="timetable_import_mode"]' );
				var mode = modeSelect ? modeSelect.value : 'merge';
				setStatus( __( 'Importing timetable...', 'masjidos' ), false );
				importBtn.disabled = true;

				readCsvFile().then( function ( csv ) {
					return api( 'prayer-times/timetable/import', {
						method: 'POST',
						body: JSON.stringify( {
							csv: csv,
							mode: mode
						} )
					} );
				} ).then( function ( response ) {
					applySummary( response.summary || window.itmms.state.timetable );
					var message = sprintf(
						__( 'Imported %1$d day(s). Skipped %2$d row(s).', 'masjidos' ),
						Number( response.imported || 0 ),
						Number( response.skipped || 0 )
					);
					if ( response.errors && response.errors.length ) {
						message += ' ' + response.errors.slice( 0, 3 ).join( ' ' );
					}
					setStatus( message, false );
					fileInput.value = '';
					return refreshDashboard();
				} ).catch( function ( error ) {
					setStatus( error.message || __( 'Import failed.', 'masjidos' ), true );
				} ).finally( function () {
					importBtn.disabled = false;
					if ( window.itmms.state && typeof window.itmms.render === 'function' ) {
						window.itmms.render();
					}
				} );
			} );
		}

		var sampleBtn = root.querySelector( '[data-timetable-sample]' );
		if ( sampleBtn ) {
			sampleBtn.addEventListener( 'click', function () {
				downloadCsv( 'prayer-times/timetable/sample', 'masjidos-prayer-timetable-sample.csv' ).catch( function () {
					setStatus( __( 'Sample download failed.', 'masjidos' ), true );
				} );
			} );
		}

		var exportBtn = root.querySelector( '[data-timetable-export]' );
		if ( exportBtn ) {
			exportBtn.addEventListener( 'click', function () {
				var year = workingYear();
				downloadCsv(
					'prayer-times/timetable/export?year=' + encodeURIComponent( year ),
					'masjidos-prayer-timetable-' + year + '.csv'
				).catch( function () {
					setStatus( __( 'Export failed.', 'masjidos' ), true );
				} );
			} );
		}

		var exportCalculatedBtn = root.querySelector( '[data-timetable-export-calculated]' );
		if ( exportCalculatedBtn ) {
			exportCalculatedBtn.addEventListener( 'click', function () {
				var year = workingYear();
				downloadCsv(
					'prayer-times/timetable/export?source=calculated&year=' + encodeURIComponent( year ),
					'masjidos-calculated-' + year + '.csv'
				).catch( function () {
					setStatus( __( 'Calculated export failed.', 'masjidos' ), true );
				} );
			} );
		}

		var clearYearBtn = root.querySelector( '[data-timetable-clear-year]' );
		if ( clearYearBtn ) {
			clearYearBtn.addEventListener( 'click', function () {
				var year = workingYear();
				if ( ! window.confirm( sprintf( __( 'Remove all imported days for %s?', 'masjidos' ), year ) ) ) {
					return;
				}
				api( 'prayer-times/timetable?year=' + encodeURIComponent( year ), { method: 'DELETE' } ).then( function ( response ) {
					applySummary( response.summary || { count: 0, active: false } );
					setStatus( sprintf( __( 'Cleared %1$d day(s) from %2$s.', 'masjidos' ), Number( response.removed || 0 ), year ), false );
					return refreshDashboard();
				} ).then( function () {
					if ( typeof window.itmms.render === 'function' ) {
						window.itmms.render();
					}
				} ).catch( function () {
					setStatus( __( 'Could not clear year.', 'masjidos' ), true );
				} );
			} );
		}

		var clearBtn = root.querySelector( '[data-timetable-clear]' );
		if ( clearBtn ) {
			clearBtn.addEventListener( 'click', function () {
				if ( ! window.confirm( __( 'Remove all imported timetable days?', 'masjidos' ) ) ) {
					return;
				}
				api( 'prayer-times/timetable', { method: 'DELETE' } ).then( function ( response ) {
					applySummary( response.summary || { count: 0, active: false } );
					setStatus( __( 'Imported timetable cleared.', 'masjidos' ), false );
					return refreshDashboard();
				} ).then( function () {
					if ( typeof window.itmms.render === 'function' ) {
						window.itmms.render();
					}
				} ).catch( function () {
					setStatus( __( 'Could not clear timetable.', 'masjidos' ), true );
				} );
			} );
		}
	}

	function settingsHtml() {
		return settingsForm();
	}
} )();
