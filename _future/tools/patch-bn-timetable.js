/**
 * Patch BN translations for Prayer Setup → Timetable tab.
 */
const fs = require( 'node:fs' );
const path = require( 'node:path' );

const file = path.join( __dirname, 'bn_BD-translations.json' );
const translations = JSON.parse( fs.readFileSync( file, 'utf8' ) );

const patch = {
	Timetable: 'সময়সূচি',
	'CSV Timetable': 'CSV সময়সূচি',
	'Upload your official masjid year timetable with Azan and Iqamah columns. Imported dates override calculated times.':
		'আজান ও জামাত কলামসহ মসজিদের অফিসিয়াল বার্ষিক সময়সূচি আপলোড করুন। ইমপোর্ট করা তারিখ হিসাবকৃত সময়কে ওভাররাইড করবে।',
	'days loaded': 'দিন লোড হয়েছে',
	Coverage: 'কভারেজ',
	'No imported timetable yet': 'এখনো কোনো ইমপোর্ট করা সময়সূচি নেই',
	'CSV File': 'CSV ফাইল',
	'Columns: date, fajr, sunrise, dhuhr, asr, maghrib, isha, and optional *_iqamah columns.':
		'কলাম: date, fajr, sunrise, dhuhr, asr, maghrib, isha এবং ঐচ্ছিক *_iqamah কলাম।',
	'Import Mode': 'ইমপোর্ট মোড',
	'Merge rows by date': 'তারিখ অনুযায়ী মার্জ করুন',
	'Replace all imported days': 'সব ইমপোর্ট করা দিন প্রতিস্থাপন করুন',
	'Download CSV': 'CSV ডাউনলোড',
	'Import CSV': 'CSV ইমপোর্ট',
	'Export Imported': 'ইমপোর্টকৃত এক্সপোর্ট',
	'Export Calculated Year': 'হিসাবকৃত বছর এক্সপোর্ট',
	'Clear Timetable': 'সময়সূচি মুছুন',
	'How CSV timetable works': 'CSV সময়সূচি কীভাবে কাজ করে',
	'Download the sample CSV and match your mosque committee format.':
		'নমুনা CSV ডাউনলোড করে মসজিদ কমিটির ফরম্যাটের সাথে মিলিয়ে নিন।',
	'Use YYYY-MM-DD dates and 24-hour or AM/PM times.':
		'তারিখ YYYY-MM-DD এবং সময় ২৪-ঘণ্টা বা AM/PM ফরম্যাটে দিন।',
	'Import the full year once; widgets and TV display will use those official times.':
		'একবার পুরো বছর ইমপোর্ট করুন; উইজেট ও TV ডিসপ্লে সেই অফিসিয়াল সময় ব্যবহার করবে।',
	'Dates without CSV rows still use your calculation settings.':
		'CSV-তে নেই এমন তারিখে আপনার হিসাব সেটিংস ব্যবহার হবে।',
	'CSV timetable Iqamah columns override these rules on imported dates. Otherwise rules apply daily from calculated Azan times.':
		'ইমপোর্ট করা তারিখে CSV-এর জামাত কলাম এই নিয়মগুলো ওভাররাইড করে। অন্যথায় হিসাবকৃত আজান থেকে দৈনিক নিয়ম প্রয়োগ হয়।',
	'Remove all imported timetable days?': 'সব ইমপোর্ট করা সময়সূচির দিন মুছবেন?',
	'Imported timetable cleared.': 'ইমপোর্ট করা সময়সূচি মুছে ফেলা হয়েছে।',
	'Could not clear timetable.': 'সময়সূচি মুছা যায়নি।',
	'Importing timetable...': 'সময়সূচি ইমপোর্ট হচ্ছে...',
	'Choose a CSV file first.': 'আগে একটি CSV ফাইল বেছে নিন।',
	'Import failed.': 'ইমপোর্ট ব্যর্থ হয়েছে।',
	'Export failed.': 'এক্সপোর্ট ব্যর্থ হয়েছে।',
	'Sample download failed.': 'নমুনা ডাউনলোড ব্যর্থ হয়েছে।',
	'Calculated export failed.': 'হিসাবকৃত এক্সপোর্ট ব্যর্থ হয়েছে।',
	Profile: 'প্রোফাইল',
	Calculation: 'হিসাব',
	Public: 'পাবলিক',
	'TV Display': 'TV ডিসপ্লে',
};

let changed = 0;
for ( const [ key, value ] of Object.entries( patch ) ) {
	if ( translations[ key ] !== value ) {
		translations[ key ] = value;
		changed += 1;
	}
}

fs.writeFileSync( file, `${ JSON.stringify( translations, null, '\t' ) }\n`, 'utf8' );
console.log( `Patched ${ changed } timetable BN strings.` );
