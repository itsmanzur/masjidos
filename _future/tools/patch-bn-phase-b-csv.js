/**
 * Merge Phase B CSV polish BN strings, then rebuild pack.
 */
const fs = require( 'node:fs' );
const path = require( 'node:path' );
const { spawnSync } = require( 'node:child_process' );

const translationsPath = path.join( __dirname, 'bn_BD-translations.json' );
const translations = JSON.parse( fs.readFileSync( translationsPath, 'utf8' ) );

const patch = {
	'Working year': 'ওয়ার্কিং বছর',
	'By year': 'বছর অনুযায়ী',
	'Sample CSV': 'স্যাম্পল CSV',
	'Validate CSV': 'CSV যাচাই',
	'Export Imported Year': 'ইমপোর্টেড বছর এক্সপোর্ট',
	'Clear Year': 'বছর মুছুন',
	'Clear All': 'সব মুছুন',
	'Validating CSV...': 'CSV যাচাই হচ্ছে...',
	'Validation failed.': 'যাচাই ব্যর্থ।',
	'Valid: %1$d day(s). Errors: %2$d. Range: %3$s → %4$s.': 'সঠিক: %1$d দিন। ত্রুটি: %2$d। রেঞ্জ: %3$s → %4$s।',
	'Remove all imported days for %s?': '%s-এর সব ইমপোর্টেড দিন মুছে ফেলবেন?',
	'Cleared %1$d day(s) from %2$s.': '%2$s থেকে %1$d দিন মুছেছে।',
	'Could not clear year.': 'বছর মুছা যায়নি।',
	'Large store detected. Prefer one year per import, or clear an old year to keep the site light.': 'বড় স্টোর ধরা পড়েছে। প্রতি ইমপোর্টে এক বছর রাখুন, অথবা পুরনো বছর মুছে সাইট হালকা রাখুন।',
	'Columns: date, fajr, sunrise, dhuhr, asr, maghrib, isha, and optional *_iqamah columns. Max ~2 MB / one year.': 'কলাম: date, fajr, sunrise, dhuhr, asr, maghrib, isha, এবং ঐচ্ছিক *_iqamah। সর্বোচ্চ ~২ MB / এক বছর।',
	'Export Calculated Year as a starting template, or download the Sample CSV.': 'শুরুর টেমপ্লেট হিসেবে Export Calculated Year নিন, অথবা Sample CSV ডাউনলোড করুন।',
	'Edit times to match your official mosque committee table (YYYY-MM-DD + 24h or AM/PM).': 'অফিশিয়াল মসজিদ কমিটির টেবিল অনুযায়ী সময় সম্পাদনা করুন (YYYY-MM-DD + ২৪ঘণ্টা বা AM/PM)।',
	'Validate CSV, then Import. Widgets and TV use imported days as official times.': 'CSV যাচাই করে Import করুন। উইজেট ও TV ইমপোর্টেড দিনকে অফিশিয়াল সময় হিসেবে ব্যবহার করে।',
	'Dates without CSV rows still use your calculation settings and Iqamah rules.': 'যে তারিখে CSV নেই, সেখানে হিসাব সেটিংস ও Iqamah নিয়ম চলবে।',
	'CSV file is too large. Import one year at a time (about 365 rows).': 'CSV ফাইল অনেক বড়। একবারে এক বছর ইমপোর্ট করুন (প্রায় ৩৬৫ সারি)।',
	'CSV validation found no valid rows.': 'CSV যাচাইয়ে কোনো সঠিক সারি পাওয়া যায়নি।',
	'In Prayer Setup → Timetable: Export Calculated Year, edit official times, Validate, then Import. Widgets and TV use imported days.': 'Prayer Setup → Timetable: Export Calculated Year নিন, অফিশিয়াল সময় সম্পাদনা করুন, Validate করে Import করুন। উইজেট ও TV ইমপোর্টেড দিন ব্যবহার করে।',
	'Import Official CSV Year': 'অফিশিয়াল CSV বছর ইমপোর্ট',
	'Use Iqamah rules (fixed or minutes after Azan) in Prayer Setup → Iqamah.': 'Prayer Setup → Iqamah-এ Iqamah নিয়ম ব্যবহার করুন (ফিক্সড বা আজানের কয়েক মিনিট পর)।',
};

let changed = 0;
for ( const [ key, value ] of Object.entries( patch ) ) {
	if ( translations[ key ] !== value ) {
		translations[ key ] = value;
		changed += 1;
	}
}

fs.writeFileSync( translationsPath, `${ JSON.stringify( translations, null, '\t' ) }\n`, 'utf8' );
console.log( `Merged ${ changed } Phase B CSV strings.` );

spawnSync( process.execPath, [ path.join( __dirname, 'build-pot.js' ) ], { stdio: 'inherit' } );
spawnSync( process.execPath, [ path.join( __dirname, 'sync-bn-from-po.js' ) ], { stdio: 'inherit' } );
