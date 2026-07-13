<?php
/**
 * Custom Duas & Azkar library powered by a WordPress custom post type.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers and reads custom Duas & Azkar entries.
 */
final class ITMMS_Duas_Library {

	public const POST_TYPE = 'itmms_dua';
	public const TAXONOMY = 'itmms_dua_category';

	/** @var ITMMS_Duas_Library|null */
	private static ?ITMMS_Duas_Library $instance = null;

	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {}
	private function __clone() {}

	public function init(): void {
		add_action( 'init', [ $this, 'register_post_type' ] );
		add_action( 'init', [ $this, 'register_taxonomy' ] );
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
		add_action( 'save_post_' . self::POST_TYPE, [ $this, 'save_meta' ], 10, 2 );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', [ $this, 'posts_columns' ] );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', [ $this, 'render_post_column' ], 10, 2 );
	}

	public function register_post_type(): void {
		register_post_type(
			self::POST_TYPE,
			[
				'labels'       => [
					'name'          => __( 'Duas Library', 'masjidos' ),
					'singular_name' => __( 'Dua', 'masjidos' ),
					'add_new_item'  => __( 'Add New Dua', 'masjidos' ),
					'edit_item'     => __( 'Edit Dua', 'masjidos' ),
					'new_item'      => __( 'New Dua', 'masjidos' ),
					'view_item'     => __( 'View Dua', 'masjidos' ),
					'search_items'  => __( 'Search Duas', 'masjidos' ),
					'menu_name'     => __( 'Duas Library', 'masjidos' ),
				],
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => false,
				'show_in_rest' => false,
				'supports'     => [ 'title', 'page-attributes' ],
				'menu_icon'    => 'dashicons-book-alt',
				'capability_type' => 'post',
			]
		);
	}

	public function register_taxonomy(): void {
		register_taxonomy(
			self::TAXONOMY,
			self::POST_TYPE,
			[
				'labels'            => [
					'name'          => __( 'Dua Categories', 'masjidos' ),
					'singular_name' => __( 'Dua Category', 'masjidos' ),
					'search_items'  => __( 'Search Dua Categories', 'masjidos' ),
					'all_items'     => __( 'All Dua Categories', 'masjidos' ),
					'edit_item'     => __( 'Edit Dua Category', 'masjidos' ),
					'update_item'   => __( 'Update Dua Category', 'masjidos' ),
					'add_new_item'  => __( 'Add New Dua Category', 'masjidos' ),
					'new_item_name' => __( 'New Dua Category Name', 'masjidos' ),
					'menu_name'     => __( 'Dua Categories', 'masjidos' ),
				],
				'public'            => false,
				'show_ui'           => true,
				'show_admin_column' => false,
				'show_in_rest'      => false,
				'hierarchical'      => false,
				'rewrite'           => false,
			]
		);
	}

	public function add_meta_boxes(): void {
		add_meta_box(
			'itmms-dua-details',
			__( 'Dua Details', 'masjidos' ),
			[ $this, 'render_meta_box' ],
			self::POST_TYPE,
			'normal',
			'high'
		);
	}

	public function render_meta_box( WP_Post $post ): void {
		wp_nonce_field( 'itmms_save_dua_meta', 'itmms_dua_nonce' );

		$fields = $this->meta_fields();
		echo '<div class="itmms-dua-meta-box" style="display:grid;gap:14px;max-width:960px;">';
		echo '<p style="margin:0;color:#667085;">' . esc_html__( 'Use the Dua Categories panel to assign category keys such as morning, daily, food, sleep, or travel.', 'masjidos' ) . '</p>';
		foreach ( $fields as $key => $field ) {
			$value = (string) get_post_meta( $post->ID, $key, true );
			echo '<label style="display:grid;gap:6px;">';
			echo '<strong>' . esc_html( $field['label'] ) . '</strong>';
			if ( 'textarea' === $field['type'] ) {
				echo '<textarea name="' . esc_attr( $key ) . '" rows="' . esc_attr( (string) $field['rows'] ) . '" style="width:100%;">' . esc_textarea( $value ) . '</textarea>';
			} elseif ( '_itmms_dua_audio_url' === $key ) {
				echo '<span style="display:flex;gap:8px;align-items:center;">';
				echo '<input type="url" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" style="width:100%;" data-itmms-dua-audio-url>';
				echo '<button type="button" class="button" data-itmms-dua-audio-picker>' . esc_html__( 'Select Audio', 'masjidos' ) . '</button>';
				echo '</span>';
			} else {
				echo '<input type="' . esc_attr( $field['type'] ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" style="width:100%;">';
			}
			if ( ! empty( $field['help'] ) ) {
				echo '<span style="color:#667085;">' . esc_html( $field['help'] ) . '</span>';
			}
			echo '</label>';
		}
		echo '</div>';
	}

	public function enqueue_admin_assets( string $hook ): void {
		if ( ! $this->is_dua_admin_screen() ) {
			return;
		}

		wp_enqueue_media();
		wp_add_inline_script(
			'media-editor',
			"document.addEventListener('click',function(event){var button=event.target.closest('[data-itmms-dua-audio-picker]');if(!button||!window.wp||!wp.media){return;}event.preventDefault();var input=document.querySelector('[data-itmms-dua-audio-url]');var frame=wp.media({title:'" . esc_js( __( 'Select Audio', 'masjidos' ) ) . "',button:{text:'" . esc_js( __( 'Use this audio', 'masjidos' ) ) . "'},library:{type:'audio'},multiple:false});frame.on('select',function(){var file=frame.state().get('selection').first();if(file&&input){input.value=file.toJSON().url||'';}});frame.open();});"
		);
	}

	/**
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post Post object.
	 */
	public function save_meta( int $post_id, WP_Post $post ): void {
		if ( ! isset( $_POST['itmms_dua_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['itmms_dua_nonce'] ) ), 'itmms_save_dua_meta' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		foreach ( $this->meta_fields() as $key => $field ) {
			$raw = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
			if ( 'url' === $field['type'] ) {
				$value = esc_url_raw( $raw );
			} elseif ( '_itmms_dua_repeat' === $key ) {
				$value = (string) max( 1, min( 1000, absint( $raw ) ?: 1 ) );
			} else {
				$value = 'textarea' === $field['type'] ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
			}
			update_post_meta( $post_id, $key, $value );
		}
	}

	/**
	 * Return custom published duas in the same shape as built-in entries.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public static function items(): array {
		$query = new WP_Query(
			[
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => 100,
				'orderby'        => 'menu_order title',
				'order'          => 'ASC',
				'no_found_rows'  => true,
			]
		);

		$items = [];
		foreach ( $query->posts as $post ) {
			$categories = self::post_categories( $post->ID );
			if ( empty( $categories ) ) {
				$categories = self::split_categories( (string) get_post_meta( $post->ID, '_itmms_dua_categories', true ) );
			}
			$items[] = [
				'key'        => 'custom-' . $post->ID,
				'categories' => $categories ?: [ 'daily' ],
				'title'      => [
					'en' => get_the_title( $post ),
					'bn' => (string) get_post_meta( $post->ID, '_itmms_dua_title_bn', true ) ?: get_the_title( $post ),
					'ar' => (string) get_post_meta( $post->ID, '_itmms_dua_title_ar', true ) ?: get_the_title( $post ),
				],
				'arabic'     => (string) get_post_meta( $post->ID, '_itmms_dua_arabic', true ),
				'latin'      => (string) get_post_meta( $post->ID, '_itmms_dua_latin', true ),
				'meaning'    => [
					'en' => (string) get_post_meta( $post->ID, '_itmms_dua_meaning_en', true ),
					'bn' => (string) get_post_meta( $post->ID, '_itmms_dua_meaning_bn', true ),
					'ar' => (string) get_post_meta( $post->ID, '_itmms_dua_meaning_ar', true ),
				],
				'source'     => (string) get_post_meta( $post->ID, '_itmms_dua_source', true ),
				'repeat'     => max( 1, absint( get_post_meta( $post->ID, '_itmms_dua_repeat', true ) ) ?: 1 ),
				'audio_url'  => (string) get_post_meta( $post->ID, '_itmms_dua_audio_url', true ),
			];
		}

		wp_reset_postdata();
		return $items;
	}

	/**
	 * @return array<string,array<string,mixed>>
	 */
	private function meta_fields(): array {
		return [
			'_itmms_dua_title_bn' => [ 'label' => __( 'Bangla Title', 'masjidos' ), 'type' => 'text', 'help' => __( 'Optional Bangla display title.', 'masjidos' ) ],
			'_itmms_dua_title_ar' => [ 'label' => __( 'Arabic Title', 'masjidos' ), 'type' => 'text', 'help' => __( 'Optional Arabic display title.', 'masjidos' ) ],
			'_itmms_dua_arabic' => [ 'label' => __( 'Arabic Text', 'masjidos' ), 'type' => 'textarea', 'rows' => 4, 'help' => '' ],
			'_itmms_dua_latin' => [ 'label' => __( 'Transliteration', 'masjidos' ), 'type' => 'textarea', 'rows' => 2, 'help' => '' ],
			'_itmms_dua_meaning_en' => [ 'label' => __( 'English Meaning', 'masjidos' ), 'type' => 'textarea', 'rows' => 3, 'help' => '' ],
			'_itmms_dua_meaning_bn' => [ 'label' => __( 'Bangla Meaning', 'masjidos' ), 'type' => 'textarea', 'rows' => 3, 'help' => '' ],
			'_itmms_dua_meaning_ar' => [ 'label' => __( 'Arabic Meaning', 'masjidos' ), 'type' => 'textarea', 'rows' => 3, 'help' => '' ],
			'_itmms_dua_source' => [ 'label' => __( 'Source', 'masjidos' ), 'type' => 'text', 'help' => __( 'Reference such as Quran 2:201 or Bukhari.', 'masjidos' ) ],
			'_itmms_dua_repeat' => [ 'label' => __( 'Repeat Count', 'masjidos' ), 'type' => 'number', 'help' => __( 'Suggested number of times to recite.', 'masjidos' ) ],
			'_itmms_dua_audio_url' => [ 'label' => __( 'Audio URL', 'masjidos' ), 'type' => 'url', 'help' => __( 'Optional pronunciation audio URL.', 'masjidos' ) ],
		];
	}

	/**
	 * @return string[]
	 */
	private static function split_categories( string $value ): array {
		$parts = array_map( 'sanitize_key', array_map( 'trim', explode( ',', $value ) ) );
		return array_values( array_filter( $parts ) );
	}

	/**
	 * @return string[]
	 */
	private static function post_categories( int $post_id ): array {
		$terms = get_the_terms( $post_id, self::TAXONOMY );
		if ( ! is_array( $terms ) ) {
			return [];
		}

		return array_values(
			array_filter(
				array_map(
					static function ( WP_Term $term ): string {
						return sanitize_key( $term->slug );
					},
					$terms
				)
			)
		);
	}

	/**
	 * @param array<string,string> $columns Columns.
	 * @return array<string,string>
	 */
	public function posts_columns( array $columns ): array {
		$date = $columns['date'] ?? '';
		unset( $columns['date'] );

		$columns['itmms_dua_categories'] = __( 'Categories', 'masjidos' );
		$columns['itmms_dua_repeat']     = __( 'Repeat', 'masjidos' );
		$columns['itmms_dua_source']     = __( 'Source', 'masjidos' );
		$columns['itmms_dua_audio']      = __( 'Audio', 'masjidos' );
		if ( $date ) {
			$columns['date'] = $date;
		}

		return $columns;
	}

	public function render_post_column( string $column, int $post_id ): void {
		if ( 'itmms_dua_categories' === $column ) {
			$terms = get_the_term_list( $post_id, self::TAXONOMY, '', ', ' );
			echo $terms ? wp_kses_post( $terms ) : esc_html__( 'Uncategorized', 'masjidos' );
			return;
		}

		if ( 'itmms_dua_repeat' === $column ) {
			echo esc_html( (string) max( 1, absint( get_post_meta( $post_id, '_itmms_dua_repeat', true ) ) ?: 1 ) );
			return;
		}

		if ( 'itmms_dua_source' === $column ) {
			$source = (string) get_post_meta( $post_id, '_itmms_dua_source', true );
			echo $source ? esc_html( $source ) : '&mdash;';
			return;
		}

		if ( 'itmms_dua_audio' === $column ) {
			$audio_url = (string) get_post_meta( $post_id, '_itmms_dua_audio_url', true );
			echo $audio_url ? '<span aria-label="' . esc_attr__( 'Audio available', 'masjidos' ) . '">✓</span>' : '&mdash;';
		}
	}

	private function is_dua_admin_screen(): bool {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		return $screen && self::POST_TYPE === $screen->post_type;
	}
}
