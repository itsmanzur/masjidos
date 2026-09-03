/**
 * MasjidOS landing interactions. Preview only — not enqueued by the plugin.
 */
(function () {
	"use strict";

	var dict = {
		en: {
			nav_features: "Features",
			nav_compare: "Compare",
			nav_pricing: "Pricing",
			nav_free: "Get Free",
			hero_badge: "WordPress plugin · Free on WordPress.org",
			hero_title: "The mosque OS for WordPress",
			hero_lede:
				"Prayer times, Friday, TV, notices, and learning widgets — Free. Donations, accounts, members, school, and hall booking in Pro.",
			cta_free: "Get Free",
			cta_pro: "See Pro",
			hero_m1t: "Local-first",
			hero_m1: "Prayer calc on your server",
			hero_m2t: "Three languages",
			hero_m2: "English, Bangla, Arabic",
			hero_m3t: "No telemetry",
			hero_m3: "Your masjid data stays yours",
			board_next: "Next prayer",
			trust1t: "WordPress",
			trust1: "Shortcodes + Gutenberg blocks",
			trust2t: "Local prayer",
			trust2: "Optional Aladhan / CSV override",
			trust3t: "en / bn / ar",
			trust3: "Admin + public labels",
			trust4t: "Privacy",
			trust4: "No tracking in Free or Pro",
			why_k: "Why it exists",
			why_t: "Committees should not run on WhatsApp and spreadsheets",
			why_l:
				"Scattered prayer times, taped lobby notices, and donation ledgers in chat leave mosques guessing. One WordPress site can run the public face — Pro can run the books.",
			before_t: "Before",
			before_p: "WhatsApp times, paper notices, Excel no one trusts.",
			after_t: "After",
			after_p: "Live prayer, TV board, Friday tools — plus optional money, members, and school.",
			tour_k: "Live preview",
			tour_t: "Tap a product surface",
			tour_l: "These are interactive mocks of what visitors and the committee actually see.",
			tab_prayer: "Prayer",
			tab_friday: "Friday",
			tab_tv: "TV lobby",
			tab_give: "Donations",
			prayer_h: "Daily prayer + Iqamah",
			prayer_p: "Local solar calculation with method presets, minute offsets, Hijri adjust, and Qibla. CSV timetable wins when you upload masjid-measured times.",
			friday_h: "Jumuah & Minbar",
			friday_p: "Two Jumuah sessions, this week’s khatib, upcoming khutbahs, archive, and Ask the Imam.",
			tv_h: "Fullscreen lobby TV",
			tv_p: "Open /masjidos-display/ on a stick PC. Layouts, dim nights, quiet mode, rotating slides. Pro adds a collections slide.",
			give_h: "Campaigns that post to the ledger",
			give_p: "Public form stays pending until confirm. bKash / Nagad can auto-complete. Confirmed gifts can post into Accounts funds.",
			feat_k: "Modules",
			feat_t: "Free toolkit, Pro committee tools",
			feat_l: "Filter, then open a card. Nothing here is loaded into the plugin — this folder is marketing only.",
			all: "All",
			free: "Free",
			pro: "Pro",
			compare_k: "Free vs Pro",
			compare_t: "Same WordPress site. Extra plugin for the books.",
			compare_l: "Pro requires Free. It extends filters — it does not replace MasjidOS.",
			diff_only: "Show differences only",
			price_k: "Plans",
			price_t: "Start Free. Add Pro when the committee needs it.",
			price_l: "Pro price is a placeholder until checkout is live.",
			plan_free: "Free",
			plan_pro: "Pro",
			plan_net: "Pro Network",
			plan_free_amt: "$0",
			plan_pro_amt: "License",
			plan_net_amt: "Sales",
			plan_free_p: "Public mosque toolkit, forever",
			plan_pro_p: "Money, CRM, school, hall, designs",
			plan_net_p: "Multi-mosque / multisite",
			faq_k: "FAQ",
			faq_t: "Straight answers",
			cta_t: "Install Free in minutes",
			cta_l: "Then activate MasjidOS Pro on the same site when you need donations, ledger, and members.",
			foot: "Preview page inside _future/landing — not shipped with the plugin ZIP.",
			faq1q: "Does prayer calculation need an API?",
			faq1a: "No. Local solar calculation is the default. Optional Aladhan or a CSV timetable you upload. Core prayer works without a network call.",
			faq2q: "Is Free forever free?",
			faq2a: "Yes. The public mosque toolkit stays on WordPress.org. Pro is a separate plugin for committee money, CRM, school, and hall booking.",
			faq3q: "Does Pro need Free?",
			faq3a: "Yes. Activate MasjidOS first, then MasjidOS Pro. Pro sets MASJIDOS_PRO_ACTIVE and extends Free through filters.",
			faq4q: "bKash and Nagad?",
			faq4a: "In Pro, with sandbox or live keys. Cash and bank pledges stay pending until a treasurer confirms.",
			faq5q: "What is Pro Network?",
			faq5a: "A multi-mosque / multisite-oriented license. Contact sales for site count and terms. Price is a placeholder here.",
			li_f1: "Prayer, TV, Jumuah & Minbar",
			li_f2: "Notices, events, education widgets",
			li_f3: "Admin English / Bangla / Arabic",
			li_f4: "On WordPress.org, forever Free",
			li_p1: "Everything in Free",
			li_p2: "Donations, bKash / Nagad",
			li_p3: "Accounts, collections, transparency",
			li_p4: "Members, dues, attendance",
			li_p5: "Hall, madrasa, volunteers, designs",
			li_n1: "Everything in Pro",
			li_n2: "License for N sites / network",
			li_n3: "Federations and WordPress multisite",
			price_note: "Always requires WordPress. Pro requires Free active.",
			buy_pro: "Get Pro (soon)",
			contact: "Contact sales",
			col_mod: "Module",
			col_free: "Free",
			col_pro: "Pro",
			cmp1: "Prayer times, Iqamah, Qibla, Hijri",
			cmp2: "TV lobby /masjidos-display/",
			cmp3: "Jumuah, Minbar, Ask the Imam",
			cmp4: "Notices, events, education widgets",
			cmp5: "EN / BN / AR admin",
			cmp6: "Donations + bKash / Nagad",
			cmp7: "Accounts, collections, transparency PDF",
			cmp8: "Members, dues, attendance, cards",
			cmp9: "Hall booking, madrasa, volunteers",
			cmp10: "Premium widget design packs",
			cmp11: "Imam / Muazzin roles",
			cmp12: "Treasurer / teacher roles",
			t_p1: "Local engine, Karachi/MWL/… presets",
			t_p2: "CSV timetable overrides API/local",
			t_p3: "Monthly print view + 7-day admin preview",
			t_p4: "Shortcode, block, REST today/month",
			t_f1: "This week’s khatib + two sessions",
			t_f2: "Khutbah archive and search",
			t_f3: "Khatib profiles and Friday schedule",
			t_f4: "Ask the Imam public form",
			t_t1: "Classic, split, and focus layouts",
			t_t2: "Dim + quiet minutes around Iqamah",
			t_t3: "Self-hosted fonts — no Google at runtime",
			t_t4: "Pro: Jumuah/Eid collections slide",
			t_g1: "Pending cash until confirm",
			t_g2: "bKash / Nagad callbacks",
			t_g3: "Restricted Zakat / Sadaqah funds",
			t_g4: "Public [masjidos_financials]",
			raised: "Raised ৳8.47 lakh of ৳20 lakh",
			mock_local: "Local calc · Dhaka",
			mock_jumuah: "Jumuah",
			mock_khatib: "This week’s khatib",
			topic: "Guarding the tongue · 1:15 PM",
			tv_path: "/masjidos-display/",
			camp: "Masjid expansion",
		},
		bn: {
			nav_features: "ফিচার",
			nav_compare: "তুলনা",
			nav_pricing: "প্রাইসিং",
			nav_free: "ফ্রি নিন",
			hero_badge: "ওয়ার্ডপ্রেস প্লাগিন · WordPress.org-এ ফ্রি",
			hero_title: "ওয়ার্ডপ্রেসের জন্য মসজিদ OS",
			hero_lede:
				"নামাজের সময়, জুমা, TV, নোটিশ ও শিক্ষা উইজেট — ফ্রি। ডোনেশন, হিসাব, সদস্য, স্কুল ও হল বুকিং — প্রো-তে।",
			cta_free: "ফ্রি নিন",
			cta_pro: "প্রো দেখুন",
			hero_m1t: "লোকাল-ফার্স্ট",
			hero_m1: "নামাজ হিসাব আপনার সার্ভারে",
			hero_m2t: "তিন ভাষা",
			hero_m2: "ইংরেজি, বাংলা, আরবি",
			hero_m3t: "কোনো টেলিমেট্রি নেই",
			hero_m3: "মসজিদের ডেটা আপনারই",
			board_next: "পরবর্তী নামাজ",
			trust1t: "ওয়ার্ডপ্রেস",
			trust1: "শর্টকোড + গুটেনবার্গ ব্লক",
			trust2t: "লোকাল নামাজ",
			trust2: "ঐচ্ছিক Aladhan / CSV",
			trust3t: "en / bn / ar",
			trust3: "অ্যাডমিন ও পাবলিক লেবেল",
			trust4t: "প্রাইভেসি",
			trust4: "ফ্রি বা প্রো-তে ট্র্যাকিং নেই",
			why_k: "কেন",
			why_t: "কমিটি হোয়াটসঅ্যাপ আর স্প্রেডশিটে চলবে না",
			why_l:
				"নামাজের সময় ছড়ানো, লবিতে কাগজের নোটিশ, চ্যাটে দানের খাতা। একটা ওয়ার্ডপ্রেস সাইটে পাবলিক মুখ — প্রো-তে হিসাব।",
			before_t: "আগে",
			before_p: "হোয়াটসঅ্যাপে সময়, কাগজের নোটিশ, অবিশ্বাস্য এক্সেল।",
			after_t: "পরে",
			after_p: "লাইভ নামাজ, TV বোর্ড, জুমার টুল — সাথে অর্থ, সদস্য, স্কুল।",
			tour_k: "লাইভ প্রিভিউ",
			tour_t: "একটা সারফেস বেছে নিন",
			tour_l: "ভিজিটর ও কমিটি যা দেখে, তার ইন্টারঅ্যাকটিভ মক।",
			tab_prayer: "নামাজ",
			tab_friday: "জুমা",
			tab_tv: "TV লবি",
			tab_give: "ডোনেশন",
			prayer_h: "দৈনিক নামাজ + ইকামাহ",
			prayer_p: "লোকাল সোলার হিসাব, মেথড, মিনিট অফসেট, হিজরি অ্যাডজাস্ট, কিবলা। CSV থাকলে সেটাই আগে।",
			friday_h: "জুমা ও মিনবার",
			friday_p: "দুই জুমা, এ সপ্তাহের খতিব, আসন্ন খুতবা, আর্কাইভ, ইমামকে প্রশ্ন।",
			tv_h: "ফুলস্ক্রিন লবি TV",
			tv_p: "/masjidos-display/ খুলুন। লেআউট, নাইট ডিম, কুইয়েট মোড। প্রো-তে কালেকশন স্লাইড।",
			give_h: "ক্যাম্পেইন যা লেজারে যায়",
			give_p: "পাবলিক ফর্ম পেন্ডিং থাকে। bKash/Nagad অটো-কমপ্লিট করতে পারে। কনফার্ম হলে ফান্ডে পোস্ট।",
			feat_k: "মডিউল",
			feat_t: "ফ্রি টুলকিট, প্রো কমিটি টুল",
			feat_l: "ফিল্টার করে কার্ড খুলুন। এই ফোল্ডার শুধু মার্কেটিং — প্লাগিনে লোড হয় না।",
			all: "সব",
			free: "ফ্রি",
			pro: "প্রো",
			compare_k: "ফ্রি বনাম প্রো",
			compare_t: "একই সাইট। হিসাবের জন্য আলাদা প্লাগিন।",
			compare_l: "প্রো-এর জন্য ফ্রি লাগে। ফিল্টার দিয়ে বাড়ে — ফ্রিকে রিপ্লেস করে না।",
			diff_only: "শুধু পার্থক্য দেখান",
			price_k: "প্ল্যান",
			price_t: "ফ্রি দিয়ে শুরু। কমিটির দরকার হলে প্রো।",
			price_l: "প্রো দাম চেকআউট চালু হওয়া পর্যন্ত প্লেসহোল্ডার।",
			plan_free: "ফ্রি",
			plan_pro: "প্রো",
			plan_net: "প্রো নেটওয়ার্ক",
			plan_free_amt: "$0",
			plan_pro_amt: "লাইসেন্স",
			plan_net_amt: "সেলস",
			plan_free_p: "পাবলিক মসজিদ টুলকিট, চিরকাল",
			plan_pro_p: "অর্থ, CRM, স্কুল, হল, ডিজাইন",
			plan_net_p: "মাল্টি-মসজিদ / মাল্টিসাইট",
			faq_k: "প্রশ্ন",
			faq_t: "সোজা উত্তর",
			cta_t: "কয়েক মিনিটে ফ্রি ইনস্টল করুন",
			cta_l: "ডোনেশন, লেজার ও সদস্য লাগলে একই সাইটে MasjidOS Pro চালু করুন।",
			foot: "প্রিভিউ পেজ _future/landing — প্লাগিন ZIP-এ যায় না।",
			faq1q: "নামাজের হিসাবে API লাগে?",
			faq1a: "না। ডিফল্ট লোকাল সোলার হিসাব। ঐচ্ছিক Aladhan বা আপনার CSV। নেটওয়ার্ক ছাড়াই চলে।",
			faq2q: "ফ্রি কি চিরকাল ফ্রি?",
			faq2a: "হ্যাঁ। পাবলিক টুলকিট WordPress.org-এ থাকবে। প্রো আলাদা প্লাগিন — অর্থ, CRM, স্কুল, হল।",
			faq3q: "প্রো-এর জন্য ফ্রি লাগে?",
			faq3a: "হ্যাঁ। আগে MasjidOS, তারপর MasjidOS Pro। প্রো ফিল্টার দিয়ে ফ্রিকে বাড়ায়।",
			faq4q: "bKash ও Nagad?",
			faq4a: "প্রো-তে, স্যান্ডবক্স বা লাইভ কি সহ। ক্যাশ/ব্যাংক ট্রেজারার কনফার্ম না করা পর্যন্ত পেন্ডিং।",
			faq5q: "প্রো নেটওয়ার্ক কী?",
			faq5a: "মাল্টি-মসজিদ / মাল্টিসাইট লাইসেন্স। সাইট সংখ্যা সেলসে জানতে হবে। এখানে দাম প্লেসহোল্ডার।",
			li_f1: "নামাজ, TV, জুমা ও মিনবার",
			li_f2: "নোটিশ, ইভেন্ট, শিক্ষা উইজেট",
			li_f3: "অ্যাডমিন ইংরেজি / বাংলা / আরবি",
			li_f4: "WordPress.org-এ চিরকাল ফ্রি",
			li_p1: "ফ্রির সবকিছু",
			li_p2: "ডোনেশন, bKash / Nagad",
			li_p3: "হিসাব, কালেকশন, স্বচ্ছতা",
			li_p4: "সদস্য, চাঁদা, হাজিরা",
			li_p5: "হল, মাদরাসা, স্বেচ্ছাসেবক, ডিজাইন",
			li_n1: "প্রো-এর সবকিছু",
			li_n2: "N সাইট / নেটওয়ার্ক লাইসেন্স",
			li_n3: "ফেডারেশন ও ওয়ার্ডপ্রেস মাল্টিসাইট",
			price_note: "ওয়ার্ডপ্রেস লাগে। প্রো-এর জন্য ফ্রি অ্যাকটিভ থাকতে হবে।",
			buy_pro: "প্রো নিন (শীঘ্রই)",
			contact: "সেলসে যোগাযোগ",
			col_mod: "মডিউল",
			col_free: "ফ্রি",
			col_pro: "প্রো",
			cmp1: "নামাজ, ইকামাহ, কিবলা, হিজরি",
			cmp2: "TV লবি /masjidos-display/",
			cmp3: "জুমা, মিনবার, ইমামকে প্রশ্ন",
			cmp4: "নোটিশ, ইভেন্ট, শিক্ষা উইজেট",
			cmp5: "EN / BN / AR অ্যাডমিন",
			cmp6: "ডোনেশন + bKash / Nagad",
			cmp7: "হিসাব, কালেকশন, স্বচ্ছতা PDF",
			cmp8: "সদস্য, চাঁদা, হাজিরা, কার্ড",
			cmp9: "হল বুকিং, মাদরাসা, স্বেচ্ছাসেবক",
			cmp10: "প্রিমিয়াম উইজেট ডিজাইন",
			cmp11: "ইমাম / মুয়াজ্জিন রোল",
			cmp12: "ট্রেজারার / টিচার রোল",
			t_p1: "লোকাল ইঞ্জিন, Karachi/MWL/… প্রিসেট",
			t_p2: "CSV টিমটেবল API/লোকালকে ওভাররাইড করে",
			t_p3: "মাসিক প্রিন্ট + অ্যাডমিনে ৭ দিনের প্রিভিউ",
			t_p4: "শর্টকোড, ব্লক, REST today/month",
			t_f1: "এ সপ্তাহের খতিব + দুই জামাত",
			t_f2: "খুতবা আর্কাইভ ও সার্চ",
			t_f3: "খতিব প্রোফাইল ও শিডিউল",
			t_f4: "ইমামকে প্রশ্ন পাবলিক ফর্ম",
			t_t1: "ক্লাসিক, স্প্লিট, ফোকাস লেআউট",
			t_t2: "ইকামাহর আশেপাশে ডিম + কুইয়েট",
			t_t3: "সেলফ-হোস্টেড ফন্ট — রানটাইমে Google নেই",
			t_t4: "প্রো: জুমা/ঈদ কালেকশন স্লাইড",
			t_g1: "ক্যাশ কনফার্ম না হওয়া পর্যন্ত পেন্ডিং",
			t_g2: "bKash / Nagad কলব্যাক",
			t_g3: "রেস্ট্রিক্টেড যাকাত / সদকা ফান্ড",
			t_g4: "পাবলিক [masjidos_financials]",
			raised: "৳২০ লাখের মধ্যে ৳৮.৪৭ লাখ উঠেছে",
			mock_local: "লোকাল হিসাব · ঢাকা",
			mock_jumuah: "জুমা",
			mock_khatib: "এ সপ্তাহের খতিব",
			topic: "জিহবা হিফাজত · ১:১৫ PM",
			tv_path: "/masjidos-display/",
			camp: "মসজিদ সম্প্রসারণ",
		},
	};

	var features = [
		{
			id: "prayer",
			tier: "free",
			en: {
				t: "Prayer times",
				s: "Local calc, Iqamah rules, Qibla, Hijri, monthly print.",
				d: [
					"Default local solar engine — no API required",
					"Optional Aladhan source or CSV masjid timetable",
					"Iqamah rules + per-prayer minute offsets",
					"Qibla, Hijri ±3, Ishraq / Zawal extras",
					"Shortcode [masjidos_prayer_times] + block",
				],
			},
			bn: {
				t: "নামাজের সময়",
				s: "লোকাল হিসাব, ইকামাহ, কিবলা, হিজরি, মাসিক প্রিন্ট।",
				d: [
					"ডিফল্ট লোকাল সোলার ইঞ্জিন — API লাগে না",
					"ঐচ্ছিক Aladhan বা CSV টিমটেবল",
					"ইকামাহ রুল + মিনিট অফসেট",
					"কিবলা, হিজরি ±৩, ইশরাক / জাওয়াল",
					"শর্টকোড [masjidos_prayer_times] + ব্লক",
				],
			},
		},
		{
			id: "tv",
			tier: "free",
			en: {
				t: "TV display",
				s: "Fullscreen lobby board at /masjidos-display/.",
				d: [
					"Classic / split / focus layouts",
					"Dim nights, quiet minutes, countdown alerts",
					"Self-hosted Outfit, Cairo, Noto Sans Bengali",
					"Announcement slides from the notice board",
				],
			},
			bn: {
				t: "TV ডিসপ্লে",
				s: "/masjidos-display/-এ ফুলস্ক্রিন লবি বোর্ড।",
				d: [
					"ক্লাসিক / স্প্লিট / ফোকাস লেআউট",
					"নাইট ডিম, কুইয়েট মিনিট, কাউন্টডাউন",
					"সেলফ-হোস্টেড ফন্ট",
					"নোটিশ থেকে স্লাইড",
				],
			},
		},
		{
			id: "minbar",
			tier: "free",
			en: {
				t: "Jumuah & Minbar",
				s: "Khatib, archive, planner, Ask the Imam.",
				d: [
					"Two Jumuah sessions + khatib profile",
					"Khutbah archive, this week’s khatib, search",
					"Khatib profiles & Friday schedule",
					"[masjidos_ask_imam] public questions",
				],
			},
			bn: {
				t: "জুমা ও মিনবার",
				s: "খতিব, আর্কাইভ, প্ল্যানার, ইমামকে প্রশ্ন।",
				d: [
					"দুই জুমা + খতিব প্রোফাইল",
					"খুতবা আর্কাইভ, এ সপ্তাহের খতিব, সার্চ",
					"খতিব প্রোফাইল ও শিডিউল",
					"[masjidos_ask_imam] পাবলিক প্রশ্ন",
				],
			},
		},
		{
			id: "community",
			tier: "free",
			en: {
				t: "Notices, events, calendar",
				s: "Ticker, list, iCal, Islamic calendar.",
				d: [
					"[masjidos_announcements] list or ticker",
					"[masjidos_events] + iCal export",
					"[masjidos_islamic_calendar]",
					"Date windows use masjid timezone",
				],
			},
			bn: {
				t: "নোটিশ, ইভেন্ট, ক্যালেন্ডার",
				s: "টিকার, তালিকা, iCal, ইসলামিক ক্যালেন্ডার।",
				d: [
					"[masjidos_announcements] লিস্ট বা টিকার",
					"[masjidos_events] + iCal",
					"[masjidos_islamic_calendar]",
					"তারিখ মসজিদের টাইমজোনে",
				],
			},
		},
		{
			id: "edu",
			tier: "free",
			en: {
				t: "Islamic education",
				s: "Duas, Quran, Hadith, 99 Names, articles.",
				d: [
					"Duas & Azkar + Duas Library CPT",
					"Verse, Hadith, audio Quran, 99 Names",
					"Islamic Articles CPT with public grid",
					"EN / BN / AR widget language",
				],
			},
			bn: {
				t: "ইসলামি শিক্ষা",
				s: "দুআ, কুরআন, হাদিস, ৯৯ নাম, আর্টিকেল।",
				d: [
					"দুআ ও আযকার + লাইব্রেরি CPT",
					"আয়াত, হাদিস, অডিও কুরআন, ৯৯ নাম",
					"ইসলামি আর্টিকেল CPT",
					"উইজেট ভাষা en / bn / ar",
				],
			},
		},
		{
			id: "admin",
			tier: "free",
			en: {
				t: "Admin app & roles",
				s: "Fullscreen SPA, Imam / Muazzin caps.",
				d: [
					"Fullscreen MasjidOS admin, not wp-admin clutter",
					"Imam and Muazzin roles with custom caps",
					"Docs + shortcode generators",
					"REST masjidos/v1 — almost no admin-ajax",
				],
			},
			bn: {
				t: "অ্যাডমিন অ্যাপ ও রোল",
				s: "ফুলস্ক্রিন SPA, ইমাম / মুয়াজ্জিন ক্যাপ।",
				d: [
					"ফুলস্ক্রিন MasjidOS অ্যাডমিন",
					"ইমাম ও মুয়াজ্জিন রোল",
					"ডকস + শর্টকোড জেনারেটর",
					"REST masjidos/v1",
				],
			},
		},
		{
			id: "donate",
			tier: "pro",
			en: {
				t: "Donations & gateways",
				s: "Campaigns, cash, bKash / Nagad, receipts.",
				d: [
					"[masjidos_campaign] and [masjidos_donation_form]",
					"Pending until treasurer confirms (cash/bank)",
					"bKash & Nagad sandbox / live",
					"Optional post into Accounts funds",
				],
			},
			bn: {
				t: "ডোনেশন ও গেটওয়ে",
				s: "ক্যাম্পেইন, ক্যাশ, bKash / Nagad, রসিদ।",
				d: [
					"[masjidos_campaign] ও [masjidos_donation_form]",
					"ক্যাশ/ব্যাংক কনফার্ম না হওয়া পর্যন্ত পেন্ডিং",
					"bKash ও Nagad স্যান্ডবক্স / লাইভ",
					"অ্যাকাউন্টস ফান্ডে পোস্ট করা যায়",
				],
			},
		},
		{
			id: "accounts",
			tier: "pro",
			en: {
				t: "Accounts & collections",
				s: "Funds, ledger, Jumuah/Eid board, transparency.",
				d: [
					"Restricted funds: Zakat, Sadaqah, Lillah, Fidya",
					"Ledger, transfers, budgets, month lock",
					"[masjidos_collections] + TV collections slide",
					"[masjidos_transparency_report] + Print/PDF",
				],
			},
			bn: {
				t: "হিসাব ও কালেকশন",
				s: "ফান্ড, লেজার, জুমা/ঈদ বোর্ড, স্বচ্ছতা।",
				d: [
					"রেস্ট্রিক্টেড ফান্ড: যাকাত, সদকা, লিল্লাহ, ফিদয়া",
					"লেজার, ট্রান্সফার, বাজেট, মাস লক",
					"[masjidos_collections] + TV স্লাইড",
					"[masjidos_transparency_report] + প্রিন্ট/PDF",
				],
			},
		},
		{
			id: "members",
			tier: "pro",
			en: {
				t: "Members, dues, attendance",
				s: "Families, cards, directory, reports.",
				d: [
					"Private CRM + optional public directory",
					"Dues with reminder cron",
					"Jumuah attendance + print/CSV report",
					"A6 membership card (browser → PDF)",
				],
			},
			bn: {
				t: "সদস্য, চাঁদা, হাজিরা",
				s: "পরিবার, কার্ড, ডিরেক্টরি, রিপোর্ট।",
				d: [
					"প্রাইভেট CRM + ঐচ্ছিক পাবলিক ডিরেক্টরি",
					"চাঁদা রিমাইন্ডার ক্রন",
					"জুমা হাজিরা + প্রিন্ট/CSV",
					"A6 সদস্য কার্ড",
				],
			},
		},
		{
			id: "ops",
			tier: "pro",
			en: {
				t: "Hall, school, volunteers",
				s: "Bookings, madrasa fees, teacher portal.",
				d: [
					"[masjidos_facility_booking] with conflict check",
					"Classes, students, fees, guardian reminders",
					"Teacher login at /masjidos-teacher/",
					"[masjidos_volunteers] shifts + signup",
				],
			},
			bn: {
				t: "হল, স্কুল, স্বেচ্ছাসেবক",
				s: "বুকিং, মাদরাসা ফি, টিচার পোর্টাল।",
				d: [
					"[masjidos_facility_booking] কনফ্লিক্ট চেক",
					"ক্লাস, স্টুডেন্ট, ফি, অভিভাবক রিমাইন্ডার",
					"/masjidos-teacher/ টিচার লগইন",
					"[masjidos_volunteers] শিফট + সাইনআপ",
				],
			},
		},
		{
			id: "designs",
			tier: "pro",
			en: {
				t: "Premium design packs",
				s: "Unlocks Free widget design keys.",
				d: [
					"Prayer: premium-card, mosque-display, ramadan-special",
					"Jumuah: premium-sermon, mosque-notice",
					"Monthly: premium-print, mosque-board, ramadan-monthly",
					"Notices: digital-board, ramadan-banner",
				],
			},
			bn: {
				t: "প্রিমিয়াম ডিজাইন প্যাক",
				s: "ফ্রি উইজেট ডিজাইন আনলক।",
				d: [
					"নামাজ: premium-card, mosque-display, ramadan-special",
					"জুমা: premium-sermon, mosque-notice",
					"মাসিক: premium-print, mosque-board, ramadan-monthly",
					"নোটিশ: digital-board, ramadan-banner",
				],
			},
		},
	];

	var lang = localStorage.getItem("mos-land-lang") === "bn" ? "bn" : "en";

	function applyLang() {
		document.documentElement.lang = lang;
		document.documentElement.dir = "ltr";
		document.querySelectorAll("[data-i18n]").forEach(function (el) {
			var key = el.getAttribute("data-i18n");
			var pack = dict[lang] || dict.en;
			if (pack[key]) el.textContent = pack[key];
		});
		document.querySelectorAll(".lang button").forEach(function (b) {
			b.classList.toggle("is-on", b.getAttribute("data-lang") === lang);
		});
		renderFeatures();
	}

	function renderFeatures() {
		var root = document.getElementById("feat-grid");
		if (!root) return;
		var filter = root.getAttribute("data-filter") || "all";
		root.innerHTML = "";
		features.forEach(function (f) {
			if (filter !== "all" && f.tier !== filter) return;
			var copy = f[lang] || f.en;
			var btn = document.createElement("button");
			btn.className = "feat";
			btn.type = "button";
			btn.setAttribute("data-id", f.id);
			btn.innerHTML =
				'<span class="pill pill--' +
				f.tier +
				'">' +
				(f.tier === "pro" ? (lang === "bn" ? "প্রো" : "Pro") : lang === "bn" ? "ফ্রি" : "Free") +
				"</span><h3></h3><p></p>";
			btn.querySelector("h3").textContent = copy.t;
			btn.querySelector("p").textContent = copy.s;
			btn.addEventListener("click", function () {
				openDrawer(f);
			});
			root.appendChild(btn);
		});
	}

	function openDrawer(f) {
		var copy = f[lang] || f.en;
		var drawer = document.getElementById("drawer");
		document.getElementById("drawer-pill").className = "pill pill--" + f.tier;
		document.getElementById("drawer-pill").textContent = f.tier === "pro" ? (lang === "bn" ? "প্রো" : "Pro") : lang === "bn" ? "ফ্রি" : "Free";
		document.getElementById("drawer-title").textContent = copy.t;
		var ul = document.getElementById("drawer-list");
		ul.innerHTML = "";
		copy.d.forEach(function (line) {
			var li = document.createElement("li");
			li.textContent = line;
			ul.appendChild(li);
		});
		drawer.classList.add("is-open");
		drawer.setAttribute("aria-hidden", "false");
	}

	function closeDrawer() {
		var drawer = document.getElementById("drawer");
		drawer.classList.remove("is-open");
		drawer.setAttribute("aria-hidden", "true");
	}

	/* Prayer board — Dhaka-like demo times against the visitor's clock */
	var demoTimes = [
		{ key: "Fajr", bn: "ফজর", h: 4, m: 18, iq: "4:35" },
		{ key: "Dhuhr", bn: "যোহর", h: 12, m: 1, iq: "12:30" },
		{ key: "Asr", bn: "আসর", h: 16, m: 12, iq: "16:30" },
		{ key: "Maghrib", bn: "মাগরিব", h: 18, m: 48, iq: "18:53" },
		{ key: "Isha", bn: "এশা", h: 20, m: 15, iq: "20:30" },
	];

	function pad(n) {
		return String(n).padStart(2, "0");
	}

	function tickBoard() {
		var now = new Date();
		var nextIdx = 0;
		var nextDate = null;
		demoTimes.forEach(function (p, i) {
			var d = new Date(now.getFullYear(), now.getMonth(), now.getDate(), p.h, p.m, 0);
			if (d <= now) d.setDate(d.getDate() + 1);
			if (!nextDate || d < nextDate) {
				nextDate = d;
				nextIdx = i;
			}
		});
		/* If next is tomorrow's Fajr, still highlight today's last upcoming today */
		var todayNext = -1;
		demoTimes.forEach(function (p, i) {
			var d = new Date(now.getFullYear(), now.getMonth(), now.getDate(), p.h, p.m, 0);
			if (d > now && todayNext === -1) todayNext = i;
		});
		var highlight = todayNext === -1 ? nextIdx : todayNext;

		var nameEl = document.getElementById("next-name");
		var timerEl = document.getElementById("next-timer");
		if (nameEl) nameEl.textContent = lang === "bn" ? demoTimes[highlight].bn : demoTimes[highlight].key;
		if (timerEl && nextDate) {
			var ms = Math.max(0, nextDate - now);
			if (todayNext !== -1) {
				var td = new Date(now.getFullYear(), now.getMonth(), now.getDate(), demoTimes[todayNext].h, demoTimes[todayNext].m, 0);
				ms = Math.max(0, td - now);
			}
			var s = Math.floor(ms / 1000);
			var hh = Math.floor(s / 3600);
			var mm = Math.floor((s % 3600) / 60);
			var ss = s % 60;
			timerEl.textContent = pad(hh) + ":" + pad(mm) + ":" + pad(ss);
		}

		var hijri = document.getElementById("board-hijri");
		if (hijri) {
			hijri.innerHTML =
				(lang === "bn" ? "২২ মুহাররম ১৪৪৮" : "22 Muharram 1448") +
				"<br>" +
				now.toLocaleDateString(lang === "bn" ? "bn-BD" : "en-GB", { weekday: "long" });
		}

		document.querySelectorAll("[data-prayer-row]").forEach(function (row) {
			var i = Number(row.getAttribute("data-prayer-row"));
			row.classList.toggle("is-now", i === highlight);
			var label = row.querySelector("[data-prayer-name]");
			if (label) label.textContent = lang === "bn" ? demoTimes[i].bn : demoTimes[i].key;
		});
	}

	function initTour() {
		var tabs = document.querySelectorAll(".tour__tab");
		var stages = document.querySelectorAll(".tour__stage");
		tabs.forEach(function (tab) {
			tab.addEventListener("click", function () {
				var id = tab.getAttribute("data-tour");
				tabs.forEach(function (t) {
					t.classList.toggle("is-on", t === tab);
				});
				stages.forEach(function (s) {
					s.classList.toggle("is-on", s.getAttribute("data-tour") === id);
				});
				if (id === "give") {
					var bar = document.getElementById("raise-bar");
					if (bar) bar.style.width = "42%";
				}
			});
		});
	}

	function initTv() {
		var slides = document.querySelectorAll(".tv-slide");
		var dots = document.querySelectorAll(".tv-dots span");
		if (!slides.length) return;
		var i = 0;
		setInterval(function () {
			i = (i + 1) % slides.length;
			slides.forEach(function (s, n) {
				s.classList.toggle("is-on", n === i);
			});
			dots.forEach(function (d, n) {
				d.classList.toggle("is-on", n === i);
			});
		}, 3800);
	}

	function initNav() {
		var nav = document.querySelector(".nav");
		var burger = document.querySelector(".nav__burger");
		if (burger) {
			burger.addEventListener("click", function () {
				nav.classList.toggle("is-open");
			});
		}
		document.querySelectorAll('.nav__links a[href^="#"]').forEach(function (a) {
			a.addEventListener("click", function () {
				nav.classList.remove("is-open");
			});
		});
		var map = [
			["#features", "a[href='#features']"],
			["#compare", "a[href='#compare']"],
			["#pricing", "a[href='#pricing']"],
		];
		var els = map.map(function (pair) {
			return [document.querySelector(pair[0]), document.querySelector(pair[1])];
		});
		window.addEventListener(
			"scroll",
			function () {
				var y = window.scrollY + 90;
				els.forEach(function (pair) {
					if (!pair[0] || !pair[1]) return;
					var top = pair[0].offsetTop;
					var bottom = top + pair[0].offsetHeight;
					pair[1].classList.toggle("is-active", y >= top && y < bottom);
				});
			},
			{ passive: true }
		);
	}

	document.addEventListener("DOMContentLoaded", function () {
		applyLang();
		tickBoard();
		setInterval(tickBoard, 1000);
		initTour();
		initTv();
		initNav();

		document.querySelectorAll(".lang button").forEach(function (b) {
			b.addEventListener("click", function () {
				lang = b.getAttribute("data-lang");
				localStorage.setItem("mos-land-lang", lang);
				applyLang();
				tickBoard();
			});
		});

		document.querySelectorAll(".filter button").forEach(function (b) {
			b.addEventListener("click", function () {
				document.querySelectorAll(".filter button").forEach(function (x) {
					x.classList.toggle("is-on", x === b);
				});
				document.getElementById("feat-grid").setAttribute("data-filter", b.getAttribute("data-filter"));
				renderFeatures();
			});
		});

		var dim = document.getElementById("drawer-dim");
		var close = document.getElementById("drawer-close");
		if (dim) dim.addEventListener("click", closeDrawer);
		if (close) close.addEventListener("click", closeDrawer);
		document.addEventListener("keydown", function (e) {
			if (e.key === "Escape") closeDrawer();
		});

		var diff = document.getElementById("diff-only");
		var table = document.querySelector("table.compare");
		if (diff && table) {
			table.classList.toggle("show-all", !diff.checked);
			diff.addEventListener("change", function () {
				table.classList.toggle("show-all", !diff.checked);
			});
		}

		var bar = document.getElementById("raise-bar");
		if (bar && "IntersectionObserver" in window) {
			new IntersectionObserver(
				function (entries) {
					entries.forEach(function (en) {
						if (en.isIntersecting) bar.style.width = "42%";
					});
				},
				{ threshold: 0.4 }
			).observe(bar.parentElement);
		}
	});
})();
