<?php
/**
 * A polite, dismissible request for a WordPress.org review.
 *
 * Shown only to administrators, only on MasjidOS's own admin screens, and only after the plugin has been
 * installed for 14 days. "Maybe later" hides it for another 14 days; "I already did" hides it for good.
 * Nothing is sent anywhere: the button just opens the plugin's review page on WordPress.org.
 *
 * @package MasjidOS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Review request notice.
 */
class ITMMS_Review_Notice {

	const OPT_INSTALLED = 'itmms_installed_at';
	const OPT_STATE     = 'itmms_review_notice';
	const DELAY         = 14 * DAY_IN_SECONDS;
	const ACTION        = 'itmms_review_notice';

	/**
	 * Hooks.
	 */
	public static function init() {
		if ( ! is_admin() ) {
			return;
		}
		add_action( 'admin_notices', array( __CLASS__, 'render' ) );
		add_action( 'admin_post_' . self::ACTION, array( __CLASS__, 'handle' ) );
	}

	/**
	 * Is this one of MasjidOS's own screens?
	 *
	 * @return bool
	 */
	private static function on_plugin_screen() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen check.
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		return 0 === strpos( $page, 'masjidos' );
	}

	/**
	 * Print the notice when it is due.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) || ! self::on_plugin_screen() ) {
			return;
		}

		$installed = (int) get_option( self::OPT_INSTALLED, 0 );
		if ( ! $installed ) {
			update_option( self::OPT_INSTALLED, time(), false );
			return;
		}

		$state = get_option( self::OPT_STATE, array() );
		$state = is_array( $state ) ? $state : array();
		if ( ! empty( $state['done'] ) || time() < $installed + self::DELAY || time() < (int) ( $state['later_until'] ?? 0 ) ) {
			return;
		}

		$link = function ( $choice ) {
			return wp_nonce_url(
				add_query_arg(
					array(
						'action' => self::ACTION,
						'choice' => $choice,
					),
					admin_url( 'admin-post.php' )
				),
				self::ACTION
			);
		};
		?>
		<div class="notice notice-info">
			<p>
				<strong><?php esc_html_e( 'Enjoying MasjidOS?', 'masjidos' ); ?></strong>
				<?php esc_html_e( 'A short review on WordPress.org helps other site owners find it and helps a small independent team keep improving it. It takes about a minute.', 'masjidos' ); ?>
			</p>
			<p>
				<a class="button button-primary" href="https://wordpress.org/support/plugin/masjidos/reviews/#new-post" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Leave a review', 'masjidos' ); ?></a>
				<a class="button" href="<?php echo esc_url( $link( 'later' ) ); ?>"><?php esc_html_e( 'Maybe later', 'masjidos' ); ?></a>
				<a class="button-link" href="<?php echo esc_url( $link( 'done' ) ); ?>"><?php esc_html_e( 'I already did', 'masjidos' ); ?></a>
			</p>
		</div>
		<?php
	}

	/**
	 * Save the choice and go back.
	 */
	public static function handle() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'masjidos' ), 403 );
		}
		check_admin_referer( self::ACTION );

		$choice = isset( $_GET['choice'] ) ? sanitize_key( wp_unslash( $_GET['choice'] ) ) : '';
		$state  = get_option( self::OPT_STATE, array() );
		$state  = is_array( $state ) ? $state : array();
		if ( 'done' === $choice ) {
			$state['done'] = 1;
		} else {
			$state['later_until'] = time() + self::DELAY;
		}
		update_option( self::OPT_STATE, $state, false );

		wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'admin.php?page=masjidos' ) );
		exit;
	}
}
