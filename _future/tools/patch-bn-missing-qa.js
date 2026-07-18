const fs = require( 'node:fs' );
const path = require( 'node:path' );

const file = path.join( __dirname, 'bn_BD-translations.json' );
const data = JSON.parse( fs.readFileSync( file, 'utf8' ) );

const additions = {
	'%1$d of %2$d complete — green items are detected from your saved settings.': '%1$d/%2$d সম্পন্ন — সবুজ আইটেম আপনার সেভ করা সেটিংস থেকে শনাক্ত।',
	'%d results': '%dটি ফলাফল',
	'%s days left': '%s দিন বাকি',
	'%s events': '%sটি ইভেন্ট',
	'1 event': '১টি ইভেন্ট',
	'Apple Calendar (.ics)': 'অ্যাপল ক্যালেন্ডার (.ics)',
	'Asma ul Husna': 'আসমাউল হুসনা',
	'Audio Quran': 'অডিও কুরআন',
	'Auto from settings': 'সেটিংস থেকে স্বয়ংক্রিয়',
	'Check back later for today’s hadith.': 'আজকের হাদিসের জন্য পরে আবার দেখুন।',
	'Check back later for today’s Quran verse.': 'আজকের কুরআনের আয়াতের জন্য পরে আবার দেখুন।',
	'Check Prayer Setup and try again.': 'নামাজ সেটআপ যাচাই করে আবার চেষ্টা করুন।',
	'Check Prayer Setup: timezone, coordinates, and calculation method.': 'নামাজ সেটআপ যাচাই করুন: টাইমজোন, কোঅর্ডিনেট ও হিসাব পদ্ধতি।',
	'Currency is saved in Free for future finance reports.': 'ভবিষ্যতের ফাইন্যান্স রিপোর্টের জন্য Free-এ মুদ্রা সেভ থাকে।',
	'Documentation': 'ডকুমেন্টেশন',
	'Documentation sections': 'ডকুমেন্টেশন বিভাগ',
	'Donations, ledgers, and public transparency reports unlock when MasjidOS Pro is active. Free can still store these preferences now.': 'দান, লেজার ও পাবলিক স্বচ্ছতা রিপোর্ট MasjidOS Pro সক্রিয় হলে আনলক হয়। Free এখনও এই পছন্দগুলো সেভ করতে পারে।',
	'Enable the Prayer Times module before using this shortcode.': 'এই শর্টকোড ব্যবহারের আগে Prayer Times মডিউল চালু করুন।',
	'Follow Overview once, generate shortcodes, or open Features for live previews. Docs stay as your attribute reference.': 'একবার Overview অনুসরণ করুন, শর্টকোড তৈরি করুন, অথবা লাইভ প্রিভিউর জন্য Features খুলুন। Docs অ্যাট্রিবিউট রেফারেন্স হিসেবেই থাকবে।',
	'Get masjid widgets live in minutes': 'মিনিটের মধ্যে মসজিদ উইজেট লাইভ করুন',
	'Google Calendar': 'গুগল ক্যালেন্ডার',
	'Grid': 'গ্রিড',
	'Islamic learning': 'ইসলামিক জ্ঞান',
	'Khutbah archive': 'খুতবা আর্কাইভ',
	'Mishary Rashid Alafasy': 'মিশারি রাশিদ আলআফাসির',
	'Month Navigation': 'মাস নেভিগেশন',
	'No duas found': 'কোনো দোয়া পাওয়া যায়নি',
	'No hadith available': 'কোনো হাদিস নেই',
	'No monthly timetable available': 'মাসিক সময়সূচি নেই',
	'No names available': 'কোনো নাম নেই',
	'No prayer times available': 'নামাজের সময় পাওয়া যায়নি',
	'No surahs available': 'কোনো সূরা নেই',
	'No verse available': 'কোনো আয়াত নেই',
	'Outlook': 'আউটলুক',
	'Pick a common zone or type any valid IANA timezone.': 'সাধারণ একটি জোন বেছে নিন অথবা যেকোনো বৈধ IANA টাইমজোন লিখুন।',
	'Planned': 'পরিকল্পিত',
	'Prayer Times is disabled': 'Prayer Times নিষ্ক্রিয়',
	'Preferences that prepare Free for public finance features shipping in Pro.': 'Pro-তে আসা পাবলিক ফাইন্যান্স ফিচারের জন্য Free প্রস্তুত রাখার পছন্দসমূহ।',
	'Pro finance preview': 'Pro ফাইন্যান্স প্রিভিউ',
	'Search khutbahs': 'খুতবা খুঁজুন',
	'Settings sections': 'সেটিংস বিভাগ',
	'Show Audio': 'অডিও দেখান',
	'Show Hijri Date': 'হিজরি তারিখ দেখান',
	'Show Ishraq / Zawal': 'ইশরাক / যাওয়াল দেখান',
	'Show Share': 'শেয়ার দেখান',
	'Show Tafsir Link': 'তাফসীর লিংক দেখান',
	'The 99 Names collection could not be loaded.': '৯৯ নামের সংগ্রহ লোড করা যায়নি।',
	'The Audio Quran list could not be loaded.': 'অডিও কুরআন তালিকা লোড করা যায়নি।',
	'This week': 'এই সপ্তাহ',
	'Transparency reports become public when MasjidOS Pro finance modules are active.': 'MasjidOS Pro ফাইন্যান্স মডিউল সক্রিয় হলে স্বচ্ছতা রিপোর্ট পাবলিক হয়।',
	'Try another category or increase the limit.': 'অন্য ক্যাটাগরি চেষ্টা করুন অথবা লিমিট বাড়ান।'
};

let added = 0;
for ( const [ key, value ] of Object.entries( additions ) ) {
	if ( ! Object.prototype.hasOwnProperty.call( data, key ) ) {
		data[ key ] = value;
		added += 1;
	}
}

// Keep JSON keys roughly sorted for maintainability.
const sorted = {};
Object.keys( data ).sort( ( a, b ) => a.localeCompare( b ) ).forEach( ( key ) => {
	sorted[ key ] = data[ key ];
} );

fs.writeFileSync( file, `${ JSON.stringify( sorted, null, '\t' ) }\n`, 'utf8' );
console.log( `Added ${ added } translations. Total keys: ${ Object.keys( sorted ).length }` );
