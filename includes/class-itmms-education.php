<?php
/**
 * Content and Education Module for MasjidOS.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles education features and post types.
 */
class ITMMS_Education {

	/**
	 * Register Custom Post Types and taxonomies.
	 */
	public static function register_post_type(): void {
		$labels = [
			'name'               => _x( 'Islamic Articles', 'post type general name', 'masjidos' ),
			'singular_name'      => _x( 'Islamic Article', 'post type singular name', 'masjidos' ),
			'menu_name'          => _x( 'Articles', 'admin menu', 'masjidos' ),
			'name_admin_bar'     => _x( 'Islamic Article', 'add new on admin bar', 'masjidos' ),
			'add_new'            => _x( 'Add New', 'article', 'masjidos' ),
			'add_new_item'       => __( 'Add New Islamic Article', 'masjidos' ),
			'new_item'           => __( 'New Article', 'masjidos' ),
			'edit_item'          => __( 'Edit Article', 'masjidos' ),
			'view_item'          => __( 'View Article', 'masjidos' ),
			'all_items'          => __( 'All Articles', 'masjidos' ),
			'search_items'      => __( 'Search Articles', 'masjidos' ),
			'not_found'          => __( 'No articles found.', 'masjidos' ),
			'not_found_in_trash' => __( 'No articles found in Trash.', 'masjidos' ),
		];

		register_post_type(
			'itmms_article',
			[
				'labels'             => $labels,
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => 'masjidos',
				'query_var'          => true,
				'rewrite'            => [ 'slug' => 'islamic-article' ],
				'capability_type'    => 'post',
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => null,
				'supports'           => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
				'show_in_rest'       => true,
			]
		);

		$tax_labels = [
			'name'              => _x( 'Article Categories', 'taxonomy general name', 'masjidos' ),
			'singular_name'     => _x( 'Article Category', 'taxonomy singular name', 'masjidos' ),
			'search_items'      => __( 'Search Categories', 'masjidos' ),
			'all_items'         => __( 'All Categories', 'masjidos' ),
			'parent_item'       => __( 'Parent Category', 'masjidos' ),
			'parent_item_colon' => __( 'Parent Category:', 'masjidos' ),
			'edit_item'         => __( 'Edit Category', 'masjidos' ),
			'update_item'       => __( 'Update Category', 'masjidos' ),
			'add_new_item'      => __( 'Add New Category', 'masjidos' ),
			'new_item_name'     => __( 'New Category Name', 'masjidos' ),
			'menu_name'         => __( 'Categories', 'masjidos' ),
		];

		register_taxonomy(
			'itmms_article_category',
			[ 'itmms_article' ],
			[
				'hierarchical'      => true,
				'labels'            => $tax_labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => [ 'slug' => 'article-category' ],
				'show_in_rest'      => true,
			]
		);
	}

	/**
	 * Install default education taxonomy terms.
	 */
	public static function install_defaults(): void {
		self::register_post_type();

		$terms = [
			'Aqeedah' => __( 'Articles related to Islamic creed.', 'masjidos' ),
			'Fiqh'    => __( 'Articles related to Islamic jurisprudence.', 'masjidos' ),
			'Seerah'  => __( 'Articles related to the biography of Prophet Muhammad (peace be upon him).', 'masjidos' ),
		];

		foreach ( $terms as $name => $description ) {
			if ( ! term_exists( $name, 'itmms_article_category' ) ) {
				wp_insert_term( $name, 'itmms_article_category', [ 'description' => $description ] );
			}
		}
	}

