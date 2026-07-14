<?php
/**
 * Built-in Duas & Azkar data provider.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Provides bundled Duas & Azkar labels and items.
 */
final class ITMMS_Duas_Azkar {

	private function __construct() {}

	/**
	 * @return array<string,string>
	 */
	public static function labels( string $language ): array {
		$labels = [
			'en' => [
				'title'       => __( 'Duas & Azkar', 'masjidos' ),
				'eyebrow'     => __( 'Daily remembrance', 'masjidos' ),
				'translation' => __( 'Meaning', 'masjidos' ),
				'source'      => __( 'Source', 'masjidos' ),
				'category'    => __( 'Category', 'masjidos' ),
			],
			'bn' => [
				'title'       => 'দোয়া ও আজকার',
				'eyebrow'     => 'প্রতিদিনের স্মরণ',
				'translation' => 'অর্থ',
				'source'      => 'সূত্র',
				'category'    => 'ধরন',
			],
			'ar' => [
				'title'       => 'الأدعية والأذكار',
				'eyebrow'     => 'ذكر يومي',
				'translation' => 'المعنى',
				'source'      => 'المصدر',
				'category'    => 'التصنيف',
			],
		];

		$extra_labels = [
			/* translators: %d: number of repetitions */
			'repeat'       => __( 'Read %d times', 'masjidos' ),
			'counter'      => __( 'Track recitation count', 'masjidos' ),
			'read'         => __( 'Read', 'masjidos' ),
			'reset'        => __( 'Reset counter', 'masjidos' ),
			'reset_short'  => __( 'Reset', 'masjidos' ),
			'share'        => __( 'Share', 'masjidos' ),
			'listen'       => __( 'Listen to pronunciation', 'masjidos' ),
			'listen_short' => __( 'Audio', 'masjidos' ),
			'copied'       => __( 'Copied', 'masjidos' ),
		];

		if ( 'bn' === $language ) {
			$extra_labels = [
				'repeat'       => '%d বার পড়ুন',
				'counter'      => 'পড়ার সংখ্যা রাখুন',
				'read'         => 'পড়েছি',
				'reset'        => 'কাউন্টার রিসেট',
				'reset_short'  => 'রিসেট',
				'share'        => 'শেয়ার',
				'listen'       => 'উচ্চারণ শুনুন',
				'listen_short' => 'অডিও',
				'copied'       => 'কপি হয়েছে',
			];
		}

		if ( 'ar' === $language ) {
			$extra_labels = [
				'repeat'       => 'اقرأ %d مرات',
				'counter'      => 'تتبع عدد التلاوات',
				'read'         => 'قرأت',
				'reset'        => 'إعادة العداد',
				'reset_short'  => 'إعادة',
				'share'        => 'مشاركة',
				'listen'       => 'استماع للنطق',
				'listen_short' => 'صوت',
				'copied'       => 'تم النسخ',
			];
		}

		return array_merge( $labels[ $language ] ?? $labels['en'], $extra_labels );
	}

