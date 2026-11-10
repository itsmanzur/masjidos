<?php
/**
 * ITMMS_Public_Blocks methods for ITMMS_Public.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * @package MasjidOS
 */
trait ITMMS_Public_Blocks {

	/**
	 * Register custom MasjidOS block category.
	 *
	 * @param array<int,array<string,mixed>> $categories Existing block categories.
	 * @return array<int,array<string,mixed>>
	 */
	public function register_block_category( array $categories ): array {
		foreach ( $categories as $category ) {
			if ( isset( $category['slug'] ) && 'masjidos' === $category['slug'] ) {
				return $categories;
			}
		}

		array_unshift(
			$categories,
			[
				'slug'  => 'masjidos',
				'title' => __( 'MasjidOS', 'masjidos' ),
				'icon'  => null,
			]
		);

		return $categories;
	}

	/**
	 * Register Gutenberg blocks.
	 */
	public function register_blocks(): void {
		add_filter( 'block_categories_all', [ $this, 'register_block_category' ], 10, 1 );

		$default_lang  = class_exists( 'ITMMS_Settings' ) ? ITMMS_Settings::ui_language() : 'en';
		$language_attr = [
			'type'    => 'string',
			'default' => $default_lang,
		];
		$title_attr = static function ( string $default ): array {
			return [
				'type'    => 'string',
				'default' => $default,
			];
		};

		register_block_type(
			'masjidos/prayer-times',
			[
				'render_callback' => [ $this, 'render_prayer_times_block' ],
				'attributes'      => [
					'title'    => $title_attr( __( 'Prayer Times', 'masjidos' ) ),
					'design'   => [ 'type' => 'string', 'default' => 'classic' ],
					'language' => $language_attr,
					'qibla'    => [ 'type' => 'string', 'default' => 'yes' ],
					'meta'     => [ 'type' => 'string', 'default' => 'yes' ],
					'iqamah'   => [ 'type' => 'string', 'default' => 'yes' ],
					'hijri'    => [ 'type' => 'string', 'default' => 'yes' ],
				],
			]
		);

		register_block_type(
			'masjidos/islamic-calendar',
			[
				'render_callback' => [ $this, 'render_islamic_calendar_block' ],
				'attributes'      => [
					'title'      => $title_attr( __( 'Islamic Calendar', 'masjidos' ) ),
					'language'   => $language_attr,
					'navigation' => [ 'type' => 'string', 'default' => 'yes' ],
					'month'      => [ 'type' => 'string', 'default' => '' ],
					'year'       => [ 'type' => 'string', 'default' => '' ],
				],
			]
		);

		register_block_type(
			'masjidos/monthly-prayer-times',
			[
				'render_callback' => [ $this, 'render_monthly_prayer_times_block' ],
				'attributes'      => [
					'title'      => $title_attr( __( 'Monthly Prayer Timetable', 'masjidos' ) ),
					'language'   => $language_attr,
					'design'     => [ 'type' => 'string', 'default' => 'table' ],
					'month'      => [ 'type' => 'string', 'default' => '' ],
					'year'       => [ 'type' => 'string', 'default' => '' ],
					'iqamah'     => [ 'type' => 'string', 'default' => 'no' ],
					'navigation' => [ 'type' => 'string', 'default' => 'yes' ],
					'extras'     => [ 'type' => 'string', 'default' => 'no' ],
				],
			]
		);

		register_block_type(
			'masjidos/jumuah',
			[
				'render_callback' => [ $this, 'render_jumuah_block' ],
				'attributes'      => [
					'title'    => $title_attr( __( 'Jumuah Prayer', 'masjidos' ) ),
					'language' => $language_attr,
					'design'   => [ 'type' => 'string', 'default' => 'classic' ],
					'meta'     => [ 'type' => 'string', 'default' => 'yes' ],
				],
			]
		);

		register_block_type(
			'masjidos/announcements',
			[
				'render_callback' => [ $this, 'render_announcements_block' ],
				'attributes'      => [
					'title'     => $title_attr( __( 'Masjid Notices', 'masjidos' ) ),
					'language'  => $language_attr,
					'design'    => [ 'type' => 'string', 'default' => 'list' ],
					'type'      => [ 'type' => 'string', 'default' => 'all' ],
					'limit'     => [ 'type' => 'string', 'default' => '5' ],
					'show_date' => [ 'type' => 'string', 'default' => 'yes' ],
				],
			]
		);

		register_block_type(
			'masjidos/events',
			[
				'render_callback' => [ $this, 'render_events_block' ],
				'attributes'      => [
					'title'    => $title_attr( __( 'Upcoming Events', 'masjidos' ) ),
					'language' => $language_attr,
					'limit'    => [ 'type' => 'string', 'default' => '5' ],
				],
			]
		);

		register_block_type(
			'masjidos/duas-azkar',
			[
				'render_callback' => [ $this, 'render_duas_azkar_block' ],
				'attributes'      => [
					'title'    => $title_attr( __( 'Duas & Azkar', 'masjidos' ) ),
					'language' => $language_attr,
					'category' => [ 'type' => 'string', 'default' => 'all' ],
					'limit'    => [ 'type' => 'string', 'default' => '4' ],
					'design'   => [ 'type' => 'string', 'default' => 'cards' ],
					'source'   => [ 'type' => 'string', 'default' => 'yes' ],
					'counter'  => [ 'type' => 'string', 'default' => 'yes' ],
					'share'    => [ 'type' => 'string', 'default' => 'yes' ],
					'audio'    => [ 'type' => 'string', 'default' => 'yes' ],
				],
			]
		);

		register_block_type(
			'masjidos/custom-duas',
			[
				'render_callback' => [ $this, 'render_custom_duas_block' ],
				'attributes'      => [
					'title'    => $title_attr( __( 'Duas & Azkar', 'masjidos' ) ),
					'language' => $language_attr,
					'category' => [ 'type' => 'string', 'default' => 'all' ],
					'limit'    => [ 'type' => 'string', 'default' => '12' ],
					'design'   => [ 'type' => 'string', 'default' => 'cards' ],
					'source'   => [ 'type' => 'string', 'default' => 'yes' ],
					'counter'  => [ 'type' => 'string', 'default' => 'yes' ],
					'share'    => [ 'type' => 'string', 'default' => 'yes' ],
					'audio'    => [ 'type' => 'string', 'default' => 'yes' ],
				],
			]
		);

		register_block_type(
			'masjidos/khutbah-archive',
			[
				'render_callback' => [ $this, 'render_khutbah_archive_block' ],
				'attributes'      => [
					'title'    => $title_attr( __( 'Jumuah Khutbah Archive', 'masjidos' ) ),
					'language' => $language_attr,
					'limit'    => [ 'type' => 'string', 'default' => '12' ],
					'category' => [ 'type' => 'string', 'default' => '' ],
				],
			]
		);

		register_block_type(
			'masjidos/khatib-this-week',
			[
				'render_callback' => [ $this, 'render_khatib_this_week_block' ],
				'attributes'      => [
					'title'    => $title_attr( __( 'This Week\'s Khatib', 'masjidos' ) ),
					'language' => $language_attr,
				],
			]
		);

		register_block_type(
			'masjidos/upcoming-khutbah',
			[
				'render_callback' => [ $this, 'render_upcoming_khutbah_block' ],
				'attributes'      => [
					'title'    => $title_attr( __( 'Upcoming Khutbahs', 'masjidos' ) ),
					'language' => $language_attr,
					'limit'    => [ 'type' => 'string', 'default' => '5' ],
				],
			]
		);

		register_block_type(
			'masjidos/khutbah-search',
			[
				'render_callback' => [ $this, 'render_khutbah_search_block' ],
				'attributes'      => [
					'title'    => $title_attr( __( 'Search Khutbah Archive', 'masjidos' ) ),
					'language' => $language_attr,
					'limit'    => [ 'type' => 'string', 'default' => '6' ],
				],
			]
		);

		register_block_type(
			'masjidos/quran-verse',
			[
				'render_callback' => [ $this, 'render_quran_verse_block' ],
				'attributes'      => [
					'title'    => $title_attr( __( 'Quran Verse of the Day', 'masjidos' ) ),
					'language' => $language_attr,
					'design'   => [ 'type' => 'string', 'default' => 'classic' ],
					'share'    => [ 'type' => 'string', 'default' => 'yes' ],
					'tafsir'   => [ 'type' => 'string', 'default' => 'yes' ],
				],
			]
		);

		register_block_type(
			'masjidos/hadith',
			[
				'render_callback' => [ $this, 'render_hadith_block' ],
				'attributes'      => [
					'title'    => $title_attr( __( 'Hadith of the Day', 'masjidos' ) ),
					'language' => $language_attr,
					'design'   => [ 'type' => 'string', 'default' => 'classic' ],
					'share'    => [ 'type' => 'string', 'default' => 'yes' ],
				],
			]
		);

		register_block_type(
			'masjidos/allah-names',
			[
				'render_callback' => [ $this, 'render_allah_names_block' ],
				'attributes'      => [
					'title'    => $title_attr( __( '99 Names of Allah', 'masjidos' ) ),
					'language' => $language_attr,
					'design'   => [ 'type' => 'string', 'default' => 'grid' ],
					'limit'    => [ 'type' => 'string', 'default' => '99' ],
				],
			]
		);

		register_block_type(
			'masjidos/audio-quran',
			[
				'render_callback' => [ $this, 'render_audio_quran_block' ],
				'attributes'      => [
					'title'    => $title_attr( __( 'Audio Quran Player', 'masjidos' ) ),
					'language' => $language_attr,
					'design'   => [ 'type' => 'string', 'default' => 'classic' ],
				],
			]
		);

		register_block_type(
			'masjidos/articles',
			[
				'render_callback' => [ $this, 'render_articles_block' ],
				'attributes'      => [
					'title'    => $title_attr( __( 'Islamic Articles', 'masjidos' ) ),
					'language' => $language_attr,
					'category' => [ 'type' => 'string', 'default' => '' ],
					'limit'    => [ 'type' => 'string', 'default' => '6' ],
					'excerpt'  => [ 'type' => 'string', 'default' => 'yes' ],
					'design'   => [ 'type' => 'string', 'default' => 'grid' ],
				],
			]
		);

		register_block_type(
			'masjidos/ask-imam',
			[
				'render_callback' => [ $this, 'render_ask_imam_block' ],
				'attributes'      => [
					'title'    => $title_attr( __( 'Ask the Imam', 'masjidos' ) ),
					'language' => $language_attr,
				],
			]
		);

		register_block_type(
			'masjidos/imam-answers',
			[
				'render_callback' => [ $this, 'render_imam_answers_block' ],
				'attributes'      => [
					'title'    => $title_attr( __( 'Answered Questions', 'masjidos' ) ),
					'language' => $language_attr,
					'category' => [ 'type' => 'string', 'default' => '' ],
					'limit'    => [ 'type' => 'string', 'default' => '10' ],
				],
			]
		);
	}

