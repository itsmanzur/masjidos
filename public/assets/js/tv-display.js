/**
 * Standalone Mosque TV Display Script.
 */
( function () {
	'use strict';

	// Parse localized data payload
	var rawData = document.getElementById( 'itmms-tv-data' );
	if ( ! rawData ) {
		return;
	}

	var data;
	try {
		data = JSON.parse( rawData.textContent );
	} catch ( e ) {
		console.error( 'Failed to parse TV display data:', e );
		return;
	}

	var prayers = data.prayers || [];
	var labels = data.labels || {};
	var lang = data.lang || 'en';

	// Helper to pad numbers (e.g. 9 -> 09)
	function pad2( num ) {
		return String( num ).padStart( 2, '0' );
	}

	// Format clock time
	function updateClock() {
		var now = new Date();
		var clock = document.getElementById( 'itmms-tv-clock' );
		if ( clock ) {
			clock.textContent = pad2( now.getHours() ) + ':' + pad2( now.getMinutes() ) + ':' + pad2( now.getSeconds() );
		}
	}
	setInterval( updateClock, 1000 );
	updateClock();

	// Parse prayer or Iqamah time relative to a base date
	function parseTime( rawDateString, timeString ) {
		if ( ! timeString ) {
			return null;
		}

		var baseDate = new Date( rawDateString );
		var hours, minutes;

		// Parse formatted time like "5:00 AM" or "13:30" or "05:00 PM"
		var match = timeString.match( /(\d+):(\d+)\s*(AM|PM)?/i );
		if ( match ) {
			hours = parseInt( match[1], 10 );
			minutes = parseInt( match[2], 10 );
			var ampm = match[3];

			if ( ampm ) {
				ampm = ampm.toUpperCase();
				if ( ampm === 'PM' && hours < 12 ) {
					hours += 12;
				}
				if ( ampm === 'AM' && hours === 12 ) {
					hours = 0;
				}
			}
		} else {
			return null;
		}

		baseDate.setHours( hours, minutes, 0, 0 );
		return baseDate;
	}

	// Update active prayer states and next countdown target
	function tickPrayerState() {
		var now = new Date();
		var currentPrayerKey = null;
		var nextPrayer = null;
		var countdownTarget = null;
		var countdownLabel = labels.azan;

		// We will map prayer times and Iqamah times to absolute Date objects
		var mappedPrayers = prayers.map( function ( prayer ) {
			var azanDate = new Date( prayer.raw );
			var iqamahDate = prayer.key === 'sunrise' ? null : parseTime( prayer.raw, prayer.iqamah );
			return {
				key: prayer.key,
				name: prayer.name,
				azan: azanDate,
				iqamah: iqamahDate || azanDate // Fallback to azan if no iqamah
			};
		} );

		// Find the current active prayer (the one whose Azan time has passed but next prayer Azan has not arrived)
		for ( var i = 0; i < mappedPrayers.length; i++ ) {
			var curr = mappedPrayers[i];
			var nextIndex = ( i + 1 ) % mappedPrayers.length;
			var next = mappedPrayers[nextIndex];

			var nextAzan = new Date( next.azan );
			if ( nextIndex === 0 ) {
				// If next is Fajr tomorrow
				nextAzan.setDate( nextAzan.getDate() + 1 );
			}

			if ( now >= curr.azan && now < nextAzan ) {
				currentPrayerKey = curr.key;
			}
		}

		// Fallback if none (e.g. before Fajr)
		if ( ! currentPrayerKey && mappedPrayers.length ) {
			currentPrayerKey = mappedPrayers[mappedPrayers.length - 1].key;
		}

		// Find the next countdown target:
		// We loop to find the first event (Azan or Iqamah) in the future
		var events = [];
		mappedPrayers.forEach( function ( p ) {
			// Today's Azan
			var azanToday = new Date( p.azan );
			events.push( { key: p.key, name: p.name, type: 'azan', time: azanToday } );

			// Today's Iqamah (if different from Azan)
			if ( p.iqamah && p.iqamah.getTime() !== p.azan.getTime() ) {
				var iqamahToday = new Date( p.iqamah );
				events.push( { key: p.key, name: p.name, type: 'jamaat', time: iqamahToday } );
			}

			// Tomorrow's Azan (for overnight wrapping)
			var azanTomorrow = new Date( p.azan );
			azanTomorrow.setDate( azanTomorrow.getDate() + 1 );
			events.push( { key: p.key, name: p.name, type: 'azan', time: azanTomorrow } );

			// Tomorrow's Iqamah
			if ( p.iqamah && p.iqamah.getTime() !== p.azan.getTime() ) {
				var iqamahTomorrow = new Date( p.iqamah );
				iqamahTomorrow.setDate( iqamahTomorrow.getDate() + 1 );
				events.push( { key: p.key, name: p.name, type: 'jamaat', time: iqamahTomorrow } );
			}
		} );

		// Sort events by chronological order
		events.sort( function ( a, b ) {
			return a.time - b.time;
		} );

		// Find the first event in the future
		for ( var j = 0; j < events.length; j++ ) {
			if ( events[j].time > now ) {
				nextPrayer = events[j];
				countdownTarget = events[j].time;
				countdownLabel = labels[events[j].type] || 'Countdown';
				break;
			}
		}

		// Update UI highlight classes
		document.querySelectorAll( '.itmms-tv__table-row' ).forEach( function ( row ) {
			var rowKey = row.getAttribute( 'data-prayer-row' );
			row.classList.toggle( 'is-current', rowKey === currentPrayerKey );
		} );

		// Update Countdown panel
		var nextNameEl = document.getElementById( 'itmms-tv-next-name' );
		var labelEl = document.getElementById( 'itmms-tv-countdown-label' );
		var countdownEl = document.getElementById( 'itmms-tv-countdown' );

		if ( nextPrayer && nextNameEl && labelEl && countdownEl ) {
			nextNameEl.textContent = nextPrayer.name;
			labelEl.textContent = countdownLabel;

			var diffSeconds = Math.max( 0, Math.floor( ( countdownTarget.getTime() - now.getTime() ) / 1000 ) );
			var hours = Math.floor( diffSeconds / 3600 );
			var mins = Math.floor( ( diffSeconds % 3600 ) / 60 );
			var secs = diffSeconds % 60;

			countdownEl.textContent = pad2( hours ) + ':' + pad2( mins ) + ':' + pad2( secs );
		}
	}

	setInterval( tickPrayerState, 1000 );
	tickPrayerState();

	// Ticker banner animation speed setting
	function initTicker() {
		var ticker = document.getElementById( 'itmms-tv-ticker' );
		var track = document.getElementById( 'itmms-tv-ticker-track' );
		if ( ! ticker || ! track ) {
			return;
		}

		var speedSeconds = parseInt( ticker.getAttribute( 'data-speed' ), 10 ) || 7;
		var items = track.querySelectorAll( '.itmms-tv__ticker-item' );
		if ( items.length <= 1 ) {
			return; // No need to slide if there's only 1 default item
		}

		var currentIndex = 0;

		function slideNext() {
			currentIndex = ( currentIndex + 1 ) % items.length;
			var offset = -currentIndex * 100;
			track.style.transform = 'translateY(' + offset + '%)';
		}

		// Apply vertical sliding transition to track
		track.style.transition = 'transform 0.8s cubic-bezier(0.25, 1, 0.5, 1)';
		setInterval( slideNext, speedSeconds * 1000 );
	}

	initTicker();

	// Auto reload page every 15 minutes to fetch new calculations and updates
	setTimeout( function () {
		window.location.reload();
	}, 15 * 60 * 1000 );

	// Auto-reload when internet connection status changes to online
	window.addEventListener( 'online', function () {
		window.location.reload();
	} );
} )();
