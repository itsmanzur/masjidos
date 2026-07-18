/**
 * Merge existing bn_BD.po + JSON, fill missing POT strings with msgid fallback, then rebuild pack.
 */
const fs = require( 'node:fs' );
const path = require( 'node:path' );
const { spawnSync } = require( 'node:child_process' );

const pluginRoot = path.resolve( __dirname, '..', '..' );
const languagesDir = path.join( pluginRoot, 'languages' );
const potPath = path.join( languagesDir, 'masjidos.pot' );
const poPath = path.join( languagesDir, 'masjidos-bn_BD.po' );
const translationsPath = path.join( __dirname, 'bn_BD-translations.json' );

function parseQuoted( line ) {
	return JSON.parse( line.slice( line.indexOf( '"' ) ) );
}

function parsePotMsgids( pot ) {
	return pot.split( /\r?\n\r?\n/ ).flatMap( ( block ) => {
		if ( ! block.includes( 'msgid ' ) || block.startsWith( 'msgid ""' ) ) {
			return [];
		}
		const lines = block.split( /\r?\n/ );
		const msgidLine = lines.find( ( line ) => line.startsWith( 'msgid ' ) );
		const pluralLine = lines.find( ( line ) => line.startsWith( 'msgid_plural ' ) );
		return [ {
			msgid: parseQuoted( msgidLine ),
			plural: pluralLine ? parseQuoted( pluralLine ) : '',
		} ];
	} );
}

function parsePoTranslations( po ) {
	const map = {};
	const blocks = po.split( /\r?\n\r?\n/ );
	for ( const block of blocks ) {
		if ( ! block.includes( 'msgid ' ) || block.trimStart().startsWith( 'msgid ""' ) ) {
			continue;
		}
		const lines = block.split( /\r?\n/ );
		const msgidLine = lines.find( ( line ) => line.startsWith( 'msgid ' ) );
		if ( ! msgidLine ) {
			continue;
		}
		const msgid = parseQuoted( msgidLine );
		if ( ! msgid ) {
			continue;
		}
		const pluralLine = lines.find( ( line ) => line.startsWith( 'msgid_plural ' ) );
		if ( pluralLine ) {
			const m0 = lines.find( ( line ) => line.startsWith( 'msgstr[0] ' ) );
			const m1 = lines.find( ( line ) => line.startsWith( 'msgstr[1] ' ) );
			if ( m0 && m1 ) {
				const a = parseQuoted( m0 );
				const b = parseQuoted( m1 );
				if ( a.trim() && b.trim() ) {
					map[ msgid ] = [ a, b ];
				}
			}
			continue;
		}
		const msgstrLine = lines.find( ( line ) => line.startsWith( 'msgstr ' ) );
		if ( msgstrLine ) {
			const value = parseQuoted( msgstrLine );
			if ( value.trim() ) {
				map[ msgid ] = value;
			}
		}
	}
	return map;
}

const pot = fs.readFileSync( potPath, 'utf8' );
const entries = parsePotMsgids( pot );
const fromJson = JSON.parse( fs.readFileSync( translationsPath, 'utf8' ) );
const fromPo = fs.existsSync( poPath ) ? parsePoTranslations( fs.readFileSync( poPath, 'utf8' ) ) : {};

const merged = { ...fromJson };
let addedFromPo = 0;
let filledFallback = 0;

for ( const [ key, value ] of Object.entries( fromPo ) ) {
	if ( ! Object.prototype.hasOwnProperty.call( merged, key ) ) {
		merged[ key ] = value;
		addedFromPo += 1;
	}
}

for ( const entry of entries ) {
	if ( Object.prototype.hasOwnProperty.call( merged, entry.msgid ) ) {
		continue;
	}
	if ( entry.plural ) {
		merged[ entry.msgid ] = [ entry.msgid, entry.plural ];
	} else {
		merged[ entry.msgid ] = entry.msgid;
	}
	filledFallback += 1;
}

fs.writeFileSync( translationsPath, `${ JSON.stringify( merged, null, '\t' ) }\n`, 'utf8' );
console.log( `Synced JSON: +${ addedFromPo } from PO, +${ filledFallback } English fallbacks, total ${ Object.keys( merged ).length }` );

const result = spawnSync( process.execPath, [ path.join( __dirname, 'build-bn-pack.js' ) ], {
	cwd: pluginRoot,
	stdio: 'inherit',
} );
process.exit( result.status || 0 );
