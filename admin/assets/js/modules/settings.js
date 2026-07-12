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
		normalizeJumuahSessions: normalizeJumuahSessions
	};

	function settingsForm() {
		var state = window.itmms.state;
		var s = state.settings;
		var offsets = s.prayer_offsets || {};
		var iqamah = s.iqamah_times || {};
		var jumuah = s.jumuah || {};
		var khatib = normalizeKhatib( jumuah.khatib );
		var sessions = normalizeJumuahSessions( jumuah );

		var profilePanel = settingsPanel( __( 'Masjid Profile', 'masjidos' ), __( 'Core identity, location, and timezone details used across widgets.', 'masjidos' ), [
			field( __( 'Masjid Name', 'masjidos' ), 'masjid_name', s.masjid_name, 'text' ),
			field( __( 'City', 'masjidos' ), 'city', s.city, 'text' ),
			field( __( 'Country', 'masjidos' ), 'country', s.country, 'text' ),
			field( __( 'Timezone', 'masjidos' ), 'timezone', s.timezone, 'text' ),
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
			selectField( __( 'Currency', 'masjidos' ), 'currency', s.currency, [
				[ 'BDT', 'BDT' ],
				[ 'USD', 'USD' ],
				[ 'GBP', 'GBP' ],
				[ 'EUR', 'EUR' ],
				[ 'SAR', 'SAR' ]
			] )
		].join( '' ) );

		var adjustmentsPanel = settingsPanel( __( 'Prayer Time Adjustments', 'masjidos' ), __( 'Fine-tune calculated times to match the official masjid timetable.', 'masjidos' ), [
			offsetField( __( 'Fajr', 'masjidos' ), 'fajr', offsets.fajr ),
			offsetField( __( 'Sunrise', 'masjidos' ), 'sunrise', offsets.sunrise ),
			offsetField( __( 'Dhuhr', 'masjidos' ), 'dhuhr', offsets.dhuhr ),
			offsetField( __( 'Asr', 'masjidos' ), 'asr', offsets.asr ),
			offsetField( __( 'Maghrib', 'masjidos' ), 'maghrib', offsets.maghrib ),
			offsetField( __( 'Isha', 'masjidos' ), 'isha', offsets.isha )
		].join( '' ) );

		var iqamahPanel = settingsPanel( __( 'Iqamah Times', 'masjidos' ), __( 'Set jamaat start times shown on the dashboard and public prayer widget.', 'masjidos' ), [
			iqamahField( __( 'Fajr', 'masjidos' ), 'fajr', iqamah.fajr ),
			iqamahField( __( 'Dhuhr', 'masjidos' ), 'dhuhr', iqamah.dhuhr ),
			iqamahField( __( 'Asr', 'masjidos' ), 'asr', iqamah.asr ),
			iqamahField( __( 'Maghrib', 'masjidos' ), 'maghrib', iqamah.maghrib ),
			iqamahField( __( 'Isha', 'masjidos' ), 'isha', iqamah.isha )
		].join( '' ) );

		var jumuahPanel = settingsPanel( __( 'Jumuah Settings', 'masjidos' ), __( 'Set Friday sessions, khatib profile, topic, language, and public notice.', 'masjidos' ), [
			'<label class="itmms-check itmms-jumuah-enabled"><input type="checkbox" data-jumuah-enabled ' + ( jumuah.enabled !== false ? 'checked' : '' ) + '> ' + esc( __( 'Enable Jumuah widget', 'masjidos' ) ) + '</label>',
			jumuahSessionField( __( 'First Jumuah', 'masjidos' ), 0, sessions[0] ),
			jumuahSessionField( __( 'Second Jumuah', 'masjidos' ), 1, sessions[1] || { label: __( 'Second Jumuah', 'masjidos' ), khutbah_time: '', jamaat_time: '' } ),
			field( __( 'Khutbah Topic', 'masjidos' ), 'jumuah_topic', jumuah.topic || '', 'text' ),
			field( __( 'Khutbah Language', 'masjidos' ), 'jumuah_language', jumuah.language || '', 'text' ),
			field( __( 'Khatib Name', 'masjidos' ), 'jumuah_khatib_name', khatib.name || '', 'text' ),
			mediaField( __( 'Khatib Photo', 'masjidos' ), 'jumuah_khatib_image_url', khatib.image_url || '' ),
			textareaField( __( 'Khatib Short Bio', 'masjidos' ), 'jumuah_khatib_bio', khatib.bio || '', __( 'Example: Imam & Khatib, Powerup Masjid.', 'masjidos' ) ),
			textareaField( __( 'Jumuah Notice', 'masjidos' ), 'jumuah_notice', jumuah.notice || '', __( 'Optional public note, for example: Please arrive early.', 'masjidos' ) )
		].join( '' ), 'itmms-settings-panel--jumuah' );

		var tvDisplayUrl = ( window.itmmData.siteUrl || '' ) + '/masjidos-display/';
		var tvPanel = settingsPanel( __( 'TV Display Settings', 'masjidos' ), __( 'Configure fullscreen mosque display layout, theme, and font size options.', 'masjidos' ), [
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
			fieldWithHelp( __( 'Announcement Scroll Speed', 'masjidos' ), 'tv_announcement_speed', s.tv_announcement_speed || 7, 'number', '1', __( 'Number of seconds to display each announcement (3 to 30 seconds).', 'masjidos' ), '3', '30' ),
			mediaField( __( 'TV Custom Logo', 'masjidos' ), 'tv_logo_url', s.tv_logo_url || '' ),
			tvDisplayHelpUrl( tvDisplayUrl )
		].join( '' ) );

		return '<form class="itmms-settings-form" id="itmms-settings-form">' +
			'<div class="itmms-settings-tabs" role="tablist">' +
				settingsTabButton( 'profile', __( 'Profile', 'masjidos' ), true ) +
				settingsTabButton( 'calculation', __( 'Calculation', 'masjidos' ), false ) +
				settingsTabButton( 'adjustments', __( 'Adjustments', 'masjidos' ), false ) +
				settingsTabButton( 'iqamah', __( 'Iqamah', 'masjidos' ), false ) +
				settingsTabButton( 'jumuah', __( 'Jumuah', 'masjidos' ), false ) +
				settingsTabButton( 'tv', __( 'TV Display', 'masjidos' ), false ) +
				settingsTabButton( 'public', __( 'Public', 'masjidos' ), false ) +
			'</div>' +
			'<div class="itmms-settings-tab-panels">' +
				'<div class="itmms-settings-tab-panel active" data-settings-panel="profile">' + profilePanel + '</div>' +
				'<div class="itmms-settings-tab-panel" data-settings-panel="calculation">' + prayerPanel + '</div>' +
				'<div class="itmms-settings-tab-panel" data-settings-panel="adjustments">' + adjustmentsPanel + '</div>' +
				'<div class="itmms-settings-tab-panel" data-settings-panel="iqamah">' + iqamahPanel + '</div>' +
				'<div class="itmms-settings-tab-panel" data-settings-panel="jumuah">' + jumuahPanel + '</div>' +
				'<div class="itmms-settings-tab-panel" data-settings-panel="tv">' + tvPanel + '</div>' +
				'<div class="itmms-settings-tab-panel" data-settings-panel="public">' + settingsPanel( __( 'Public Options', 'masjidos' ), __( 'Small public-facing switches for transparency and display preferences.', 'masjidos' ), '<label class="itmms-check"><input type="checkbox" name="public_transparency" ' + ( s.public_transparency ? 'checked' : '' ) + '> ' + esc( __( 'Public transparency reports', 'masjidos' ) ) + '</label>' ) + '</div>' +
			'</div>' +
			'<div class="itmms-form-actions"><button class="itmms-btn itmms-btn-primary" type="submit">' + esc( __( 'Save Settings', 'masjidos' ) ) + '</button><span id="itmms-save-status"></span></div>' +
		'</form>';
	}

	function settingsTabButton( key, label, active ) {
		return '<button type="button" class="itmms-settings-tab' + ( active ? ' active' : '' ) + '" data-settings-tab="' + esc( key ) + '">' + esc( label ) + '</button>';
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
				'<p style="margin-top: 10px; font-size: 12px; color: #667085;"><strong>' + esc( __( 'Tip:', 'masjidos' ) ) + '</strong> ' + esc( __( 'You can override theme, language, and font size in the URL directly, for example: ', 'masjidos' ) ) + '<code>' + esc( url ) + '?theme=green&lang=bn&font_size=large</code></p>' +
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

	function settingsHtml() {
		return '<div class="itmms-section-heading"><h2>' + esc( __( 'Settings', 'masjidos' ) ) + '</h2><p>' + esc( __( 'Organized setup panels for masjid profile, prayer times, Iqamah, and Jumuah.', 'masjidos' ) ) + '</p></div>' + settingsForm();
	}
} )();
