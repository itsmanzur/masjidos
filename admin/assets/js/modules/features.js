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
	function getFeatures() {
		var data = window.itmms.data || {};
		var tvUrl = ( data.siteUrl || '' ) + '/masjidos-display/';

		return [
			{
				id: 'prayer-times',
				icon: 'clock',
				color: 'teal',
				name: __( 'Prayer Times Widget', 'masjidos' ),
				desc: __( 'Displays today\'s prayer times with countdown to next prayer, Qibla compass, and Hijri date.', 'masjidos' ),
				shortcode: '[masjidos_prayer_times]',
				params: [
					{ key: 'language', label: __( 'Language', 'masjidos' ), type: 'select', options: [ { v: 'en', l: 'English' }, { v: 'bn', l: 'বাংলা' }, { v: 'ar', l: 'العربية' } ] },
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
				icon: 'calendar',
				color: 'blue',
				name: __( 'Monthly Timetable', 'masjidos' ),
				desc: __( 'Full month prayer timetable with Hijri dates, print support, and month navigation.', 'masjidos' ),
				shortcode: '[masjidos_monthly_prayer_times]',
				params: [
					{ key: 'language', label: __( 'Language', 'masjidos' ), type: 'select', options: [ { v: 'en', l: 'English' }, { v: 'bn', l: 'বাংলা' }, { v: 'ar', l: 'العربية' } ] },
					{ key: 'iqamah', label: __( 'Show Iqamah', 'masjidos' ), type: 'toggle', default: 'no' }
				],
				restEndpoint: 'monthly-prayer-widget',
				restQuery: function( opts ) { return '?language=' + opts.language + '&iqamah=' + opts.iqamah; },
				badge: 'Core'
			},
			{
				id: 'islamic-calendar',
				icon: 'calendar',
				color: 'green',
				name: __( 'Islamic Calendar', 'masjidos' ),
				desc: __( 'Dual Hijri + Gregorian calendar with Islamic holy days highlighted and mosque events overlay.', 'masjidos' ),
				shortcode: '[masjidos_islamic_calendar]',
				params: [
					{ key: 'language', label: __( 'Language', 'masjidos' ), type: 'select', options: [ { v: 'en', l: 'English' }, { v: 'bn', l: 'বাংলা' }, { v: 'ar', l: 'العربية' } ] }
				],
				restEndpoint: 'calendar',
				restQuery: function( opts ) { return '?language=' + opts.language; },
				badge: 'New'
			},
			{
				id: 'jumuah',
				icon: 'clock',
				color: 'gold',
				name: __( 'Jumuah Times', 'masjidos' ),
				desc: __( 'Friday prayer schedule with Khutbah & Jamaat times, multiple sessions support.', 'masjidos' ),
				shortcode: '[masjidos_jumuah]',
				params: [
					{ key: 'language', label: __( 'Language', 'masjidos' ), type: 'select', options: [ { v: 'en', l: 'English' }, { v: 'bn', l: 'বাংলা' }, { v: 'ar', l: 'العربية' } ] }
				],
				restEndpoint: 'jumuah-widget',
				restQuery: function( opts ) { return '?language=' + opts.language; },
				badge: 'Core'
			},
			{
				id: 'announcements',
				icon: 'megaphone',
				color: 'orange',
				name: __( 'Notices Widget', 'masjidos' ),
				desc: __( 'Scrolling or list-style mosque announcements with priority, types, and expiry dates.', 'masjidos' ),
				shortcode: '[masjidos_announcements]',
				params: [
					{ key: 'language', label: __( 'Language', 'masjidos' ), type: 'select', options: [ { v: 'en', l: 'English' }, { v: 'bn', l: 'বাংলা' }, { v: 'ar', l: 'العربية' } ] },
					{ key: 'design', label: __( 'Design', 'masjidos' ), type: 'select', options: [ { v: 'list', l: __( 'List', 'masjidos' ) }, { v: 'ticker', l: __( 'Ticker', 'masjidos' ) } ] }
				],
				restEndpoint: 'announcements-widget',
				restQuery: function( opts ) { return '?language=' + opts.language + '&design=' + opts.design; },
				badge: 'Core'
			},
			{
				id: 'events',
				icon: 'calendar',
				color: 'purple',
				name: __( 'Events Widget', 'masjidos' ),
				desc: __( 'Upcoming mosque events list with date, time, location, and description.', 'masjidos' ),
				shortcode: '[masjidos_events]',
				params: [
					{ key: 'language', label: __( 'Language', 'masjidos' ), type: 'select', options: [ { v: 'en', l: 'English' }, { v: 'bn', l: 'বাংলা' }, { v: 'ar', l: 'العربية' } ] }
				],
				restEndpoint: 'events-widget',
				restQuery: function( opts ) { return '?language=' + opts.language; },
				badge: 'Core'
			},
			{
				id: 'tv-display',
				icon: 'external',
				color: 'dark',
				name: __( 'TV Display Mode', 'masjidos' ),
				desc: __( 'Fullscreen mosque TV board — shows prayer times, countdown, Hijri date, and scrolling notices.', 'masjidos' ),
				shortcode: tvUrl,
				tvUrl: tvUrl,
				params: [],
				badge: 'New'
			}
		];
	}

	// ── HTML Builder ──────────────────────────────────────────────────
	function featuresHtml() {
		var features = getFeatures();

		var cards = features.map( function( f ) {
			var badgeClass = f.badge === 'New' ? 'itmms-feat-badge--new' : ( f.badge === 'Pro' ? 'itmms-feat-badge--pro' : '' );
			var paramsHtml = '';
			if ( f.params && f.params.length ) {
				paramsHtml = '<div class="itmms-feat-params" id="itmms-feat-params-' + esc( f.id ) + '">' +
					f.params.map( function( p ) {
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
						return '';
					} ).join( '' ) +
					'</div>';
			}

			var shortcodeBox = f.tvUrl
				? '<div class="itmms-feat-shortcode"><span class="itmms-feat-shortcode-label">URL</span>' +
				  '<code id="itmms-feat-code-' + esc( f.id ) + '">' + esc( f.tvUrl ) + '</code>' +
				  '<button class="itmms-feat-copy" data-feat-copy="' + esc( f.id ) + '" title="' + esc( __( 'Copy URL', 'masjidos' ) ) + '">' + icon( 'ledger' ) + '</button>' +
				  '</div>'
				: '<div class="itmms-feat-shortcode"><span class="itmms-feat-shortcode-label">Shortcode</span>' +
				  '<code id="itmms-feat-code-' + esc( f.id ) + '">' + esc( f.shortcode ) + '</code>' +
				  '<button class="itmms-feat-copy" data-feat-copy="' + esc( f.id ) + '" title="' + esc( __( 'Copy shortcode', 'masjidos' ) ) + '">' + icon( 'ledger' ) + '</button>' +
				  '</div>';

			var actions = f.tvUrl
				? '<a class="itmms-feat-btn itmms-feat-btn--preview" href="' + esc( f.tvUrl ) + '" target="_blank" rel="noopener">' +
				  icon( 'external' ) + '<span>' + esc( __( 'Open TV Screen', 'masjidos' ) ) + '</span></a>'
				: '<button type="button" class="itmms-feat-btn itmms-feat-btn--preview" data-feat-preview="' + esc( f.id ) + '">' +
				  '<svg viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2"></rect><path d="M8 21h8M12 17v4"></path></svg>' +
				  '<span>' + esc( __( 'Live Preview', 'masjidos' ) ) + '</span></button>';

			return '<article class="itmms-feat-card itmms-feat-card--' + esc( f.color ) + '" id="itmms-feat-card-' + esc( f.id ) + '">' +
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
		} ).join( '' );

		return '<div class="itmms-features-page">' +
			'<div class="itmms-features-hero">' +
				'<div class="itmms-features-hero-text">' +
					'<h2>' + esc( __( 'All Features', 'masjidos' ) ) + '</h2>' +
					'<p>' + esc( __( 'Everything your mosque website needs — interactive widgets, calendars, TV displays, and more.', 'masjidos' ) ) + '</p>' +
				'</div>' +
			'</div>' +
			'<div class="itmms-feat-grid">' + cards + '</div>' +
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
	function bindFeaturesEvents() {
		var app = document.getElementById( 'itmms-app' );
		if ( ! app ) return;

		var data = window.itmms.data || {};
		var features = getFeatures();

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
			var atts = Object.keys( params ).map( function( k ) {
				return k + '="' + params[k] + '"';
			} );
			var tag = '[' + f.shortcode.replace( /^\[|\]$/g, '' ).split( ' ' )[0];
			code.textContent = atts.length ? tag + ' ' + atts.join( ' ' ) + ']' : tag + ']';
		}

		// Param select changes
		app.addEventListener( 'change', function( e ) {
			var select = e.target.closest( 'select[data-feat-param]' );
			if ( ! select ) return;
			var featId = select.getAttribute( 'data-feat-id' );
			var param = select.getAttribute( 'data-feat-param' );
			featState[ featId ][ param ] = select.value;
			rebuildShortcode( featId );
		} );

		// Toggle buttons
		app.addEventListener( 'click', function( e ) {
			var toggle = e.target.closest( 'button.itmms-feat-toggle[data-feat-param]' );
			if ( ! toggle ) return;
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
			if ( ! copyBtn ) return;
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
			if ( ! previewBtn ) return;
			var featId = previewBtn.getAttribute( 'data-feat-preview' );
			openPreviewModal( featId, featState, features, data );
		} );

		// Viewport toggle inside modal
		app.addEventListener( 'click', function( e ) {
			var vpBtn = e.target.closest( '.itmms-preview-viewport-btn[data-viewport]' );
			if ( ! vpBtn ) return;
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
