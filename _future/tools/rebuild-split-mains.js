/**
 * Rebuild slim Public/REST main classes from current monoliths,
 * keeping already-extracted trait files on disk.
 *
 * Usage: node _future/tools/rebuild-split-mains.js
 */
const fs = require( 'node:fs' );
const path = require( 'node:path' );

const root = path.resolve( __dirname, '..', '..' );

function parseMethods( filePath ) {
	const lines = fs.readFileSync( filePath, 'utf8' ).split( /\r?\n/ );
	const methods = [];
	for ( let i = 0; i < lines.length; i++ ) {
		const m = lines[ i ].match( /^\t((?:public|private|protected)(?:\s+static)?)\s+function\s+(\w+)/ );
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
	return { lines, methods, classEnd };
}

function stripOrphanDocblocks( filePath ) {
	let text = fs.readFileSync( filePath, 'utf8' );
	// Remove a trailing orphaned docblock just before trait closing brace.
	text = text.replace( /\n\t\/\*\*[\s\S]*?\*\/\s*\n\}(\s*)$/, '\n}\n' );
	// Remove orphaned docblock between two methods (docblock with no following function).
	text = text.replace( /\n(\t\/\*\*[\s\S]*?\*\/)\n+(\t\/\*\*)/g, '\n$2' );
	fs.writeFileSync( filePath, text, 'utf8' );
}

function rebuildPublic() {
	const file = path.join( root, 'public/class-itmms-public.php' );
	const { lines, methods } = parseMethods( file );
	const keepNames = new Set( [
		'get_instance',
		'__construct',
		'__clone',
		'init',
		'render_prayer_times_shortcode',
		'render_monthly_prayer_times_shortcode',
		'render_islamic_calendar_shortcode',
		'render_duas_azkar_shortcode',
		'render_announcements_shortcode',
		'render_events_shortcode',
		'render_jumuah_shortcode',
		'render_khutbah_archive_shortcode',
		'render_khatib_this_week_shortcode',
		'render_upcoming_khutbah_shortcode',
		'render_khutbah_search_shortcode',
		'render_quran_verse_shortcode',
		'render_hadith_shortcode',
		'render_allah_names_shortcode',
		'render_audio_quran_shortcode',
		'render_articles_shortcode',
	] );
	const keep = methods.filter( ( m ) => keepNames.has( m.name ) );
	const byName = Object.fromEntries( methods.map( ( m ) => [ m.name, m ] ) );
	const ordered = [ ...keepNames ].map( ( name ) => byName[ name ] ).filter( Boolean );

	const propertyBlock = [
		'\t/** @var ITMMS_Public|null */',
		'\tprivate static ?ITMMS_Public $instance = null;',
		'',
	].join( '\n' );

	const body = ordered
		.map( ( m ) => lines.slice( m.docStart, m.end + 1 ).join( '\n' ) )
		.join( '\n\n' );

	const out = [
		'<?php',
		'/**',
		' * Public shortcodes and assets.',
		' *',
		' * @package MasjidOS',
		' */',
		'',
		"defined( 'ABSPATH' ) || exit;",
		'',
		'/**',
		' * Public-facing bootstrap.',
		' */',
		'final class ITMMS_Public {',
		'',
		'\tuse ITMMS_Public_Helpers;',
		'\tuse ITMMS_Public_Designs;',
		'\tuse ITMMS_Public_Blocks;',
		'\tuse ITMMS_Public_Display;',
		'',
		propertyBlock,
		body,
		'}',
		'',
	].join( '\n' );

	fs.writeFileSync( file, out, 'utf8' );
	console.log( 'Rebuilt public main,', out.split( '\n' ).length, 'lines; methods:', ordered.map( ( m ) => m.name ).join( ', ' ) );
}

function rebuildRest() {
	const file = path.join( root, 'includes/class-itmms-rest.php' );
	const { lines, methods } = parseMethods( file );
	const byName = Object.fromEntries( methods.map( ( m ) => [ m.name, m ] ) );
	const ordered = [ 'init', 'register_routes' ].map( ( name ) => byName[ name ] ).filter( Boolean );

	const body = ordered
		.map( ( m ) => lines.slice( m.docStart, m.end + 1 ).join( '\n' ) )
		.join( '\n\n' );

	const out = [
		'<?php',
		'/**',
		' * REST API endpoints for the MasjidOS admin app.',
		' *',
		' * @package MasjidOS',
		' */',
		'',
		"defined( 'ABSPATH' ) || exit;",
		'',
		'/**',
		' * Registers REST routes.',
		' */',
		'final class ITMMS_REST {',
		'',
		'\tuse ITMMS_REST_Permissions;',
		'\tuse ITMMS_REST_Dashboard;',
		'\tuse ITMMS_REST_Prayer;',
		'\tuse ITMMS_REST_Widgets;',
		'\tuse ITMMS_REST_Content;',
		'\tuse ITMMS_REST_Minbar;',
		'',
		"\tprivate const NAMESPACE = 'masjidos/v1';",
		'',
		body,
		'}',
		'',
	].join( '\n' );

	fs.writeFileSync( file, out, 'utf8' );
	console.log( 'Rebuilt REST main,', out.split( '\n' ).length, 'lines' );
}

[
	'public/trait-itmms-public-helpers.php',
	'public/trait-itmms-public-designs.php',
	'public/trait-itmms-public-blocks.php',
	'public/trait-itmms-public-display.php',
	'includes/rest/trait-itmms-rest-permissions.php',
	'includes/rest/trait-itmms-rest-dashboard.php',
	'includes/rest/trait-itmms-rest-prayer.php',
	'includes/rest/trait-itmms-rest-widgets.php',
	'includes/rest/trait-itmms-rest-content.php',
	'includes/rest/trait-itmms-rest-minbar.php',
].forEach( ( rel ) => {
	const full = path.join( root, rel );
	if ( fs.existsSync( full ) ) {
		stripOrphanDocblocks( full );
		console.log( 'Cleaned', rel );
	}
} );

rebuildPublic();
rebuildRest();
