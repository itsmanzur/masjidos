<?php
/**
 * Template for public Jumuah widget.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

$itmms_hero_location = $location ?: get_bloginfo( 'name' );
$itmms_show_meta_location = $has_meta && $location && $location !== $itmms_hero_location;
?>
<div class="itmms-public-jumuah itmms-public-jumuah--<?php echo esc_attr( $design ); ?> itmms-public-jumuah--lang-<?php echo esc_attr( $language ); ?> <?php echo $is_compact ? 'itmms-public-jumuah--compact' : ''; ?> <?php echo $has_meta ? '' : 'itmms-public-jumuah--no-meta'; ?> <?php echo $meta_lite ? 'itmms-public-jumuah--meta-lite' : ''; ?>">
	<div class="itmms-public-jumuah__hero">
		<div>
			<span class="itmms-public-jumuah__eyebrow"><?php echo esc_html( $itmms_hero_location ); ?></span>
			<h2><?php echo esc_html( $atts['title'] ); ?></h2>
			<p class="itmms-public-jumuah__context"><?php echo esc_html( $labels['friday'] ?? __( 'Friday', 'masjidos' ) ); ?></p>
			<?php if ( $notice ) : ?>
				<p class="itmms-public-jumuah__notice"><?php echo esc_html( $notice ); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<div class="itmms-public-jumuah__body">
		<div class="itmms-public-jumuah__times">
			<?php foreach ( $sessions as $itmms_index => $itmms_session ) : ?>
				<?php
				$itmms_session = is_array( $itmms_session ) ? $itmms_session : [];
				$itmms_khutbah = $this->format_time( (string) ( $itmms_session['khutbah_time'] ?? '' ), $timezone, $language );
				$itmms_jamaat  = $this->format_time( (string) ( $itmms_session['jamaat_time'] ?? '' ), $timezone, $language );
				$itmms_primary = $itmms_jamaat ?: $itmms_khutbah;
				?>
				<div class="itmms-public-jumuah__session">
					<span class="itmms-public-jumuah__session-label"><?php echo esc_html( $this->jumuah_session_label( (string) ( $itmms_session['label'] ?? '' ), $language, (int) $itmms_index ) ); ?></span>
					<?php if ( $itmms_khutbah ) : ?>
						<small class="itmms-public-jumuah__khutbah"><?php echo esc_html( $labels['khutbah'] ); ?>: <?php echo esc_html( $itmms_khutbah ); ?></small>
					<?php endif; ?>
					<?php if ( $itmms_primary ) : ?>
						<strong class="itmms-public-jumuah__jamaat">
							<em><?php echo esc_html( $itmms_jamaat ? $labels['jamaat'] : $labels['khutbah'] ); ?></em>
							<?php echo esc_html( $itmms_primary ); ?>
						</strong>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php if ( $has_meta ) : ?>
			<div class="itmms-public-jumuah__meta">
				<?php if ( $has_khatib_profile ) : ?>
					<div class="itmms-public-jumuah__khatib">
						<?php if ( $khatib_image ) : ?>
							<img src="<?php echo esc_url( $khatib_image ); ?>" alt="<?php echo esc_attr( $khatib_name ?: $labels['khatib'] ); ?>">
						<?php else : ?>
							<i><?php echo esc_html( $this->initials( $khatib_name ?: $labels['khatib'] ) ); ?></i>
						<?php endif; ?>
						<div>
							<span><?php echo esc_html( $labels['khatib'] ); ?></span>
							<strong><?php echo esc_html( $khatib_name ?: $labels['khatib'] ); ?></strong>
							<?php if ( $khatib_bio ) : ?>
								<p><?php echo esc_html( $khatib_bio ); ?></p>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>
				<?php if ( $topic ) : ?>
					<span><b><?php echo esc_html( $labels['topic'] ); ?></b><?php echo esc_html( $topic ); ?></span>
				<?php endif; ?>
				<?php if ( $jumuah_language ) : ?>
					<span><b><?php echo esc_html( $labels['language'] ); ?></b><?php echo esc_html( $jumuah_language ); ?></span>
				<?php endif; ?>
				<?php if ( $itmms_show_meta_location ) : ?>
					<span><b><?php echo esc_html( $labels['location'] ); ?></b><?php echo esc_html( $location ); ?></span>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</div>
