const fs = require( 'node:fs' );
const path = require( 'node:path' );

const file = path.join( __dirname, 'bn_BD-translations.json' );
const data = JSON.parse( fs.readFileSync( file, 'utf8' ) );

const additions = {
	"%1$d views, %2$d helpful": "%1$d ভিউ, %2$d সহায়ক",
	"%d helpful": [
		"%d সহায়ক",
		"%d সহায়ক"
	],
	"%d view": [
		"%d ভিউ",
		"%d ভিউ"
	],
	"Add New Question": "নতুন প্রশ্ন যোগ করুন",
	"Add New Question Category": "নতুন প্রশ্ন ক্যাটাগরি যোগ করুন",
	"All Question Categories": "সকল প্রশ্ন ক্যাটাগরি",
	"Answer & Visibility": "উত্তর ও দৃশ্যমানতা",
	"Answer Question": "প্রশ্নের উত্তর দিন",
	"Answer with care. Public answers will later be shown in the Ask the Imam public archive when the public widget is enabled.": "সতর্কতার সাথে উত্তর দিন। পাবলিক উত্তরগুলো পরবর্তীতে আস্ক দ্য ইমাম পাবলিক আর্কাইভে প্রদর্শিত হবে যখন পাবলিক উইজেট সক্রিয় করা হবে।",
	"Answered": "উত্তর দেওয়া হয়েছে",
	"Answered by the Imam": "ইমাম কর্তৃক উত্তর দেওয়া হয়েছে",
	"Answered Questions": "উত্তর দেওয়া প্রশ্নসমূহ",
	"Ask another question": "আরেকটি প্রশ্ন জিজ্ঞাসা করুন",
	"Ask the Imam": "ইমামকে জিজ্ঞাসা করুন",
	"Ask the Imam / Answered Questions": "ইমামকে জিজ্ঞাসা করুন / উত্তর দেওয়া প্রশ্নসমূহ",
	"Ask the Imam Form": "ইমামকে জিজ্ঞাসা করার ফর্ম",
	"Ask the Imam shortcode attributes": "আস্ক দ্য ইমাম শর্টকোড অ্যাট্রিবিউটসমূহ",
	"Asker Email": "জিজ্ঞাসাকারীর ইমেইল",
	"Asker Name": "জিজ্ঞাসাকারীর নাম",
	"Changes public Q&A widget labels.": "পাবলিক প্রশ্নোত্তর উইজেট লেবেল পরিবর্তন করে।",
	"Changes the form or answers widget heading.": "ফর্ম বা উত্তর উইজেটের শিরোনাম পরিবর্তন করে।",
	"Choose category": "ক্যাটাগরি বেছে নিন",
	"Closed": "বন্ধ",
	"Coming in Pro": "প্রো-তে আসছে",
	"compare with your printed board": "আপনার প্রিন্ট করা বোর্ডের সাথে তুলনা করুন",
	"Could not submit the question. Please try again.": "প্রশ্নটি জমা দেওয়া যায়নি। দয়া করে আবার চেষ্টা করুন।",
	"Currency, donation tracking, and public transparency reports unlock when MasjidOS Pro is active. Nothing to configure here in Free.": "কারেন্সি, অনুদান ট্র্যাকিং এবং পাবলিক স্বচ্ছতা রিপোর্ট MasjidOS Pro সক্রিয় থাকলে আনলক হবে। এখানে Free সংস্করণে কিছু কনফিগার করার নেই।",
	"Daily Life": "দৈনন্দিন জীবন",
	"Donations, ledgers & transparency": "অনুদান, লেজার ও স্বচ্ছতা",
	"Edit Question Category": "প্রশ্ন ক্যাটাগরি সম্পাদন",
	"Example: Is Friday ghusl Sunnah?": "উদাহরণ: শুক্রবারের গোসল কি সুন্নাহ?",
	"Finance and public transparency tools ship with MasjidOS Pro — not in the free plugin.": "ফাইন্যান্স এবং পাবলিক স্বচ্ছতা টুলস MasjidOS Pro এর সাথে আসে — ফ্রি প্লাগইনে নয়।",
	"Generators only offer free designs. Pro design keys live under the Pro tab and render only when MasjidOS Pro is active.": "জেনারেটরগুলো শুধুমাত্র ফ্রি ডিজাইন অফার করে। প্রো ডিজাইন কি-গুলো প্রো ট্যাবের অধীনে রয়েছে এবং শুধুমাত্র MasjidOS Pro সক্রিয় থাকলেই রেন্ডার হবে।",
	"Helpful Votes": "সহায়ক ভোট",
	"If the answer is marked public, it will appear in the answered questions library.": "যদি উত্তরটি পাবলিক হিসেবে চিহ্নিত করা হয়, তবে এটি উত্তর দেওয়া প্রশ্নগুলোর লাইব্রেরিতে প্রদর্শিত হবে।",
	"Imam Answers Library": "ইমামের উত্তরের লাইব্রেরি",
	"Imam Question": "ইমামের প্রশ্ন",
	"Imam's answer": "ইমামের উত্তর",
	"Includes nonce, honeypot, and basic rate limiting": "nonce, honeypot এবং বেসিক রেট লিমিটিং অন্তর্ভুক্ত",
	"Keep this question private": "এই প্রশ্নটি প্রাইভেট রাখুন",
	"Let congregants submit Islamic questions from a public page.": "মুসল্লিদের একটি পাবলিক পেজ থেকে ইসলামিক প্রশ্ন জমা দেওয়ার সুযোগ দিন।",
	"MasjidOS requires WordPress 6.2 or higher.": "MasjidOS ব্যবহারের জন্য ওয়ার্ডপ্রেস ৬.২ বা তার বেশি প্রয়োজন।",
	"Minbar Q&A": "মিম্বার প্রশ্নোত্তর",
	"New Question": "নতুন প্রশ্ন",
	"New Question Category Name": "নতুন প্রশ্ন ক্যাটাগরির নাম",
	"Next 7 days preview": "পরবর্তী ৭ দিনের প্রিভিউ",
	"No public answers found": "কোনো পাবলিক উত্তর পাওয়া যায়নি",
	"No questions found in Trash.": "ট্র্যাশে কোনো প্রশ্ন পাওয়া যায়নি।",
	"No questions found.": "কোনো প্রশ্ন পাওয়া যায়নি।",
	"Only answered questions marked public are shown": "শুধুমাত্র পাবলিক হিসেবে চিহ্নিত উত্তর দেওয়া প্রশ্নগুলো দেখানো হচ্ছে",
	"Only for [masjidos_imam_answers]; controls how many answers appear.": "শুধুমাত্র [masjidos_imam_answers] এর জন্য; কয়টি উত্তর প্রদর্শিত হবে তা নিয়ন্ত্রণ করে।",
	"Only for [masjidos_imam_answers]; filters public answers by category slug.": "শুধুমাত্র [masjidos_imam_answers] এর জন্য; ক্যাটাগরি স্ল্যাগ অনুযায়ী পাবলিক উত্তর ফিল্টার করে।",
	"Optional, never shown publicly": "ঐচ্ছিক, কখনোই প্রকাশ্যে দেখানো হবে না",
	"Please check the form": "দয়া করে ফর্মটি পরীক্ষা করুন",
	"Please write a clear question before submitting.": "জমা দেওয়ার আগে দয়া করে একটি স্পষ্ট প্রশ্ন লিখুন।",
	"Popularity": "জনপ্রিয়তা",
	"Private questions stay hidden from public widgets and search.": "প্রাইভেট প্রশ্নগুলো পাবলিক উইজেট এবং সার্চ থেকে লুকানো থাকে।",
	"Public answer": "পাবলিক উত্তর",
	"Public question form for congregants. Questions wait for imam review in the admin panel.": "মুসল্লিদের জন্য পাবলিক প্রশ্ন ফর্ম। প্রশ্নগুলো অ্যাডমিন প্যানেলে ইমামের পর্যালোচনার জন্য অপেক্ষা করবে।",
	"Question asked": "প্রশ্ন করা হয়েছে",
	"Question Categories": "প্রশ্ন ক্যাটাগরিসমূহ",
	"Question Category": "প্রশ্ন ক্যাটাগরি",
	"Question Details": "প্রশ্নের বিবরণ",
	"Question received": "প্রশ্ন পাওয়া গেছে",
	"Question submitted. The imam can now review it from the admin panel.": "প্রশ্ন জমা দেওয়া হয়েছে। ইমাম এখন অ্যাডমিন প্যানেল থেকে এটি পর্যালোচনা করতে পারেন।",
	"Questions are reviewed before answers appear publicly.": "উত্তরগুলো প্রকাশ্যে আসার আগে প্রশ্নগুলো পর্যালোচনা করা হয়।",
	"Questions are saved as pending in MasjidOS > Ask the Imam": "প্রশ্নগুলো MasjidOS > Ask the Imam-এ পেন্ডিং হিসেবে সংরক্ষিত হয়",
	"Review submitted questions, write answers, and mark safe answers as public.": "জমা দেওয়া প্রশ্নগুলো পর্যালোচনা করুন, উত্তর লিখুন এবং নিরাপদ উত্তরগুলোকে পাবলিক হিসেবে চিহ্নিত করুন।",
	"Search answers...": "উত্তর খুঁজুন...",
	"Search Question Categories": "প্রশ্ন ক্যাটাগরি খুঁজুন",
	"Search Questions": "প্রশ্ন খুঁজুন",
	"Search the answered library before submitting a new question.": "নতুন প্রশ্ন জমা দেওয়ার আগে উত্তর দেওয়া লাইব্রেরিটি সার্চ করে দেখুন।",
	"Searchable public Q&A library showing only answered questions marked public.": "সার্চযোগ্য পাবলিক প্রশ্নোত্তর লাইব্রেরি যেখানে কেবল পাবলিক হিসেবে চিহ্নিত উত্তর দেওয়া প্রশ্নগুলো দেখানো হয়।",
	"Security check failed. Please refresh the page and try again.": "নিরাপত্তা পরীক্ষা ব্যর্থ হয়েছে। অনুগ্রহ করে পেজটি রিফ্রেশ করে আবার চেষ্টা করুন।",
	"Short title": "সংক্ষিপ্ত শিরোনাম",
	"Show the searchable public Q&A library.": "সার্চযোগ্য পাবলিক প্রশ্নোত্তর লাইব্রেরি দেখান।",
	"Submit Question": "প্রশ্ন জমা দিন",
	"Submit your Islamic question. The imam will review it before answering.": "আপনার ইসলামিক প্রশ্নটি জমা দিন। উত্তর দেওয়ার আগে ইমাম এটি পর্যালোচনা করবেন।",
	"Submitting...": "জমা দেওয়া হচ্ছে...",
	"Too many questions were submitted recently. Please try again later.": "সম্প্রতি খুব বেশি প্রশ্ন জমা দেওয়া হয়েছে। অনুগ্রহ করে পরে আবার চেষ্টা করুন।",
	"Try another search term or submit a new question.": "অন্য কোনো সার্চ টার্ম চেষ্টা করুন অথবা নতুন প্রশ্ন জমা দিন।",
	"Update Question Category": "প্রশ্ন ক্যাটাগরি আপডেট করুন",
	"Use [masjidos_ask_imam] for submissions and [masjidos_imam_answers] for the public answered library. Manage questions in MasjidOS > Ask the Imam.": "প্রশ্ন জমা দেওয়ার জন্য [masjidos_ask_imam] এবং পাবলিক উত্তরের লাইব্রেরির জন্য [masjidos_imam_answers] ব্যবহার করুন। প্রশ্নগুলো MasjidOS > Ask the Imam এ পরিচালনা করুন।",
	"View Question": "প্রশ্ন দেখুন",
	"Views": "ভিউসমূহ",
	"Visibility": "দৃশ্যমানতা",
	"Visitors can search previous answers before submitting a new question": "দর্শনার্থীরা নতুন প্রশ্ন জমা দেওয়ার আগে পূর্ববর্তী উত্তরগুলো সার্চ করতে পারেন",
	"Write the question clearly. Avoid sharing sensitive personal details.": "প্রশ্নটি স্পষ্টভাবে লিখুন। সংবেদনশীল ব্যক্তিগত তথ্য শেয়ার করা এড়িয়ে চলুন।",
	"Your question": "আপনার প্রশ্ন"
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