	public function render_prayer_times_block( array $attributes ): string {
		return $this->render_prayer_times_shortcode( $attributes );
	}

	public function render_islamic_calendar_block( array $attributes ): string {
		return $this->render_islamic_calendar_shortcode( $attributes );
	}

	public function render_monthly_prayer_times_block( array $attributes ): string {
		return $this->render_monthly_prayer_times_shortcode( $attributes );
	}

	public function render_jumuah_block( array $attributes ): string {
		return $this->render_jumuah_shortcode( $attributes );
	}

	public function render_announcements_block( array $attributes ): string {
		return $this->render_announcements_shortcode( $attributes );
	}

	public function render_events_block( array $attributes ): string {
		return $this->render_events_shortcode( $attributes );
	}

	public function render_duas_azkar_block( array $attributes ): string {
		return $this->render_duas_azkar_shortcode( $attributes );
	}

	public function render_custom_duas_block( array $attributes ): string {
		return $this->render_custom_duas_shortcode( $attributes );
	}

	public function render_khutbah_archive_block( array $attributes ): string {
		return $this->render_khutbah_archive_shortcode( $attributes );
	}

	public function render_khatib_this_week_block( array $attributes ): string {
		return $this->render_khatib_this_week_shortcode( $attributes );
	}

	public function render_upcoming_khutbah_block( array $attributes ): string {
		return $this->render_upcoming_khutbah_shortcode( $attributes );
	}

