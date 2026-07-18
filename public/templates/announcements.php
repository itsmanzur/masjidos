<?php
/**
 * Template for active announcements list / ticker / banner / popup widget.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

// Check if currently Ramadan
$itmms_is_ramadan = false;
if ( class_exists( 'ITMMS_Hijri' ) ) {
	$itmms_hijri = ITMMS_Hijri::for_date( new DateTimeImmutable() );
	if ( isset( $itmms_hijri['month'] ) && 9 === (int) $itmms_hijri['month'] ) {
		$itmms_is_ramadan = true;
	}
}

$itmms_ramadan_class = $itmms_is_ramadan ? 'is-ramadan' : '';
$design = in_array( $design, [ 'ticker', 'banner', 'popup', 'list' ], true ) ? $design : 'list';

$itmms_active_label = 1 === count( $notices )
	? $labels['active_one']
	: sprintf(
		$labels['active_count'],
		class_exists( 'ITMMS_Hijri' )
			? ITMMS_Hijri::number( (string) count( $notices ), $language )
			: (string) count( $notices )
	);
?>

<?php if ( 'popup' === $design ) : ?>
	<!-- POPUP ANNOUNCEMENT -->
	<?php if ( ! empty( $notices ) ) : ?>
		<?php $itmms_top_notice = $notices[0]; // Display the highest priority notice as popup ?>
		<div class="itmms-public-popup-overlay" id="itmms-popup-<?php echo esc_attr( $itmms_top_notice['id'] ); ?>" data-itmms-popup-id="<?php echo esc_attr( $itmms_top_notice['id'] ); ?>" role="dialog" aria-modal="true" aria-labelledby="itmms-popup-title-<?php echo esc_attr( $itmms_top_notice['id'] ); ?>">
			<div class="itmms-public-popup-content <?php echo esc_attr( $itmms_ramadan_class ); ?>">
				<button type="button" class="itmms-public-popup-close" data-itmms-popup-close="<?php echo esc_attr( $itmms_top_notice['id'] ); ?>" aria-label="<?php echo esc_attr( __( 'Close popup', 'masjidos' ) ); ?>">&times;</button>

				<?php if ( $itmms_is_ramadan ) : ?>
					<div class="itmms-public-popup-ramadan-badge">
						<?php echo esc_html( __( 'Ramadan Mubarak', 'masjidos' ) ); ?>
					</div>
				<?php endif; ?>

				<h2 id="itmms-popup-title-<?php echo esc_attr( $itmms_top_notice['id'] ); ?>"><?php echo esc_html( (string) $itmms_top_notice['title'] ); ?></h2>
				<div class="itmms-public-popup-body">
					<?php echo nl2br( esc_html( (string) $itmms_top_notice['content'] ) ); ?>
				</div>
			</div>
		</div>
	<?php endif; ?>

<?php elseif ( 'banner' === $design ) : ?>
	<!-- BANNER STYLE -->
	<section class="itmms-public-announcements itmms-public-announcements--banner <?php echo esc_attr( $itmms_ramadan_class ); ?> itmms-public-announcements--lang-<?php echo esc_attr( $language ); ?>">
		<div class="itmms-public-announcements__banner-content">
			<?php foreach ( $notices as $itmms_notice ) : ?>
				<?php
				$itmms_type = (string) ( $itmms_notice['announcement_type'] ?? 'general' );
				$itmms_type_label = $labels[ $itmms_type ] ?? $labels['general'];
				?>
				<div class="itmms-public-announcements__banner-item itmms-public-announcements__banner-item--<?php echo esc_attr( $itmms_type ); ?>">
					<span class="itmms-public-announcements__banner-type"><?php echo esc_html( $itmms_type_label ); ?></span>
					<strong class="itmms-public-announcements__banner-title"><?php echo esc_html( (string) $itmms_notice['title'] ); ?></strong>
					<?php if ( '' !== trim( (string) $itmms_notice['content'] ) ) : ?>
						<div class="itmms-public-announcements__banner-body"><?php echo nl2br( esc_html( (string) $itmms_notice['content'] ) ); ?></div>
					<?php endif; ?>
					<?php if ( $show_date ) : ?>
						<time datetime="<?php echo esc_attr( str_replace( ' ', 'T', (string) $itmms_notice['start_date'] ) ); ?>"><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( (string) $itmms_notice['start_date'] ) ) ); ?></time>
					<?php endif; ?>
				</div>
				<?php break; // Show only the top priority notice in banner mode ?>
			<?php endforeach; ?>
		</div>
	</section>

<?php elseif ( 'ticker' === $design ) : ?>
	<!-- TICKER STYLE -->
	<section class="itmms-public-announcements itmms-public-announcements--ticker <?php echo esc_attr( $itmms_ramadan_class ); ?> itmms-public-announcements--lang-<?php echo esc_attr( $language ); ?>">
		<div class="itmms-public-announcements__ticker-label">
			<span><?php echo esc_html( $labels['notice'] ); ?></span>
			<button type="button" data-itmms-ticker-toggle aria-pressed="false" data-pause-label="<?php echo esc_attr( $labels['pause'] ); ?>" data-play-label="<?php echo esc_attr( $labels['play'] ); ?>"><?php echo esc_html( $labels['pause'] ); ?></button>
		</div>
		<div class="itmms-public-announcements__ticker-window">
			<div class="itmms-public-announcements__ticker-track">
				<?php for ( $itmms_copy = 0; $itmms_copy < 2; $itmms_copy++ ) : ?>
					<div class="itmms-public-announcements__ticker-group" <?php echo 1 === $itmms_copy ? 'aria-hidden="true"' : ''; ?>>
						<?php foreach ( $notices as $itmms_notice ) : ?>
							<?php
							$itmms_type = (string) ( $itmms_notice['announcement_type'] ?? 'general' );
							$itmms_type_label = $labels[ $itmms_type ] ?? $labels['general'];
							?>
							<span class="itmms-public-announcements__ticker-item itmms-public-announcements__ticker-item--<?php echo esc_attr( $itmms_type ); ?>">
								<em><?php echo esc_html( $itmms_type_label ); ?></em>
								<b><?php echo esc_html( (string) $itmms_notice['title'] ); ?></b>
								<?php echo esc_html( (string) $itmms_notice['content'] ); ?>
							</span>
						<?php endforeach; ?>
					</div>
				<?php endfor; ?>
			</div>
		</div>
	</section>

<?php else : ?>
	<!-- DEFAULT LIST STYLE -->
	<section class="itmms-public-announcements itmms-public-announcements--list <?php echo esc_attr( $itmms_ramadan_class ); ?> itmms-public-announcements--lang-<?php echo esc_attr( $language ); ?>">
		<header class="itmms-public-announcements__header">
			<div>
				<span class="itmms-public-announcements__eyebrow"><?php echo esc_html( $location ?: get_bloginfo( 'name' ) ); ?></span>
				<h2><?php echo esc_html( (string) $atts['title'] ); ?></h2>
			</div>
			<b><?php echo esc_html( $itmms_active_label ); ?></b>
		</header>
		<div class="itmms-public-announcements__list">
			<?php foreach ( $notices as $itmms_notice ) : ?>
				<article class="itmms-public-announcements__item itmms-public-announcements__item--<?php echo esc_attr( (string) $itmms_notice['announcement_type'] ); ?>">
					<div class="itmms-public-announcements__item-head">
						<span><?php echo esc_html( $labels[ (string) $itmms_notice['announcement_type'] ] ?? $labels['general'] ); ?></span>
						<?php if ( $show_date ) : ?><time datetime="<?php echo esc_attr( str_replace( ' ', 'T', (string) $itmms_notice['start_date'] ) ); ?>"><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( (string) $itmms_notice['start_date'] ) ) ); ?></time><?php endif; ?>
					</div>
					<h3><?php echo esc_html( (string) $itmms_notice['title'] ); ?></h3>
					<?php if ( '' !== (string) $itmms_notice['content'] ) : ?><p><?php echo nl2br( esc_html( (string) $itmms_notice['content'] ) ); ?></p><?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>
