/**
 * MasjidOS public widgets.
 */
( function () {
	'use strict';

	function pad2( value ) {
		return String( value ).padStart( 2, '0' );
	}

	function formatCountdown( target ) {
		var seconds = Math.max( 0, Math.floor( ( target.getTime() - Date.now() ) / 1000 ) );
		return pad2( Math.floor( seconds / 3600 ) ) + ':' +
			pad2( Math.floor( ( seconds % 3600 ) / 60 ) ) + ':' +
			pad2( seconds % 60 );
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

		output.textContent = formatCountdown( target );
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

		var widget = cell.closest( '[data-itmms-calendar]' );
		var drawer = widget.querySelector( '#itmms-calendar-mobile-drawer' );
		var titleEl = widget.querySelector( '#itmms-calendar-drawer-title' );
		var listEl = widget.querySelector( '#itmms-calendar-drawer-list' );
		if ( ! drawer || ! titleEl || ! listEl ) {
			return;
		}

		widget.querySelectorAll( '.itmms-public-calendar__cell' ).forEach( function ( c ) {
			c.classList.remove( 'is-selected' );
		} );
		cell.classList.add( 'is-selected' );

		var gDateLabel = cell.getAttribute( 'data-gregorian-date' );
		var hDateLabel = cell.getAttribute( 'data-hijri-date-label' );
		
		var parts = gDateLabel.split( '-' );
		var formattedGDate = new Date( parts[0], parts[1] - 1, parts[2] ).toLocaleDateString( undefined, { day: 'numeric', month: 'long', year: 'numeric' } );

		titleEl.textContent = formattedGDate + ' / ' + hDateLabel;

		var eventItems = cell.querySelectorAll( '.itmms-public-calendar__event-item' );
		var holyLabel = cell.querySelector( '.itmms-public-calendar__holy-label' );
		
		var listHtml = '';
		if ( holyLabel ) {
			listHtml += '<div class="itmms-public-calendar__drawer-item is-holiday">' + holyLabel.innerHTML + '</div>';
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
		drawer.style.display = 'block';
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

	function initDuasAzkar( root ) {
		( root || document ).querySelectorAll( '.itmms-public-duas__item[data-itmms-dua-key]' ).forEach( function ( item ) {
			var key = item.getAttribute( 'data-itmms-dua-key' );
			var output = item.querySelector( '[data-itmms-dua-count-value]' );
			if ( ! key || ! output ) {
				return;
			}

			output.textContent = String( getDuaCount( key ) );
		} );
	}

	document.addEventListener( 'click', function ( event ) {
		var countButton = event.target.closest( '[data-itmms-dua-count]' );
		if ( countButton ) {
			var countKey = countButton.getAttribute( 'data-itmms-dua-count' );
			var countOutput = countButton.querySelector( '[data-itmms-dua-count-value]' );
			var nextCount = getDuaCount( countKey ) + 1;
			setDuaCount( countKey, nextCount );
			if ( countOutput ) {
				countOutput.textContent = String( nextCount );
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
				resetOutput.textContent = '0';
			}
			return;
		}

		var audioButton = event.target.closest( '[data-itmms-dua-audio]' );
		if ( audioButton ) {
			var audioUrl = audioButton.getAttribute( 'data-itmms-dua-audio' );
			if ( audioUrl ) {
				new window.Audio( audioUrl ).play();
			}
			return;
		}

		var shareButton = event.target.closest( '[data-itmms-dua-share]' );
		if ( shareButton ) {
			var shareItem = shareButton.closest( '.itmms-public-duas__item' );
			var shareText = shareItem ? shareItem.getAttribute( 'data-itmms-dua-text' ) : '';
			if ( navigator.share ) {
				navigator.share( { text: shareText } ).catch( function () {} );
			} else if ( navigator.clipboard && shareText ) {
				navigator.clipboard.writeText( shareText );
			}
		}
	} );

	// Initialize on page load
	window.itmmsPublicRefresh = function ( root ) {
		initPrayerCountdowns( root || document );
		initQiblaCompass( root || document );
		initDuasAzkar( root || document );
	};

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', function () {
			window.itmmsPublicRefresh( document );
		} );
	} else {
		window.itmmsPublicRefresh( document );
	}
} )();
