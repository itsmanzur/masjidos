<?php
/**
 * ITMMS_Public_Designs methods for ITMMS_Public.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * @package MasjidOS
 */
trait ITMMS_Public_Designs {

	/**
	 * Available public widget designs. Pro can add designs with this filter,
	 * but free plugin only ships free design renderers.
	 *
	 * @return array<string,array<string,string>>
	 */
	private function get_designs(): array {
		$designs = [
			'classic' => [
				'label'       => __( 'Classic', 'masjidos' ),
				'tier'        => 'free',
				'description' => __( 'Balanced prayer times card with Qibla and meta panel.', 'masjidos' ),
			],
			'compact' => [
				'label'       => __( 'Compact', 'masjidos' ),
				'tier'        => 'free',
				'description' => __( 'Narrow prayer table for sidebars and smaller sections.', 'masjidos' ),
			],
			'premium-card' => [
				'label'       => __( 'Premium Card', 'masjidos' ),
				'tier'        => 'pro',
				'description' => __( 'Available in MasjidOS Pro.', 'masjidos' ),
			],
			'mosque-display' => [
				'label'       => __( 'Mosque Display', 'masjidos' ),
				'tier'        => 'pro',
				'description' => __( 'Available in MasjidOS Pro.', 'masjidos' ),
			],
			'ramadan-special' => [
				'label'       => __( 'Ramadan Special', 'masjidos' ),
				'tier'        => 'pro',
				'description' => __( 'Available in MasjidOS Pro.', 'masjidos' ),
			],
		];

		/**
		 * Filter public prayer widget design registry.
		 *
		 * Pro plugins may add their own design keys and render them through
		 * masjidos_render_prayer_widget_design.
		 *
		 * @param array<string,array<string,string>> $designs Design registry.
		 */
		$filtered = apply_filters( 'masjidos_prayer_widget_designs', $designs );
		return is_array( $filtered ) ? $filtered : $designs;
	}

	/**
	 * @return array<string,array<string,string>>
	 */
	private function get_jumuah_designs(): array {
		$designs = [
			'classic' => [
				'label'       => __( 'Classic', 'masjidos' ),
				'tier'        => 'free',
				'description' => __( 'Wide Jumuah card with khutbah, jamaat, and meta details.', 'masjidos' ),
			],
			'compact' => [
				'label'       => __( 'Compact', 'masjidos' ),
				'tier'        => 'free',
				'description' => __( 'Narrow Jumuah card for sidebars and mobile-first sections.', 'masjidos' ),
			],
			'premium-sermon' => [
				'label'       => __( 'Premium Sermon', 'masjidos' ),
				'tier'        => 'pro',
				'description' => __( 'Available in MasjidOS Pro.', 'masjidos' ),
			],
			'mosque-notice' => [
				'label'       => __( 'Mosque Notice', 'masjidos' ),
				'tier'        => 'pro',
				'description' => __( 'Available in MasjidOS Pro.', 'masjidos' ),
			],
		];

		/**
		 * Filter public Jumuah widget design registry.
		 *
		 * Pro plugins may add their own design keys and render them through
		 * masjidos_render_jumuah_widget_design.
		 *
		 * @param array<string,array<string,string>> $designs Design registry.
		 */
		$filtered = apply_filters( 'masjidos_jumuah_widget_designs', $designs );
		return is_array( $filtered ) ? $filtered : $designs;
	}

	/**
	 * @return array<string,array<string,string>>
	 */
	private function get_monthly_designs(): array {
		$designs = [
			'table' => [
				'label'       => __( 'Table', 'masjidos' ),
				'tier'        => 'free',
				'description' => __( 'Clean monthly prayer timetable table.', 'masjidos' ),
			],
			'compact' => [
				'label'       => __( 'Compact', 'masjidos' ),
				'tier'        => 'free',
				'description' => __( 'Card-style monthly timetable for narrower sections.', 'masjidos' ),
			],
			'premium-print' => [
				'label'       => __( 'Premium Print', 'masjidos' ),
				'tier'        => 'pro',
				'description' => __( 'Available in MasjidOS Pro.', 'masjidos' ),
			],
			'mosque-board' => [
				'label'       => __( 'Mosque Board', 'masjidos' ),
				'tier'        => 'pro',
				'description' => __( 'Available in MasjidOS Pro.', 'masjidos' ),
			],
			'ramadan-monthly' => [
				'label'       => __( 'Ramadan Monthly', 'masjidos' ),
				'tier'        => 'pro',
				'description' => __( 'Available in MasjidOS Pro.', 'masjidos' ),
			],
		];

		/**
		 * Filter public monthly timetable design registry.
		 *
		 * Pro plugins may add their own design keys and render them through
		 * masjidos_render_monthly_prayer_widget_design.
		 *
		 * @param array<string,array<string,string>> $designs Design registry.
		 */
		$filtered = apply_filters( 'masjidos_monthly_prayer_widget_designs', $designs );
		return is_array( $filtered ) ? $filtered : $designs;
	}

