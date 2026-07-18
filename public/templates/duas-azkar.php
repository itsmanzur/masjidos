<?php
/**
 * Template for the Duas & Azkar public widget.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

$itmms_count = count( $items );
$itmms_count_label = class_exists( 'ITMMS_Hijri' )
	? ITMMS_Hijri::number( (string) $itmms_count, $language )
	: (string) $itmms_count;
?>
<section class="itmms-public-duas itmms-public-duas--<?php echo esc_attr( $design ); ?> itmms-public-duas--lang-<?php echo esc_attr( $language ); ?>" data-itmms-share-success="<?php echo esc_attr( $labels['copied'] ); ?>">
	<header class="itmms-public-duas__header">
		<div>
			<span class="itmms-public-duas__eyebrow"><?php echo esc_html( $labels['eyebrow'] ); ?></span>
			<h2><?php echo esc_html( (string) $atts['title'] ); ?></h2>
		</div>
		<b><?php echo esc_html( $category_label ); ?> · <?php echo esc_html( $itmms_count_label ); ?></b>
	</header>

	<div class="itmms-public-duas__list">
		<?php foreach ( $items as $itmms_dua ) : ?>
			<?php
			$itmms_dua_key = sanitize_key( (string) $itmms_dua['key'] );
			$itmms_repeat  = max( 0, (int) ( $itmms_dua['repeat'] ?? 0 ) );
			$itmms_repeat_label = $itmms_repeat > 0
				? sprintf(
					str_replace( '%d', '%s', (string) $labels['repeat'] ),
					class_exists( 'ITMMS_Hijri' )
						? ITMMS_Hijri::number( (string) $itmms_repeat, $language )
						: (string) $itmms_repeat
				)
				: '';
			$itmms_share_text = trim(
				(string) $itmms_dua['title'] . "\n\n" .
				(string) $itmms_dua['arabic'] . "\n\n" .
				(string) $itmms_dua['meaning']
			);
			?>
			<article class="itmms-public-duas__item" data-itmms-dua-key="<?php echo esc_attr( $itmms_dua_key ); ?>" data-itmms-dua-text="<?php echo esc_attr( $itmms_share_text ); ?>" <?php echo $itmms_repeat > 0 ? 'data-itmms-dua-target="' . esc_attr( (string) $itmms_repeat ) . '"' : ''; ?>>
				<div class="itmms-public-duas__meta">
					<div class="itmms-public-duas__title">
						<span><?php echo esc_html( (string) $itmms_dua['title'] ); ?></span>
						<?php if ( $show_source && ! empty( $itmms_dua['source'] ) ) : ?>
							<small><?php echo esc_html( $labels['source'] ); ?>: <?php echo esc_html( (string) $itmms_dua['source'] ); ?></small>
						<?php endif; ?>
					</div>
					<?php if ( $itmms_repeat_label ) : ?>
						<b><?php echo esc_html( $itmms_repeat_label ); ?></b>
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
				<?php if ( 'compact' !== $design && ! empty( $itmms_dua['latin'] ) ) : ?>
					<p class="itmms-public-duas__latin"><?php echo esc_html( (string) $itmms_dua['latin'] ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $itmms_dua['meaning'] ) ) : ?>
					<p class="itmms-public-duas__meaning"><b><?php echo esc_html( $labels['translation'] ); ?></b> <?php echo esc_html( (string) $itmms_dua['meaning'] ); ?></p>
				<?php endif; ?>
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
						<button type="button" class="itmms-public-duas__share" data-itmms-dua-share data-itmms-share-success="<?php echo esc_attr( $labels['copied'] ); ?>" aria-label="<?php echo esc_attr( $labels['share'] ); ?>">
							<?php echo esc_html( $labels['share'] ); ?>
						</button>
					<?php endif; ?>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
</section>
