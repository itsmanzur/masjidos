<?php
/**
 * Template for the Duas & Azkar public widget.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="itmms-public-duas itmms-public-duas--<?php echo esc_attr( $design ); ?> itmms-public-duas--lang-<?php echo esc_attr( $language ); ?>">
	<header class="itmms-public-duas__header">
		<div>
			<span><?php echo esc_html( $labels['eyebrow'] ); ?></span>
			<h2><?php echo esc_html( (string) $atts['title'] ); ?></h2>
		</div>
		<b><?php echo esc_html( $labels['category'] ); ?>: <?php echo esc_html( $category_label ); ?></b>
	</header>

	<div class="itmms-public-duas__list">
		<?php foreach ( $items as $itmms_dua ) : ?>
			<?php
			$itmms_dua_key = sanitize_key( (string) $itmms_dua['key'] );
			$itmms_share_text = trim(
				(string) $itmms_dua['title'] . "\n\n" .
				(string) $itmms_dua['arabic'] . "\n\n" .
				(string) $itmms_dua['meaning']
			);
			?>
			<article class="itmms-public-duas__item" data-itmms-dua-key="<?php echo esc_attr( $itmms_dua_key ); ?>" data-itmms-dua-text="<?php echo esc_attr( $itmms_share_text ); ?>">
				<div class="itmms-public-duas__meta">
					<div class="itmms-public-duas__title">
						<span><?php echo esc_html( (string) $itmms_dua['title'] ); ?></span>
						<?php if ( $show_source && ! empty( $itmms_dua['source'] ) ) : ?>
							<small><?php echo esc_html( $labels['source'] ); ?>: <?php echo esc_html( (string) $itmms_dua['source'] ); ?></small>
						<?php endif; ?>
					</div>
					<?php if ( ! empty( $itmms_dua['repeat'] ) ) : ?>
						<b><?php echo esc_html( sprintf( $labels['repeat'], (int) $itmms_dua['repeat'] ) ); ?></b>
					<?php endif; ?>
				</div>
				<?php if ( ! empty( $itmms_dua['categories'] ) && is_array( $itmms_dua['categories'] ) ) : ?>
					<div class="itmms-public-duas__badges" aria-label="<?php echo esc_attr( $labels['category'] ); ?>">
						<?php foreach ( array_slice( $itmms_dua['categories'], 0, 3 ) as $itmms_dua_category ) : ?>
							<?php $itmms_dua_category_key = sanitize_key( (string) $itmms_dua_category ); ?>
							<span><?php echo esc_html( $category_labels[ $itmms_dua_category_key ] ?? ucwords( str_replace( '-', ' ', $itmms_dua_category_key ) ) ); ?></span>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				<p class="itmms-public-duas__arabic" dir="rtl" lang="ar"><?php echo esc_html( (string) $itmms_dua['arabic'] ); ?></p>
				<?php if ( 'compact' !== $design ) : ?>
					<p class="itmms-public-duas__latin"><?php echo esc_html( (string) $itmms_dua['latin'] ); ?></p>
				<?php endif; ?>
				<p class="itmms-public-duas__meaning"><b><?php echo esc_html( $labels['translation'] ); ?>:</b> <?php echo esc_html( (string) $itmms_dua['meaning'] ); ?></p>
				<div class="itmms-public-duas__actions">
					<?php if ( $show_counter ) : ?>
						<button type="button" class="itmms-public-duas__counter" data-itmms-dua-count="<?php echo esc_attr( $itmms_dua_key ); ?>" aria-label="<?php echo esc_attr( $labels['counter'] ); ?>">
							<span><?php echo esc_html( $labels['read'] ); ?></span>
							<b data-itmms-dua-count-value>0</b>
						</button>
						<button type="button" class="itmms-public-duas__iconbtn" data-itmms-dua-reset="<?php echo esc_attr( $itmms_dua_key ); ?>" aria-label="<?php echo esc_attr( $labels['reset'] ); ?>">
							<?php echo esc_html( $labels['reset_short'] ); ?>
						</button>
					<?php endif; ?>
					<?php if ( $show_audio ) : ?>
						<button type="button" class="itmms-public-duas__iconbtn" data-itmms-dua-audio="<?php echo esc_url( (string) ( $itmms_dua['audio_url'] ?? '' ) ); ?>" aria-label="<?php echo esc_attr( $labels['listen'] ); ?>" <?php disabled( empty( $itmms_dua['audio_url'] ) ); ?>>
							<?php echo esc_html( $labels['listen_short'] ); ?>
						</button>
					<?php endif; ?>
					<?php if ( $show_share ) : ?>
						<button type="button" class="itmms-public-duas__share" data-itmms-dua-share aria-label="<?php echo esc_attr( $labels['share'] ); ?>">
							<?php echo esc_html( $labels['share'] ); ?>
						</button>
					<?php endif; ?>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
</section>
