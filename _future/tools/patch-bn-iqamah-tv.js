/**
 * Patch BN for Iqamah (Jamaat) + TV Display settings tabs.
 */
const fs = require( 'node:fs' );
const path = require( 'node:path' );

const file = path.join( __dirname, 'bn_BD-translations.json' );
const translations = JSON.parse( fs.readFileSync( file, 'utf8' ) );

const patch = {
	'Configure calculation, timetable, offsets, and Iqamah rules.':
		'হিসাব, সময়সূচি, সমন্বয় ও জামাতের নিয়ম কনফিগার করুন।',
	'Set fixed jamaat clocks or dynamic rules such as minutes after Azan.':
		'নির্দিষ্ট জামাত সময় বা আজানের পর মিনিটের মতো ডাইনামিক নিয়ম সেট করুন।',
	'Iqamah Times': 'জামাতের সময়',
	Iqamah: 'জামাত',
	Adjustments: 'সমন্বয়',
	Mode: 'মোড',
	'Fixed time': 'নির্দিষ্ট সময়',
	'Fixed Iqamah': 'নির্দিষ্ট জামাত',
	'Minutes after Azan': 'আজানের পর মিনিট',
	'Minutes before Sunrise': 'সূর্যোদয়ের আগে মিনিট',
	Hidden: 'লুকানো',
	Minutes: 'মিনিট',
	'Round Azan': 'আজান রাউন্ড',
	'No rounding': 'রাউন্ড নয়',
	'5 min': '৫ মিনিট',
	'10 min': '১০ মিনিট',
	'15 min': '১৫ মিনিট',

	'TV Display Settings': 'TV ডিসপ্লে সেটিংস',
	'Configure fullscreen mosque display layout, slides, quiet mode, overnight dim, theme, and font size.':
		'ফুলস্ক্রিন মসজিদ ডিসপ্লের লেআউট, স্লাইড, কোয়ায়েট মোড, রাতের ডিম, থিম ও ফন্ট সাইজ কনফিগার করুন।',
	'TV Layout': 'TV লেআউট',
	'Classic — table + countdown': 'ক্লাসিক — টেবিল + কাউন্টডাউন',
	'Split — large countdown first': 'স্প্লিট — বড় কাউন্টডাউন আগে',
	'Focus — hero countdown + strip': 'ফোকাস — হিরো কাউন্টডাউন + স্ট্রিপ',
	'TV Theme Style': 'TV থিম স্টাইল',
	'TV Font Size': 'TV ফন্ট সাইজ',
	'Clock Format': 'ঘড়ির ফরম্যাট',
	'12-hour (2:30:05 PM)': '১২-ঘণ্টা (২:৩০:০৫ PM)',
	'24-hour (14:30:05)': '২৪-ঘণ্টা (১৪:৩০:০৫)',
	'Pre-prayer Alert': 'নামাজের আগের অ্যালার্ট',
	'Minutes before Azan/Iqamah to pulse the countdown (1 to 30).':
		'কাউন্টডাউন পালস করতে আজান/জামাতের কত মিনিট আগে (১ থেকে ৩০)।',
	'Announcement Scroll Speed': 'ঘোষণা স্ক্রল গতি',
	'Lower = faster continuous ticker scroll (3 to 30).':
		'কম মান = দ্রুত টিকার স্ক্রল (৩ থেকে ৩০)।',
	'Rotate slides (prayer board → notices → Jumuah)':
		'স্লাইড ঘোরান (নামাজ বোর্ড → নোটিশ → জুমা)',
	'Slide Interval': 'স্লাইড ইন্টারভাল',
	'Seconds between slides (6 to 60).': 'স্লাইডের মধ্যে সেকেন্ড (৬ থেকে ৬০)।',
	'Quiet / Salah mode (pause slides & ticker after Iqamah)':
		'কোয়ায়েট / সালাত মোড (জামাতের পর স্লাইড ও টিকার থামে)',
	'Quiet Duration': 'কোয়ায়েট সময়কাল',
	'Minutes after Iqamah to keep the calm “Prayer in progress” screen (5 to 45).':
		'জামাতের পর শান্ত “নামাজ চলছে” স্ক্রিন কত মিনিট রাখবে (৫ থেকে ৪৫)।',
	'Enable overnight dim (screen softens overnight)':
		'রাতের ডিম চালু করুন (স্ক্রিন নরম হয়)',
	'Dim Start': 'ডিম শুরু',
	'Dim End': 'ডিম শেষ',
	'TV Custom Logo': 'TV কাস্টম লোগো',
	'Override layout, theme, language, and font size in the URL, for example: ':
		'URL-এ লেআউট, থিম, ভাষা ও ফন্ট সাইজ ওভাররাইড করতে পারেন, যেমন: ',
	'Optional layout override, for example /masjidos-display/?layout=focus.':
		'ঐচ্ছিক লেআউট ওভাররাইড, যেমন /masjidos-display/?layout=focus।',
	'Masjid TV Display': 'মসজিদ TV ডিসপ্লে',
	'Open TV': 'TV খুলুন',
	Small: 'ছোট',
	Normal: 'সাধারণ',
	Large: 'বড়',
	'Extra Large': 'অতিরিক্ত বড়',
};

let changed = 0;
for ( const [ key, value ] of Object.entries( patch ) ) {
	if ( translations[ key ] !== value ) {
		translations[ key ] = value;
		changed += 1;
	}
}

fs.writeFileSync( file, `${ JSON.stringify( translations, null, '\t' ) }\n`, 'utf8' );
console.log( `Patched ${ changed } iqamah/TV BN strings.` );
