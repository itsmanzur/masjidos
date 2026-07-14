<?php
/**
 * Template for Audio Quran Embed public widget.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$itmms_quran_labels = [
	'select_surah' => ( 'bn' === $language ) ? 'সূরা নির্বাচন করুন' : __( 'Select Surah', 'masjidos' ),
	'play'         => ( 'bn' === $language ) ? 'প্লে' : __( 'Play', 'masjidos' ),
];
?>
<section class="itmms-public-audio-quran itmms-public-audio-quran--lang-<?php echo esc_attr( $language ); ?>">
	<header class="itmms-public-audio-quran__header">
		<h2><?php echo esc_html( (string) $atts['title'] ); ?></h2>
	</header>
	<div class="itmms-public-audio-quran__player-container">
		<div class="itmms-public-audio-quran__selector">
			<select id="itmms-quran-surah-select" data-itmms-quran-surah>
				<option value=""><?php echo esc_html( $itmms_quran_labels['select_surah'] ); ?></option>
				<?php foreach ( $surahs as $number => $surah ) : ?>
					<?php
					$name = $surah['name_en'];
					if ( 'bn' === $language ) {
						$name = $surah['name_bn'];
					} elseif ( 'ar' === $language ) {
						$name = $surah['name_ar'];
					}
					$url = sprintf( 'https://download.quranicaudio.com/quran/mishary_radhi_al_afasy/%03d.mp3', $number );
					?>
					<option value="<?php echo esc_url( $url ); ?>">
						<?php echo esc_html( sprintf( '%d. %s (%s)', $number, $name, $surah['name_ar'] ) ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="itmms-public-audio-quran__audio-element">
			<audio id="itmms-quran-audio-player" controls preload="none"></audio>
		</div>
	</div>
</section>
