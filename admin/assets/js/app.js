/**
 * MasjidOS Admin - Main Application Entry.
 */
( function ( data ) {
	'use strict';

	var __ = window.wp.i18n.__;
	var _n = window.wp.i18n._n;
	var sprintf = window.wp.i18n.sprintf;

	var app = document.getElementById( 'itmms-app' );
	if ( ! app ) {
		return;
	}

	window.itmms = window.itmms || {};

	function urlHasExplicitView() {
		try {
			return new URL( window.location.href ).searchParams.has( 'itmms_view' );
		} catch ( e ) {
			return false;
		}
	}

	function shouldForceWelcome( view ) {
		if ( ! data.showWelcome ) {
			return false;
		}
		if ( urlHasExplicitView() ) {
			return view && view.activeTab === 'welcome';
		}
		return true;
	}

	// Setup shared state
	var initialView = window.itmms.readViewState();
	if ( shouldForceWelcome( initialView ) ) {
		initialView.activeTab = 'welcome';
	}
	var state = {
		settings: data.settings || {},
		modules: data.modules || [],
		stats: { announcements: 0, events: 0 },
		prayers: [],
		nextPrayer: null,
		prayerMeta: null,
		hijriDate: null,
		trust: null,
		upcomingDays: [],
		timetable: data.timetable || { count: 0, active: false },
		announcements: [],
		dashboardAnnouncements: [],
		editingAnnouncement: 0,
		events: [],
		dashboardEvents: [],
		editingEvent: 0,
		khutbahs: [],
		editingKhutbah: 0,
		khutbahCategories: {},
		khutbahSimilar: [],
		archiveFilter: { q: '', category: '' },
		minbarTab: initialView.minbarTab || 'dashboard',
		minbarDash: null,
		minbarProfiles: [],
		minbarSchedule: [],
		minbarPlans: [],
		minbarBookmarks: [],
		minbarRefResults: [],
		minbarRefQuery: '',
		minbarRefType: 'all',
		editingPlan: null,
		editingProfile: null,
		editingSchedule: null,
		sermonDraft: null,
		activeTab: initialView.activeTab === 'khutbah' ? 'minbar' : initialView.activeTab,
		settingsTab: initialView.settingsTab,
		docsTab: initialView.docsTab
	};
	window.itmms.state = state;

	// Import helpers
	var esc = window.itmms.esc;
	var icon = window.itmms.icon;
	var api = window.itmms.api;
	var exitToWordPress = window.itmms.exitToWordPress;
	var initials = window.itmms.initials;
	var readViewState = window.itmms.readViewState;
	var syncViewUrl = window.itmms.syncViewUrl;
	var primeWordPressExit = window.itmms.primeWordPressExit;
	var activeModuleCount = window.itmms.activeModuleCount;

	// Import module templates
	var dashboardHtml = window.itmms.dashboard.dashboardHtml;
	var modulesHtml = window.itmms.dashboard.modulesHtml;
	var welcomeHtml = window.itmms.welcome && window.itmms.welcome.welcomeHtml ? window.itmms.welcome.welcomeHtml : function () { return ''; };
	var settingsHtml = window.itmms.settings.settingsHtml;
	var announcementsHtml = window.itmms.announcements.announcementsHtml;
	var docsHtml = window.itmms.docs.docsHtml;
	var eventsHtml = window.itmms.events.eventsHtml;
	var minbarHtml = window.itmms.minbar.minbarHtml;
	var bindMinbarEvents = window.itmms.minbar.bindMinbarEvents;
	var featuresHtml = window.itmms.features.featuresHtml;
	var bindFeaturesEvents = window.itmms.features.bindFeaturesEvents;
	
	// Import sub-helpers
	var prayerNameLabel = window.itmms.dashboard.prayerNameLabel;
	var announcementTypeLabel = window.itmms.announcements.announcementTypeLabel;
	var datetimeLocalValue = window.itmms.announcements.datetimeLocalValue;

	function render() {
		var userName = data.user && data.user.name ? data.user.name : __( 'Masjid Admin', 'masjidos' );
		var masjidName = state.settings.masjid_name || 'MasjidOS';
		var location = [ state.settings.city, state.settings.country ].filter( Boolean ).join( ', ' );

		var archiveCount = ( state.minbarDash && state.minbarDash.stats && state.minbarDash.stats.archive ) || state.khutbahs.length || 0;
		var mods = state.settings.modules || {};

		app.innerHTML = '<div class="itmms-shell' + ( state.activeTab === 'welcome' ? ' itmms-shell--welcome' : '' ) + '">' +
			'<aside class="itmms-sidebar">' +
				'<div class="itmms-brand"><div class="itmms-brand-mark">' + icon( 'ledger' ) + '</div><div><strong>MasjidOS</strong><span>' + esc( masjidName ) + '</span></div></div>' +
				'<nav class="itmms-nav">' +
					navGroup( __( 'Overview', 'masjidos' ),
						navButton( 'welcome', 'crescent', __( 'Welcome', 'masjidos' ) ) +
						navButton( 'dashboard', 'grid', __( 'Dashboard', 'masjidos' ) ) +
						navButton( 'modules', 'settings', __( 'Modules', 'masjidos' ) ) +
						navButton( 'features', 'star', __( 'Features', 'masjidos' ) ) +
						navButton( 'settings', 'clock', __( 'Prayer Setup', 'masjidos' ), { settingsTab: 'timetable' } ) +
						( mods.announcements ? navButton( 'announcements', 'megaphone', __( 'Announcements', 'masjidos' ) ) : '' )
					) +
					navGroup( __( 'Minbar', 'masjidos' ),
						navButton( 'minbar', 'mic', __( 'Overview', 'masjidos' ), { minbarTab: 'dashboard' } ) +
						navButton( 'minbar', 'book', __( 'Archive', 'masjidos' ), { minbarTab: 'archive', badge: archiveCount } ) +
						navButton( 'minbar', 'calendar', __( 'Planner', 'masjidos' ), { minbarTab: 'planner' } ) +
						navButton( 'minbar', 'books', __( 'References', 'masjidos' ), { minbarTab: 'references' } ) +
						navButton( 'minbar', 'pen', __( 'Sermon Builder', 'masjidos' ), { minbarTab: 'builder' } ) +
						navButton( 'minbar', 'members', __( 'Schedule', 'masjidos' ), { minbarTab: 'schedule' } )
					) +
					navGroup( __( 'Management', 'masjidos' ),
						( mods.events ? navButton( 'events', 'calendar', __( 'Events', 'masjidos' ) ) : '' ) +
						( mods.duas_azkar ? navLink( data.adminUrl + 'edit.php?post_type=itmms_dua', 'book', __( 'Duas Library', 'masjidos' ) ) : '' ) +
						navButton( 'settings', 'settings', __( 'Settings', 'masjidos' ) ) +
						navButton( 'docs', 'book', __( 'Docs', 'masjidos' ) )
					) +
					proNavHtml() +
				'</nav>' +
				'<div class="itmms-user-card"><div>' + esc( initials( userName ) ) + '</div><span><strong>' + esc( userName ) + '</strong><small>' + esc( __( 'Administrator', 'masjidos' ) ) + '</small></span></div>' +
			'</aside>' +
			'<main class="itmms-main">' +
				'<header class="itmms-topbar">' +
					'<button class="itmms-sidebar-toggle" aria-label="' + esc( __( 'Toggle menu', 'masjidos' ) ) + '">' +
						'<svg viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>' +
					'</button>' +
					'<div><h1 id="itmms-title">' + esc( __( 'Dashboard', 'masjidos' ) ) + '</h1><p id="itmms-subtitle">' + esc( location || data.siteUrl ) + '</p></div>' +
					'<div class="itmms-topbar-actions">' +
						languageSwitcherHtml() +
						'<a class="itmms-btn itmms-wp-exit-btn" href="' + esc( data.adminUrl ) + '" title="' + esc( __( 'Leave MasjidOS and open the WordPress admin dashboard', 'masjidos' ) ) + '" aria-label="' + esc( __( 'Leave MasjidOS and open the WordPress admin dashboard', 'masjidos' ) ) + '" data-itmms-exit>' + icon( 'external' ) + '<span>' + esc( __( 'WP Dashboard', 'masjidos' ) ) + '</span></a>' +
						'<button class="itmms-btn itmms-btn-primary" data-open-settings>' + esc( __( 'Configure', 'masjidos' ) ) + '</button>' +
					'</div>' +
				'</header>' +
				'<section class="itmms-page">' +
					'<div class="itmms-tab-content' + ( state.activeTab === 'welcome' ? ' active' : '' ) + '" id="itmms-tab-welcome">' + welcomeHtml() + '</div>' +
					'<div class="itmms-tab-content' + ( state.activeTab === 'dashboard' ? ' active' : '' ) + '" id="itmms-tab-dashboard">' + dashboardHtml() + '</div>' +
					'<div class="itmms-tab-content" id="itmms-tab-announcements">' + announcementsHtml() + '</div>' +
					'<div class="itmms-tab-content" id="itmms-tab-events">' + eventsHtml() + '</div>' +
					'<div class="itmms-tab-content" id="itmms-tab-minbar">' + minbarHtml() + '</div>' +
					'<div class="itmms-tab-content" id="itmms-tab-features">' + featuresHtml() + '</div>' +
					'<div class="itmms-tab-content" id="itmms-tab-modules">' + modulesHtml() + '</div>' +
					'<div class="itmms-tab-content" id="itmms-tab-settings">' + settingsHtml() + '</div>' +
					'<div class="itmms-tab-content" id="itmms-tab-docs">' + docsHtml() + '</div>' +
				'</section>' +
			'</main>' +
		'</div>';

		bindEvents();
		bindFeaturesEvents();
		if ( window.itmms.welcome && window.itmms.welcome.bindWelcomeEvents ) {
			window.itmms.welcome.bindWelcomeEvents();
		}
		ensureWpMenuToggle();
		applyUiDirection();
		switchTab( state.activeTab );
		activateSettingsTab( state.settingsTab );
		activateDocsTab( state.docsTab );
		renderHijriDate();
		tickCountdown();
	}

	function languageSwitcherHtml() {
		var current = ( state.settings && state.settings.ui_language ) || data.uiLanguage || 'en';
		var options = [
			[ 'en', __( 'English', 'masjidos' ) ],
			[ 'bn', __( 'Bangla', 'masjidos' ) ],
			[ 'ar', __( 'Arabic', 'masjidos' ) ]
		];
		return '<label class="itmms-lang-switcher">' +
			'<span class="screen-reader-text">' + esc( __( 'UI Language', 'masjidos' ) ) + '</span>' +
			'<select id="itmms-ui-language" aria-label="' + esc( __( 'UI Language', 'masjidos' ) ) + '">' +
				options.map( function ( option ) {
					return '<option value="' + esc( option[0] ) + '"' + ( option[0] === current ? ' selected' : '' ) + '>' + esc( option[1] ) + '</option>';
				} ).join( '' ) +
			'</select>' +
		'</label>';
	}

	function uiLocaleFor( lang ) {
		return ( { en: 'en_US', bn: 'bn_BD', ar: 'ar' } )[ lang ] || 'en_US';
	}

	var uiTranslationCache = {};

	function resetUiTranslations( messages ) {
		if ( window.wp.i18n.resetLocaleData ) {
			window.wp.i18n.resetLocaleData( messages, 'masjidos' );
		} else if ( window.wp.i18n.setLocaleData ) {
			window.wp.i18n.setLocaleData( messages, 'masjidos' );
		}
	}

	function loadUiTranslations( lang ) {
		return new Promise( function ( resolve, reject ) {
			if ( lang === 'en' ) {
				resetUiTranslations( {
					'': {
						domain: 'messages',
						lang: 'en_US',
						'plural-forms': 'nplurals=2; plural=(n != 1);'
					}
				} );
				resolve();
				return;
			}

			if ( uiTranslationCache[ lang ] ) {
				resetUiTranslations( uiTranslationCache[ lang ] );
				resolve();
				return;
			}

			var locale = uiLocaleFor( lang );
			var base = String( data.languagesUrl || '' ).replace( /\/?$/, '/' );
			var url = base + 'masjidos-' + locale + '-itmms-admin.json?ver=' + encodeURIComponent( data.i18nRev || data.version || '' );

			fetch( url, { credentials: 'same-origin' } ).then( function ( response ) {
				if ( ! response.ok ) {
					throw new Error( 'translation-pack' );
				}
				return response.json();
			} ).then( function ( json ) {
				var messages = json && json.locale_data && json.locale_data.messages;
				if ( ! messages ) {
					throw new Error( 'translation-pack' );
				}
				uiTranslationCache[ lang ] = messages;
				resetUiTranslations( messages );
				resolve();
			} ).catch( reject );
		} );
	}

	function applyUiLanguage( lang, opts ) {
		opts = opts || {};
		var tab = state.activeTab;
		var settingsTab = state.settingsTab;
		var docsTab = state.docsTab;
		var minbarTab = state.minbarTab;

		return loadUiTranslations( lang ).then( function () {
			state.settings = state.settings || {};
			state.settings.ui_language = lang;
			data.uiLanguage = lang;
			data.uiLocale = uiLocaleFor( lang );
			data.uiIsRtl = lang === 'ar';
			data.locale = data.uiLocale;
			render();
			switchTab( tab );
			if ( tab === 'settings' && settingsTab ) {
				activateSettingsTab( settingsTab );
			}
			if ( tab === 'docs' && docsTab ) {
				activateDocsTab( docsTab );
			}
			if ( tab === 'minbar' && minbarTab ) {
				state.minbarTab = minbarTab;
			}
			if ( opts.showSaved ) {
				var saved = document.getElementById( 'itmms-save-status' );
				if ( saved ) {
					saved.textContent = __( 'Saved', 'masjidos' );
				}
			}
		} );
	}

	function applyUiDirection() {
		var lang = ( state.settings && state.settings.ui_language ) || data.uiLanguage || 'en';
		var isRtl = lang === 'ar';
		document.body.classList.remove( 'itmms-ui-en', 'itmms-ui-bn', 'itmms-ui-ar', 'itmms-ui-rtl' );
		document.body.classList.add( 'itmms-ui-' + lang );
		document.body.classList.toggle( 'itmms-ui-rtl', isRtl );
		document.body.classList.toggle( 'rtl', isRtl );
		var shell = app.querySelector( '.itmms-shell' );
		if ( shell ) {
			shell.setAttribute( 'dir', isRtl ? 'rtl' : 'ltr' );
			shell.setAttribute( 'lang', lang === 'bn' ? 'bn' : ( lang === 'ar' ? 'ar' : 'en' ) );
		}
	}

	function bindLanguageSwitcher() {
		var select = document.getElementById( 'itmms-ui-language' );
		if ( ! select || select.getAttribute( 'data-bound' ) === '1' ) {
			return;
		}
		select.setAttribute( 'data-bound', '1' );
		select.addEventListener( 'change', function () {
			var next = select.value;
			var previous = ( state.settings && state.settings.ui_language ) || data.uiLanguage || 'en';
			if ( [ 'en', 'bn', 'ar' ].indexOf( next ) === -1 || next === previous ) {
				return;
			}
			select.disabled = true;
			var payload = Object.assign( {}, state.settings, { ui_language: next } );
			api( 'settings', {
				method: 'POST',
				body: JSON.stringify( payload )
			} ).then( function ( response ) {
				state.settings = response.settings || payload;
				if ( response.modules ) {
					state.modules = response.modules;
				}
				return applyUiLanguage( next );
			} ).catch( function () {
				select.disabled = false;
				select.value = previous;
				window.alert( __( 'Could not save. Please try again.', 'masjidos' ) );
			} );
		} );
	}

	function ensureWpMenuToggle() {
		var existing = document.getElementById( 'itmms-wp-menu-toggle' );
		if ( existing ) {
			syncWpMenuToggle( existing );
			return;
		}

		var btn = document.createElement( 'button' );
		btn.type = 'button';
		btn.id = 'itmms-wp-menu-toggle';
		btn.className = 'itmms-wp-menu-toggle';
		btn.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h10M4 18h16"></path><path d="M18 9l3 3-3 3"></path></svg>';
		document.body.appendChild( btn );

		try {
			if ( window.localStorage && window.localStorage.getItem( 'itmms_wp_menu_open' ) === '1' ) {
				document.body.classList.add( 'itmms-wp-menu-open' );
			}
		} catch ( e ) {}

		syncWpMenuToggle( btn );

		btn.addEventListener( 'click', function () {
			var open = document.body.classList.toggle( 'itmms-wp-menu-open' );
			try {
				if ( window.localStorage ) {
					window.localStorage.setItem( 'itmms_wp_menu_open', open ? '1' : '0' );
				}
			} catch ( err ) {}
			syncWpMenuToggle( btn );
		} );
	}

	function syncWpMenuToggle( btn ) {
		if ( ! btn ) {
			return;
		}
		var open = document.body.classList.contains( 'itmms-wp-menu-open' );
		btn.classList.toggle( 'is-open', open );
		btn.setAttribute( 'aria-pressed', open ? 'true' : 'false' );
		btn.title = open
			? __( 'Hide WordPress menu — full-width MasjidOS', 'masjidos' )
			: __( 'Show WordPress admin menu beside MasjidOS', 'masjidos' );
		btn.setAttribute( 'aria-label', btn.title );
	}

	function navGroup( label, itemsHtml ) {
		return '<div class="itmms-nav-group"><div class="itmms-nav-label">' + esc( label ) + '</div>' + itemsHtml + '</div>';
	}

	function navButton( tab, iconName, label, opts ) {
		opts = opts || {};
		var attrs = ' data-tab="' + esc( tab ) + '"';
		if ( opts.minbarTab ) {
			attrs += ' data-minbar-tab="' + esc( opts.minbarTab ) + '"';
		}
		if ( opts.settingsTab ) {
			attrs += ' data-settings-tab="' + esc( opts.settingsTab ) + '"';
		}
		var badge = '';
		if ( opts.badge != null && Number( opts.badge ) > 0 ) {
			badge = '<span class="itmms-nav-badge">' + esc( opts.badge ) + '</span>';
		}
		return '<button type="button" class="itmms-nav-item"' + attrs + '>' + icon( iconName ) + '<span>' + esc( label ) + '</span>' + badge + '</button>';
	}

	function navLink( href, iconName, label ) {
		return '<a class="itmms-nav-item" href="' + esc( href ) + '">' + icon( iconName ) + '<span>' + esc( label ) + '</span></a>';
	}

	function proNavHtml() {
		var extras = Array.isArray( data.adminNav ) ? data.adminNav : [];
		var pro = data.pro || {};
		var html = '';

		if ( extras.length ) {
			var items = extras.map( function ( item ) {
				if ( ! item || ! item.label ) {
					return '';
				}
				if ( item.type === 'tab' && item.tab ) {
					return navButton( item.tab, item.icon || 'star', item.label, item.opts || {} );
				}
				if ( item.url ) {
					return navLink( item.url, item.icon || 'star', item.label );
				}
				return '';
			} ).join( '' );
			if ( items ) {
				html += navGroup( extras[0].group_label || __( 'Pro', 'masjidos' ), items );
			}
		}

		if ( ! pro.active && ( data.proUrl || pro.url ) ) {
			html += navGroup( __( 'Pro', 'masjidos' ),
				navLink( data.proUrl || pro.url, 'star', pro.cta || __( 'Learn about Pro', 'masjidos' ) )
			);
		}

		return html;
	}

	function isPrayerSettingsTab( key ) {
		return [ 'calculation', 'timetable', 'adjustments', 'iqamah' ].indexOf( key ) !== -1;
	}

	function bindEvents() {
		app.querySelectorAll( '[data-itmms-exit]' ).forEach( function ( link ) {
			link.addEventListener( 'click', exitToWordPress );
		} );

		bindLanguageSwitcher();

		var adminbarExit = document.querySelector( '#wp-admin-bar-itmms-exit a' );
		if ( adminbarExit ) {
			adminbarExit.addEventListener( 'click', exitToWordPress );
		}

		app.querySelectorAll( '[data-tab]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var tab = btn.getAttribute( 'data-tab' );
				var minbarTab = btn.getAttribute( 'data-minbar-tab' );
				var settingsTab = btn.getAttribute( 'data-settings-tab' );
				var docsTab = btn.getAttribute( 'data-docs-tab' );

				if ( minbarTab ) {
					state.minbarTab = minbarTab;
				}
				if ( settingsTab ) {
					state.settingsTab = settingsTab;
				} else if ( tab === 'settings' && btn.classList.contains( 'itmms-nav-item' ) ) {
					if ( isPrayerSettingsTab( state.settingsTab ) ) {
						state.settingsTab = 'profile';
					}
				}
				if ( docsTab ) {
					state.docsTab = docsTab;
				}

				if ( tab === 'minbar' ) {
					state.activeTab = 'minbar';
					render();
					syncViewUrl( 'push' );
					return;
				}

				switchTab( tab, 'push' );
				if ( tab === 'settings' ) {
					activateSettingsTab( state.settingsTab, 'replace' );
				}
				if ( tab === 'docs' && docsTab ) {
					activateDocsTab( docsTab );
				}
			} );
		} );

		bindAnnouncementEvents();
		bindEventEvents();
		bindMinbarEvents( api, render, switchTab );
		if ( window.itmms.dashboard && window.itmms.dashboard.bindDashboardEvents ) {
			window.itmms.dashboard.bindDashboardEvents();
		}

		app.querySelectorAll( '[data-open-settings]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				if ( ! isPrayerSettingsTab( state.settingsTab ) ) {
					state.settingsTab = 'calculation';
				}
				switchTab( 'settings', 'push' );
				activateSettingsTab( state.settingsTab, 'replace' );
			} );
		} );

		bindMediaPickers();
		bindSettingsTabs();
		if ( window.itmms.settings && window.itmms.settings.bindTimetableEvents ) {
			window.itmms.settings.bindTimetableEvents( app.querySelector( '[data-settings-panel="timetable"]' ) );
		}
		if ( window.itmms.settings && window.itmms.settings.bindIqamahRuleEvents ) {
			window.itmms.settings.bindIqamahRuleEvents( app.querySelector( '[data-settings-panel="iqamah"]' ) );
		}
		bindDocsTabs();

		app.querySelectorAll( '[data-copy-shortcode]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () { copyShortcode( btn ); } );
		} );

		bindShortcodeBuilder();

		app.querySelectorAll( '.itmms-toggle' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var key = btn.getAttribute( 'data-module' );
				state.settings.modules[ key ] = ! state.settings.modules[ key ];
				saveSettings( state.settings, btn );
			} );
		} );

		var form = document.getElementById( 'itmms-settings-form' );
		if ( form ) {
			form.addEventListener( 'submit', function ( event ) {
				event.preventDefault();
				var payload = Object.assign( {}, state.settings );
				new FormData( form ).forEach( function ( value, key ) {
					payload[ key ] = value;
				} );
				payload.public_transparency = form.elements.public_transparency.checked;
				payload.show_ishraq = !!( form.elements.show_ishraq && form.elements.show_ishraq.checked );
				payload.show_zawal = !!( form.elements.show_zawal && form.elements.show_zawal.checked );
				payload.tv_slides = !!( form.elements.tv_slides && form.elements.tv_slides.checked );
				payload.tv_quiet_enabled = !!( form.elements.tv_quiet_enabled && form.elements.tv_quiet_enabled.checked );
				payload.tv_dim_enabled = !!( form.elements.tv_dim_enabled && form.elements.tv_dim_enabled.checked );
				payload.modules = state.settings.modules;
				payload.prayer_offsets = {};
				form.querySelectorAll( '[data-offset]' ).forEach( function ( input ) {
					payload.prayer_offsets[ input.getAttribute( 'data-offset' ) ] = input.value;
				} );
				payload.iqamah_times = {};
				form.querySelectorAll( '[data-iqamah]' ).forEach( function ( input ) {
					payload.iqamah_times[ input.getAttribute( 'data-iqamah' ) ] = input.value;
				} );
				payload.iqamah_rules = {};
				form.querySelectorAll( '[data-iqamah-rule-mode]' ).forEach( function ( select ) {
					var key = select.getAttribute( 'data-iqamah-rule-mode' );
					var minutesInput = form.querySelector( '[data-iqamah-rule-minutes="' + key + '"]' );
					var roundSelect = form.querySelector( '[data-iqamah-rule-round="' + key + '"]' );
					payload.iqamah_rules[ key ] = {
						mode: select.value,
						minutes: minutesInput ? Number( minutesInput.value || 0 ) : 0,
						round: roundSelect ? Number( roundSelect.value || 0 ) : 0
					};
				} );
				payload.jumuah = {
					enabled: !! ( form.querySelector( '[data-jumuah-enabled]' ) && form.querySelector( '[data-jumuah-enabled]' ).checked ),
					khutbah_time: '',
					jamaat_time: '',
					topic: form.elements.jumuah_topic ? form.elements.jumuah_topic.value : '',
					language: form.elements.jumuah_language ? form.elements.jumuah_language.value : '',
					khatib: {
						name: form.elements.jumuah_khatib_name ? form.elements.jumuah_khatib_name.value : '',
						image_url: form.elements.jumuah_khatib_image_url ? form.elements.jumuah_khatib_image_url.value : '',
						bio: form.elements.jumuah_khatib_bio ? form.elements.jumuah_khatib_bio.value : ''
					},
					sessions: [],
					notice: form.elements.jumuah_notice ? form.elements.jumuah_notice.value : ''
				};
				form.querySelectorAll( '[data-jumuah-session]' ).forEach( function ( input ) {
					var index = Number( input.getAttribute( 'data-jumuah-session' ) || 0 );
					var fieldName = input.getAttribute( 'data-jumuah-session-field' );
					payload.jumuah.sessions[ index ] = payload.jumuah.sessions[ index ] || {};
					payload.jumuah.sessions[ index ][ fieldName ] = input.value;
				} );
				payload.jumuah.sessions = payload.jumuah.sessions.filter( Boolean );
				payload.jumuah.khutbah_time = payload.jumuah.sessions[0] ? payload.jumuah.sessions[0].khutbah_time : '';
				payload.jumuah.jamaat_time = payload.jumuah.sessions[0] ? payload.jumuah.sessions[0].jamaat_time : '';
				saveSettings( payload );
			} );
		}

		// Mobile Sidebar Toggle
		var sidebarToggle = app.querySelector( '.itmms-sidebar-toggle' );
		if ( sidebarToggle ) {
			sidebarToggle.addEventListener( 'click', function () {
				var shell = app.querySelector( '.itmms-shell' );
				if ( shell ) {
					shell.classList.toggle( 'sidebar-open' );
				}
			} );
		}
	}

	function bindAnnouncementEvents() {
		var form = document.getElementById( 'itmms-announcement-form' );
		if ( form ) {
			form.addEventListener( 'submit', function ( event ) {
				event.preventDefault();
				var fields = new FormData( form );
				var payload = {};
				fields.forEach( function ( value, key ) { payload[ key ] = value; } );
				payload.is_active = !! form.elements.is_active.checked;
				payload.priority = Number( payload.priority || 0 );
				saveAnnouncement( payload, form.querySelector( '[type="submit"]' ) );
			} );
		}

		app.querySelectorAll( '[data-edit-announcement]' ).forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				state.editingAnnouncement = Number( button.getAttribute( 'data-edit-announcement' ) );
				render();
				switchTab( 'announcements' );
			} );
		} );

		app.querySelectorAll( '[data-new-announcement], [data-cancel-announcement]' ).forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				state.editingAnnouncement = 0;
				render();
				switchTab( 'announcements' );
			} );
		} );

		app.querySelectorAll( '[data-delete-announcement]' ).forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				var id = Number( button.getAttribute( 'data-delete-announcement' ) );
				if ( ! window.confirm( __( 'Delete this notice permanently?', 'masjidos' ) ) ) {
					return;
				}
				button.disabled = true;
				api( 'announcements/' + id, { method: 'DELETE' } ).then( function () {
					state.editingAnnouncement = 0;
					return loadAnnouncements();
				} ).then( function () {
					render();
					switchTab( 'announcements' );
				} ).catch( function ( error ) {
					button.disabled = false;
					window.alert( error.message );
				} );
			} );
		} );
	}

	function loadKhutbahs() {
		return window.itmms.minbar.loadKhutbahs( api );
	}

	function loadMinbarData() {
		return Promise.all( [
			loadKhutbahs().catch( function () {} ),
			window.itmms.minbar.loadMinbarDash( api ),
			window.itmms.minbar.loadProfiles( api ).catch( function () {} ),
			window.itmms.minbar.loadSchedule( api ).catch( function () {} ),
			window.itmms.minbar.loadPlans( api ).catch( function () {} ),
			window.itmms.minbar.loadBookmarks( api ).catch( function () {} )
		] );
	}

	function bindEventEvents() {
		var form = document.getElementById( 'itmms-event-form' );
		if ( form ) {
			form.addEventListener( 'submit', function ( event ) {
				event.preventDefault();
				var fields = new FormData( form );
				var payload = {};
				fields.forEach( function ( value, key ) { payload[ key ] = value; } );
				saveEvent( payload, form.querySelector( '[type="submit"]' ) );
			} );
		}

		app.querySelectorAll( '[data-edit-event]' ).forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				state.editingEvent = Number( button.getAttribute( 'data-edit-event' ) );
				render();
				switchTab( 'events' );
			} );
		} );

		app.querySelectorAll( '[data-new-event], [data-cancel-event]' ).forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				state.editingEvent = 0;
				render();
				switchTab( 'events' );
			} );
		} );

		app.querySelectorAll( '[data-delete-event]' ).forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				var id = Number( button.getAttribute( 'data-delete-event' ) );
				if ( ! window.confirm( __( 'Delete this event permanently?', 'masjidos' ) ) ) {
					return;
				}
				button.disabled = true;
				api( 'events/' + id, { method: 'DELETE' } ).then( function () {
					state.editingEvent = 0;
					return loadEvents();
				} ).then( function () {
					render();
					switchTab( 'events' );
				} ).catch( function ( error ) {
					button.disabled = false;
					window.alert( error.message );
				} );
			} );
		} );
	}

	function saveEvent( payload, trigger ) {
		var status = document.getElementById( 'itmms-event-status' );
		var editing = Number( state.editingEvent );
		if ( trigger ) {
			trigger.disabled = true;
		}
		if ( status ) {
			status.textContent = __( 'Saving...', 'masjidos' );
		}

		api( editing ? 'events/' + editing : 'events', {
			method: editing ? 'PUT' : 'POST',
			body: JSON.stringify( payload )
		} ).then( function () {
			state.editingEvent = 0;
			return loadEvents();
		} ).then( function () {
			render();
			switchTab( 'events' );
			var saved = document.getElementById( 'itmms-event-status' );
			if ( saved ) {
				saved.textContent = editing ? __( 'Event updated.', 'masjidos' ) : __( 'Event created.', 'masjidos' );
			}
		} ).catch( function ( error ) {
			if ( status ) {
				status.textContent = error.message;
			}
			if ( trigger ) {
				trigger.disabled = false;
			}
		} );
	}

	function loadEvents() {
		return api( 'events' ).then( function ( response ) {
			state.events = response.events || [];
			var active = state.events.filter( function ( event ) { return 'upcoming' === event.status || 'ongoing' === event.status; } );
			state.dashboardEvents = active.slice( 0, 5 );
			state.stats.events = active.length;
		} );
	}

	function saveAnnouncement( payload, trigger ) {
		var status = document.getElementById( 'itmms-announcement-status' );
		var editing = Number( state.editingAnnouncement );
		if ( trigger ) {
			trigger.disabled = true;
		}
		if ( status ) {
			status.textContent = __( 'Saving...', 'masjidos' );
		}

		api( editing ? 'announcements/' + editing : 'announcements', {
			method: editing ? 'PUT' : 'POST',
			body: JSON.stringify( payload )
		} ).then( function () {
			state.editingAnnouncement = 0;
			return loadAnnouncements();
		} ).then( function () {
			render();
			switchTab( 'announcements' );
			var saved = document.getElementById( 'itmms-announcement-status' );
			if ( saved ) {
				saved.textContent = editing ? __( 'Notice updated.', 'masjidos' ) : __( 'Notice published.', 'masjidos' );
			}
		} ).catch( function ( error ) {
			if ( status ) {
				status.textContent = error.message;
			}
			if ( trigger ) {
				trigger.disabled = false;
			}
		} );
	}

	function loadAnnouncements() {
		return api( 'announcements' ).then( function ( response ) {
			state.announcements = response.announcements || [];
			var active = state.announcements.filter( function ( notice ) { return 'active' === notice.status; } );
			state.dashboardAnnouncements = active.slice( 0, 5 );
			state.stats.announcements = active.length;
		} );
	}

	function bindSettingsTabs() {
		var tabs = app.querySelectorAll( '.itmms-settings-tab[data-settings-tab]' );
		if ( ! tabs.length ) {
			return;
		}

		tabs.forEach( function ( tab ) {
			tab.addEventListener( 'click', function () {
				activateSettingsTab( tab.getAttribute( 'data-settings-tab' ), 'push' );
			} );
		} );

		var detectBtn = document.getElementById( 'itmms-detect-location' );
		if ( detectBtn ) {
			detectBtn.addEventListener( 'click', function () {
				if ( ! navigator.geolocation ) {
					window.alert( __( 'Geolocation is not supported by your browser.', 'masjidos' ) );
					return;
				}
				detectBtn.disabled = true;
				var originalText = detectBtn.innerHTML;
				detectBtn.textContent = __( 'Detecting location...', 'masjidos' );

				navigator.geolocation.getCurrentPosition(
					function ( position ) {
						var lat = position.coords.latitude.toFixed( 4 );
						var lng = position.coords.longitude.toFixed( 4 );
						var latInput = document.querySelector( 'input[name="latitude"]' );
						var lngInput = document.querySelector( 'input[name="longitude"]' );
						if ( latInput ) {
							latInput.value = lat;
							state.settings.latitude = lat;
						}
						if ( lngInput ) {
							lngInput.value = lng;
							state.settings.longitude = lng;
						}

						// Detect timezone based on browser if supported
						try {
							var detectedTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
							var tzInput = document.querySelector( 'input[name="timezone"]' );
							if ( detectedTimezone && tzInput ) {
								tzInput.value = detectedTimezone;
								state.settings.timezone = detectedTimezone;

								// Clean up or update warnings
								var warning = document.getElementById( 'itmms-timezone-mismatch-warning' );
								if ( warning ) {
									if ( window.itmmData && window.itmmData.siteTimezone && detectedTimezone === window.itmmData.siteTimezone ) {
										warning.style.display = 'none';
									} else {
										warning.style.display = 'block';
										var code = warning.querySelectorAll( 'code' );
										if ( code.length >= 1 ) {
											code[0].textContent = detectedTimezone;
										}
										if ( code.length >= 2 && window.itmmData.siteTimezone ) {
											code[1].textContent = window.itmmData.siteTimezone;
										}
									}
								}
							}
						} catch ( e ) {}

						detectBtn.disabled = false;
						detectBtn.innerHTML = originalText;
						window.alert( __( 'Location detected and coordinates updated.', 'masjidos' ) );
					},
					function ( error ) {
						detectBtn.disabled = false;
						detectBtn.innerHTML = originalText;
						window.alert( __( 'Unable to detect location. Please input coordinates manually.', 'masjidos' ) );
					},
					{ enableHighAccuracy: true, timeout: 8000 }
				);
			} );
		}

		app.querySelectorAll( '[data-copy-tv-url]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var url = btn.getAttribute( 'data-url' );
				if ( navigator.clipboard && navigator.clipboard.writeText ) {
					navigator.clipboard.writeText( url ).then( function () {
						var originalText = btn.textContent;
						btn.textContent = __( 'Copied!', 'masjidos' );
						setTimeout( function () { btn.textContent = originalText; }, 2000 );
					} );
				} else {
					window.alert( __( 'Copy failed. Please copy the URL manually.', 'masjidos' ) );
				}
			} );
		} );
	}

	function activateSettingsTab( key, historyMode ) {
		if ( [ 'profile', 'calculation', 'timetable', 'adjustments', 'iqamah', 'jumuah', 'tv', 'public' ].indexOf( key ) === -1 ) {
			key = 'profile';
		}
		state.settingsTab = key;
		var prayerMode = isPrayerSettingsTab( key );
		var form = app.querySelector( '#itmms-settings-form' );
		if ( form ) {
			form.classList.toggle( 'itmms-settings-form--prayer', prayerMode );
			form.classList.toggle( 'itmms-settings-form--general', ! prayerMode );
		}
		app.querySelectorAll( '.itmms-settings-tab[data-settings-tab]' ).forEach( function ( tab ) {
			var tabKey = tab.getAttribute( 'data-settings-tab' );
			var group = tab.getAttribute( 'data-settings-group' ) || 'general';
			var show = prayerMode ? group === 'prayer' : group === 'general';
			var active = tabKey === key;
			tab.hidden = ! show;
			tab.classList.toggle( 'active', active );
			tab.setAttribute( 'aria-selected', active ? 'true' : 'false' );
		} );
		app.querySelectorAll( '[data-settings-panel]' ).forEach( function ( panel ) {
			panel.classList.toggle( 'active', panel.getAttribute( 'data-settings-panel' ) === key );
		} );
		if ( 'timetable' === key && window.itmms.settings && window.itmms.settings.bindTimetableEvents ) {
			window.itmms.settings.bindTimetableEvents( app.querySelector( '[data-settings-panel="timetable"]' ) );
		}
		if ( 'iqamah' === key && window.itmms.settings && window.itmms.settings.bindIqamahRuleEvents ) {
			window.itmms.settings.bindIqamahRuleEvents( app.querySelector( '[data-settings-panel="iqamah"]' ) );
		}
		if ( state.activeTab === 'settings' ) {
			var meta = pageMeta( 'settings' );
			var title = document.getElementById( 'itmms-title' );
			var subtitle = document.getElementById( 'itmms-subtitle' );
			if ( title ) {
				title.textContent = meta.title;
			}
			if ( subtitle ) {
				subtitle.textContent = meta.desc;
			}
			app.querySelectorAll( '.itmms-nav-item[data-tab="settings"]' ).forEach( function ( item ) {
				var itemSettings = item.getAttribute( 'data-settings-tab' );
				var isActive = itemSettings
					? isPrayerSettingsTab( state.settingsTab ) && ( itemSettings === state.settingsTab || itemSettings === 'timetable' )
					: ! isPrayerSettingsTab( state.settingsTab );
				item.classList.toggle( 'active', isActive );
			} );
		}
		if ( historyMode ) {
			syncViewUrl( historyMode );
		}
	}

	function bindMediaPickers() {
		app.querySelectorAll( '.itmms-media-field' ).forEach( function ( field ) {
			var input = field.querySelector( '[data-media-url]' );
			var preview = field.querySelector( '[data-media-preview]' );
			var select = field.querySelector( '[data-select-media]' );
			var remove = field.querySelector( '[data-remove-media]' );

			function updatePreview() {
				var value = input ? input.value.trim() : '';
				if ( ! preview ) {
					return;
				}
				preview.innerHTML = value ? '<img src="' + esc( value ) + '" alt="">' : '<span>' + esc( __( 'No photo', 'masjidos' ) ) + '</span>';
			}

			if ( input ) {
				input.addEventListener( 'input', updatePreview );
			}

			if ( remove ) {
				remove.addEventListener( 'click', function () {
					if ( input ) {
						input.value = '';
						updatePreview();
					}
				} );
			}

			if ( select ) {
				select.addEventListener( 'click', function () {
					openMediaFrame( function ( url ) {
						if ( input ) {
							input.value = url;
							updatePreview();
						}
					} );
				} );
			}
		} );
	}

	function bindDocsTabs() {
		var tabs = app.querySelectorAll( '[data-doc-tab]' );
		if ( ! tabs.length ) {
			return;
		}

		tabs.forEach( function ( tab ) {
			tab.addEventListener( 'click', function () {
				activateDocsTab( tab.getAttribute( 'data-doc-tab' ), 'push' );
			} );
		} );

		bindDocsAccordions();
		bindDocsChecklist();
	}

	function bindDocsChecklist() {
		app.querySelectorAll( '[data-docs-check]' ).forEach( function ( input ) {
			if ( input.disabled ) {
				return;
			}
			input.addEventListener( 'change', function () {
				var id = input.getAttribute( 'data-docs-check' );
				var row = input.closest( '.itmms-docs-check' );
				try {
					window.localStorage.setItem( 'itmms_docs_check_' + id, input.checked ? '1' : '0' );
				} catch ( e ) {}
				if ( row ) {
					row.classList.toggle( 'is-done', input.checked );
				}
			} );
		} );
	}

	function bindDocsAccordions() {
		app.querySelectorAll( '[data-docs-accordion-open]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var id = btn.getAttribute( 'data-docs-accordion-open' );
				var panel = app.querySelector( '[data-docs-accordion="' + id + '"]' );
				if ( ! panel ) {
					return;
				}
				app.querySelectorAll( '[data-docs-accordion]' ).forEach( function ( item ) {
					if ( item !== panel ) {
						item.removeAttribute( 'open' );
					}
				} );
				panel.setAttribute( 'open', '' );
				panel.scrollIntoView( { behavior: 'smooth', block: 'start' } );
				app.querySelectorAll( '[data-docs-accordion-open]' ).forEach( function ( chip ) {
					chip.classList.toggle( 'is-active', chip.getAttribute( 'data-docs-accordion-open' ) === id );
				} );
			} );
		} );
	}

	function activateDocsTab( key, historyMode ) {
		var allowed = [ 'overview', 'generators', 'prayer', 'jumuah', 'minbar', 'calendar', 'duas', 'notices', 'events', 'articles', 'education', 'pro', 'reference' ];
		if ( allowed.indexOf( key ) === -1 ) {
			key = 'overview';
		}
		state.docsTab = key;
		app.querySelectorAll( '[data-doc-tab]' ).forEach( function ( tab ) {
			var active = tab.getAttribute( 'data-doc-tab' ) === key;
			tab.classList.toggle( 'active', active );
			tab.setAttribute( 'aria-selected', active ? 'true' : 'false' );
		} );
		app.querySelectorAll( '[data-doc-panel]' ).forEach( function ( panel ) {
			panel.classList.toggle( 'active', panel.getAttribute( 'data-doc-panel' ) === key );
		} );
		if ( historyMode ) {
			syncViewUrl( historyMode );
		}
	}

	function openMediaFrame( onSelect ) {
		if ( ! window.wp || ! window.wp.media ) {
			return;
		}

		var frame = window.wp.media( {
			title: __( 'Select Khatib Photo', 'masjidos' ),
			button: { text: __( 'Use this photo', 'masjidos' ) },
			library: { type: 'image' },
			multiple: false
		} );

		frame.on( 'select', function () {
			var attachment = frame.state().get( 'selection' ).first();
			var data = attachment ? attachment.toJSON() : null;
			if ( data && data.url ) {
				onSelect( data.url );
			}
		} );

		frame.open();
	}

	function copyShortcode( btn ) {
		var shortcode = btn.getAttribute( 'data-copy-shortcode' ) || '';
		copyText( shortcode, btn );
	}

	function bindShortcodeBuilder() {
		var output = app.querySelector( '[data-generated-shortcode]' );
		var jumuahOutput = app.querySelector( '[data-generated-jumuah-shortcode]' );
		var monthlyOutput = app.querySelector( '[data-generated-monthly-shortcode]' );
		var calendarOutput = app.querySelector( '[data-generated-calendar-shortcode]' );
		var duasOutput = app.querySelector( '[data-generated-duas-shortcode]' );
		var announcementOutput = app.querySelector( '[data-generated-announcement-shortcode]' );
		var eventsOutput = app.querySelector( '[data-generated-events-shortcode]' );

		function update() {
			if ( output ) {
				output.textContent = generatedShortcode();
			}
			if ( jumuahOutput ) {
				jumuahOutput.textContent = generatedJumuahShortcode();
			}
			if ( monthlyOutput ) {
				monthlyOutput.textContent = generatedMonthlyShortcode();
			}
			if ( calendarOutput ) {
				calendarOutput.textContent = generatedCalendarShortcode();
			}
			if ( duasOutput ) {
				duasOutput.textContent = generatedDuasShortcode();
			}
			if ( announcementOutput ) {
				announcementOutput.textContent = generatedAnnouncementShortcode();
			}
			if ( eventsOutput ) {
				eventsOutput.textContent = generatedEventsShortcode();
			}
		}

		app.querySelectorAll( '[data-builder-design], [data-builder-language], [data-builder-title], [data-builder-qibla], [data-builder-meta], [data-builder-iqamah], [data-builder-hijri], [data-jumuah-builder-design], [data-jumuah-builder-language], [data-jumuah-builder-title], [data-jumuah-builder-meta], [data-monthly-builder-design], [data-monthly-builder-month], [data-monthly-builder-year], [data-monthly-builder-language], [data-monthly-builder-title], [data-monthly-builder-iqamah], [data-monthly-builder-navigation], [data-calendar-builder-month], [data-calendar-builder-year], [data-calendar-builder-language], [data-calendar-builder-title], [data-calendar-builder-navigation], [data-duas-builder-design], [data-duas-builder-language], [data-duas-builder-category], [data-duas-builder-limit], [data-duas-builder-title], [data-duas-builder-source], [data-duas-builder-counter], [data-duas-builder-share], [data-duas-builder-audio], [data-announcement-builder-design], [data-announcement-builder-language], [data-announcement-builder-type], [data-announcement-builder-limit], [data-announcement-builder-title], [data-announcement-builder-date], [data-events-builder-design], [data-events-builder-language], [data-events-builder-limit], [data-events-builder-title]' ).forEach( function ( input ) {
			input.addEventListener( 'input', update );
			input.addEventListener( 'change', update );
		} );

		var copy = app.querySelector( '[data-copy-generated-shortcode]' );
		if ( copy && output ) {
			copy.addEventListener( 'click', function () {
				copyText( output.textContent, copy );
			} );
		}

		var jumuahCopy = app.querySelector( '[data-copy-generated-jumuah-shortcode]' );
		if ( jumuahCopy && jumuahOutput ) {
			jumuahCopy.addEventListener( 'click', function () {
				copyText( jumuahOutput.textContent, jumuahCopy );
			} );
		}

		var monthlyCopy = app.querySelector( '[data-copy-generated-monthly-shortcode]' );
		if ( monthlyCopy && monthlyOutput ) {
			monthlyCopy.addEventListener( 'click', function () {
				copyText( monthlyOutput.textContent, monthlyCopy );
			} );
		}

		var calendarCopy = app.querySelector( '[data-copy-generated-calendar-shortcode]' );
		if ( calendarCopy && calendarOutput ) {
			calendarCopy.addEventListener( 'click', function () {
				copyText( calendarOutput.textContent, calendarCopy );
			} );
		}

		var duasCopy = app.querySelector( '[data-copy-generated-duas-shortcode]' );
		if ( duasCopy && duasOutput ) {
			duasCopy.addEventListener( 'click', function () {
				copyText( duasOutput.textContent, duasCopy );
			} );
		}

		var announcementCopy = app.querySelector( '[data-copy-generated-announcement-shortcode]' );
		if ( announcementCopy && announcementOutput ) {
			announcementCopy.addEventListener( 'click', function () {
				copyText( announcementOutput.textContent, announcementCopy );
			} );
		}

		var eventsCopy = app.querySelector( '[data-copy-generated-events-shortcode]' );
		if ( eventsCopy && eventsOutput ) {
			eventsCopy.addEventListener( 'click', function () {
				copyText( eventsOutput.textContent, eventsCopy );
			} );
		}

		update();
	}

	function generatedShortcode() {
		var design = builderValue( '[data-builder-design]', 'classic' );
		var language = builderValue( '[data-builder-language]', 'en' );
		var title = builderValue( '[data-builder-title]', '' );
		var attrs = [];

		if ( 'classic' !== design ) {
			attrs.push( 'design="' + design + '"' );
		}
		if ( 'en' !== language ) {
			attrs.push( 'language="' + language + '"' );
		}
		if ( title ) {
			attrs.push( 'title="' + title.replace( /"/g, '&quot;' ) + '"' );
		}
		if ( ! builderChecked( '[data-builder-qibla]' ) ) {
			attrs.push( 'qibla="no"' );
		}
		if ( ! builderChecked( '[data-builder-meta]' ) ) {
			attrs.push( 'meta="no"' );
		}
		if ( ! builderChecked( '[data-builder-iqamah]' ) ) {
			attrs.push( 'iqamah="no"' );
		}
		if ( ! builderChecked( '[data-builder-hijri]' ) ) {
			attrs.push( 'hijri="no"' );
		}

		return '[masjidos_prayer_times' + ( attrs.length ? ' ' + attrs.join( ' ' ) : '' ) + ']';
	}

	function generatedJumuahShortcode() {
		var design = builderValue( '[data-jumuah-builder-design]', 'classic' );
		var language = builderValue( '[data-jumuah-builder-language]', 'en' );
		var title = builderValue( '[data-jumuah-builder-title]', '' );
		var attrs = [];

		if ( 'classic' !== design ) {
			attrs.push( 'design="' + design + '"' );
		}
		if ( 'en' !== language ) {
			attrs.push( 'language="' + language + '"' );
		}
		if ( title ) {
			attrs.push( 'title="' + title.replace( /"/g, '&quot;' ) + '"' );
		}
		if ( ! builderChecked( '[data-jumuah-builder-meta]' ) ) {
			attrs.push( 'meta="no"' );
		}

		return '[masjidos_jumuah' + ( attrs.length ? ' ' + attrs.join( ' ' ) : '' ) + ']';
	}

	function generatedMonthlyShortcode() {
		var design = builderValue( '[data-monthly-builder-design]', 'table' );
		var month = builderValue( '[data-monthly-builder-month]', '' );
		var year = builderValue( '[data-monthly-builder-year]', '' );
		var language = builderValue( '[data-monthly-builder-language]', 'en' );
		var title = builderValue( '[data-monthly-builder-title]', '' );
		var attrs = [];

		if ( 'table' !== design ) {
			attrs.push( 'design="' + design + '"' );
		}
		if ( month ) {
			attrs.push( 'month="' + month + '"' );
		}
		if ( year ) {
			attrs.push( 'year="' + year + '"' );
		}
		if ( 'en' !== language ) {
			attrs.push( 'language="' + language + '"' );
		}
		if ( title ) {
			attrs.push( 'title="' + title.replace( /"/g, '&quot;' ) + '"' );
		}
		if ( builderChecked( '[data-monthly-builder-iqamah]' ) ) {
			attrs.push( 'iqamah="yes"' );
		}
		if ( ! builderChecked( '[data-monthly-builder-navigation]' ) ) {
			attrs.push( 'navigation="no"' );
		}

		return '[masjidos_monthly_prayer_times' + ( attrs.length ? ' ' + attrs.join( ' ' ) : '' ) + ']';
	}

	function generatedCalendarShortcode() {
		var month = builderValue( '[data-calendar-builder-month]', '' );
		var year = builderValue( '[data-calendar-builder-year]', '' );
		var language = builderValue( '[data-calendar-builder-language]', 'en' );
		var title = builderValue( '[data-calendar-builder-title]', '' );
		var attrs = [];

		if ( month ) {
			attrs.push( 'month="' + month + '"' );
		}
		if ( year ) {
			attrs.push( 'year="' + year + '"' );
		}
		if ( 'en' !== language ) {
			attrs.push( 'language="' + language + '"' );
		}
		if ( title ) {
			attrs.push( 'title="' + title.replace( /"/g, '&quot;' ) + '"' );
		}
		if ( ! builderChecked( '[data-calendar-builder-navigation]' ) ) {
			attrs.push( 'navigation="no"' );
		}

		return '[masjidos_islamic_calendar' + ( attrs.length ? ' ' + attrs.join( ' ' ) : '' ) + ']';
	}

	function generatedDuasShortcode() {
		var design = builderValue( '[data-duas-builder-design]', 'cards' );
		var language = builderValue( '[data-duas-builder-language]', 'en' );
		var category = builderValue( '[data-duas-builder-category]', 'all' );
		var limit = builderValue( '[data-duas-builder-limit]', '4' );
		var title = builderValue( '[data-duas-builder-title]', '' );
		var attrs = [];

		if ( 'cards' !== design ) {
			attrs.push( 'design="' + design + '"' );
		}
		if ( 'en' !== language ) {
			attrs.push( 'language="' + language + '"' );
		}
		if ( 'all' !== category ) {
			attrs.push( 'category="' + category + '"' );
		}
		if ( limit && '4' !== limit ) {
			attrs.push( 'limit="' + limit + '"' );
		}
		if ( title ) {
			attrs.push( 'title="' + title.replace( /"/g, '&quot;' ) + '"' );
		}
		if ( ! builderChecked( '[data-duas-builder-source]' ) ) {
			attrs.push( 'source="no"' );
		}
		if ( ! builderChecked( '[data-duas-builder-counter]' ) ) {
			attrs.push( 'counter="no"' );
		}
		if ( ! builderChecked( '[data-duas-builder-share]' ) ) {
			attrs.push( 'share="no"' );
		}
		if ( ! builderChecked( '[data-duas-builder-audio]' ) ) {
			attrs.push( 'audio="no"' );
		}

		return '[masjidos_duas_azkar' + ( attrs.length ? ' ' + attrs.join( ' ' ) : '' ) + ']';
	}

	function generatedAnnouncementShortcode() {
		var design = builderValue( '[data-announcement-builder-design]', 'list' );
		var language = builderValue( '[data-announcement-builder-language]', 'en' );
		var type = builderValue( '[data-announcement-builder-type]', 'all' );
		var limit = builderValue( '[data-announcement-builder-limit]', '5' );
		var title = builderValue( '[data-announcement-builder-title]', '' );
		var attrs = [];

		if ( 'list' !== design ) {
			attrs.push( 'design="' + design + '"' );
		}
		if ( 'en' !== language ) {
			attrs.push( 'language="' + language + '"' );
		}
		if ( 'all' !== type ) {
			attrs.push( 'type="' + type + '"' );
		}
		if ( '5' !== limit ) {
			attrs.push( 'limit="' + limit + '"' );
		}
		if ( title ) {
			attrs.push( 'title="' + title.replace( /"/g, '&quot;' ) + '"' );
		}
		if ( ! builderChecked( '[data-announcement-builder-date]' ) ) {
			attrs.push( 'show_date="no"' );
		}

		return '[masjidos_announcements' + ( attrs.length ? ' ' + attrs.join( ' ' ) : '' ) + ']';
	}

	function generatedEventsShortcode() {
		var design = builderValue( '[data-events-builder-design]', 'list' );
		var language = builderValue( '[data-events-builder-language]', 'en' );
		var limit = builderValue( '[data-events-builder-limit]', '5' );
		var title = builderValue( '[data-events-builder-title]', '' );
		var attrs = [];

		if ( 'list' !== design ) {
			attrs.push( 'design="' + design + '"' );
		}
		if ( 'en' !== language ) {
			attrs.push( 'language="' + language + '"' );
		}
		if ( '5' !== limit && limit ) {
			attrs.push( 'limit="' + limit + '"' );
		}
		if ( title ) {
			attrs.push( 'title="' + title.replace( /"/g, '&quot;' ) + '"' );
		}

		return '[masjidos_events' + ( attrs.length ? ' ' + attrs.join( ' ' ) : '' ) + ']';
	}

	function builderValue( selector, fallback ) {
		var el = app.querySelector( selector );
		return el ? String( el.value || '' ).trim() : fallback;
	}

	function builderChecked( selector ) {
		var el = app.querySelector( selector );
		return ! el || el.checked;
	}

	function copyText( text, btn ) {
		var original = btn.textContent;
		function done( ok ) {
			btn.textContent = ok ? __( 'Copied', 'masjidos' ) : __( 'Select', 'masjidos' );
			setTimeout( function () {
				btn.textContent = original;
			}, 1400 );
		}

		if ( navigator.clipboard && navigator.clipboard.writeText ) {
			navigator.clipboard.writeText( text ).then( function () {
				done( true );
			} ).catch( function () {
				done( fallbackCopyText( text ) );
			} );
			return;
		}

		done( fallbackCopyText( text ) );
	}

	function fallbackCopyText( text ) {
		var area = document.createElement( 'textarea' );
		area.value = text;
		area.setAttribute( 'readonly', 'readonly' );
		area.style.position = 'fixed';
		area.style.top = '-9999px';
		area.style.left = '-9999px';
		document.body.appendChild( area );
		area.focus();
		area.select();

		try {
			return document.execCommand( 'copy' );
		} catch ( e ) {
			return false;
		} finally {
			document.body.removeChild( area );
		}
	}

	function switchTab( tab, historyMode ) {
		if ( tab === 'khutbah' ) {
			tab = 'minbar';
		}
		if ( [ 'welcome', 'dashboard', 'announcements', 'events', 'minbar', 'features', 'modules', 'settings', 'docs' ].indexOf( tab ) === -1 ) {
			tab = 'dashboard';
		}
		state.activeTab = tab;
		var shell = app.querySelector( '.itmms-shell' );
		if ( shell ) {
			shell.classList.toggle( 'itmms-shell--welcome', tab === 'welcome' );
		}
		app.querySelectorAll( '.itmms-nav-item[data-tab]' ).forEach( function ( item ) {
			var itemTab = item.getAttribute( 'data-tab' );
			var itemMinbar = item.getAttribute( 'data-minbar-tab' );
			var itemSettings = item.getAttribute( 'data-settings-tab' );
			var isActive = false;

			if ( itemTab !== tab ) {
				item.classList.toggle( 'active', false );
				return;
			}

			if ( tab === 'minbar' ) {
				isActive = ( itemMinbar || 'dashboard' ) === ( state.minbarTab || 'dashboard' );
			} else if ( tab === 'settings' ) {
				if ( itemSettings ) {
					isActive = isPrayerSettingsTab( state.settingsTab ) && (
						itemSettings === state.settingsTab ||
						( itemSettings === 'timetable' && isPrayerSettingsTab( state.settingsTab ) )
					);
				} else {
					isActive = ! isPrayerSettingsTab( state.settingsTab );
				}
			} else {
				isActive = ! itemMinbar && ! itemSettings;
			}

			item.classList.toggle( 'active', isActive );
		} );
		app.querySelectorAll( '.itmms-tab-content' ).forEach( function ( item ) {
			item.classList.toggle( 'active', item.id === 'itmms-tab-' + tab );
		} );
		var title = document.getElementById( 'itmms-title' );
		var subtitle = document.getElementById( 'itmms-subtitle' );
		var location = [ state.settings.city, state.settings.country ].filter( Boolean ).join( ', ' );
		var fallbackSub = location || data.siteUrl || '';
		var meta = pageMeta( tab );
		if ( title ) {
			title.textContent = meta.title;
		}
		if ( subtitle ) {
			subtitle.textContent = meta.desc || fallbackSub;
		}

		// Close sidebar on mobile after choosing a tab
		if ( shell ) {
			shell.classList.remove( 'sidebar-open' );
		}

		if ( historyMode ) {
			syncViewUrl( historyMode );
		}
	}

	function pageMeta( tab ) {
		var minbar = {
			dashboard: {
				title: __( 'Minbar', 'masjidos' ),
				desc: __( 'Plan, write, and archive Friday khutbahs — profiles, schedule, and references in one place.', 'masjidos' )
			},
			archive: {
				title: __( 'Archive', 'masjidos' ),
				desc: __( 'Search, edit, and reuse past khutbahs.', 'masjidos' )
			},
			planner: {
				title: __( 'Planner', 'masjidos' ),
				desc: __( 'Plan upcoming topics around Islamic days and seasons.', 'masjidos' )
			},
			references: {
				title: __( 'References', 'masjidos' ),
				desc: __( 'Find Qur\'an, hadith, and dua references for your sermon.', 'masjidos' )
			},
			builder: {
				title: __( 'Sermon Builder', 'masjidos' ),
				desc: __( 'Draft your khutbah outline and notes in one workspace.', 'masjidos' )
			},
			schedule: {
				title: __( 'Schedule', 'masjidos' ),
				desc: __( 'Manage khatib profiles and the Friday roster.', 'masjidos' )
			}
		};
		var pages = {
			welcome: {
				title: __( 'Welcome', 'masjidos' ),
				desc: __( 'Get your masjid live in a few minutes.', 'masjidos' )
			},
			dashboard: {
				title: __( 'Dashboard', 'masjidos' ),
				desc: __( 'Live overview for your masjid operations.', 'masjidos' )
			},
			announcements: {
				title: __( 'Announcements', 'masjidos' ),
				desc: __( 'Create public notices, urgent updates, and scheduled Jumuah messages.', 'masjidos' )
			},
			events: {
				title: __( 'Events', 'masjidos' ),
				desc: __( 'Schedule special lectures, community gatherings, Eid prayers, or charity events.', 'masjidos' )
			},
			minbar: minbar[ state.minbarTab ] || minbar.dashboard,
			features: {
				title: __( 'Features', 'masjidos' ),
				desc: __( 'Browse widgets, copy shortcodes, and preview before publishing.', 'masjidos' )
			},
			modules: {
				title: __( 'Modules', 'masjidos' ),
				desc: __( 'Turn modules on only when your masjid needs them. Disabled modules stay light.', 'masjidos' )
			},
			settings: {
				title: isPrayerSettingsTab( state.settingsTab ) ? __( 'Prayer Setup', 'masjidos' ) : __( 'Settings', 'masjidos' ),
				desc: isPrayerSettingsTab( state.settingsTab )
					? __( 'Configure calculation, timetable, offsets, and Iqamah rules.', 'masjidos' )
					: __( 'Organized setup panels for masjid profile, prayer times, Iqamah, and Jumuah.', 'masjidos' )
			},
			docs: {
				title: __( 'Docs', 'masjidos' ),
				desc: __( 'Attribute reference, paste guides, and shortcode generators.', 'masjidos' )
			}
		};
		return pages[ tab ] || { title: tab, desc: '' };
	}

	function saveSettings( payload, trigger ) {
		var status = document.getElementById( 'itmms-save-status' );
		var previousLanguage = ( state.settings && state.settings.ui_language ) || data.uiLanguage || 'en';
		if ( status ) {
			status.textContent = __( 'Saving...', 'masjidos' );
		}
		if ( trigger ) {
			trigger.disabled = true;
		}

		api( 'settings', {
			method: 'POST',
			body: JSON.stringify( payload )
		} ).then( function ( response ) {
			state.settings = response.settings;
			state.modules = response.modules;
			var nextLanguage = ( response.settings && response.settings.ui_language ) || 'en';
			if ( nextLanguage !== previousLanguage ) {
				return applyUiLanguage( nextLanguage ).then( function () {
					return api( 'dashboard' );
				} );
			}
			return api( 'dashboard' );
		} ).then( function ( response ) {
			if ( ! response ) {
				return;
			}
			state.settings = response.settings || state.settings;
			state.modules = response.modules || state.modules;
			state.stats = response.stats || state.stats;
			state.prayers = response.prayers || state.prayers;
			state.nextPrayer = response.next_prayer || state.nextPrayer;
			state.prayerMeta = response.prayer_meta || state.prayerMeta;
			state.hijriDate = response.hijri_date || state.hijriDate;
			state.trust = response.trust || state.trust;
			state.upcomingDays = response.upcoming_days || [];
			state.timetable = response.timetable || state.timetable;
			state.dashboardAnnouncements = response.announcements || state.dashboardAnnouncements;
			render();
			switchTab( 'settings' );
			var saved = document.getElementById( 'itmms-save-status' );
			if ( saved ) {
				saved.textContent = __( 'Saved', 'masjidos' );
			}
		} ).catch( function () {
			if ( status ) {
				status.textContent = __( 'Could not save. Please try again.', 'masjidos' );
			}
			if ( trigger ) {
				trigger.disabled = false;
			}
		} );
	}

	function loadDashboard() {
		api( 'dashboard' ).then( function ( response ) {
			state.settings = response.settings || state.settings;
			state.modules = response.modules || state.modules;
			state.stats = response.stats || state.stats;
			state.prayers = response.prayers || state.prayers;
			state.nextPrayer = response.next_prayer || state.nextPrayer;
			state.prayerMeta = response.prayer_meta || state.prayerMeta;
			state.hijriDate = response.hijri_date || state.hijriDate;
			state.trust = response.trust || state.trust;
			state.upcomingDays = response.upcoming_days || [];
			state.timetable = response.timetable || state.timetable;
			state.dashboardAnnouncements = response.announcements || [];
			state.dashboardEvents = response.events || [];
			state.pro = response.pro || data.pro || {};
			state.proCards = response.pro_cards || [];
			return Promise.all( [
				loadAnnouncements().catch( function () {} ),
				loadEvents().catch( function () {} ),
				loadMinbarData()
			] );
		} ).then( function () {
			if ( shouldForceWelcome( { activeTab: state.activeTab } ) && ! urlHasExplicitView() ) {
				state.activeTab = 'welcome';
			}
			render();
		} ).catch( function () {
			if ( shouldForceWelcome( { activeTab: state.activeTab } ) && ! urlHasExplicitView() ) {
				state.activeTab = 'welcome';
			}
			render();
		} );
	}

	function renderHijriDate() {
		var el = document.getElementById( 'itmms-hijri-date' );
		if ( ! el ) {
			return;
		}
		el.textContent = state.hijriDate && state.hijriDate.label ? state.hijriDate.label : __( 'Available soon', 'masjidos' );
	}

	function tickCountdown() {
		var el = document.getElementById( 'itmms-countdown' );
		if ( ! el ) {
			return;
		}

		var now = new Date();
		var target = state.nextPrayer && state.nextPrayer.raw ? new Date( state.nextPrayer.raw ) : new Date();
		if ( ! state.nextPrayer || ! state.nextPrayer.raw ) {
			target.setHours( 18, 48, 0, 0 );
			if ( target < now ) {
				target.setDate( target.getDate() + 1 );
			}
		}

		var ms = target - now;
		var seconds = Math.max( 0, Math.floor( ms / 1000 ) );
		var h = String( Math.floor( seconds / 3600 ) ).padStart( 2, '0' );
		var m = String( Math.floor( ( seconds % 3600 ) / 60 ) ).padStart( 2, '0' );
		var s = String( seconds % 60 ).padStart( 2, '0' );
		el.textContent = h + ':' + m + ':' + s;
		setTimeout( tickCountdown, 1000 );
	}

	primeWordPressExit();
	window.itmms.render = render;
	window.itmms.switchTab = switchTab;
	window.itmms.activateSettingsTab = activateSettingsTab;
	window.addEventListener( 'popstate', function () {
		var view = readViewState();
		state.settingsTab = view.settingsTab;
		state.docsTab = view.docsTab;
		state.minbarTab = view.minbarTab || 'dashboard';
		state.activeTab = view.activeTab;
		render();
		activateSettingsTab( view.settingsTab );
		activateDocsTab( view.docsTab );
	} );
	loadDashboard();
} )( window.itmmData || {} );