	/**
	 * Return bundled Duas & Azkar items for the public widget.
	 *
	 * @param string $language Active widget language.
	 * @param string $category Requested category.
	 * @param int    $limit Maximum number of items.
	 * @return array<int,array<string,mixed>>
	 */
	public static function items( string $language, string $category, int $limit ): array {
		$items = [
			[
				'key'        => 'rabbana-atina',
				'categories' => [ 'quran', 'daily' ],
				'title'      => [
					'en' => __( 'Good in this world and the next', 'masjidos' ),
					'bn' => 'দুনিয়া ও আখিরাতের কল্যাণ',
					'ar' => 'خير الدنيا والآخرة',
				],
				'arabic'     => 'رَبَّنَا آتِنَا فِي الدُّنْيَا حَسَنَةً وَفِي الآخِرَةِ حَسَنَةً وَقِنَا عَذَابَ النَّارِ',
				'latin'      => 'Rabbana atina fid-dunya hasanatan wa fil-akhirati hasanatan wa qina adhaban-nar.',
				'meaning'    => [
					'en' => __( 'Our Lord, give us good in this world and good in the Hereafter, and protect us from the punishment of the Fire.', 'masjidos' ),
					'bn' => 'হে আমাদের রব, আমাদের দুনিয়ায় কল্যাণ দিন, আখিরাতে কল্যাণ দিন এবং আগুনের শাস্তি থেকে রক্ষা করুন।',
					'ar' => 'ربنا أعطنا خير الدنيا والآخرة وقنا عذاب النار.',
				],
				'source'     => __( 'Quran 2:201', 'masjidos' ),
				'repeat'     => 1,
			],
			[
				'key'        => 'rabbana-zalamna',
				'categories' => [ 'quran', 'forgiveness' ],
				'title'      => [
					'en' => __( 'Seeking forgiveness', 'masjidos' ),
					'bn' => 'ক্ষমা প্রার্থনা',
					'ar' => 'طلب المغفرة',
				],
				'arabic'     => 'رَبَّنَا ظَلَمْنَا أَنفُسَنَا وَإِن لَّمْ تَغْفِرْ لَنَا وَتَرْحَمْنَا لَنَكُونَنَّ مِنَ الْخَاسِرِينَ',
				'latin'      => 'Rabbana zalamna anfusana wa in lam taghfir lana wa tarhamna lanakunanna minal-khasirin.',
				'meaning'    => [
					'en' => __( 'Our Lord, we have wronged ourselves. If You do not forgive us and have mercy on us, we will surely be among the losers.', 'masjidos' ),
					'bn' => 'হে আমাদের রব, আমরা নিজেদের প্রতি জুলুম করেছি। আপনি যদি আমাদের ক্ষমা না করেন ও রহম না করেন, তবে আমরা ক্ষতিগ্রস্তদের অন্তর্ভুক্ত হব।',
					'ar' => 'ربنا ظلمنا أنفسنا، وإن لم تغفر لنا وترحمنا لنكونن من الخاسرين.',
				],
				'source'     => __( 'Quran 7:23', 'masjidos' ),
				'repeat'     => 1,
			],
			[
				'key'        => 'sayyidul-istighfar',
				'categories' => [ 'morning', 'evening', 'forgiveness' ],
				'title'      => [
					'en' => __( 'Master supplication for forgiveness', 'masjidos' ),
					'bn' => 'ক্ষমার শ্রেষ্ঠ দোয়া',
					'ar' => 'سيد الاستغفار',
				],
				'arabic'     => 'اللَّهُمَّ أَنْتَ رَبِّي لاَ إِلَهَ إِلاَّ أَنْتَ، خَلَقْتَنِي وَأَنَا عَبْدُكَ',
				'latin'      => 'Allahumma anta Rabbi la ilaha illa anta, khalaqtani wa ana abduka.',
				'meaning'    => [
					'en' => __( 'O Allah, You are my Lord. There is no god but You. You created me and I am Your servant.', 'masjidos' ),
					'bn' => 'হে আল্লাহ, আপনি আমার রব। আপনি ছাড়া কোনো উপাস্য নেই। আপনি আমাকে সৃষ্টি করেছেন এবং আমি আপনার বান্দা।',
					'ar' => 'اللهم أنت ربي، لا إله إلا أنت، خلقتني وأنا عبدك.',
				],
				'source'     => __( 'Bukhari', 'masjidos' ),
				'repeat'     => 1,
			],
			[
				'key'        => 'allahumma-afuw',
				'categories' => [ 'forgiveness', 'daily' ],
				'title'      => [
					'en' => __( 'Ask for pardon', 'masjidos' ),
					'bn' => 'ক্ষমা ও মার্জনা প্রার্থনা',
					'ar' => 'طلب العفو',
				],
				'arabic'     => 'اللَّهُمَّ إِنَّكَ عَفُوٌّ تُحِبُّ الْعَفْوَ فَاعْفُ عَنِّي',
				'latin'      => 'Allahumma innaka afuwwun tuhibbul-afwa fa’fu anni.',
				'meaning'    => [
					'en' => __( 'O Allah, You are Pardoning and You love to pardon, so pardon me.', 'masjidos' ),
					'bn' => 'হে আল্লাহ, আপনি ক্ষমাশীল, ক্ষমা করতে ভালোবাসেন; তাই আমাকে ক্ষমা করুন।',
					'ar' => 'اللهم إنك عفو تحب العفو فاعف عني.',
				],
				'source'     => __( 'Tirmidhi', 'masjidos' ),
				'repeat'     => 1,
			],
			[
				'key'        => 'subhanallahi-wa-bihamdihi',
				'categories' => [ 'morning', 'evening', 'daily' ],
				'title'      => [
					'en' => __( 'Simple daily dhikr', 'masjidos' ),
					'bn' => 'সহজ দৈনিক জিকির',
					'ar' => 'ذكر يومي يسير',
				],
				'arabic'     => 'سُبْحَانَ اللَّهِ وَبِحَمْدِهِ',
				'latin'      => 'SubhanAllahi wa bihamdihi.',
				'meaning'    => [
					'en' => __( 'Glory is to Allah and praise is to Him.', 'masjidos' ),
					'bn' => 'আল্লাহ পবিত্র, সকল প্রশংসা তাঁরই।',
					'ar' => 'سبحان الله وبحمده.',
				],
				'source'     => __( 'Bukhari and Muslim', 'masjidos' ),
				'repeat'     => 100,
			],
			[
				'key'        => 'hasbunallah',
				'categories' => [ 'quran', 'protection' ],
				'title'      => [
					'en' => __( 'Trust in Allah', 'masjidos' ),
					'bn' => 'আল্লাহর ওপর ভরসা',
					'ar' => 'التوكل على الله',
				],
				'arabic'     => 'حَسْبُنَا اللَّهُ وَنِعْمَ الْوَكِيلُ',
				'latin'      => 'HasbunAllahu wa ni’mal-wakil.',
				'meaning'    => [
					'en' => __( 'Allah is sufficient for us, and He is the best disposer of affairs.', 'masjidos' ),
					'bn' => 'আমাদের জন্য আল্লাহই যথেষ্ট এবং তিনি উত্তম কর্মবিধায়ক।',
					'ar' => 'حسبنا الله ونعم الوكيل.',
				],
				'source'     => __( 'Quran 3:173', 'masjidos' ),
				'repeat'     => 1,
			],
			[
				'key'        => 'before-eating',
				'categories' => [ 'food', 'daily' ],
				'title'      => [
					'en' => __( 'Before eating', 'masjidos' ),
					'bn' => 'খাবারের আগে',
					'ar' => __( 'Before eating', 'masjidos' ),
				],
				'arabic'     => 'بِسْمِ اللَّهِ',
				'latin'      => 'Bismillah.',
				'meaning'    => [
					'en' => __( 'In the name of Allah.', 'masjidos' ),
					'bn' => 'আল্লাহর নামে শুরু করছি।',
					'ar' => __( 'In the name of Allah.', 'masjidos' ),
				],
				'source'     => __( 'Abu Dawud and Tirmidhi', 'masjidos' ),
				'repeat'     => 1,
			],
			[
				'key'        => 'after-eating',
				'categories' => [ 'food', 'daily' ],
				'title'      => [
					'en' => __( 'After eating', 'masjidos' ),
					'bn' => 'খাবারের পরে',
					'ar' => __( 'After eating', 'masjidos' ),
				],
				'arabic'     => 'الْحَمْدُ لِلَّهِ الَّذِي أَطْعَمَنِي هَذَا وَرَزَقَنِيهِ',
				'latin'      => 'Alhamdu lillahil-ladhi atamani hadha wa razaqanihi.',
				'meaning'    => [
					'en' => __( 'All praise is for Allah who fed me this and provided it for me.', 'masjidos' ),
					'bn' => 'সব প্রশংসা আল্লাহর, যিনি আমাকে এই খাবার খাইয়েছেন এবং রিজিক দিয়েছেন।',
					'ar' => __( 'All praise is for Allah who fed me this and provided it for me.', 'masjidos' ),
				],
				'source'     => __( 'Abu Dawud and Tirmidhi', 'masjidos' ),
				'repeat'     => 1,
			],
			[
				'key'        => 'before-sleep',
				'categories' => [ 'sleep', 'evening', 'daily' ],
				'title'      => [
					'en' => __( 'Before sleeping', 'masjidos' ),
					'bn' => 'ঘুমের আগে',
					'ar' => __( 'Before sleeping', 'masjidos' ),
				],
				'arabic'     => 'بِاسْمِكَ اللَّهُمَّ أَمُوتُ وَأَحْيَا',
				'latin'      => 'Bismika Allahumma amutu wa ahya.',
				'meaning'    => [
					'en' => __( 'In Your name, O Allah, I die and I live.', 'masjidos' ),
					'bn' => 'হে আল্লাহ, আপনার নামেই আমি মরি এবং বাঁচি।',
					'ar' => __( 'In Your name, O Allah, I die and I live.', 'masjidos' ),
				],
				'source'     => __( 'Bukhari', 'masjidos' ),
				'repeat'     => 1,
			],
			[
				'key'        => 'wake-up',
				'categories' => [ 'sleep', 'morning', 'daily' ],
				'title'      => [
					'en' => __( 'After waking up', 'masjidos' ),
					'bn' => 'ঘুম থেকে উঠে',
					'ar' => __( 'After waking up', 'masjidos' ),
				],
				'arabic'     => 'الْحَمْدُ لِلَّهِ الَّذِي أَحْيَانَا بَعْدَ مَا أَمَاتَنَا وَإِلَيْهِ النُّشُورُ',
				'latin'      => 'Alhamdu lillahil-ladhi ahyana bada ma amatana wa ilayhin-nushur.',
				'meaning'    => [
					'en' => __( 'All praise is for Allah who gave us life after causing us to die, and to Him is the resurrection.', 'masjidos' ),
					'bn' => 'সব প্রশংসা আল্লাহর, যিনি আমাদের মৃত্যুসম ঘুমের পর জীবন দিলেন; আর তাঁর কাছেই প্রত্যাবর্তন।',
					'ar' => __( 'All praise is for Allah who gave us life after causing us to die, and to Him is the resurrection.', 'masjidos' ),
				],
				'source'     => __( 'Bukhari', 'masjidos' ),
				'repeat'     => 1,
			],
			[
				'key'        => 'leaving-home',
				'categories' => [ 'home', 'protection', 'daily' ],
				'title'      => [
					'en' => __( 'Leaving home', 'masjidos' ),
					'bn' => 'বাড়ি থেকে বের হওয়ার সময়',
					'ar' => __( 'Leaving home', 'masjidos' ),
				],
				'arabic'     => 'بِسْمِ اللَّهِ تَوَكَّلْتُ عَلَى اللَّهِ وَلَا حَوْلَ وَلَا قُوَّةَ إِلَّا بِاللَّهِ',
				'latin'      => 'Bismillahi tawakkaltu alallah, wa la hawla wa la quwwata illa billah.',
				'meaning'    => [
					'en' => __( 'In the name of Allah, I trust in Allah, and there is no power nor strength except with Allah.', 'masjidos' ),
					'bn' => 'আল্লাহর নামে বের হলাম, আল্লাহর ওপর ভরসা করলাম; আল্লাহ ছাড়া কোনো শক্তি ও ক্ষমতা নেই।',
					'ar' => __( 'In the name of Allah, I trust in Allah, and there is no power nor strength except with Allah.', 'masjidos' ),
				],
				'source'     => __( 'Abu Dawud and Tirmidhi', 'masjidos' ),
				'repeat'     => 1,
			],
			[
				'key'        => 'entering-masjid',
				'categories' => [ 'masjid', 'daily' ],
				'title'      => [
					'en' => __( 'Entering the masjid', 'masjidos' ),
					'bn' => 'মসজিদে প্রবেশের সময়',
					'ar' => __( 'Entering the masjid', 'masjidos' ),
				],
				'arabic'     => 'اللَّهُمَّ افْتَحْ لِي أَبْوَابَ رَحْمَتِكَ',
				'latin'      => 'Allahummaftah li abwaba rahmatik.',
				'meaning'    => [
					'en' => __( 'O Allah, open for me the doors of Your mercy.', 'masjidos' ),
					'bn' => 'হে আল্লাহ, আমার জন্য আপনার রহমতের দরজাগুলো খুলে দিন।',
					'ar' => __( 'O Allah, open for me the doors of Your mercy.', 'masjidos' ),
				],
				'source'     => __( 'Muslim', 'masjidos' ),
				'repeat'     => 1,
			],
			[
				'key'        => 'leaving-masjid',
				'categories' => [ 'masjid', 'daily' ],
				'title'      => [
					'en' => __( 'Leaving the masjid', 'masjidos' ),
					'bn' => 'মসজিদ থেকে বের হওয়ার সময়',
					'ar' => __( 'Leaving the masjid', 'masjidos' ),
				],
				'arabic'     => 'اللَّهُمَّ إِنِّي أَسْأَلُكَ مِنْ فَضْلِكَ',
				'latin'      => 'Allahumma inni asaluka min fadlik.',
				'meaning'    => [
					'en' => __( 'O Allah, I ask You from Your bounty.', 'masjidos' ),
					'bn' => 'হে আল্লাহ, আমি আপনার অনুগ্রহ প্রার্থনা করছি।',
					'ar' => __( 'O Allah, I ask You from Your bounty.', 'masjidos' ),
				],
				'source'     => __( 'Muslim', 'masjidos' ),
				'repeat'     => 1,
			],
			[
				'key'        => 'travel',
				'categories' => [ 'travel', 'protection' ],
				'title'      => [
					'en' => __( 'Travel supplication', 'masjidos' ),
					'bn' => 'সফরের দোয়া',
					'ar' => __( 'Travel supplication', 'masjidos' ),
				],
				'arabic'     => 'سُبْحَانَ الَّذِي سَخَّرَ لَنَا هَذَا وَمَا كُنَّا لَهُ مُقْرِنِينَ',
				'latin'      => 'Subhanalladhi sakhkhara lana hadha wa ma kunna lahu muqrinin.',
				'meaning'    => [
					'en' => __( 'Glory is to Him who has subjected this to us, and we could never have it by our own efforts.', 'masjidos' ),
					'bn' => 'পবিত্র তিনি, যিনি এটিকে আমাদের অধীন করেছেন; আমরা নিজেরা তা করতে সক্ষম ছিলাম না।',
					'ar' => __( 'Glory is to Him who has subjected this to us, and we could never have it by our own efforts.', 'masjidos' ),
				],
				'source'     => __( 'Quran 43:13', 'masjidos' ),
				'repeat'     => 1,
			],
			[
				'key'        => 'rain',
				'categories' => [ 'rain', 'daily' ],
				'title'      => [
					'en' => __( 'When it rains', 'masjidos' ),
					'bn' => 'বৃষ্টির সময়',
					'ar' => __( 'When it rains', 'masjidos' ),
				],
				'arabic'     => 'اللَّهُمَّ صَيِّبًا نَافِعًا',
				'latin'      => 'Allahumma sayyiban nafia.',
				'meaning'    => [
					'en' => __( 'O Allah, make it beneficial rain.', 'masjidos' ),
					'bn' => 'হে আল্লাহ, একে উপকারী বৃষ্টি বানিয়ে দিন।',
					'ar' => __( 'O Allah, make it beneficial rain.', 'masjidos' ),
				],
				'source'     => __( 'Bukhari', 'masjidos' ),
				'repeat'     => 1,
			],
		];

		if ( class_exists( 'ITMMS_Duas_Library' ) ) {
			$items = array_merge( $items, ITMMS_Duas_Library::items() );
		}

		$filtered = 'all' === $category ? $items : array_values(
			array_filter(
				$items,
				static function ( array $item ) use ( $category ): bool {
					return in_array( $category, $item['categories'], true );
				}
			)
		);

		if ( empty( $filtered ) ) {
			$filtered = $items;
		}

		return array_slice(
			array_map(
				function ( array $item ) use ( $language ): array {
					$item['title'] = $item['title'][ $language ] ?? $item['title']['en'];
					$item['meaning'] = ! empty( $item['meaning'][ $language ] ) ? $item['meaning'][ $language ] : $item['meaning']['en'];
					return $item;
				},
				$filtered
			),
			0,
			$limit
		);
	}

}