	/**
	 * Get Quran Verse of the Day.
	 */
	public static function get_verse_of_day(): array {
		$verses = [
			[
				'ar'  => 'اللَّهُ لَا إِلَٰهَ إِلَّا هُوَ الْحَيُّ الْقَيُّومُ',
				'en'  => 'Allah - there is no deity except Him, the Ever-Living, the Sustainer of all existence.',
				'bn'  => 'আল্লাহ, তিনি ছাড়া কোনো উপাস্য নেই; তিনি চিরঞ্জীব, সবকিছুর ধারক।',
				'ref' => 'Surah Al-Baqarah 2:255',
			],
			[
				'ar'  => 'إِنَّ مَعَ الْعُسْرِ يُسْرًا',
				'en'  => 'Indeed, with hardship comes ease.',
				'bn'  => 'নিশ্চয়ই কষ্টের সাথে স্বস্তি রয়েছে।',
				'ref' => 'Surah Ash-Sharh 94:6',
			],
			[
				'ar'  => 'وَقُلْ رَبِّ زِدْنِي عِلْمًا',
				'en'  => 'And say, "My Lord, increase me in knowledge."',
				'bn'  => 'বলুন, হে আমার রব, আমার জ্ঞান বৃদ্ধি করুন।',
				'ref' => 'Surah Ta-Ha 20:114',
			],
			[
				'ar'  => 'ادْعُونِي أَسْتَجِبْ لَكُمْ',
				'en'  => 'Call upon Me; I will respond to you.',
				'bn'  => 'তোমরা আমাকে ডাকো, আমি তোমাদের ডাকে সাড়া দেব।',
				'ref' => 'Surah Ghafir 40:60',
			],
			[
				'ar'  => 'إِنَّ اللَّهَ مَعَ الصَّابِرِينَ',
				'en'  => 'Indeed, Allah is with the patient.',
				'bn'  => 'নিশ্চয়ই আল্লাহ ধৈর্যশীলদের সাথে আছেন।',
				'ref' => 'Surah Al-Baqarah 2:153',
			],
		];

		$index = (int) gmdate( 'z' ) % count( $verses );
		return $verses[ $index ];
	}

	/**
	 * Get Hadith of the Day.
	 */
	public static function get_hadith_of_day(): array {
		$hadiths = [
			[
				'ar'  => 'إِنَّمَا الأَعْمَالُ بِالنِّيَّاتِ',
				'en'  => 'Actions are judged by intentions.',
				'bn'  => 'সব কাজ নিয়তের উপর নির্ভরশীল।',
				'ref' => 'Sahih Al-Bukhari 1',
			],
			[
				'ar'  => 'الدِّينُ النَّصِيحَةُ',
				'en'  => 'The religion is sincere advice.',
				'bn'  => 'দ্বীন হলো কল্যাণ কামনা করা।',
				'ref' => 'Sahih Muslim 55',
			],
			[
				'ar'  => 'لَا يُؤْمِنُ أَحَدُكُمْ حَتَّى يُحِبَّ لِأَخِيهِ مَا يُحِبُّ لِنَفْسِهِ',
				'en'  => 'None of you truly believes until he loves for his brother what he loves for himself.',
				'bn'  => 'তোমাদের কেউ পূর্ণ মুমিন হবে না, যতক্ষণ না সে নিজের জন্য যা ভালোবাসে তার ভাইয়ের জন্যও তা ভালোবাসে।',
				'ref' => 'Sahih Al-Bukhari 13',
			],
			[
				'ar'  => 'مَنْ كَانَ يُؤْمِنُ بِاللَّهِ وَالْيَوْمِ الآخِرِ فَلْيَقُلْ خَيْرًا أَوْ لِيَصْمُتْ',
				'en'  => 'Whoever believes in Allah and the Last Day should speak good or remain silent.',
				'bn'  => 'যে আল্লাহ ও শেষ দিনের প্রতি ঈমান রাখে, সে যেন ভালো কথা বলে অথবা চুপ থাকে।',
				'ref' => 'Sahih Al-Bukhari 6018',
			],
			[
				'ar'  => 'الْمُسْلِمُ مَنْ سَلِمَ الْمُسْلِمُونَ مِنْ لِسَانِهِ وَيَدِهِ',
				'en'  => 'A Muslim is the one from whose tongue and hand other Muslims are safe.',
				'bn'  => 'প্রকৃত মুসলিম সে, যার জিহ্বা ও হাত থেকে অন্য মুসলমান নিরাপদ থাকে।',
				'ref' => 'Sahih Al-Bukhari 10',
			],
		];

		$index = (int) gmdate( 'z' ) % count( $hadiths );
		return $hadiths[ $index ];
	}

