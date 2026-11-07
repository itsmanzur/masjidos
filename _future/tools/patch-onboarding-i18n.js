/**
 * Adds bn_BD and ar translations for the dashboard setup checklist widget (and a few other
 * untranslated dashboard strings), then run build-bn-pack.js and build-ar-pack.js.
 *
 * Usage: node _future/tools/patch-onboarding-i18n.js
 */
const fs = require( 'node:fs' );
const path = require( 'node:path' );

// key: [ bn_BD, ar ]
const T = {
	'Masjid Setup Checklist': [ 'মসজিদ সেটআপ চেকলিস্ট', 'قائمة إعداد المسجد' ],
	'%1$d of %2$d steps completed (%3$d%%)': [ '%1$d/%2$d ধাপ সম্পন্ন (%3$d%%)', 'اكتملت %1$d من %2$d خطوات (%3$d%%)' ],
	'%1$d of %2$d complete (%3$d%%)': [ '%1$d/%2$d সম্পন্ন (%3$d%%)', 'اكتمل %1$d من %2$d (%3$d%%)' ],
	'Show Checklist': [ 'চেকলিস্ট দেখান', 'إظهار القائمة' ],
	'Setup Wizard': [ 'সেটআপ উইজার্ড', 'معالج الإعداد' ],
	'1-Min Setup Wizard': [ '১ মিনিটের সেটআপ উইজার্ড', 'معالج الإعداد في دقيقة' ],
	'Getting Started': [ 'শুরু করুন', 'البدء' ],
	'Complete these 5 quick steps to get your mosque website and TV display live.': [ '৫টি সহজ ধাপ সম্পন্ন করে আপনার মসজিদের ওয়েবসাইট ও TV ডিসপ্লে চালু করুন।', 'أكمل هذه الخطوات الخمس السريعة لتشغيل موقع مسجدك وشاشة العرض.' ],
	'Hide checklist': [ 'চেকলিস্ট লুকান', 'إخفاء القائمة' ],
	'Hide': [ 'লুকান', 'إخفاء' ],
	'Location & Timezone': [ 'অবস্থান ও টাইমজোন', 'الموقع والمنطقة الزمنية' ],
	'Set city coordinates, calculation method & local timezone.': [ 'শহরের স্থানাঙ্ক, গণনা পদ্ধতি ও স্থানীয় টাইমজোন সেট করুন।', 'حدّد إحداثيات المدينة وطريقة الحساب والمنطقة الزمنية المحلية.' ],
	'Review': [ 'পর্যালোচনা', 'مراجعة' ],
	'Iqamah & Jumuah': [ 'ইকামাহ ও জুমুআ', 'الإقامة والجمعة' ],
	'Configure daily jamaat rules and Friday khutbah schedule.': [ 'দৈনিক জামাতের নিয়ম ও জুমুআর খুতবার সময়সূচি ঠিক করুন।', 'اضبط قواعد الجماعة اليومية وجدول خطبة الجمعة.' ],
	'Adjust': [ 'সমন্বয়', 'تعديل' ],
	'Set Times': [ 'সময় নির্ধারণ', 'تحديد الأوقات' ],
	'Website Prayer Page': [ 'ওয়েবসাইটের নামাজের পেজ', 'صفحة الصلاة للموقع' ],
	'Publish a ready-to-use Prayer Times & Masjid Board page.': [ 'নামাজের সময় ও মসজিদ বোর্ডের একটি তৈরি পেজ প্রকাশ করুন।', 'انشر صفحة جاهزة لمواقيت الصلاة ولوحة المسجد.' ],
	'View Page': [ 'পেজ দেখুন', 'عرض الصفحة' ],
	'Create Page (1-Click)': [ 'পেজ তৈরি করুন (১ ক্লিক)', 'إنشاء الصفحة (بنقرة واحدة)' ],
	'Creating...': [ 'তৈরি হচ্ছে...', 'جارٍ الإنشاء...' ],
	'First Notice or Khutbah': [ 'প্রথম নোটিশ বা খুতবা', 'أول إعلان أو خطبة' ],
	'Post an active announcement or archive a Friday sermon.': [ 'একটি সক্রিয় ঘোষণা দিন বা জুমুআর খুতবা আর্কাইভ করুন।', 'انشر إعلانًا نشطًا أو أرشف خطبة الجمعة.' ],
	'Manage Notices': [ 'নোটিশ পরিচালনা', 'إدارة الإعلانات' ],
	'Mosque TV Display': [ 'মসজিদ TV ডিসপ্লে', 'شاشة عرض المسجد' ],
	'Preview the fullscreen digital signage for prayer hall & lobby.': [ 'নামাজঘর ও লবির জন্য ফুলস্ক্রিন ডিজিটাল ডিসপ্লে প্রিভিউ করুন।', 'عاين شاشة العرض الرقمية بملء الشاشة لقاعة الصلاة والردهة.' ],
	'Open TV Display': [ 'TV ডিসপ্লে খুলুন', 'افتح شاشة العرض' ],
	'Author': [ 'লেখক', 'المؤلف' ],
	'Auto API (Aladhan)': [ 'অটো API (আলাদান)', 'API تلقائي (الأذان)' ],
	'Friday': [ 'শুক্রবার', 'الجمعة' ],
	'Hanafi': [ 'হানাফি', 'حنفي' ],
	'ISNA (North America)': [ 'ISNA (উত্তর আমেরিকা)', 'ISNA (أمريكا الشمالية)' ],
	'Listen': [ 'শুনুন', 'استمع' ],
	'Manage modules': [ 'মডিউল পরিচালনা', 'إدارة الوحدات' ],
	'Method & timetable': [ 'পদ্ধতি ও সময়সূচি', 'الطريقة والجدول' ],
	'Open announcements': [ 'ঘোষণা খুলুন', 'فتح الإعلانات' ],
	'Open events': [ 'ইভেন্ট খুলুন', 'فتح الفعاليات' ],
	'Preview': [ 'প্রিভিউ', 'معاينة' ],
	'Shortcode': [ 'শর্টকোড', 'الرمز القصير' ],
	'Standard (Shafi, Maliki, Hanbali)': [ 'স্ট্যান্ডার্ড (শাফেয়ী, মালিকী, হাম্বলী)', 'قياسي (الشافعي، المالكي، الحنبلي)' ],
	'Turkey (Diyanet)': [ 'তুরস্ক (দিয়ানেত)', 'تركيا (ديانت)' ]
};

const files = [ [ 'bn_BD-translations.json', 0 ], [ 'ar-translations.json', 1 ] ];
for ( const [ file, idx ] of files ) {
	const p = path.join( __dirname, file );
	const raw = fs.readFileSync( p, 'utf8' );
	const data = JSON.parse( raw );
	let changed = 0;
	for ( const [ key, vals ] of Object.entries( T ) ) {
		// Never overwrite a real translation; replace only missing or English-fallback values.
		if ( ! data[ key ] || data[ key ] === key ) {
			data[ key ] = vals[ idx ];
			changed++;
		}
	}
	const eol = raw.includes( '\r\n' ) ? '\r\n' : '\n';
	fs.writeFileSync( p, JSON.stringify( data, null, '\t' ).replace( /\n/g, eol ) + eol );
	console.log( file, 'updated', changed );
}
