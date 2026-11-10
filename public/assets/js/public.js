/**
 * MasjidOS public widgets.
 */
( function () {
	'use strict';

	var activeDuaAudio = null;
	var activeDuaButton = null;

	function pad2( value ) {
		return String( value ).padStart( 2, '0' );
	}

	function digitMap( language ) {
		if ( language === 'bn' ) {
			return { '0': '০', '1': '১', '2': '২', '3': '৩', '4': '৪', '5': '৫', '6': '৬', '7': '৭', '8': '৮', '9': '৯' };
		}
		if ( language === 'ar' ) {
			return { '0': '٠', '1': '١', '2': '٢', '3': '٣', '4': '٤', '5': '٥', '6': '٦', '7': '٧', '8': '٨', '9': '٩' };
		}
		return null;
	}

	function localizeDigits( value, language ) {
		var text = String( value );
		var map = digitMap( language );
		if ( ! map ) {
			return text;
		}
		return text.replace( /[0-9]/g, function ( digit ) {
			return map[ digit ] || digit;
		} );
	}

	function prayerWidgetLanguage( widget ) {
		if ( ! widget ) {
			return 'en';
		}
		if ( widget.classList.contains( 'itmms-public-prayer--lang-bn' ) ) {
			return 'bn';
		}
		if ( widget.classList.contains( 'itmms-public-prayer--lang-ar' ) ) {
			return 'ar';
		}
		return 'en';
	}

	function formatCountdown( target, language ) {
		var seconds = Math.max( 0, Math.floor( ( target.getTime() - Date.now() ) / 1000 ) );
		var text = pad2( Math.floor( seconds / 3600 ) ) + ':' +
			pad2( Math.floor( ( seconds % 3600 ) / 60 ) ) + ':' +
			pad2( seconds % 60 );
		return localizeDigits( text, language || 'en' );
	}

	function advanceNextPrayer( widget ) {
		var rows = Array.prototype.slice.call( widget.querySelectorAll( '.itmms-public-prayer__row[data-prayer-raw]' ) );
		if ( ! rows.length ) {
			return null;
		}

		var nonObligatory = { sunrise: true, ishraq: true, zawal: true };
		var now = new Date();
		var obligatoryRows = [];

		rows.forEach( function ( row ) {
			var key = row.getAttribute( 'data-prayer-key' ) || '';
			var raw = row.getAttribute( 'data-prayer-raw' ) || '';
			if ( nonObligatory[ key ] || ! /^\d{1,2}:\d{2}/.test( raw ) ) {
				return;
			}
			var parts = raw.split( ':' );
			var date = new Date( now.getFullYear(), now.getMonth(), now.getDate(), Number( parts[0] ), Number( parts[1] ), 0, 0 );
			obligatoryRows.push( {
				row: row,
				key: key,
				date: date
			} );
		} );

		if ( ! obligatoryRows.length ) {
			return null;
		}

		var nextEntry = null;
		var currentEntry = obligatoryRows[ obligatoryRows.length - 1 ];

		for ( var i = 0; i < obligatoryRows.length; i++ ) {
			if ( obligatoryRows[ i ].date.getTime() > now.getTime() + 1000 ) {
				nextEntry = obligatoryRows[ i ];
				currentEntry = i > 0 ? obligatoryRows[ i - 1 ] : obligatoryRows[ obligatoryRows.length - 1 ];
				break;
			}
		}

		if ( ! nextEntry ) {
			nextEntry = {
				row: obligatoryRows[ 0 ].row,
				key: obligatoryRows[ 0 ].key,
				date: new Date( obligatoryRows[ 0 ].date.getTime() + 86400000 )
			};
			currentEntry = obligatoryRows[ obligatoryRows.length - 1 ];
		}

		rows.forEach( function ( row ) {
			row.classList.toggle( 'is-current', currentEntry && row === currentEntry.row );
		} );

		var nextStrong = widget.querySelector( '.itmms-public-prayer__next strong' );
		if ( nextStrong && nextEntry.row ) {
			var nextLabel = nextEntry.row.getAttribute( 'data-prayer-label' ) || '';
			var nextClock = nextEntry.row.getAttribute( 'data-prayer-clock' ) || '';
			if ( nextLabel && nextClock ) {
				nextStrong.textContent = nextLabel + ' · ' + nextClock;
			}
		}

		var iso = nextEntry.date.toISOString();
		widget.setAttribute( 'data-next-prayer', iso );
		return nextEntry.date;
	}

	function tick( widget ) {
		if ( ! document.documentElement.contains( widget ) ) {
			return;
		}

		var raw = widget.getAttribute( 'data-next-prayer' );
		var target = raw ? new Date( raw ) : null;
		var output = widget.querySelector( '[data-itmms-public-countdown]' );
		if ( ! target || ! output ) {
			return;
		}

		if ( target.getTime() <= Date.now() ) {
			var advanced = advanceNextPrayer( widget );
			if ( advanced ) {
				target = advanced;
			}
		}

		output.textContent = formatCountdown( target, prayerWidgetLanguage( widget ) );
		window.setTimeout( function () {
			tick( widget );
		}, 1000 );
	}

	function initPrayerCountdowns( root ) {
		( root || document ).querySelectorAll( '.itmms-public-prayer' ).forEach( function ( widget ) {
			if ( widget.getAttribute( 'data-itmms-countdown-ready' ) ) {
				return;
			}
			widget.setAttribute( 'data-itmms-countdown-ready', '1' );
			tick( widget );
		} );
	}

	function loadMonthlyWidget( widget, month, year, focusSelector ) {
		var endpoint = widget.getAttribute( 'data-endpoint' );
		var error = widget.querySelector( '[data-itmms-monthly-error]' );
		if ( ! endpoint || widget.classList.contains( 'is-loading' ) ) {
			return;
		}

		var params = new URLSearchParams( {
			month: String( month ),
			year: String( year ),
			design: widget.getAttribute( 'data-design' ) || 'table',
			language: widget.getAttribute( 'data-language' ) || 'en',
			iqamah: widget.getAttribute( 'data-iqamah' ) || 'no',
			extras: widget.getAttribute( 'data-extras' ) || 'no',
			title: widget.getAttribute( 'data-title' ) || ''
		} );

		widget.classList.add( 'is-loading' );
		widget.setAttribute( 'aria-busy', 'true' );
		if ( error ) {
			error.hidden = true;
		}

		fetch( endpoint + '?' + params.toString(), {
			credentials: 'same-origin',
			headers: { Accept: 'application/json' }
		} )
			.then( function ( response ) {
				if ( ! response.ok ) {
					throw new Error( 'Monthly timetable request failed.' );
				}
				return response.json();
			} )
			.then( function ( data ) {
				var template = document.createElement( 'template' );
				template.innerHTML = data.html || '';
				var replacement = template.content.firstElementChild;
				if ( ! replacement ) {
					throw new Error( 'Monthly timetable response was empty.' );
				}

				widget.replaceWith( replacement );
				var focusTarget = replacement.querySelector( focusSelector );
				if ( focusTarget ) {
					focusTarget.focus();
				}
			} )
			.catch( function () {
				widget.classList.remove( 'is-loading' );
				widget.removeAttribute( 'aria-busy' );
				if ( error ) {
					error.textContent = widget.getAttribute( 'data-error' ) || 'The timetable could not be loaded.';
					error.hidden = false;
				}
			} );
	}

	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest( '[data-itmms-monthly-step]' );
		if ( ! button ) {
			return;
		}

		var widget = button.closest( '[data-itmms-monthly]' );
		var current = new Date( Number( widget.getAttribute( 'data-year' ) ), Number( widget.getAttribute( 'data-month' ) ) - 1, 1 );
		current.setMonth( current.getMonth() + Number( button.getAttribute( 'data-itmms-monthly-step' ) ) );
		loadMonthlyWidget( widget, current.getMonth() + 1, current.getFullYear(), '[data-itmms-monthly-step="' + button.getAttribute( 'data-itmms-monthly-step' ) + '"]' );
	} );

	document.addEventListener( 'click', function ( event ) {
		var currentButton = event.target.closest( '[data-itmms-monthly-current]' );
		if ( currentButton ) {
			var widget = currentButton.closest( '[data-itmms-monthly]' );
			loadMonthlyWidget( widget, Number( widget.getAttribute( 'data-current-month' ) ), Number( widget.getAttribute( 'data-current-year' ) ), '[data-itmms-monthly-current]' );
			return;
		}

		var printButton = event.target.closest( '[data-itmms-monthly-print]' );
		if ( printButton ) {
			document.querySelectorAll( '.itmms-public-monthly.is-print-target' ).forEach( function ( target ) {
				target.classList.remove( 'is-print-target' );
			} );
			printButton.closest( '[data-itmms-monthly]' ).classList.add( 'is-print-target' );
			document.body.classList.add( 'itmms-printing-monthly' );
			window.print();
		}
	} );

	window.addEventListener( 'afterprint', function () {
		document.body.classList.remove( 'itmms-printing-monthly' );
		document.querySelectorAll( '.itmms-public-monthly.is-print-target' ).forEach( function ( target ) {
			target.classList.remove( 'is-print-target' );
		} );
	} );

	document.addEventListener( 'change', function ( event ) {
		if ( ! event.target.matches( '[data-itmms-monthly-month], [data-itmms-monthly-year]' ) ) {
			return;
		}

		var widget = event.target.closest( '[data-itmms-monthly]' );
		var month = widget.querySelector( '[data-itmms-monthly-month]' );
		var year = widget.querySelector( '[data-itmms-monthly-year]' );
		loadMonthlyWidget( widget, Number( month.value ), Number( year.value ), event.target.matches( '[data-itmms-monthly-month]' ) ? '[data-itmms-monthly-month]' : '[data-itmms-monthly-year]' );
	} );

	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest( '[data-itmms-ticker-toggle]' );
		if ( ! button ) {
			return;
		}

		var ticker = button.closest( '.itmms-public-announcements--ticker' );
		var paused = ticker.classList.toggle( 'is-paused' );
		button.setAttribute( 'aria-pressed', paused ? 'true' : 'false' );
		button.textContent = paused ? button.getAttribute( 'data-play-label' ) : button.getAttribute( 'data-pause-label' );
	} );

	function initQiblaCompass( root ) {
		( root || document ).querySelectorAll( '[data-itmms-public-qibla]' ).forEach( function ( qiblaWidget ) {
			if ( qiblaWidget.getAttribute( 'data-itmms-qibla-ready' ) ) {
				return;
			}
			qiblaWidget.setAttribute( 'data-itmms-qibla-ready', '1' );
			var bearing = parseFloat( qiblaWidget.getAttribute( 'data-itmms-public-qibla' ) ) || 0;
			var needle = qiblaWidget.querySelector( '.itmms-public-qibla__compass span' );
			var prompt = qiblaWidget.querySelector( '.itmms-public-qibla__prompt' );
			if ( ! needle ) {
				return;
			}

			var active = false;

			function handleOrientation( event ) {
				var heading = null;
				// iOS specific
				if ( event.webkitCompassHeading !== undefined ) {
					heading = event.webkitCompassHeading;
				} else if ( event.absolute === true || event.alpha !== null ) {
					// Android/standard absolute orientation
					// alpha is 0 when top points due North, increases counter-clockwise
					heading = 360 - event.alpha;
				}

				if ( heading !== null ) {
					var angle = bearing - heading;
					needle.style.transform = 'rotate(' + angle + 'deg)';
					if ( prompt && ! active ) {
						active = true;
						var widgetContainer = qiblaWidget.closest( '.itmms-public-prayer' );
						var langCode = 'en';
						if ( widgetContainer ) {
							var lang = widgetContainer.className.match( /itmms-public-prayer--lang-(\w+)/ );
							if ( lang ) {
								langCode = lang[1];
							}
						}
						if ( 'bn' === langCode ) {
							prompt.textContent = 'লাইভ কিবলা কম্পাস';
						} else if ( 'ar' === langCode ) {
							prompt.textContent = 'القبلة المباشرة';
						} else {
							prompt.textContent = 'Live Qibla Compass';
						}
						prompt.style.color = 'var(--itmms-public-accent)';
					}
				}
			}

			function startCompass() {
				if ( typeof DeviceOrientationEvent !== 'undefined' && typeof DeviceOrientationEvent.requestPermission === 'function' ) {
					DeviceOrientationEvent.requestPermission()
						.then( function ( response ) {
							if ( response === 'granted' ) {
								window.addEventListener( 'deviceorientation', handleOrientation, true );
							} else {
								window.alert( 'Permission to access device orientation was denied.' );
							}
						} )
						.catch( function ( e ) {
							console.error( 'Compass permission error:', e );
						} );
				} else {
					// standard/Android
					if ( 'ondeviceorientationabsolute' in window ) {
						window.addEventListener( 'deviceorientationabsolute', handleOrientation, true );
					} else {
						window.addEventListener( 'deviceorientation', handleOrientation, true );
					}
				}
			}

			qiblaWidget.addEventListener( 'click', startCompass );
			// Support keyboard trigger
			qiblaWidget.addEventListener( 'keydown', function ( e ) {
				if ( e.key === 'Enter' || e.key === ' ' ) {
					e.preventDefault();
					startCompass();
				}
			} );
		} );
	}

	function loadCalendarWidget( widget, month, year, focusSelector ) {
		var endpoint = widget.getAttribute( 'data-endpoint' );
		var language = widget.getAttribute( 'data-language' ) || 'en';
		var title = widget.getAttribute( 'data-title' ) || '';

		if ( ! endpoint ) {
			return;
		}

		widget.classList.add( 'is-loading' );
		widget.setAttribute( 'aria-busy', 'true' );

		var url = endpoint + '?month=' + month + '&year=' + year + '&language=' + language + '&title=' + encodeURIComponent( title );

		fetch( url )
			.then( function ( response ) {
				if ( ! response.ok ) {
					throw new Error();
				}
				return response.json();
			} )
			.then( function ( data ) {
				if ( ! data.html ) {
					throw new Error();
				}
				var temp = document.createElement( 'div' );
				temp.innerHTML = data.html;
				var newWidget = temp.firstElementChild;
				widget.replaceWith( newWidget );

				if ( focusSelector ) {
					var el = newWidget.querySelector( focusSelector );
					if ( el ) {
						el.focus();
					}
				}
			} )
			.catch( function () {
				widget.classList.remove( 'is-loading' );
				widget.removeAttribute( 'aria-busy' );
			} );
	}

	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest( '[data-itmms-calendar-step]' );
		if ( ! button ) {
			return;
		}

		var widget = button.closest( '[data-itmms-calendar]' );
		var current = new Date( Number( widget.getAttribute( 'data-year' ) ), Number( widget.getAttribute( 'data-month' ) ) - 1, 1 );
		current.setMonth( current.getMonth() + Number( button.getAttribute( 'data-itmms-calendar-step' ) ) );
		loadCalendarWidget( widget, current.getMonth() + 1, current.getFullYear(), '[data-itmms-calendar-step="' + button.getAttribute( 'data-itmms-calendar-step' ) + '"]' );
	} );

	document.addEventListener( 'click', function ( event ) {
		var currentButton = event.target.closest( '[data-itmms-calendar-current]' );
		if ( currentButton ) {
			var widget = currentButton.closest( '[data-itmms-calendar]' );
			loadCalendarWidget( widget, Number( widget.getAttribute( 'data-current-month' ) ), Number( widget.getAttribute( 'data-current-year' ) ), '[data-itmms-calendar-current]' );
		}
	} );

	document.addEventListener( 'change', function ( event ) {
		if ( ! event.target.matches( '[data-itmms-calendar-month], [data-itmms-calendar-year]' ) ) {
			return;
		}

		var widget = event.target.closest( '[data-itmms-calendar]' );
		var month = widget.querySelector( '[data-itmms-calendar-month]' );
		var year = widget.querySelector( '[data-itmms-calendar-year]' );
		loadCalendarWidget( widget, Number( month.value ), Number( year.value ), event.target.matches( '[data-itmms-calendar-month]' ) ? '[data-itmms-calendar-month]' : '[data-itmms-calendar-year]' );
	} );

	document.addEventListener( 'click', function ( event ) {
		var cell = event.target.closest( '.itmms-public-calendar__cell' );
		if ( ! cell ) {
			return;
		}

		var widget = cell.closest( '.itmms-public-calendar' );
		if ( ! widget ) {
			return;
		}

		var drawer = widget.querySelector( '[data-itmms-calendar-drawer]' );
		var titleEl = widget.querySelector( '[data-itmms-calendar-drawer-title]' );
		var listEl = widget.querySelector( '[data-itmms-calendar-drawer-list]' );
		if ( ! drawer || ! titleEl || ! listEl ) {
			return;
		}

		// Drawer is primarily for compact layouts where cell details are hidden.
		if ( window.matchMedia && window.matchMedia( '(min-width: 721px)' ).matches ) {
			var compact = widget.clientWidth > 0 && widget.clientWidth <= 720;
			if ( ! compact ) {
				return;
			}
		}

		widget.querySelectorAll( '.itmms-public-calendar__cell' ).forEach( function ( c ) {
			c.classList.remove( 'is-selected' );
		} );
		cell.classList.add( 'is-selected' );

		var gDateLabel = cell.getAttribute( 'data-gregorian-date' ) || '';
		var hDateLabel = cell.getAttribute( 'data-hijri-date-label' ) || '';
		var language = widget.getAttribute( 'data-language' ) || 'en';
		var locale = language === 'bn' ? 'bn-BD' : ( language === 'ar' ? 'ar' : undefined );
		var formattedGDate = gDateLabel;
		var parts = gDateLabel.split( '-' );

		if ( parts.length === 3 ) {
			formattedGDate = new Date( Number( parts[0] ), Number( parts[1] ) - 1, Number( parts[2] ) )
				.toLocaleDateString( locale, { day: 'numeric', month: 'long', year: 'numeric' } );
		}

		titleEl.textContent = formattedGDate + ( hDateLabel ? ' / ' + hDateLabel : '' );

		var eventItems = cell.querySelectorAll( '.itmms-public-calendar__event-item' );
		var holyLabel = cell.querySelector( '.itmms-public-calendar__holy-label' );
		var listHtml = '';

		if ( holyLabel ) {
			listHtml += '<div class="itmms-public-calendar__drawer-item is-holiday">' + holyLabel.textContent + '</div>';
		}

		if ( eventItems.length > 0 ) {
			eventItems.forEach( function ( item ) {
				listHtml += '<div class="itmms-public-calendar__drawer-item">' + item.innerHTML + '</div>';
			} );
		}

		if ( listHtml === '' ) {
			var emptyMsg = widget.getAttribute( 'data-error' ) || 'No events scheduled';
			listHtml = '<div class="itmms-public-calendar__drawer-item is-empty">' + emptyMsg + '</div>';
		}

		listEl.innerHTML = listHtml;
		drawer.hidden = false;
		drawer.style.display = 'block';
	} );

	document.addEventListener( 'keydown', function ( event ) {
		if ( event.key !== 'Enter' && event.key !== ' ' ) {
			return;
		}
		var cell = event.target.closest( '.itmms-public-calendar__cell' );
		if ( ! cell ) {
			return;
		}
		event.preventDefault();
		cell.click();
	} );

	function duaStorageKey( key ) {
		return 'masjidos_dua_count_' + key;
	}

	function getDuaCount( key ) {
		try {
			return Number( window.localStorage.getItem( duaStorageKey( key ) ) || '0' );
		} catch ( e ) {
			return 0;
		}
	}

	function setDuaCount( key, value ) {
		try {
			window.localStorage.setItem( duaStorageKey( key ), String( value ) );
		} catch ( e ) {}
	}

	function removeDuaCount( key ) {
		try {
			window.localStorage.removeItem( duaStorageKey( key ) );
		} catch ( e ) {}
	}

	function duaDigitMap( language ) {
		if ( language === 'bn' ) {
			return { '0': '০', '1': '১', '2': '২', '3': '৩', '4': '৪', '5': '৫', '6': '৬', '7': '৭', '8': '৮', '9': '৯' };
		}
		if ( language === 'ar' ) {
			return { '0': '٠', '1': '١', '2': '٢', '3': '٣', '4': '٤', '5': '٥', '6': '٦', '7': '٧', '8': '٨', '9': '٩' };
		}
		return null;
	}

	function formatDuaCount( value, language ) {
		var text = String( value );
		var map = duaDigitMap( language );
		if ( ! map ) {
			return text;
		}
		return text.replace( /[0-9]/g, function ( digit ) {
			return map[ digit ] || digit;
		} );
	}

	function duaWidgetLanguage( node ) {
		var widget = node && node.closest ? node.closest( '.itmms-public-duas' ) : null;
		if ( ! widget ) {
			return 'en';
		}
		if ( widget.classList.contains( 'itmms-public-duas--lang-bn' ) ) {
			return 'bn';
		}
		if ( widget.classList.contains( 'itmms-public-duas--lang-ar' ) ) {
			return 'ar';
		}
		return 'en';
	}

	function syncDuaCompleteState( item, count ) {
		if ( ! item ) {
			return;
		}
		var target = Number( item.getAttribute( 'data-itmms-dua-target' ) || '0' );
		if ( target > 0 && count >= target ) {
			item.classList.add( 'is-complete' );
		} else {
			item.classList.remove( 'is-complete' );
		}

		var progressBar = item.querySelector( '[data-itmms-dua-progress]' );
		if ( progressBar && target > 0 ) {
			var percent = Math.min( 100, Math.max( 0, Math.round( ( count / target ) * 100 ) ) );
			progressBar.style.width = percent + '%';
		}
	}

	function initDuasAzkar( root ) {
		( root || document ).querySelectorAll( '.itmms-public-duas__item[data-itmms-dua-key]' ).forEach( function ( item ) {
			var key = item.getAttribute( 'data-itmms-dua-key' );
			var output = item.querySelector( '[data-itmms-dua-count-value]' );
			if ( ! key || ! output ) {
				return;
			}

			var count = getDuaCount( key );
			output.textContent = formatDuaCount( count, duaWidgetLanguage( item ) );
			syncDuaCompleteState( item, count );
		} );
	}

	function resetActiveDuaAudioButton() {
		if ( activeDuaButton ) {
			var orig = activeDuaButton.getAttribute( 'data-original-label' );
			if ( orig ) {
				activeDuaButton.textContent = orig;
			}
			activeDuaButton.classList.remove( 'is-playing' );
			activeDuaButton.setAttribute( 'aria-pressed', 'false' );
			activeDuaButton = null;
		}
	}

	function stopActiveDuaAudio() {
		if ( activeDuaAudio ) {
			try {
				activeDuaAudio.pause();
				activeDuaAudio.currentTime = 0;
			} catch ( e ) {}
			activeDuaAudio = null;
		}
		resetActiveDuaAudioButton();
	}

	function toggleDuaAudio( button ) {
		var audioUrl = button.getAttribute( 'data-itmms-dua-audio' );
		if ( ! audioUrl ) {
			return;
		}

		if ( activeDuaButton === button && activeDuaAudio && ! activeDuaAudio.paused ) {
			stopActiveDuaAudio();
			return;
		}

		stopActiveDuaAudio();

		document.querySelectorAll( 'audio' ).forEach( function ( el ) {
			try {
				if ( ! el.paused ) {
					el.pause();
				}
			} catch ( e ) {}
		} );

		if ( ! button.getAttribute( 'data-original-label' ) ) {
			button.setAttribute( 'data-original-label', button.textContent );
		}

		var audio = new window.Audio( audioUrl );
		activeDuaAudio = audio;
		activeDuaButton = button;
		button.classList.add( 'is-playing' );
		button.setAttribute( 'aria-pressed', 'true' );
		button.textContent = button.getAttribute( 'data-playing-label' ) || button.textContent;

		audio.addEventListener( 'ended', function () {
			if ( activeDuaAudio === audio ) {
				stopActiveDuaAudio();
			}
		} );
		audio.addEventListener( 'error', function () {
			if ( activeDuaAudio === audio ) {
				stopActiveDuaAudio();
			}
		} );

		audio.play().catch( function () {
			if ( activeDuaAudio === audio ) {
				stopActiveDuaAudio();
			}
		} );
	}

	document.addEventListener( 'play', function ( event ) {
		if ( event.target && event.target.tagName === 'AUDIO' ) {
			stopActiveDuaAudio();
			document.querySelectorAll( 'audio' ).forEach( function ( el ) {
				if ( el !== event.target && ! el.paused ) {
					try {
						el.pause();
					} catch ( e ) {}
				}
			} );
		}
	}, true );

	function initAnnouncementPopups( root ) {
		( root || document ).querySelectorAll( '[data-itmms-popup-id]' ).forEach( function ( popup ) {
			var isEditorPreview = popup.classList.contains( 'is-editor-preview' );
			var id = popup.getAttribute( 'data-itmms-popup-id' );
			if ( ! isEditorPreview ) {
				try {
					if ( id && window.sessionStorage.getItem( 'itmms_popup_dismissed_' + id ) === 'yes' ) {
						popup.style.display = 'none';
						return;
					}
				} catch ( e ) {}
			}

			if ( popup.getAttribute( 'data-itmms-popup-ready' ) === '1' ) {
				return;
			}
			popup.setAttribute( 'data-itmms-popup-ready', '1' );

			var closeBtn = popup.querySelector( '[data-itmms-popup-close]' );
			if ( closeBtn && ! isEditorPreview ) {
				window.setTimeout( function () {
					closeBtn.focus();
				}, 0 );
			}

			popup.addEventListener( 'keydown', function ( event ) {
				if ( event.key !== 'Escape' || isEditorPreview ) {
					return;
				}
				event.preventDefault();
				popup.style.display = 'none';
				try {
					if ( id ) {
						window.sessionStorage.setItem( 'itmms_popup_dismissed_' + id, 'yes' );
					}
				} catch ( err ) {}
			} );
		} );
	}

	function initNoticeTickers( root ) {
		( root || document ).querySelectorAll( '.itmms-public-announcements--ticker' ).forEach( function ( ticker ) {
			var track = ticker.querySelector( '.itmms-public-announcements__ticker-track' );
			if ( ! track || track.getAttribute( 'data-itmms-ticker-ready' ) ) {
				return;
			}
			track.setAttribute( 'data-itmms-ticker-ready', '1' );

			// Keep a steady readable pace (~40px/sec) based on content width.
			var loopWidth = Math.max( track.scrollWidth / 2, track.offsetWidth || 0 );
			var pxPerSecond = 40;
			var duration = Math.max( 55, Math.round( loopWidth / pxPerSecond ) );
			track.style.animationDuration = duration + 's';
		} );
	}

	function shareText( text, button, successLabel ) {
		if ( navigator.share ) {
			navigator.share( { text: text } ).catch( function () {} );
			return;
		}

		if ( navigator.clipboard && text ) {
			navigator.clipboard.writeText( text ).then( function () {
				if ( button && successLabel ) {
					var original = button.innerText;
					button.innerText = successLabel;
					setTimeout( function () {
						button.innerText = original;
					}, 1800 );
				}
			} );
		}
	}

	function setAskImamMessage( form, html ) {
		var wrap = form.closest( '.itmms-public-qa' );
		if ( ! wrap ) {
			return null;
		}

		var oldMessage = wrap.querySelector( '.itmms-public-qa__message' );
		if ( oldMessage ) {
			oldMessage.remove();
		}

		var template = document.createElement( 'template' );
		template.innerHTML = html || '';
		var message = template.content.firstElementChild;
		if ( ! message ) {
			return null;
		}

		wrap.insertBefore( message, form );
		message.setAttribute( 'tabindex', '-1' );
		message.focus( { preventScroll: true } );
		message.scrollIntoView( { behavior: 'smooth', block: 'center' } );
		return message;
	}

	function setAskImamLoading( form, isLoading ) {
		var button = form.querySelector( '.itmms-public-qa__submit' );
		if ( ! button ) {
			return;
		}

		if ( isLoading ) {
			button.setAttribute( 'data-itmms-original-label', button.textContent );
			button.textContent = button.getAttribute( 'data-itmms-loading-label' ) || 'Submitting...';
			button.disabled = true;
			form.setAttribute( 'aria-busy', 'true' );
		} else {
			button.textContent = button.getAttribute( 'data-itmms-original-label' ) || button.textContent;
			button.disabled = false;
			form.removeAttribute( 'aria-busy' );
		}
	}

	function syncFilterResetVisibility( form ) {
		if ( ! form ) {
			return;
		}
		var resetBtn = form.querySelector( '[data-itmms-filter-reset]' );
		if ( ! resetBtn ) {
			return;
		}
		var hasValue = false;
		form.querySelectorAll( 'input[type="search"], input[type="text"], select' ).forEach( function ( field ) {
			if ( field.value && String( field.value ).trim() !== '' ) {
				hasValue = true;
			}
		} );
		resetBtn.hidden = ! hasValue;
	}

	function submitAjaxFilterForm( form ) {
		var containerSelector = '.itmms-public-archive, .itmms-public-minbar, .itmms-public-qa';
		var widget = form.closest( containerSelector );
		if ( ! widget || widget.classList.contains( 'is-loading' ) ) {
			return;
		}

		var allWidgets = Array.prototype.slice.call( document.querySelectorAll( containerSelector ) );
		var widgetIndex = allWidgets.indexOf( widget );

		var url = new URL( form.getAttribute( 'action' ) || window.location.href, window.location.href );
		var formData = new FormData( form );

		Array.prototype.slice.call( form.elements ).forEach( function ( el ) {
			if ( el.name ) {
				url.searchParams.delete( el.name );
			}
		} );

		formData.forEach( function ( value, key ) {
			var strVal = String( value ).trim();
			if ( strVal !== '' ) {
				url.searchParams.set( key, strVal );
			}
		} );

		widget.classList.add( 'is-loading' );
		widget.setAttribute( 'aria-busy', 'true' );
		syncFilterResetVisibility( form );

		fetch( url.toString(), {
			credentials: 'same-origin',
			headers: { 'X-Requested-With': 'XMLHttpRequest' }
		} )
			.then( function ( response ) {
				if ( ! response.ok ) {
					throw new Error( 'Filter request failed.' );
				}
				return response.text();
			} )
			.then( function ( html ) {
				var parser = new window.DOMParser();
				var doc = parser.parseFromString( html, 'text/html' );
				var docWidgets = Array.prototype.slice.call( doc.querySelectorAll( containerSelector ) );
				var replacement = ( widgetIndex >= 0 && docWidgets[ widgetIndex ] ) ? docWidgets[ widgetIndex ] : docWidgets[ 0 ];
				if ( ! replacement ) {
					throw new Error( 'Matching widget not found.' );
				}

				widget.replaceWith( replacement );
				if ( window.history && window.history.replaceState ) {
					window.history.replaceState( {}, '', url.toString() );
				}
				if ( typeof window.itmmsPublicRefresh === 'function' ) {
					window.itmmsPublicRefresh( replacement );
				}
			} )
			.catch( function () {
				window.location.href = url.toString();
			} );
	}

	document.addEventListener( 'change', function ( event ) {
		var surahSelect = event.target.closest( '[data-itmms-quran-surah]' );
		if ( surahSelect ) {
			var player = surahSelect.closest( '.itmms-public-audio-quran' ).querySelector( '[data-itmms-quran-player]' );
			if ( ! player ) {
				return;
			}

			if ( surahSelect.value ) {
				player.src = surahSelect.value;
				player.play();
			} else {
				player.pause();
				player.removeAttribute( 'src' );
			}
			return;
		}

		var filterForm = event.target.closest( 'form[data-itmms-ajax-filter]' );
		if ( filterForm ) {
			syncFilterResetVisibility( filterForm );
			if ( event.target.tagName === 'SELECT' ) {
				submitAjaxFilterForm( filterForm );
			}
		}
	} );

	document.addEventListener( 'input', function ( event ) {
		var filterForm = event.target.closest( 'form[data-itmms-ajax-filter]' );
		if ( filterForm ) {
			syncFilterResetVisibility( filterForm );
		}

		var namesSearch = event.target.closest( '[data-itmms-names-search]' );
		if ( namesSearch ) {
			var namesWidget = namesSearch.closest( '.itmms-public-names' );
			if ( ! namesWidget ) {
				return;
			}
			var query = String( namesSearch.value || '' ).trim().toLowerCase();
			var isExpanded = namesWidget.classList.contains( 'is-expanded' );
			var footer = namesWidget.querySelector( '.itmms-public-names__footer' );

			namesWidget.querySelectorAll( '.itmms-public-names__card' ).forEach( function ( card ) {
				var text = ( card.getAttribute( 'data-itmms-name-search-text' ) || card.textContent || '' ).toLowerCase();
				var isExtra = card.getAttribute( 'data-itmms-name-extra' ) === '1';

				if ( query !== '' ) {
					card.hidden = text.indexOf( query ) === -1;
				} else {
					card.hidden = isExtra && ! isExpanded;
				}
			} );

			if ( footer ) {
				footer.hidden = query !== '';
			}
		}
	} );

	document.addEventListener( 'submit', function ( event ) {
		var ajaxFilterForm = event.target.closest( 'form[data-itmms-ajax-filter]' );
		if ( ajaxFilterForm ) {
			event.preventDefault();
			submitAjaxFilterForm( ajaxFilterForm );
			return;
		}

		var form = event.target.closest( '.itmms-public-qa__form' );
		if ( ! form || ! window.itmmsPublicAskImam || ! window.itmmsPublicAskImam.ajaxUrl ) {
			return;
		}

		event.preventDefault();
		if ( form.getAttribute( 'aria-busy' ) === 'true' ) {
			return;
		}

		var data = new FormData( form );
		data.append( 'action', 'itmms_ask_imam_submit' );
		setAskImamLoading( form, true );

		fetch( window.itmmsPublicAskImam.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: data
		} )
			.then( function ( response ) {
				return response.json().then( function ( json ) {
					if ( ! response.ok || ! json.success ) {
						throw json;
					}
					return json;
				} );
			} )
			.then( function ( json ) {
				var messageHtml = json.data && json.data.message_html ? json.data.message_html : '';
				setAskImamMessage( form, messageHtml );
				form.reset();
				form.hidden = true;
			} )
			.catch( function ( json ) {
				var messageHtml = json && json.data && json.data.message_html ? json.data.message_html : '';
				setAskImamMessage( form, messageHtml || '<div class="itmms-public-qa__message is-error"><strong class="itmms-public-qa__message-title">Please check the form</strong><span class="itmms-public-qa__message-text">Could not submit the question. Please try again.</span></div>' );
				setAskImamLoading( form, false );
			} );
	} );

	document.addEventListener( 'click', function ( event ) {
		var filterResetBtn = event.target.closest( '[data-itmms-filter-reset]' );
		if ( filterResetBtn ) {
			var filterForm = filterResetBtn.closest( 'form[data-itmms-ajax-filter]' );
			if ( filterForm ) {
				event.preventDefault();
				filterForm.querySelectorAll( 'input[type="search"], input[type="text"], select' ).forEach( function ( field ) {
					field.value = '';
				} );
				filterResetBtn.hidden = true;
				submitAjaxFilterForm( filterForm );
			}
			return;
		}

		var namesToggle = event.target.closest( '[data-itmms-names-toggle]' );
		if ( namesToggle ) {
			var namesWidget = namesToggle.closest( '.itmms-public-names' );
			if ( namesWidget ) {
				var expanded = namesWidget.classList.toggle( 'is-expanded' );
				namesToggle.setAttribute( 'aria-expanded', expanded ? 'true' : 'false' );
				namesToggle.textContent = expanded
					? ( namesToggle.getAttribute( 'data-collapse-label' ) || namesToggle.textContent )
					: ( namesToggle.getAttribute( 'data-expand-label' ) || namesToggle.textContent );
				namesWidget.querySelectorAll( '.itmms-public-names__card[data-itmms-name-extra="1"]' ).forEach( function ( card ) {
					card.hidden = ! expanded;
				} );
			}
			return;
		}

		var duaFilterBtn = event.target.closest( '[data-itmms-dua-filter]' );
		if ( duaFilterBtn ) {
			var duasWidget = duaFilterBtn.closest( '.itmms-public-duas' );
			if ( duasWidget ) {
				var selectedCat = duaFilterBtn.getAttribute( 'data-itmms-dua-filter' ) || 'all';
				duasWidget.querySelectorAll( '[data-itmms-dua-filter]' ).forEach( function ( btn ) {
					var active = btn === duaFilterBtn;
					btn.classList.toggle( 'is-active', active );
					btn.setAttribute( 'aria-selected', active ? 'true' : 'false' );
				} );
				duasWidget.querySelectorAll( '.itmms-public-duas__item' ).forEach( function ( item ) {
					if ( selectedCat === 'all' ) {
						item.hidden = false;
						return;
					}
					var cats = ( item.getAttribute( 'data-itmms-dua-categories' ) || '' ).split( /\s+/ );
					item.hidden = cats.indexOf( selectedCat ) === -1;
				} );
			}
			return;
		}

		var askAgainLink = event.target.closest( '.itmms-public-qa__message-link' );
		if ( askAgainLink ) {
			var qaWrap = askAgainLink.closest( '.itmms-public-qa' );
			var qaForm = qaWrap ? qaWrap.querySelector( '.itmms-public-qa__form' ) : null;
			if ( qaForm && qaForm.hidden ) {
				event.preventDefault();
				var qaMessage = qaWrap.querySelector( '.itmms-public-qa__message' );
				if ( qaMessage ) {
					qaMessage.remove();
				}
				qaForm.hidden = false;
				var questionField = qaForm.querySelector( '[name="itmms_qa_question"]' );
				if ( questionField ) {
					questionField.focus();
				}
			}
			return;
		}

		var popupClose = event.target.closest( '[data-itmms-popup-close]' );
		if ( popupClose ) {
			var popupId = popupClose.getAttribute( 'data-itmms-popup-close' );
			var popup = document.getElementById( 'itmms-popup-' + popupId );
			if ( popup && popup.classList.contains( 'is-editor-preview' ) ) {
				return;
			}
			if ( popup ) {
				popup.style.display = 'none';
			}
			try {
				window.sessionStorage.setItem( 'itmms_popup_dismissed_' + popupId, 'yes' );
			} catch ( e ) {}
			return;
		}

		var countButton = event.target.closest( '[data-itmms-dua-count]' );
		if ( countButton ) {
			var countKey = countButton.getAttribute( 'data-itmms-dua-count' );
			var countItem = countButton.closest( '.itmms-public-duas__item' );
			var countOutput = countButton.querySelector( '[data-itmms-dua-count-value]' );
			var nextCount = getDuaCount( countKey ) + 1;
			var targetCount = countItem ? Number( countItem.getAttribute( 'data-itmms-dua-target' ) || '0' ) : 0;
			setDuaCount( countKey, nextCount );
			if ( countOutput ) {
				countOutput.textContent = formatDuaCount( nextCount, duaWidgetLanguage( countButton ) );
			}
			syncDuaCompleteState( countItem, nextCount );
			if ( typeof navigator !== 'undefined' && typeof navigator.vibrate === 'function' ) {
				try {
					if ( targetCount > 0 && nextCount === targetCount ) {
						navigator.vibrate( [ 30, 50, 40 ] );
					} else {
						navigator.vibrate( 12 );
					}
				} catch ( e ) {}
			}
			return;
		}

		var resetButton = event.target.closest( '[data-itmms-dua-reset]' );
		if ( resetButton ) {
			var resetKey = resetButton.getAttribute( 'data-itmms-dua-reset' );
			var resetItem = resetButton.closest( '.itmms-public-duas__item' );
			var resetOutput = resetItem ? resetItem.querySelector( '[data-itmms-dua-count-value]' ) : null;
			removeDuaCount( resetKey );
			if ( resetOutput ) {
				resetOutput.textContent = formatDuaCount( 0, duaWidgetLanguage( resetButton ) );
			}
			syncDuaCompleteState( resetItem, 0 );
			return;
		}

		var audioButton = event.target.closest( '[data-itmms-dua-audio]' );
		if ( audioButton ) {
			toggleDuaAudio( audioButton );
			return;
		}

		var shareButton = event.target.closest( '[data-itmms-dua-share]' );
		if ( shareButton ) {
			var shareItem = shareButton.closest( '.itmms-public-duas__item' );
			var shareRoot = shareButton.closest( '.itmms-public-duas' );
			var duaShareText = shareItem ? shareItem.getAttribute( 'data-itmms-dua-text' ) : '';
			var shareSuccess = shareButton.getAttribute( 'data-itmms-share-success' )
				|| ( shareRoot ? shareRoot.getAttribute( 'data-itmms-share-success' ) : '' )
				|| '';
			shareText( duaShareText, shareButton, shareSuccess );
			return;
		}

		var educationShareButton = event.target.closest( '[data-itmms-education-share]' );
		if ( educationShareButton ) {
			shareText(
				educationShareButton.getAttribute( 'data-itmms-share-text' ) || '',
				educationShareButton,
				educationShareButton.getAttribute( 'data-itmms-share-success' ) || ''
			);
			return;
		}

		var calToggle = event.target.closest( '[data-itmms-cal-toggle]' );
		if ( calToggle ) {
			event.preventDefault();
			var calWrap = calToggle.closest( '.itmms-public-events__cal' );
			var calMenu = calWrap ? calWrap.querySelector( '.itmms-public-events__cal-menu' ) : null;
			var willOpen = calMenu && calMenu.hasAttribute( 'hidden' );
			document.querySelectorAll( '.itmms-public-events__cal-menu' ).forEach( function ( menu ) {
				menu.setAttribute( 'hidden', '' );
			} );
			document.querySelectorAll( '[data-itmms-cal-toggle]' ).forEach( function ( btn ) {
				btn.setAttribute( 'aria-expanded', 'false' );
			} );
			if ( willOpen && calMenu ) {
				calMenu.removeAttribute( 'hidden' );
				calToggle.setAttribute( 'aria-expanded', 'true' );
			}
			return;
		}

		if ( ! event.target.closest( '.itmms-public-events__cal' ) ) {
			document.querySelectorAll( '.itmms-public-events__cal-menu' ).forEach( function ( menu ) {
				menu.setAttribute( 'hidden', '' );
			} );
			document.querySelectorAll( '[data-itmms-cal-toggle]' ).forEach( function ( btn ) {
				btn.setAttribute( 'aria-expanded', 'false' );
			} );
		}
	} );

	document.addEventListener( 'keydown', function ( event ) {
		if ( event.key !== 'Escape' ) {
			return;
		}
		document.querySelectorAll( '.itmms-public-events__cal-menu' ).forEach( function ( menu ) {
			menu.setAttribute( 'hidden', '' );
		} );
		document.querySelectorAll( '[data-itmms-cal-toggle]' ).forEach( function ( btn ) {
			btn.setAttribute( 'aria-expanded', 'false' );
		} );
	} );

	// Initialize on page load and after Elementor / Gutenberg dynamic renders
	window.itmmsPublicRefresh = function ( root ) {
		var scope = ( root && root.querySelectorAll ) ? root : document;
		initPrayerCountdowns( scope );
		initQiblaCompass( scope );
		initDuasAzkar( scope );
		initAnnouncementPopups( scope );
		initNoticeTickers( scope );
	};

	window.itmmsShareVerse = function ( text, btn, successLabel ) {
		shareText( text, btn, successLabel );
	};

	function bindEditorLiveRefresh() {
		if ( window.jQuery ) {
			window.jQuery( window ).on( 'elementor/frontend/init', function () {
				if ( window.elementorFrontend && window.elementorFrontend.hooks ) {
					window.elementorFrontend.hooks.addAction( 'frontend/element_ready/global', function ( $scope ) {
						window.itmmsPublicRefresh( $scope && $scope[0] ? $scope[0] : document );
					} );
				}
			} );
		}

		if ( typeof window.MutationObserver === 'function' && document.body ) {
			var refreshTimer = null;
			var observer = new window.MutationObserver( function ( mutations ) {
				var shouldRefresh = false;
				for ( var i = 0; i < mutations.length; i++ ) {
					if ( mutations[ i ].addedNodes && mutations[ i ].addedNodes.length > 0 ) {
						shouldRefresh = true;
						break;
					}
				}
				if ( shouldRefresh ) {
					window.clearTimeout( refreshTimer );
					refreshTimer = window.setTimeout( function () {
						window.itmmsPublicRefresh( document );
					}, 120 );
				}
			} );
			observer.observe( document.body, { childList: true, subtree: true } );
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', function () {
			window.itmmsPublicRefresh( document );
			bindEditorLiveRefresh();
		} );
	} else {
		window.itmmsPublicRefresh( document );
		bindEditorLiveRefresh();
	}
} )();
