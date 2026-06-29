<?php
/**
 * Template for active announcements list / ticker widget.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="itmms-public-announcements itmms-public-announcements--<?php echo esc_attr( $design ); ?> itmms-public-announcements--lang-<?php echo esc_attr( $language ); ?>">
	<?php if ( 'ticker' === $design ) : ?>
		<div class="itmms-public-announcements__ticker-label"><span><?php echo esc_html( $labels['notice'] ); ?></span><button type="button" data-itmms-ticker-toggle aria-pressed="false" data-pause-label="<?php echo esc_attr( $labels['pause'] ); ?>" data-play-label="<?php echo esc_attr( $labels['play'] ); ?>"><?php echo esc_html( $labels['pause'] ); ?></button></div>
		<div class="itmms-public-announcements__ticker-window">
			<div class="itmms-public-announcements__ticker-track">
				<?php for ( $itmms_copy = 0; $itmms_copy < 2; $itmms_copy++ ) : ?>
					<div class="itmms-public-announcements__ticker-group" <?php echo 1 === $itmms_copy ? 'aria-hidden="true"' : ''; ?>>
						<?php foreach ( $notices as $itmms_notice ) : ?>
							<span><b><?php echo esc_html( (string) $itmms_notice['title'] ); ?></b><?php echo esc_html( (string) $itmms_notice['content'] ); ?></span>
						<?php endforeach; ?>
					</div>
				<?php endfor; ?>
			</div>
		</div>
	<?php else : ?>
		<header class="itmms-public-announcements__header">
			<div><span><?php echo esc_html( $location ?: get_bloginfo( 'name' ) ); ?></span><h2><?php echo esc_html( (string) $atts['title'] ); ?></h2></div>
			<b><?php echo esc_html( 1 === count( $notices ) ? $labels['active_one'] : sprintf( $labels['active_count'], count( $notices ) ) ); ?></b>
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
	<?php endif; ?>
</section>
