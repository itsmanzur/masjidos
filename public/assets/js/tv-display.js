/**
 * Standalone Mosque TV Display Script.
 */
( function () {
	'use strict';

	var rawData = document.getElementById( 'itmms-tv-data' );
	if ( ! rawData ) {
		return;
	}

	var data;
	try {
		data = JSON.parse( rawData.textContent );
	} catch ( e ) {
		return;
	}

	var prayers = data.prayers || [];
	var labels = data.labels || {};
	var dim = data.dim || {};
	var clockFormat = data.clock === '12h' ? '12h' : '24h';
	var alertMinutes = Math.max( 1, Math.min( 30, parseInt( data.alertMinutes, 10 ) || 10 ) );
	var quietConfig = data.quiet || {};
	var quietEnabled = quietConfig.enabled !== false;
	var quietMinutes = Math.max( 5, Math.min( 45, parseInt( quietConfig.minutes, 10 ) || 15 ) );
	var quietActive = false;
	var forceBoardSlide = null;

	function pad2( num ) {
		return String( num ).padStart( 2, '0' );
	}

	function formatClock( now ) {
		var hours = now.getHours();
		var minutes = now.getMinutes();
		var seconds = now.getSeconds();

		if ( clockFormat === '12h' ) {
			var suffix = hours >= 12 ? 'PM' : 'AM';
			var hour12 = hours % 12;
			if ( hour12 === 0 ) {
				hour12 = 12;
			}
			return pad2( hour12 ) + ':' + pad2( minutes ) + ':' + pad2( seconds ) + ' ' + suffix;
		}

		return pad2( hours ) + ':' + pad2( minutes ) + ':' + pad2( seconds );
	}

	function scheduleSecondAligned( callback ) {
		callback();
		var delay = 1000 - ( Date.now() % 1000 );
		setTimeout( function () {
			callback();
			setInterval( callback, 1000 );
		}, delay );
	}

	function updateClock() {
		var clock = document.getElementById( 'itmms-tv-clock' );
		if ( clock ) {
			clock.textContent = formatClock( new Date() );
		}
	}

	function parseTime( rawDateString, timeString ) {
		if ( ! timeString ) {
			return null;
		}

		var baseDate = new Date( rawDateString );
		var match = timeString.match( /(\d+):(\d+)\s*(AM|PM)?/i );
		if ( ! match ) {
			return null;
		}

		var hours = parseInt( match[1], 10 );
		var minutes = parseInt( match[2], 10 );
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

		baseDate.setHours( hours, minutes, 0, 0 );
		return baseDate;
	}

	function tickPrayerState() {
		var now = new Date();
		var currentPrayerKey = null;
		var nextEvent = null;

		var mappedPrayers = prayers.map( function ( prayer ) {
			var azanDate = new Date( prayer.raw );
			var isExtra = prayer.kind === 'extra' || prayer.key === 'ishraq' || prayer.key === 'zawal' || prayer.key === 'sunrise';
			var iqamahDate = isExtra ? null : parseTime( prayer.raw, prayer.iqamah );
			var hasSeparateIqamah = !!( iqamahDate && iqamahDate.getTime() !== azanDate.getTime() );
			return {
				key: prayer.key,
				name: prayer.name,
				kind: prayer.kind || ( isExtra ? 'extra' : 'fard' ),
				azan: azanDate,
				iqamah: iqamahDate || azanDate,
				hasSeparateIqamah: hasSeparateIqamah,
				isExtra: isExtra
			};
		} );

		var fardPrayers = mappedPrayers.filter( function ( p ) {
			return ! p.isExtra;
		} );

		for ( var i = 0; i < fardPrayers.length; i++ ) {
			var curr = fardPrayers[i];
			var nextIndex = ( i + 1 ) % fardPrayers.length;
			var next = fardPrayers[nextIndex];
			var nextAzan = new Date( next.azan );
			if ( nextIndex === 0 ) {
				nextAzan.setDate( nextAzan.getDate() + 1 );
			}
			if ( now >= curr.azan && now < nextAzan ) {
				currentPrayerKey = curr.key;
			}
		}

		if ( ! currentPrayerKey && fardPrayers.length ) {
			currentPrayerKey = fardPrayers[fardPrayers.length - 1].key;
		}

		var events = [];
		fardPrayers.forEach( function ( p ) {
			events.push( { key: p.key, name: p.name, type: 'azan', time: new Date( p.azan ) } );

			if ( p.hasSeparateIqamah ) {
				events.push( { key: p.key, name: p.name, type: 'jamaat', time: new Date( p.iqamah ) } );
			}

			var azanTomorrow = new Date( p.azan );
			azanTomorrow.setDate( azanTomorrow.getDate() + 1 );
			events.push( { key: p.key, name: p.name, type: 'azan', time: azanTomorrow } );

			if ( p.hasSeparateIqamah ) {
				var iqamahTomorrow = new Date( p.iqamah );
				iqamahTomorrow.setDate( iqamahTomorrow.getDate() + 1 );
				events.push( { key: p.key, name: p.name, type: 'jamaat', time: iqamahTomorrow } );
			}
		} );

		events.sort( function ( a, b ) {
			return a.time - b.time;
		} );

		for ( var j = 0; j < events.length; j++ ) {
			if ( events[j].time > now ) {
				nextEvent = events[j];
				break;
			}
		}

		var nextIqamahKey = nextEvent && nextEvent.type === 'jamaat' ? nextEvent.key : null;
		var nextAzanKey = nextEvent && nextEvent.type === 'azan' ? nextEvent.key : null;

		document.querySelectorAll( '.itmms-tv__table-row' ).forEach( function ( row ) {
			var rowKey = row.getAttribute( 'data-prayer-row' );
			row.classList.toggle( 'is-current', rowKey === currentPrayerKey );
			row.classList.toggle( 'is-next-iqamah', rowKey === nextIqamahKey );
			row.classList.toggle( 'is-next-azan', rowKey === nextAzanKey && rowKey !== currentPrayerKey );
		} );

		updateQuietMode( now, fardPrayers );

		var nextNameEl = document.getElementById( 'itmms-tv-next-name' );
		var labelEl = document.getElementById( 'itmms-tv-countdown-label' );
		var countdownEl = document.getElementById( 'itmms-tv-countdown' );
		var countdownBox = document.getElementById( 'itmms-tv-countdown-box' );
		var alertBadge = document.getElementById( 'itmms-tv-alert-badge' );

		if ( nextEvent && nextNameEl && labelEl && countdownEl ) {
			if ( quietActive ) {
				nextNameEl.textContent = quietActive.name || nextEvent.name;
				labelEl.textContent = labels.quiet_message || 'Prayer in progress';
				var quietLeft = Math.max( 0, Math.floor( ( quietActive.endsAt - now.getTime() ) / 1000 ) );
				countdownEl.textContent = pad2( Math.floor( quietLeft / 3600 ) ) + ':' +
					pad2( Math.floor( ( quietLeft % 3600 ) / 60 ) ) + ':' +
					pad2( quietLeft % 60 );

				if ( countdownBox ) {
					countdownBox.classList.remove( 'is-alert', 'is-alert-critical', 'is-target-jamaat', 'is-target-azan' );
					countdownBox.classList.add( 'is-quiet' );
				}

				document.body.classList.remove( 'itmms-tv--alert', 'itmms-tv--alert-critical' );
				if ( alertBadge ) {
					alertBadge.hidden = true;
				}
			} else {
				nextNameEl.textContent = nextEvent.name;
				labelEl.textContent = labels[nextEvent.type] || 'Countdown';

				var diffMs = Math.max( 0, nextEvent.time.getTime() - now.getTime() );
				var diffSeconds = Math.floor( diffMs / 1000 );
				countdownEl.textContent = pad2( Math.floor( diffSeconds / 3600 ) ) + ':' +
					pad2( Math.floor( ( diffSeconds % 3600 ) / 60 ) ) + ':' +
					pad2( diffSeconds % 60 );

				var minsLeft = diffSeconds / 60;
				var inAlert = minsLeft > 0 && minsLeft <= alertMinutes;
				var critical = minsLeft > 0 && minsLeft <= 2;

				if ( countdownBox ) {
					countdownBox.classList.remove( 'is-quiet' );
					countdownBox.classList.toggle( 'is-alert', inAlert );
					countdownBox.classList.toggle( 'is-alert-critical', critical );
					countdownBox.classList.toggle( 'is-target-jamaat', nextEvent.type === 'jamaat' );
					countdownBox.classList.toggle( 'is-target-azan', nextEvent.type === 'azan' );
				}

				document.body.classList.toggle( 'itmms-tv--alert', inAlert );
				document.body.classList.toggle( 'itmms-tv--alert-critical', critical );

				if ( alertBadge ) {
					if ( inAlert ) {
						alertBadge.hidden = false;
						alertBadge.textContent = labels.alert || 'Almost time';
					} else {
						alertBadge.hidden = true;
					}
				}
			}
		}
	}

	function updateQuietMode( now, fardPrayers ) {
		var quietEl = document.getElementById( 'itmms-tv-quiet' );
		var quietNameEl = document.getElementById( 'itmms-tv-quiet-name' );
		var active = null;

		if ( quietEnabled && fardPrayers && fardPrayers.length ) {
			var windowMs = quietMinutes * 60 * 1000;
			var candidates = [];

			fardPrayers.forEach( function ( p ) {
				if ( p.key === 'sunrise' ) {
					return;
				}
				candidates.push( {
					key: p.key,
					name: labels[p.key] || p.name,
					startsAt: p.iqamah.getTime()
				} );

				var tomorrow = new Date( p.iqamah );
				tomorrow.setDate( tomorrow.getDate() + 1 );
				candidates.push( {
					key: p.key,
					name: labels[p.key] || p.name,
					startsAt: tomorrow.getTime()
				} );
			} );

			var nowMs = now.getTime();
			candidates.forEach( function ( item ) {
				var endsAt = item.startsAt + windowMs;
				if ( nowMs >= item.startsAt && nowMs < endsAt ) {
					if ( ! active || item.startsAt > active.startsAt ) {
						active = {
							key: item.key,
							name: item.name,
							startsAt: item.startsAt,
							endsAt: endsAt
						};
					}
				}
			} );
		}

		var wasQuiet = quietActive;
		quietActive = active;

		document.body.classList.toggle( 'itmms-tv--quiet', !!active );

		if ( quietEl ) {
			if ( active ) {
				quietEl.hidden = false;
				if ( quietNameEl ) {
					quietNameEl.textContent = active.name;
				}
			} else {
				quietEl.hidden = true;
			}
		}

		if ( active && ( ! wasQuiet || wasQuiet.key !== active.key ) && typeof forceBoardSlide === 'function' ) {
			forceBoardSlide();
		}
	}

	function initTicker() {
		var ticker = document.getElementById( 'itmms-tv-ticker' );
		var track = document.getElementById( 'itmms-tv-ticker-track' );
		if ( ! ticker || ! track ) {
			return;
		}

		var items = track.querySelectorAll( '.itmms-tv__ticker-item' );
		if ( ! items.length ) {
			return;
		}

		track.innerHTML = track.innerHTML + track.innerHTML;

		var speedSeconds = parseInt( ticker.getAttribute( 'data-speed' ), 10 ) || 7;
		var pxPerSec = Math.max( 30, Math.min( 90, 120 - ( speedSeconds * 3 ) ) );

		function applyDuration() {
			var halfWidth = track.scrollWidth / 2;
			if ( halfWidth < 10 ) {
				return;
			}
			track.style.animationDuration = Math.max( 8, halfWidth / pxPerSec ) + 's';
			track.classList.add( 'is-scrolling' );
		}

		window.requestAnimationFrame( function () {
			window.requestAnimationFrame( applyDuration );
		} );
	}

	function initSlides() {
		var stage = document.getElementById( 'itmms-tv-stage' );
		if ( ! stage || stage.getAttribute( 'data-slides' ) !== '1' ) {
			return;
		}

		var slides = Array.prototype.slice.call( stage.querySelectorAll( '.itmms-tv__slide' ) );
		if ( slides.length < 2 ) {
			return;
		}

		var interval = parseInt( stage.getAttribute( 'data-interval' ), 10 ) || 12;
		var dotsWrap = document.getElementById( 'itmms-tv-slide-dots' );
		var index = 0;

		if ( dotsWrap ) {
			slides.forEach( function ( _, i ) {
				var dot = document.createElement( 'span' );
				dot.className = 'itmms-tv__slide-dot' + ( 0 === i ? ' is-active' : '' );
				dotsWrap.appendChild( dot );
			} );
		}

		function show( nextIndex ) {
			index = nextIndex;
			slides.forEach( function ( slide, i ) {
				slide.classList.toggle( 'is-active', i === index );
			} );
			if ( dotsWrap ) {
				Array.prototype.forEach.call( dotsWrap.children, function ( dot, i ) {
					dot.classList.toggle( 'is-active', i === index );
				} );
			}
		}

		forceBoardSlide = function () {
			show( 0 );
		};

		setInterval( function () {
			if ( document.body.classList.contains( 'itmms-tv--quiet' ) ) {
				if ( index !== 0 ) {
					show( 0 );
				}
				return;
			}
			show( ( index + 1 ) % slides.length );
		}, interval * 1000 );
	}

	function parseHm( value ) {
		var parts = String( value || '' ).split( ':' );
		if ( parts.length < 2 ) {
			return null;
		}
		var hours = parseInt( parts[0], 10 );
		var minutes = parseInt( parts[1], 10 );
		if ( isNaN( hours ) || isNaN( minutes ) ) {
			return null;
		}
		return ( hours * 60 ) + minutes;
	}

	function isInDimWindow( nowMinutes, startMinutes, endMinutes ) {
		if ( startMinutes === endMinutes ) {
			return false;
		}
		if ( startMinutes < endMinutes ) {
			return nowMinutes >= startMinutes && nowMinutes < endMinutes;
		}
		return nowMinutes >= startMinutes || nowMinutes < endMinutes;
	}

	function initOvernightDim() {
		if ( ! dim.enabled ) {
			return;
		}

		var start = parseHm( dim.start || '23:00' );
		var end = parseHm( dim.end || '04:30' );
		if ( null === start || null === end ) {
			return;
		}

		function tickDim() {
			var now = new Date();
			var minutes = ( now.getHours() * 60 ) + now.getMinutes();
			document.body.classList.toggle( 'itmms-tv--dimmed', isInDimWindow( minutes, start, end ) );
		}

		tickDim();
		setInterval( tickDim, 30000 );
	}

	initTicker();
	initSlides();
	initOvernightDim();
	scheduleSecondAligned( updateClock );
	scheduleSecondAligned( tickPrayerState );

	setTimeout( function () {
		window.location.reload();
	}, 15 * 60 * 1000 );

	window.addEventListener( 'online', function () {
		window.location.reload();
	} );
} )();
