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
	 * Register Gutenberg blocks.
	 */
	public function register_blocks(): void {
		$language_attr = [
			'type'    => 'string',
			'default' => 'en',
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
					'title'    => $title_attr( __( 'Islamic Calendar', 'masjidos' ) ),
					'language' => $language_attr,
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

	/**
	 * Enqueue Gutenberg block editor assets.
	 */
	public function enqueue_block_editor_assets(): void {
		wp_enqueue_script(
			'itmms-block-editor',
			ITMMS_PLUGIN_URL . 'admin/assets/js/block-editor.js',
			[ 'wp-blocks', 'wp-components', 'wp-block-editor', 'wp-element', 'wp-i18n' ],
			ITMMS_VERSION,
			true
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'itmms-block-editor', 'masjidos', ITMMS_PLUGIN_DIR . 'languages' );
		}

		wp_localize_script(
			'itmms-block-editor',
			'itmmsBlockData',
			[
				'defaultLanguage' => ITMMS_Settings::ui_language(),
			]
		);
	}

}
