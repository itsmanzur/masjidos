/**
 * MasjidOS Admin - Announcements/Notices Module.
 */
( function () {
	'use strict';

	window.itmms = window.itmms || {};
	var __ = window.wp.i18n.__;
	var sprintf = window.wp.i18n.sprintf;

	// Import shared helpers
	var esc = window.itmms.esc;

	// Expose announcements module
	window.itmms.announcements = {
		announcementsHtml: announcementsHtml,
		announcementTypeLabel: announcementTypeLabel,
		datetimeLocalValue: datetimeLocalValue
	};

	function announcementsHtml() {
		var state = window.itmms.state;
		var editing = state.announcements.filter( function ( notice ) {
			return Number( notice.id ) === Number( state.editingAnnouncement );
		} )[0] || null;
		var activeCount = state.announcements.filter( function ( notice ) { return 'active' === notice.status; } ).length;
		var scheduledCount = state.announcements.filter( function ( notice ) { return 'scheduled' === notice.status; } ).length;
		var startValue = editing ? datetimeLocalValue( editing.start_date ) : datetimeLocalValue( new Date() );

		return ( editing ? '<div class="itmms-page-toolbar"><button type="button" class="itmms-btn itmms-btn-ghost" data-new-announcement>' + esc( __( 'New Notice', 'masjidos' ) ) + '</button></div>' : '' ) +
			'<div class="itmms-announcement-summary"><span><b>' + esc( activeCount ) + '</b> ' + esc( __( 'Live now', 'masjidos' ) ) + '</span><span><b>' + esc( scheduledCount ) + '</b> ' + esc( __( 'Scheduled', 'masjidos' ) ) + '</span><span><b>' + esc( state.announcements.length ) + '</b> ' + esc( __( 'Total', 'masjidos' ) ) + '</span></div>' +
			'<div class="itmms-announcement-layout">' +
				'<form class="itmms-announcement-editor" id="itmms-announcement-form">' +
					'<div class="itmms-announcement-panel-head"><div><span>' + esc( editing ? __( 'Editing notice', 'masjidos' ) : __( 'New notice', 'masjidos' ) ) + '</span><h3>' + esc( editing ? editing.title : __( 'Publish an update', 'masjidos' ) ) + '</h3></div></div>' +
					'<label class="itmms-field"><span>' + esc( __( 'Title', 'masjidos' ) ) + '</span><input type="text" name="title" maxlength="255" required value="' + esc( editing ? editing.title : '' ) + '" placeholder="' + esc( __( 'Example: Friday parking update', 'masjidos' ) ) + '"></label>' +
					'<label class="itmms-field"><span>' + esc( __( 'Details', 'masjidos' ) ) + '</span><textarea name="content" rows="5" placeholder="' + esc( __( 'Add the useful details visitors need.', 'masjidos' ) ) + '">' + esc( editing ? editing.content : '' ) + '</textarea></label>' +
					'<div class="itmms-announcement-form-grid">' +
						'<label class="itmms-field"><span>' + esc( __( 'Type', 'masjidos' ) ) + '</span><select name="announcement_type">' + announcementTypeOptions( editing ? editing.announcement_type : 'general' ) + '</select></label>' +
						'<label class="itmms-field"><span>' + esc( __( 'Priority', 'masjidos' ) ) + '</span><input type="number" name="priority" min="0" max="10" value="' + esc( editing ? editing.priority : 0 ) + '"><small>' + esc( __( 'Higher notices appear first.', 'masjidos' ) ) + '</small></label>' +
						'<label class="itmms-field"><span>' + esc( __( 'Starts', 'masjidos' ) ) + '</span><input type="datetime-local" name="start_date" required value="' + esc( startValue ) + '"><small>' + esc( sprintf( __( 'Uses %s.', 'masjidos' ), state.settings.timezone || __( 'site timezone', 'masjidos' ) ) ) + '</small></label>' +
						'<label class="itmms-field"><span>' + esc( __( 'Ends', 'masjidos' ) ) + '</span><input type="datetime-local" name="end_date" value="' + esc( editing ? datetimeLocalValue( editing.end_date ) : '' ) + '"><small>' + esc( __( 'Optional. Uses the same timezone.', 'masjidos' ) ) + '</small></label>' +
					'</div>' +
					'<label class="itmms-check itmms-announcement-publish"><input type="checkbox" name="is_active"' + ( ! editing || editing.is_active ? ' checked' : '' ) + '><span>' + esc( __( 'Published', 'masjidos' ) ) + '</span><small>' + esc( __( 'Uncheck to keep this notice private.', 'masjidos' ) ) + '</small></label>' +
					'<div class="itmms-announcement-actions"><button class="itmms-btn itmms-btn-primary" type="submit">' + esc( editing ? __( 'Update Notice', 'masjidos' ) : __( 'Publish Notice', 'masjidos' ) ) + '</button>' + ( editing ? '<button class="itmms-btn itmms-btn-ghost" type="button" data-cancel-announcement>' + esc( __( 'Cancel', 'masjidos' ) ) + '</button>' : '' ) + '<span id="itmms-announcement-status"></span></div>' +
				'</form>' +
				'<section class="itmms-announcement-list-panel"><div class="itmms-announcement-panel-head"><div><span>' + esc( __( 'Notice library', 'masjidos' ) ) + '</span><h3>' + esc( __( 'Recent announcements', 'masjidos' ) ) + '</h3></div></div>' + announcementListHtml() + '</section>' +
			'</div>';
	}

	function announcementListHtml() {
		var state = window.itmms.state;
		if ( ! state.announcements.length ) {
			return '<div class="itmms-announcement-empty">' + esc( __( 'No notices yet. Publish the first update from the editor.', 'masjidos' ) ) + '</div>';
		}

		return '<div class="itmms-announcement-list">' + state.announcements.map( function ( notice ) {
			return '<article class="itmms-announcement-row itmms-announcement-row--' + esc( notice.announcement_type ) + '">' +
				'<div class="itmms-announcement-row-top"><span class="itmms-announcement-type">' + esc( announcementTypeLabel( notice.announcement_type ) ) + '</span><span class="itmms-announcement-status is-' + esc( notice.status ) + '">' + esc( announcementStatusLabel( notice.status ) ) + '</span></div>' +
				'<h4>' + esc( notice.title ) + '</h4>' + ( notice.content ? '<p>' + esc( notice.content ) + '</p>' : '' ) +
				'<div class="itmms-announcement-meta"><span>' + esc( sprintf( __( 'Starts %s', 'masjidos' ), formatAnnouncementDate( notice.start_date ) ) ) + '</span>' + ( notice.end_date ? '<span>' + esc( sprintf( __( 'Ends %s', 'masjidos' ), formatAnnouncementDate( notice.end_date ) ) ) + '</span>' : '<span>' + esc( __( 'No expiry', 'masjidos' ) ) + '</span>' ) + '<span>' + esc( sprintf( __( 'Priority %s', 'masjidos' ), notice.priority ) ) + '</span></div>' +
				'<div class="itmms-announcement-row-actions"><button type="button" class="itmms-link-btn" data-edit-announcement="' + esc( notice.id ) + '">' + esc( __( 'Edit', 'masjidos' ) ) + '</button><button type="button" class="itmms-link-btn is-danger" data-delete-announcement="' + esc( notice.id ) + '">' + esc( __( 'Delete', 'masjidos' ) ) + '</button></div>' +
			'</article>';
		} ).join( '' ) + '</div>';
	}

	function announcementTypeOptions( selected ) {
		return [ [ 'general', __( 'General', 'masjidos' ) ], [ 'urgent', __( 'Urgent', 'masjidos' ) ], [ 'jumuah', __( 'Jumuah', 'masjidos' ) ] ].map( function ( option ) {
			return '<option value="' + option[0] + '"' + ( option[0] === selected ? ' selected' : '' ) + '>' + option[1] + '</option>';
		} ).join( '' );
	}

	function announcementTypeLabel( type ) {
		return { general: __( 'Notice', 'masjidos' ), urgent: __( 'Urgent', 'masjidos' ), jumuah: __( 'Jumuah', 'masjidos' ) }[ type ] || __( 'Notice', 'masjidos' );
	}

	function announcementStatusLabel( status ) {
		return {
			active: __( 'Active', 'masjidos' ),
			scheduled: __( 'Scheduled', 'masjidos' ),
			expired: __( 'Expired', 'masjidos' ),
			draft: __( 'Draft', 'masjidos' )
		}[ status ] || status;
	}

	function datetimeLocalValue( value ) {
		if ( ! value ) {
			return '';
		}
		if ( value instanceof Date ) {
			return new Date( value.getTime() - value.getTimezoneOffset() * 60000 ).toISOString().slice( 0, 16 );
		}
		return String( value ).replace( ' ', 'T' ).slice( 0, 16 );
	}

	function formatAnnouncementDate( value ) {
		var data = window.itmms.data;
		if ( ! value ) {
			return '';
		}
		var date = new Date( String( value ).replace( ' ', 'T' ) );
		return isNaN( date.getTime() ) ? value : new Intl.DateTimeFormat( String( data.locale || 'en_US' ).replace( '_', '-' ), { dateStyle: 'medium', timeStyle: 'short' } ).format( date );
	}
} )();