	public function render_khutbah_search_block( array $attributes ): string {
		return $this->render_khutbah_search_shortcode( $attributes );
	}

	public function render_quran_verse_block( array $attributes ): string {
		return $this->render_quran_verse_shortcode( $attributes );
	}

	public function render_hadith_block( array $attributes ): string {
		return $this->render_hadith_shortcode( $attributes );
	}

	public function render_allah_names_block( array $attributes ): string {
		return $this->render_allah_names_shortcode( $attributes );
	}

	public function render_audio_quran_block( array $attributes ): string {
		return $this->render_audio_quran_shortcode( $attributes );
	}

	public function render_articles_block( array $attributes ): string {
		return $this->render_articles_shortcode( $attributes );
	}

	public function render_ask_imam_block( array $attributes ): string {
		if ( ! class_exists( 'ITMMS_Ask_Imam' ) ) {
			return '';
		}
		return ITMMS_Ask_Imam::get_instance()->render_submit_shortcode( $attributes );
	}

	public function render_imam_answers_block( array $attributes ): string {
		if ( ! class_exists( 'ITMMS_Ask_Imam' ) ) {
			return '';
		}
		return ITMMS_Ask_Imam::get_instance()->render_answers_shortcode( $attributes );
	}

	/**
	 * Helper to collect taxonomy terms as slug => name map for block editor dropdowns.
	 *
	 * @return array<string,string>
	 */
	private function get_taxonomy_options_for_blocks( string $taxonomy ): array {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return [];
		}
		$terms = get_terms(
			[
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
			]
		);
		if ( ! is_array( $terms ) ) {
			return [];
		}
		$options = [];
		foreach ( $terms as $term ) {
			if ( $term instanceof WP_Term ) {
				$options[ $term->slug ] = $term->name;
			}
		}
		return $options;
	}

	/**
	 * Enqueue Gutenberg block editor assets.
	 */
	public function enqueue_block_editor_assets(): void {
		wp_enqueue_style(
			'itmms-public-styles',
			ITMMS_PLUGIN_URL . 'public/assets/css/public.css',
			[],
			ITMMS_VERSION
		);

		wp_enqueue_script(
			'itmms-block-editor',
			ITMMS_PLUGIN_URL . 'admin/assets/js/block-editor.js',
			[ 'wp-blocks', 'wp-components', 'wp-block-editor', 'wp-element', 'wp-i18n', 'wp-server-side-render' ],
			ITMMS_VERSION,
			true
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'itmms-block-editor', 'masjidos', ITMMS_PLUGIN_DIR . 'languages' );
		}

		$settings = class_exists( 'ITMMS_Settings' ) ? ITMMS_Settings::get_all() : [];
		$modules  = isset( $settings['modules'] ) && is_array( $settings['modules'] ) ? $settings['modules'] : [];

		wp_localize_script(
			'itmms-block-editor',
			'itmmsBlockData',
			[
				'defaultLanguage'     => ITMMS_Settings::ui_language(),
				'proActive'           => function_exists( 'masjidos_pro_is_active' ) && masjidos_pro_is_active(),
				'customDuaCategories' => class_exists( 'ITMMS_Duas_Library' ) ? ITMMS_Duas_Library::category_options() : [],
				'khutbahCategories'   => class_exists( 'ITMMS_Khutbah' ) ? ITMMS_Khutbah::categories() : [],
				'articleCategories'   => class_exists( 'ITMMS_Education' ) ? $this->get_taxonomy_options_for_blocks( ITMMS_Education::TAXONOMY ) : [],
				'qaCategories'        => class_exists( 'ITMMS_Ask_Imam' ) ? $this->get_taxonomy_options_for_blocks( ITMMS_Ask_Imam::TAXONOMY ) : [],
				'moduleStatus'        => [
					'prayer_times'  => ! isset( $modules['prayer_times'] ) || ! empty( $modules['prayer_times'] ),
					'announcements' => ! isset( $modules['announcements'] ) || ! empty( $modules['announcements'] ),
					'events'        => ! empty( $modules['events'] ),
					'duas_azkar'    => ! isset( $modules['duas_azkar'] ) || ! empty( $modules['duas_azkar'] ),
					'jumuah'        => ! empty( $settings['jumuah']['enabled'] ),
				],
				'adminUrls'           => [
					'features'      => admin_url( 'admin.php?page=masjidos#features' ),
					'prayer_times'  => admin_url( 'admin.php?page=masjidos#prayer-times' ),
					'announcements' => admin_url( 'admin.php?page=masjidos#announcements' ),
					'events'        => admin_url( 'admin.php?page=masjidos#events' ),
					'minbar'        => admin_url( 'admin.php?page=masjidos#minbar' ),
					'articles'      => admin_url( 'edit.php?post_type=itmms_article' ),
					'duas'          => admin_url( 'edit.php?post_type=itmms_dua' ),
					'ask_imam'      => admin_url( 'edit.php?post_type=itmms_imam_question' ),
				],
			]
		);
	}

}
