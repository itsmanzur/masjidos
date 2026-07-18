/**
 * MasjidOS public widgets.
 */
( function () {
	'use strict';

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

	function initAnnouncementPopups( root ) {
		( root || document ).querySelectorAll( '[data-itmms-popup-id]' ).forEach( function ( popup ) {
			var id = popup.getAttribute( 'data-itmms-popup-id' );
			try {
				if ( id && window.sessionStorage.getItem( 'itmms_popup_dismissed_' + id ) === 'yes' ) {
					popup.style.display = 'none';
					return;
				}
			} catch ( e ) {}

			if ( popup.getAttribute( 'data-itmms-popup-ready' ) === '1' ) {
				return;
			}
			popup.setAttribute( 'data-itmms-popup-ready', '1' );

			var closeBtn = popup.querySelector( '[data-itmms-popup-close]' );
			if ( closeBtn ) {
				window.setTimeout( function () {
					closeBtn.focus();
				}, 0 );
			}

			popup.addEventListener( 'keydown', function ( event ) {
				if ( event.key !== 'Escape' ) {
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

	document.addEventListener( 'change', function ( event ) {
		var surahSelect = event.target.closest( '[data-itmms-quran-surah]' );
		if ( ! surahSelect ) {
			return;
		}

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
	} );

	document.addEventListener( 'click', function ( event ) {
		var popupClose = event.target.closest( '[data-itmms-popup-close]' );
		if ( popupClose ) {
			var popupId = popupClose.getAttribute( 'data-itmms-popup-close' );
			var popup = document.getElementById( 'itmms-popup-' + popupId );
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
			setDuaCount( countKey, nextCount );
			if ( countOutput ) {
				countOutput.textContent = formatDuaCount( nextCount, duaWidgetLanguage( countButton ) );
			}
			syncDuaCompleteState( countItem, nextCount );
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
			var audioUrl = audioButton.getAttribute( 'data-itmms-dua-audio' );
			if ( audioUrl ) {
				new window.Audio( audioUrl ).play();
			}
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

	// Initialize on page load
	window.itmmsPublicRefresh = function ( root ) {
		initPrayerCountdowns( root || document );
		initQiblaCompass( root || document );
		initDuasAzkar( root || document );
		initAnnouncementPopups( root || document );
		initNoticeTickers( root || document );
	};

	window.itmmsShareVerse = function ( text, btn, successLabel ) {
		shareText( text, btn, successLabel );
	};

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', function () {
			window.itmmsPublicRefresh( document );
		} );
	} else {
		window.itmmsPublicRefresh( document );
	}
} )();
