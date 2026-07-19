/**
 * Split ITMMS_REST into trait files. Usage: node _future/tools/split-rest-traits.js
 */
const fs = require( 'node:fs' );
const path = require( 'node:path' );

const root = path.resolve( __dirname, '..', '..' );
const restFile = path.join( root, 'includes/class-itmms-rest.php' );
const src = fs.readFileSync( restFile, 'utf8' );
const lines = src.split( /\r?\n/ );

const methods = [];
for ( let i = 0; i < lines.length; i++ ) {
	const m = lines[ i ].match( /^\t(public|private|protected) function (\w+)/ );
	if ( m ) {
		methods.push( { line: i, name: m[ 2 ], vis: m[ 1 ] } );
	}
}

let classEnd = lines.length - 1;
while ( classEnd > 0 && lines[ classEnd ].trim() !== '}' ) {
	classEnd--;
}

for ( let i = 0; i < methods.length; i++ ) {
	const start = methods[ i ].line;
	let docStart = start;
	while (
		docStart > 0 &&
		( lines[ docStart - 1 ].trim().startsWith( '*' ) ||
			lines[ docStart - 1 ].trim().startsWith( '/**' ) ||
			lines[ docStart - 1 ].trim() === '' )
	) {
		if ( lines[ docStart - 1 ].trim().startsWith( '/**' ) ) {
			docStart--;
			break;
		}
		docStart--;
	}
	if ( ! lines[ docStart ].trim().startsWith( '/**' ) ) {
		docStart = start;
	}
	methods[ i ].docStart = docStart;
}

for ( let i = 0; i < methods.length; i++ ) {
	const nextStart = i + 1 < methods.length ? methods[ i + 1 ].docStart : classEnd;
	methods[ i ].end = nextStart - 1;
	while ( methods[ i ].end > methods[ i ].docStart && lines[ methods[ i ].end ].trim() === '' ) {
		methods[ i ].end--;
	}
}

const groups = {
	permissions: [
		'can_read',
		'can_manage_settings',
		'can_manage_announcements',
		'can_manage_events',
		'can_manage_khutbah',
		'can_manage_prayers',
	],
	dashboard: [
		'get_dashboard',
		'dismiss_welcome',
		'get_settings',
		'update_settings',
	],
	prayer: [
		'get_prayer_times_today',
		'get_prayer_times_date',
		'get_prayer_times_month_json',
		'get_salahapi_document',
		'get_salahapi_csv',
		'get_prayer_timetable',
		'import_prayer_timetable',
		'export_prayer_timetable',
		'sample_prayer_timetable',
		'clear_prayer_timetable',
	],
	widgets: [
		'public_cached_response',
		'widget_preview_args',
		'yes_no_param',
		'short_text_param',
		'get_prayer_widget',
		'get_monthly_prayer_widget',
		'get_jumuah_widget',
		'get_announcements_widget',
		'get_events_widget',
		'get_duas_azkar_widget',
		'get_calendar_widget',
	],
	content: [
		'get_announcements',
		'get_public_announcements',
		'create_announcement',
		'update_announcement',
		'delete_announcement',
		'get_events',
		'get_public_events',
		'create_event',
		'update_event',
		'delete_event',
	],
	minbar: [
		'get_khutbahs',
		'create_khutbah',
		'update_khutbah',
		'delete_khutbah',
		'get_minbar_dashboard',
		'get_minbar_profiles',
		'create_minbar_profile',
		'update_minbar_profile',
		'delete_minbar_profile',
		'get_minbar_schedule',
		'create_minbar_schedule',
		'update_minbar_schedule',
		'delete_minbar_schedule',
		'get_minbar_plans',
		'save_minbar_plan',
		'delete_minbar_plan',
		'search_minbar_references',
		'get_minbar_bookmarks',
		'add_minbar_bookmark',
		'delete_minbar_bookmark',
	],
};

const byName = Object.fromEntries( methods.map( ( m ) => [ m.name, m ] ) );
const extracted = new Set();

function sliceMethods( names ) {
	const chunks = [];
	for ( const name of names ) {
		const m = byName[ name ];
		if ( ! m ) {
			console.error( 'MISSING', name );
			continue;
		}
		extracted.add( name );
		chunks.push( lines.slice( m.docStart, m.end + 1 ).join( '\n' ) );
	}
	return chunks.join( '\n\n' );
}

function writeTrait( file, traitName, body ) {
	const out = [
		'<?php',
		'/**',
		` * ${ traitName } methods for ITMMS_REST.`,
		' *',
		' * @package MasjidOS',
		' */',
		'',
		"defined( 'ABSPATH' ) || exit;",
		'',
		'/**',
		' * @package MasjidOS',
		' */',
		`trait ${ traitName } {`,
		'',
		body,
		'',
		'}',
		'',
	].join( '\n' );
	fs.writeFileSync( path.join( root, file ), out, 'utf8' );
	console.log( 'Wrote', file, '(' + body.split( '\n' ).length + ' lines body)' );
}

fs.mkdirSync( path.join( root, 'includes/rest' ), { recursive: true } );

writeTrait( 'includes/rest/trait-itmms-rest-permissions.php', 'ITMMS_REST_Permissions', sliceMethods( groups.permissions ) );
writeTrait( 'includes/rest/trait-itmms-rest-dashboard.php', 'ITMMS_REST_Dashboard', sliceMethods( groups.dashboard ) );
writeTrait( 'includes/rest/trait-itmms-rest-prayer.php', 'ITMMS_REST_Prayer', sliceMethods( groups.prayer ) );
writeTrait( 'includes/rest/trait-itmms-rest-widgets.php', 'ITMMS_REST_Widgets', sliceMethods( groups.widgets ) );
writeTrait( 'includes/rest/trait-itmms-rest-content.php', 'ITMMS_REST_Content', sliceMethods( groups.content ) );
writeTrait( 'includes/rest/trait-itmms-rest-minbar.php', 'ITMMS_REST_Minbar', sliceMethods( groups.minbar ) );

const keep = methods.filter( ( m ) => ! extracted.has( m.name ) );
const firstKeep = keep[ 0 ];
let header = lines.slice( 0, firstKeep.docStart ).join( '\n' );
header = header.replace(
	/final class ITMMS_REST \{/,
	[
		'final class ITMMS_REST {',
		'',
		'\tuse ITMMS_REST_Permissions;',
		'\tuse ITMMS_REST_Dashboard;',
		'\tuse ITMMS_REST_Prayer;',
		'\tuse ITMMS_REST_Widgets;',
		'\tuse ITMMS_REST_Content;',
		'\tuse ITMMS_REST_Minbar;',
	].join( '\n' )
);

const body = keep.map( ( m ) => lines.slice( m.docStart, m.end + 1 ).join( '\n' ) ).join( '\n\n' );
const outMain = `${ header }\n${ body }\n}\n`;
fs.writeFileSync( restFile, outMain, 'utf8' );
console.log( 'Main kept:', keep.map( ( m ) => m.name ).join( ', ' ) );
console.log( 'Main lines:', outMain.split( '\n' ).length );
