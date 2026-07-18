/**
 * Patch English-fallback BN strings used on the admin dashboard.
 */
const fs = require( 'node:fs' );
const path = require( 'node:path' );

const file = path.join( __dirname, 'bn_BD-translations.json' );
const translations = JSON.parse( fs.readFileSync( file, 'utf8' ) );

const patch = {
	'Accuracy & Trust': 'নির্ভুলতা ও বিশ্বাস',
	'Next 7 Days Preview': 'আগামী ৭ দিনের প্রিভিউ',
	'Verify accuracy against your masjid board': 'মসজিদের বোর্ডের সাথে মিলিয়ে যাচাই করুন',
	"This Week's Minbar": 'এই সপ্তাহের মিম্বার',
	Ishraq: 'ইশরাক',
	Zawal: 'জাওয়াল',
	Open: 'খুলুন',
	Shortcodes: 'শর্টকোড',
	Donations: 'দান',
	Accounts: 'হিসাব',
	Designs: 'ডিজাইন',
	'Raised %1$s %2$s · %3$d pending': 'সংগৃহীত %1$s %2$s · %3$d অপেক্ষমাণ',
	'Net %1$s %2$s · Collections board': 'নেট %1$s %2$s · কালেকশন বোর্ড',
	'Premium prayer & Jumuah designs unlocked': 'প্রিমিয়াম নামাজ ও জুমার ডিজাইন আনলক',
	'Open Minbar': 'মিম্বার খুলুন',
	'Open Minbar archive': 'মিম্বার আর্কাইভ খুলুন',
	'Open Minbar planner': 'মিম্বার প্ল্যানার খুলুন',
	'Khutbah Archive': 'খুতবা আর্কাইভ',
	'Planned Topics': 'পরিকল্পিত বিষয়',
	'Modules On': 'চালু মডিউল',
	'Active Notices': 'সক্রিয় নোটিশ',
	'Active Events': 'সক্রিয় ইভেন্ট',
	'Add notice': 'নোটিশ যোগ করুন',
	'Add Notice': 'নোটিশ যোগ করুন',
	'WP Dashboard': 'WP ড্যাশবোর্ড',
	Configure: 'কনফিগার',
	Details: 'বিস্তারিত',
	'Hide details': 'বিস্তারিত লুকান',
	Pro: 'প্রো',
	Docs: 'ডকুমেন্টস',
	'Premium designs and committee tools in a separate Pro plugin.': 'প্রিমিয়াম ডিজাইন ও কমিটি টুল আলাদা প্রো প্লাগইনে।',
	'Learn about Pro': 'প্রো সম্পর্কে জানুন',
	'Coordinates look valid': 'কোঅর্ডিনেট ঠিক আছে',
	'Set latitude and longitude': 'অক্ষাংশ ও দ্রাঘিমাংশ সেট করুন',
	'Timezone is set': 'টাইমজোন সেট আছে',
	'Avoid UTC / +00:00 for local masjid times': 'স্থানীয় মসজিদের সময়ের জন্য UTC/+০০:০০ এড়ান',
	'Matches WordPress timezone': 'ওয়ার্ডপ্রেস টাইমজোনের সাথে মিলে',
	'WP site timezone is %s': 'WP সাইট টাইমজোন %s',
	'Qibla Direction': 'কিবলার দিক',
	'Clockwise from true north': 'প্রকৃত উত্তর থেকে ঘড়ির কাঁটার দিকে',
	Source: 'উৎস',
	Location: 'অবস্থান',
	Coordinates: 'কোঅর্ডিনেট',
	Method: 'পদ্ধতি',
	Asr: 'আসর',
	Timezone: 'টাইমজোন',
	'Hijri adjustment': 'হিজরি সমন্বয়',
	Offsets: 'সমন্বয়',
	None: 'নেই',
	'Hijri dates may differ by one day based on local moon sighting.': 'স্থানীয় চাঁদ দেখার কারণে হিজরি তারিখ একদিন এদিক-ওদিক হতে পারে।',
	Date: 'তারিখ',
	Fajr: 'ফজর',
	Dhuhr: 'যোহর',
	Maghrib: 'মাগরিব',
	Isha: 'এশা',
	Sunrise: 'সূর্যোদয়',
	Now: 'এখন',
	Topic: 'বিষয়',
	Khatib: 'খতিব',
	'Topic not set yet': 'বিষয় এখনো নির্ধারিত নয়',
	'Khatib TBD': 'খতিব নির্ধারিত নয়',
	'No Friday roster yet. Plan a khatib and topic.': 'এখনো জুমার রোস্টার নেই। খতিব ও বিষয় পরিকল্পনা করুন।',
	Minbar: 'মিম্বার',
	Features: 'ফিচার',
	Events: 'ইভেন্ট',
	'Prayer Setup': 'নামাজের সেটআপ',
	'No active notices. Click to add one.': 'কোনো সক্রিয় নোটিশ নেই। যোগ করতে ক্লিক করুন।',
	'Assalamu Alaikum': 'আসসালামু আলাইকুম',
	Jumuah: 'জুমা',
	Khutbah: 'খুতবা',
	Jamaat: 'জামাত',
};

let changed = 0;
for ( const [ key, value ] of Object.entries( patch ) ) {
	if ( translations[ key ] !== value ) {
		translations[ key ] = value;
		changed += 1;
	}
}

fs.writeFileSync( file, `${ JSON.stringify( translations, null, '\t' ) }\n`, 'utf8' );
console.log( `Patched ${ changed } dashboard BN strings.` );