	/**
	 * Get 99 Names of Allah.
	 */
	public static function get_allah_names(): array {
		$names = [
			[ 'الرحمن', 'Ar-Rahman', 'The Most Merciful', 'পরম দয়ালু' ],
			[ 'الرحيم', 'Ar-Raheem', 'The Especially Merciful', 'অতিশয় দয়ালু' ],
			[ 'الملك', 'Al-Malik', 'The King', 'সর্বাধিপতি' ],
			[ 'القدوس', 'Al-Quddus', 'The Holy', 'অতি পবিত্র' ],
			[ 'السلام', 'As-Salam', 'The Source of Peace', 'শান্তিদাতা' ],
			[ 'المؤمن', 'Al-Mu’min', 'The Giver of Faith', 'নিরাপত্তাদাতা' ],
			[ 'المهيمن', 'Al-Muhaymin', 'The Guardian', 'রক্ষক' ],
			[ 'العزيز', 'Al-Aziz', 'The Mighty', 'মহাপরাক্রমশালী' ],
			[ 'الجبار', 'Al-Jabbar', 'The Compeller', 'মহাপ্রতাপশালী' ],
			[ 'المتكبر', 'Al-Mutakabbir', 'The Supreme', 'মহিমান্বিত' ],
			[ 'الخالق', 'Al-Khaliq', 'The Creator', 'স্রষ্টা' ],
			[ 'البارئ', 'Al-Bari’,', 'The Maker', 'সৃষ্টিকারী' ],
			[ 'المصور', 'Al-Musawwir', 'The Fashioner', 'রূপদানকারী' ],
			[ 'الغفار', 'Al-Ghaffar', 'The Forgiving', 'পরম ক্ষমাশীল' ],
			[ 'القهار', 'Al-Qahhar', 'The Subduer', 'দমনকারী' ],
			[ 'الوهاب', 'Al-Wahhab', 'The Bestower', 'দানকারী' ],
			[ 'الرزاق', 'Ar-Razzaq', 'The Provider', 'রিজিকদাতা' ],
			[ 'الفتاح', 'Al-Fattah', 'The Opener', 'বিজয়দাতা' ],
			[ 'العليم', 'Al-Alim', 'The All-Knowing', 'সর্বজ্ঞ' ],
			[ 'القابض', 'Al-Qabid', 'The Withholder', 'সংকীর্ণকারী' ],
			[ 'الباسط', 'Al-Basit', 'The Expander', 'প্রশস্তকারী' ],
			[ 'الخافض', 'Al-Khafid', 'The Reducer', 'অবনতকারী' ],
			[ 'الرافع', 'Ar-Rafi’', 'The Exalter', 'উন্নতকারী' ],
			[ 'المعز', 'Al-Mu’izz', 'The Honorer', 'সম্মানদাতা' ],
			[ 'المذل', 'Al-Mudhill', 'The Humiliator', 'অপমানকারী' ],
			[ 'السميع', 'As-Sami’', 'The All-Hearing', 'সর্বশ্রোতা' ],
			[ 'البصير', 'Al-Basir', 'The All-Seeing', 'সর্বদ্রষ্টা' ],
			[ 'الحكم', 'Al-Hakam', 'The Judge', 'বিচারক' ],
			[ 'العدل', 'Al-Adl', 'The Just', 'ন্যায়পরায়ণ' ],
			[ 'اللطيف', 'Al-Latif', 'The Subtle', 'সূক্ষ্মদর্শী' ],
			[ 'الخبير', 'Al-Khabir', 'The All-Aware', 'সম্যক অবগত' ],
			[ 'الحليم', 'Al-Halim', 'The Forbearing', 'সহনশীল' ],
			[ 'العظيم', 'Al-Azim', 'The Magnificent', 'মহীয়ান' ],
			[ 'الغفور', 'Al-Ghafur', 'The All-Forgiving', 'ক্ষমাকারী' ],
			[ 'الشكور', 'Ash-Shakur', 'The Appreciative', 'কৃতজ্ঞতা গ্রহণকারী' ],
			[ 'العلي', 'Al-Aliyy', 'The Most High', 'সর্বোচ্চ' ],
			[ 'الكبير', 'Al-Kabir', 'The Greatest', 'মহান' ],
			[ 'الحفيظ', 'Al-Hafiz', 'The Preserver', 'সংরক্ষণকারী' ],
			[ 'المقيت', 'Al-Muqit', 'The Nourisher', 'রিজিকদাতা' ],
			[ 'الحسيب', 'Al-Hasib', 'The Reckoner', 'হিসাবগ্রহণকারী' ],
			[ 'الجليل', 'Al-Jalil', 'The Majestic', 'মহিমান্বিত' ],
			[ 'الكريم', 'Al-Karim', 'The Generous', 'উদার' ],
			[ 'الرقيب', 'Ar-Raqib', 'The Watchful', 'পর্যবেক্ষক' ],
			[ 'المجيب', 'Al-Mujib', 'The Responsive', 'সাড়া দানকারী' ],
			[ 'الواسع', 'Al-Wasi’', 'The All-Encompassing', 'ব্যাপক' ],
			[ 'الحكيم', 'Al-Hakim', 'The Wise', 'প্রজ্ঞাময়' ],
			[ 'الودود', 'Al-Wadud', 'The Loving', 'প্রেমময়' ],
			[ 'المجيد', 'Al-Majid', 'The Glorious', 'গৌরবময়' ],
			[ 'الباعث', 'Al-Ba’ith', 'The Resurrector', 'পুনরুত্থানকারী' ],
			[ 'الشهيد', 'Ash-Shahid', 'The Witness', 'সাক্ষী' ],
			[ 'الحق', 'Al-Haqq', 'The Truth', 'সত্য' ],
			[ 'الوكيل', 'Al-Wakil', 'The Trustee', 'কর্মবিধায়ক' ],
			[ 'القوي', 'Al-Qawiyy', 'The Strong', 'শক্তিশালী' ],
			[ 'المتين', 'Al-Matin', 'The Firm', 'সুদৃঢ়' ],
			[ 'الولي', 'Al-Waliyy', 'The Protecting Friend', 'অভিভাবক' ],
			[ 'الحميد', 'Al-Hamid', 'The Praiseworthy', 'প্রশংসিত' ],
			[ 'المحصي', 'Al-Muhsi', 'The Counter', 'গণনাকারী' ],
			[ 'المبدئ', 'Al-Mubdi’,', 'The Originator', 'প্রবর্তনকারী' ],
			[ 'المعيد', 'Al-Mu’id', 'The Restorer', 'পুনরায় সৃষ্টিকারী' ],
			[ 'المحيي', 'Al-Muhyi', 'The Giver of Life', 'জীবনদাতা' ],
			[ 'المميت', 'Al-Mumit', 'The Bringer of Death', 'মৃত্যুদাতা' ],
			[ 'الحي', 'Al-Hayy', 'The Ever-Living', 'চিরঞ্জীব' ],
			[ 'القيوم', 'Al-Qayyum', 'The Sustainer', 'ধারক' ],
			[ 'الواجد', 'Al-Wajid', 'The Finder', 'প্রাপ্তকারী' ],
			[ 'الماجد', 'Al-Majid', 'The Noble', 'মর্যাদাবান' ],
			[ 'الواحد', 'Al-Wahid', 'The One', 'এক' ],
			[ 'الأحد', 'Al-Ahad', 'The Unique', 'একক' ],
			[ 'الصمد', 'As-Samad', 'The Eternal Refuge', 'অমুখাপেক্ষী' ],
			[ 'القادر', 'Al-Qadir', 'The Able', 'সক্ষম' ],
			[ 'المقتدر', 'Al-Muqtadir', 'The Powerful', 'ক্ষমতাবান' ],
			[ 'المقدم', 'Al-Muqaddim', 'The Expediter', 'অগ্রবর্তীকারী' ],
			[ 'المؤخر', 'Al-Mu’akhkhir', 'The Delayer', 'পশ্চাতে রাখেন যিনি' ],
			[ 'الأول', 'Al-Awwal', 'The First', 'প্রথম' ],
			[ 'الآخر', 'Al-Akhir', 'The Last', 'শেষ' ],
			[ 'الظاهر', 'Az-Zahir', 'The Manifest', 'প্রকাশ্য' ],
			[ 'الباطن', 'Al-Batin', 'The Hidden', 'গোপন' ],
			[ 'الوالي', 'Al-Wali', 'The Governor', 'শাসক' ],
			[ 'المتعالي', 'Al-Muta’ali', 'The Most Exalted', 'সর্বোচ্চ' ],
			[ 'البر', 'Al-Barr', 'The Source of Goodness', 'সৎকর্মশীল' ],
			[ 'التواب', 'At-Tawwab', 'The Accepter of Repentance', 'তওবা কবুলকারী' ],
			[ 'المنتقم', 'Al-Muntaqim', 'The Avenger', 'প্রতিশোধ গ্রহণকারী' ],
			[ 'العفو', 'Al-Afuww', 'The Pardoner', 'ক্ষমাকারী' ],
			[ 'الرؤوف', 'Ar-Ra’uf', 'The Kind', 'স্নেহশীল' ],
			[ 'مالك الملك', 'Malik-ul-Mulk', 'Owner of Sovereignty', 'সার্বভৌমত্বের মালিক' ],
			[ 'ذو الجلال والإكرام', 'Dhul-Jalali wal-Ikram', 'Lord of Majesty and Honor', 'মহিমা ও সম্মানের অধিকারী' ],
			[ 'المقسط', 'Al-Muqsit', 'The Equitable', 'ন্যায়বিচারক' ],
			[ 'الجامع', 'Al-Jami’', 'The Gatherer', 'একত্রকারী' ],
			[ 'الغني', 'Al-Ghaniyy', 'The Rich', 'অমুখাপেক্ষী' ],
			[ 'المغني', 'Al-Mughni', 'The Enricher', 'অভাবমোচনকারী' ],
			[ 'المانع', 'Al-Mani’', 'The Preventer', 'প্রতিরোধকারী' ],
			[ 'الضار', 'Ad-Darr', 'The Distresser', 'ক্ষতিসাধনকারী' ],
			[ 'النافع', 'An-Nafi’', 'The Benefactor', 'উপকারকারী' ],
			[ 'النور', 'An-Nur', 'The Light', 'জ্যোতি' ],
			[ 'الهادي', 'Al-Hadi', 'The Guide', 'পথপ্রদর্শক' ],
			[ 'البديع', 'Al-Badi’', 'The Incomparable', 'অতুলনীয় স্রষ্টা' ],
			[ 'الباقي', 'Al-Baqi', 'The Everlasting', 'চিরস্থায়ী' ],
			[ 'الوارث', 'Al-Warith', 'The Inheritor', 'উত্তরাধিকারী' ],
			[ 'الرشيد', 'Ar-Rashid', 'The Guide', 'সঠিক পথপ্রদর্শক' ],
			[ 'الصبور', 'As-Sabur', 'The Patient', 'পরম ধৈর্যশীল' ],
		];

		return array_map(
			static function ( array $name ): array {
				return [
					'ar'    => $name[0],
					'trans' => $name[1],
					'en'    => $name[2],
					'bn'    => $name[3],
				];
			},
			$names
		);
	}

