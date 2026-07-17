/**
 * Build Arabic (ar) translation packs with English fallback for missing strings.
 *
 * Usage: node _future/tools/build-ar-pack.js
 */
const fs = require( 'node:fs' );
const path = require( 'node:path' );

const pluginRoot = path.resolve( __dirname, '..', '..' );
const languagesDir = path.join( pluginRoot, 'languages' );
const potPath = path.join( languagesDir, 'masjidos.pot' );
const translationsPath = path.join( __dirname, 'ar-translations.json' );
const translations = JSON.parse( fs.readFileSync( translationsPath, 'utf8' ) );
const pluralForms = 'nplurals=6; plural=(n==0 ? 0 : n==1 ? 1 : n==2 ? 2 : n%100>=3 && n%100<=10 ? 3 : n%100>=11 ? 4 : 5);';
const locale = 'ar';

function parseQuoted( line ) {
	return JSON.parse( line.slice( line.indexOf( '"' ) ) );
}

function parsePot() {
	return fs.readFileSync( potPath, 'utf8' ).split( /\r?\n\r?\n/ ).flatMap( ( block ) => {
		if ( ! block.includes( 'msgid ' ) || block.startsWith( 'msgid ""' ) ) {
			return [];
		}
		const lines = block.split( /\r?\n/ );
		const msgidLine = lines.find( ( line ) => line.startsWith( 'msgid ' ) );
		const pluralLine = lines.find( ( line ) => line.startsWith( 'msgid_plural ' ) );
		const referenceLine = lines.find( ( line ) => line.startsWith( '#: ' ) ) || '';
		const comments = lines
			.filter( ( line ) => line.startsWith( '#. translators: ' ) )
			.map( ( line ) => line.slice( 16 ) );
		return [ {
			msgid: parseQuoted( msgidLine ),
			plural: pluralLine ? parseQuoted( pluralLine ) : '',
			references: referenceLine.slice( 3 ).split( ' ' ).filter( Boolean ),
			comments
		} ];
	} );
}

function resolveTranslation( entry ) {
	if ( Object.prototype.hasOwnProperty.call( translations, entry.msgid ) ) {
		return translations[ entry.msgid ];
	}
	if ( entry.plural ) {
		return [ entry.msgid, entry.plural ];
	}
	return entry.msgid;
}

function poQuote( value ) {
	return `"${ value.replace( /\\/g, '\\\\' ).replace( /"/g, '\\"' ).replace( /\n/g, '\\n' ) }"`;
}

function metadata() {
	return [
		'Project-Id-Version: MasjidOS 1.1.0',
		'Report-Msgid-Bugs-To: https://wordpress.org/support/plugin/masjidos/',
		`Language: ${ locale }`,
		'Language-Team: Arabic',
		'MIME-Version: 1.0',
		'Content-Type: text/plain; charset=UTF-8',
		'Content-Transfer-Encoding: 8bit',
		`Plural-Forms: ${ pluralForms }`,
		'X-Domain: masjidos',
		''
	].join( '\n' );
}

function buildPo( entries ) {
	const header = [
		'msgid ""',
		'msgstr ""',
		...metadata().split( '\n' ).map( ( line ) => poQuote( `${ line }\n` ) ),
		''
	];
	const body = entries.flatMap( ( entry ) => {
		const translated = resolveTranslation( entry );
		const lines = [
			...entry.comments.map( ( comment ) => `#. translators: ${ comment }` ),
			`#: ${ entry.references.join( ' ' ) }`,
			`msgid ${ poQuote( entry.msgid ) }`
		];
		if ( entry.plural ) {
			const forms = Array.isArray( translated ) ? translated : [ entry.msgid, entry.plural ];
			lines.push( `msgid_plural ${ poQuote( entry.plural ) }` );
			lines.push( `msgstr[0] ${ poQuote( forms[0] || entry.msgid ) }` );
			lines.push( `msgstr[1] ${ poQuote( forms[1] || entry.plural ) }` );
			lines.push( `msgstr[2] ${ poQuote( forms[2] || forms[1] || entry.plural ) }` );
			lines.push( `msgstr[3] ${ poQuote( forms[3] || forms[1] || entry.plural ) }` );
			lines.push( `msgstr[4] ${ poQuote( forms[4] || forms[1] || entry.plural ) }` );
			lines.push( `msgstr[5] ${ poQuote( forms[5] || forms[1] || entry.plural ) }` );
		} else {
			lines.push( `msgstr ${ poQuote( typeof translated === 'string' ? translated : entry.msgid ) }` );
		}
		lines.push( '' );
		return lines;
	} );
	return [ ...header, ...body ].join( '\n' );
}

