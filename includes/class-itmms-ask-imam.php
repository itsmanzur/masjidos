<?php
/**
 * Ask the Imam question library for the Minbar module.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the Ask the Imam custom post type and admin workflow.
 */
final class ITMMS_Ask_Imam {

	public const POST_TYPE = 'itmms_imam_question';
	public const TAXONOMY  = 'itmms_qa_category';

	/** @var ITMMS_Ask_Imam|null */
	private static ?ITMMS_Ask_Imam $instance = null;

	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {}
	private function __clone() {}

	/**
	 * Wire the library into WordPress.
	 */
	public function init(): void {
		add_action( 'init', [ $this, 'register_taxonomy' ] );
		add_action( 'init', [ $this, 'register_post_type' ] );
		add_action( 'init', [ $this, 'ensure_default_categories' ], 20 );
		add_action( 'init', [ $this, 'handle_public_submission' ], 30 );
		add_action( 'wp_ajax_itmms_ask_imam_submit', [ $this, 'handle_ajax_submission' ] );
		add_action( 'wp_ajax_nopriv_itmms_ask_imam_submit', [ $this, 'handle_ajax_submission' ] );
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
		add_action( 'save_post_' . self::POST_TYPE, [ $this, 'save_meta' ], 10, 2 );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', [ $this, 'posts_columns' ] );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', [ $this, 'render_post_column' ], 10, 2 );
		add_shortcode( 'masjidos_ask_imam', [ $this, 'render_submit_shortcode' ] );
		add_shortcode( 'masjidos_imam_answers', [ $this, 'render_answers_shortcode' ] );
	}

	/**
	 * Register question categories.
	 */
	public function register_taxonomy(): void {
		register_taxonomy(
			self::TAXONOMY,
			self::POST_TYPE,
			[
				'labels'            => [
					'name'          => __( 'Question Categories', 'masjidos' ),
					'singular_name' => __( 'Question Category', 'masjidos' ),
					'search_items'  => __( 'Search Question Categories', 'masjidos' ),
					'all_items'     => __( 'All Question Categories', 'masjidos' ),
					'edit_item'     => __( 'Edit Question Category', 'masjidos' ),
					'update_item'   => __( 'Update Question Category', 'masjidos' ),
					'add_new_item'  => __( 'Add New Question Category', 'masjidos' ),
					'new_item_name' => __( 'New Question Category Name', 'masjidos' ),
					'menu_name'     => __( 'Question Categories', 'masjidos' ),
				],
				'public'            => false,
				'show_ui'           => true,
				'show_admin_column' => false,
				'show_in_rest'      => false,
				'hierarchical'      => true,
				'rewrite'           => false,
			]
		);
	}

	/**
	 * Register the native admin question library.
	 */
	public function register_post_type(): void {
		register_post_type(
			self::POST_TYPE,
			[
				'labels'          => [
					'name'               => __( 'Ask the Imam', 'masjidos' ),
					'singular_name'      => __( 'Imam Question', 'masjidos' ),
					'add_new_item'       => __( 'Add New Question', 'masjidos' ),
					'edit_item'          => __( 'Answer Question', 'masjidos' ),
					'new_item'           => __( 'New Question', 'masjidos' ),
					'view_item'          => __( 'View Question', 'masjidos' ),
					'search_items'       => __( 'Search Questions', 'masjidos' ),
					'not_found'          => __( 'No questions found.', 'masjidos' ),
					'not_found_in_trash' => __( 'No questions found in Trash.', 'masjidos' ),
					'menu_name'          => __( 'Ask the Imam', 'masjidos' ),
				],
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => false,
				'show_in_rest'    => false,
				'supports'        => [ 'title', 'editor' ],
				'menu_icon'       => 'dashicons-format-chat',
				'capability_type' => 'post',
			]
		);
	}

	/**
	 * Seed the core categories once.
	 */
	public function ensure_default_categories(): void {
		if ( get_option( 'itmms_ask_imam_categories_seeded', false ) ) {
			return;
		}

		foreach ( $this->default_categories() as $slug => $name ) {
			if ( ! term_exists( $slug, self::TAXONOMY ) ) {
				wp_insert_term( $name, self::TAXONOMY, [ 'slug' => $slug ] );
			}
		}

		update_option( 'itmms_ask_imam_categories_seeded', 1, false );
	}

	/**
	 * Add answer and moderation meta boxes.
	 */
	public function add_meta_boxes(): void {
		add_meta_box(
			'itmms-ask-imam-answer',
			__( 'Answer & Visibility', 'masjidos' ),
			[ $this, 'render_answer_meta_box' ],
			self::POST_TYPE,
			'normal',
			'high'
		);

		add_meta_box(
			'itmms-ask-imam-details',
			__( 'Question Details', 'masjidos' ),
			[ $this, 'render_details_meta_box' ],
			self::POST_TYPE,
			'side',
			'default'
		);
	}

	/**
	 * Render answer editor.
	 *
	 * @param WP_Post $post Current post.
	 */
	public function render_answer_meta_box( WP_Post $post ): void {
		wp_nonce_field( 'itmms_save_ask_imam_meta', 'itmms_ask_imam_nonce' );

		$answer = (string) get_post_meta( $post->ID, '_itmms_qa_answer', true );
		echo '<p style="margin-top:0;color:#667085;">' . esc_html__( 'Answer with care. Public answers will later be shown in the Ask the Imam public archive when the public widget is enabled.', 'masjidos' ) . '</p>';
		wp_editor(
			$answer,
			'itmms_qa_answer_editor',
			[
				'textarea_name' => '_itmms_qa_answer',
				'textarea_rows' => 8,
				'media_buttons' => false,
				'teeny'         => true,
			]
		);
	}

	/**
	 * Render moderation fields.
	 *
	 * @param WP_Post $post Current post.
	 */
	public function render_details_meta_box( WP_Post $post ): void {
		$status      = self::question_status( $post->ID );
		$is_public   = self::is_public( $post->ID );
		$asker_name  = (string) get_post_meta( $post->ID, '_itmms_qa_asker_name', true );
		$asker_email = (string) get_post_meta( $post->ID, '_itmms_qa_asker_email', true );
		$views       = (int) get_post_meta( $post->ID, '_itmms_qa_views', true );
		$helpful     = (int) get_post_meta( $post->ID, '_itmms_qa_helpful', true );

		echo '<div style="display:grid;gap:12px;">';
		echo '<label><strong>' . esc_html__( 'Status', 'masjidos' ) . '</strong><select name="_itmms_qa_status" style="width:100%;margin-top:6px;">';
		foreach ( self::status_labels() as $key => $label ) {
			echo '<option value="' . esc_attr( $key ) . '"' . selected( $status, $key, false ) . '>' . esc_html( $label ) . '</option>';
		}
		echo '</select></label>';

		echo '<label><input type="checkbox" name="_itmms_qa_is_public" value="1"' . checked( $is_public, true, false ) . '> ' . esc_html__( 'Public answer', 'masjidos' ) . '</label>';
		echo '<p style="margin:0;color:#667085;">' . esc_html__( 'Private questions stay hidden from public widgets and search.', 'masjidos' ) . '</p>';

		echo '<label><strong>' . esc_html__( 'Asker Name', 'masjidos' ) . '</strong><input type="text" name="_itmms_qa_asker_name" value="' . esc_attr( $asker_name ) . '" style="width:100%;margin-top:6px;"></label>';
		echo '<label><strong>' . esc_html__( 'Asker Email', 'masjidos' ) . '</strong><input type="email" name="_itmms_qa_asker_email" value="' . esc_attr( $asker_email ) . '" style="width:100%;margin-top:6px;"></label>';

		echo '<label><strong>' . esc_html__( 'Views', 'masjidos' ) . '</strong><input type="number" min="0" name="_itmms_qa_views" value="' . esc_attr( (string) max( 0, $views ) ) . '" style="width:100%;margin-top:6px;"></label>';
		echo '<label><strong>' . esc_html__( 'Helpful Votes', 'masjidos' ) . '</strong><input type="number" min="0" name="_itmms_qa_helpful" value="' . esc_attr( (string) max( 0, $helpful ) ) . '" style="width:100%;margin-top:6px;"></label>';
		echo '</div>';
	}

	/**
	 * Save moderation metadata.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post Post object.
	 */
	public function save_meta( int $post_id, WP_Post $post ): void {
		if ( ! isset( $_POST['itmms_ask_imam_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['itmms_ask_imam_nonce'] ) ), 'itmms_save_ask_imam_meta' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$answer = isset( $_POST['_itmms_qa_answer'] ) ? wp_kses_post( wp_unslash( $_POST['_itmms_qa_answer'] ) ) : '';
		update_post_meta( $post_id, '_itmms_qa_answer', $answer );

		$status = isset( $_POST['_itmms_qa_status'] ) ? sanitize_key( wp_unslash( $_POST['_itmms_qa_status'] ) ) : 'pending';
		if ( ! array_key_exists( $status, self::status_labels() ) ) {
			$status = '' !== trim( wp_strip_all_tags( $answer ) ) ? 'answered' : 'pending';
		}
		update_post_meta( $post_id, '_itmms_qa_status', $status );
		update_post_meta( $post_id, '_itmms_qa_is_public', isset( $_POST['_itmms_qa_is_public'] ) ? '1' : '0' );

		$asker_name = isset( $_POST['_itmms_qa_asker_name'] ) ? sanitize_text_field( wp_unslash( $_POST['_itmms_qa_asker_name'] ) ) : '';
		update_post_meta( $post_id, '_itmms_qa_asker_name', $asker_name );

		$asker_email = isset( $_POST['_itmms_qa_asker_email'] ) ? sanitize_email( wp_unslash( $_POST['_itmms_qa_asker_email'] ) ) : '';
		update_post_meta( $post_id, '_itmms_qa_asker_email', $asker_email );

		$views = isset( $_POST['_itmms_qa_views'] ) ? absint( wp_unslash( $_POST['_itmms_qa_views'] ) ) : 0;
		update_post_meta( $post_id, '_itmms_qa_views', max( 0, $views ) );

		$helpful = isset( $_POST['_itmms_qa_helpful'] ) ? absint( wp_unslash( $_POST['_itmms_qa_helpful'] ) ) : 0;
		update_post_meta( $post_id, '_itmms_qa_helpful', max( 0, $helpful ) );

		if ( 'answered' === $status && ! get_post_meta( $post_id, '_itmms_qa_answered_at', true ) ) {
			update_post_meta( $post_id, '_itmms_qa_answered_at', current_time( 'mysql' ) );
			update_post_meta( $post_id, '_itmms_qa_answered_by', get_current_user_id() );
		}
	}

	/**
	 * @param array<string,string> $columns Columns.
	 * @return array<string,string>
	 */
	public function posts_columns( array $columns ): array {
		$date = $columns['date'] ?? '';
		unset( $columns['date'] );

		$columns['itmms_qa_category']   = __( 'Category', 'masjidos' );
		$columns['itmms_qa_status']     = __( 'Status', 'masjidos' );
		$columns['itmms_qa_visibility'] = __( 'Visibility', 'masjidos' );
		$columns['itmms_qa_popularity'] = __( 'Popularity', 'masjidos' );
		$columns['itmms_qa_answered']   = __( 'Answered', 'masjidos' );
		if ( $date ) {
			$columns['date'] = $date;
		}

		return $columns;
	}

	/**
	 * Render custom post columns.
	 */
	public function render_post_column( string $column, int $post_id ): void {
		if ( 'itmms_qa_category' === $column ) {
			$terms = get_the_term_list( $post_id, self::TAXONOMY, '', ', ' );
			echo $terms ? wp_kses_post( $terms ) : esc_html__( 'Uncategorized', 'masjidos' );
			return;
		}

		if ( 'itmms_qa_status' === $column ) {
			$labels = self::status_labels();
			$status = self::question_status( $post_id );
			echo esc_html( $labels[ $status ] ?? $labels['pending'] );
			return;
		}

		if ( 'itmms_qa_visibility' === $column ) {
			echo self::is_public( $post_id ) ? esc_html__( 'Public', 'masjidos' ) : esc_html__( 'Private', 'masjidos' );
			return;
		}

		if ( 'itmms_qa_popularity' === $column ) {
			$views = (int) get_post_meta( $post_id, '_itmms_qa_views', true );
			$helpful = (int) get_post_meta( $post_id, '_itmms_qa_helpful', true );
			echo esc_html( sprintf( __( '%1$d views, %2$d helpful', 'masjidos' ), max( 0, $views ), max( 0, $helpful ) ) );
			return;
		}

		if ( 'itmms_qa_answered' === $column ) {
			$answered_at = (string) get_post_meta( $post_id, '_itmms_qa_answered_at', true );
			echo $answered_at ? esc_html( mysql2date( get_option( 'date_format' ), $answered_at ) ) : '&mdash;';
		}
	}

	/**
	 * Process public question submissions.
	 */
	public function handle_public_submission(): void {
		if ( empty( $_POST['itmms_ask_imam_action'] ) || 'submit_question' !== sanitize_key( wp_unslash( $_POST['itmms_ask_imam_action'] ) ) ) {
			return;
		}

		$redirect = $this->submission_redirect_url();
		$redirect = remove_query_arg( [ 'itmms_qa_submitted', 'itmms_qa_error' ], $redirect );
		$result   = $this->process_public_submission();

		if ( is_wp_error( $result ) ) {
			wp_safe_redirect( $this->add_submission_result_arg( $redirect, 'itmms_qa_error', $result->get_error_code() ) );
			exit;
		}

		wp_safe_redirect( $this->add_submission_result_arg( $redirect, 'itmms_qa_submitted', '1' ) );
		exit;
	}

	/**
	 * Process no-reload public question submissions.
	 */
	public function handle_ajax_submission(): void {
		$language = isset( $_POST['itmms_qa_language'] ) ? $this->normalize_language( sanitize_key( wp_unslash( $_POST['itmms_qa_language'] ) ) ) : ITMMS_Settings::ui_language();
		$labels   = $this->public_labels( $language );
		$result   = $this->process_public_submission();

		if ( is_wp_error( $result ) ) {
			wp_send_json_error(
				[
					'code'         => $result->get_error_code(),
					'message_html' => $this->build_error_message( $labels, $result->get_error_code() ),
				],
				400
			);
		}

		wp_send_json_success(
			[
				'message_html' => $this->build_success_message( $labels, $this->submission_redirect_url() ),
			]
		);
	}

	/**
	 * Validate and create a public question.
	 *
	 * @return true|WP_Error True on success.
	 */
	private function process_public_submission() {
		if ( ! isset( $_POST['itmms_ask_imam_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['itmms_ask_imam_nonce'] ) ), 'itmms_public_ask_imam' ) ) {
			return new WP_Error( 'security' );
		}

		$honeypot = isset( $_POST['itmms_qa_website'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['itmms_qa_website'] ) ) ) : '';
		if ( '' !== $honeypot ) {
			return true;
		}

		if ( ! $this->can_submit_from_client() ) {
			return new WP_Error( 'rate' );
		}

		$question = isset( $_POST['itmms_qa_question'] ) ? sanitize_textarea_field( wp_unslash( $_POST['itmms_qa_question'] ) ) : '';
		$title    = isset( $_POST['itmms_qa_title'] ) ? sanitize_text_field( wp_unslash( $_POST['itmms_qa_title'] ) ) : '';
		$name     = isset( $_POST['itmms_qa_name'] ) ? sanitize_text_field( wp_unslash( $_POST['itmms_qa_name'] ) ) : '';
		$email    = isset( $_POST['itmms_qa_email'] ) ? sanitize_email( wp_unslash( $_POST['itmms_qa_email'] ) ) : '';
		$category = isset( $_POST['itmms_qa_category'] ) ? sanitize_key( wp_unslash( $_POST['itmms_qa_category'] ) ) : '';
		$is_public = empty( $_POST['itmms_qa_private'] );

		if ( '' === $question || strlen( $question ) < 12 ) {
			return new WP_Error( 'question' );
		}

		if ( '' === $title ) {
			$title = wp_trim_words( $question, 12, '' );
		}

		$post_id = wp_insert_post(
			[
				'post_type'    => self::POST_TYPE,
				'post_status'  => 'pending',
				'post_title'   => $title,
				'post_content' => $question,
			],
			true
		);

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			return new WP_Error( 'save' );
		}

		update_post_meta( (int) $post_id, '_itmms_qa_status', 'pending' );
		update_post_meta( (int) $post_id, '_itmms_qa_is_public', $is_public ? '1' : '0' );
		update_post_meta( (int) $post_id, '_itmms_qa_asker_name', $name );
		update_post_meta( (int) $post_id, '_itmms_qa_asker_email', $email );
		update_post_meta( (int) $post_id, '_itmms_qa_submitted_at', current_time( 'mysql' ) );
		update_post_meta( (int) $post_id, '_itmms_qa_views', 0 );
		update_post_meta( (int) $post_id, '_itmms_qa_helpful', 0 );

		if ( '' !== $category && term_exists( $category, self::TAXONOMY ) ) {
			wp_set_object_terms( (int) $post_id, [ $category ], self::TAXONOMY, false );
		}

		return true;
	}

	/**
	 * Render the public question form.
	 *
	 * @param array<string,mixed>|string $atts Shortcode attributes.
	 */
	public function render_submit_shortcode( $atts = [] ): string {
		$atts = shortcode_atts(
			[
				'title'    => __( 'Ask the Imam', 'masjidos' ),
				'language' => ITMMS_Settings::ui_language(),
			],
			is_array( $atts ) ? $atts : [],
			'masjidos_ask_imam'
		);

		$this->enqueue_public_assets();
		$language = $this->normalize_language( (string) $atts['language'] );
		$labels   = $this->public_labels( $language );
		$terms    = get_terms( [ 'taxonomy' => self::TAXONOMY, 'hide_empty' => false ] );
		$message  = $this->public_message( $labels );
		$form_url = $this->current_page_url();
		$submitted = $this->has_successful_submission();

		ob_start();
		?>
		<section class="itmms-public-qa itmms-public-qa--form itmms-public-qa--lang-<?php echo esc_attr( $language ); ?>" id="masjidos-ask-imam">
			<header class="itmms-public-qa__header">
				<span class="itmms-public-qa__eyebrow"><?php echo esc_html( $labels['eyebrow'] ); ?></span>
				<h2><?php echo esc_html( (string) $atts['title'] ); ?></h2>
				<p><?php echo esc_html( $labels['form_intro'] ); ?></p>
			</header>
			<?php echo $this->sanitize_public_html( $message ); ?>
			<?php if ( ! $submitted ) : ?>
			<form class="itmms-public-qa__form" method="post" action="">
				<?php wp_nonce_field( 'itmms_public_ask_imam', 'itmms_ask_imam_nonce' ); ?>
				<input type="hidden" name="itmms_ask_imam_action" value="submit_question">
				<input type="hidden" name="itmms_qa_redirect" value="<?php echo esc_url( $form_url ); ?>">
				<input type="hidden" name="itmms_qa_language" value="<?php echo esc_attr( $language ); ?>">
				<label class="itmms-public-qa__field itmms-public-qa__trap">
					<span><?php echo esc_html__( 'Website', 'masjidos' ); ?></span>
					<input type="text" name="itmms_qa_website" value="" tabindex="-1" autocomplete="off">
				</label>
				<label class="itmms-public-qa__field">
					<span><?php echo esc_html( $labels['question_title'] ); ?></span>
					<input type="text" name="itmms_qa_title" maxlength="140" placeholder="<?php echo esc_attr( $labels['title_placeholder'] ); ?>">
				</label>
				<label class="itmms-public-qa__field">
					<span><?php echo esc_html( $labels['question'] ); ?></span>
					<textarea name="itmms_qa_question" rows="6" required minlength="12" placeholder="<?php echo esc_attr( $labels['question_placeholder'] ); ?>"></textarea>
				</label>
				<div class="itmms-public-qa__grid">
					<label class="itmms-public-qa__field">
						<span><?php echo esc_html( $labels['category'] ); ?></span>
						<select name="itmms_qa_category">
							<option value=""><?php echo esc_html( $labels['choose_category'] ); ?></option>
							<?php if ( is_array( $terms ) ) : ?>
								<?php foreach ( $terms as $term ) : ?>
									<option value="<?php echo esc_attr( $term->slug ); ?>"><?php echo esc_html( $term->name ); ?></option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
					</label>
					<label class="itmms-public-qa__field">
						<span><?php echo esc_html( $labels['name'] ); ?></span>
						<input type="text" name="itmms_qa_name" maxlength="120" placeholder="<?php echo esc_attr( $labels['optional'] ); ?>">
					</label>
					<label class="itmms-public-qa__field">
						<span><?php echo esc_html( $labels['email'] ); ?></span>
						<input type="email" name="itmms_qa_email" maxlength="190" placeholder="<?php echo esc_attr( $labels['optional_private'] ); ?>">
					</label>
				</div>
				<label class="itmms-public-qa__check">
					<input type="checkbox" name="itmms_qa_private" value="1">
					<span><?php echo esc_html( $labels['keep_private'] ); ?></span>
				</label>
				<p class="itmms-public-qa__note"><?php echo esc_html( $labels['review_note'] ); ?></p>
				<button type="submit" class="itmms-public-qa__submit" data-itmms-loading-label="<?php echo esc_attr( $labels['submitting'] ); ?>"><?php echo esc_html( $labels['submit'] ); ?></button>
			</form>
			<?php endif; ?>
		</section>
		<?php
		return $this->sanitize_public_html( (string) ob_get_clean() );
	}

	/**
	 * Render public answered questions with search/filter.
	 *
	 * @param array<string,mixed>|string $atts Shortcode attributes.
	 */
	public function render_answers_shortcode( $atts = [] ): string {
		$atts = shortcode_atts(
			[
				'title'    => __( 'Answered Questions', 'masjidos' ),
				'language' => ITMMS_Settings::ui_language(),
				'limit'    => '10',
				'category' => '',
			],
			is_array( $atts ) ? $atts : [],
			'masjidos_imam_answers'
		);

		$this->enqueue_public_assets();
		$language = $this->normalize_language( (string) $atts['language'] );
		$labels   = $this->public_labels( $language );
		$limit    = max( 1, min( 50, absint( $atts['limit'] ) ?: 10 ) );
		$search   = isset( $_GET['itmms_qa_search'] ) ? sanitize_text_field( wp_unslash( $_GET['itmms_qa_search'] ) ) : '';
		$category = isset( $_GET['itmms_qa_category'] ) ? sanitize_key( wp_unslash( $_GET['itmms_qa_category'] ) ) : sanitize_key( (string) $atts['category'] );
		$terms    = get_terms( [ 'taxonomy' => self::TAXONOMY, 'hide_empty' => false ] );
		$query    = $this->answered_public_query( $limit, $search, $category );

		ob_start();
		?>
		<section class="itmms-public-qa itmms-public-qa--answers itmms-public-qa--lang-<?php echo esc_attr( $language ); ?>">
			<header class="itmms-public-qa__header">
				<span class="itmms-public-qa__eyebrow"><?php echo esc_html( $labels['answers_eyebrow'] ); ?></span>
				<h2><?php echo esc_html( (string) $atts['title'] ); ?></h2>
				<p><?php echo esc_html( $labels['search_intro'] ); ?></p>
			</header>
			<form class="itmms-public-qa__search" method="get" action="">
				<input type="search" name="itmms_qa_search" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php echo esc_attr( $labels['search_placeholder'] ); ?>">
				<select name="itmms_qa_category">
					<option value=""><?php echo esc_html( $labels['all_categories'] ); ?></option>
					<?php if ( is_array( $terms ) ) : ?>
						<?php foreach ( $terms as $term ) : ?>
							<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $category, $term->slug ); ?>><?php echo esc_html( $term->name ); ?></option>
						<?php endforeach; ?>
					<?php endif; ?>
				</select>
				<button type="submit"><?php echo esc_html( $labels['search'] ); ?></button>
			</form>
			<div class="itmms-public-qa__list">
				<?php if ( ! $query->have_posts() ) : ?>
					<div class="itmms-public-qa__empty">
						<strong><?php echo esc_html( $labels['no_answers'] ); ?></strong>
						<p><?php echo esc_html( $labels['no_answers_hint'] ); ?></p>
					</div>
				<?php endif; ?>
				<?php while ( $query->have_posts() ) : ?>
					<?php
					$query->the_post();
					$post_id = get_the_ID();
					$answer  = (string) get_post_meta( $post_id, '_itmms_qa_answer', true );
					$views   = max( 0, (int) get_post_meta( $post_id, '_itmms_qa_views', true ) );
					$helpful = max( 0, (int) get_post_meta( $post_id, '_itmms_qa_helpful', true ) );
					$cats    = get_the_terms( $post_id, self::TAXONOMY );
					$views++;
					update_post_meta( $post_id, '_itmms_qa_views', $views );
					?>
					<article class="itmms-public-qa__item">
						<div class="itmms-public-qa__item-head">
							<div>
								<?php if ( is_array( $cats ) && ! empty( $cats ) ) : ?>
									<span class="itmms-public-qa__badge"><?php echo esc_html( $cats[0]->name ); ?></span>
								<?php endif; ?>
								<h3><?php echo esc_html( get_the_title() ); ?></h3>
							</div>
							<span class="itmms-public-qa__popular"><?php echo esc_html( $this->format_count_label( $views, 'view' ) ); ?></span>
						</div>
						<div class="itmms-public-qa__question">
							<strong><?php echo esc_html( $labels['question_label'] ); ?></strong>
							<p><?php echo esc_html( get_the_content() ); ?></p>
						</div>
						<div class="itmms-public-qa__answer">
							<strong><?php echo esc_html( $labels['answer_label'] ); ?></strong>
							<?php echo wp_kses_post( wpautop( $answer ) ); ?>
						</div>
						<div class="itmms-public-qa__meta">
							<span><?php echo esc_html( get_the_date() ); ?></span>
							<span><?php echo esc_html( $this->format_count_label( $helpful, 'helpful' ) ); ?></span>
						</div>
					</article>
				<?php endwhile; ?>
			</div>
		</section>
		<?php
		wp_reset_postdata();
		return $this->sanitize_public_html( (string) ob_get_clean() );
	}

	/**
	 * Sanitize public Q&A widget HTML while preserving forms.
	 */
	private function sanitize_public_html( string $html ): string {
		$allowed = wp_kses_allowed_html( 'post' );

		$allowed['form'] = [
			'action' => true,
			'class'  => true,
			'method' => true,
			'role'   => true,
		];
		$allowed['input'] = [
			'autocomplete' => true,
			'class'        => true,
			'id'           => true,
			'maxlength'    => true,
			'minlength'    => true,
			'name'         => true,
			'placeholder'  => true,
			'required'     => true,
			'tabindex'     => true,
			'type'         => true,
			'value'        => true,
		];
		$allowed['textarea'] = [
			'class'       => true,
			'id'          => true,
			'maxlength'   => true,
			'minlength'   => true,
			'name'        => true,
			'placeholder' => true,
			'required'    => true,
			'rows'        => true,
		];
		$allowed['select'] = [
			'class' => true,
			'id'    => true,
			'name'  => true,
		];
		$allowed['option'] = [
			'selected' => true,
			'value'    => true,
		];
		$allowed['button'] = [
			'class'                    => true,
			'data-itmms-loading-label' => true,
			'type'                     => true,
		];
		$allowed['label'] = [
			'class' => true,
			'for'   => true,
		];
		$allowed['a'] = array_merge(
			$allowed['a'] ?? [],
			[
				'class' => true,
				'href'  => true,
			]
		);
		foreach ( [ 'section', 'header', 'div', 'article', 'span', 'strong', 'h2', 'h3', 'p' ] as $tag ) {
			$allowed[ $tag ] = array_merge(
				$allowed[ $tag ] ?? [],
				[
					'aria-hidden' => true,
					'class' => true,
					'dir'   => true,
					'id'    => true,
					'lang'  => true,
				]
			);
		}

		return wp_kses( $html, $allowed );
	}

	/**
	 * Format public count labels with correct singular/plural wording.
	 */
	private function format_count_label( int $count, string $type ): string {
		$count = max( 0, $count );
		if ( 'helpful' === $type ) {
			return sprintf(
				/* translators: %d: helpful vote count */
				_n( '%d helpful', '%d helpful', $count, 'masjidos' ),
				$count
			);
		}

		return sprintf(
			/* translators: %d: view count */
			_n( '%d view', '%d views', $count, 'masjidos' ),
			$count
		);
	}

	/**
	 * Query answered public questions.
	 */
	private function answered_public_query( int $limit, string $search = '', string $category = '' ): WP_Query {
		$args = [
			'post_type'      => self::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'meta_key'       => '_itmms_qa_views',
			'orderby'        => [
				'meta_value_num' => 'DESC',
				'date'           => 'DESC',
			],
			'meta_query'     => [
				'relation' => 'AND',
				[
					'key'   => '_itmms_qa_status',
					'value' => 'answered',
				],
				[
					'key'   => '_itmms_qa_is_public',
					'value' => '1',
				],
			],
		];

		if ( '' !== $search ) {
			$args['s'] = $search;
		}

		if ( '' !== $category && term_exists( $category, self::TAXONOMY ) ) {
			$args['tax_query'] = [
				[
					'taxonomy' => self::TAXONOMY,
					'field'    => 'slug',
					'terms'    => [ $category ],
				],
			];
		}

		return new WP_Query( $args );
	}

	/**
	 * Enqueue shared public widget assets.
	 */
	private function enqueue_public_assets(): void {
		wp_enqueue_style(
			'itmms-public-font',
			'https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap',
			[],
			ITMMS_VERSION
		);
		wp_enqueue_style(
			'itmms-public',
			ITMMS_PLUGIN_URL . 'public/assets/css/public.css',
			[ 'itmms-public-font' ],
			ITMMS_VERSION
		);
		wp_enqueue_script(
			'itmms-public',
			ITMMS_PLUGIN_URL . 'public/assets/js/public.js',
			[],
			ITMMS_VERSION,
			true
		);
		wp_localize_script(
			'itmms-public',
			'itmmsPublicAskImam',
			[
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			]
		);
	}

	/**
	 * Show submit feedback after redirect.
	 *
	 * @param array<string,string> $labels Labels.
	 */
	private function public_message( array $labels ): string {
		$error = isset( $_GET['itmms_qa_error'] ) ? sanitize_key( wp_unslash( $_GET['itmms_qa_error'] ) ) : '';
		if ( '' !== $error ) {
			return $this->build_error_message( $labels, $error );
		}

		if ( $this->has_successful_submission() ) {
			return $this->build_success_message( $labels );
		}

		return '';
	}

	/**
	 * Build public success feedback.
	 *
	 * @param array<string,string> $labels Labels.
	 */
	private function build_success_message( array $labels, string $reset_url = '' ): string {
		$reset_url = $reset_url ? remove_query_arg( [ 'itmms_qa_submitted', 'itmms_qa_error' ], $reset_url ) : $this->current_page_url();

		return sprintf(
			'<div class="itmms-public-qa__message is-success"><span class="itmms-public-qa__message-icon" aria-hidden="true">✓</span><div><strong class="itmms-public-qa__message-title">%1$s</strong><span class="itmms-public-qa__message-text">%2$s</span><span class="itmms-public-qa__message-text is-muted">%3$s</span><a class="itmms-public-qa__message-link" href="%4$s">%5$s</a></div></div>',
			esc_html( $labels['success_title'] ),
			esc_html( $labels['success'] ),
			esc_html( $labels['success_hint'] ),
			esc_url( $reset_url . '#masjidos-ask-imam' ),
			esc_html( $labels['ask_another'] )
		);
	}

	/**
	 * Build public error feedback.
	 *
	 * @param array<string,string> $labels Labels.
	 */
	private function build_error_message( array $labels, string $error ): string {
		$text = $labels['error_general'];
		if ( 'rate' === $error ) {
			$text = $labels['error_rate'];
		} elseif ( 'question' === $error ) {
			$text = $labels['error_question'];
		} elseif ( 'security' === $error ) {
			$text = $labels['error_security'];
		}

		return sprintf(
			'<div class="itmms-public-qa__message is-error"><strong class="itmms-public-qa__message-title">%1$s</strong><span class="itmms-public-qa__message-text">%2$s</span></div>',
			esc_html( $labels['error_title'] ),
			esc_html( $text )
		);
	}

	/**
	 * Whether the current form view is the post-submit confirmation state.
	 */
	private function has_successful_submission(): bool {
		$submitted = isset( $_GET['itmms_qa_submitted'] ) ? sanitize_key( wp_unslash( $_GET['itmms_qa_submitted'] ) ) : '';
		return '1' === $submitted;
	}

	/**
	 * Resolve where a public submission should return.
	 */
	private function submission_redirect_url(): string {
		$posted = isset( $_POST['itmms_qa_redirect'] ) ? esc_url_raw( wp_unslash( $_POST['itmms_qa_redirect'] ) ) : '';
		if ( $posted && $this->is_same_site_url( $posted ) ) {
			return $posted;
		}

		$referer = wp_get_referer();
		if ( $referer && $this->is_same_site_url( $referer ) ) {
			return $referer;
		}

		return home_url( add_query_arg( [], isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/' ) );
	}

	/**
	 * Add result query arg and return to the form area.
	 */
	private function add_submission_result_arg( string $url, string $key, string $value ): string {
		$url = add_query_arg( $key, $value, $url );
		return $url . '#masjidos-ask-imam';
	}

	/**
	 * Current page URL for the hidden return field.
	 */
	private function current_page_url(): string {
		$post_id = get_queried_object_id();
		if ( $post_id ) {
			$link = get_permalink( $post_id );
			if ( is_string( $link ) && '' !== $link ) {
				return remove_query_arg( [ 'itmms_qa_submitted', 'itmms_qa_error' ], $link );
			}
		}

		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
		return remove_query_arg( [ 'itmms_qa_submitted', 'itmms_qa_error' ], home_url( $request_uri ) );
	}

	/**
	 * Keep public redirects inside the current WordPress site.
	 */
	private function is_same_site_url( string $url ): bool {
		$home_host = wp_parse_url( home_url(), PHP_URL_HOST );
		$url_host  = wp_parse_url( $url, PHP_URL_HOST );

		return is_string( $home_host ) && is_string( $url_host ) && strtolower( $home_host ) === strtolower( $url_host );
	}

	/**
	 * Basic public submit rate limit.
	 */
	private function can_submit_from_client(): bool {
		$ip = $this->client_hash();
		$key = 'itmms_ask_imam_rate_' . $ip;
		$count = absint( get_transient( $key ) );
		if ( $count >= 5 ) {
			return false;
		}

		set_transient( $key, $count + 1, HOUR_IN_SECONDS );
		return true;
	}

	/**
	 * Return a privacy-preserving client hash for rate limiting.
	 */
	private function client_hash(): string {
		$ip = 'unknown';
		if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		return substr( hash( 'sha256', $ip . wp_salt( 'nonce' ) ), 0, 20 );
	}

	/**
	 * Normalize public widget language.
	 */
	private function normalize_language( string $language ): string {
		$language = sanitize_key( $language );
		return in_array( $language, [ 'en', 'bn', 'ar' ], true ) ? $language : 'en';
	}

	/**
	 * @return array<string,string>
	 */
	private function public_labels( string $language ): array {
		$labels = [
			'eyebrow'              => __( 'Minbar Q&A', 'masjidos' ),
			'form_intro'           => __( 'Submit your Islamic question. The imam will review it before answering.', 'masjidos' ),
			'question_title'       => __( 'Short title', 'masjidos' ),
			'title_placeholder'    => __( 'Example: Is Friday ghusl Sunnah?', 'masjidos' ),
			'question'             => __( 'Your question', 'masjidos' ),
			'question_placeholder' => __( 'Write the question clearly. Avoid sharing sensitive personal details.', 'masjidos' ),
			'category'             => __( 'Category', 'masjidos' ),
			'choose_category'      => __( 'Choose category', 'masjidos' ),
			'name'                 => __( 'Name', 'masjidos' ),
			'email'                => __( 'Email', 'masjidos' ),
			'optional'             => __( 'Optional', 'masjidos' ),
			'optional_private'     => __( 'Optional, never shown publicly', 'masjidos' ),
			'keep_private'         => __( 'Keep this question private', 'masjidos' ),
			'review_note'          => __( 'Questions are reviewed before answers appear publicly.', 'masjidos' ),
			'submit'               => __( 'Submit Question', 'masjidos' ),
			'submitting'           => __( 'Submitting...', 'masjidos' ),
			'success_title'        => __( 'Question received', 'masjidos' ),
			'success'              => __( 'Question submitted. The imam can now review it from the admin panel.', 'masjidos' ),
			'success_hint'         => __( 'If the answer is marked public, it will appear in the answered questions library.', 'masjidos' ),
			'ask_another'          => __( 'Ask another question', 'masjidos' ),
			'error_title'          => __( 'Please check the form', 'masjidos' ),
			'error_general'        => __( 'Could not submit the question. Please try again.', 'masjidos' ),
			'error_rate'           => __( 'Too many questions were submitted recently. Please try again later.', 'masjidos' ),
			'error_question'       => __( 'Please write a clear question before submitting.', 'masjidos' ),
			'error_security'       => __( 'Security check failed. Please refresh the page and try again.', 'masjidos' ),
			'answers_eyebrow'      => __( 'Answered by the Imam', 'masjidos' ),
			'search_intro'         => __( 'Search the answered library before submitting a new question.', 'masjidos' ),
			'search_placeholder'   => __( 'Search answers...', 'masjidos' ),
			'all_categories'       => __( 'All categories', 'masjidos' ),
			'search'               => __( 'Search', 'masjidos' ),
			'no_answers'           => __( 'No public answers found', 'masjidos' ),
			'no_answers_hint'      => __( 'Try another search term or submit a new question.', 'masjidos' ),
			'question_label'       => __( 'Question asked', 'masjidos' ),
			'answer_label'         => __( 'Imam\'s answer', 'masjidos' ),
		];

		if ( 'bn' === $language ) {
			$labels = array_merge(
				$labels,
				[
					'eyebrow'              => 'মিম্বার প্রশ্নোত্তর',
					'form_intro'           => 'আপনার ইসলামিক প্রশ্ন পাঠান। ইমাম উত্তর দেওয়ার আগে প্রশ্নটি রিভিউ করবেন।',
					'question_title'       => 'সংক্ষিপ্ত শিরোনাম',
					'title_placeholder'    => 'যেমন: জুমার গোসল কি সুন্নত?',
					'question'             => 'আপনার প্রশ্ন',
					'question_placeholder' => 'প্রশ্নটি পরিষ্কারভাবে লিখুন। খুব ব্যক্তিগত তথ্য দেওয়া এড়িয়ে চলুন।',
					'category'             => 'ক্যাটাগরি',
					'choose_category'      => 'ক্যাটাগরি নির্বাচন করুন',
					'name'                 => 'নাম',
					'email'                => 'ইমেইল',
					'optional'             => 'ঐচ্ছিক',
					'optional_private'     => 'ঐচ্ছিক, প্রকাশ্যে দেখানো হবে না',
					'keep_private'         => 'এই প্রশ্নটি private রাখুন',
					'review_note'          => 'প্রশ্নগুলো রিভিউ হওয়ার পর public answer হিসেবে দেখানো হবে।',
					'submit'               => 'প্রশ্ন পাঠান',
					'success'              => 'প্রশ্ন জমা হয়েছে। ইমাম এখন admin panel থেকে রিভিউ করতে পারবেন।',
					'error_general'        => 'প্রশ্ন জমা হয়নি। আবার চেষ্টা করুন।',
					'error_rate'           => 'সম্প্রতি অনেক প্রশ্ন জমা হয়েছে। কিছুক্ষণ পর চেষ্টা করুন।',
					'error_question'       => 'জমা দেওয়ার আগে একটি পরিষ্কার প্রশ্ন লিখুন।',
					'error_security'       => 'Security check failed. পেজ refresh করে আবার চেষ্টা করুন।',
					'answers_eyebrow'      => 'ইমামের উত্তর',
					'search_intro'         => 'নতুন প্রশ্ন করার আগে আগের উত্তর খুঁজে দেখুন।',
					'search_placeholder'   => 'খুঁজুন: নামাজ, যাকাত, পরিবার, দৈনন্দিন জীবন...',
					'all_categories'       => 'সব ক্যাটাগরি',
					'search'               => 'খুঁজুন',
					'no_answers'           => 'কোনো public answer পাওয়া যায়নি',
					'no_answers_hint'      => 'অন্য শব্দ দিয়ে খুঁজুন অথবা নতুন প্রশ্ন পাঠান।',
					'question_label'       => 'প্রশ্ন',
					'answer_label'         => 'উত্তর',
				]
			);
		}

		return $labels;
	}

	/**
	 * Return normalized question status.
	 */
	public static function question_status( int $post_id ): string {
		$status = sanitize_key( (string) get_post_meta( $post_id, '_itmms_qa_status', true ) );
		return array_key_exists( $status, self::status_labels() ) ? $status : 'pending';
	}

	/**
	 * Whether a question answer may be public.
	 */
	public static function is_public( int $post_id ): bool {
		return '1' === (string) get_post_meta( $post_id, '_itmms_qa_is_public', true );
	}

	/**
	 * @return array<string,string>
	 */
	public static function status_labels(): array {
		return [
			'pending'  => __( 'Pending', 'masjidos' ),
			'answered' => __( 'Answered', 'masjidos' ),
			'closed'   => __( 'Closed', 'masjidos' ),
		];
	}

	/**
	 * @return array<string,string>
	 */
	private function default_categories(): array {
		return [
			'fiqh'       => __( 'Fiqh', 'masjidos' ),
			'aqeedah'    => __( 'Aqeedah', 'masjidos' ),
			'akhlaq'     => __( 'Akhlaq', 'masjidos' ),
			'daily-life' => __( 'Daily Life', 'masjidos' ),
		];
	}
}