	/**
	 * List of commonly used Surahs for the Audio Quran.
	 */
	public static function get_surahs(): array {
		return [
			1   => [ 'name_en' => 'Al-Fatihah', 'name_bn' => 'আল-ফাতিহা', 'name_ar' => 'الفاتحة' ],
			2   => [ 'name_en' => 'Al-Baqarah', 'name_bn' => 'আল-বাকারা', 'name_ar' => 'البقرة' ],
			3   => [ 'name_en' => 'Ali Imran', 'name_bn' => 'আলে ইমরান', 'name_ar' => 'آل عمران' ],
			4   => [ 'name_en' => 'An-Nisa', 'name_bn' => 'আন-নিসা', 'name_ar' => 'النساء' ],
			5   => [ 'name_en' => 'Al-Ma’idah', 'name_bn' => 'আল-মায়িদা', 'name_ar' => 'المائدة' ],
			36  => [ 'name_en' => 'Ya-Sin', 'name_bn' => 'ইয়াসিন', 'name_ar' => 'يس' ],
			55  => [ 'name_en' => 'Ar-Rahman', 'name_bn' => 'আর-রহমান', 'name_ar' => 'الرحمن' ],
			67  => [ 'name_en' => 'Al-Mulk', 'name_bn' => 'আল-মুলক', 'name_ar' => 'الملك' ],
			112 => [ 'name_en' => 'Al-Ikhlas', 'name_bn' => 'আল-ইখলাস', 'name_ar' => 'الإخلاص' ],
			113 => [ 'name_en' => 'Al-Falaq', 'name_bn' => 'আল-ফালাক', 'name_ar' => 'الفلق' ],
			114 => [ 'name_en' => 'An-Nas', 'name_bn' => 'আন-নাস', 'name_ar' => 'الناس' ],
		];
	}
}
