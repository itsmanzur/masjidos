<?php
/**
 * Template for Audio Quran Embed public widget.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

$itmms_quran_labels = [
	'eyebrow'      => ( 'bn' === $language ) ? 'অডিও কুরআন' : ( ( 'ar' === $language ) ? 'القرآن الصوتي' : __( 'Audio Quran', 'masjidos' ) ),
	'select_surah' => ( 'bn' === $language ) ? 'সূরা নির্বাচন করুন' : ( ( 'ar' === $language ) ? 'اختر سورة' : __( 'Select Surah', 'masjidos' ) ),
	'reciter'      => ( 'bn' === $language ) ? 'মিশারী রাশিদ আল-আফাসী' : ( ( 'ar' === $language ) ? 'مشاري راشد العفاسي' : __( 'Mishary Rashid Alafasy', 'masjidos' ) ),
];
$itmms_player_id = 'itmms-quran-audio-' . wp_unique_id();
?>
<section class="itmms-public-audio-quran itmms-public-audio-quran--<?php echo esc_attr( $design ); ?> itmms-public-audio-quran--lang-<?php echo esc_attr( $language ); ?>">
	<header class="itmms-public-audio-quran__header">
		<div>
			<span class="itmms-public-audio-quran__eyebrow"><?php echo esc_html( $itmms_quran_labels['eyebrow'] ); ?></span>
			<h2><?php echo esc_html( (string) $atts['title'] ); ?></h2>
		</div>
		<b><?php echo esc_html( $itmms_quran_labels['reciter'] ); ?></b>
	</header>
	<div class="itmms-public-audio-quran__body">
		<label class="itmms-public-audio-quran__selector">
			<span class="screen-reader-text"><?php echo esc_html( $itmms_quran_labels['select_surah'] ); ?></span>
			<select data-itmms-quran-surah aria-label="<?php echo esc_attr( $itmms_quran_labels['select_surah'] ); ?>">
				<option value=""><?php echo esc_html( $itmms_quran_labels['select_surah'] ); ?></option>
				<?php foreach ( $surahs as $itmms_number => $itmms_surah ) : ?>
					<?php
					$itmms_name = $itmms_surah['name_en'];
					if ( 'bn' === $language ) {
						$itmms_name = $itmms_surah['name_bn'];
					} elseif ( 'ar' === $language ) {
						$itmms_name = $itmms_surah['name_ar'];
					}
					$itmms_url = sprintf( 'https://download.quranicaudio.com/quran/mishary_radhi_al_afasy/%03d.mp3', (int) $itmms_number );
					$itmms_num = class_exists( 'ITMMS_Hijri' )
						? ITMMS_Hijri::number( (string) (int) $itmms_number, $language )
						: (string) (int) $itmms_number;
					?>
					<option value="<?php echo esc_url( $itmms_url ); ?>">
						<?php echo esc_html( sprintf( '%s. %s (%s)', $itmms_num, $itmms_name, $itmms_surah['name_ar'] ) ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</label>
		<audio class="itmms-public-audio-quran__player" id="<?php echo esc_attr( $itmms_player_id ); ?>" data-itmms-quran-player controls preload="none"></audio>
	</div>
</section>
