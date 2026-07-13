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

	// Setup shared state
	var initialView = window.itmms.readViewState();
	var state = {
		settings: data.settings || {},
		modules: data.modules || [],
		stats: { announcements: 0, events: 0 },
		prayers: [],
		nextPrayer: null,
		prayerMeta: null,
		hijriDate: null,
		announcements: [],
		dashboardAnnouncements: [],
		editingAnnouncement: 0,
		events: [],
		dashboardEvents: [],
		editingEvent: 0,
		activeTab: initialView.activeTab,
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
	var settingsHtml = window.itmms.settings.settingsHtml;
	var announcementsHtml = window.itmms.announcements.announcementsHtml;
	var docsHtml = window.itmms.docs.docsHtml;
	var eventsHtml = window.itmms.events.eventsHtml;
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

		app.innerHTML = '<div class="itmms-shell">' +
			'<aside class="itmms-sidebar">' +
				'<div class="itmms-brand"><div class="itmms-brand-mark">' + icon( 'ledger' ) + '</div><div><strong>MasjidOS</strong><span>' + esc( masjidName ) + '</span></div></div>' +
				'<nav class="itmms-nav">' +
					navButton( 'dashboard', 'clock', __( 'Dashboard', 'masjidos' ), true ) +
					( state.settings.modules.announcements ? navButton( 'announcements', 'megaphone', __( 'Notices', 'masjidos' ), false ) : '' ) +
					( state.settings.modules.events ? navButton( 'events', 'calendar', __( 'Events', 'masjidos' ), false ) : '' ) +
					( state.settings.modules.duas_azkar ? navLink( data.adminUrl + 'edit.php?post_type=itmms_dua', 'book', __( 'Duas Library', 'masjidos' ) ) : '' ) +
					navButton( 'features', 'star', __( 'Features', 'masjidos' ), false ) +
					navButton( 'modules', 'settings', __( 'Modules', 'masjidos' ), false ) +
					navButton( 'settings', 'settings', __( 'Settings', 'masjidos' ), false ) +
					navButton( 'docs', 'book', __( 'Docs', 'masjidos' ), false ) +
				'</nav>' +
				'<div class="itmms-user-card"><div>' + esc( initials( userName ) ) + '</div><span><strong>' + esc( userName ) + '</strong><small>' + esc( __( 'Administrator', 'masjidos' ) ) + '</small></span></div>' +
			'</aside>' +
			'<main class="itmms-main">' +
				'<header class="itmms-topbar">' +
					'<button class="itmms-sidebar-toggle" aria-label="' + esc( __( 'Toggle menu', 'masjidos' ) ) + '">' +
						'<svg viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>' +
					'</button>' +
					'<div><h1 id="itmms-title">' + esc( __( 'Dashboard', 'masjidos' ) ) + '</h1><p>' + esc( location || data.siteUrl ) + '</p></div>' +
					'<div class="itmms-topbar-actions"><a class="itmms-btn itmms-wp-exit-btn" href="' + esc( data.adminUrl ) + '" title="' + esc( __( 'Go to WordPress Dashboard', 'masjidos' ) ) + '" aria-label="' + esc( __( 'Go to WordPress Dashboard', 'masjidos' ) ) + '" data-itmms-exit>' + icon( 'external' ) + '<span>WordPress</span></a><button class="itmms-btn itmms-btn-primary" data-open-settings>' + esc( __( 'Configure', 'masjidos' ) ) + '</button></div>' +
				'</header>' +
				'<section class="itmms-page">' +
					'<div class="itmms-tab-content active" id="itmms-tab-dashboard">' + dashboardHtml() + '</div>' +
					'<div class="itmms-tab-content" id="itmms-tab-announcements">' + announcementsHtml() + '</div>' +
					'<div class="itmms-tab-content" id="itmms-tab-events">' + eventsHtml() + '</div>' +
					'<div class="itmms-tab-content" id="itmms-tab-features">' + featuresHtml() + '</div>' +
					'<div class="itmms-tab-content" id="itmms-tab-modules">' + modulesHtml() + '</div>' +
					'<div class="itmms-tab-content" id="itmms-tab-settings">' + settingsHtml() + '</div>' +
					'<div class="itmms-tab-content" id="itmms-tab-docs">' + docsHtml() + '</div>' +
				'</section>' +
			'</main>' +
		'</div>';

		bindEvents();
		bindFeaturesEvents();
		switchTab( state.activeTab );
		activateSettingsTab( state.settingsTab );
		activateDocsTab( state.docsTab );
		renderHijriDate();
		tickCountdown();
	}

	function navButton( tab, iconName, label, active ) {
		return '<button class="itmms-nav-item' + ( active ? ' active' : '' ) + '" data-tab="' + esc( tab ) + '">' + icon( iconName ) + '<span>' + esc( label ) + '</span></button>';
	}

	function navLink( href, iconName, label ) {
		return '<a class="itmms-nav-item" href="' + esc( href ) + '">' + icon( iconName ) + '<span>' + esc( label ) + '</span></a>';
	}

	function bindEvents() {
		app.querySelectorAll( '[data-itmms-exit]' ).forEach( function ( link ) {
			link.addEventListener( 'click', exitToWordPress );
		} );

		var adminbarExit = document.querySelector( '#wp-admin-bar-itmms-exit a' );
		if ( adminbarExit ) {
			adminbarExit.addEventListener( 'click', exitToWordPress );
		}

		app.querySelectorAll( '.itmms-nav-item[data-tab]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () { switchTab( btn.getAttribute( 'data-tab' ), 'push' ); } );
		} );

		bindAnnouncementEvents();
		bindEventEvents();

		app.querySelectorAll( '[data-open-settings]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () { switchTab( 'settings', 'push' ); } );
		} );

		bindMediaPickers();
		bindSettingsTabs();
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
				payload.modules = state.settings.modules;
				payload.prayer_offsets = {};
				form.querySelectorAll( '[data-offset]' ).forEach( function ( input ) {
					payload.prayer_offsets[ input.getAttribute( 'data-offset' ) ] = input.value;
				} );
				payload.iqamah_times = {};
				form.querySelectorAll( '[data-iqamah]' ).forEach( function ( input ) {
					payload.iqamah_times[ input.getAttribute( 'data-iqamah' ) ] = input.value;
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
		var tabs = app.querySelectorAll( '[data-settings-tab]' );
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
		if ( [ 'profile', 'calculation', 'adjustments', 'iqamah', 'jumuah', 'tv', 'public' ].indexOf( key ) === -1 ) {
			key = 'profile';
		}
		state.settingsTab = key;
		app.querySelectorAll( '[data-settings-tab]' ).forEach( function ( tab ) {
			tab.classList.toggle( 'active', tab.getAttribute( 'data-settings-tab' ) === key );
		} );
		app.querySelectorAll( '[data-settings-panel]' ).forEach( function ( panel ) {
			panel.classList.toggle( 'active', panel.getAttribute( 'data-settings-panel' ) === key );
		} );
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
	}

	function activateDocsTab( key, historyMode ) {
		if ( [ 'overview', 'generators', 'prayer', 'jumuah', 'notices', 'pro', 'reference' ].indexOf( key ) === -1 ) {
			key = 'overview';
		}
		state.docsTab = key;
		app.querySelectorAll( '[data-doc-tab]' ).forEach( function ( tab ) {
			tab.classList.toggle( 'active', tab.getAttribute( 'data-doc-tab' ) === key );
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

		app.querySelectorAll( '[data-builder-design], [data-builder-language], [data-builder-title], [data-builder-qibla], [data-builder-meta], [data-builder-iqamah], [data-jumuah-builder-design], [data-jumuah-builder-language], [data-jumuah-builder-title], [data-jumuah-builder-meta], [data-monthly-builder-design], [data-monthly-builder-month], [data-monthly-builder-year], [data-monthly-builder-language], [data-monthly-builder-title], [data-monthly-builder-iqamah], [data-monthly-builder-navigation], [data-calendar-builder-month], [data-calendar-builder-year], [data-calendar-builder-language], [data-calendar-builder-title], [data-calendar-builder-navigation], [data-duas-builder-design], [data-duas-builder-language], [data-duas-builder-category], [data-duas-builder-limit], [data-duas-builder-title], [data-duas-builder-source], [data-duas-builder-counter], [data-duas-builder-share], [data-duas-builder-audio], [data-announcement-builder-design], [data-announcement-builder-language], [data-announcement-builder-type], [data-announcement-builder-limit], [data-announcement-builder-title], [data-announcement-builder-date], [data-events-builder-design], [data-events-builder-language], [data-events-builder-limit], [data-events-builder-title]' ).forEach( function ( input ) {
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
		if ( [ 'dashboard', 'announcements', 'events', 'features', 'modules', 'settings', 'docs' ].indexOf( tab ) === -1 ) {
			tab = 'dashboard';
		}
		state.activeTab = tab;
		app.querySelectorAll( '.itmms-nav-item' ).forEach( function ( item ) {
			item.classList.toggle( 'active', item.getAttribute( 'data-tab' ) === tab );
		} );
		app.querySelectorAll( '.itmms-tab-content' ).forEach( function ( item ) {
			item.classList.toggle( 'active', item.id === 'itmms-tab-' + tab );
		} );
		var title = document.getElementById( 'itmms-title' );
		if ( title ) {
			title.textContent = {
				dashboard: __( 'Dashboard', 'masjidos' ),
				announcements: __( 'Notices', 'masjidos' ),
				events: __( 'Events', 'masjidos' ),
				features: __( 'Features', 'masjidos' ),
				modules: __( 'Modules', 'masjidos' ),
				settings: __( 'Settings', 'masjidos' ),
				docs: __( 'Docs', 'masjidos' )
			}[ tab ] || tab;
		}

		// Close sidebar on mobile after choosing a tab
		var shell = app.querySelector( '.itmms-shell' );
		if ( shell ) {
			shell.classList.remove( 'sidebar-open' );
		}

		if ( historyMode ) {
			syncViewUrl( historyMode );
		}
	}

	function saveSettings( payload, trigger ) {
		var status = document.getElementById( 'itmms-save-status' );
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
			return api( 'prayer-times/today' );
		} ).then( function ( response ) {
			state.prayers = response.prayers || state.prayers;
			state.nextPrayer = response.next_prayer || state.nextPrayer;
			state.prayerMeta = response.meta || state.prayerMeta;
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
			state.dashboardAnnouncements = response.announcements || [];
			state.dashboardEvents = response.events || [];
			return Promise.all( [
				loadAnnouncements().catch( function () {} ),
				loadEvents().catch( function () {} )
			] );
		} ).then( function () {
			render();
		} ).catch( function () {
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
	window.addEventListener( 'popstate', function () {
		var view = readViewState();
		state.settingsTab = view.settingsTab;
		state.docsTab = view.docsTab;
		switchTab( view.activeTab );
		activateSettingsTab( view.settingsTab );
		activateDocsTab( view.docsTab );
	} );
	loadDashboard();
} )( window.itmmData || {} );
