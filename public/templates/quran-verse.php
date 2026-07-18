<?php
/**
 * Template for public Quran Verse of the Day widget.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

$itmms_verse_labels = [
	'eyebrow' => ( 'bn' === $language ) ? 'আয়াতুল কুরআন' : ( ( 'ar' === $language ) ? 'آية قرآنية' : __( 'Quran', 'masjidos' ) ),
	'share'   => ( 'bn' === $language ) ? 'শেয়ার করুন' : ( ( 'ar' === $language ) ? 'مشاركة' : __( 'Share', 'masjidos' ) ),
	'tafsir'  => ( 'bn' === $language ) ? 'তাফসীর দেখুন' : ( ( 'ar' === $language ) ? 'التفسير' : __( 'View Tafsir', 'masjidos' ) ),
	'copied'  => ( 'bn' === $language ) ? 'কপি হয়েছে' : ( ( 'ar' === $language ) ? 'تم النسخ' : __( 'Copied', 'masjidos' ) ),
];

$itmms_translation = $verse['en'] ?? '';
if ( 'bn' === $language ) {
	$itmms_translation = $verse['bn'] ?? $itmms_translation;
}

$itmms_tafsir_url = 'https://quran.com/search?q=' . rawurlencode( (string) ( $verse['ref'] ?? '' ) );
$itmms_share_text = trim(
	(string) ( $verse['ar'] ?? '' ) . "\n\n" .
	(string) $itmms_translation . "\n\n" .
	'(' . (string) ( $verse['ref'] ?? '' ) . ')'
);
?>
<section class="itmms-public-verse itmms-public-verse--<?php echo esc_attr( $design ); ?> itmms-public-verse--lang-<?php echo esc_attr( $language ); ?>">
	<header class="itmms-public-verse__header">
		<div>
			<span class="itmms-public-verse__eyebrow"><?php echo esc_html( $itmms_verse_labels['eyebrow'] ); ?></span>
			<h2><?php echo esc_html( (string) $atts['title'] ); ?></h2>
		</div>
		<?php if ( ! empty( $verse['ref'] ) ) : ?>
			<b><?php echo esc_html( (string) $verse['ref'] ); ?></b>
		<?php endif; ?>
	</header>
	<div class="itmms-public-verse__content">
		<p class="itmms-public-verse__arabic" dir="rtl" lang="ar"><?php echo esc_html( (string) ( $verse['ar'] ?? '' ) ); ?></p>
		<?php if ( $itmms_translation ) : ?>
			<p class="itmms-public-verse__translation"><?php echo esc_html( (string) $itmms_translation ); ?></p>
		<?php endif; ?>
		<?php if ( $show_tafsir || $show_share ) : ?>
			<div class="itmms-public-verse__actions">
				<?php if ( $show_tafsir ) : ?>
					<a href="<?php echo esc_url( $itmms_tafsir_url ); ?>" target="_blank" rel="noopener noreferrer" class="itmms-public-verse__link">
						<?php echo esc_html( $itmms_verse_labels['tafsir'] ); ?>
					</a>
				<?php endif; ?>
				<?php if ( $show_share ) : ?>
					<button type="button" class="itmms-public-verse__share" data-itmms-education-share data-itmms-share-text="<?php echo esc_attr( $itmms_share_text ); ?>" data-itmms-share-success="<?php echo esc_attr( $itmms_verse_labels['copied'] ); ?>">
						<?php echo esc_html( $itmms_verse_labels['share'] ); ?>
					</button>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