	/**
	 * @return array<string,array<string,string>>
	 */
	private function get_announcement_designs(): array {
		$designs = [
			'list' => [
				'label'       => __( 'Notice List', 'masjidos' ),
				'tier'        => 'free',
				'description' => __( 'Readable public notice board.', 'masjidos' ),
			],
			'ticker' => [
				'label'       => __( 'Notice Ticker', 'masjidos' ),
				'tier'        => 'free',
				'description' => __( 'Compact scrolling announcement strip.', 'masjidos' ),
			],
			'banner' => [
				'label'       => __( 'Notice Banner', 'masjidos' ),
				'tier'        => 'free',
				'description' => __( 'Slim top banner for the highest-priority notice.', 'masjidos' ),
			],
			'popup' => [
				'label'       => __( 'Popup Modal', 'masjidos' ),
				'tier'        => 'free',
				'description' => __( 'Dismissible modal for urgent announcements.', 'masjidos' ),
			],
			'digital-board' => [
				'label'       => __( 'Digital Board', 'masjidos' ),
				'tier'        => 'pro',
				'description' => __( 'Available in MasjidOS Pro.', 'masjidos' ),
			],
			'ramadan-banner' => [
				'label'       => __( 'Ramadan Banner', 'masjidos' ),
				'tier'        => 'pro',
				'description' => __( 'Available in MasjidOS Pro.', 'masjidos' ),
			],
		];

		$filtered = apply_filters( 'masjidos_announcement_widget_designs', $designs );
		return is_array( $filtered ) ? $filtered : $designs;
	}

	private function normalize_design( string $design, bool $legacy_compact ): string {
		$design = sanitize_key( $design );
		if ( $legacy_compact ) {
			return 'compact';
		}

		return $design ?: 'classic';
	}

	/**
	 * @param array<string,string>|null $definition Design definition.
	 */
	private function render_locked_design_notice( string $design, ?array $definition ): string {
		return $this->render_pro_lock_notice(
			$definition['label'] ?? ucwords( str_replace( '-', ' ', $design ) ),
			__( 'This prayer widget design is available in MasjidOS Pro.', 'masjidos' ),
			'[masjidos_prayer_times design="' . $design . '"]'
		);
	}

	/**
	 * @param array<string,string>|null $definition Design definition.
	 */
	private function render_locked_jumuah_design_notice( string $design, ?array $definition ): string {
		return $this->render_pro_lock_notice(
			$definition['label'] ?? ucwords( str_replace( '-', ' ', $design ) ),
			__( 'This Jumuah widget design is available in MasjidOS Pro.', 'masjidos' ),
			'[masjidos_jumuah design="' . $design . '"]'
		);
	}

	/**
	 * @param array<string,string>|null $definition Design definition.
	 */
	private function render_locked_monthly_design_notice( string $design, ?array $definition ): string {
		return $this->render_pro_lock_notice(
			$definition['label'] ?? ucwords( str_replace( '-', ' ', $design ) ),
			__( 'This monthly timetable design is available in MasjidOS Pro.', 'masjidos' ),
			'[masjidos_monthly_prayer_times design="' . $design . '"]'
		);
	}

	/**
	 * @param array<string,string>|null $definition Design definition.
	 */
	private function render_locked_announcement_design_notice( string $design, ?array $definition ): string {
		return $this->render_pro_lock_notice(
			$definition['label'] ?? ucwords( str_replace( '-', ' ', $design ) ),
			__( 'This announcement widget design is available in MasjidOS Pro.', 'masjidos' ),
			'[masjidos_announcements design="' . $design . '"]'
		);
	}

	/**
	 * Shared Pro lock notice with marketing CTA (no Pro code).
	 */
	private function render_pro_lock_notice( string $label, string $message, string $shortcode ): string {
		$pro_url = function_exists( 'masjidos_pro_url' ) ? masjidos_pro_url() : '';
		$cta     = '';
		if ( $pro_url && ! masjidos_pro_is_active() ) {
			$cta = '<p class="itmms-public-prayer-lock__cta"><a href="' . esc_url( $pro_url ) . '" target="_blank" rel="noopener noreferrer">' .
				esc_html__( 'Learn about MasjidOS Pro', 'masjidos' ) .
			'</a></p>';
		}

		return '<div class="itmms-public-prayer-lock">' .
			'<strong>' . esc_html( $label ) . '</strong>' .
			'<p>' . esc_html( $message ) . '</p>' .
			'<code>' . esc_html( $shortcode ) . '</code>' .
			$cta .
		'</div>';
	}

}
