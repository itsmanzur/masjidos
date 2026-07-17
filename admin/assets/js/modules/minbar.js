/**
 * MasjidOS Admin - Minbar (Khatib Tools) Module.
 */
( function () {
	'use strict';

	window.itmms = window.itmms || {};
	var __ = window.wp.i18n.__;
	var sprintf = window.wp.i18n.sprintf;
	var esc = window.itmms.esc;

	window.itmms.minbar = {
		minbarHtml: minbarHtml,
		bindMinbarEvents: bindMinbarEvents
	};

	// Compat: older code may still call khutbahHtml.
	window.itmms.khutbah = window.itmms.khutbah || {};
	window.itmms.khutbah.khutbahHtml = function () {
		return minbarHtml();
	};

	function minbarHtml() {
		var state = window.itmms.state;
		var tab = state.minbarTab || 'dashboard';
		return '<div class="itmms-minbar">' +
			'<div class="itmms-minbar-panel">' + panelHtml( tab ) + '</div>' +
		'</div>';
	}

	function panelHtml( tab ) {
		switch ( tab ) {
			case 'archive':
				return archiveHtml();
			case 'planner':
				return plannerHtml();
			case 'references':
				return referencesHtml();
			case 'builder':
				return builderHtml();
			case 'schedule':
				return scheduleHtml();
			default:
				return dashboardHtml();
		}
	}

	function dash() {
		return window.itmms.state.minbarDash || {
			greeting_name: '',
			stats: { archive: 0, this_month: 0, planned: 0, references: 0, khatibs: 0 },
			recent: [],
			holy_days: [],
			cta: null,
			schedule: [],
			ai_available: false
		};
	}

	function categories() {
		return ( window.itmms.state.khutbahCategories ) || {
			aqeedah: 'Aqeedah',
			akhlaq: 'Akhlaq',
			fiqh: 'Fiqh',
			seerah: 'Seerah',
			tazkiyah: 'Tazkiyah',
			ramadan: 'Ramadan',
			other: 'Other'
		};
	}

	function categoryOptions( selected ) {
		var cats = categories();
		var html = '<option value="">' + esc( __( 'Select category', 'masjidos' ) ) + '</option>';
		Object.keys( cats ).forEach( function ( key ) {
			html += '<option value="' + esc( key ) + '"' + ( selected === key ? ' selected' : '' ) + '>' + esc( cats[ key ] ) + '</option>';
		} );
		return html;
	}

	function dashboardHtml() {
		var d = dash();
		var stats = d.stats || {};
		return '<div class="itmms-minbar-dash">' +
			( d.cta ? '<div class="itmms-minbar-cta"><div class="itmms-minbar-cta-copy"><strong>' + esc( d.cta.title || '' ) + '</strong><p>' + esc( d.cta.message || '' ) + '</p></div><button type="button" class="itmms-btn itmms-btn-primary" data-minbar-tab="planner">' + esc( __( 'Open Planner', 'masjidos' ) ) + '</button></div>' : '' ) +
			'<div class="itmms-minbar-stats">' +
				statCard( 't', stats.archive || 0, __( 'Archive total', 'masjidos' ) ) +
				statCard( 'g', stats.this_month || 0, __( 'This month', 'masjidos' ) ) +
				statCard( 'a', stats.planned || 0, __( 'Planned topics', 'masjidos' ) ) +
				statCard( 'p', stats.khatibs || 0, __( 'Active khatibs', 'masjidos' ) ) +
			'</div>' +
			'<div class="itmms-minbar-grid-2 itmms-minbar-dash-grid">' +
				'<section class="itmms-minbar-card itmms-minbar-dash-card"><div class="itmms-minbar-card-head"><h3>' + esc( __( 'Recent khutbahs', 'masjidos' ) ) + '</h3><button type="button" class="itmms-minbar-card-link" data-minbar-tab="archive">' + esc( __( 'View archive', 'masjidos' ) ) + '</button></div><div class="itmms-minbar-card-body">' + recentListHtml( d.recent || [] ) + '</div></section>' +
				'<section class="itmms-minbar-card itmms-minbar-dash-card"><div class="itmms-minbar-card-head"><h3>' + esc( __( 'Upcoming Islamic days', 'masjidos' ) ) + '</h3></div><div class="itmms-minbar-card-body">' + holyDaysHtml( d.holy_days || [] ) + '</div></section>' +
			'</div>' +
			'<section class="itmms-minbar-card itmms-minbar-dash-card"><div class="itmms-minbar-card-head"><h3>' + esc( __( 'Upcoming schedule', 'masjidos' ) ) + '</h3><button type="button" class="itmms-minbar-card-link" data-minbar-tab="schedule">' + esc( __( 'Manage', 'masjidos' ) ) + '</button></div><div class="itmms-minbar-card-body">' + scheduleListHtml( d.schedule || [], true ) + '</div></section>' +
		'</div>';
	}

	function statCard( tone, value, label ) {
		return '<div class="itmms-minbar-stat itmms-minbar-stat--' + esc( tone ) + '"><div class="itmms-minbar-stat-val">' + esc( value ) + '</div><div class="itmms-minbar-stat-lbl">' + esc( label ) + '</div></div>';
	}

	function recentListHtml( items ) {
		if ( ! items.length ) {
			return '<div class="itmms-announcement-empty">' + esc( __( 'No archive entries yet.', 'masjidos' ) ) + '</div>';
		}
		return '<div class="itmms-minbar-dash-recent-list">' + items.map( function ( item ) {
			var parts = dateParts( item.date );
			var title = String( item.topic || '' );
			if ( title.length > 72 ) {
				title = title.slice( 0, 72 ).trim() + '…';
			}
			return '<button type="button" class="itmms-minbar-dash-recent" data-open-recent-khutbah="' + esc( item.id ) + '">' +
				'<div class="sch-date-badge sch-date-badge--sm" title="' + esc( formatDate( item.date ) ) + '">' +
					'<span class="sch-day">' + esc( parts.day ) + '</span>' +
					'<span class="sch-mon">' + esc( parts.mon ) + '</span>' +
				'</div>' +
				'<div class="itmms-minbar-dash-recent-body">' +
					'<div class="itmms-minbar-dash-recent-title">' + esc( title ) + '</div>' +
					'<div class="itmms-minbar-dash-recent-meta">' +
						'<span>' + esc( sprintf( __( 'Khatib: %s', 'masjidos' ), item.khatib || '—' ) ) + '</span>' +
						( item.category ? '<span class="itmms-minbar-tag">' + esc( categories()[ item.category ] || item.category ) + '</span>' : '' ) +
					'</div>' +
				'</div>' +
			'</button>';
		} ).join( '' ) + '</div>';
	}

	function holyDaysHtml( items ) {
		if ( ! items.length ) {
			return '<div class="itmms-announcement-empty">' + esc( __( 'No upcoming holy days in range.', 'masjidos' ) ) + '</div>';
		}
		return '<div class="itmms-minbar-dash-holy-list">' + items.map( function ( item ) {
			return '<div class="itmms-minbar-dash-holy">' +
				'<span class="itmms-minbar-ie-dot"></span>' +
				'<div class="itmms-minbar-dash-holy-info">' +
					'<div class="itmms-minbar-dash-holy-name">' + esc( item.title || '' ) + '</div>' +
					'<div class="itmms-minbar-dash-holy-date">' + esc( formatDate( item.date ) ) + '</div>' +
				'</div>' +
				'<span class="itmms-minbar-dash-holy-days">' + esc( sprintf( __( '%d days', 'masjidos' ), item.days || 0 ) ) + '</span>' +
			'</div>';
		} ).join( '' ) + '</div>';
	}

	function archiveHtml() {
		var state = window.itmms.state;
		var editing = state.khutbahs.filter( function ( item ) {
			return Number( item.id ) === Number( state.editingKhutbah );
		} )[0] || null;
		var today = new Date();
		var dateValue = editing && editing.date ? String( editing.date ).slice( 0, 10 ) : today.toISOString().slice( 0, 10 );
		var filter = state.archiveFilter || { q: '', category: '' };
		var cats = categories();
		var filtered = state.khutbahs.filter( function ( item ) {
			var hay = ( ( item.topic || '' ) + ' ' + ( item.khatib || '' ) + ' ' + ( item.summary || '' ) + ' ' + ( item.tags || '' ) ).toLowerCase();
			if ( filter.q && hay.indexOf( String( filter.q ).toLowerCase() ) === -1 ) {
				return false;
			}
			if ( filter.category && item.category !== filter.category ) {
				return false;
			}
			return true;
		} );
		var withAudio = state.khutbahs.filter( function ( item ) { return !! item.audio_url; } ).length;
		var withPdf = state.khutbahs.filter( function ( item ) { return !! item.doc_url; } ).length;

		return '<div class="itmms-announcement-summary">' +
			'<span><b>' + esc( state.khutbahs.length ) + '</b> ' + esc( __( 'Total', 'masjidos' ) ) + '</span>' +
			'<span><b>' + esc( withAudio ) + '</b> ' + esc( __( 'Audio', 'masjidos' ) ) + '</span>' +
			'<span><b>' + esc( withPdf ) + '</b> ' + esc( __( 'PDF', 'masjidos' ) ) + '</span>' +
			'<span><code>[masjidos_khutbah_archive]</code></span>' +
		'</div>' +
		'<div class="itmms-minbar-toolbar">' +
			'<input type="search" data-archive-filter="q" value="' + esc( filter.q || '' ) + '" placeholder="' + esc( __( 'Search topic, khatib, tags…', 'masjidos' ) ) + '">' +
			'<select data-archive-filter="category"><option value="">' + esc( __( 'All categories', 'masjidos' ) ) + '</option>' +
			Object.keys( cats ).map( function ( key ) {
				return '<option value="' + esc( key ) + '"' + ( filter.category === key ? ' selected' : '' ) + '>' + esc( cats[ key ] ) + '</option>';
			} ).join( '' ) + '</select>' +
			( editing ? '<button type="button" class="itmms-btn itmms-btn-ghost" data-new-khutbah>' + esc( __( 'New entry', 'masjidos' ) ) + '</button>' : '' ) +
		'</div>' +
		'<div class="itmms-announcement-layout">' +
			'<form class="itmms-announcement-editor" id="itmms-khutbah-form">' +
				'<div class="itmms-announcement-panel-head"><div><span>' + esc( editing ? __( 'Editing', 'masjidos' ) : __( 'New entry', 'masjidos' ) ) + '</span><h3>' + esc( editing ? editing.topic : __( 'Add a Friday khutbah', 'masjidos' ) ) + '</h3></div></div>' +
				'<label class="itmms-field"><span>' + esc( __( 'Topic', 'masjidos' ) ) + '</span><input type="text" name="topic" maxlength="255" required value="' + esc( editing ? editing.topic : '' ) + '"></label>' +
				'<label class="itmms-field"><span>' + esc( __( 'Summary', 'masjidos' ) ) + '</span><textarea name="summary" rows="4">' + esc( editing ? editing.summary : '' ) + '</textarea></label>' +
				'<div class="itmms-announcement-form-grid">' +
					'<label class="itmms-field"><span>' + esc( __( 'Date', 'masjidos' ) ) + '</span><input type="date" name="date" required value="' + esc( dateValue ) + '"></label>' +
					'<label class="itmms-field"><span>' + esc( __( 'Khatib', 'masjidos' ) ) + '</span><input type="text" name="khatib" maxlength="255" required value="' + esc( editing ? editing.khatib : '' ) + '"></label>' +
					'<label class="itmms-field"><span>' + esc( __( 'Language', 'masjidos' ) ) + '</span><input type="text" name="language" value="' + esc( editing ? editing.language : '' ) + '"></label>' +
					'<label class="itmms-field"><span>' + esc( __( 'Category', 'masjidos' ) ) + '</span><select name="category">' + categoryOptions( editing ? editing.category : '' ) + '</select></label>' +
					'<label class="itmms-field"><span>' + esc( __( 'Tags', 'masjidos' ) ) + '</span><input type="text" name="tags" value="' + esc( editing ? editing.tags : '' ) + '" placeholder="' + esc( __( 'Comma-separated', 'masjidos' ) ) + '"></label>' +
					'<label class="itmms-field"><span>' + esc( __( 'Duration (min)', 'masjidos' ) ) + '</span><input type="number" name="duration_minutes" min="1" max="180" value="' + esc( editing && editing.duration_minutes ? editing.duration_minutes : '' ) + '"></label>' +
					'<label class="itmms-field"><span>' + esc( __( 'Quran refs', 'masjidos' ) ) + '</span><input type="text" name="quran_refs" value="' + esc( editing ? ( Array.isArray( editing.quran_refs ) ? editing.quran_refs.join( ', ' ) : editing.quran_refs ) : '' ) + '"></label>' +
					'<label class="itmms-field"><span>' + esc( __( 'Hadith refs', 'masjidos' ) ) + '</span><input type="text" name="hadith_refs" value="' + esc( editing ? ( Array.isArray( editing.hadith_refs ) ? editing.hadith_refs.join( ', ' ) : editing.hadith_refs ) : '' ) + '"></label>' +
					'<label class="itmms-field"><span>' + esc( __( 'Audio URL', 'masjidos' ) ) + '</span><div class="itmms-minbar-media-row"><input type="url" name="audio_url" id="itmms-khutbah-audio" value="' + esc( editing ? editing.audio_url : '' ) + '"><button type="button" class="itmms-btn itmms-btn-ghost" data-pick-media="audio" data-target="#itmms-khutbah-audio">' + esc( __( 'Library', 'masjidos' ) ) + '</button></div></label>' +
					'<label class="itmms-field"><span>' + esc( __( 'PDF / Doc URL', 'masjidos' ) ) + '</span><div class="itmms-minbar-media-row"><input type="url" name="doc_url" id="itmms-khutbah-doc" value="' + esc( editing ? editing.doc_url : '' ) + '"><button type="button" class="itmms-btn itmms-btn-ghost" data-pick-media="pdf" data-target="#itmms-khutbah-doc">' + esc( __( 'Library', 'masjidos' ) ) + '</button></div></label>' +
				'</div>' +
				'<label class="itmms-field itmms-field-inline"><input type="checkbox" name="is_public" value="1"' + ( ! editing || editing.is_public ? ' checked' : '' ) + '> <span>' + esc( __( 'Public (show on website)', 'masjidos' ) ) + '</span></label>' +
				'<div class="itmms-announcement-actions"><button class="itmms-btn itmms-btn-primary" type="submit">' + esc( editing ? __( 'Update', 'masjidos' ) : __( 'Save', 'masjidos' ) ) + '</button>' + ( editing ? '<button class="itmms-btn itmms-btn-ghost" type="button" data-cancel-khutbah>' + esc( __( 'Cancel', 'masjidos' ) ) + '</button>' : '' ) + '<span id="itmms-khutbah-status"></span></div>' +
				( state.khutbahSimilar && state.khutbahSimilar.length ? '<div class="itmms-minbar-warn"><strong>' + esc( __( 'Similar topics in archive', 'masjidos' ) ) + '</strong><ul>' + state.khutbahSimilar.map( function ( s ) {
					return '<li>' + esc( s.topic ) + ' <small>(' + esc( formatDate( s.date ) ) + ')</small></li>';
				} ).join( '' ) + '</ul></div>' : '' ) +
			'</form>' +
			'<section class="itmms-announcement-list-panel"><div class="itmms-announcement-panel-head"><div><span>' + esc( __( 'Library', 'masjidos' ) ) + '</span><h3>' + esc( __( 'Saved khutbahs', 'masjidos' ) ) + '</h3></div></div>' + archiveListHtml( filtered ) + '</section>' +
		'</div>';
	}

	function archiveListHtml( items ) {
		if ( ! items.length ) {
			return '<div class="itmms-announcement-empty">' + esc( __( 'No matching khutbahs.', 'masjidos' ) ) + '</div>';
		}
		return '<div class="itmms-minbar-archive-list">' + items.map( function ( item ) {
			var parts = dateParts( item.date );
			var summary = item.summary ? String( item.summary ) : '';
			if ( summary.length > 120 ) {
				summary = summary.slice( 0, 120 ).trim() + '…';
			}
			return '<article class="itmms-minbar-archive-card">' +
				'<div class="sch-row sch-row--meta">' +
					'<div class="sch-date-badge" title="' + esc( formatDate( item.date ) ) + '">' +
						'<span class="sch-day">' + esc( parts.day ) + '</span>' +
						'<span class="sch-mon">' + esc( parts.mon ) + '</span>' +
					'</div>' +
					'<div class="itmms-minbar-archive-badges">' +
						( categories()[ item.category ]
							? '<span class="itmms-minbar-tag">' + esc( categories()[ item.category ] ) + '</span>'
							: '' ) +
						'<span class="sch-status ' + ( item.is_public ? 'status-confirmed' : 'status-pending' ) + '">' +
							esc( item.is_public ? __( 'Public', 'masjidos' ) : __( 'Private', 'masjidos' ) ) +
						'</span>' +
					'</div>' +
				'</div>' +
				'<div class="itmms-minbar-archive-body">' +
					'<h4 class="itmms-minbar-archive-title">' + esc( item.topic || '—' ) + '</h4>' +
					'<p class="itmms-minbar-archive-khatib">' + esc( sprintf( __( 'Khatib: %s', 'masjidos' ), item.khatib || '—' ) ) + '</p>' +
					( summary ? '<p class="itmms-minbar-archive-summary">' + esc( summary ) + '</p>' : '' ) +
					( ( item.audio_url || item.doc_url || item.tags )
						? '<div class="itmms-minbar-archive-meta">' +
							( item.audio_url ? '<span class="itmms-minbar-tag">' + esc( __( 'Audio', 'masjidos' ) ) + '</span>' : '' ) +
							( item.doc_url ? '<span class="itmms-minbar-tag">' + esc( __( 'PDF', 'masjidos' ) ) + '</span>' : '' ) +
							( item.tags ? '<span class="itmms-minbar-archive-tags">' + esc( item.tags ) + '</span>' : '' ) +
						'</div>'
						: '' ) +
				'</div>' +
				'<div class="itmms-minbar-plan-actions">' +
					'<button type="button" class="itmms-minbar-plan-btn" data-edit-khutbah="' + esc( item.id ) + '">' + esc( __( 'Edit', 'masjidos' ) ) + '</button>' +
					'<button type="button" class="itmms-minbar-plan-btn" data-copy-khutbah="' + esc( item.id ) + '">' + esc( __( 'Copy', 'masjidos' ) ) + '</button>' +
					'<button type="button" class="itmms-minbar-plan-btn itmms-minbar-plan-btn--archive" data-load-builder="' + esc( item.id ) + '">' + esc( __( 'Builder', 'masjidos' ) ) + '</button>' +
					'<button type="button" class="itmms-minbar-plan-btn itmms-minbar-plan-btn--danger" data-delete-khutbah="' + esc( item.id ) + '">' + esc( __( 'Delete', 'masjidos' ) ) + '</button>' +
				'</div>' +
			'</article>';
		} ).join( '' ) + '</div>';
	}

	function plannerHtml() {
		var state = window.itmms.state;
		var plans = state.minbarPlans || [];
		var editing = state.editingPlan || null;
		var fridaySlots = nextFridays( 5 );
		var holy = ( dash().holy_days || [] ).slice( 0, 4 );

		return ( dash().cta ? '<div class="itmms-minbar-cta"><strong>' + esc( dash().cta.title || '' ) + '</strong><p>' + esc( dash().cta.message || '' ) + '</p></div>' : '' ) +
			'<div class="itmms-minbar-planner-week">' + fridaySlots.map( function ( slot ) {
				var plan = plans.filter( function ( p ) { return p.date === slot.date; } )[0];
				return '<button type="button" class="itmms-minbar-week-day' + ( plan ? ' has-topic' : '' ) + ( slot.isNext ? ' current' : '' ) + '" data-plan-date="' + esc( slot.date ) + '">' +
					'<div class="wd-name">' + esc( __( 'Fri', 'masjidos' ) ) + '</div>' +
					'<div class="wd-date">' + esc( slot.day ) + '</div>' +
					( plan ? '<div class="wd-topic">' + esc( plan.topic ) + '</div>' : '<div class="wd-empty">' + esc( __( 'Add topic', 'masjidos' ) ) + '</div>' ) +
				'</button>';
			} ).join( '' ) + '</div>' +
			'<div class="itmms-minbar-grid-2">' +
				'<form class="itmms-announcement-editor" id="itmms-plan-form">' +
					'<div class="itmms-announcement-panel-head"><div><span>' + esc( __( 'Topic planner', 'masjidos' ) ) + '</span><h3>' + esc( editing ? __( 'Edit plan', 'masjidos' ) : __( 'Plan a Friday', 'masjidos' ) ) + '</h3></div></div>' +
					'<input type="hidden" name="id" value="' + esc( editing ? editing.id : '' ) + '">' +
					'<div class="itmms-announcement-form-grid">' +
						'<label class="itmms-field"><span>' + esc( __( 'Date', 'masjidos' ) ) + '</span><input type="date" name="date" required value="' + esc( editing ? editing.date : ( fridaySlots[0] ? fridaySlots[0].date : '' ) ) + '"></label>' +
						'<label class="itmms-field"><span>' + esc( __( 'Category', 'masjidos' ) ) + '</span><select name="category">' + categoryOptions( editing ? editing.category : '' ) + '</select></label>' +
					'</div>' +
					'<label class="itmms-field"><span>' + esc( __( 'Topic', 'masjidos' ) ) + '</span><input type="text" name="topic" required value="' + esc( editing ? editing.topic : '' ) + '"></label>' +
					'<label class="itmms-field"><span>' + esc( __( 'Notes', 'masjidos' ) ) + '</span><textarea name="notes" rows="3">' + esc( editing ? editing.notes : '' ) + '</textarea></label>' +
					'<div class="itmms-announcement-actions"><button class="itmms-btn itmms-btn-primary" type="submit">' + esc( __( 'Save plan', 'masjidos' ) ) + '</button>' + ( editing ? '<button type="button" class="itmms-btn itmms-btn-ghost" data-cancel-plan>' + esc( __( 'Cancel', 'masjidos' ) ) + '</button>' : '' ) + '<span id="itmms-plan-status"></span></div>' +
				'</form>' +
				'<section class="itmms-minbar-card"><div class="itmms-minbar-card-head"><h3>' + esc( __( 'Islamic days', 'masjidos' ) ) + '</h3></div><div class="itmms-minbar-card-body">' + holyDaysHtml( holy ) + '</div></section>' +
			'</div>' +
			'<section class="itmms-minbar-card"><div class="itmms-minbar-card-head"><h3>' + esc( __( 'Planned topics', 'masjidos' ) ) + '</h3></div><div class="itmms-minbar-card-body">' +
				( ! plans.length ? '<div class="itmms-announcement-empty">' + esc( __( 'No plans yet. Click a Friday slot or use the form.', 'masjidos' ) ) + '</div>' :
					'<div class="itmms-minbar-plan-grid">' + plans.map( function ( plan ) {
						var notePreview = plan.notes ? String( plan.notes ) : '';
						if ( notePreview.length > 90 ) {
							notePreview = notePreview.slice( 0, 90 ).trim() + '…';
						}
						return '<article class="itmms-minbar-plan-card">' +
							'<div class="itmms-minbar-plan-card-top">' +
								'<time class="itmms-minbar-plan-date">' + esc( formatDate( plan.date ) ) + '</time>' +
								( plan.duplicates && plan.duplicates.length ? '<span class="itmms-minbar-tag itmms-minbar-tag--warn">' + esc( __( 'Similar', 'masjidos' ) ) + '</span>' : '' ) +
								( plan.category && categories()[ plan.category ] ? '<span class="itmms-minbar-tag">' + esc( categories()[ plan.category ] ) + '</span>' : '' ) +
							'</div>' +
							'<h4 class="itmms-minbar-plan-title">' + esc( plan.topic ) + '</h4>' +
							( notePreview ? '<p class="itmms-minbar-plan-notes">' + esc( notePreview ) + '</p>' : '<p class="itmms-minbar-plan-notes is-empty">' + esc( __( 'No notes', 'masjidos' ) ) + '</p>' ) +
							'<div class="itmms-minbar-plan-actions">' +
								'<button type="button" class="itmms-minbar-plan-btn" data-edit-plan="' + esc( plan.id ) + '">' + esc( __( 'Edit', 'masjidos' ) ) + '</button>' +
								'<button type="button" class="itmms-minbar-plan-btn" data-copy-plan="' + esc( plan.id ) + '">' + esc( __( 'Copy', 'masjidos' ) ) + '</button>' +
								'<button type="button" class="itmms-minbar-plan-btn itmms-minbar-plan-btn--archive" data-plan-to-archive="' + esc( plan.id ) + '">' + esc( __( 'Archive', 'masjidos' ) ) + '</button>' +
								'<button type="button" class="itmms-minbar-plan-btn itmms-minbar-plan-btn--danger" data-delete-plan="' + esc( plan.id ) + '">' + esc( __( 'Delete', 'masjidos' ) ) + '</button>' +
							'</div></article>';
					} ).join( '' ) + '</div>' ) +
			'</div></section>';
	}

	function referencesHtml() {
		var state = window.itmms.state;
		var results = state.minbarRefResults || [];
		var bookmarks = state.minbarBookmarks || [];
		var q = state.minbarRefQuery || '';
		var type = state.minbarRefType || 'all';

		return '<div class="itmms-minbar-search-bar">' +
			'<input type="search" id="itmms-ref-q" value="' + esc( q ) + '" placeholder="' + esc( __( 'Search verses, hadith, duas…', 'masjidos' ) ) + '">' +
			'<select id="itmms-ref-type">' +
				'<option value="all"' + ( type === 'all' ? ' selected' : '' ) + '>' + esc( __( 'All', 'masjidos' ) ) + '</option>' +
				'<option value="quran"' + ( type === 'quran' ? ' selected' : '' ) + '>' + esc( __( 'Quran', 'masjidos' ) ) + '</option>' +
				'<option value="hadith"' + ( type === 'hadith' ? ' selected' : '' ) + '>' + esc( __( 'Hadith', 'masjidos' ) ) + '</option>' +
				'<option value="dua"' + ( type === 'dua' ? ' selected' : '' ) + '>' + esc( __( 'Dua', 'masjidos' ) ) + '</option>' +
			'</select>' +
			'<button type="button" class="itmms-btn itmms-btn-primary" data-search-refs>' + esc( __( 'Search', 'masjidos' ) ) + '</button>' +
		'</div>' +
		'<div class="itmms-minbar-grid-2">' +
			'<section class="itmms-minbar-card"><div class="itmms-minbar-card-head"><h3>' + esc( __( 'Results', 'masjidos' ) ) + '</h3></div><div class="itmms-minbar-card-body">' +
				( ! results.length ? '<div class="itmms-announcement-empty">' + esc( __( 'Search the local library. No external API in Free.', 'masjidos' ) ) + '</div>' :
					results.map( function ( ref ) {
						return refCardHtml( ref, false );
					} ).join( '' ) ) +
			'</div></section>' +
			'<section class="itmms-minbar-card"><div class="itmms-minbar-card-head"><h3>' + esc( __( 'Bookmarks', 'masjidos' ) ) + '</h3></div><div class="itmms-minbar-card-body">' +
				( ! bookmarks.length ? '<div class="itmms-announcement-empty">' + esc( __( 'Bookmark references to use in Sermon Builder.', 'masjidos' ) ) + '</div>' :
					bookmarks.map( function ( ref ) {
						return refCardHtml( ref, true );
					} ).join( '' ) ) +
			'</div></section>' +
		'</div>';
	}

	function refCardHtml( ref, isBookmark ) {
		var type = ref.type || 'quran';
		var encoded = encodeURIComponent( JSON.stringify( ref ) );
		return '<div class="itmms-minbar-ref itmms-minbar-ref--' + esc( type ) + '" data-ref-payload="' + esc( encoded ) + '">' +
			( ref.ar ? '<div class="itmms-minbar-ref-ar">' + esc( ref.ar ) + '</div>' : '' ) +
			'<div class="itmms-minbar-ref-en">' + esc( ref.en || '' ) + '</div>' +
			'<div class="itmms-minbar-ref-src"><span>' + esc( ref.ref || type ) + '</span>' +
				'<span class="itmms-minbar-ref-actions">' +
					'<button type="button" class="itmms-link-btn" data-copy-ref="' + esc( ( ref.ar || '' ) + '\n' + ( ref.en || '' ) + '\n' + ( ref.ref || '' ) ) + '">' + esc( __( 'Copy', 'masjidos' ) ) + '</button>' +
					( isBookmark
						? '<button type="button" class="itmms-link-btn is-danger" data-remove-bookmark="' + esc( ref.id ) + '">' + esc( __( 'Remove', 'masjidos' ) ) + '</button>'
						: '<button type="button" class="itmms-link-btn" data-add-bookmark>' + esc( __( 'Bookmark', 'masjidos' ) ) + '</button>' ) +
				'</span></div>' +
		'</div>';
	}

	function builderHtml() {
		var state = window.itmms.state;
		var draft = state.sermonDraft || defaultOutline();
		var bookmarks = state.minbarBookmarks || [];
		var words = countWords( [ draft.intro, draft.point1, draft.point2, draft.point3, draft.conclusion, draft.second ].join( ' ' ) );
		var pct = Math.min( 100, Math.round( ( words / 1200 ) * 100 ) );
		var mins = Math.max( 1, Math.round( words / 130 ) );
		var aiOk = !!( dash().ai_available );

		return '<div class="itmms-minbar-builder-shell">' +
			'<div class="itmms-minbar-wc-bar">' +
				'<span class="wc-label">' + esc( __( 'Word count', 'masjidos' ) ) + '</span>' +
				'<div class="wc-track"><div class="wc-fill" id="itmms-sermon-wc-fill" style="width:' + pct + '%"></div></div>' +
				'<span class="wc-count" id="itmms-sermon-wc-count">' + esc( words ) + '</span>' +
				'<span class="wc-time" id="itmms-sermon-wc-time">~' + esc( mins ) + ' ' + esc( __( 'min', 'masjidos' ) ) + '</span>' +
				'<button type="button" class="itmms-btn itmms-btn-ghost" data-print-sermon>' + esc( __( 'Print', 'masjidos' ) ) + '</button>' +
				'<button type="button" class="itmms-btn' + ( aiOk ? ' itmms-btn-primary' : ' itmms-btn-ghost' ) + '" data-ai-assist' + ( aiOk ? '' : ' disabled' ) + ' title="' + esc( aiOk ? __( 'AI Assist', 'masjidos' ) : __( 'Pro feature', 'masjidos' ) ) + '">' + esc( __( 'AI Assist', 'masjidos' ) ) + ( aiOk ? '' : ' · Pro' ) + '</button>' +
			'</div>' +
			( ! aiOk ? '<div class="itmms-minbar-pro-note">' + esc( __( 'AI Assistant is available in MasjidOS Pro. Free includes outline editor, bookmarks, and print.', 'masjidos' ) ) + '</div>' : '' ) +
			'<form id="itmms-sermon-form" class="itmms-minbar-builder">' +
				'<div class="itmms-minbar-builder-meta">' +
					'<label class="itmms-field"><span>' + esc( __( 'Archive entry (optional)', 'masjidos' ) ) + '</span><select name="khutbah_id"><option value="0">' + esc( __( 'New / none', 'masjidos' ) ) + '</option>' +
						( window.itmms.state.khutbahs || [] ).map( function ( k ) {
							return '<option value="' + esc( k.id ) + '"' + ( Number( draft.khutbah_id ) === Number( k.id ) ? ' selected' : '' ) + '>' + esc( k.topic ) + '</option>';
						} ).join( '' ) + '</select></label>' +
					'<label class="itmms-field"><span>' + esc( __( 'Topic', 'masjidos' ) ) + '</span><input type="text" name="topic" value="' + esc( draft.topic || '' ) + '" required placeholder="' + esc( __( 'Khutbah topic…', 'masjidos' ) ) + '"></label>' +
				'</div>' +
				'<div class="itmms-minbar-outline">' +
					sermonSection( 'intro', '01', __( 'Introduction', 'masjidos' ), __( 'Open with praise, greeting, and the theme.', 'masjidos' ), draft.intro || '', 'intro' ) +
					sermonSection( 'point1', '02', __( 'Main point 1', 'masjidos' ), __( 'First supporting argument with evidence.', 'masjidos' ), draft.point1 || '', 'main1' ) +
					sermonSection( 'point2', '03', __( 'Main point 2', 'masjidos' ), __( 'Second argument — deepen the theme.', 'masjidos' ), draft.point2 || '', 'main2' ) +
					sermonSection( 'point3', '04', __( 'Main point 3', 'masjidos' ), __( 'Third argument or practical application.', 'masjidos' ), draft.point3 || '', 'main3' ) +
					sermonSection( 'conclusion', '05', __( 'Conclusion', 'masjidos' ), __( 'Summarize and call to action.', 'masjidos' ), draft.conclusion || '', 'conc' ) +
					sermonSection( 'second', '06', __( 'Second khutbah', 'masjidos' ), __( 'Duas, reminders, and closing.', 'masjidos' ), draft.second || '', 'second' ) +
				'</div>' +
				'<div class="itmms-minbar-card itmms-minbar-builder-refs"><div class="itmms-minbar-card-head"><h3>' + esc( __( 'Attached references', 'masjidos' ) ) + '</h3></div><div class="itmms-minbar-card-body">' +
					( ! bookmarks.length ? '<p class="itmms-docs-note">' + esc( __( 'Bookmark items in References to attach them here.', 'masjidos' ) ) + '</p>' :
						'<div class="itmms-minbar-ss-refs">' + bookmarks.map( function ( b ) {
							return '<span class="itmms-minbar-ss-ref">' + esc( b.ref || b.type ) + '</span>';
						} ).join( '' ) + '</div>' ) +
				'</div></div>' +
				'<div class="itmms-announcement-actions"><button type="submit" class="itmms-btn itmms-btn-primary">' + esc( __( 'Save outline to archive', 'masjidos' ) ) + '</button><span id="itmms-sermon-status"></span></div>' +
			'</form>' +
		'</div>' +
		'<div id="itmms-sermon-print" class="itmms-minbar-print" hidden></div>';
	}

	function sermonSection( name, num, title, hint, value, tone ) {
		var wc = countWords( value );
		return '<section class="itmms-minbar-ss itmms-minbar-ss--' + esc( tone ) + '" data-sermon-section="' + esc( name ) + '">' +
			'<div class="itmms-minbar-ss-rail" aria-hidden="true"><span class="itmms-minbar-ss-num">' + esc( num ) + '</span></div>' +
			'<div class="itmms-minbar-ss-body">' +
				'<header class="itmms-minbar-ss-head">' +
					'<div class="itmms-minbar-ss-titles">' +
						'<h4 class="itmms-minbar-ss-title">' + esc( title ) + '</h4>' +
						'<p class="itmms-minbar-ss-hint">' + esc( hint ) + '</p>' +
					'</div>' +
					'<span class="itmms-minbar-ss-words"><b data-section-wc="' + esc( name ) + '">' + esc( wc ) + '</b> ' + esc( __( 'words', 'masjidos' ) ) + '</span>' +
				'</header>' +
				'<textarea name="' + esc( name ) + '" rows="5" data-sermon-field placeholder="' + esc( hint ) + '">' + esc( value ) + '</textarea>' +
			'</div>' +
		'</section>';
	}

	function scheduleHtml() {
		var state = window.itmms.state;
		var profiles = state.minbarProfiles || [];
		var schedule = state.minbarSchedule || [];
		var editingProfile = state.editingProfile || null;
		var editingSchedule = state.editingSchedule || null;
		var defaultDate = editingSchedule ? editingSchedule.scheduled_date : ( nextFridays( 1 )[0] ? nextFridays( 1 )[0].date : '' );
		var typeValue = editingSchedule ? ( editingSchedule.type || 'jumuah' ) : 'jumuah';

		return '<div class="itmms-minbar-grid-2">' +
			'<form class="itmms-announcement-editor" id="itmms-profile-form" novalidate>' +
				'<div class="itmms-announcement-panel-head"><div><span>' + esc( __( 'Khatib profiles', 'masjidos' ) ) + '</span><h3>' + esc( editingProfile ? __( 'Edit profile', 'masjidos' ) : __( 'Add khatib', 'masjidos' ) ) + '</h3></div></div>' +
				'<div class="itmms-minbar-profile-photo">' +
					'<div class="itmms-minbar-profile-photo-preview">' +
						( isUsablePhotoUrl( editingProfile && editingProfile.photo_url )
							? '<img data-photo-preview src="' + esc( editingProfile.photo_url ) + '" alt="">'
							: '<img data-photo-preview src="" alt="" hidden><span class="itmms-minbar-profile-photo-placeholder">' + esc( __( 'Photo', 'masjidos' ) ) + '</span>' ) +
					'</div>' +
					'<div class="itmms-minbar-profile-photo-fields">' +
						'<label class="itmms-field"><span>' + esc( __( 'Profile photo', 'masjidos' ) ) + '</span><div class="itmms-minbar-media-row"><input type="text" name="photo_url" id="itmms-khatib-photo" value="' + esc( editingProfile && isUsablePhotoUrl( editingProfile.photo_url ) ? editingProfile.photo_url : '' ) + '" placeholder="https://"><button type="button" class="itmms-btn itmms-btn-ghost" data-pick-media="image" data-target="#itmms-khatib-photo">' + esc( __( 'Library', 'masjidos' ) ) + '</button></div></label>' +
					'</div>' +
				'</div>' +
				'<div class="itmms-announcement-form-grid">' +
					'<label class="itmms-field"><span>' + esc( __( 'Name', 'masjidos' ) ) + '</span><input type="text" name="name" value="' + esc( editingProfile ? editingProfile.name : '' ) + '" placeholder="' + esc( __( 'Optional', 'masjidos' ) ) + '"></label>' +
					'<label class="itmms-field"><span>' + esc( __( 'Title / role', 'masjidos' ) ) + '</span><input type="text" name="title" value="' + esc( editingProfile ? ( editingProfile.title || '' ) : '' ) + '" placeholder="' + esc( __( 'e.g. Imam, Guest khatib', 'masjidos' ) ) + '"></label>' +
					'<label class="itmms-field"><span>' + esc( __( 'Phone', 'masjidos' ) ) + '</span><input type="text" name="phone" value="' + esc( editingProfile ? editingProfile.phone : '' ) + '"></label>' +
					'<label class="itmms-field"><span>' + esc( __( 'Email', 'masjidos' ) ) + '</span><input type="text" name="email" value="' + esc( editingProfile ? ( editingProfile.email || '' ) : '' ) + '" placeholder="name@example.com"></label>' +
					'<label class="itmms-field"><span>' + esc( __( 'Expertise', 'masjidos' ) ) + '</span><input type="text" name="expertise" value="' + esc( editingProfile ? editingProfile.expertise : '' ) + '" placeholder="' + esc( __( 'e.g. Fiqh, Seerah', 'masjidos' ) ) + '"></label>' +
					'<label class="itmms-field"><span>' + esc( __( 'Languages', 'masjidos' ) ) + '</span><input type="text" name="languages" value="' + esc( editingProfile ? ( editingProfile.languages || '' ) : '' ) + '" placeholder="' + esc( __( 'e.g. Bangla, English, Arabic', 'masjidos' ) ) + '"></label>' +
					'<label class="itmms-field"><span>' + esc( __( 'Location', 'masjidos' ) ) + '</span><input type="text" name="location" value="' + esc( editingProfile ? ( editingProfile.location || '' ) : '' ) + '" placeholder="' + esc( __( 'City / masjid', 'masjidos' ) ) + '"></label>' +
					'<label class="itmms-field"><span>' + esc( __( 'Website', 'masjidos' ) ) + '</span><input type="text" name="website" value="' + esc( editingProfile ? ( editingProfile.website || '' ) : '' ) + '" placeholder="https:// or #"></label>' +
					'<label class="itmms-field"><span>' + esc( __( 'Facebook', 'masjidos' ) ) + '</span><input type="text" name="facebook_url" value="' + esc( editingProfile ? ( editingProfile.facebook_url || '' ) : '' ) + '" placeholder="https:// or #"></label>' +
					'<label class="itmms-field"><span>' + esc( __( 'YouTube', 'masjidos' ) ) + '</span><input type="text" name="youtube_url" value="' + esc( editingProfile ? ( editingProfile.youtube_url || '' ) : '' ) + '" placeholder="https:// or #"></label>' +
					'<label class="itmms-field"><span>' + esc( __( 'Instagram', 'masjidos' ) ) + '</span><input type="text" name="instagram_url" value="' + esc( editingProfile ? ( editingProfile.instagram_url || '' ) : '' ) + '" placeholder="https:// or #"></label>' +
					'<label class="itmms-field"><span>' + esc( __( 'LinkedIn', 'masjidos' ) ) + '</span><input type="text" name="linkedin_url" value="' + esc( editingProfile ? ( editingProfile.linkedin_url || '' ) : '' ) + '" placeholder="https:// or #"></label>' +
					'<label class="itmms-field"><span>' + esc( __( 'X (Twitter)', 'masjidos' ) ) + '</span><input type="text" name="x_url" value="' + esc( editingProfile ? ( editingProfile.x_url || '' ) : '' ) + '" placeholder="https:// or #"></label>' +
					'<label class="itmms-field"><span>' + esc( __( 'TikTok', 'masjidos' ) ) + '</span><input type="text" name="tiktok_url" value="' + esc( editingProfile ? ( editingProfile.tiktok_url || '' ) : '' ) + '" placeholder="https:// or #"></label>' +
				'</div>' +
				'<label class="itmms-field"><span>' + esc( __( 'Bio', 'masjidos' ) ) + '</span><textarea name="bio" rows="3">' + esc( editingProfile ? editingProfile.bio : '' ) + '</textarea></label>' +
				'<label class="itmms-field itmms-field-inline"><input type="checkbox" name="is_active" value="1"' + ( ! editingProfile || editingProfile.is_active ? ' checked' : '' ) + '> <span>' + esc( __( 'Active', 'masjidos' ) ) + '</span></label>' +
				'<div class="itmms-announcement-actions"><button class="itmms-btn itmms-btn-primary" type="submit">' + esc( editingProfile ? __( 'Update profile', 'masjidos' ) : __( 'Save profile', 'masjidos' ) ) + '</button>' + ( editingProfile ? '<button type="button" class="itmms-btn itmms-btn-ghost" data-cancel-profile>' + esc( __( 'Cancel', 'masjidos' ) ) + '</button>' : '' ) + '</div>' +
				'<div class="itmms-minbar-profile-list">' + ( ! profiles.length ? '<div class="itmms-announcement-empty">' + esc( __( 'No profiles yet.', 'masjidos' ) ) + '</div>' :
					profiles.map( function ( p ) {
						return '<article class="itmms-minbar-profile-card">' +
							'<div class="itmms-minbar-profile-card-top">' +
								'<div class="itmms-minbar-profile-card-media">' +
								( isUsablePhotoUrl( p.photo_url )
									? '<img src="' + esc( p.photo_url ) + '" alt="">'
									: '<span>' + esc( ( p.name || '?' ).slice( 0, 1 ).toUpperCase() ) + '</span>' ) +
								'</div>' +
								( p.is_active ? '' : '<span class="itmms-minbar-profile-badge">' + esc( __( 'Inactive', 'masjidos' ) ) + '</span>' ) +
							'</div>' +
							'<div class="itmms-minbar-profile-card-body">' +
								'<h4 class="itmms-minbar-profile-name">' + esc( p.name ) + '</h4>' +
								( p.title ? '<p class="itmms-minbar-profile-meta">' + esc( p.title ) + '</p>' : '' ) +
								( p.expertise ? '<p class="itmms-minbar-profile-meta">' + esc( p.expertise ) + '</p>' : '' ) +
								( p.email || p.phone ? '<p class="itmms-minbar-profile-meta">' + esc( [ p.email, p.phone ].filter( Boolean ).join( ' · ' ) ) + '</p>' : '' ) +
								profileLinksHtml( p ) +
							'</div>' +
							'<div class="itmms-minbar-plan-actions">' +
								'<button type="button" class="itmms-minbar-plan-btn" data-edit-profile="' + esc( p.id ) + '">' + esc( __( 'Edit', 'masjidos' ) ) + '</button>' +
								'<button type="button" class="itmms-minbar-plan-btn" data-copy-profile="' + esc( p.id ) + '">' + esc( __( 'Copy', 'masjidos' ) ) + '</button>' +
								'<button type="button" class="itmms-minbar-plan-btn itmms-minbar-plan-btn--danger" data-delete-profile="' + esc( p.id ) + '">' + esc( __( 'Delete', 'masjidos' ) ) + '</button>' +
							'</div></article>';
					} ).join( '' ) ) + '</div>' +
			'</form>' +
			'<form class="itmms-announcement-editor" id="itmms-schedule-form">' +
				'<div class="itmms-announcement-panel-head"><div><span>' + esc( __( 'Roster', 'masjidos' ) ) + '</span><h3>' + esc( editingSchedule ? __( 'Edit slot', 'masjidos' ) : __( 'Schedule a Friday', 'masjidos' ) ) + '</h3></div></div>' +
				'<div class="itmms-announcement-form-grid">' +
					'<label class="itmms-field"><span>' + esc( __( 'Khatib', 'masjidos' ) ) + '</span><select name="khatib_id" required><option value="">' + esc( __( 'Select…', 'masjidos' ) ) + '</option>' +
						profiles.filter( function ( p ) { return p.is_active; } ).map( function ( p ) {
							return '<option value="' + esc( p.id ) + '"' + ( editingSchedule && Number( editingSchedule.khatib_id ) === Number( p.id ) ? ' selected' : '' ) + '>' + esc( p.name ) + '</option>';
						} ).join( '' ) + '</select></label>' +
					'<label class="itmms-field"><span>' + esc( __( 'Date', 'masjidos' ) ) + '</span><input type="date" name="scheduled_date" required value="' + esc( defaultDate ) + '"></label>' +
					'<label class="itmms-field"><span>' + esc( __( 'Status', 'masjidos' ) ) + '</span><select name="status">' +
						'<option value="confirmed"' + ( ! editingSchedule || editingSchedule.status === 'confirmed' ? ' selected' : '' ) + '>' + esc( __( 'Confirmed', 'masjidos' ) ) + '</option>' +
						'<option value="guest"' + ( editingSchedule && editingSchedule.status === 'guest' ? ' selected' : '' ) + '>' + esc( __( 'Guest', 'masjidos' ) ) + '</option>' +
						'<option value="pending"' + ( editingSchedule && editingSchedule.status === 'pending' ? ' selected' : '' ) + '>' + esc( __( 'Pending', 'masjidos' ) ) + '</option>' +
					'</select></label>' +
					'<label class="itmms-field"><span>' + esc( __( 'Type', 'masjidos' ) ) + '</span><select name="type">' +
						'<option value="jumuah"' + ( typeValue === 'jumuah' ? ' selected' : '' ) + '>' + esc( __( 'Jumuah', 'masjidos' ) ) + '</option>' +
						'<option value="special"' + ( typeValue === 'special' ? ' selected' : '' ) + '>' + esc( __( 'Special', 'masjidos' ) ) + '</option>' +
						'<option value="eid"' + ( typeValue === 'eid' ? ' selected' : '' ) + '>' + esc( __( 'Eid', 'masjidos' ) ) + '</option>' +
						'<option value="other"' + ( typeValue === 'other' || ( typeValue && [ 'jumuah', 'special', 'eid' ].indexOf( typeValue ) === -1 ) ? ' selected' : '' ) + '>' + esc( __( 'Other', 'masjidos' ) ) + '</option>' +
					'</select></label>' +
				'</div>' +
				'<label class="itmms-field"><span>' + esc( __( 'Topic', 'masjidos' ) ) + '</span><input type="text" name="topic" value="' + esc( editingSchedule ? editingSchedule.topic : '' ) + '"></label>' +
				'<label class="itmms-field"><span>' + esc( __( 'Notes', 'masjidos' ) ) + '</span><textarea name="notes" rows="2">' + esc( editingSchedule ? editingSchedule.notes : '' ) + '</textarea></label>' +
				'<div class="itmms-announcement-actions"><button class="itmms-btn itmms-btn-primary" type="submit">' + esc( editingSchedule ? __( 'Update schedule', 'masjidos' ) : __( 'Add to roster', 'masjidos' ) ) + '</button>' + ( editingSchedule ? '<button type="button" class="itmms-btn itmms-btn-ghost" data-cancel-schedule>' + esc( __( 'Cancel', 'masjidos' ) ) + '</button>' : '' ) + '</div>' +
				'<div class="itmms-minbar-roster">' +
					'<div class="itmms-minbar-roster-head">' + esc( __( 'Upcoming roster', 'masjidos' ) ) + '</div>' +
					scheduleListHtml( schedule, false ) +
				'</div>' +
			'</form>' +
		'</div>';
	}

	function isUsablePhotoUrl( value ) {
		var url = String( value || '' ).trim();
		if ( ! url || url === '#' || url === 'https://' || url === 'http://' ) {
			return false;
		}
		return /^https?:\/\/.+/i.test( url ) || url.indexOf( '/' ) === 0;
	}

	function setPhotoPreview( root, url ) {
		if ( ! root ) {
			return;
		}
		var preview = root.querySelector( '[data-photo-preview]' );
		var placeholder = root.querySelector( '.itmms-minbar-profile-photo-placeholder' );
		if ( ! preview ) {
			return;
		}
		if ( isUsablePhotoUrl( url ) ) {
			preview.src = url;
			preview.hidden = false;
			if ( placeholder ) {
				placeholder.hidden = true;
			}
		} else {
			preview.removeAttribute( 'src' );
			preview.hidden = true;
			if ( placeholder ) {
				placeholder.hidden = false;
			} else if ( preview.parentNode ) {
				var span = document.createElement( 'span' );
				span.className = 'itmms-minbar-profile-photo-placeholder';
				span.textContent = __( 'Photo', 'masjidos' );
				preview.parentNode.appendChild( span );
			}
		}
	}

	function profileLinksHtml( profile ) {
		var items = [
			{ key: 'website', label: __( 'Website', 'masjidos' ) },
			{ key: 'facebook_url', label: __( 'Facebook', 'masjidos' ) },
			{ key: 'youtube_url', label: __( 'YouTube', 'masjidos' ) },
			{ key: 'instagram_url', label: __( 'Instagram', 'masjidos' ) },
			{ key: 'linkedin_url', label: __( 'LinkedIn', 'masjidos' ) },
			{ key: 'x_url', label: __( 'X', 'masjidos' ) },
			{ key: 'tiktok_url', label: __( 'TikTok', 'masjidos' ) },
		];
		var parts = [];
		items.forEach( function ( item ) {
			var href = String( profile[ item.key ] || '' ).trim();
			if ( ! href ) {
				return;
			}
			parts.push(
				'<a class="itmms-minbar-profile-link" href="' + esc( href ) + '"' +
				( href.charAt( 0 ) === '#' ? '' : ' target="_blank" rel="noopener noreferrer"' ) +
				'>' + esc( item.label ) + '</a>'
			);
		} );
		if ( ! parts.length ) {
			return '';
		}
		return '<div class="itmms-minbar-profile-links">' + parts.join( '' ) + '</div>';
	}

	function scheduleListHtml( items, compact ) {
		if ( ! items.length ) {
			return '<div class="itmms-announcement-empty">' + esc( __( 'No schedule entries.', 'masjidos' ) ) + '</div>';
		}
		if ( compact ) {
			return '<div class="itmms-minbar-dash-schedule-grid">' + items.map( function ( row ) {
				var parts = dateParts( row.scheduled_date );
				return '<article class="itmms-minbar-dash-sch">' +
					'<div class="sch-date-badge sch-date-badge--sm" title="' + esc( formatDate( row.scheduled_date ) ) + '">' +
						'<span class="sch-day">' + esc( parts.day ) + '</span>' +
						'<span class="sch-mon">' + esc( parts.mon ) + '</span>' +
					'</div>' +
					( isUsablePhotoUrl( row.khatib_photo )
						? '<div class="sch-avatar sch-avatar--photo"><img src="' + esc( row.khatib_photo ) + '" alt=""></div>'
						: '<div class="sch-avatar">' + esc( ( row.khatib_name || '?' ).slice( 0, 1 ).toUpperCase() ) + '</div>' ) +
					'<div class="sch-info">' +
						'<div class="sch-name">' + esc( row.khatib_name || '—' ) + '</div>' +
						'<div class="sch-topic">' + esc( row.topic || __( 'Topic TBA', 'masjidos' ) ) + '</div>' +
					'</div>' +
					'<span class="sch-status status-' + esc( row.status || 'pending' ) + '">' + esc( statusLabel( row.status ) ) + '</span>' +
				'</article>';
			} ).join( '' ) + '</div>';
		}
		return '<div class="itmms-minbar-roster-list">' + items.map( function ( row ) {
			var parts = dateParts( row.scheduled_date );
			return '<article class="itmms-minbar-sch">' +
				'<div class="sch-row sch-row--meta">' +
					'<div class="sch-date-badge" title="' + esc( formatDate( row.scheduled_date ) ) + '">' +
						'<span class="sch-day">' + esc( parts.day ) + '</span>' +
						'<span class="sch-mon">' + esc( parts.mon ) + '</span>' +
					'</div>' +
					'<span class="sch-status status-' + esc( row.status || 'pending' ) + '">' + esc( statusLabel( row.status ) ) + '</span>' +
				'</div>' +
				'<div class="sch-row sch-row--body">' +
					( isUsablePhotoUrl( row.khatib_photo )
						? '<div class="sch-avatar sch-avatar--photo"><img src="' + esc( row.khatib_photo ) + '" alt=""></div>'
						: '<div class="sch-avatar">' + esc( ( row.khatib_name || '?' ).slice( 0, 1 ).toUpperCase() ) + '</div>' ) +
					'<div class="sch-info">' +
						'<div class="sch-name">' + esc( row.khatib_name || '—' ) + '</div>' +
						'<div class="sch-topic">' + esc( row.topic || __( 'Topic TBA', 'masjidos' ) ) + '</div>' +
					'</div>' +
				'</div>' +
				( compact ? '' :
					'<div class="sch-row sch-row--actions">' +
						'<button type="button" class="itmms-minbar-plan-btn" data-edit-schedule="' + esc( row.id ) + '">' + esc( __( 'Edit', 'masjidos' ) ) + '</button>' +
						'<button type="button" class="itmms-minbar-plan-btn" data-copy-schedule="' + esc( row.id ) + '">' + esc( __( 'Copy', 'masjidos' ) ) + '</button>' +
						'<button type="button" class="itmms-minbar-plan-btn itmms-minbar-plan-btn--danger" data-delete-schedule="' + esc( row.id ) + '">' + esc( __( 'Delete', 'masjidos' ) ) + '</button>' +
					'</div>' ) +
			'</article>';
		} ).join( '' ) + '</div>';
	}

	function dateParts( value ) {
		var fallback = { day: '—', mon: '' };
		if ( ! value ) {
			return fallback;
		}
		var date = new Date( String( value ) + 'T00:00:00' );
		if ( isNaN( date.getTime() ) ) {
			return fallback;
		}
		var locale = String( ( window.itmms.data && window.itmms.data.locale ) || 'en_US' ).replace( '_', '-' );
		return {
			day: String( date.getDate() ),
			mon: new Intl.DateTimeFormat( locale, { month: 'short' } ).format( date ),
		};
	}

	function statusLabel( status ) {
		if ( status === 'guest' ) {
			return __( 'Guest', 'masjidos' );
		}
		if ( status === 'pending' ) {
			return __( 'Pending', 'masjidos' );
		}
		return __( 'Confirmed', 'masjidos' );
	}

	function defaultOutline() {
		return { khutbah_id: 0, topic: '', intro: '', point1: '', point2: '', point3: '', conclusion: '', second: '' };
	}

	function countWords( text ) {
		var t = String( text || '' ).trim();
		if ( ! t ) {
			return 0;
		}
		return t.split( /\s+/ ).filter( Boolean ).length;
	}

	function nextFridays( count ) {
		var out = [];
		var d = new Date();
		d.setHours( 12, 0, 0, 0 );
		var day = d.getDay();
		var add = ( 5 - day + 7 ) % 7;
		if ( add === 0 && day !== 5 ) {
			add = 7;
		}
		d.setDate( d.getDate() + add );
		for ( var i = 0; i < count; i++ ) {
			var cur = new Date( d.getTime() );
			cur.setDate( d.getDate() + ( i * 7 ) );
			out.push( {
				date: cur.toISOString().slice( 0, 10 ),
				day: String( cur.getDate() ),
				isNext: i === 0
			} );
		}
		return out;
	}

	function monthShort( m ) {
		var names = [ '', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' ];
		return names[ parseInt( m, 10 ) ] || '';
	}

	function formatDate( value ) {
		if ( ! value ) {
			return '';
		}
		var date = new Date( String( value ) + 'T00:00:00' );
		if ( isNaN( date.getTime() ) ) {
			return value;
		}
		var locale = String( ( window.itmms.data && window.itmms.data.locale ) || 'en_US' ).replace( '_', '-' );
		return new Intl.DateTimeFormat( locale, { dateStyle: 'medium' } ).format( date );
	}

	function refreshMinbar() {
		if ( typeof window.itmms.render === 'function' ) {
			window.itmms.render();
			if ( typeof window.itmms.switchTab === 'function' ) {
				window.itmms.switchTab( 'minbar', 'replace' );
			}
		}
	}

	function bindMinbarEvents( api, render, switchTab ) {
		var app = document.getElementById( 'itmms-app' );
		var state = window.itmms.state;
		if ( ! app ) {
			return;
		}

		app.querySelectorAll( '[data-minbar-tab]:not(.itmms-nav-item)' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				state.minbarTab = btn.getAttribute( 'data-minbar-tab' ) || 'dashboard';
				state.activeTab = 'minbar';
				render();
				if ( window.itmms.syncViewUrl ) {
					window.itmms.syncViewUrl( 'push' );
				}
			} );
		} );

		app.querySelectorAll( '[data-open-recent-khutbah]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var id = Number( btn.getAttribute( 'data-open-recent-khutbah' ) );
				state.editingKhutbah = id;
				state.khutbahSimilar = [];
				state.minbarTab = 'archive';
				state.activeTab = 'minbar';
				render();
				if ( window.itmms.syncViewUrl ) {
					window.itmms.syncViewUrl( 'push' );
				}
			} );
		} );

		app.querySelectorAll( '[data-archive-filter]' ).forEach( function ( el ) {
			el.addEventListener( 'change', function () {
				state.archiveFilter = state.archiveFilter || { q: '', category: '' };
				state.archiveFilter[ el.getAttribute( 'data-archive-filter' ) ] = el.value;
				render();
				switchTab( 'minbar', 'replace' );
			} );
			if ( el.tagName === 'INPUT' ) {
				el.addEventListener( 'keydown', function ( e ) {
					if ( e.key === 'Enter' ) {
						e.preventDefault();
						state.archiveFilter = state.archiveFilter || { q: '', category: '' };
						state.archiveFilter.q = el.value;
						render();
						switchTab( 'minbar', 'replace' );
					}
				} );
			}
		} );

		app.querySelectorAll( '[data-pick-media]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				if ( ! window.wp || ! window.wp.media ) {
					window.alert( __( 'Media library unavailable.', 'masjidos' ) );
					return;
				}
				var target = app.querySelector( btn.getAttribute( 'data-target' ) );
				var kind = btn.getAttribute( 'data-pick-media' );
				var libraryType = 'audio';
				var title = __( 'Select audio', 'masjidos' );
				if ( kind === 'pdf' ) {
					libraryType = 'application';
					title = __( 'Select PDF', 'masjidos' );
				} else if ( kind === 'image' ) {
					libraryType = 'image';
					title = __( 'Select photo', 'masjidos' );
				}
				var frame = window.wp.media( {
					title: title,
					button: { text: __( 'Use file', 'masjidos' ) },
					multiple: false,
					library: { type: libraryType }
				} );
				frame.on( 'select', function () {
					var attachment = frame.state().get( 'selection' ).first().toJSON();
					if ( target && attachment && attachment.url ) {
						target.value = attachment.url;
						if ( kind === 'image' ) {
							setPhotoPreview( app, attachment.url );
						}
					}
				} );
				frame.open();
			} );
		} );

		var photoInput = app.querySelector( '#itmms-khatib-photo' );
		if ( photoInput ) {
			photoInput.addEventListener( 'input', function () {
				setPhotoPreview( app, photoInput.value );
			} );
		}

		var khutbahForm = document.getElementById( 'itmms-khutbah-form' );
		if ( khutbahForm ) {
			khutbahForm.addEventListener( 'submit', function ( event ) {
				event.preventDefault();
				var fields = new FormData( khutbahForm );
				var payload = {};
				fields.forEach( function ( value, key ) { payload[ key ] = value; } );
				payload.is_public = khutbahForm.querySelector( '[name="is_public"]' ) && khutbahForm.querySelector( '[name="is_public"]' ).checked ? 1 : 0;
				saveKhutbah( api, render, switchTab, payload, khutbahForm.querySelector( '[type="submit"]' ) );
			} );
		}

		app.querySelectorAll( '[data-edit-khutbah]' ).forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				state.editingKhutbah = Number( button.getAttribute( 'data-edit-khutbah' ) );
				state.khutbahSimilar = [];
				state.minbarTab = 'archive';
				render();
				switchTab( 'minbar', 'replace' );
			} );
		} );

		app.querySelectorAll( '[data-copy-khutbah]' ).forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				var id = Number( button.getAttribute( 'data-copy-khutbah' ) );
				var item = ( state.khutbahs || [] ).filter( function ( k ) { return Number( k.id ) === id; } )[0];
				if ( ! item ) {
					return;
				}
				button.disabled = true;
				var today = new Date();
				var dateValue = today.toISOString().slice( 0, 10 );
				var quranRefs = Array.isArray( item.quran_refs ) ? item.quran_refs.join( ', ' ) : ( item.quran_refs || '' );
				var hadithRefs = Array.isArray( item.hadith_refs ) ? item.hadith_refs.join( ', ' ) : ( item.hadith_refs || '' );
				var payload = {
					topic: ( item.topic || __( 'Untitled', 'masjidos' ) ) + ' ' + __( '(copy)', 'masjidos' ),
					summary: item.summary || '',
					date: dateValue,
					khatib: item.khatib || '',
					language: item.language || '',
					category: item.category || '',
					tags: item.tags || '',
					duration_minutes: item.duration_minutes || '',
					quran_refs: quranRefs,
					hadith_refs: hadithRefs,
					audio_url: item.audio_url || '',
					doc_url: item.doc_url || '',
					is_public: item.is_public ? 1 : 0,
					outline: item.outline || null
				};
				api( 'khutbah', { method: 'POST', body: JSON.stringify( payload ) } ).then( function ( res ) {
					state.editingKhutbah = ( res && res.khutbah && res.khutbah.id ) ? Number( res.khutbah.id ) : ( res && res.id ? Number( res.id ) : 0 );
					state.khutbahSimilar = ( res && res.similar ) || [];
					return loadKhutbahs( api );
				} ).then( function () {
					return loadMinbarDash( api );
				} ).then( function () {
					state.minbarTab = 'archive';
					render();
					switchTab( 'minbar', 'replace' );
				} ).catch( function ( error ) {
					button.disabled = false;
					window.alert( error.message );
				} );
			} );
		} );

		app.querySelectorAll( '[data-new-khutbah], [data-cancel-khutbah]' ).forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				state.editingKhutbah = 0;
				state.khutbahSimilar = [];
				state.minbarTab = 'archive';
				render();
				switchTab( 'minbar', 'replace' );
			} );
		} );

		app.querySelectorAll( '[data-delete-khutbah]' ).forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				var id = Number( button.getAttribute( 'data-delete-khutbah' ) );
				if ( ! window.confirm( __( 'Delete this khutbah permanently?', 'masjidos' ) ) ) {
					return;
				}
				button.disabled = true;
				api( 'khutbah/' + id, { method: 'DELETE' } ).then( function () {
					state.editingKhutbah = 0;
					return loadKhutbahs( api );
				} ).then( function () {
					return loadMinbarDash( api );
				} ).then( function () {
					state.minbarTab = 'archive';
					render();
					switchTab( 'minbar', 'replace' );
				} ).catch( function ( error ) {
					button.disabled = false;
					window.alert( error.message );
				} );
			} );
		} );

		app.querySelectorAll( '[data-load-builder]' ).forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				var id = Number( button.getAttribute( 'data-load-builder' ) );
				var item = ( state.khutbahs || [] ).filter( function ( k ) { return Number( k.id ) === id; } )[0];
				var outline = item && item.outline && typeof item.outline === 'object' ? item.outline : {};
				state.sermonDraft = {
					khutbah_id: id,
					topic: item ? item.topic : '',
					intro: outline.intro || '',
					point1: outline.point1 || '',
					point2: outline.point2 || '',
					point3: outline.point3 || '',
					conclusion: outline.conclusion || '',
					second: outline.second || ''
				};
				state.minbarTab = 'builder';
				render();
				switchTab( 'minbar', 'replace' );
			} );
		} );

		// Planner
		var planForm = document.getElementById( 'itmms-plan-form' );
		if ( planForm ) {
			planForm.addEventListener( 'submit', function ( event ) {
				event.preventDefault();
				var fields = new FormData( planForm );
				var payload = {};
				fields.forEach( function ( value, key ) { payload[ key ] = value; } );
				var status = document.getElementById( 'itmms-plan-status' );
				var btn = planForm.querySelector( '[type="submit"]' );
				if ( btn ) { btn.disabled = true; }
				if ( status ) { status.textContent = __( 'Saving...', 'masjidos' ); }
				api( 'minbar/plans', { method: 'POST', body: JSON.stringify( payload ) } ).then( function ( res ) {
					state.minbarPlans = res.plans || [];
					state.editingPlan = null;
					return loadMinbarDash( api );
				} ).then( function () {
					state.minbarTab = 'planner';
					render();
					switchTab( 'minbar', 'replace' );
				} ).catch( function ( error ) {
					if ( status ) { status.textContent = error.message; }
					if ( btn ) { btn.disabled = false; }
				} );
			} );
		}

		app.querySelectorAll( '[data-plan-date]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var date = btn.getAttribute( 'data-plan-date' );
				var existing = ( state.minbarPlans || [] ).filter( function ( p ) { return p.date === date; } )[0];
				state.editingPlan = existing || { id: '', date: date, topic: '', category: '', notes: '' };
				state.minbarTab = 'planner';
				render();
				switchTab( 'minbar', 'replace' );
			} );
		} );

		app.querySelectorAll( '[data-edit-plan]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var id = btn.getAttribute( 'data-edit-plan' );
				state.editingPlan = ( state.minbarPlans || [] ).filter( function ( p ) { return p.id === id; } )[0] || null;
				render();
				switchTab( 'minbar', 'replace' );
			} );
		} );

		app.querySelectorAll( '[data-copy-plan]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var id = btn.getAttribute( 'data-copy-plan' );
				var plan = ( state.minbarPlans || [] ).filter( function ( p ) { return p.id === id; } )[0];
				if ( ! plan ) {
					return;
				}
				var taken = {};
				( state.minbarPlans || [] ).forEach( function ( p ) {
					taken[ p.date ] = true;
				} );
				var slots = nextFridays( 12 );
				var copyDate = plan.date;
				for ( var i = 0; i < slots.length; i++ ) {
					if ( ! taken[ slots[ i ].date ] ) {
						copyDate = slots[ i ].date;
						break;
					}
				}
				btn.disabled = true;
				api( 'minbar/plans', {
					method: 'POST',
					body: JSON.stringify( {
						id: '',
						date: copyDate,
						topic: plan.topic,
						category: plan.category || '',
						notes: plan.notes || ''
					} )
				} ).then( function ( res ) {
					state.minbarPlans = res.plans || [];
					state.editingPlan = res.plan || null;
					return loadMinbarDash( api );
				} ).then( function () {
					state.minbarTab = 'planner';
					render();
					switchTab( 'minbar', 'replace' );
				} ).catch( function ( error ) {
					btn.disabled = false;
					window.alert( error.message );
				} );
			} );
		} );

		app.querySelectorAll( '[data-cancel-plan]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				state.editingPlan = null;
				render();
				switchTab( 'minbar', 'replace' );
			} );
		} );

		app.querySelectorAll( '[data-delete-plan]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var id = btn.getAttribute( 'data-delete-plan' );
				if ( ! window.confirm( __( 'Delete this plan?', 'masjidos' ) ) ) {
					return;
				}
				api( 'minbar/plans/' + id, { method: 'DELETE' } ).then( function ( res ) {
					state.minbarPlans = res.plans || [];
					return loadMinbarDash( api );
				} ).then( function () {
					render();
					switchTab( 'minbar', 'replace' );
				} );
			} );
		} );

		app.querySelectorAll( '[data-plan-to-archive]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var id = btn.getAttribute( 'data-plan-to-archive' );
				var plan = ( state.minbarPlans || [] ).filter( function ( p ) { return p.id === id; } )[0];
				if ( ! plan ) {
					return;
				}
				var khatib = ( state.minbarProfiles || [] ).filter( function ( p ) { return p.is_active; } )[0];
				api( 'khutbah', {
					method: 'POST',
					body: JSON.stringify( {
						date: plan.date,
						topic: plan.topic,
						khatib: khatib ? khatib.name : __( 'TBA', 'masjidos' ),
						category: plan.category || '',
						summary: plan.notes || '',
						is_public: 1
					} )
				} ).then( function () {
					return loadKhutbahs( api );
				} ).then( function () {
					state.minbarTab = 'archive';
					render();
					switchTab( 'minbar', 'replace' );
				} ).catch( function ( error ) {
					window.alert( error.message );
				} );
			} );
		} );

		// References
		app.querySelectorAll( '[data-search-refs]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var qEl = document.getElementById( 'itmms-ref-q' );
				var tEl = document.getElementById( 'itmms-ref-type' );
				state.minbarRefQuery = qEl ? qEl.value : '';
				state.minbarRefType = tEl ? tEl.value : 'all';
				api( 'minbar/references?q=' + encodeURIComponent( state.minbarRefQuery ) + '&type=' + encodeURIComponent( state.minbarRefType ) ).then( function ( res ) {
					state.minbarRefResults = res.results || [];
					state.minbarBookmarks = res.bookmarks || state.minbarBookmarks || [];
					render();
					switchTab( 'minbar', 'replace' );
				} );
			} );
		} );

		app.querySelectorAll( '[data-copy-ref]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var text = btn.getAttribute( 'data-copy-ref' ) || '';
				if ( navigator.clipboard && navigator.clipboard.writeText ) {
					navigator.clipboard.writeText( text );
				}
			} );
		} );

		app.querySelectorAll( '[data-add-bookmark]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var card = btn.closest( '.itmms-minbar-ref' );
				var payload = card ? card.getAttribute( 'data-ref-payload' ) : '';
				if ( ! payload ) {
					return;
				}
				var ref;
				try {
					ref = JSON.parse( decodeURIComponent( payload ) );
				} catch ( e ) {
					return;
				}
				api( 'minbar/bookmarks', { method: 'POST', body: JSON.stringify( ref ) } ).then( function ( res ) {
					state.minbarBookmarks = res.bookmarks || [];
					render();
					switchTab( 'minbar', 'replace' );
				} );
			} );
		} );

		app.querySelectorAll( '[data-remove-bookmark]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var id = btn.getAttribute( 'data-remove-bookmark' );
				api( 'minbar/bookmarks/' + id, { method: 'DELETE' } ).then( function ( res ) {
					state.minbarBookmarks = res.bookmarks || [];
					render();
					switchTab( 'minbar', 'replace' );
				} );
			} );
		} );

		// Sermon builder
		var sermonForm = document.getElementById( 'itmms-sermon-form' );
		if ( sermonForm ) {
			sermonForm.querySelectorAll( '[data-sermon-field]' ).forEach( function ( ta ) {
				ta.addEventListener( 'input', function () {
					state.sermonDraft = collectSermon( sermonForm );
					updateSermonWordCounts( sermonForm );
				} );
			} );
			sermonForm.addEventListener( 'submit', function ( event ) {
				event.preventDefault();
				var draft = collectSermon( sermonForm );
				var status = document.getElementById( 'itmms-sermon-status' );
				var outline = {
					intro: draft.intro,
					point1: draft.point1,
					point2: draft.point2,
					point3: draft.point3,
					conclusion: draft.conclusion,
					second: draft.second
				};
				var kid = Number( draft.khutbah_id );
				var payload;
				var req;
				if ( kid > 0 ) {
					payload = { outline: outline, topic: draft.topic };
					req = api( 'khutbah/' + kid, { method: 'PUT', body: JSON.stringify( payload ) } );
				} else {
					payload = {
						date: new Date().toISOString().slice( 0, 10 ),
						topic: draft.topic,
						khatib: __( 'TBA', 'masjidos' ),
						outline: outline,
						is_public: 0,
						summary: ''
					};
					req = api( 'khutbah', { method: 'POST', body: JSON.stringify( payload ) } );
				}
				if ( status ) { status.textContent = __( 'Saving...', 'masjidos' ); }
				req.then( function () {
					return loadKhutbahs( api );
				} ).then( function () {
					if ( status ) { status.textContent = __( 'Outline saved.', 'masjidos' ); }
					state.sermonDraft = draft;
				} ).catch( function ( error ) {
					if ( status ) { status.textContent = error.message; }
				} );
			} );
		}

		app.querySelectorAll( '[data-print-sermon]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var form = document.getElementById( 'itmms-sermon-form' );
				if ( ! form ) {
					return;
				}
				var draft = collectSermon( form );
				var printEl = document.getElementById( 'itmms-sermon-print' );
				if ( ! printEl ) {
					return;
				}
				printEl.hidden = false;
				printEl.innerHTML = '<h1>' + esc( draft.topic ) + '</h1>' +
					'<h3>' + esc( __( 'Introduction', 'masjidos' ) ) + '</h3><p>' + esc( draft.intro ).replace( /\n/g, '<br>' ) + '</p>' +
					'<h3>' + esc( __( 'Main point 1', 'masjidos' ) ) + '</h3><p>' + esc( draft.point1 ).replace( /\n/g, '<br>' ) + '</p>' +
					'<h3>' + esc( __( 'Main point 2', 'masjidos' ) ) + '</h3><p>' + esc( draft.point2 ).replace( /\n/g, '<br>' ) + '</p>' +
					'<h3>' + esc( __( 'Main point 3', 'masjidos' ) ) + '</h3><p>' + esc( draft.point3 ).replace( /\n/g, '<br>' ) + '</p>' +
					'<h3>' + esc( __( 'Conclusion', 'masjidos' ) ) + '</h3><p>' + esc( draft.conclusion ).replace( /\n/g, '<br>' ) + '</p>' +
					'<h3>' + esc( __( 'Second khutbah', 'masjidos' ) ) + '</h3><p>' + esc( draft.second ).replace( /\n/g, '<br>' ) + '</p>';
				window.print();
				printEl.hidden = true;
			} );
		} );

		app.querySelectorAll( '[data-ai-assist]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				if ( btn.disabled ) {
					window.alert( __( 'AI Assistant is a MasjidOS Pro feature.', 'masjidos' ) );
				}
			} );
		} );

		// Profiles / schedule
		var profileForm = document.getElementById( 'itmms-profile-form' );
		if ( profileForm ) {
			profileForm.addEventListener( 'submit', function ( event ) {
				event.preventDefault();
				var fields = new FormData( profileForm );
				var payload = {};
				fields.forEach( function ( value, key ) { payload[ key ] = value; } );
				payload.is_active = profileForm.querySelector( '[name="is_active"]' ) && profileForm.querySelector( '[name="is_active"]' ).checked ? 1 : 0;
				var editing = state.editingProfile;
				var path = editing ? 'minbar/profiles/' + editing.id : 'minbar/profiles';
				api( path, { method: editing ? 'PUT' : 'POST', body: JSON.stringify( payload ) } ).then( function () {
					state.editingProfile = null;
					return loadProfiles( api );
				} ).then( function () {
					render();
					switchTab( 'minbar', 'replace' );
				} ).catch( function ( error ) {
					window.alert( error.message );
				} );
			} );
		}

		app.querySelectorAll( '[data-edit-profile]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var id = Number( btn.getAttribute( 'data-edit-profile' ) );
				state.editingProfile = ( state.minbarProfiles || [] ).filter( function ( p ) { return Number( p.id ) === id; } )[0] || null;
				render();
				switchTab( 'minbar', 'replace' );
			} );
		} );

		app.querySelectorAll( '[data-copy-profile]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var id = Number( btn.getAttribute( 'data-copy-profile' ) );
				var profile = ( state.minbarProfiles || [] ).filter( function ( p ) { return Number( p.id ) === id; } )[0];
				if ( ! profile ) {
					return;
				}
				btn.disabled = true;
				var payload = {
					name: ( profile.name || __( 'Untitled khatib', 'masjidos' ) ) + ' ' + __( '(copy)', 'masjidos' ),
					title: profile.title || '',
					phone: profile.phone || '',
					email: profile.email || '',
					photo_url: profile.photo_url || '',
					expertise: profile.expertise || '',
					languages: profile.languages || '',
					location: profile.location || '',
					website: profile.website || '',
					facebook_url: profile.facebook_url || '',
					youtube_url: profile.youtube_url || '',
					instagram_url: profile.instagram_url || '',
					linkedin_url: profile.linkedin_url || '',
					x_url: profile.x_url || '',
					tiktok_url: profile.tiktok_url || '',
					bio: profile.bio || '',
					is_active: profile.is_active ? 1 : 0
				};
				api( 'minbar/profiles', { method: 'POST', body: JSON.stringify( payload ) } ).then( function ( created ) {
					state.editingProfile = ( created && created.profile ) ? created.profile : null;
					return loadProfiles( api );
				} ).then( function () {
					render();
					switchTab( 'minbar', 'replace' );
				} ).catch( function ( error ) {
					btn.disabled = false;
					window.alert( error.message );
				} );
			} );
		} );

		app.querySelectorAll( '[data-cancel-profile]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				state.editingProfile = null;
				render();
				switchTab( 'minbar', 'replace' );
			} );
		} );

		app.querySelectorAll( '[data-delete-profile]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var id = Number( btn.getAttribute( 'data-delete-profile' ) );
				if ( ! window.confirm( __( 'Delete this khatib profile?', 'masjidos' ) ) ) {
					return;
				}
				api( 'minbar/profiles/' + id, { method: 'DELETE' } ).then( function () {
					return loadProfiles( api );
				} ).then( function () {
					render();
					switchTab( 'minbar', 'replace' );
				} );
			} );
		} );

		var scheduleForm = document.getElementById( 'itmms-schedule-form' );
		if ( scheduleForm ) {
			scheduleForm.addEventListener( 'submit', function ( event ) {
				event.preventDefault();
				var fields = new FormData( scheduleForm );
				var payload = {};
				fields.forEach( function ( value, key ) { payload[ key ] = value; } );
				var editing = state.editingSchedule;
				var path = editing ? 'minbar/schedule/' + editing.id : 'minbar/schedule';
				api( path, { method: editing ? 'PUT' : 'POST', body: JSON.stringify( payload ) } ).then( function () {
					state.editingSchedule = null;
					return loadSchedule( api );
				} ).then( function () {
					return loadMinbarDash( api );
				} ).then( function () {
					render();
					switchTab( 'minbar', 'replace' );
				} ).catch( function ( error ) {
					window.alert( error.message );
				} );
			} );
		}

		app.querySelectorAll( '[data-edit-schedule]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var id = Number( btn.getAttribute( 'data-edit-schedule' ) );
				state.editingSchedule = ( state.minbarSchedule || [] ).filter( function ( s ) { return Number( s.id ) === id; } )[0] || null;
				render();
				switchTab( 'minbar', 'replace' );
			} );
		} );

		app.querySelectorAll( '[data-copy-schedule]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var id = Number( btn.getAttribute( 'data-copy-schedule' ) );
				var row = ( state.minbarSchedule || [] ).filter( function ( s ) { return Number( s.id ) === id; } )[0];
				if ( ! row ) {
					return;
				}
				var taken = {};
				( state.minbarSchedule || [] ).forEach( function ( s ) {
					taken[ s.scheduled_date ] = true;
				} );
				var slots = nextFridays( 16 );
				var copyDate = row.scheduled_date;
				for ( var i = 0; i < slots.length; i++ ) {
					if ( ! taken[ slots[ i ].date ] ) {
						copyDate = slots[ i ].date;
						break;
					}
				}
				btn.disabled = true;
				api( 'minbar/schedule', {
					method: 'POST',
					body: JSON.stringify( {
						khatib_id: row.khatib_id,
						scheduled_date: copyDate,
						type: row.type || 'jumuah',
						topic: row.topic || '',
						status: row.status || 'confirmed',
						notes: row.notes || ''
					} )
				} ).then( function ( res ) {
					state.editingSchedule = ( res && res.entry ) ? res.entry : null;
					return loadSchedule( api );
				} ).then( function () {
					return loadMinbarDash( api );
				} ).then( function () {
					render();
					switchTab( 'minbar', 'replace' );
				} ).catch( function ( error ) {
					btn.disabled = false;
					window.alert( error.message );
				} );
			} );
		} );

		app.querySelectorAll( '[data-cancel-schedule]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				state.editingSchedule = null;
				render();
				switchTab( 'minbar', 'replace' );
			} );
		} );

		app.querySelectorAll( '[data-delete-schedule]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var id = Number( btn.getAttribute( 'data-delete-schedule' ) );
				if ( ! window.confirm( __( 'Delete this schedule entry?', 'masjidos' ) ) ) {
					return;
				}
				api( 'minbar/schedule/' + id, { method: 'DELETE' } ).then( function () {
					return loadSchedule( api );
				} ).then( function () {
					render();
					switchTab( 'minbar', 'replace' );
				} );
			} );
		} );
	}

	function collectSermon( form ) {
		var fields = new FormData( form );
		return {
			khutbah_id: fields.get( 'khutbah_id' ) || 0,
			topic: fields.get( 'topic' ) || '',
			intro: fields.get( 'intro' ) || '',
			point1: fields.get( 'point1' ) || '',
			point2: fields.get( 'point2' ) || '',
			point3: fields.get( 'point3' ) || '',
			conclusion: fields.get( 'conclusion' ) || '',
			second: fields.get( 'second' ) || ''
		};
	}

	function updateSermonWordCounts( form ) {
		var draft = collectSermon( form );
		var keys = [ 'intro', 'point1', 'point2', 'point3', 'conclusion', 'second' ];
		keys.forEach( function ( key ) {
			var el = form.querySelector( '[data-section-wc="' + key + '"]' );
			if ( el ) {
				el.textContent = String( countWords( draft[ key ] || '' ) );
			}
		} );
		var total = countWords( keys.map( function ( k ) { return draft[ k ] || ''; } ).join( ' ' ) );
		var pct = Math.min( 100, Math.round( ( total / 1200 ) * 100 ) );
		var mins = Math.max( 1, Math.round( total / 130 ) );
		var fill = document.getElementById( 'itmms-sermon-wc-fill' );
		var countEl = document.getElementById( 'itmms-sermon-wc-count' );
		var timeEl = document.getElementById( 'itmms-sermon-wc-time' );
		if ( fill ) {
			fill.style.width = pct + '%';
		}
		if ( countEl ) {
			countEl.textContent = String( total );
		}
		if ( timeEl ) {
			timeEl.textContent = '~' + mins + ' ' + __( 'min', 'masjidos' );
		}
	}

	function saveKhutbah( api, render, switchTab, payload, trigger ) {
		var state = window.itmms.state;
		var status = document.getElementById( 'itmms-khutbah-status' );
		var editing = Number( state.editingKhutbah );
		if ( trigger ) {
			trigger.disabled = true;
		}
		if ( status ) {
			status.textContent = __( 'Saving...', 'masjidos' );
		}
		api( editing ? 'khutbah/' + editing : 'khutbah', {
			method: editing ? 'PUT' : 'POST',
			body: JSON.stringify( payload )
		} ).then( function ( res ) {
			state.editingKhutbah = 0;
			state.khutbahSimilar = ( res && res.similar ) || [];
			return loadKhutbahs( api );
		} ).then( function () {
			return loadMinbarDash( api );
		} ).then( function () {
			state.minbarTab = 'archive';
			render();
			switchTab( 'minbar', 'replace' );
			var saved = document.getElementById( 'itmms-khutbah-status' );
			if ( saved ) {
				saved.textContent = editing ? __( 'Khutbah updated.', 'masjidos' ) : __( 'Khutbah saved.', 'masjidos' );
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

	function loadKhutbahs( api ) {
		return api( 'khutbah' ).then( function ( response ) {
			window.itmms.state.khutbahs = response.khutbahs || [];
			if ( response.categories ) {
				window.itmms.state.khutbahCategories = response.categories;
			}
			if ( response.stats ) {
				window.itmms.state.khutbahStats = response.stats;
			}
		} );
	}

	function loadMinbarDash( api ) {
		return api( 'minbar/dashboard' ).then( function ( response ) {
			window.itmms.state.minbarDash = response || {};
			if ( response.categories ) {
				window.itmms.state.khutbahCategories = response.categories;
			}
		} ).catch( function () {} );
	}

	function loadProfiles( api ) {
		return api( 'minbar/profiles' ).then( function ( response ) {
			window.itmms.state.minbarProfiles = response.profiles || [];
		} );
	}

	function loadSchedule( api ) {
		return api( 'minbar/schedule' ).then( function ( response ) {
			window.itmms.state.minbarSchedule = response.schedule || [];
		} );
	}

	function loadPlans( api ) {
		return api( 'minbar/plans' ).then( function ( response ) {
			window.itmms.state.minbarPlans = response.plans || [];
		} );
	}

	function loadBookmarks( api ) {
		return api( 'minbar/bookmarks' ).then( function ( response ) {
			window.itmms.state.minbarBookmarks = response.bookmarks || [];
		} );
	}

	window.itmms.minbar.loadKhutbahs = loadKhutbahs;
	window.itmms.minbar.loadMinbarDash = loadMinbarDash;
	window.itmms.minbar.loadProfiles = loadProfiles;
	window.itmms.minbar.loadSchedule = loadSchedule;
	window.itmms.minbar.loadPlans = loadPlans;
	window.itmms.minbar.loadBookmarks = loadBookmarks;
	window.itmms.minbar.refreshMinbar = refreshMinbar;
} )();
