/**
 * bn_BD translations for the Docs page and the Gutenberg block editor strings.
 * Then run build-bn-pack.js.
 *
 * Usage: node _future/tools/patch-docs-blocks-i18n.js
 */
const fs = require( 'node:fs' );
const path = require( 'node:path' );

const T = {
	// --- Docs ---
	'Automatically hides past events based on their end time': 'শেষের সময় অনুযায়ী অতীত ইভেন্ট নিজে থেকে লুকিয়ে ফেলে',
	'Bangla Events': 'বাংলা ইভেন্ট',
	'Changes headers, dates, and location prefixes': 'হেডার, তারিখ ও স্থানের উপসর্গ বদলে দেয়',
	'Changes public widget labels.': 'প্রকাশ্য উইজেটের লেবেল বদলে দেয়।',
	'Changes the events list heading.': 'ইভেন্ট তালিকার শিরোনাম বদলে দেয়।',
	'Combines schedule roster and planner topics': 'সময়সূচির রোস্টার ও পরিকল্পনার বিষয় একসাথে দেখায়',
	'Create a public events feed shortcode for community pages or mosque notice boards.': 'কমিউনিটি পেজ বা মসজিদের নোটিশ বোর্ডের জন্য প্রকাশ্য ইভেন্ট ফিডের শর্টকোড তৈরি করুন।',
	'Defaults to 5 events': 'ডিফল্ট ৫টি ইভেন্ট',
	'Donations, Accounts ledger, and Special day collections docs appear here after MasjidOS Pro is active. Shortcode details are maintained by the Pro plugin.': 'MasjidOS Pro সক্রিয় হলে দান, হিসাবের খতিয়ান ও বিশেষ দিনের সংগ্রহের ডকুমেন্টেশন এখানে দেখা যাবে। শর্টকোডের বিবরণ Pro প্লাগিন নিজেই রাখে।',
	'Event shortcode attributes': 'ইভেন্ট শর্টকোডের অ্যাট্রিবিউট',
	'Events Generator': 'ইভেন্ট জেনারেটর',
	'Events List': 'ইভেন্টের তালিকা',
	'Filter by topic, khatib, category, or date': 'বিষয়, খতিব, ক্যাটাগরি বা তারিখ দিয়ে ফিল্টার করুন',
	'First create and schedule an event from MasjidOS > Events': 'আগে MasjidOS > Events থেকে একটি ইভেন্ট তৈরি ও নির্ধারণ করুন',
	'Jump to': 'যান',
	'Keeps your custom event title and description unchanged': 'আপনার লেখা ইভেন্টের শিরোনাম ও বিবরণ অপরিবর্তিত রাখে',
	'Learn about MasjidOS Pro': 'MasjidOS Pro সম্পর্কে জানুন',
	'Limit Events': 'ইভেন্টের সংখ্যা সীমা',
	'Limit the number of events displayed on the page.': 'পেজে কয়টি ইভেন্ট দেখাবে তার সীমা দিন।',
	'Limits the number of events shown.': 'কয়টি ইভেন্ট দেখাবে তার সীমা ঠিক করে।',
	'MasjidOS > Duas Library': 'MasjidOS > দোয়া লাইব্রেরি',
	'Maximum limit is 20': 'সর্বোচ্চ সীমা ২০',
	'Only public archive entries are shown': 'শুধু প্রকাশ্য আর্কাইভ এন্ট্রি দেখানো হয়',
	'Open /masjidos-display/ on the site to show a fullscreen mosque board with prayer times, Iqamah, Hijri date, Jumuah, and notices.': 'সাইটে /masjidos-display/ খুলুন; নামাজের সময়, ইকামাহ, হিজরি তারিখ, জুমুআ ও নোটিশ সহ ফুলস্ক্রিন মসজিদ বোর্ড দেখাবে।',
	'Open Pro': 'Pro খুলুন',
	'Open in Pro': 'Pro-তে খুলুন',
	'Optional URL override for display labels.': 'ডিসপ্লে লেবেলের জন্য ঐচ্ছিক URL।',
	'Optional URL override for large screens.': 'বড় স্ক্রিনের জন্য ঐচ্ছিক URL।',
	'Optional URL override, for example /masjidos-display/?theme=light.': 'ঐচ্ছিক URL, যেমন /masjidos-display/?theme=light।',
	'Optional audio player and PDF download links': 'ঐচ্ছিক অডিও প্লেয়ার ও PDF ডাউনলোডের লিংক',
	'Optional category filter for the archive.': 'আর্কাইভের জন্য ঐচ্ছিক ক্যাটাগরি ফিল্টার।',
	'Pick a topic below — sections stay collapsed so the page stays short as Pro grows.': 'নিচ থেকে একটি বিষয় বেছে নিন — অংশগুলো বন্ধ থাকে, তাই Pro বাড়লেও পেজ ছোট থাকে।',
	'Popup Modal': 'পপআপ মডাল',
	'Pro docs topics': 'Pro ডকুমেন্টেশনের বিষয়',
	'Pro tools': 'Pro টুল',
	'Pro tools (when installed)': 'Pro টুল (ইনস্টল থাকলে)',
	'Search for MasjidOS blocks when you prefer a block instead of manually pasting a shortcode.': 'শর্টকোড হাতে পেস্ট না করে ব্লক ব্যবহার করতে চাইলে MasjidOS ব্লক খুঁজুন।',
	'Selects the layout style.': 'লেআউটের ধরন বেছে নেয়।',
	'Show a Hijri + Gregorian calendar with Islamic dates, holy days, and mosque events.': 'হিজরি ও গ্রেগরিয়ান ক্যালেন্ডার দেখান, ইসলামি তারিখ, পবিত্র দিন ও মসজিদের ইভেন্ট সহ।',
	'Show a fixed Gregorian month while still displaying Hijri dates inside each day.': 'নির্দিষ্ট গ্রেগরিয়ান মাস দেখান, প্রতিটি দিনের ভেতরে হিজরি তারিখ সহ।',
	'Show calendar labels and Hijri month names in Bangla.': 'ক্যালেন্ডারের লেবেল ও হিজরি মাসের নাম বাংলায় দেখান।',
	'Show upcoming community gatherings, lectures, and special prayers.': 'আসন্ন কমিউনিটি সমাবেশ, আলোচনা ও বিশেষ নামাজ দেখান।',
	'Shows a specific Gregorian month with Hijri dates inside the grid.': 'একটি নির্দিষ্ট গ্রেগরিয়ান মাস দেখায়, গ্রিডের ভেতরে হিজরি তারিখ সহ।',
	'Shows mosque events on matching days': 'মিল থাকা দিনগুলোতে মসজিদের ইভেন্ট দেখায়',
	'Shows or hides reload-free calendar controls.': 'রিলোড ছাড়া চলা ক্যালেন্ডার নিয়ন্ত্রণ দেখায় বা লুকায়।',
	'Translate event labels and titles into Bangla.': 'ইভেন্টের লেবেল ও শিরোনাম বাংলায় অনুবাদ করে।',
	'URL': 'URL',
	'Use this when you want a fixed embedded month.': 'নির্দিষ্ট মাস এমবেড করতে চাইলে এটি ব্যবহার করুন।',
	'Useful for Ramadan, Eid, or event archive pages': 'রমজান, ঈদ বা ইভেন্ট আর্কাইভ পেজের জন্য উপযোগী',
	'Useful on homepage or Jumuah pages': 'হোমপেজ বা জুমুআর পেজে উপযোগী',
	'Uses the Hijri Date Adjustment from Settings > Calculation': 'Settings > Calculation-এর হিজরি তারিখ সমন্বয় ব্যবহার করে',
	'Visit /masjidos-display/ on your site if you need a fullscreen board for mosque TVs.': 'মসজিদের TV-র জন্য ফুলস্ক্রিন বোর্ড দরকার হলে আপনার সাইটের /masjidos-display/ দেখুন।',
	'Widgets': 'উইজেট',
	'limit attribute controls how many rows show': 'limit অ্যাট্রিবিউট ঠিক করে কয়টি সারি দেখাবে',

	// --- Gutenberg blocks ---
	'Browse published Jumuah khutbah records with search and filters.': 'সার্চ ও ফিল্টার সহ প্রকাশিত জুমুআর খুতবার রেকর্ড দেখুন।',
	'Category slug': 'ক্যাটাগরি স্লাগ',
	'Category: ': 'ক্যাটাগরি: ',
	'Display Friday prayer sessions, khutbah times, and khatib details.': 'জুমুআর নামাজের সেশন, খুতবার সময় ও খতিবের বিবরণ দেখান।',
	'Display curated duas and azkar with optional counter and share buttons.': 'বাছাই করা দোয়া ও আযকার দেখান, ঐচ্ছিক কাউন্টার ও শেয়ার বাটন সহ।',
	'Display the 99 Names of Allah with meanings.': 'আল্লাহর ৯৯ নাম অর্থ সহ দেখান।',
	'Embed an audio Quran player for selected Surahs.': 'নির্বাচিত সূরার জন্য অডিও কুরআন প্লেয়ার এমবেড করুন।',
	'Limit: ': 'সীমা: ',
	'List published Islamic articles from the MasjidOS Articles CPT.': 'MasjidOS Articles থেকে প্রকাশিত ইসলামিক প্রবন্ধের তালিকা দেখান।',
	'List upcoming mosque events and community gatherings.': 'আসন্ন মসজিদের ইভেন্ট ও কমিউনিটি সমাবেশের তালিকা দেখান।',
	'List upcoming scheduled or planned Friday topics.': 'আসন্ন নির্ধারিত বা পরিকল্পিত জুমুআর বিষয়ের তালিকা দেখান।',
	'MasjidOS 99 Names': 'MasjidOS ৯৯ নাম',
	'MasjidOS Articles': 'MasjidOS প্রবন্ধ',
	'MasjidOS Audio Quran': 'MasjidOS অডিও কুরআন',
	'MasjidOS Duas & Azkar': 'MasjidOS দোয়া ও আযকার',
	'MasjidOS Events': 'MasjidOS ইভেন্ট',
	'MasjidOS Hadith': 'MasjidOS হাদিস',
	'MasjidOS Jumuah': 'MasjidOS জুমুআ',
	'MasjidOS Khutbah Archive': 'MasjidOS খুতবা আর্কাইভ',
	'MasjidOS Khutbah Search': 'MasjidOS খুতবা সার্চ',
	'MasjidOS Monthly Timetable': 'MasjidOS মাসিক সময়সূচি',
	'MasjidOS Notices': 'MasjidOS নোটিশ',
	'MasjidOS Quran Verse': 'MasjidOS কুরআনের আয়াত',
	'MasjidOS This Week\'s Khatib': 'MasjidOS এই সপ্তাহের খতিব',
	'MasjidOS Upcoming Khutbahs': 'MasjidOS আসন্ন খুতবা',
	'Optional. Example: fiqh, aqeedah, seerah': 'ঐচ্ছিক। যেমন: ফিকহ, আকীদা, সীরাত',
	'Popup': 'পপআপ',
	'Search Khutbah Archive': 'খুতবা আর্কাইভে খুঁজুন',
	'Show Calculations Meta': 'গণনার তথ্য দেখান',
	'Show Dates': 'তারিখ দেখান',
	'Show Excerpt': 'সারাংশ দেখান',
	'Show Iqamah Times': 'ইকামাহর সময় দেখান',
	'Show a full-month prayer timetable with navigation and print support.': 'নেভিগেশন ও প্রিন্ট সুবিধা সহ পুরো মাসের নামাজের সময়সূচি দেখান।',
	'Show a rotating Hadith of the day with translation.': 'অনুবাদ সহ পালাক্রমে আজকের হাদিস দেখান।',
	'Show a rotating Quran verse of the day with translation.': 'অনুবাদ সহ পালাক্রমে আজকের কুরআনের আয়াত দেখান।',
	'Show active mosque notices and announcements.': 'সক্রিয় মসজিদের নোটিশ ও ঘোষণা দেখান।',
	'Upcoming Events': 'আসন্ন ইভেন্ট'
};

const p = path.join( __dirname, 'bn_BD-translations.json' );
const raw = fs.readFileSync( p, 'utf8' );
const data = JSON.parse( raw );
let changed = 0;
for ( const [ key, val ] of Object.entries( T ) ) {
	if ( ! data[ key ] || data[ key ] === key ) {
		data[ key ] = val;
		changed++;
	}
}
const eol = raw.includes( '\r\n' ) ? '\r\n' : '\n';
fs.writeFileSync( p, JSON.stringify( data, null, '\t' ).replace( /\n/g, eol ) + eol );
console.log( 'bn_BD-translations.json updated', changed, 'of', Object.keys( T ).length );
