/**
 * Split ITMMS_Public into trait files. Usage: node _future/tools/split-public-traits.js
 */
const fs = require( 'node:fs' );
const path = require( 'node:path' );

const root = path.resolve( __dirname, '..', '..' );
const publicFile = path.join( root, 'public/class-itmms-public.php' );
const src = fs.readFileSync( publicFile, 'utf8' );
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
	helpers: [
		'normalize_language',
		'translate_for_language',
		'render_localized_empty_state',
		'empty_state_msgids_for_i18n',
		'labels',
		'prayer_label',
		'jumuah_labels',
		'jumuah_session_label',
		'monthly_labels',
		'build_prayer_trust_items',
		'prayer_source_label',
		'hijri_label_for_date',
		'announcement_labels',
		'month_names',
		'weekday_label',
		'indexed_prayers',
		'format_time',
		'initials',
		'render_announcement_empty_state',
		'enqueue_assets',
		'safe_kses',
	],
	designs: [
		'get_designs',
		'get_jumuah_designs',
		'get_monthly_designs',
		'get_announcement_designs',
		'normalize_design',
		'render_locked_design_notice',
		'render_locked_jumuah_design_notice',
		'render_locked_monthly_design_notice',
		'render_locked_announcement_design_notice',
		'render_pro_lock_notice',
	],
	blocks: [
		'register_blocks',
		'render_prayer_times_block',
		'render_islamic_calendar_block',
		'render_monthly_prayer_times_block',
		'render_jumuah_block',
		'render_announcements_block',
		'render_events_block',
		'render_duas_azkar_block',
		'render_khutbah_archive_block',
		'render_khatib_this_week_block',
		'render_upcoming_khutbah_block',
		'render_khutbah_search_block',
		'render_quran_verse_block',
		'render_hadith_block',
		'render_allah_names_block',
		'render_audio_quran_block',
		'render_articles_block',
		'enqueue_block_editor_assets',
	],
	display: [
		'register_display_rewrites',
		'register_display_query_vars',
		'handle_display_template_redirect',
		'event_calendar_links',
		'handle_ical_export_redirect',
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
		` * ${ traitName } methods for ITMMS_Public.`,
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

writeTrait( 'public/trait-itmms-public-helpers.php', 'ITMMS_Public_Helpers', sliceMethods( groups.helpers ) );
writeTrait( 'public/trait-itmms-public-designs.php', 'ITMMS_Public_Designs', sliceMethods( groups.designs ) );
writeTrait( 'public/trait-itmms-public-blocks.php', 'ITMMS_Public_Blocks', sliceMethods( groups.blocks ) );
writeTrait( 'public/trait-itmms-public-display.php', 'ITMMS_Public_Display', sliceMethods( groups.display ) );

const keep = methods.filter( ( m ) => ! extracted.has( m.name ) );
const firstKeep = keep[ 0 ];
let header = lines.slice( 0, firstKeep.docStart ).join( '\n' );
header = header.replace(
	/final class ITMMS_Public \{/,
	[
		'final class ITMMS_Public {',
		'',
		'\tuse ITMMS_Public_Helpers;',
		'\tuse ITMMS_Public_Designs;',
		'\tuse ITMMS_Public_Blocks;',
		'\tuse ITMMS_Public_Display;',
	].join( '\n' )
);

const body = keep.map( ( m ) => lines.slice( m.docStart, m.end + 1 ).join( '\n' ) ).join( '\n\n' );
const outMain = `${ header }\n${ body }\n}\n`;
fs.writeFileSync( publicFile, outMain, 'utf8' );
console.log( 'Main kept:', keep.map( ( m ) => m.name ).join( ', ' ) );
console.log( 'Main lines:', outMain.split( '\n' ).length );
