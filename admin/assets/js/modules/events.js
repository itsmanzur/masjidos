/**
 * MasjidOS Admin - Events Module.
 */
( function () {
	'use strict';

	window.itmms = window.itmms || {};
	var __ = window.wp.i18n.__;
	var sprintf = window.wp.i18n.sprintf;

	// Import shared helpers
	var esc = window.itmms.esc;
	var datetimeLocalValue = window.itmms.announcements.datetimeLocalValue;

	// Expose events module
	window.itmms.events = {
		eventsHtml: eventsHtml
	};

	function eventsHtml() {
		var state = window.itmms.state;
		var editing = state.events.filter( function ( event ) {
			return Number( event.id ) === Number( state.editingEvent );
		} )[0] || null;
		var upcomingCount = state.events.filter( function ( event ) { return 'upcoming' === event.status; } ).length;
		var ongoingCount = state.events.filter( function ( event ) { return 'ongoing' === event.status; } ).length;
		var startValue = editing ? datetimeLocalValue( editing.start_time ) : datetimeLocalValue( new Date() );

		return ( editing ? '<div class="itmms-page-toolbar"><button type="button" class="itmms-btn itmms-btn-ghost" data-new-event>' + esc( __( 'New Event', 'masjidos' ) ) + '</button></div>' : '' ) +
			'<div class="itmms-announcement-summary"><span><b>' + esc( upcomingCount ) + '</b> ' + esc( __( 'Upcoming', 'masjidos' ) ) + '</span><span><b>' + esc( ongoingCount ) + '</b> ' + esc( __( 'Ongoing', 'masjidos' ) ) + '</span><span><b>' + esc( state.events.length ) + '</b> ' + esc( __( 'Total', 'masjidos' ) ) + '</span></div>' +
			'<div class="itmms-announcement-layout">' +
				'<form class="itmms-announcement-editor" id="itmms-event-form">' +
					'<div class="itmms-announcement-panel-head"><div><span>' + esc( editing ? __( 'Editing event', 'masjidos' ) : __( 'New event', 'masjidos' ) ) + '</span><h3>' + esc( editing ? editing.title : __( 'Add an event', 'masjidos' ) ) + '</h3></div></div>' +
					'<label class="itmms-field"><span>' + esc( __( 'Event Title', 'masjidos' ) ) + '</span><input type="text" name="title" maxlength="255" required value="' + esc( editing ? editing.title : '' ) + '" placeholder="' + esc( __( 'Example: Eid-ul-Fitr Prayer Gathering', 'masjidos' ) ) + '"></label>' +
					'<label class="itmms-field"><span>' + esc( __( 'Description', 'masjidos' ) ) + '</span><textarea name="description" rows="5" placeholder="' + esc( __( 'Add event details, schedule, or prerequisites...', 'masjidos' ) ) + '">' + esc( editing ? editing.description : '' ) + '</textarea></label>' +
					'<div class="itmms-announcement-form-grid">' +
						'<label class="itmms-field"><span>' + esc( __( 'Starts', 'masjidos' ) ) + '</span><input type="datetime-local" name="start_time" required value="' + esc( startValue ) + '"><small>' + esc( sprintf( __( 'Uses %s.', 'masjidos' ), state.settings.timezone || __( 'site timezone', 'masjidos' ) ) ) + '</small></label>' +
						'<label class="itmms-field"><span>' + esc( __( 'Ends', 'masjidos' ) ) + '</span><input type="datetime-local" name="end_time" value="' + esc( editing ? datetimeLocalValue( editing.end_time ) : '' ) + '"><small>' + esc( __( 'Optional.', 'masjidos' ) ) + '</small></label>' +
						'<label class="itmms-field"><span>' + esc( __( 'Location', 'masjidos' ) ) + '</span><input type="text" name="location" value="' + esc( editing ? editing.location : '' ) + '" placeholder="' + esc( __( 'Example: Main Prayer Hall', 'masjidos' ) ) + '"></label>' +
					'</div>' +
					'<div class="itmms-announcement-actions"><button class="itmms-btn itmms-btn-primary" type="submit">' + esc( editing ? __( 'Update Event', 'masjidos' ) : __( 'Create Event', 'masjidos' ) ) + '</button>' + ( editing ? '<button class="itmms-btn itmms-btn-ghost" type="button" data-cancel-event>' + esc( __( 'Cancel', 'masjidos' ) ) + '</button>' : '' ) + '<span id="itmms-event-status"></span></div>' +
				'</form>' +
				'<section class="itmms-announcement-list-panel"><div class="itmms-announcement-panel-head"><div><span>' + esc( __( 'Mosque calendar', 'masjidos' ) ) + '</span><h3>' + esc( __( 'Scheduled events', 'masjidos' ) ) + '</h3></div></div>' + eventListHtml() + '</section>' +
			'</div>';
	}

	function eventListHtml() {
		var state = window.itmms.state;
		if ( ! state.events.length ) {
			return '<div class="itmms-announcement-empty">' + esc( __( 'No events registered yet. Create the first one using the editor.', 'masjidos' ) ) + '</div>';
		}

		return '<div class="itmms-announcement-list">' + state.events.map( function ( event ) {
			return '<article class="itmms-announcement-row itmms-announcement-row--general">' +
				'<div class="itmms-announcement-row-top"><span class="itmms-announcement-type">' + esc( event.location || __( 'No Location', 'masjidos' ) ) + '</span><span class="itmms-announcement-status is-' + esc( event.status ) + '">' + esc( eventStatusLabel( event.status ) ) + '</span></div>' +
				'<h4>' + esc( event.title ) + '</h4>' + ( event.description ? '<p>' + esc( event.description ) + '</p>' : '' ) +
				'<div class="itmms-announcement-meta"><span>' + esc( sprintf( __( 'Starts %s', 'masjidos' ), formatEventDate( event.start_time ) ) ) + '</span>' + ( event.end_time ? '<span>' + esc( sprintf( __( 'Ends %s', 'masjidos' ), formatEventDate( event.end_time ) ) ) + '</span>' : '<span>' + esc( __( 'No end time', 'masjidos' ) ) + '</span>' ) + '</div>' +
				'<div class="itmms-announcement-row-actions"><button type="button" class="itmms-link-btn" data-edit-event="' + esc( event.id ) + '">' + esc( __( 'Edit', 'masjidos' ) ) + '</button><button type="button" class="itmms-link-btn is-danger" data-delete-event="' + esc( event.id ) + '">' + esc( __( 'Delete', 'masjidos' ) ) + '</button></div>' +
			'</article>';
		} ).join( '' ) + '</div>';
	}

	function eventStatusLabel( status ) {
		return {
			upcoming: __( 'Upcoming', 'masjidos' ),
			ongoing: __( 'Ongoing', 'masjidos' ),
			past: __( 'Past', 'masjidos' )
		}[ status ] || status;
	}

	function formatEventDate( value ) {
		var data = window.itmms.data;
		if ( ! value ) {
			return '';
		}
		var date = new Date( String( value ).replace( ' ', 'T' ) );
		return isNaN( date.getTime() ) ? value : new Intl.DateTimeFormat( String( data.locale || 'en_US' ).replace( '_', '-' ), { dateStyle: 'medium', timeStyle: 'short' } ).format( date );
	}
} )();
