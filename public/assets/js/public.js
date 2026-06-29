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

	document.querySelectorAll( '.itmms-public-prayer' ).forEach( tick );

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
} )();