function buildMo( entries ) {
	const records = [ { original: '', translated: metadata() } ].concat( entries.map( ( entry ) => {
		const translated = resolveTranslation( entry );
		if ( entry.plural ) {
			const forms = Array.isArray( translated ) ? translated : [ entry.msgid, entry.plural ];
			const padded = [
				forms[0] || entry.msgid,
				forms[1] || entry.plural,
				forms[2] || forms[1] || entry.plural,
				forms[3] || forms[1] || entry.plural,
				forms[4] || forms[1] || entry.plural,
				forms[5] || forms[1] || entry.plural
			];
			return {
				original: `${ entry.msgid }\0${ entry.plural }`,
				translated: padded.join( '\0' )
			};
		}
		return {
			original: entry.msgid,
			translated: typeof translated === 'string' ? translated : entry.msgid
		};
	} ) ).sort( ( a, b ) => Buffer.from( a.original ).compare( Buffer.from( b.original ) ) );

	const count = records.length;
	const originalsTableOffset = 28;
	const translationsTableOffset = originalsTableOffset + count * 8;
	const stringsOffset = translationsTableOffset + count * 8;
	const originalBuffers = records.map( ( record ) => Buffer.from( record.original, 'utf8' ) );
	const translationBuffers = records.map( ( record ) => Buffer.from( record.translated, 'utf8' ) );
	const originalBytes = originalBuffers.reduce( ( total, item ) => total + item.length + 1, 0 );
	const totalBytes = stringsOffset + originalBytes + translationBuffers.reduce( ( total, item ) => total + item.length + 1, 0 );
	const output = Buffer.alloc( totalBytes );

	output.writeUInt32LE( 0x950412de, 0 );
	output.writeUInt32LE( 0, 4 );
	output.writeUInt32LE( count, 8 );
	output.writeUInt32LE( originalsTableOffset, 12 );
	output.writeUInt32LE( translationsTableOffset, 16 );
	output.writeUInt32LE( 0, 20 );
	output.writeUInt32LE( stringsOffset, 24 );

	let originalOffset = stringsOffset;
	let translationOffset = stringsOffset + originalBytes;
	for ( let index = 0; index < count; index++ ) {
		const original = originalBuffers[ index ];
		const translated = translationBuffers[ index ];
		output.writeUInt32LE( original.length, originalsTableOffset + index * 8 );
		output.writeUInt32LE( originalOffset, originalsTableOffset + index * 8 + 4 );
		output.writeUInt32LE( translated.length, translationsTableOffset + index * 8 );
		output.writeUInt32LE( translationOffset, translationsTableOffset + index * 8 + 4 );
		original.copy( output, originalOffset );
		translated.copy( output, translationOffset );
		originalOffset += original.length + 1;
		translationOffset += translated.length + 1;
	}
	return output;
}

function buildJson( entries ) {
	const messages = {
		'': { domain: 'messages', lang: locale, 'plural-forms': pluralForms }
	};
	for ( const entry of entries.filter( ( item ) => item.references.some( ( reference ) => reference.startsWith( 'admin/assets/js/' ) ) ) ) {
		const translated = resolveTranslation( entry );
		messages[ entry.msgid ] = entry.plural
			? ( Array.isArray( translated ) ? translated : [ entry.msgid, entry.plural ] )
			: [ typeof translated === 'string' ? translated : entry.msgid ];
	}
	return {
		'translation-revision-date': new Date().toISOString(),
		generator: 'MasjidOS Arabic translation pack builder',
		source: 'admin/assets/js/',
		domain: 'messages',
		locale_data: { messages }
	};
}

const entries = parsePot();
const covered = entries.filter( ( entry ) => Object.prototype.hasOwnProperty.call( translations, entry.msgid ) ).length;

fs.mkdirSync( languagesDir, { recursive: true } );
fs.writeFileSync( path.join( languagesDir, `masjidos-${ locale }.po` ), buildPo( entries ), 'utf8' );
fs.writeFileSync( path.join( languagesDir, `masjidos-${ locale }.mo` ), buildMo( entries ) );
fs.writeFileSync( path.join( languagesDir, `masjidos-${ locale }-itmms-admin.json` ), `${ JSON.stringify( buildJson( entries ) ) }\n`, 'utf8' );
console.log( `Built ar pack: ${ entries.length } strings (${ covered } translated, rest English fallback).` );
