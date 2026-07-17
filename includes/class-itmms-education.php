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

	public const POST_TYPE = 'itmms_article';
	public const TAXONOMY  = 'itmms_article_category';

	public const META_LANGUAGE    = 'itmms_article_language';
	public const META_SOURCE      = 'itmms_article_source';
	public const META_AUTHOR      = 'itmms_article_author';
	public const META_TRANSLATOR  = 'itmms_article_translator';
	public const META_EXTERNAL    = 'itmms_article_external_url';
	public const META_AUDIO       = 'itmms_article_audio_url';
	public const META_TAKEAWAY    = 'itmms_article_takeaway';

	/**
	 * Wire article admin + public hooks.
	 */
	public static function init(): void {
		add_action( 'init', [ __CLASS__, 'register_post_meta_fields' ], 20 );
		add_action( 'add_meta_boxes', [ __CLASS__, 'add_meta_boxes' ] );
		add_action( 'save_post_' . self::POST_TYPE, [ __CLASS__, 'save_meta_box' ], 10, 2 );
		add_action( 'enqueue_block_editor_assets', [ __CLASS__, 'enqueue_article_editor_assets' ] );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_article_admin_assets' ] );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', [ __CLASS__, 'posts_columns' ] );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', [ __CLASS__, 'render_post_column' ], 10, 2 );
		add_filter( 'body_class', [ __CLASS__, 'body_class' ] );
		add_filter( 'the_content', [ __CLASS__, 'wrap_singular_content' ], 8 );
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_singular_assets' ] );
	}

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
			self::POST_TYPE,
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
			self::TAXONOMY,
			[ self::POST_TYPE ],
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
	 * Register REST-visible article meta for the block editor.
	 */
	public static function register_post_meta_fields(): void {
		$auth = static function (): bool {
			return current_user_can( 'edit_posts' );
		};

		register_post_meta(
			self::POST_TYPE,
			self::META_LANGUAGE,
			[
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'default'           => 'en',
				'auth_callback'     => $auth,
				'sanitize_callback' => [ __CLASS__, 'sanitize_language' ],
			]
		);

		register_post_meta(
			self::POST_TYPE,
			self::META_SOURCE,
			[
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'default'           => '',
				'auth_callback'     => $auth,
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		register_post_meta(
			self::POST_TYPE,
			self::META_AUTHOR,
			[
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'default'           => '',
				'auth_callback'     => $auth,
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		register_post_meta(
			self::POST_TYPE,
			self::META_TRANSLATOR,
			[
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'default'           => '',
				'auth_callback'     => $auth,
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		register_post_meta(
			self::POST_TYPE,
			self::META_EXTERNAL,
			[
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'default'           => '',
				'auth_callback'     => $auth,
				'sanitize_callback' => 'esc_url_raw',
			]
		);

		register_post_meta(
			self::POST_TYPE,
			self::META_AUDIO,
			[
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'default'           => '',
				'auth_callback'     => $auth,
				'sanitize_callback' => 'esc_url_raw',
			]
		);

		register_post_meta(
			self::POST_TYPE,
			self::META_TAKEAWAY,
			[
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'default'           => '',
				'auth_callback'     => $auth,
				'sanitize_callback' => 'sanitize_text_field',
			]
		);
	}

	/**
	 * @param mixed $value Raw language.
	 */
	public static function sanitize_language( $value ): string {
		$language = strtolower( sanitize_key( (string) $value ) );
		return in_array( $language, [ 'en', 'bn', 'ar' ], true ) ? $language : 'en';
	}

	public static function add_meta_boxes(): void {
		add_meta_box(
			'itmms-article-details',
			__( 'Article Details', 'masjidos' ),
			[ __CLASS__, 'render_meta_box' ],
			self::POST_TYPE,
			'side',
			'high'
		);
	}

	/**
	 * Classic / sidebar meta box (also visible under Gutenberg).
	 */
	public static function render_meta_box( WP_Post $post ): void {
		wp_nonce_field( 'itmms_save_article_meta', 'itmms_article_nonce' );

		$language   = self::sanitize_language( get_post_meta( $post->ID, self::META_LANGUAGE, true ) );
		$source     = (string) get_post_meta( $post->ID, self::META_SOURCE, true );
		$author     = (string) get_post_meta( $post->ID, self::META_AUTHOR, true );
		$translator = (string) get_post_meta( $post->ID, self::META_TRANSLATOR, true );
		$external   = (string) get_post_meta( $post->ID, self::META_EXTERNAL, true );
		$audio      = (string) get_post_meta( $post->ID, self::META_AUDIO, true );
		$takeaway   = (string) get_post_meta( $post->ID, self::META_TAKEAWAY, true );

		echo '<div class="itmms-article-meta-box">';
		echo '<p class="itmms-article-meta-help">' . esc_html__( 'Attribution and extras for this article. Titles stay as you type them.', 'masjidos' ) . '</p>';

		echo '<label class="itmms-article-meta-field"><span>' . esc_html__( 'Article Language', 'masjidos' ) . '</span>';
		echo '<select name="' . esc_attr( self::META_LANGUAGE ) . '">';
		foreach (
			[
				'en' => __( 'English', 'masjidos' ),
				'bn' => __( 'Bangla', 'masjidos' ),
				'ar' => __( 'Arabic', 'masjidos' ),
			] as $code => $label
		) {
			printf(
				'<option value="%1$s" %2$s>%3$s</option>',
				esc_attr( $code ),
				selected( $language, $code, false ),
				esc_html( $label )
			);
		}
		echo '</select></label>';

		echo '<label class="itmms-article-meta-field"><span>' . esc_html__( 'Author / Scholar', 'masjidos' ) . '</span>';
		echo '<input type="text" name="' . esc_attr( self::META_AUTHOR ) . '" value="' . esc_attr( $author ) . '" placeholder="' . esc_attr__( 'e.g. Imam / Scholar name', 'masjidos' ) . '"></label>';

		echo '<label class="itmms-article-meta-field"><span>' . esc_html__( 'Translator', 'masjidos' ) . '</span>';
		echo '<input type="text" name="' . esc_attr( self::META_TRANSLATOR ) . '" value="' . esc_attr( $translator ) . '" placeholder="' . esc_attr__( 'Optional translator name', 'masjidos' ) . '"></label>';

		echo '<label class="itmms-article-meta-field"><span>' . esc_html__( 'Source / Reference', 'masjidos' ) . '</span>';
		echo '<input type="text" name="' . esc_attr( self::META_SOURCE ) . '" value="' . esc_attr( $source ) . '" placeholder="' . esc_attr__( 'e.g. Book, hadith collection, citation', 'masjidos' ) . '"></label>';

		echo '<label class="itmms-article-meta-field"><span>' . esc_html__( 'Key Takeaway', 'masjidos' ) . '</span>';
		echo '<input type="text" name="' . esc_attr( self::META_TAKEAWAY ) . '" value="' . esc_attr( $takeaway ) . '" placeholder="' . esc_attr__( 'One-line summary for readers', 'masjidos' ) . '"></label>';

		echo '<label class="itmms-article-meta-field"><span>' . esc_html__( 'Original / External URL', 'masjidos' ) . '</span>';
		echo '<input type="url" name="' . esc_attr( self::META_EXTERNAL ) . '" value="' . esc_attr( $external ) . '" placeholder="https://"></label>';

		echo '<label class="itmms-article-meta-field"><span>' . esc_html__( 'Audio URL', 'masjidos' ) . '</span>';
		echo '<input type="url" name="' . esc_attr( self::META_AUDIO ) . '" value="' . esc_attr( $audio ) . '" placeholder="' . esc_attr__( 'Optional audio narration URL', 'masjidos' ) . '"></label>';
		echo '</div>';
	}

	/**
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post Post object.
	 */
	public static function save_meta_box( int $post_id, WP_Post $post ): void {
		if ( ! isset( $_POST['itmms_article_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['itmms_article_nonce'] ) ), 'itmms_save_article_meta' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$map = [
			self::META_LANGUAGE   => [ __CLASS__, 'sanitize_language' ],
			self::META_AUTHOR     => 'sanitize_text_field',
			self::META_TRANSLATOR => 'sanitize_text_field',
			self::META_SOURCE     => 'sanitize_text_field',
			self::META_TAKEAWAY   => 'sanitize_text_field',
			self::META_EXTERNAL   => 'esc_url_raw',
			self::META_AUDIO      => 'esc_url_raw',
		];

		foreach ( $map as $key => $callback ) {
			if ( ! isset( $_POST[ $key ] ) ) {
				continue;
			}
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized immediately via $callback.
			$raw = wp_unslash( $_POST[ $key ] );
			update_post_meta( $post_id, $key, call_user_func( $callback, is_scalar( $raw ) ? (string) $raw : '' ) );
		}
	}

	/**
	 * Block editor sidebar panel + MasjidOS content styles.
	 */
	public static function enqueue_article_editor_assets(): void {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || self::POST_TYPE !== $screen->post_type ) {
			return;
		}

		wp_enqueue_style(
			'itmms-fonts',
			ITMMS_PLUGIN_URL . 'public/assets/css/tv-fonts.css',
			[],
			ITMMS_VERSION
		);

		wp_enqueue_style(
			'itmms-article-editor',
			ITMMS_PLUGIN_URL . 'admin/assets/css/article-editor.css',
			[ 'itmms-fonts' ],
			ITMMS_VERSION
		);

		wp_enqueue_script(
			'itmms-article-editor',
			ITMMS_PLUGIN_URL . 'admin/assets/js/article-editor.js',
			[ 'wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-i18n' ],
			ITMMS_VERSION,
			true
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'itmms-article-editor', 'masjidos', ITMMS_PLUGIN_DIR . 'languages' );
		}
	}

	/**
	 * Meta-box styles on article admin screens.
	 */
	public static function enqueue_article_admin_assets( string $hook ): void {
		if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || self::POST_TYPE !== $screen->post_type ) {
			return;
		}

		wp_enqueue_style(
			'itmms-article-editor',
			ITMMS_PLUGIN_URL . 'admin/assets/css/article-editor.css',
			[],
			ITMMS_VERSION
		);
	}

	/**
	 * @param array<string,string> $columns List table columns.
	 * @return array<string,string>
	 */
	public static function posts_columns( array $columns ): array {
		$next = [];
		foreach ( $columns as $key => $label ) {
			$next[ $key ] = $label;
			if ( 'title' === $key ) {
				$next['itmms_article_language'] = __( 'Language', 'masjidos' );
				$next['itmms_article_author']   = __( 'Author', 'masjidos' );
			}
		}
		return $next;
	}

	public static function render_post_column( string $column, int $post_id ): void {
		if ( 'itmms_article_language' === $column ) {
			$lang = self::sanitize_language( get_post_meta( $post_id, self::META_LANGUAGE, true ) );
			$labels = [
				'en' => __( 'English', 'masjidos' ),
				'bn' => __( 'Bangla', 'masjidos' ),
				'ar' => __( 'Arabic', 'masjidos' ),
			];
			echo esc_html( $labels[ $lang ] ?? $lang );
			return;
		}
		if ( 'itmms_article_author' === $column ) {
			$author = (string) get_post_meta( $post_id, self::META_AUTHOR, true );
			echo $author !== '' ? esc_html( $author ) : '&mdash;';
		}
	}

	/**
	 * @param string[] $classes Body classes.
	 * @return string[]
	 */
	public static function body_class( array $classes ): array {
		if ( is_singular( self::POST_TYPE ) ) {
			$classes[] = 'itmms-article-singular';
			$lang = self::sanitize_language( get_post_meta( get_queried_object_id(), self::META_LANGUAGE, true ) );
			$classes[] = 'itmms-article-lang-' . $lang;
		}
		return $classes;
	}

	/**
	 * Wrap single article content for theme styling.
	 */
	public static function wrap_singular_content( string $content ): string {
		if ( ! is_singular( self::POST_TYPE ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		$post_id = (int) get_the_ID();
		$meta    = self::get_article_meta( $post_id );
		$minutes = self::estimate_reading_minutes( $content );
		$terms   = get_the_terms( $post_id, self::TAXONOMY );
		$cats    = [];
		if ( is_array( $terms ) ) {
			foreach ( $terms as $term ) {
				if ( $term instanceof WP_Term ) {
					$cats[] = $term->name;
				}
			}
		}

		$lang_labels = [
			'en' => __( 'English', 'masjidos' ),
			'bn' => __( 'Bangla', 'masjidos' ),
			'ar' => __( 'Arabic', 'masjidos' ),
		];

		$rows = [];
		$rows[] = '<p class="itmms-article-meta__lang"><span>' . esc_html__( 'Language', 'masjidos' ) . '</span> ' . esc_html( $lang_labels[ $meta['language'] ] ?? $meta['language'] ) . '</p>';
		if ( ! empty( $cats ) ) {
			$rows[] = '<p class="itmms-article-meta__cats"><span>' . esc_html__( 'Category', 'masjidos' ) . '</span> ' . esc_html( implode( ', ', $cats ) ) . '</p>';
		}
		if ( $meta['author'] !== '' ) {
			$rows[] = '<p class="itmms-article-meta__author"><span>' . esc_html__( 'Author', 'masjidos' ) . '</span> ' . esc_html( $meta['author'] ) . '</p>';
		}
		if ( $meta['translator'] !== '' ) {
			$rows[] = '<p class="itmms-article-meta__translator"><span>' . esc_html__( 'Translator', 'masjidos' ) . '</span> ' . esc_html( $meta['translator'] ) . '</p>';
		}
		if ( $meta['source'] !== '' ) {
			$rows[] = '<p class="itmms-article-meta__source"><span>' . esc_html__( 'Source', 'masjidos' ) . '</span> ' . esc_html( $meta['source'] ) . '</p>';
		}
		if ( $minutes > 0 ) {
			/* translators: %d: estimated reading minutes */
			$rows[] = '<p class="itmms-article-meta__read"><span>' . esc_html__( 'Reading time', 'masjidos' ) . '</span> ' . esc_html( sprintf( _n( '%d min', '%d min', $minutes, 'masjidos' ), $minutes ) ) . '</p>';
		}
		if ( $meta['external'] !== '' ) {
			$rows[] = '<p class="itmms-article-meta__external"><span>' . esc_html__( 'Original', 'masjidos' ) . '</span> <a href="' . esc_url( $meta['external'] ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Open source link', 'masjidos' ) . '</a></p>';
		}

		$meta_html = '<aside class="itmms-article-meta">' . implode( '', $rows ) . '</aside>';

		$takeaway_html = '';
		if ( $meta['takeaway'] !== '' ) {
			$takeaway_html = '<p class="itmms-article-takeaway"><strong>' . esc_html__( 'Key takeaway', 'masjidos' ) . '</strong> ' . esc_html( $meta['takeaway'] ) . '</p>';
		}

		$audio_html = '';
		if ( $meta['audio'] !== '' ) {
			$audio_html = '<div class="itmms-article-audio"><span>' . esc_html__( 'Listen', 'masjidos' ) . '</span><audio controls preload="none" src="' . esc_url( $meta['audio'] ) . '"></audio></div>';
		}

		return '<div class="itmms-article-entry itmms-article-entry--lang-' . esc_attr( $meta['language'] ) . '">' .
			$meta_html .
			$takeaway_html .
			$audio_html .
			'<div class="itmms-article-entry__content">' . $content . '</div></div>';
	}

	/**
	 * @return array{language:string,author:string,translator:string,source:string,external:string,audio:string,takeaway:string}
	 */
	public static function get_article_meta( int $post_id ): array {
		return [
			'language'   => self::sanitize_language( get_post_meta( $post_id, self::META_LANGUAGE, true ) ),
			'author'     => (string) get_post_meta( $post_id, self::META_AUTHOR, true ),
			'translator' => (string) get_post_meta( $post_id, self::META_TRANSLATOR, true ),
			'source'     => (string) get_post_meta( $post_id, self::META_SOURCE, true ),
			'external'   => (string) get_post_meta( $post_id, self::META_EXTERNAL, true ),
			'audio'      => (string) get_post_meta( $post_id, self::META_AUDIO, true ),
			'takeaway'   => (string) get_post_meta( $post_id, self::META_TAKEAWAY, true ),
		];
	}

	/**
	 * Rough reading-time estimate (~200 wpm).
	 */
	public static function estimate_reading_minutes( string $html ): int {
		$text = trim( wp_strip_all_tags( $html ) );
		if ( '' === $text ) {
			return 0;
		}
		$words = preg_split( '/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY );
		$count = is_array( $words ) ? count( $words ) : 0;
		return max( 1, (int) ceil( $count / 200 ) );
	}

	public static function enqueue_singular_assets(): void {
		if ( ! is_singular( self::POST_TYPE ) ) {
			return;
		}

		wp_enqueue_style(
			'itmms-fonts',
			ITMMS_PLUGIN_URL . 'public/assets/css/tv-fonts.css',
			[],
			ITMMS_VERSION
		);
		wp_enqueue_style(
			'itmms-public',
			ITMMS_PLUGIN_URL . 'public/assets/css/public.css',
			[ 'itmms-fonts' ],
			ITMMS_VERSION
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
			if ( ! term_exists( $name, self::TAXONOMY ) ) {
				wp_insert_term( $name, self::TAXONOMY, [ 'description' => $description ] );
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
			[
				'ar'  => 'وَمَن يَتَّقِ اللَّهَ يَجْعَل لَّهُ مَخْرَجًا',
				'en'  => 'And whoever fears Allah - He will make for him a way out.',
				'bn'  => 'যে আল্লাহকে ভয় করে, তিনি তার জন্য নিষ্কৃতির পথ করে দেন।',
				'ref' => 'Surah At-Talaq 65:2',
			],
			[
				'ar'  => 'فَاذْكُرُونِي أَذْكُرْكُمْ',
				'en'  => 'So remember Me; I will remember you.',
				'bn'  => 'অতএব তোমরা আমাকে স্মরণ করো, আমিও তোমাদের স্মরণ করব।',
				'ref' => 'Surah Al-Baqarah 2:152',
			],
			[
				'ar'  => 'وَبَشِّرِ الصَّابِرِينَ',
				'en'  => 'And give good tidings to the patient.',
				'bn'  => 'এবং ধৈর্যশীলদের সুসংবাদ দাও।',
				'ref' => 'Surah Al-Baqarah 2:155',
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
			[
				'ar'  => 'خَيْرُكُمْ مَنْ تَعَلَّمَ الْقُرْآنَ وَعَلَّمَهُ',
				'en'  => 'The best among you are those who learn the Quran and teach it.',
				'bn'  => 'তোমাদের মধ্যে সর্বোত্তম সে, যে কুরআন শেখে এবং অন্যকে শেখায়।',
				'ref' => 'Sahih Al-Bukhari 5027',
			],
			[
				'ar'  => 'الطُّهُورُ شَطْرُ الإِيمَانِ',
				'en'  => 'Cleanliness is half of faith.',
				'bn'  => 'পবিত্রতা ঈমানের অর্ধেক।',
				'ref' => 'Sahih Muslim 223',
			],
			[
				'ar'  => 'مَنْ سَلَكَ طَرِيقًا يَلْتَمِسُ فِيهِ عِلْمًا سَهَّلَ اللَّهُ لَهُ بِهِ طَرِيقًا إِلَى الْجَنَّةِ',
				'en'  => 'Whoever travels a path in search of knowledge, Allah makes easy for him a path to Paradise.',
				'bn'  => 'যে জ্ঞান অর্জনের পথে চলে, আল্লাহ তার জন্য জান্নাতের পথ সহজ করে দেন।',
				'ref' => 'Sahih Muslim 2699',
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
	 * Query published Islamic articles for public widgets.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public static function get_articles( int $limit = 6, string $category = '' ): array {
		$args = [
			'post_type'              => self::POST_TYPE,
			'post_status'            => 'publish',
			'posts_per_page'         => max( 1, min( 24, $limit ) ),
			'orderby'                => 'date',
			'order'                  => 'DESC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => true,
		];

		if ( '' !== $category ) {
			$args['tax_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				[
					'taxonomy' => self::TAXONOMY,
					'field'    => 'slug',
					'terms'    => $category,
				],
			];
		}

		$query = new WP_Query( $args );
		$items = [];

		foreach ( $query->posts as $post ) {
			if ( ! $post instanceof WP_Post ) {
				continue;
			}

			$terms = get_the_terms( $post, self::TAXONOMY );
			$cats = [];
			if ( is_array( $terms ) ) {
				foreach ( $terms as $term ) {
					if ( $term instanceof WP_Term ) {
						$cats[] = $term->name;
					}
				}
			}

			$thumb = get_the_post_thumbnail_url( $post, 'medium' );
			$meta  = self::get_article_meta( (int) $post->ID );
			$items[] = [
				'id'         => (int) $post->ID,
				'title'      => get_the_title( $post ),
				'url'        => get_permalink( $post ),
				'excerpt'    => wp_trim_words( get_the_excerpt( $post ), 28 ),
				'image'      => is_string( $thumb ) ? $thumb : '',
				'categories' => $cats,
				'date'       => get_the_date( 'Y-m-d', $post ),
				'language'   => $meta['language'],
				'author'     => $meta['author'],
				'translator' => $meta['translator'],
				'source'     => $meta['source'],
				'takeaway'   => $meta['takeaway'],
				'external'   => $meta['external'],
				'audio'      => $meta['audio'],
			];
		}

		return $items;
	}

	/**
	 * List of commonly used Surahs for the Audio Quran.
	 *
	 * @return array<int,array<string,string>>
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
