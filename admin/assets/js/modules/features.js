/**
 * MasjidOS Admin - Features Showcase Module.
 */
( function () {
	'use strict';

	window.itmms = window.itmms || {};
	var __ = window.wp.i18n.__;
	var esc = window.itmms.esc;
	var icon = window.itmms.icon;

	// Expose features module
	window.itmms.features = {
		featuresHtml: featuresHtml,
		bindFeaturesEvents: bindFeaturesEvents
	};

	// ── Feature Definitions ──────────────────────────────────────────
	function languageOptions() {
		return [
			{ v: 'en', l: __( 'English', 'masjidos' ) },
			{ v: 'bn', l: __( 'Bangla', 'masjidos' ) },
			{ v: 'ar', l: __( 'Arabic', 'masjidos' ) }
		];
	}
	function getFeatures() {
		var data = window.itmms.data || {};
		var tvUrl = ( data.siteUrl || '' ) + '/masjidos-display/';
		var langs = languageOptions();

		return [
			{
				id: 'prayer-times',
				icon: 'compass',
				color: 'teal',
				group: 'widgets',
				name: __( 'Prayer Times Widget', 'masjidos' ),
				desc: __( 'Displays today\'s prayer times with countdown to next prayer, Qibla compass, and Hijri date.', 'masjidos' ),
				shortcode: '[masjidos_prayer_times]',
				params: [
					{ key: 'language', label: __( 'Language', 'masjidos' ), type: 'select', options: langs },
					{ key: 'design', label: __( 'Design', 'masjidos' ), type: 'select', options: [ { v: 'classic', l: __( 'Classic', 'masjidos' ) }, { v: 'compact', l: __( 'Compact', 'masjidos' ) } ] },
					{ key: 'iqamah', label: __( 'Show Iqamah', 'masjidos' ), type: 'toggle', default: 'yes' },
					{ key: 'qibla', label: __( 'Show Qibla', 'masjidos' ), type: 'toggle', default: 'yes' }
				],
				restEndpoint: 'prayer-widget',
				restQuery: function( opts ) { return '?language=' + opts.language + '&design=' + opts.design + '&iqamah=' + opts.iqamah + '&qibla=' + opts.qibla; },
				badge: 'Core'
			},
			{
				id: 'monthly-timetable',
				icon: 'table',
				color: 'blue',
				group: 'widgets',
				name: __( 'Monthly Timetable', 'masjidos' ),
				desc: __( 'Full month prayer timetable with Hijri dates, print support, and month navigation.', 'masjidos' ),
				shortcode: '[masjidos_monthly_prayer_times]',
				params: [
					{ key: 'language', label: __( 'Language', 'masjidos' ), type: 'select', options: langs },
					{ key: 'iqamah', label: __( 'Show Iqamah', 'masjidos' ), type: 'toggle', default: 'no' }
				],
				restEndpoint: 'monthly-prayer-widget',
				restQuery: function( opts ) { return '?language=' + opts.language + '&iqamah=' + opts.iqamah; },
				badge: 'Core'
			},
			{
				id: 'islamic-calendar',
				icon: 'crescent',
				color: 'green',
				group: 'widgets',
				name: __( 'Islamic Calendar', 'masjidos' ),
				desc: __( 'Dual Hijri + Gregorian calendar with Islamic holy days highlighted and mosque events overlay.', 'masjidos' ),
				shortcode: '[masjidos_islamic_calendar]',
				params: [
					{ key: 'language', label: __( 'Language', 'masjidos' ), type: 'select', options: langs }
				],
				restEndpoint: 'calendar',
				restQuery: function( opts ) { return '?language=' + opts.language; },
				badge: 'New'
			},
			{
				id: 'duas-azkar',
				icon: 'hands',
				color: 'teal',
				group: 'widgets',
				name: __( 'Duas & Azkar', 'masjidos' ),
				desc: __( 'Daily duas and azkar with Arabic, meaning, counter, share, and audio.', 'masjidos' ),
				shortcode: '[masjidos_duas_azkar]',
				params: [
					{ key: 'language', label: __( 'Language', 'masjidos' ), type: 'select', options: langs },
					{ key: 'design', label: __( 'Design', 'masjidos' ), type: 'select', options: [ { v: 'cards', l: __( 'Cards', 'masjidos' ) }, { v: 'compact', l: __( 'Compact', 'masjidos' ) } ] },
					{ key: 'category', label: __( 'Category', 'masjidos' ), type: 'select', options: [ { v: 'all', l: __( 'All', 'masjidos' ) }, { v: 'daily', l: __( 'Daily', 'masjidos' ) }, { v: 'morning', l: __( 'Morning', 'masjidos' ) }, { v: 'evening', l: __( 'Evening', 'masjidos' ) }, { v: 'food', l: __( 'Food', 'masjidos' ) }, { v: 'sleep', l: __( 'Sleep', 'masjidos' ) }, { v: 'home', l: __( 'Home', 'masjidos' ) }, { v: 'masjid', l: __( 'Masjid', 'masjidos' ) }, { v: 'travel', l: __( 'Travel', 'masjidos' ) }, { v: 'rain', l: __( 'Rain', 'masjidos' ) }, { v: 'forgiveness', l: __( 'Forgiveness', 'masjidos' ) }, { v: 'quran', l: __( 'Quranic', 'masjidos' ) }, { v: 'protection', l: __( 'Protection', 'masjidos' ) } ] },
					{ key: 'counter', label: __( 'Show Counter', 'masjidos' ), type: 'toggle', default: 'yes' },
					{ key: 'share', label: __( 'Show Share Button', 'masjidos' ), type: 'toggle', default: 'yes' },
					{ key: 'audio', label: __( 'Show Audio Button', 'masjidos' ), type: 'toggle', default: 'yes' }
				],
				restEndpoint: 'duas-azkar-widget',
				restQuery: function( opts ) { return '?language=' + opts.language + '&design=' + opts.design + '&category=' + opts.category + '&counter=' + opts.counter + '&share=' + opts.share + '&audio=' + opts.audio; },
				badge: 'New'
			},
			{
				id: 'jumuah',
				icon: 'mic',
				color: 'gold',
				group: 'widgets',
				name: __( 'Jumuah Times', 'masjidos' ),
				desc: __( 'Friday prayer schedule with Khutbah & Jamaat times, multiple sessions support.', 'masjidos' ),
				shortcode: '[masjidos_jumuah]',
				params: [
					{ key: 'language', label: __( 'Language', 'masjidos' ), type: 'select', options: langs }
				],
				restEndpoint: 'jumuah-widget',
				restQuery: function( opts ) { return '?language=' + opts.language; },
				badge: 'Core'
			},
			{
				id: 'announcements',
				icon: 'megaphone',
				color: 'orange',
				group: 'widgets',
				name: __( 'Notices Widget', 'masjidos' ),
				desc: __( 'Scrolling or list-style mosque announcements with priority, types, and expiry dates.', 'masjidos' ),
				shortcode: '[masjidos_announcements]',
				params: [
					{ key: 'language', label: __( 'Language', 'masjidos' ), type: 'select', options: langs },
					{ key: 'design', label: __( 'Design', 'masjidos' ), type: 'select', options: [ { v: 'list', l: __( 'List', 'masjidos' ) }, { v: 'ticker', l: __( 'Ticker', 'masjidos' ) }, { v: 'banner', l: __( 'Banner', 'masjidos' ) }, { v: 'popup', l: __( 'Popup Modal', 'masjidos' ) } ] }
				],
				restEndpoint: 'announcements-widget',
				restQuery: function( opts ) { return '?language=' + opts.language + '&design=' + opts.design; },
				badge: 'Core'
			},
			{
				id: 'events',
				icon: 'calendar',
				color: 'purple',
				group: 'widgets',
				name: __( 'Events Widget', 'masjidos' ),
				desc: __( 'Upcoming mosque events list with date, time, location, and description.', 'masjidos' ),
				shortcode: '[masjidos_events]',
				params: [
					{ key: 'language', label: __( 'Language', 'masjidos' ), type: 'select', options: langs }
				],
				restEndpoint: 'events-widget',
				restQuery: function( opts ) { return '?language=' + opts.language; },
				badge: 'Core'
			},
			{
				id: 'khutbah-archive',
				icon: 'books',
				color: 'gold',
				group: 'minbar',
				name: __( 'Khutbah Archive', 'masjidos' ),
				desc: __( 'Searchable public archive of Friday khutbahs with categories, audio, and PDF downloads.', 'masjidos' ),
				shortcode: '[masjidos_khutbah_archive]',
				params: [
					{ key: 'language', label: __( 'Language', 'masjidos' ), type: 'select', options: langs },
					{ key: 'limit', label: __( 'Limit', 'masjidos' ), type: 'text', placeholder: '12', default: '12' },
					{ key: 'category', label: __( 'Category', 'masjidos' ), type: 'text', placeholder: 'fiqh' }
				],
				badge: 'Minbar'
			},
			{
				id: 'khatib-this-week',
				icon: 'members',
				color: 'teal',
				group: 'minbar',
				name: __( 'This Week\'s Khatib', 'masjidos' ),
				desc: __( 'Show the scheduled khatib and topic for this Friday from the Minbar roster.', 'masjidos' ),
				shortcode: '[masjidos_khatib_this_week]',
				params: [
					{ key: 'language', label: __( 'Language', 'masjidos' ), type: 'select', options: langs }
				],
				badge: 'Minbar'
			},
			{
				id: 'upcoming-khutbah',
				icon: 'list',
				color: 'teal',
				group: 'minbar',
				name: __( 'Upcoming Khutbahs', 'masjidos' ),
				desc: __( 'List upcoming scheduled or planned Friday topics for your congregation.', 'masjidos' ),
				shortcode: '[masjidos_upcoming_khutbah]',
				params: [
					{ key: 'language', label: __( 'Language', 'masjidos' ), type: 'select', options: langs },
					{ key: 'limit', label: __( 'Limit', 'masjidos' ), type: 'text', placeholder: '5', default: '5' }
				],
				badge: 'Minbar'
			},
			{
				id: 'khutbah-search',
				icon: 'search',
				color: 'gold',
				group: 'minbar',
				name: __( 'Khutbah Search', 'masjidos' ),
				desc: __( 'Compact public search widget for the Minbar archive.', 'masjidos' ),
				shortcode: '[masjidos_khutbah_search]',
				params: [
					{ key: 'language', label: __( 'Language', 'masjidos' ), type: 'select', options: langs },
					{ key: 'limit', label: __( 'Limit', 'masjidos' ), type: 'text', placeholder: '6', default: '6' }
				],
				badge: 'Minbar'
			},
			{
				id: 'quran-verse',
				icon: 'quote',
				color: 'teal',
				group: 'widgets',
				name: __( 'Quran Verse of the Day', 'masjidos' ),
				desc: __( 'A daily verse from the Holy Quran in Arabic with English or Bangla meanings.', 'masjidos' ),
				shortcode: '[masjidos_quran_verse]',
				params: [
					{ key: 'language', label: __( 'Language', 'masjidos' ), type: 'select', options: [ { v: 'en', l: __( 'English', 'masjidos' ) }, { v: 'bn', l: __( 'Bangla', 'masjidos' ) } ] }
				],
				badge: 'New'
			},
			{
				id: 'hadith',
				icon: 'scroll',
				color: 'purple',
				group: 'widgets',
				name: __( 'Hadith of the Day', 'masjidos' ),
				desc: __( 'A daily Hadith from authentic collections with meanings and sharing buttons.', 'masjidos' ),
				shortcode: '[masjidos_hadith]',
				params: [
					{ key: 'language', label: __( 'Language', 'masjidos' ), type: 'select', options: [ { v: 'en', l: __( 'English', 'masjidos' ) }, { v: 'bn', l: __( 'Bangla', 'masjidos' ) } ] }
				],
				badge: 'New'
			},
			{
				id: 'allah-names',
				icon: 'star',
				color: 'gold',
				group: 'widgets',
				name: __( '99 Names of Allah', 'masjidos' ),
				desc: __( 'Displays the beautiful 99 names of Allah (Asmaul Husna) in a grid with meanings.', 'masjidos' ),
				shortcode: '[masjidos_allah_names]',
				params: [
					{ key: 'language', label: __( 'Language', 'masjidos' ), type: 'select', options: [ { v: 'en', l: __( 'English', 'masjidos' ) }, { v: 'bn', l: __( 'Bangla', 'masjidos' ) } ] }
				],
				badge: 'New'
			},
			{
				id: 'audio-quran',
				icon: 'volume',
				color: 'blue',
				group: 'widgets',
				name: __( 'Audio Quran Player', 'masjidos' ),
				desc: __( 'An embedded audio player for listening to Surah recitations.', 'masjidos' ),
				shortcode: '[masjidos_audio_quran]',
				params: [
					{ key: 'language', label: __( 'Language', 'masjidos' ), type: 'select', options: langs }
				],
				badge: 'New'
			},
			{
				id: 'articles',
				icon: 'article',
				color: 'teal',
				group: 'widgets',
				name: __( 'Islamic Articles', 'masjidos' ),
				desc: __( 'List published Islamic articles with optional category filter.', 'masjidos' ),
				shortcode: '[masjidos_articles]',
				params: [
					{ key: 'language', label: __( 'Language', 'masjidos' ), type: 'select', options: langs },
					{ key: 'category', label: __( 'Category slug', 'masjidos' ), type: 'text', placeholder: 'fiqh' },
					{ key: 'limit', label: __( 'Limit', 'masjidos' ), type: 'text', placeholder: '6', default: '6' },
					{ key: 'excerpt', label: __( 'Show excerpt', 'masjidos' ), type: 'select', options: [ { v: 'yes', l: __( 'Yes', 'masjidos' ) }, { v: 'no', l: __( 'No', 'masjidos' ) } ] }
				],
				badge: 'New'
			},
			{
				id: 'tv-display',
				icon: 'monitor',
				color: 'dark',
				group: 'display',
				name: __( 'TV Display Mode', 'masjidos' ),
				desc: __( 'Fullscreen mosque TV board — prayer times, countdown, Hijri date, and notices.', 'masjidos' ),
				shortcode: tvUrl,
				tvUrl: tvUrl,
				params: [],
				badge: 'New'
			}
		];
	}

	// ── HTML Builder ──────────────────────────────────────────────────
	function paramControlHtml( f, p ) {
		if ( p.type === 'select' ) {
			return '<label class="itmms-feat-param">' +
				'<span>' + esc( p.label ) + '</span>' +
				'<select data-feat-id="' + esc( f.id ) + '" data-feat-param="' + esc( p.key ) + '">' +
				p.options.map( function( o ) { return '<option value="' + esc( o.v ) + '">' + esc( o.l ) + '</option>'; } ).join( '' ) +
				'</select>' +
				'</label>';
		}
		if ( p.type === 'toggle' ) {
			var isOn = p.default === 'yes';
			return '<label class="itmms-feat-param itmms-feat-param--toggle">' +
				'<span>' + esc( p.label ) + '</span>' +
				'<button type="button" class="itmms-feat-toggle' + ( isOn ? ' on' : '' ) + '" ' +
				'data-feat-id="' + esc( f.id ) + '" data-feat-param="' + esc( p.key ) + '" ' +
				'data-on="yes" data-off="no" aria-pressed="' + ( isOn ? 'true' : 'false' ) + '">' +
				'</button>' +
				'</label>';
		}
		if ( p.type === 'text' || p.type === 'number' ) {
			return '<label class="itmms-feat-param itmms-feat-param--text">' +
				'<span>' + esc( p.label ) + '</span>' +
				'<input type="' + ( p.type === 'number' ? 'number' : 'text' ) + '" data-feat-id="' + esc( f.id ) + '" data-feat-param="' + esc( p.key ) + '" value="' + esc( p.default || '' ) + '" placeholder="' + esc( p.placeholder || '' ) + '">' +
				'</label>';
		}
		return '';
	}

	function featureCardHtml( f ) {
		var badgeClass = f.badge === 'New' ? 'itmms-feat-badge--new' : ( f.badge === 'Pro' ? 'itmms-feat-badge--pro' : ( f.badge === 'Minbar' ? 'itmms-feat-badge--minbar' : '' ) );
		var hasParams = f.params && f.params.length;
		var paramsHtml = hasParams
			? '<div class="itmms-feat-params" id="itmms-feat-params-' + esc( f.id ) + '" hidden>' +
				f.params.map( function( p ) { return paramControlHtml( f, p ); } ).join( '' ) +
				'</div>'
			: '';

		var shortcodeBox = f.tvUrl
			? '<div class="itmms-feat-shortcode"><span class="itmms-feat-shortcode-label">URL</span>' +
			  '<code id="itmms-feat-code-' + esc( f.id ) + '">' + esc( f.tvUrl ) + '</code>' +
			  '<button type="button" class="itmms-feat-copy" data-feat-copy="' + esc( f.id ) + '" title="' + esc( __( 'Copy URL', 'masjidos' ) ) + '">' + icon( 'ledger' ) + '</button>' +
			  '</div>'
			: '<div class="itmms-feat-shortcode"><span class="itmms-feat-shortcode-label">Shortcode</span>' +
			  '<code id="itmms-feat-code-' + esc( f.id ) + '">' + esc( f.shortcode ) + '</code>' +
			  '<button type="button" class="itmms-feat-copy" data-feat-copy="' + esc( f.id ) + '" title="' + esc( __( 'Copy shortcode', 'masjidos' ) ) + '">' + icon( 'ledger' ) + '</button>' +
			  '</div>';

		var actions = '';
		if ( hasParams ) {
			actions += '<button type="button" class="itmms-feat-btn itmms-feat-btn--ghost" data-feat-customize="' + esc( f.id ) + '" aria-expanded="false">' +
				'<span>' + esc( __( 'Customize', 'masjidos' ) ) + '</span></button>';
		}
		if ( f.tvUrl ) {
			actions += '<a class="itmms-feat-btn itmms-feat-btn--preview" href="' + esc( f.tvUrl ) + '" target="_blank" rel="noopener">' +
				icon( 'external' ) + '<span>' + esc( __( 'Open TV', 'masjidos' ) ) + '</span></a>';
		} else {
			actions += '<button type="button" class="itmms-feat-btn itmms-feat-btn--preview" data-feat-preview="' + esc( f.id ) + '">' +
				icon( 'monitor' ) +
				'<span>' + esc( __( 'Preview', 'masjidos' ) ) + '</span></button>';
		}

		return '<article class="itmms-feat-card itmms-feat-card--' + esc( f.color ) + '" id="itmms-feat-card-' + esc( f.id ) + '" data-feat-group="' + esc( f.group || 'widgets' ) + '">' +
			'<div class="itmms-feat-card-header">' +
				'<div class="itmms-feat-icon itmms-feat-icon--' + esc( f.color ) + '">' + icon( f.icon ) + '</div>' +
				'<span class="itmms-feat-badge ' + badgeClass + '">' + esc( f.badge ) + '</span>' +
			'</div>' +
			'<h3 class="itmms-feat-name">' + esc( f.name ) + '</h3>' +
			'<p class="itmms-feat-desc">' + esc( f.desc ) + '</p>' +
			paramsHtml +
			shortcodeBox +
			'<div class="itmms-feat-actions">' + actions + '</div>' +
			'</article>';
	}

	function featuresHtml() {
		var features = getFeatures();
		var filter = ( window.itmms.state && window.itmms.state.featuresFilter ) || 'all';
		var tabs = [
			{ id: 'all', label: __( 'All', 'masjidos' ) },
			{ id: 'widgets', label: __( 'Widgets', 'masjidos' ) },
			{ id: 'minbar', label: __( 'Minbar', 'masjidos' ) },
			{ id: 'display', label: __( 'Display', 'masjidos' ) }
		];

		return '<div class="itmms-features-page">' +
			'<div class="itmms-feat-toolbar">' +
				'<div class="itmms-feat-tabs" role="tablist">' +
					tabs.map( function( t ) {
						return '<button type="button" class="itmms-feat-tab' + ( filter === t.id ? ' is-active' : '' ) + '" data-feat-filter="' + esc( t.id ) + '" role="tab" aria-selected="' + ( filter === t.id ? 'true' : 'false' ) + '">' + esc( t.label ) + '</button>';
					} ).join( '' ) +
				'</div>' +
				'<p class="itmms-feat-hint">' + esc( __( 'Copy a shortcode, customize options, then paste on any page. Need attribute details? See Docs.', 'masjidos' ) ) + '</p>' +
			'</div>' +
			'<div class="itmms-feat-grid">' + features.map( featureCardHtml ).join( '' ) + '</div>' +
			previewModalHtml() +
			'</div>';
	}

	// ── Preview Modal HTML ─────────────────────────────────────────────
	function previewModalHtml() {
		return '<div class="itmms-preview-modal" id="itmms-preview-modal" role="dialog" aria-modal="true" aria-labelledby="itmms-preview-modal-title" hidden>' +
			'<div class="itmms-preview-modal__backdrop" id="itmms-preview-backdrop"></div>' +
			'<div class="itmms-preview-modal__panel">' +
				'<div class="itmms-preview-modal__header">' +
					'<h3 id="itmms-preview-modal-title">' + esc( __( 'Live Preview', 'masjidos' ) ) + '</h3>' +
					'<div class="itmms-preview-modal__actions">' +
						'<button type="button" class="itmms-preview-viewport-btn active" data-viewport="desktop" title="' + esc( __( 'Desktop view', 'masjidos' ) ) + '">' +
							'<svg viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2"></rect><path d="M8 21h8M12 17v4"></path></svg>' +
						'</button>' +
						'<button type="button" class="itmms-preview-viewport-btn" data-viewport="mobile" title="' + esc( __( 'Mobile view', 'masjidos' ) ) + '">' +
							'<svg viewBox="0 0 24 24"><rect x="5" y="2" width="14" height="20" rx="2"></rect><circle cx="12" cy="18" r="1"></circle></svg>' +
						'</button>' +
						'<button type="button" class="itmms-preview-close" id="itmms-preview-close" aria-label="' + esc( __( 'Close preview', 'masjidos' ) ) + '">' +
							'<svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>' +
						'</button>' +
					'</div>' +
				'</div>' +
				'<div class="itmms-preview-modal__body" id="itmms-preview-body">' +
					'<div class="itmms-preview-loading" id="itmms-preview-loading">' +
						'<div class="itmms-preview-spinner"></div>' +
						'<p>' + esc( __( 'Loading preview…', 'masjidos' ) ) + '</p>' +
					'</div>' +
					'<div class="itmms-preview-content" id="itmms-preview-content"></div>' +
				'</div>' +
			'</div>' +
		'</div>';
	}

	// ── Event Bindings ────────────────────────────────────────────────
	function applyFeatureFilter( app, filter ) {
		filter = filter || 'all';
		if ( window.itmms.state ) {
			window.itmms.state.featuresFilter = filter;
		}
		app.querySelectorAll( '.itmms-feat-tab' ).forEach( function( tab ) {
			var active = tab.getAttribute( 'data-feat-filter' ) === filter;
			tab.classList.toggle( 'is-active', active );
			tab.setAttribute( 'aria-selected', active ? 'true' : 'false' );
		} );
		app.querySelectorAll( '.itmms-feat-card[data-feat-group]' ).forEach( function( card ) {
			var group = card.getAttribute( 'data-feat-group' );
			card.hidden = filter !== 'all' && group !== filter;
		} );
	}

	function bindFeaturesEvents() {
		var app = document.getElementById( 'itmms-app' );
		if ( ! app ) return;

		var data = window.itmms.data || {};
		var features = getFeatures();
		applyFeatureFilter( app, ( window.itmms.state && window.itmms.state.featuresFilter ) || 'all' );

		if ( app._itmmsFeaturesBound ) {
			return;
		}
		app._itmmsFeaturesBound = true;

		// Build feature state map for param tracking
		var featState = {};
		features.forEach( function( f ) {
			featState[ f.id ] = {};
			( f.params || [] ).forEach( function( p ) {
				featState[ f.id ][ p.key ] = p.default || ( p.options && p.options[0] ? p.options[0].v : '' );
			} );
		} );

		// Rebuild shortcode from current params
		function rebuildShortcode( featId ) {
			var f = features.find( function(x){ return x.id === featId; } );
			if ( ! f ) return;
			var code = app.querySelector( '#itmms-feat-code-' + featId );
			if ( ! code ) return;

			if ( f.tvUrl ) return; // URL — don't rebuild

			var params = featState[ featId ] || {};
			var atts = Object.keys( params ).filter( function( k ) {
				return params[ k ] !== '' && params[ k ] != null;
			} ).map( function( k ) {
				return k + '="' + params[k] + '"';
			} );
			var tag = '[' + f.shortcode.replace( /^\[|\]$/g, '' ).split( ' ' )[0];
			code.textContent = atts.length ? tag + ' ' + atts.join( ' ' ) + ']' : tag + ']';
		}

		// Filter tabs
		app.addEventListener( 'click', function( e ) {
			var filterBtn = e.target.closest( '[data-feat-filter]' );
			if ( ! filterBtn || ! app.contains( filterBtn ) ) return;
			applyFeatureFilter( app, filterBtn.getAttribute( 'data-feat-filter' ) || 'all' );
		} );

		// Customize expand/collapse
		app.addEventListener( 'click', function( e ) {
			var btn = e.target.closest( '[data-feat-customize]' );
			if ( ! btn || ! app.contains( btn ) ) return;
			var featId = btn.getAttribute( 'data-feat-customize' );
			var panel = app.querySelector( '#itmms-feat-params-' + featId );
			var card = app.querySelector( '#itmms-feat-card-' + featId );
			if ( ! panel ) return;
			var open = panel.hasAttribute( 'hidden' );
			if ( open ) {
				panel.removeAttribute( 'hidden' );
			} else {
				panel.setAttribute( 'hidden', '' );
			}
			btn.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			btn.classList.toggle( 'is-open', open );
			btn.querySelector( 'span' ).textContent = open ? __( 'Hide options', 'masjidos' ) : __( 'Customize', 'masjidos' );
			if ( card ) {
				card.classList.toggle( 'is-customizing', open );
			}
		} );

		// Param select / text changes
		app.addEventListener( 'change', function( e ) {
			var el = e.target.closest( '[data-feat-param]' );
			if ( ! el || ! app.contains( el ) ) return;
			var featId = el.getAttribute( 'data-feat-id' );
			var param = el.getAttribute( 'data-feat-param' );
			if ( ! featState[ featId ] ) return;
			featState[ featId ][ param ] = el.value;
			rebuildShortcode( featId );
		} );

		app.addEventListener( 'input', function( e ) {
			var el = e.target.closest( 'input[data-feat-param]' );
			if ( ! el || ! app.contains( el ) ) return;
			var featId = el.getAttribute( 'data-feat-id' );
			var param = el.getAttribute( 'data-feat-param' );
			if ( ! featState[ featId ] ) return;
			featState[ featId ][ param ] = el.value;
			rebuildShortcode( featId );
		} );

		// Toggle buttons
		app.addEventListener( 'click', function( e ) {
			var toggle = e.target.closest( 'button.itmms-feat-toggle[data-feat-param]' );
			if ( ! toggle || ! app.contains( toggle ) ) return;
			var featId = toggle.getAttribute( 'data-feat-id' );
			var param = toggle.getAttribute( 'data-feat-param' );
			var isOn = toggle.classList.contains( 'on' );
			toggle.classList.toggle( 'on', ! isOn );
			toggle.setAttribute( 'aria-pressed', ! isOn ? 'true' : 'false' );
			featState[ featId ][ param ] = ! isOn ? ( toggle.getAttribute( 'data-on' ) || 'yes' ) : ( toggle.getAttribute( 'data-off' ) || 'no' );
			rebuildShortcode( featId );
		} );

		// Copy shortcode / URL
		function copyText( text ) {
			if ( navigator.clipboard && navigator.clipboard.writeText ) {
				return navigator.clipboard.writeText( text );
			}

			var area = document.createElement( 'textarea' );
			area.value = text;
			area.setAttribute( 'readonly', '' );
			area.style.position = 'fixed';
			area.style.opacity = '0';
			document.body.appendChild( area );
			area.select();
			document.execCommand( 'copy' );
			document.body.removeChild( area );
			return Promise.resolve();
		}

		app.addEventListener( 'click', function( e ) {
			var copyBtn = e.target.closest( '[data-feat-copy]' );
			if ( ! copyBtn || ! app.contains( copyBtn ) ) return;
			var featId = copyBtn.getAttribute( 'data-feat-copy' );
			var code = app.querySelector( '#itmms-feat-code-' + featId );
			if ( ! code ) return;
			copyText( code.textContent ).then( function() {
				copyBtn.classList.add( 'copied' );
				setTimeout( function() { copyBtn.classList.remove( 'copied' ); }, 1800 );
			} );
		} );

		// Live Preview open
		app.addEventListener( 'click', function( e ) {
			var previewBtn = e.target.closest( '[data-feat-preview]' );
			if ( ! previewBtn || ! app.contains( previewBtn ) ) return;
			var featId = previewBtn.getAttribute( 'data-feat-preview' );
			openPreviewModal( featId, featState, features, data );
		} );

		// Viewport toggle inside modal
		app.addEventListener( 'click', function( e ) {
			var vpBtn = e.target.closest( '.itmms-preview-viewport-btn[data-viewport]' );
			if ( ! vpBtn || ! app.contains( vpBtn ) ) return;
			var vp = vpBtn.getAttribute( 'data-viewport' );
			app.querySelectorAll( '.itmms-preview-viewport-btn' ).forEach( function(b){ b.classList.remove('active'); } );
			vpBtn.classList.add( 'active' );
			var content = app.querySelector( '#itmms-preview-content' );
			if ( content ) {
				content.setAttribute( 'data-viewport', vp );
			}
		} );

		// Close modal (button, backdrop, Escape)
		app.addEventListener( 'click', function( e ) {
			if ( e.target.closest( '#itmms-preview-close' ) || e.target.closest( '#itmms-preview-backdrop' ) ) {
				closePreviewModal();
			}
		} );

		document.addEventListener( 'keydown', function( e ) {
			if ( e.key === 'Escape' ) {
				closePreviewModal();
			}
		} );
	}

	// ── Preview Modal Logic ───────────────────────────────────────────
	function openPreviewModal( featId, featState, features, data ) {
		var modal = document.getElementById( 'itmms-preview-modal' );
		var loading = document.getElementById( 'itmms-preview-loading' );
		var content = document.getElementById( 'itmms-preview-content' );
		var titleEl = document.getElementById( 'itmms-preview-modal-title' );
		if ( ! modal || ! loading || ! content ) return;

		var f = features.find( function(x){ return x.id === featId; } );
		if ( ! f ) return;

		// Show modal
		modal.removeAttribute( 'hidden' );
		document.body.style.overflow = 'hidden';
		loading.style.display = 'flex';
		content.innerHTML = '';
		content.removeAttribute( 'data-viewport' );
		if ( titleEl ) { titleEl.textContent = f.name + ' — Live Preview'; }

		// Reset viewport buttons
		var app = document.getElementById( 'itmms-app' );
		if ( app ) {
			app.querySelectorAll( '.itmms-preview-viewport-btn' ).forEach( function(b){
				b.classList.toggle( 'active', b.getAttribute( 'data-viewport' ) === 'desktop' );
			} );
		}

		// Build REST URL
		if ( ! f.restEndpoint ) {
			loading.style.display = 'none';
			content.innerHTML = '<div class="itmms-preview-error"><p>' +
				'Live preview is not supported for this widget. Simply copy the shortcode above and paste it on any WordPress page.' +
				'</p></div>';
			return;
		}

		var params = featState[ featId ] || {};
		var queryString = f.restQuery ? f.restQuery( params ) : '';
		var restBase = ( data.restUrl || '' ).replace( /\/$/, '' );
		var endpoint = restBase + '/' + f.restEndpoint + queryString;

		fetch( endpoint, {
			headers: { 'X-WP-Nonce': data.nonce, 'Accept': 'application/json' }
		} )
			.then( function( r ) { return r.json(); } )
			.then( function( payload ) {
				loading.style.display = 'none';
				content.innerHTML =
					'<div class="itmms-preview-frame">' +
						'<div class="itmms-preview-frame__device">' +
							( payload.html || '<p style="padding:24px;color:#667085;">No preview available.</p>' ) +
						'</div>' +
					'</div>';
				if ( window.itmmsPublicRefresh ) {
					window.itmmsPublicRefresh( content );
				}
			} )
			.catch( function() {
				loading.style.display = 'none';
				content.innerHTML = '<div class="itmms-preview-error"><p>' +
					'⚠ Preview could not be loaded. Check that the module is enabled in <strong>Settings</strong>.' +
					'</p></div>';
			} );
	}

	function closePreviewModal() {
		var modal = document.getElementById( 'itmms-preview-modal' );
		if ( modal ) {
			modal.setAttribute( 'hidden', '' );
			document.body.style.overflow = '';
			var content = document.getElementById( 'itmms-preview-content' );
			if ( content ) { content.innerHTML = ''; }
		}
	}

} )();
