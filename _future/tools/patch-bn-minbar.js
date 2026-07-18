/**
 * Merge Minbar module BN translations, then rebuild pack.
 */
const fs = require( 'node:fs' );
const path = require( 'node:path' );
const { spawnSync } = require( 'node:child_process' );

const translationsPath = path.join( __dirname, 'bn_BD-translations.json' );
const patchPath = path.join( __dirname, 'minbar-bn-patch.json' );
const translations = JSON.parse( fs.readFileSync( translationsPath, 'utf8' ) );
const patch = JSON.parse( fs.readFileSync( patchPath, 'utf8' ) );

let changed = 0;
for ( const [ key, value ] of Object.entries( patch ) ) {
	if ( translations[ key ] !== value ) {
		translations[ key ] = value;
		changed += 1;
	}
}

// Force Mimbar spelling everywhere in values.
let spelling = 0;
for ( const [ key, value ] of Object.entries( translations ) ) {
	if ( typeof value === 'string' && value.includes( 'মিনবার' ) ) {
		translations[ key ] = value.split( 'মিনবার' ).join( 'মিম্বার' );
		spelling += 1;
	}
}

fs.writeFileSync( translationsPath, `${ JSON.stringify( translations, null, '\t' ) }\n`, 'utf8' );
console.log( `Merged ${ changed } minbar strings; fixed ${ spelling } মিনবার spellings.` );

spawnSync( process.execPath, [ path.join( __dirname, 'build-pot.js' ) ], { stdio: 'inherit' } );
spawnSync( process.execPath, [ path.join( __dirname, 'sync-bn-from-po.js' ) ], { stdio: 'inherit' } );
