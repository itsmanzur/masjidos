const fs = require( 'node:fs' );
const path = require( 'node:path' );

const pluginRoot = path.resolve( __dirname, '..', '..' );
const languagesDir = path.join( pluginRoot, 'languages' );
const potPath = path.join( languagesDir, 'masjidos.pot' );
const translationsPath = path.join( __dirname, 'bn_BD-translations.json' );
const translations = JSON.parse( fs.readFileSync( translationsPath, 'utf8' ) );
const pluralForms = 'nplurals=2; plural=(n != 1);';

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

function placeholders( value ) {
	return ( value.match( /%(?:\d+\$)?[sd]/g ) || [] ).sort().join( '|' );
}

function poQuote( value ) {
	return `"${ value.replace( /\\/g, '\\\\' ).replace( /"/g, '\\"' ).replace( /\n/g, '\\n' ) }"`;
}

function metadata() {
	return [
		'Project-Id-Version: MasjidOS 1.1.5',
		'Report-Msgid-Bugs-To: https://wordpress.org/support/plugin/masjidos/',
		'Language: bn_BD',
		'Language-Team: Bengali',
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
		const translated = translations[ entry.msgid ];
		const lines = [
			...entry.comments.map( ( comment ) => `#. translators: ${ comment }` ),
			`#: ${ entry.references.join( ' ' ) }`,
			`msgid ${ poQuote( entry.msgid ) }`
		];
		if ( entry.plural ) {
			lines.push( `msgid_plural ${ poQuote( entry.plural ) }` );
			lines.push( `msgstr[0] ${ poQuote( translated[0] ) }` );
			lines.push( `msgstr[1] ${ poQuote( translated[1] ) }` );
		} else {
			lines.push( `msgstr ${ poQuote( translated ) }` );
		}
		lines.push( '' );
		return lines;
	} );
	return [ ...header, ...body ].join( '\n' );
}

function buildMo( entries ) {
	const records = [ { original: '', translated: metadata() } ].concat( entries.map( ( entry ) => {
		const translated = translations[ entry.msgid ];
		return {
			original: entry.plural ? `${ entry.msgid }\0${ entry.plural }` : entry.msgid,
			translated: entry.plural ? `${ translated[0] }\0${ translated[1] }` : translated
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
		'': { domain: 'messages', lang: 'bn_BD', 'plural-forms': pluralForms }
	};
	for ( const entry of entries.filter( ( item ) => item.references.some( ( reference ) => reference.startsWith( 'admin/assets/js/' ) ) ) ) {
		const translated = translations[ entry.msgid ];
		messages[ entry.msgid ] = entry.plural ? translated : [ translated ];
	}
	return {
		'translation-revision-date': new Date().toISOString(),
		generator: 'MasjidOS translation pack builder',
		source: 'admin/assets/js/',
		domain: 'messages',
		locale_data: { messages }
	};
}

const entries = parsePot();
const missing = entries.filter( ( entry ) => ! Object.prototype.hasOwnProperty.call( translations, entry.msgid ) );
if ( missing.length ) {
	throw new Error( `Missing ${ missing.length } translations:\n${ missing.map( ( entry ) => entry.msgid ).join( '\n' ) }` );
}

for ( const entry of entries ) {
	const translated = translations[ entry.msgid ];
	const values = entry.plural ? translated : [ translated ];
	if ( entry.plural && ( ! Array.isArray( translated ) || translated.length !== 2 ) ) {
		throw new Error( `Plural translation must contain two forms: ${ entry.msgid }` );
	}
	for ( const value of values ) {
		if ( typeof value !== 'string' || ! value.trim() ) {
			throw new Error( `Empty translation: ${ entry.msgid }` );
		}
		if ( placeholders( entry.msgid ) !== placeholders( value ) && ( ! entry.plural || placeholders( entry.plural ) !== placeholders( value ) ) ) {
			throw new Error( `Placeholder mismatch: ${ entry.msgid } -> ${ value }` );
		}
	}
}

fs.mkdirSync( languagesDir, { recursive: true } );
fs.writeFileSync( path.join( languagesDir, 'masjidos-bn_BD.po' ), buildPo( entries ), 'utf8' );
fs.writeFileSync( path.join( languagesDir, 'masjidos-bn_BD.mo' ), buildMo( entries ) );
fs.writeFileSync( path.join( languagesDir, 'masjidos-bn_BD-itmms-admin.json' ), `${ JSON.stringify( buildJson( entries ) ) }\n`, 'utf8' );
console.log( `Built bn_BD pack with ${ entries.length } translated strings.` );
