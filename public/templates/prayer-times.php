<?php
/**
 * Template for daily prayer times widget.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="itmms-public-prayer itmms-public-prayer--<?php echo esc_attr( $design ); ?> itmms-public-prayer--lang-<?php echo esc_attr( $language ); ?> <?php echo $is_compact ? 'itmms-public-prayer--compact' : ''; ?> <?php echo $show_iqamah_column ? '' : 'itmms-public-prayer--no-iqamah'; ?>" data-next-prayer="<?php echo esc_attr( $next['raw'] ?? '' ); ?>">
	<div class="itmms-public-prayer__hero">
		<div>
			<span class="itmms-public-prayer__eyebrow"><?php echo esc_html( $meta['location'] ?: get_bloginfo( 'name' ) ); ?></span>
			<h2><?php echo esc_html( $atts['title'] ); ?></h2>
			<p class="itmms-public-prayer__dates">
				<span><?php echo esc_html( $date_label ); ?></span>
				<?php if ( ! empty( $hijri_label ) ) : ?>
					<span><?php echo esc_html( $hijri_label ); ?></span>
				<?php endif; ?>
				<span><?php echo esc_html( $meta['timezone'] ?? '' ); ?></span>
			</p>
			<?php if ( $show_meta && ! empty( $trust_items ) ) : ?>
				<div class="itmms-public-prayer__trust" aria-label="<?php echo esc_attr( __( 'Prayer calculation details', 'masjidos' ) ); ?>">
					<?php foreach ( $trust_items as $itmms_trust_item ) : ?>
						<?php if ( '' !== trim( (string) $itmms_trust_item[1] ) ) : ?>
							<span><b><?php echo esc_html( (string) $itmms_trust_item[0] ); ?></b><?php echo esc_html( (string) $itmms_trust_item[1] ); ?></span>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
				<?php if ( ! empty( $trust_note ) ) : ?>
					<p class="itmms-public-prayer__trust-note"><?php echo esc_html( (string) $trust_note ); ?></p>
				<?php endif; ?>
			<?php endif; ?>
		</div>
		<div class="itmms-public-prayer__next">
			<span><?php echo esc_html( $labels['next_prayer'] ); ?></span>
			<strong><?php echo esc_html( $next_name . ' - ' . ITMMS_Hijri::format_clock( (string) ( $next['time'] ?? '' ), $language ) ); ?></strong>
			<b data-itmms-public-countdown>00:00:00</b>
		</div>
	</div>

	<div class="itmms-public-prayer__grid">
		<div class="itmms-public-prayer__table">
			<?php foreach ( $data['prayers'] as $itmms_prayer ) : ?>
				<?php
				$itmms_is_extra = 'extra' === ( $itmms_prayer['kind'] ?? '' ) || in_array( (string) ( $itmms_prayer['key'] ?? '' ), [ 'ishraq', 'zawal', 'sunrise' ], true );
				?>
				<div class="itmms-public-prayer__row <?php echo ! empty( $itmms_prayer['current'] ) ? 'is-current' : ''; ?> <?php echo $itmms_is_extra ? 'is-extra' : ''; ?>">
					<span class="itmms-public-prayer__name">
						<?php echo esc_html( $this->prayer_label( (string) $itmms_prayer['key'], $language, (string) $itmms_prayer['name'] ) ); ?>
					</span>
					<?php if ( $show_iqamah_column ) : ?>
						<span class="itmms-public-prayer__iqamah">
							<?php if ( ! $itmms_is_extra && ! empty( $itmms_prayer['iqamah'] ) ) : ?>
								<small><?php echo esc_html( $labels['iqamah'] ); ?></small>
								<b><?php echo esc_html( ITMMS_Hijri::format_clock( (string) $itmms_prayer['iqamah'], $language ) ); ?></b>
							<?php endif; ?>
						</span>
					<?php endif; ?>
					<span class="itmms-public-prayer__time">
						<time><?php echo esc_html( ITMMS_Hijri::format_clock( (string) $itmms_prayer['time'], $language ) ); ?></time>
						<?php if ( ! empty( $itmms_prayer['offset'] ) && 'ishraq' !== ( $itmms_prayer['key'] ?? '' ) ) : ?>
							<?php
							$itmms_base_time_label = sprintf(
								/* translators: %s: Prayer time before the configured minute offset is applied. */
								__( 'Base time: %s', 'masjidos' ),
								ITMMS_Hijri::format_clock( (string) ( $itmms_prayer['base_time'] ?? $itmms_prayer['time'] ), $language )
							);
							?>
							<i title="<?php echo esc_attr( $itmms_base_time_label ); ?>">
								<?php echo esc_html( ITMMS_Hijri::number( ( $itmms_prayer['offset'] > 0 ? '+' : '' ) . $itmms_prayer['offset'] . 'm', $language ) ); ?>
							</i>
						<?php endif; ?>
						<?php if ( ! empty( $itmms_prayer['current'] ) ) : ?>
							<b><?php echo esc_html( $labels['now'] ); ?></b>
						<?php endif; ?>
					</span>
				</div>
			<?php endforeach; ?>
		</div>

		<?php
		// Hero trust chips already cover method/source; keep side meta only when trust is off.
		$itmms_show_side_meta = $show_meta && empty( $trust_items );
		?>
		<?php if ( $show_qibla || $itmms_show_side_meta ) : ?>
			<aside class="itmms-public-prayer__side">
				<?php if ( $show_qibla ) : ?>
					<div class="itmms-public-qibla" data-itmms-public-qibla="<?php echo esc_attr( (string) ( $meta['qibla_direction'] ?? 0 ) ); ?>" role="button" tabindex="0" title="<?php echo esc_attr( $labels['qibla_prompt'] ); ?>">
						<div class="itmms-public-qibla__compass">
							<span style="transform: rotate(<?php echo esc_attr( (string) ( $meta['qibla_direction'] ?? 0 ) ); ?>deg)"></span>
						</div>
						<div>
							<span><?php echo esc_html( $labels['qibla'] ); ?></span>
							<strong><?php echo esc_html( ITMMS_Hijri::number( (string) ( $meta['qibla_direction'] ?? '0' ), $language ) ); ?>&deg;</strong>
							<small class="itmms-public-qibla__prompt"><?php echo esc_html( $labels['qibla_prompt'] ); ?></small>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( $itmms_show_side_meta ) : ?>
					<div class="itmms-public-prayer__meta">
						<span><b><?php echo esc_html( $labels['location'] ); ?></b><?php echo esc_html( $meta['location'] ?? '' ); ?></span>
						<span><b><?php echo esc_html( $labels['method'] ); ?></b><?php echo esc_html( $meta['calculation_method'] ?? '' ); ?></span>
						<span><b><?php echo esc_html( $labels['asr'] ); ?></b><?php echo esc_html( $meta['asr_method'] ?? '' ); ?></span>
						<span><b><?php echo esc_html( $labels['timezone'] ); ?></b><?php echo esc_html( $meta['timezone'] ?? '' ); ?></span>
						<span><b><?php echo esc_html( $labels['source'] ); ?></b><?php echo esc_html( $source_label ); ?></span>
					</div>
				<?php endif; ?>
			</aside>
		<?php endif; ?>
	</div>
</div>
