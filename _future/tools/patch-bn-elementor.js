const fs = require( 'node:fs' );
const path = require( 'node:path' );

const file = path.join( __dirname, 'bn_BD-translations.json' );
const data = JSON.parse( fs.readFileSync( file, 'utf8' ) );

const additions = {
	"99 Names of Allah (Asma ul Husna)": "আল্লাহর ৯৯ নাম (আসমাউল হুসনা)",
	"All Types": "সকল ধরন",
	"Answered Questions Library": "উত্তর দেওয়া প্রশ্ন লাইব্রেরি",
	"Arabic (العربية)": "আরবি (العربية)",
	"Ask the Imam (Q&A)": "ইমামকে জিজ্ঞাসা (প্রশ্নোত্তর)",
	"Background Color": "ব্যাকগ্রাউন্ড কালার",
	"Bangla (বাংলা)": "বাংলা (বাংলা)",
	"Border Radius": "বর্ডার রেডিয়াস",
	"Card & Container Style": "কার্ড ও কন্টেইনার স্টাইল",
	"Classic (Cards Grid)": "ক্লাসিক (কার্ড গ্রিড)",
	"Classic Card": "ক্লাসিক কার্ড",
	"Compact (List)": "কমপ্যাক্ট (তালিকা)",
	"Compact List": "কমপ্যাক্ট তালিকা",
	"Content Settings": "কন্টেন্ট সেটিংস",
	"Custom Heading Title": "কাস্টম শিরোনাম",
	"Design Layout": "ডিজাইন লেআউট",
	"Display Mode": "প্রদর্শন মোড",
	"Duas & Daily Azkar": "দোয়া ও দৈনন্দিন আজকার",
	"Education / Class": "শিক্ষা / ক্লাস",
	"Eid Announcement": "ঈদের ঘোষণা",
	"Filter by Notice Type": "নোটিশের ধরন অনুযায়ী ফিল্টার",
	"Heading Color": "শিরোনামের রং",
	"Heading Title": "শিরোনাম",
	"Islamic Articles Grid": "ইসলামিক প্রবন্ধ গ্রিড",
	"Islamic Dual Calendar": "দ্বৈত ইসলামিক ক্যালেন্ডার",
	"Islamic Learning & Content": "ইসলামিক জ্ঞান ও কন্টেন্ট",
	"Jumuah & Khatib": "জুমুআহ ও খতিব",
	"Leave empty for default module title": "ডিফল্ট মডিউল শিরোনামের জন্য খালি রাখুন",
	"Leave empty for default title": "ডিফল্ট শিরোনামের জন্য খালি রাখুন",
	"Live Ticker (Marquee)": "লাইভ টিকার (স্ক্রোল)",
	"Masjid Notices & News": "মসজিদের নোটিশ ও খবর",
	"Month/Year Navigation": "মাস/বছর নেভিগেশন",
	"Notice Settings": "নোটিশ সেটিংস",
	"Number of Answers to Show": "কতগুলো উত্তর দেখানো হবে",
	"Number of Items (for Articles / Duas)": "আইটেমের সংখ্যা (প্রবন্ধ / দোয়ার জন্য)",
	"Number of Notices": "নোটিশের সংখ্যা",
	"Prayer Names Color": "নামাজের নামের রং",
	"Prayer Time Digits Color": "নামাজের সময়ের সংখ্যার রং",
	"Q&A Settings": "প্রশ্নোত্তর সেটিংস",
	"Question Submission Form": "প্রশ্ন জমা দেওয়ার ফর্ম",
	"Select Content Module": "কন্টেন্ট মডিউল নির্বাচন করুন",
	"Show Date Badge": "তারিখ ব্যাজ দেখান",
	"Show Ishraq / Zawal Times": "ইশরাক / যাওয়ালের সময় দেখান",
	"Show Khatib Profile & Topic": "খতিবের প্রোফাইল ও বিষয় দেখান",
	"Show Qibla Direction": "কিবলা দিক দেখান",
	"Show Sunrise / Next Prayer Info": "সূর্যোদয় / পরবর্তী নামাজের তথ্য দেখান",
	"Slim Banner": "স্লিম ব্যানার",
	"Table Grid": "টেবিল গ্রিড",
	"Timetable Settings": "সময়সূচি সেটিংস",
	"Today’s Prayer Times": "আজকের নামাজের সময়সূচি",
	"Typography & Colors": "টাইপোগ্রাফি ও রং",
	"Urgent / Important": "জরুরি / গুরুত্বপূর্ণ"
};

let added = 0;
for ( const [ key, value ] of Object.entries( additions ) ) {
	if ( ! Object.prototype.hasOwnProperty.call( data, key ) ) {
		data[ key ] = value;
		added += 1;
	}
}

const sorted = {};
Object.keys( data ).sort( ( a, b ) => a.localeCompare( b ) ).forEach( ( key ) => {
	sorted[ key ] = data[ key ];
} );

fs.writeFileSync( file, `${ JSON.stringify( sorted, null, '\t' ) }\n`, 'utf8' );
console.log( `Added ${ added } translations. Total keys: ${ Object.keys( sorted ).length }` );
