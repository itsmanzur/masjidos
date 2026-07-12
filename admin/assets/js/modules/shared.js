/**
 * MasjidOS Admin - Shared Module / Namespace definitions.
 */
( function () {
	'use strict';

	window.itmms = window.itmms || {};

	// Localized data from wp_localize_script (exposed globally as itmmData)
	var data = window.itmmData || {};

	var __ = window.wp.i18n.__;
	var sprintf = window.wp.i18n.sprintf;

	// Expose properties
	window.itmms.data = data;
	window.itmms.state = {}; // Will be set up at application init

	window.itmms.readViewState = readViewState;
	window.itmms.syncViewUrl = syncViewUrl;
	window.itmms.esc = esc;
	window.itmms.icon = icon;
	window.itmms.api = api;
	window.itmms.exitToWordPress = exitToWordPress;
	window.itmms.primeWordPressExit = primeWordPressExit;
	window.itmms.initials = initials;

	function readViewState() {
		var values = {
			activeTab: 'dashboard',
			settingsTab: 'profile',
			docsTab: 'overview'
		};

		try {
			var url = new URL( window.location.href );
			var activeTab = url.searchParams.get( 'itmms_view' );
			var settingsTab = url.searchParams.get( 'itmms_settings' );
			var docsTab = url.searchParams.get( 'itmms_docs' );

			if ( [ 'dashboard', 'announcements', 'events', 'features', 'modules', 'settings', 'docs' ].indexOf( activeTab ) !== -1 ) {
				values.activeTab = activeTab;
			}
			if ( [ 'profile', 'calculation', 'adjustments', 'iqamah', 'jumuah', 'tv', 'public' ].indexOf( settingsTab ) !== -1 ) {
				values.settingsTab = settingsTab;
			}
			if ( [ 'overview', 'generators', 'prayer', 'jumuah', 'notices', 'pro', 'reference' ].indexOf( docsTab ) !== -1 ) {
				values.docsTab = docsTab;
			}
		} catch ( e ) {}

		return values;
	}

	function syncViewUrl( mode ) {
		var state = window.itmms.state;
		if ( ! window.history || ! window.history[ mode + 'State' ] ) {
			return;
		}

		try {
			var url = new URL( window.location.href );
			if ( 'dashboard' === state.activeTab ) {
				url.searchParams.delete( 'itmms_view' );
			} else {
				url.searchParams.set( 'itmms_view', state.activeTab );
			}

			if ( 'profile' === state.settingsTab ) {
				url.searchParams.delete( 'itmms_settings' );
			} else {
				url.searchParams.set( 'itmms_settings', state.settingsTab );
			}

			if ( 'overview' === state.docsTab ) {
				url.searchParams.delete( 'itmms_docs' );
			} else {
				url.searchParams.set( 'itmms_docs', state.docsTab );
			}

			window.history[ mode + 'State' ]( { itmmsView: state.activeTab }, '', url.href );
		} catch ( e ) {}
	}

	function esc( value ) {
		return String( value == null ? '' : value )
			.replace( /&/g, '&amp;' )
			.replace( /</g, '&lt;' )
			.replace( />/g, '&gt;' )
			.replace( /"/g, '&quot;' )
			.replace( /'/g, '&#39;' );
	}

	function icon( name ) {
		var icons = {
			clock: '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path></svg>',
			calendar: '<svg viewBox="0 0 24 24"><path d="M7 3v4M17 3v4M4 9h16M5 5h14v15H5z"></path></svg>',
			donation: '<svg viewBox="0 0 24 24"><path d="M12 3v18M17 7.5c-.8-1-2.3-1.7-4-1.7-2.2 0-4 1-4 2.7s1.8 2.4 4 2.8c2.4.4 4 1.1 4 2.9S15.2 18 13 18c-2 0-3.6-.7-4.6-1.9"></path></svg>',
			members: '<svg viewBox="0 0 24 24"><path d="M16 19c0-2.2-1.8-4-4-4s-4 1.8-4 4M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8M20 19c0-1.7-1-3.1-2.5-3.7M17 5.3a3 3 0 0 1 0 5.4"></path></svg>',
			book: '<svg viewBox="0 0 24 24"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H7a3 3 0 0 0-3 3zM4 5.5V22M8 7h8M8 11h8"></path></svg>',
			megaphone: '<svg viewBox="0 0 24 24"><path d="M4 13h3l10 4V5L7 9H4zM7 13l1 6h3"></path></svg>',
			ledger: '<svg viewBox="0 0 24 24"><path d="M6 3h12v18H6zM9 7h6M9 11h6M9 15h3"></path></svg>',
			external: '<svg viewBox="0 0 24 24"><path d="M14 4h6v6M20 4l-9 9"></path><path d="M20 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h5"></path></svg>',
			settings: '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1-2.1 2.1-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.5V20h-3v-.2a1.7 1.7 0 0 0-1-1.5 1.7 1.7 0 0 0-1.9.3l-.1.1L6.6 16.6l.1-.1A1.7 1.7 0 0 0 7 14.6a1.7 1.7 0 0 0-1.5-1H5v-3h.5A1.7 1.7 0 0 0 7 9.6a1.7 1.7 0 0 0-.3-1.9l-.1-.1 2.1-2.1.1.1a1.7 1.7 0 0 0 1.9.3 1.7 1.7 0 0 0 1-1.5V4h3v.4a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.9-.3l.1-.1 2.1 2.1-.1.1a1.7 1.7 0 0 0-.3 1.9 1.7 1.7 0 0 0 1.5 1h.1v3h-.1a1.7 1.7 0 0 0-1.5 1z"></path></svg>',
			star: '<svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>'
		};
		return icons[ name ] || icons.settings;
	}

	function api( path, options ) {
		options = options || {};
		options.headers = options.headers || {};
		options.headers['X-WP-Nonce'] = data.nonce;
		options.headers.Accept = 'application/json';
		if ( options.body ) {
			options.headers['Content-Type'] = 'application/json';
		}
		return fetch( data.restUrl + path, options ).then( function ( response ) {
			return response.json().then( function ( payload ) {
				if ( ! response.ok ) {
					throw new Error( payload.message || __( 'Request failed', 'masjidos' ) );
				}
				return payload;
			} );
		} );
	}

	function exitToWordPress( event ) {
		if ( event ) {
			event.preventDefault();
		}
		window.location.assign( data.adminUrl || '/wp-admin/' );
	}

	function primeWordPressExit() {
		var prefetch = document.createElement( 'link' );
		prefetch.rel = 'prefetch';
		prefetch.href = data.adminUrl || '/wp-admin/';
		document.head.appendChild( prefetch );
	}

	function initials( name ) {
		return String( name || __( 'Masjid Admin', 'masjidos' ) ).split( ' ' ).slice( 0, 2 ).map( function ( part ) {
			return part.charAt( 0 ).toUpperCase();
		} ).join( '' );
	}
} )();
