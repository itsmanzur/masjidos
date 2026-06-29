const fs = require( 'node:fs' );
const path = require( 'node:path' );

const pluginRoot = path.resolve( __dirname, '..', '..' );
const outputFile = path.join( pluginRoot, 'languages', 'masjidos.pot' );
const entries = new Map();

function sourceFiles( directory ) {
	return fs.readdirSync( directory, { withFileTypes: true } ).flatMap( ( entry ) => {
		if ( [ '_future', '_release', 'languages' ].includes( entry.name ) ) {
			return [];
		}
		const fullPath = path.join( directory, entry.name );
		if ( entry.isDirectory() ) {
			return sourceFiles( fullPath );
		}
		return /\.(php|js)$/.test( entry.name ) ? [ fullPath ] : [];
	} );
}

function decodeLiteral( value ) {
	return value
		.replace( /\\'/g, "'" )
		.replace( /\\"/g, '"' )
		.replace( /\\n/g, '\n' )
		.replace( /\\r/g, '\r' )
		.replace( /\\t/g, '\t' )
		.replace( /\\\\/g, '\\' );
}

function lineNumber( source, index ) {
	return source.slice( 0, index ).split( '\n' ).length;
}

function translatorComment( source, index ) {
	const lineStart = source.lastIndexOf( '\n', index - 1 ) + 1;
	const precedingLines = source.slice( 0, lineStart ).trimEnd().split( '\n' );
	const previousLine = ( precedingLines.pop() || '' ).trim();
	const match = previousLine.match( /^(?:\/\*|\/\/)\s*translators:\s*(.*?)\s*(?:\*\/)?$/i );
	return match ? match[1].replace( /\s*\*\/$/, '' ).trim() : '';
}

function addEntry( singular, plural, reference, comment ) {
	const key = decodeLiteral( singular );
	const existing = entries.get( key ) || { singular: key, plural: '', references: new Set(), comments: new Set() };
	if ( plural ) {
		existing.plural = decodeLiteral( plural );
	}
	existing.references.add( reference );
	if ( comment ) {
		existing.comments.add( comment );
	}
	entries.set( key, existing );
}

for ( const file of sourceFiles( pluginRoot ) ) {
	const source = fs.readFileSync( file, 'utf8' );
	const referencePath = path.relative( pluginRoot, file ).replace( /\\/g, '/' );
	const singularPattern = /\b(?:__|esc_html__|esc_attr__)\(\s*'((?:\\.|[^'\\])*)'\s*,\s*'masjidos'\s*\)/g;
	const pluralPattern = /\b_n\(\s*'((?:\\.|[^'\\])*)'\s*,\s*'((?:\\.|[^'\\])*)'\s*,[\s\S]*?\s*,\s*'masjidos'\s*\)/g;
	let match;

	while ( ( match = singularPattern.exec( source ) ) ) {
		addEntry( match[1], '', `${ referencePath }:${ lineNumber( source, match.index ) }`, translatorComment( source, match.index ) );
	}
	while ( ( match = pluralPattern.exec( source ) ) ) {
		addEntry( match[1], match[2], `${ referencePath }:${ lineNumber( source, match.index ) }`, translatorComment( source, match.index ) );
	}
}

function potQuote( value ) {
	return `"${ value.replace( /\\/g, '\\\\' ).replace( /"/g, '\\"' ).replace( /\n/g, '\\n' ) }"`;
}

const header = [
	'msgid ""',
	'msgstr ""',
	'"Project-Id-Version: MasjidOS 1.1.5\\n"',
	'"Report-Msgid-Bugs-To: https://wordpress.org/support/plugin/masjidos/\\n"',
	`"POT-Creation-Date: ${ new Date().toISOString() }\\n"`,
	'"MIME-Version: 1.0\\n"',
	'"Content-Type: text/plain; charset=UTF-8\\n"',
	'"Content-Transfer-Encoding: 8bit\\n"',
	'"X-Domain: masjidos\\n"',
	''
];

const body = [ ...entries.values() ]
	.sort( ( a, b ) => a.singular.localeCompare( b.singular ) )
	.flatMap( ( entry ) => {
		const lines = [
			...[ ...entry.comments ].sort().map( ( comment ) => `#. translators: ${ comment }` ),
			`#: ${ [ ...entry.references ].sort().join( ' ' ) }`,
			`msgid ${ potQuote( entry.singular ) }`
		];
		if ( entry.plural ) {
			lines.push( `msgid_plural ${ potQuote( entry.plural ) }`, 'msgstr[0] ""', 'msgstr[1] ""' );
		} else {
			lines.push( 'msgstr ""' );
		}
		lines.push( '' );
		return lines;
	} );

fs.mkdirSync( path.dirname( outputFile ), { recursive: true } );
fs.writeFileSync( outputFile, [ ...header, ...body ].join( '\n' ), 'utf8' );
console.log( `Wrote ${ entries.size } strings to ${ outputFile }` );
